<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Get available payment methods
     */
    public function getPaymentMethods()
    {
        return response()->json([
            'methods' => [
                [
                    'id' => 'card',
                    'name' => 'Credit/Debit Card',
                    'icon' => 'credit-card',
                    'description' => 'Pay securely with your card',
                    'processing_time' => 'Instant',
                ],
                [
                    'id' => 'bank_transfer',
                    'name' => 'Bank Transfer',
                    'icon' => 'bank',
                    'description' => 'Transfer directly to our bank account',
                    'processing_time' => '1-2 business days',
                    'bank_details' => [
                        'bank_name' => 'Dummy Bank',
                        'account_number' => '1234-5678-9012-3456',
                        'account_name' => 'Mini Shop Sdn Bhd',
                    ]
                ],
                [
                    'id' => 'cod',
                    'name' => 'Cash on Delivery',
                    'icon' => 'cash',
                    'description' => 'Pay when you receive your order',
                    'processing_time' => 'On delivery',
                    'extra_fee' => 5.00,
                ],
            ]
        ]);
    }

    /**
     * Process dummy payment
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:card,bank_transfer,cod',
        ]);

        try {
            $order = Order::findOrFail($request->order_id);
            
            // Ensure user owns this order
            if ($order->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Check if order is already paid
            if ($order->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already paid'
                ], 400);
            }

            // Generate a fake transaction ID
            $prefix = [
                'card' => 'CC',
                'bank_transfer' => 'BT',
                'cod' => 'COD',
            ][$request->payment_method] ?? 'PAY';
            
            $transactionId = $prefix . '-' . strtoupper(uniqid()) . '-' . date('Ymd');
            
            // Simulate processing delay for realism
            sleep(1);
            
            // Update order with payment info
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => $request->payment_method,
                'transaction_id' => $transactionId,
                'paid_at' => now(),
                'status' => 'processing',
            ]);

            Log::info('Payment processed successfully', [
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'method' => $request->payment_method,
                'transaction_id' => $transactionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction_id' => $transactionId,
                'order' => $order->load('items.product'),
            ]);

        } catch (\Exception $e) {
            Log::error('Payment processing failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Ensure user owns this order
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'order_id' => $order->id,
            'status' => $order->payment_status,
            'is_paid' => $order->payment_status === 'paid',
            'transaction_id' => $order->transaction_id,
            'paid_at' => $order->paid_at,
        ]);
    }
}