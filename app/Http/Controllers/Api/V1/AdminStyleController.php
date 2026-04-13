<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StyleImage\StoreStyleImageRequest;
use App\Http\Resources\StyleImageResource;
use App\Models\StyleImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminStyleController extends Controller
{
    /**
     * Upload style images for the admin (not tied to any client).
     * POST /api/v1/admin/styles/upload
     */
    public function upload(StoreStyleImageRequest $request): JsonResponse
    {
        $uploaded = [];

        foreach ($request->file('images') as $file) {
            $publicId = 'styles/' . Str::random(40);
            $path = $file->storeAs('', $publicId, 'public');
            $imageUrl = Storage::disk('public')->url($path);

            $uploaded[] = StyleImage::create([
                'admin_id'    => $request->user()->id,
                'client_id'   => null,
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
     * Get all style images for the authenticated admin.
     * GET /api/v1/admin/styles
     */
    public function index(Request $request): JsonResponse
    {
        $page     = max(1, (int) $request->query('page', 1));
        $pageSize = max(1, (int) $request->query('pageSize', 10));

        $paginator = StyleImage::with('client:id,name')
            ->where('admin_id', $request->user()->id)
            ->latest()
            ->paginate($pageSize, ['*'], 'page', $page);

        $data = collect($paginator->items())->map(function (StyleImage $image) {
            return array_merge((new StyleImageResource($image))->resolve(), [
                'client' => $image->client ? [
                    'id'   => $image->client->id,
                    'name' => $image->client->name,
                ] : null,
            ]);
        });

        return response()->json([
            'data'       => $data,
            'pagination' => [
                'page'       => $paginator->currentPage(),
                'pageSize'   => $paginator->perPage(),
                'total'      => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Get total count of all style images.
     * GET /api/v1/admin/styles/count
     */
    public function count(Request $request): JsonResponse
    {
        $count = StyleImage::count();

        return response()->json(['count' => $count]);
    }

    /**
     * Delete multiple style images.
     * POST /api/v1/admin/styles/delete-multiple
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'imageIds'   => ['required', 'array', 'min:1'],
            'imageIds.*' => ['string'],
        ], [
            'imageIds.required' => 'No image IDs provided',
            'imageIds.array'    => 'imageIds must be an array',
            'imageIds.min'      => 'No image IDs provided',
        ]);

        if (empty($validated['imageIds'])) {
            return ApiResponse::error('No image IDs provided', null, 400);
        }

        $adminId  = $request->user()->id;
        $imageIds = $validated['imageIds'];

        $images = StyleImage::whereIn('id', $imageIds)->get()->keyBy('id');

        $deletedCount = 0;
        $failedCount  = 0;
        $failedImages = [];

        foreach ($imageIds as $id) {
            if (!isset($images[$id])) {
                $failedCount++;
                $failedImages[] = ['id' => $id, 'reason' => 'Not found'];
                continue;
            }

            $image = $images[$id];

            if ($image->admin_id !== $adminId) {
                $failedCount++;
                $failedImages[] = ['id' => $id, 'reason' => 'Forbidden'];
                continue;
            }

            $image->delete();
            $deletedCount++;
        }

        return response()->json([
            'message'      => "{$deletedCount} image(s) deleted successfully.",
            'deletedCount' => $deletedCount,
            'failedCount'  => $failedCount,
            'failedImages' => $failedImages,
        ]);
    }
}
