<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Requests\Order\UpdateOrderMeasurementRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->orders()->with(['client', 'measurement']);

        // Filter by client
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('order_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->latest()->paginate(15);

        return ApiResponse::paginated(
            'Orders retrieved successfully',
            $orders->setCollection(
                $orders->getCollection()->map(fn($order) => new OrderResource($order))
            )
        );
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $request->user()->orders()->create([
            'client_id' => $request->client_id,
            'measurement_id' => $request->measurement_id,
            'title' => $request->title,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'total_amount' => $request->total_amount,
            'status' => $request->status ?? 'pending',
            'due_date' => $request->due_date,
            'delivery_date' => $request->delivery_date,
            'notes' => $request->notes,
        ]);

        $order->load(['client', 'measurement']);

        return ApiResponse::success(
            'Order created successfully',
            new OrderResource($order),
            201
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $order = $request->user()->orders()
            ->with(['client', 'measurement', 'payments', 'styles'])
            ->findOrFail($id);

        return ApiResponse::success(
            'Order retrieved successfully',
            new OrderResource($order)
        );
    }

    public function update(UpdateOrderRequest $request, string $id): JsonResponse
    {
        $order = $request->user()->orders()->findOrFail($id);

        $order->update($request->validated());

        $order->load(['client', 'measurement']);

        return ApiResponse::success(
            'Order updated successfully',
            new OrderResource($order)
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $order = $request->user()->orders()->findOrFail($id);

        $order->delete();

        return ApiResponse::success('Order deleted successfully');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, string $id): JsonResponse
    {
        $order = $request->user()->orders()->findOrFail($id);

        $order->update(['status' => $request->status]);

        $order->load(['client', 'measurement']);

        return ApiResponse::success(
            'Order status updated successfully',
            new OrderResource($order)
        );
    }

    public function updateMeasurement(UpdateOrderMeasurementRequest $request, string $id): JsonResponse
    {
        $order = $request->user()->orders()->findOrFail($id);

        // Verify measurement belongs to the same client
        $measurement = $request->user()->measurements()
            ->where('id', $request->measurement_id)
            ->where('client_id', $order->client_id)
            ->firstOrFail();

        $order->update(['measurement_id' => $measurement->id]);

        $order->load(['client', 'measurement']);

        return ApiResponse::success(
            'Order measurement updated successfully',
            new OrderResource($order)
        );
    }
}