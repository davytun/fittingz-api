<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Dashboard
 */
class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Total clients
        $totalClients = $user->clients()->count();

        // Total orders
        $totalOrders = $user->orders()->count();

        // Orders by status
        $ordersByStatus = $user->orders()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Revenue grouped by currency (avoids mixing incompatible currencies)
        $revenuePerCurrency = $user->orders()
            ->select('currency', DB::raw('SUM(total_amount) as total'))
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->map(fn ($v) => (float) $v);

        // Payments grouped by order currency (join through orders to get currency)
        $paidPerCurrency = $user->orders()
            ->select('orders.currency', DB::raw('COALESCE(SUM(payments.amount), 0) as total'))
            ->leftJoin('payments', 'payments.order_id', '=', 'orders.id')
            ->groupBy('orders.currency')
            ->pluck('total', 'currency')
            ->map(fn ($v) => (float) $v);

        // Outstanding per currency
        $outstandingPerCurrency = $revenuePerCurrency->map(
            fn ($rev, $currency) => round($rev - ($paidPerCurrency[$currency] ?? 0), 2)
        );

        // Orders with pending payments (DB-level, no N+1)
        $ordersWithBalance = $user->orders()
            ->whereIn('status', ['pending_payment', 'in_progress'])
            ->whereRaw('total_amount > (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.order_id = orders.id)')
            ->count();

        // Revenue this month grouped by currency
        $revenueThisMonth = $user->orders()
            ->select('currency', DB::raw('SUM(total_amount) as total'))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->map(fn ($v) => (float) $v);

        // Payments this month grouped by order currency
        $paymentsThisMonth = $user->orders()
            ->select('orders.currency', DB::raw('COALESCE(SUM(payments.amount), 0) as total'))
            ->leftJoin('payments', function ($join) {
                $join->on('payments.order_id', '=', 'orders.id')
                     ->whereMonth('payments.payment_date', now()->month)
                     ->whereYear('payments.payment_date', now()->year);
            })
            ->groupBy('orders.currency')
            ->pluck('total', 'currency')
            ->map(fn ($v) => (float) $v);

        // New clients this month
        $newClientsThisMonth = $user->clients()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return ApiResponse::success(
            'Dashboard stats retrieved successfully',
            [
                'clients' => [
                    'total' => $totalClients,
                    'new_this_month' => $newClientsThisMonth,
                ],
                'orders' => [
                    'total' => $totalOrders,
                    'pending' => $ordersByStatus['pending_payment'] ?? 0,
                    'pending_payment' => $ordersByStatus['pending_payment'] ?? 0,
                    'in_progress' => $ordersByStatus['in_progress'] ?? 0,
                    'completed' => $ordersByStatus['completed'] ?? 0,
                    'delivered' => $ordersByStatus['delivered'] ?? 0,
                    'cancelled' => $ordersByStatus['cancelled'] ?? 0,
                ],
                'revenue' => [
                    'by_currency'         => $revenuePerCurrency,
                    'paid_by_currency'    => $paidPerCurrency,
                    'outstanding_by_currency' => $outstandingPerCurrency,
                    'this_month_by_currency'  => $revenueThisMonth,
                ],
                'payments' => [
                    'this_month_by_currency' => $paymentsThisMonth,
                    'orders_with_balance'    => $ordersWithBalance,
                ],
            ]
        );
    }

    public function recentOrders(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->input('limit', 10), 50));

        $orders = $request->user()->orders()
            ->with(['client', 'measurement'])
            ->withSum('payments', 'amount')
            ->latest()
            ->limit($limit)
            ->get();

        return ApiResponse::success(
            'Recent orders retrieved successfully',
            [
                'orders' => OrderResource::collection($orders),
                'total' => $orders->count(),
            ]
        );
    }

    public function pendingPayments(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with(['client', 'measurement'])
            ->withSum('payments', 'amount')
            ->whereRaw('total_amount > (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.order_id = orders.id)')
            ->orderByRaw('(total_amount - (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.order_id = orders.id)) DESC')
            ->get();

        $totalOutstanding = $orders->sum(fn($order) => $order->total_amount - ($order->payments_sum_amount ?? 0));

        return ApiResponse::success(
            'Pending payments retrieved successfully',
            [
                'orders' => OrderResource::collection($orders),
                'total_orders' => $orders->count(),
                'total_outstanding' => (float) $totalOutstanding,
            ]
        );
    }

    public function upcomingDeliveries(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 7); // Next 7 days by default

        $orders = $request->user()->orders()
            ->with(['client', 'measurement'])
            ->withSum('payments', 'amount')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', now())
            ->whereDate('due_date', '<=', now()->addDays($days))
            ->whereIn('status', ['pending_payment', 'in_progress'])
            ->orderBy('due_date', 'asc')
            ->get();

        return ApiResponse::success(
            'Upcoming deliveries retrieved successfully',
            [
                'orders' => OrderResource::collection($orders),
                'total' => $orders->count(),
                'period' => "{$days} days",
            ]
        );
    }

    public function overdueOrders(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with(['client', 'measurement'])
            ->withSum('payments', 'amount')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->whereIn('status', ['pending_payment', 'in_progress'])
            ->orderBy('due_date', 'asc')
            ->get();

        return ApiResponse::success(
            'Overdue orders retrieved successfully',
            [
                'orders' => OrderResource::collection($orders),
                'total' => $orders->count(),
            ]
        );
    }

    public function revenueAnalytics(Request $request): JsonResponse
    {
        $period = $request->input('period', 'month'); // month, year
        $user = $request->user();

        if ($period === 'year') {
            // Monthly breakdown for current year, grouped by currency
            $data = collect(range(1, 12))->map(function ($month) use ($user) {
                $revenue = $user->orders()
                    ->select('currency', DB::raw('SUM(total_amount) as total'))
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', now()->year)
                    ->groupBy('currency')
                    ->pluck('total', 'currency')
                    ->map(fn ($v) => (float) $v);

                $payments = $user->orders()
                    ->select('orders.currency', DB::raw('COALESCE(SUM(payments.amount), 0) as total'))
                    ->leftJoin('payments', function ($join) use ($month) {
                        $join->on('payments.order_id', '=', 'orders.id')
                             ->whereMonth('payments.payment_date', $month)
                             ->whereYear('payments.payment_date', now()->year);
                    })
                    ->whereMonth('orders.created_at', $month)
                    ->whereYear('orders.created_at', now()->year)
                    ->groupBy('orders.currency')
                    ->pluck('total', 'currency')
                    ->map(fn ($v) => (float) $v);

                return [
                    'month'    => now()->startOfYear()->addMonths($month - 1)->format('M'),
                    'revenue'  => $revenue,
                    'payments' => $payments,
                ];
            });
        } else {
            // Daily breakdown for current month, grouped by currency
            $daysInMonth = now()->daysInMonth;
            $data = collect(range(1, $daysInMonth))->map(function ($day) use ($user) {
                $date = now()->day($day);

                $revenue = $user->orders()
                    ->select('currency', DB::raw('SUM(total_amount) as total'))
                    ->whereDate('created_at', $date)
                    ->groupBy('currency')
                    ->pluck('total', 'currency')
                    ->map(fn ($v) => (float) $v);

                $payments = $user->orders()
                    ->select('orders.currency', DB::raw('COALESCE(SUM(payments.amount), 0) as total'))
                    ->leftJoin('payments', function ($join) use ($date) {
                        $join->on('payments.order_id', '=', 'orders.id')
                             ->whereDate('payments.payment_date', $date);
                    })
                    ->whereDate('orders.created_at', $date)
                    ->groupBy('orders.currency')
                    ->pluck('total', 'currency')
                    ->map(fn ($v) => (float) $v);

                return [
                    'day'      => $day,
                    'date'     => $date->format('Y-m-d'),
                    'revenue'  => $revenue,
                    'payments' => $payments,
                ];
            });
        }

        return ApiResponse::success(
            'Revenue analytics retrieved successfully',
            [
                'period' => $period,
                'data' => $data,
            ]
        );
    }

    public function topClients(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->input('limit', 10), 50));

        $clients = $request->user()->clients()
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->having('orders_count', '>', 0)
            ->orderByDesc('orders_sum_total_amount')
            ->limit($limit)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'total_orders' => $client->orders_count,
                    'total_spent' => (float) $client->orders_sum_total_amount,
                ];
            });

        return ApiResponse::success(
            'Top clients retrieved successfully',
            [
                'clients' => $clients,
                'total' => $clients->count(),
            ]
        );
    }
}