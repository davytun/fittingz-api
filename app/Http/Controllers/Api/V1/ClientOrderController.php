<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderMeasurementRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientOrderController extends Controller
{
    public function index(Request $request, Client $client): JsonResponse
    {
        $this->authorize('viewAny', [Order::class, $client]);

        $query = $client->orders()->with(['client', 'measurement']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('order_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

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
                $orders->getCollection()->map(fn ($order) => new OrderResource($order))
            )
        );
    }

    public function store(StoreOrderRequest $request, Client $client): JsonResponse
    {
        $this->authorize('create', [Order::class, $client]);

        $order = $client->orders()->create([
            'user_id' => $request->user()->id,
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

    public function show(Client $client, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['client', 'measurement', 'payments', 'styles']);

        return ApiResponse::success(
            'Order retrieved successfully',
            new OrderResource($order)
        );
    }

    public function update(UpdateOrderRequest $request, Client $client, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $order->update($request->validated());
        $order->load(['client', 'measurement']);

        return ApiResponse::success(
            'Order updated successfully',
            new OrderResource($order)
        );
    }

    public function destroy(Client $client, Order $order): JsonResponse
    {
        $this->authorize('delete', $order);

        $order->delete();

        return ApiResponse::success('Order deleted successfully');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Client $client, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $order->update(['status' => $request->status]);
        $order->load(['client', 'measurement']);

        return ApiResponse::success(
            'Order status updated successfully',
            new OrderResource($order)
        );
    }

    public function updateMeasurement(UpdateOrderMeasurementRequest $request, Client $client, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $order->update(['measurement_id' => $request->measurement_id]);
        $order->load(['client', 'measurement']);

        return ApiResponse::success(
            'Order measurement updated successfully',
            new OrderResource($order)
        );
    }
}
