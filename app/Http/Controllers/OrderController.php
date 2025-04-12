<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class OrderController extends Controller implements HasMiddleware
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
        // Returns only user orders
        if ($request->user()->type !== 'admin') {
            return $request->user()->orders()->with('products')->get();
        }

        // Return all orders for admins
        return Order::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string'
        ]);

        $totalAmount = 0;
        $orderProducts = [];

        foreach ($validated['products'] as $item) {
            $product = Product::find($item['id']);
            $totalAmount += $product->price * $item['quantity'];
            $orderProducts[$product->id] = [
                'quantity' => $item['quantity'],
                'unit_price' => $product->price
            ];
        }

        $order = $request->user()->orders()->create([
            'status' => 'created',
            'total_amount' => $totalAmount,
            'shipping_address' => $validated['shipping_address'],
            'billing_address' => $validated['billing_address'] ?? $validated['shipping_address']
        ]);

        $order->products()->attach($orderProducts);

        return $order->load('products');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Order $order)
    {
        // Admin can view any order
        if ($request->user()->type === 'admin') {
            return $order->load('products');
        }

        if ($request->user()->id !== $order->user_id) {
            return response()->json([
                'message' => 'Unauthorized - You can only view your own orders'
            ], 403);
        }

        return $order->load('products');
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStatus(Request $request, Order $order)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['processing', 'paid', 'shipped', 'delivered', 'cancelled'])
            ]
        ]);

        $order->update(['status' => $validated['status']]);

        return $order;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
