<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 12);
        
        $products = Product::orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
        
        return response()->json([
            'data' => $product  // Wrap in 'data' for consistent response
        ]);
    }
}