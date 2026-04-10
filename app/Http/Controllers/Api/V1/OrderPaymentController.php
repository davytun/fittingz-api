<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderPaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function index(Request $request, Client $client, Order $order): JsonResponse
    {
        if ($order->client_id !== $client->id) {
            abort(404);
        }

        $this->authorize('viewAny', [Payment::class, $order]);

        $query = $order->payments()->with(['order.client']);

        if ($request->has('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->latest('payment_date')->paginate(15);

        return ApiResponse::paginated(
            'Payments retrieved successfully',
            $payments->setCollection(
                $payments->getCollection()->map(fn ($payment) => new PaymentResource($payment))
            )
        );
    }

    public function store(StorePaymentRequest $request, Client $client, Order $order): JsonResponse
    {
        if ($order->client_id !== $client->id) {
            abort(404);
        }

        $this->authorize('create', [Payment::class, $order]);

        $payment = $this->paymentService->recordPayment($order, [
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'notes' => $request->notes,
        ]);

        $payment->load(['order.client']);

        return ApiResponse::success(
            'Payment recorded successfully',
            new PaymentResource($payment),
            201
        );
    }

    public function show(Client $client, Order $order, Payment $payment): JsonResponse
    {
        if ($order->client_id !== $client->id || $payment->order_id !== $order->id) {
            abort(404);
        }

        $this->authorize('view', $payment);

        $payment->load(['order.client']);

        return ApiResponse::success(
            'Payment retrieved successfully',
            new PaymentResource($payment)
        );
    }

    public function destroy(Client $client, Order $order, Payment $payment): JsonResponse
    {
        if ($order->client_id !== $client->id || $payment->order_id !== $order->id) {
            abort(404);
        }

        $this->authorize('delete', $payment);

        $this->paymentService->deletePayment($payment);

        return ApiResponse::success('Payment deleted successfully');
    }
}
