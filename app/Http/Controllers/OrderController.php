<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Mail\NewOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, $id)
    {
        $order = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            // Check if user is admin and can view any order
            if ($request->user()->role === 'admin') {
                $order = Order::with('items.product')->find($id);
            }
            
            if (!$order) {
                return response()->json([
                    'message' => 'Order not found'
                ], 404);
            }
        }

        return response()->json($order);
    }

   public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'message' => "Insufficient stock for {$product->name}"
                    ], 400);
                }
                
                $totalAmount += $product->price * $item['quantity'];
            }

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'shipping_address' => $request->shipping_address,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Create order items and update stock
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
                
                $product->decrement('stock', $item['quantity']);
            }

            // Load relationships for email
            $order->load('items.product', 'user');

            // Send email notification to all admin users
            $this->notifyAdmins($order);

            Log::info('Order created', [
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'total' => $totalAmount
            ]);

            return response()->json($order->load('items.product'), 201);
            
        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create order. Please try again.'
            ], 500);
        }
    }

    /**
     * Send new order notification to all admin users
     */
    private function notifyAdmins(Order $order)
    {
        try {
            // Get all admin users
            $admins = User::where('role', 'admin')->get();
            
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new NewOrderNotification($order));
            }
            
            Log::info('Admin notifications sent for order #' . $order->id, [
                'admin_count' => $admins->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send admin notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Cancel an order.
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        // Only allow cancellation of pending orders
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be cancelled'
            ], 400);
        }

        // Restore stock
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Order cancelled successfully'
        ]);
    }
}