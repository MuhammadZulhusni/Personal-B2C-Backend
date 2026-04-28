<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSuggestionController extends Controller
{
    /**
     * AI-powered product suggestion based on vibe/mood/occasion.
     * Uses OpenAI when API key is available, falls back to simple matching otherwise.
     */
    public function suggest(Request $request)
    {
        $request->validate([
            'vibe' => 'required|string|max:100',
        ]);

        $vibe = strtolower(trim($request->input('vibe')));

        // Fetch all in-stock products
        $products = Product::where('stock', '>', 0)
            ->select('id', 'name', 'description', 'category', 'price', 'stock', 'image')
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'error' => 'No products available right now. Please check back later!'
            ], 404);
        }

        $apiKey = env('OPENAI_API_KEY');

        Log::info('AI Suggestion request', [
            'vibe'        => $vibe,
            'has_api_key' => !empty($apiKey) && str_starts_with($apiKey, 'sk-'),
        ]);

        // Try OpenAI if valid API key exists
        if ($apiKey && str_starts_with($apiKey, 'sk-')) {
            Log::info('Attempting OpenAI suggestion for: ' . $vibe);
            
            $results = $this->openAiSuggest($vibe, $products, $apiKey);
            
            if ($results !== null) {
                Log::info('OpenAI suggestion successful', [
                    'vibe'         => $vibe,
                    'results_count' => count($results),
                ]);

                return response()->json([
                    'vibe'    => $vibe,
                    'results' => $results,
                    'source'  => 'openai',
                ]);
            }

            Log::warning('OpenAI suggestion failed, using fallback for: ' . $vibe);
        }

        // Fallback to simple keyword-based matching
        Log::info('Using simple suggestion for: ' . $vibe);
        
        $results = $this->simpleSuggest($vibe, $products);

        return response()->json([
            'vibe'    => $vibe,
            'results' => $results,
            'source'  => 'fallback',
        ]);
    }

    /**
     * OpenAI GPT-4o-mini based suggestion.
     * Returns null if API fails, so caller can use fallback.
     */
    private function openAiSuggest(string $vibe, $products, string $apiKey): ?array
    {
        try {
            // Build compact product list for the prompt
            $productList = $products->map(function ($p) {
                return "ID:{$p->id} | Name:{$p->name} | Category:{$p->category} | Price:RM{$p->price} | Description:{$p->description} | Stock:{$p->stock}";
            })->implode("\n");

            $prompt = <<<EOT
You are a smart personal shopping assistant for a Malaysian e-commerce store called "Mini Shop".

A customer wants to shop for this vibe, mood, or occasion: "{$vibe}"

Here is our current product inventory:
{$productList}

Your task:
1. Pick exactly 3 to 4 products from the list above that BEST match the customer's vibe.
2. ONLY use products from the provided list. Do NOT invent or hallucinate products.
3. For each product, write a short, enthusiastic, 1-sentence reason why it fits the vibe.
4. Keep the tone friendly, fun, and Malaysian-casual (use light Manglish like "perfect lah", "confirm best", "sure nice one", "this one power", etc.).
5. Make the reasons feel personal and unique to each product — not generic.

IMPORTANT: Respond ONLY with a valid JSON array. No markdown, no code blocks, no explanations. Format exactly like this:
[
  { "id": 1, "reason": "Because this one confirm best for your gaming session, the RGB will make your setup look damn nice!" },
  { "id": 2, "reason": "This snack perfect for your movie marathon, can munch all night long!" }
]
EOT;

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])
            ->timeout(20)
            ->retry(2, 1000) // Retry twice with 1-second delay
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => 'gpt-4o-mini',
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a helpful Malaysian shopping assistant. Always respond with valid JSON only. Never include markdown formatting.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens'  => 500,
                'temperature' => 0.8,
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $content = $response->json('choices.0.message.content', '[]');
            
            // Clean up response - remove any markdown code blocks
            $content = trim($content);
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
            
            Log::info('OpenAI raw response', ['content' => $content]);

            $picks = json_decode($content, true);

            if (!is_array($picks)) {
                Log::error('Failed to parse OpenAI response as JSON', [
                    'raw_content' => $content,
                    'json_error'  => json_last_error_msg(),
                ]);
                return null;
            }

            // Validate and hydrate results
            return $this->hydrateResults($picks, $products);

        } catch (\Exception $e) {
            Log::error('OpenAI suggestion exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Simple keyword-based suggestion (used when OpenAI is unavailable).
     */
    private function simpleSuggest(string $vibe, $products): array
    {
        // Extended keyword-to-category mapping
        $vibeCategories = [
            'birthday'   => ['Toys', 'Electronics', 'Fashion', 'Food & Beverages'],
            'party'      => ['Food & Beverages', 'Toys', 'Fashion', 'Electronics'],
            'celebration'=> ['Food & Beverages', 'Toys', 'Electronics', 'Fashion'],
            'work'       => ['Electronics', 'Books', 'Fashion'],
            'office'     => ['Electronics', 'Books', 'Fashion'],
            'fitness'    => ['Sports', 'Health & Beauty', 'Food & Beverages'],
            'gym'        => ['Sports', 'Health & Beauty', 'Food & Beverages'],
            'exercise'   => ['Sports', 'Health & Beauty'],
            'beach'      => ['Fashion', 'Health & Beauty', 'Sports', 'Toys'],
            'vacation'   => ['Fashion', 'Health & Beauty', 'Electronics', 'Books'],
            'holiday'    => ['Fashion', 'Toys', 'Electronics', 'Food & Beverages'],
            'travel'     => ['Fashion', 'Health & Beauty', 'Electronics', 'Automotive'],
            'gaming'     => ['Electronics', 'Toys', 'Food & Beverages'],
            'game'       => ['Electronics', 'Toys'],
            'night'      => ['Electronics', 'Fashion', 'Food & Beverages', 'Toys'],
            'gift'       => ['Toys', 'Fashion', 'Electronics', 'Books', 'Health & Beauty'],
            'present'    => ['Toys', 'Fashion', 'Electronics', 'Books', 'Health & Beauty'],
            'partner'    => ['Fashion', 'Health & Beauty', 'Electronics', 'Food & Beverages'],
            'girlfriend' => ['Fashion', 'Health & Beauty', 'Food & Beverages'],
            'boyfriend'  => ['Electronics', 'Fashion', 'Food & Beverages'],
            'study'      => ['Books', 'Electronics', 'Food & Beverages'],
            'exam'       => ['Books', 'Electronics', 'Food & Beverages'],
            'student'    => ['Books', 'Electronics', 'Food & Beverages'],
            'cooking'    => ['Food & Beverages', 'Books'],
            'kitchen'    => ['Food & Beverages', 'Books'],
            'food'       => ['Food & Beverages'],
            'home'       => ['Food & Beverages', 'Books', 'Electronics'],
            'relax'      => ['Books', 'Food & Beverages', 'Health & Beauty'],
            'chill'      => ['Books', 'Food & Beverages', 'Health & Beauty'],
            'style'      => ['Fashion', 'Health & Beauty'],
            'beauty'     => ['Health & Beauty', 'Fashion'],
            'car'        => ['Automotive'],
            'drive'      => ['Automotive'],
            'kids'       => ['Toys', 'Books', 'Food & Beverages'],
            'children'   => ['Toys', 'Books', 'Food & Beverages'],
            'baby'       => ['Toys', 'Health & Beauty', 'Food & Beverages'],
            'pet'        => ['Toys', 'Food & Beverages'],
            'cat'        => ['Toys', 'Food & Beverages'],
            'dog'        => ['Toys', 'Sports'],
            'outdoor'    => ['Sports', 'Automotive', 'Fashion'],
            'camping'    => ['Sports', 'Automotive', 'Food & Beverages'],
            'hiking'     => ['Sports', 'Fashion', 'Food & Beverages'],
            'music'      => ['Electronics', 'Toys'],
            'movie'      => ['Electronics', 'Food & Beverages'],
            'photo'      => ['Electronics'],
            'camera'     => ['Electronics'],
            'tech'       => ['Electronics', 'Books'],
            'computer'   => ['Electronics', 'Books'],
            'phone'      => ['Electronics'],
        ];

        // Find matching categories
        $matchedCategories = [];
        $vibeWords = explode(' ', $vibe);
        
        // Try exact phrase matches first
        foreach ($vibeCategories as $keyword => $categories) {
            if (stripos($vibe, $keyword) !== false) {
                $matchedCategories = array_merge($matchedCategories, $categories);
            }
        }

        // If no matches, try individual word matching
        if (empty($matchedCategories)) {
            foreach ($vibeWords as $word) {
                if (strlen($word) >= 3) {
                    foreach ($vibeCategories as $keyword => $categories) {
                        if (stripos($keyword, $word) !== false || stripos($word, $keyword) !== false) {
                            $matchedCategories = array_merge($matchedCategories, $categories);
                        }
                    }
                }
            }
        }

        // If still no match, use top products by stock
        if (empty($matchedCategories)) {
            $picks = $products->sortByDesc('stock')->take(4);
            return $picks->values()->map(function ($product) {
                return [
                    'id'          => $product->id,
                    'name'        => $product->name,
                    'category'    => $product->category,
                    'price'       => $product->price,
                    'image'       => $product->image,
                    'stock'       => $product->stock,
                    'description' => $product->description,
                    'reason'      => 'This popular item might be just what you\'re looking for!',
                ];
            })->toArray();
        }

        // Remove duplicates
        $matchedCategories = array_unique($matchedCategories);

        // Filter products by matched categories
        $filtered = $products->filter(function ($product) use ($matchedCategories) {
            return in_array($product->category, $matchedCategories);
        });

        // If not enough, fill with random products
        if ($filtered->count() < 3) {
            $needed = 3 - $filtered->count();
            $remaining = $products->whereNotIn('id', $filtered->pluck('id'))
                ->shuffle()
                ->take($needed);
            $filtered = $filtered->merge($remaining);
        }

        // Take 3-4 products
        $picks = $filtered->shuffle()->take(rand(3, 4));

        // Fun Malaysian-style reasons
        $reasons = [
            'Perfect for your vibe — confirm best!',
            'This one sure fits your style, can\'t go wrong lah!',
            'Highly recommended for this occasion!',
            'A must-have for what you\'re looking for!',
            'You\'ll love this — guaranteed satisfaction!',
            'Top pick for your needs right now!',
            'This one power, sure nice for you!',
            'Confirm best choice for your mood!',
            'Sure ngam with what you want!',
        ];

        return $picks->values()->map(function ($product) use ($reasons) {
            return [
                'id'          => $product->id,
                'name'        => $product->name,
                'category'    => $product->category,
                'price'       => $product->price,
                'image'       => $product->image,
                'stock'       => $product->stock,
                'description' => $product->description,
                'reason'      => $reasons[array_rand($reasons)],
            ];
        })->toArray();
    }

    /**
     * Hydrate AI picks with full product data from database.
     */
    private function hydrateResults(array $picks, $products): array
    {
        $productMap = $products->keyBy('id');

        $results = collect($picks)->map(function ($pick) use ($productMap) {
            $productId = $pick['id'] ?? null;
            
            if (!$productId) {
                Log::warning('AI pick missing product ID', ['pick' => $pick]);
                return null;
            }

            $product = $productMap->get($productId);
            
            if (!$product) {
                Log::warning('AI suggested product not found in database', [
                    'suggested_id' => $productId,
                    'available_ids' => $productMap->keys()->toArray(),
                ]);
                return null;
            }

            return [
                'id'          => $product->id,
                'name'        => $product->name,
                'category'    => $product->category,
                'price'       => (float) $product->price,
                'image'       => $product->image,
                'stock'       => (int) $product->stock,
                'description' => $product->description,
                'reason'      => $pick['reason'] ?? 'Great pick for your vibe!',
            ];
        })->filter()->values();

        if ($results->isEmpty()) {
            Log::warning('No valid products found from AI suggestions, using fallback');
            // Return at least some products
            return $products->shuffle()->take(3)->map(function ($product) {
                return [
                    'id'          => $product->id,
                    'name'        => $product->name,
                    'category'    => $product->category,
                    'price'       => (float) $product->price,
                    'image'       => $product->image,
                    'stock'       => (int) $product->stock,
                    'description' => $product->description,
                    'reason'      => 'This might interest you!',
                ];
            })->toArray();
        }

        return $results->toArray();
    }
}