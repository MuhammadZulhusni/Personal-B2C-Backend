<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        
        $orders = Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return response()->json(['data' => $order]);
    }

    /**
     * Update order status with automatic stock management.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $order->status;
            $newStatus = $request->status;

            // Handle stock changes
            $this->handleStockChanges($order, $oldStatus, $newStatus);

            // Update order status
            $order->update(['status' => $newStatus]);

            // Update payment status for completed orders
            if ($newStatus === 'completed') {
                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            DB::commit();

            $order->load(['user', 'items.product']);

            return response()->json([
                'message' => 'Order status updated successfully',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle stock changes based on order status transition.
     */
    private function handleStockChanges(Order $order, string $oldStatus, string $newStatus)
    {
        // Completed → Deduct stock (if not already deducted)
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $this->deductStock($order);
        }

        // Cancelled → Restore stock
        if ($newStatus === 'cancelled') {
            $this->restoreStock($order);
        }
    }

    /**
     * Deduct stock for each item in the order.
     */
    private function deductStock(Order $order)
    {
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            
            if ($product) {
                if ($product->stock < $item->quantity) {
                    throw new \Exception(
                        "Insufficient stock for product: {$product->name}"
                    );
                }
                
                $product->decrement('stock', $item->quantity);
            }
        }
    }

    /**
     * Restore stock for each item in the order.
     */
    private function restoreStock(Order $order)
    {
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            
            if ($product) {
                $product->increment('stock', $item->quantity);
            }
        }
    }
}