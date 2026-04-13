<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StyleImageResource;
use App\Models\Client;
use App\Models\Order;
use App\Models\StyleImage;
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
            'style_image_id' => [
                'required',
                'string',
                Rule::exists('style_images', 'id')->where('admin_id', $request->user()->id),
            ],
        ], [
            'style_image_id.required' => 'Style image ID is required',
            'style_image_id.exists'   => 'Style image not found',
        ]);

        $order->styleImages()->syncWithoutDetaching([$validated['style_image_id']]);
        $styleImage = StyleImage::find($validated['style_image_id']);

        return ApiResponse::success(
            'Style linked to order successfully',
            new StyleImageResource($styleImage),
            201
        );
    }

    public function detach(Request $request, Client $client, Order $order, string $styleImage): JsonResponse
    {
        if ($order->client_id !== $client->id) {
            abort(404);
        }

        $this->authorize('update', $order);

        if (!StyleImage::where('id', $styleImage)->where('admin_id', $request->user()->id)->exists()) {
            return ApiResponse::error('Style image not found', null, 404);
        }

        if (!$order->styleImages()->where('style_image_id', $styleImage)->exists()) {
            return ApiResponse::error('Style image is not linked to this order', null, 404);
        }

        $order->styleImages()->detach($styleImage);

        return ApiResponse::success('Style unlinked from order successfully');
    }
}
