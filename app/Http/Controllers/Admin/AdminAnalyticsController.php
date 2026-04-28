<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
// use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminAnalyticsController extends Controller
{
    /**
     * Get all analytics data for the dashboard.
     */
    public function index(Request $request)
    {
        $days = (int) $request->input('days', 30);

        return response()->json([
            'data' => [
                'stats'                => $this->getStats($days),
                'sales_trend'          => $this->getSalesTrend($days),
                'top_products'         => $this->getTopProducts($days),
                'category_performance' => $this->getCategoryPerformance($days),
                'recent_activity'      => $this->getRecentActivity(),
            ]
        ]);
    }

    private function getStats(int $days): array
    {
        $startDate = now()->subDays($days);
        $previousStartDate = now()->subDays($days * 2);

        $totalOrders = Order::where('created_at', '>=', $startDate)->count();
        $previousOrders = Order::whereBetween('created_at', [$previousStartDate, $startDate])->count();

        $totalRevenue = Order::where('created_at', '>=', $startDate)
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->sum('total_amount');

        $previousRevenue = Order::whereBetween('created_at', [$previousStartDate, $startDate])
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->sum('total_amount');

        $paidOrders = Order::where('created_at', '>=', $startDate)
            ->where('payment_status', 'paid')
            ->count();
        $conversionRate = $totalOrders > 0 ? round(($paidOrders / $totalOrders) * 100, 1) : 0;

        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        $totalCustomers = Order::where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->count('user_id');

        $revenueGrowth = $previousRevenue > 0 ? round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 1) : 0;
        $orderGrowth = $previousOrders > 0 ? round((($totalOrders - $previousOrders) / $previousOrders) * 100, 1) : 0;

        return [
            'total_revenue'     => (float) $totalRevenue,
            'revenue_growth'    => $revenueGrowth,
            'total_orders'      => $totalOrders,
            'order_growth'      => $orderGrowth,
            'conversion_rate'   => $conversionRate,
            'avg_order_value'   => $avgOrderValue,
            'total_customers'   => $totalCustomers,
            'paid_orders'       => $paidOrders,
        ];
    }

    private function getSalesTrend(int $days): array
    {
        $startDate = now()->subDays($days);

        $salesData = Order::where('created_at', '>=', $startDate)
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $result = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayData = $salesData->firstWhere('date', $date);
            $result[] = [
                'date'  => now()->subDays($i)->format('M d'),
                'total' => $dayData ? (float) $dayData->total : 0,
                'count' => $dayData ? (int) $dayData->count : 0,
            ];
        }

        return $result;
    }

    private function getTopProducts(int $days): array
    {
        $startDate = now()->subDays($days);

        try {
            $products = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.created_at', '>=', $startDate)
                ->where(function ($q) {
                    $q->where('orders.payment_status', 'paid')
                      ->orWhere('orders.status', 'completed');
                })
                ->select(
                    'products.id',
                    'products.name',
                    'products.image',
                    'products.category',
                    DB::raw('SUM(order_items.quantity) as total_sold'),
                    DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
                )
                ->groupBy('products.id', 'products.name', 'products.image', 'products.category')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();

            return $products->map(function ($product) {
                return [
                    'id'       => $product->id,
                    'name'     => $product->name,
                    'image'    => $product->image ?: 'https://via.placeholder.com/40',
                    'category' => $product->category ?? 'Uncategorized',
                    'sold'     => (int) $product->total_sold,
                    'revenue'  => (float) $product->total_revenue,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Top products query failed: ' . $e->getMessage());
            return [];
        }
    }

    private function getCategoryPerformance(int $days): array
    {
        $startDate = now()->subDays($days);

        try {
            $categories = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.created_at', '>=', $startDate)
                ->where(function ($q) {
                    $q->where('orders.payment_status', 'paid')
                      ->orWhere('orders.status', 'completed');
                })
                ->select(
                    'products.category',
                    DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                    DB::raw('SUM(order_items.quantity) as total_items')
                )
                ->groupBy('products.category')
                ->orderByDesc('total_orders')
                ->get();

            $totalOrders = $categories->sum('total_orders');

            if ($categories->isEmpty()) {
                $allCategories = ['Electronics', 'Fashion', 'Sports', 'Books', 'Toys', 'Food & Beverages', 'Health & Beauty', 'Automotive'];
                return array_map(function ($cat) {
                    return [
                        'category'   => $cat,
                        'orders'     => 0,
                        'items'      => 0,
                        'percentage' => 0,
                    ];
                }, $allCategories);
            }

            return $categories->map(function ($cat) use ($totalOrders) {
                return [
                    'category'   => $cat->category ?? 'Uncategorized',
                    'orders'     => (int) $cat->total_orders,
                    'items'      => (int) $cat->total_items,
                    'percentage' => $totalOrders > 0 ? round(($cat->total_orders / $totalOrders) * 100, 1) : 0,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Category performance query failed: ' . $e->getMessage());
            return [];
        }
    }

    private function getRecentActivity(): array
    {
        try {
            $recentOrders = Order::with('user')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'type'   => 'order',
                        'user'   => $order->user?->name ?? 'Guest',
                        'action' => "placed a new order #{$order->id}",
                        'time'   => $order->created_at->diffForHumans(),
                        'id'     => $order->id,
                    ];
                });

            $recentPayments = Order::with('user')
                ->whereNotNull('paid_at')
                ->orderByDesc('paid_at')
                ->limit(3)
                ->get()
                ->map(function ($order) {
                    return [
                        'type'   => 'payment',
                        'user'   => $order->user?->name ?? 'Guest',
                        'action' => "completed payment of RM " . number_format($order->total_amount, 2),
                        'time'   => $order->paid_at->diffForHumans(),
                        'id'     => $order->id,
                    ];
                });

            return $recentOrders->merge($recentPayments)
                ->take(8)
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Recent activity query failed: ' . $e->getMessage());
            return [];
        }
    }
}