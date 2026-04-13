<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StyleImage\StoreStyleImageRequest;
use App\Http\Requests\StyleImage\UpdateStyleImageRequest;
use App\Http\Resources\StyleImageResource;
use App\Models\Client;
use App\Models\StyleImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientStyleController extends Controller
{
    /**
     * Upload style images for a specific client.
     * POST /api/v1/clients/{clientId}/styles/upload
     */
    public function upload(StoreStyleImageRequest $request, string $clientId): JsonResponse
    {
        $client = Client::find($clientId);

        if (!$client) {
            return ApiResponse::error('Client not found', null, 404);
        }

        if ($client->user_id !== $request->user()->id) {
            return ApiResponse::error('Forbidden', null, 403);
        }

        $uploaded = [];

        foreach ($request->file('images') as $file) {
            $publicId = 'styles/' . Str::random(40);
            $path = $file->storeAs('', $publicId, 'public');
            $imageUrl = Storage::disk('public')->url($path);

            $uploaded[] = StyleImage::create([
                'admin_id'    => $request->user()->id,
                'client_id'   => $client->id,
                'image_url'   => $imageUrl,
                'public_id'   => $publicId,
                'category'    => $request->category,
                'description' => $request->description,
            ]);
        }

        return response()->json(
            StyleImageResource::collection($uploaded),
            201
        );
    }

    /**
     * Get style images for a specific client.
     * GET /api/v1/clients/{clientId}/styles
     */
    public function index(Request $request, string $clientId): JsonResponse
    {
        $client = Client::find($clientId);

        if (!$client) {
            return ApiResponse::error('Client not found', null, 404);
        }

        if ($client->user_id !== $request->user()->id) {
            return ApiResponse::error('Forbidden', null, 403);
        }

        $page     = max(1, (int) $request->query('page', 1));
        $pageSize = max(1, (int) $request->query('pageSize', 10));

        $paginator = StyleImage::where('client_id', $client->id)
            ->latest()
            ->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            'data'       => StyleImageResource::collection($paginator->items()),
            'pagination' => [
                'page'       => $paginator->currentPage(),
                'pageSize'   => $paginator->perPage(),
                'total'      => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Get a single style image for a client.
     * GET /api/v1/clients/{clientId}/styles/{imageId}
     */
    public function show(Request $request, string $clientId, string $imageId): JsonResponse
    {
        $client = Client::find($clientId);

        if (!$client) {
            return ApiResponse::error('Client not found', null, 404);
        }

        if ($client->user_id !== $request->user()->id) {
            return ApiResponse::error('Forbidden', null, 403);
        }

        $image = StyleImage::where('client_id', $client->id)->find($imageId);

        if (!$image) {
            return ApiResponse::error('Style image not found', null, 404);
        }

        return response()->json(new StyleImageResource($image));
    }

    /**
     * Update a style image.
     * PATCH /api/v1/clients/{clientId}/styles/{imageId}
     */
    public function update(UpdateStyleImageRequest $request, string $clientId, string $imageId): JsonResponse
    {
        $client = Client::find($clientId);

        if (!$client) {
            return ApiResponse::error('Client not found', null, 404);
        }

        if ($client->user_id !== $request->user()->id) {
            return ApiResponse::error('Forbidden', null, 403);
        }

        $image = StyleImage::where('client_id', $client->id)
            ->where('admin_id', $request->user()->id)
            ->find($imageId);

        if (!$image) {
            return ApiResponse::error('Style image not found', null, 404);
        }

        $image->update($request->validated());

        return response()->json(new StyleImageResource($image->fresh()));
    }

    /**
     * Delete a style image.
     * DELETE /api/v1/clients/{clientId}/styles/{imageId}
     */
    public function destroy(Request $request, string $clientId, string $imageId): JsonResponse
    {
        $client = Client::find($clientId);

        if (!$client) {
            return ApiResponse::error('Style image not found or already deleted', null, 404);
        }

        $image = StyleImage::where('client_id', $client->id)
            ->where('admin_id', $request->user()->id)
            ->find($imageId);

        if (!$image) {
            return ApiResponse::error('Style image not found or already deleted', null, 404);
        }

        if ($image->admin_id !== $request->user()->id) {
            return ApiResponse::error('Forbidden', null, 403);
        }

        $image->delete();

        return response()->json(['message' => 'Style image deleted successfully']);
    }
}
