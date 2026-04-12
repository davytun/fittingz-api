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

        // Total revenue (all orders)
        $totalRevenue = $user->orders()->sum('total_amount');

        // Total payments received
        $totalPaid = $user->payments()->sum('amount');

        // Outstanding balance (pending payments)
        $outstandingBalance = $totalRevenue - $totalPaid;

        // Orders with pending payments (DB-level, no N+1)
        $ordersWithBalance = $user->orders()
            ->whereIn('status', ['pending_payment', 'in_progress'])
            ->whereRaw('total_amount > (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.order_id = orders.id)')
            ->count();

        // Revenue this month
        $revenueThisMonth = $user->orders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        // Payments this month
        $paymentsThisMonth = $user->payments()
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

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
                    'total' => (float) $totalRevenue,
                    'total_paid' => (float) $totalPaid,
                    'outstanding_balance' => (float) $outstandingBalance,
                    'this_month' => (float) $revenueThisMonth,
                ],
                'payments' => [
                    'total_received' => (float) $totalPaid,
                    'this_month' => (float) $paymentsThisMonth,
                    'orders_with_balance' => $ordersWithBalance,
                ],
            ]
        );
    }

    public function recentOrders(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 10), 50);

        $orders = $request->user()->orders()
            ->with(['client', 'measurement'])
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
            // Monthly breakdown for current year
            $data = collect(range(1, 12))->map(function ($month) use ($user) {
                $revenue = $user->orders()
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount');

                $payments = $user->payments()
                    ->whereMonth('payment_date', $month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount');

                return [
                    'month' => now()->month($month)->format('M'),
                    'revenue' => (float) $revenue,
                    'payments' => (float) $payments,
                ];
            });
        } else {
            // Daily breakdown for current month
            $daysInMonth = now()->daysInMonth;
            $data = collect(range(1, $daysInMonth))->map(function ($day) use ($user) {
                $date = now()->day($day);

                $revenue = $user->orders()
                    ->whereDate('created_at', $date)
                    ->sum('total_amount');

                $payments = $user->payments()
                    ->whereDate('payment_date', $date)
                    ->sum('amount');

                return [
                    'day' => $day,
                    'date' => $date->format('Y-m-d'),
                    'revenue' => (float) $revenue,
                    'payments' => (float) $payments,
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
        $limit = min((int) $request->input('limit', 10), 50);

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