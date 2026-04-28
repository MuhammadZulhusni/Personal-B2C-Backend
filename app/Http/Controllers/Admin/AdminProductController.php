<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    /**
     * Display all products (no pagination for admin).
     */
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'data' => $products
        ]);
    }

    /**
     * Store a new product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'image'       => 'nullable|string',
        ]);

        $product = Product::create($request->all());

        return response()->json([
            'data'    => $product,
            'message' => 'Product created successfully'
        ], 201);
    }

    /**
     * Display a single product.
     */
    public function show(Product $product)
    {
        return response()->json(['data' => $product]);
    }

    /**
     * Update a product.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'image'       => 'nullable|string',
        ]);

        $product->update($request->all());

        return response()->json([
            'data'    => $product,
            'message' => 'Product updated successfully'
        ]);
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}