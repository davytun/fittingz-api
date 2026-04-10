<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StyleResource;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientOrderStyleController extends Controller
{
    public function attach(Request $request, Client $client, Order $order): JsonResponse
    {
        if ($order->client_id !== $client->id) {
            abort(404);
        }

        $this->authorize('update', $order);

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

        $order->styles()->syncWithoutDetaching([$validated['style_id']]);
        $style = $request->user()->styles()->findOrFail($validated['style_id']);

        return ApiResponse::success(
            'Style linked to order successfully',
            new StyleResource($style),
            201
        );
    }

    public function detach(Request $request, Client $client, Order $order, string $style): JsonResponse
    {
        if ($order->client_id !== $client->id) {
            abort(404);
        }

        $this->authorize('update', $order);

        $request->user()->styles()->findOrFail($style);

        if (!$order->styles()->where('style_id', $style)->exists()) {
            return ApiResponse::error(
                'Style is not linked to this order.',
                null,
                404
            );
        }

        $order->styles()->detach($style);

        return ApiResponse::success('Style unlinked from order successfully');
    }
}
