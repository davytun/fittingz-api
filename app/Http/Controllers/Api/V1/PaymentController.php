<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->payments()->with(['order.client']);

        // Filter by order_id
        if ($request->has('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        // Filter by client_id (via order relationship)
        if ($request->has('client_id')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->latest('payment_date')->paginate(15);

        return ApiResponse::paginated(
            'Payments retrieved successfully',
            $payments->setCollection(
                $payments->getCollection()->map(fn($payment) => new PaymentResource($payment))
            )
        );
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $order = $request->user()->orders()->findOrFail($request->order_id);

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

    public function show(Request $request, string $id): JsonResponse
    {
        $payment = $request->user()->payments()
            ->with(['order.client'])
            ->findOrFail($id);

        return ApiResponse::success(
            'Payment retrieved successfully',
            new PaymentResource($payment)
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $payment = $request->user()->payments()->findOrFail($id);

        $this->paymentService->deletePayment($payment);

        return ApiResponse::success('Payment deleted successfully');
    }
}