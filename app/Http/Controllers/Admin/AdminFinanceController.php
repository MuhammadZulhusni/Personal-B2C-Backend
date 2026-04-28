<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFinanceController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'this_month');

        return response()->json([
            'data' => [
                'summary'           => $this->getFinancialSummary($period),
                'monthly_revenue'   => $this->getMonthlyRevenue(),
                'expense_breakdown' => $this->getExpenseBreakdown($period),
                'transactions'      => $this->getRecentTransactions(),
            ]
        ]);
    }

    private function getFinancialSummary(string $period): array
    {
        $startDate = match ($period) {
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            'this_year'  => now()->startOfYear(),
            default      => now()->startOfMonth(),
        };

        $previousStartDate = match ($period) {
            'this_month' => now()->subMonth()->startOfMonth(),
            'last_month' => now()->subMonths(2)->startOfMonth(),
            'this_year'  => now()->subYear()->startOfYear(),
            default      => now()->subMonth()->startOfMonth(),
        };

        $totalRevenue = Order::where('created_at', '>=', $startDate)
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->sum('total_amount') ?? 0;

        $previousRevenue = Order::whereBetween('created_at', [$previousStartDate, $startDate])
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->sum('total_amount') ?? 0;

        $expenseRate = 0.30;
        $totalExpenses = round($totalRevenue * $expenseRate, 2);
        $previousExpenses = round($previousRevenue * $expenseRate, 2);
        $netProfit = round($totalRevenue - $totalExpenses, 2);
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        $pendingPayments = Order::where('payment_status', 'pending')
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount') ?? 0;
        $pendingCount = Order::where('payment_status', 'pending')
            ->where('status', '!=', 'cancelled')
            ->count();

        $revenueGrowth = $previousRevenue > 0 ? round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 1) : 0;
        $expenseChange = $previousExpenses > 0 ? round((($totalExpenses - $previousExpenses) / $previousExpenses) * 100, 1) : 0;

        return [
            'total_revenue'    => (float) $totalRevenue,
            'revenue_growth'   => $revenueGrowth,
            'total_expenses'   => (float) $totalExpenses,
            'expense_change'   => $expenseChange,
            'net_profit'       => (float) $netProfit,
            'profit_margin'    => $profitMargin,
            'pending_payments' => (float) $pendingPayments,
            'pending_count'    => $pendingCount,
        ];
    }

    private function getMonthlyRevenue(): array
    {
        $currentYear = now()->year;

        $monthlyData = Order::whereYear('created_at', $currentYear)
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $result = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthData = $monthlyData->firstWhere('month', $month);
            $previousMonthData = $monthlyData->firstWhere('month', $month - 1);

            $revenue = $monthData ? (float) $monthData->revenue : 0;
            $previousRevenue = $previousMonthData ? (float) $previousMonthData->revenue : 0;
            $growth = $previousRevenue > 0 ? round((($revenue - $previousRevenue) / $previousRevenue) * 100, 1) : 0;

            $result[] = [
                'name'    => date('M', mktime(0, 0, 0, $month, 1)),
                'revenue' => $revenue,
                'orders'  => $monthData ? (int) $monthData->orders : 0,
                'growth'  => $growth,
            ];
        }

        return $result;
    }

    private function getExpenseBreakdown(string $period): array
    {
        $startDate = match ($period) {
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            'this_year'  => now()->startOfYear(),
            default      => now()->startOfMonth(),
        };

        $totalRevenue = Order::where('created_at', '>=', $startDate)
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->sum('total_amount') ?? 0;

        $totalExpenses = $totalRevenue * 0.30;

        return [
            ['name' => 'Inventory & Stock',     'amount' => round($totalExpenses * 0.42, 2), 'color' => 'bg-blue-500'],
            ['name' => 'Marketing & Ads',       'amount' => round($totalExpenses * 0.22, 2), 'color' => 'bg-purple-500'],
            ['name' => 'Shipping & Logistics',  'amount' => round($totalExpenses * 0.15, 2), 'color' => 'bg-green-500'],
            ['name' => 'Platform & Hosting',    'amount' => round($totalExpenses * 0.10, 2), 'color' => 'bg-amber-500'],
            ['name' => 'Staff & Operations',    'amount' => round($totalExpenses * 0.07, 2), 'color' => 'bg-red-500'],
            ['name' => 'Other Expenses',        'amount' => round($totalExpenses * 0.04, 2), 'color' => 'bg-gray-500'],
        ];
    }

    private function getRecentTransactions(): array
    {
        return Order::with('user')
            ->whereNotNull('transaction_id')
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id'              => $order->id,
                    'transaction_id'  => $order->transaction_id,
                    'order_number'    => "#{$order->id}",
                    'customer'        => $order->user?->name ?? 'Guest',
                    'amount'          => (float) $order->total_amount,
                    'status'          => $order->payment_status === 'paid' ? 'Completed' : ucfirst($order->payment_status),
                    'date'            => $order->paid_at?->format('d M Y') ?? $order->created_at->format('d M Y'),
                ];
            })
            ->toArray();
    }
}