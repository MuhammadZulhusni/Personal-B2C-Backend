<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ==================== ELECTRONICS ====================
            [
                'name'        => 'Wireless Headphones Pro',
                'description' => 'Premium wireless headphones with active noise cancellation, 30-hour battery life, and crystal-clear sound quality.',
                'price'       => 299.99,
                'stock'       => 45,
                'image'       => 'https://picsum.photos/seed/headphones/400/300',
                'category'    => 'Electronics',
            ],
            [
                'name'        => 'Mechanical Keyboard RGB',
                'description' => 'Full-size RGB mechanical keyboard with Cherry MX Blue switches and aluminum frame.',
                'price'       => 159.99,
                'stock'       => 35,
                'image'       => 'https://picsum.photos/seed/keyboard/400/300',
                'category'    => 'Electronics',
            ],
            [
                'name'        => 'Gaming Mouse Pro',
                'description' => 'Ultra-light gaming mouse with 16000 DPI optical sensor and programmable buttons.',
                'price'       => 89.99,
                'stock'       => 80,
                'image'       => 'https://picsum.photos/seed/gamingmouse/400/300',
                'category'    => 'Electronics',
            ],
            [
                'name'        => 'USB-C Hub 7-in-1',
                'description' => 'Compact USB-C hub with HDMI 4K, USB 3.0 ports, SD card reader, and PD charging.',
                'price'       => 49.99,
                'stock'       => 120,
                'image'       => 'https://picsum.photos/seed/usbhub/400/300',
                'category'    => 'Electronics',
            ],
            [
                'name'        => '4K Webcam Ultra',
                'description' => '4K webcam with auto-focus, built-in ring light, and noise-cancelling microphone.',
                'price'       => 129.99,
                'stock'       => 25,
                'image'       => 'https://picsum.photos/seed/webcam/400/300',
                'category'    => 'Electronics',
            ],
            [
                'name'        => 'Curved Monitor 27"',
                'description' => '27-inch curved gaming monitor with 165Hz refresh rate and 1ms response time.',
                'price'       => 449.99,
                'stock'       => 15,
                'image'       => 'https://picsum.photos/seed/monitor/400/300',
                'category'    => 'Electronics',
            ],
            [
                'name'        => 'Bluetooth Speaker',
                'description' => 'Portable waterproof Bluetooth speaker with 360° sound and 20-hour battery.',
                'price'       => 79.99,
                'stock'       => 60,
                'image'       => 'https://picsum.photos/seed/speaker/400/300',
                'category'    => 'Electronics',
            ],
            [
                'name'        => 'Smart Watch Series X',
                'description' => 'Advanced smartwatch with health monitoring, GPS, and 7-day battery life.',
                'price'       => 349.99,
                'stock'       => 30,
                'image'       => 'https://picsum.photos/seed/smartwatch/400/300',
                'category'    => 'Electronics',
            ],

            // ==================== FASHION ====================
            [
                'name'        => 'Classic Denim Jacket',
                'description' => 'Timeless denim jacket made from premium cotton with a comfortable relaxed fit.',
                'price'       => 129.99,
                'stock'       => 40,
                'image'       => 'https://picsum.photos/seed/denim/400/300',
                'category'    => 'Fashion',
            ],
            [
                'name'        => 'Leather Crossbody Bag',
                'description' => 'Genuine leather crossbody bag with multiple compartments and adjustable strap.',
                'price'       => 89.99,
                'stock'       => 25,
                'image'       => 'https://picsum.photos/seed/bag/400/300',
                'category'    => 'Fashion',
            ],
            [
                'name'        => 'Running Sneakers Ultra',
                'description' => 'Lightweight running shoes with responsive cushioning and breathable mesh upper.',
                'price'       => 159.99,
                'stock'       => 55,
                'image'       => 'https://picsum.photos/seed/sneakers/400/300',
                'category'    => 'Fashion',
            ],
            [
                'name'        => 'Polarized Sunglasses',
                'description' => 'UV400 polarized sunglasses with lightweight titanium frame.',
                'price'       => 69.99,
                'stock'       => 70,
                'image'       => 'https://picsum.photos/seed/sunglasses/400/300',
                'category'    => 'Fashion',
            ],
            [
                'name'        => 'Silk Scarf Collection',
                'description' => 'Elegant 100% silk scarf with hand-painted floral design.',
                'price'       => 45.99,
                'stock'       => 50,
                'image'       => 'https://picsum.photos/seed/scarf/400/300',
                'category'    => 'Fashion',
            ],
            [
                'name'        => 'Wool Blend Blazer',
                'description' => 'Slim-fit wool blend blazer perfect for business and formal occasions.',
                'price'       => 199.99,
                'stock'       => 20,
                'image'       => 'https://picsum.photos/seed/blazer/400/300',
                'category'    => 'Fashion',
            ],

            // ==================== SPORTS ====================
            [
                'name'        => 'Yoga Mat Premium',
                'description' => 'Extra thick eco-friendly yoga mat with non-slip surface and carrying strap.',
                'price'       => 39.99,
                'stock'       => 90,
                'image'       => 'https://picsum.photos/seed/yogamat/400/300',
                'category'    => 'Sports',
            ],
            [
                'name'        => 'Adjustable Dumbbells',
                'description' => 'Space-saving adjustable dumbbells from 5-25kg with quick-change mechanism.',
                'price'       => 249.99,
                'stock'       => 15,
                'image'       => 'https://picsum.photos/seed/dumbbells/400/300',
                'category'    => 'Sports',
            ],
            [
                'name'        => 'Resistance Bands Set',
                'description' => 'Complete set of 5 resistance bands with different tension levels and door anchor.',
                'price'       => 29.99,
                'stock'       => 100,
                'image'       => 'https://picsum.photos/seed/bands/400/300',
                'category'    => 'Sports',
            ],
            [
                'name'        => 'Basketball Pro',
                'description' => 'Official size indoor/outdoor basketball with superior grip and durability.',
                'price'       => 34.99,
                'stock'       => 45,
                'image'       => 'https://picsum.photos/seed/basketball/400/300',
                'category'    => 'Sports',
            ],
            [
                'name'        => 'Treadmill Compact',
                'description' => 'Foldable compact treadmill with LCD display, heart rate monitor, and 12 programs.',
                'price'       => 599.99,
                'stock'       => 8,
                'image'       => 'https://picsum.photos/seed/treadmill/400/300',
                'category'    => 'Sports',
            ],

            // ==================== BOOKS ====================
            [
                'name'        => 'The Art of Programming',
                'description' => 'Comprehensive guide to modern software development practices and design patterns.',
                'price'       => 49.99,
                'stock'       => 60,
                'image'       => 'https://picsum.photos/seed/progbook/400/300',
                'category'    => 'Books',
            ],
            [
                'name'        => 'World Atlas 2024',
                'description' => 'Complete world atlas with detailed maps, country profiles, and geographical data.',
                'price'       => 39.99,
                'stock'       => 30,
                'image'       => 'https://picsum.photos/seed/atlas/400/300',
                'category'    => 'Books',
            ],
            [
                'name'        => 'Business Strategy Guide',
                'description' => 'Bestselling guide to building successful business strategies for the modern market.',
                'price'       => 34.99,
                'stock'       => 45,
                'image'       => 'https://picsum.photos/seed/business/400/300',
                'category'    => 'Books',
            ],
            [
                'name'        => 'Recipe Collection',
                'description' => '500+ recipes from around the world with step-by-step instructions and photos.',
                'price'       => 29.99,
                'stock'       => 55,
                'image'       => 'https://picsum.photos/seed/cookbook/400/300',
                'category'    => 'Books',
            ],
            [
                'name'        => 'Science Fiction Collection',
                'description' => 'Award-winning sci-fi trilogy exploring artificial intelligence and space exploration.',
                'price'       => 44.99,
                'stock'       => 35,
                'image'       => 'https://picsum.photos/seed/scifi/400/300',
                'category'    => 'Books',
            ],

            // ==================== TOYS ====================
            [
                'name'        => 'Building Blocks Set',
                'description' => '1000-piece creative building blocks set compatible with major brands.',
                'price'       => 49.99,
                'stock'       => 40,
                'image'       => 'https://picsum.photos/seed/blocks/400/300',
                'category'    => 'Toys',
            ],
            [
                'name'        => 'Remote Control Car',
                'description' => 'High-speed RC car with 4WD, rechargeable battery, and 50m range.',
                'price'       => 59.99,
                'stock'       => 25,
                'image'       => 'https://picsum.photos/seed/rccar/400/300',
                'category'    => 'Toys',
            ],
            [
                'name'        => 'Board Game Collection',
                'description' => 'Family board game set with 10 classic games including chess and monopoly.',
                'price'       => 39.99,
                'stock'       => 30,
                'image'       => 'https://picsum.photos/seed/boardgame/400/300',
                'category'    => 'Toys',
            ],
            [
                'name'        => 'Educational Robot Kit',
                'description' => 'STEM learning robot kit for kids with programmable features and sensors.',
                'price'       => 79.99,
                'stock'       => 20,
                'image'       => 'https://picsum.photos/seed/robot/400/300',
                'category'    => 'Toys',
            ],
            [
                'name'        => 'Plush Teddy Bear',
                'description' => 'Giant 120cm soft plush teddy bear made from hypoallergenic materials.',
                'price'       => 34.99,
                'stock'       => 50,
                'image'       => 'https://picsum.photos/seed/teddy/400/300',
                'category'    => 'Toys',
            ],

            // ==================== FOOD & BEVERAGES ====================
            [
                'name'        => 'Premium Coffee Beans',
                'description' => 'Single-origin Arabica coffee beans from Ethiopia, medium roast, 500g pack.',
                'price'       => 24.99,
                'stock'       => 80,
                'image'       => 'https://picsum.photos/seed/coffee/400/300',
                'category'    => 'Food & Beverages',
            ],
            [
                'name'        => 'Organic Green Tea Set',
                'description' => 'Premium Japanese green tea collection with 6 varieties in elegant gift box.',
                'price'       => 34.99,
                'stock'       => 40,
                'image'       => 'https://picsum.photos/seed/tea/400/300',
                'category'    => 'Food & Beverages',
            ],
            [
                'name'        => 'Dark Chocolate Collection',
                'description' => 'Assorted Belgian dark chocolates with cocoa content from 55% to 85%.',
                'price'       => 29.99,
                'stock'       => 35,
                'image'       => 'https://picsum.photos/seed/chocolate/400/300',
                'category'    => 'Food & Beverages',
            ],
            [
                'name'        => 'Mixed Nuts Deluxe',
                'description' => 'Premium roasted mixed nuts including almonds, cashews, and macadamias, 1kg.',
                'price'       => 39.99,
                'stock'       => 55,
                'image'       => 'https://picsum.photos/seed/nuts/400/300',
                'category'    => 'Food & Beverages',
            ],
            [
                'name'        => 'Protein Bar Variety Pack',
                'description' => '12-pack assorted protein bars with 20g protein, low sugar, gluten-free.',
                'price'       => 34.99,
                'stock'       => 65,
                'image'       => 'https://picsum.photos/seed/proteinbar/400/300',
                'category'    => 'Food & Beverages',
            ],

            // ==================== HEALTH & BEAUTY ====================
            [
                'name'        => 'Vitamin C Serum',
                'description' => 'Advanced Vitamin C serum with hyaluronic acid for bright and youthful skin.',
                'price'       => 34.99,
                'stock'       => 70,
                'image'       => 'https://picsum.photos/seed/serum/400/300',
                'category'    => 'Health & Beauty',
            ],
            [
                'name'        => 'Electric Toothbrush Pro',
                'description' => 'Sonic electric toothbrush with 5 cleaning modes, UV sanitizer, and travel case.',
                'price'       => 79.99,
                'stock'       => 30,
                'image'       => 'https://picsum.photos/seed/toothbrush/400/300',
                'category'    => 'Health & Beauty',
            ],
            [
                'name'        => 'Essential Oils Kit',
                'description' => 'Complete aromatherapy set with 10 pure essential oils and diffuser.',
                'price'       => 49.99,
                'stock'       => 25,
                'image'       => 'https://picsum.photos/seed/oils/400/300',
                'category'    => 'Health & Beauty',
            ],
            [
                'name'        => 'Hair Dryer Professional',
                'description' => 'Ionic hair dryer with multiple heat settings, concentrator, and diffuser.',
                'price'       => 89.99,
                'stock'       => 20,
                'image'       => 'https://picsum.photos/seed/hairdryer/400/300',
                'category'    => 'Health & Beauty',
            ],
            [
                'name'        => 'Sunscreen SPF 50',
                'description' => 'Broad spectrum SPF 50 sunscreen, water-resistant, non-greasy formula.',
                'price'       => 24.99,
                'stock'       => 90,
                'image'       => 'https://picsum.photos/seed/sunscreen/400/300',
                'category'    => 'Health & Beauty',
            ],

            // ==================== AUTOMOTIVE ====================
            [
                'name'        => 'Dash Cam 4K',
                'description' => '4K resolution dash camera with night vision, GPS, and parking mode.',
                'price'       => 129.99,
                'stock'       => 25,
                'image'       => 'https://picsum.photos/seed/dashcam/400/300',
                'category'    => 'Automotive',
            ],
            [
                'name'        => 'Car Phone Holder',
                'description' => 'Universal dashboard magnetic phone mount with strong suction cup.',
                'price'       => 19.99,
                'stock'       => 100,
                'image'       => 'https://picsum.photos/seed/phoneholder/400/300',
                'category'    => 'Automotive',
            ],
            [
                'name'        => 'Tire Inflator Portable',
                'description' => 'Digital portable tire inflator with auto shut-off and LED light.',
                'price'       => 39.99,
                'stock'       => 35,
                'image'       => 'https://picsum.photos/seed/inflator/400/300',
                'category'    => 'Automotive',
            ],
            [
                'name'        => 'Car Vacuum Cleaner',
                'description' => 'High-power handheld car vacuum with HEPA filter and multiple attachments.',
                'price'       => 49.99,
                'stock'       => 20,
                'image'       => 'https://picsum.photos/seed/carvacuum/400/300',
                'category'    => 'Automotive',
            ],
            [
                'name'        => 'Jump Starter Power Bank',
                'description' => '2000A peak portable jump starter with USB power bank and emergency LED.',
                'price'       => 89.99,
                'stock'       => 15,
                'image'       => 'https://picsum.photos/seed/jumpstarter/400/300',
                'category'    => 'Automotive',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}