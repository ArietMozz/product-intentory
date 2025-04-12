<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware() {
        return [
            new Middleware('auth:sanctum', except: [])
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::with('categories')->get();
        return $products;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Returns if not an admin
        if ($request->user()->type !== 'admin') {
            return [
                'message' => 'You do not have access to this action.'
            ];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // $product = Product::create($validated);
        $product = $request->user()->products()->create($validated);

        return $product;
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('categories');
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Returns if not an admin
        if ($request->user()->type !== 'admin') {
            return [
                'message' => 'You do not have access to this action.'
            ];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return $product;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, Request $request)
    {
        // Returns if not an admin
        if ($request->user()->type !== 'admin') {
            return [
                'message' => 'You do not have access to this action.'
            ];
        }

        $product->delete();

        return ['mesage' => 'The product was deleted'];
    }

    public function addCategories(Request $request, Product $product)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);
        
        $product->categories()->syncWithoutDetaching($request->category_ids);
        
        return response()->json([
            'message' => 'Categories added successfully',
            'product' => $product->load('categories')
        ]);
    }

    public function removeCategory(Product $product, Category $category)
    {
        $product->categories()->detach($category->id);
        
        return response()->json([
            'message' => 'Category removed successfully',
            'product' => $product->load('categories')
        ]);
    }
}
