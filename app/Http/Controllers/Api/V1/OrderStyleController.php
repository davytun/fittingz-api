<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StyleResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderStyleController extends Controller
{
    public function index(Request $request, string $orderId): JsonResponse
    {
        $order = $request->user()->orders()->findOrFail($orderId);

        $styles = $order->styles;

        return ApiResponse::success(
            'Order styles retrieved successfully',
            StyleResource::collection($styles)
        );
    }

    public function attach(Request $request, string $orderId): JsonResponse
    {
        $validated = $request->validate([
            'style_id' => [
                'required',
                'uuid',
                Rule::exists('styles', 'id')->where('user_id', $request->user()->id),
            ],
        ], [
            'style_id.required' => 'Style ID is required',
            'style_id.uuid' => 'Invalid style ID format',
            'style_id.exists' => 'Style not found',
        ]);

        $order = $request->user()->orders()->findOrFail($orderId);

        // Check if already attached
        if ($order->styles()->where('style_id', $validated['style_id'])->exists()) {
            return ApiResponse::error(
                'Style is already linked to this order',
                null,
                400
            );
        }

        // Attach style
        $order->styles()->attach($validated['style_id']);

        $style = $request->user()->styles()->findOrFail($validated['style_id']);

        return ApiResponse::success(
            'Style linked to order successfully',
            new StyleResource($style),
            201
        );
    }

    public function detach(Request $request, string $orderId, string $styleId): JsonResponse
    {
        $order = $request->user()->orders()->findOrFail($orderId);

        // Verify style belongs to user
        $style = $request->user()->styles()->findOrFail($styleId);

        // Check if style is attached
        if (!$order->styles()->where('style_id', $styleId)->exists()) {
            return ApiResponse::error(
                'Style is not linked to this order',
                null,
                404
            );
        }

        // Detach style
        $order->styles()->detach($styleId);

        return ApiResponse::success('Style unlinked from order successfully');
    }
}