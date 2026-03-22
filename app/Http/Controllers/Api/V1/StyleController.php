<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Style\StoreStyleRequest;
use App\Http\Requests\Style\UpdateStyleRequest;
use App\Http\Resources\StyleResource;
use App\Models\Style;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group Styles
 */
class StyleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->styles();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', strtolower($request->category));
        }

        // Filter by tags (JSON contains)
        if ($request->has('tag')) {
            $query->whereJsonContains('tags', strtolower($request->tag));
        }

        $styles = $query->latest()->paginate(20);

        return ApiResponse::paginated(
            'Styles retrieved successfully',
            $styles->setCollection(
                $styles->getCollection()->map(fn($style) => new StyleResource($style))
            )
        );
    }

    public function store(StoreStyleRequest $request): JsonResponse
    {
        // Store image
        $imagePath = $request->file('image')->store('styles', 'public');

        $style = $request->user()->styles()->create([
            'title' => $request->title,
            'description' => $request->description,
            'image_path' => $imagePath,
            'category' => $request->category,
            'tags' => $request->tags,
        ]);

        return ApiResponse::success(
            'Style uploaded successfully',
            new StyleResource($style),
            201
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $style = $request->user()->styles()->findOrFail($id);

        return ApiResponse::success(
            'Style retrieved successfully',
            new StyleResource($style)
        );
    }

    public function update(UpdateStyleRequest $request, string $id): JsonResponse
    {
        $style = $request->user()->styles()->findOrFail($id);

        $updateData = $request->validated();

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image
            if ($style->image_path && Storage::disk('public')->exists($style->image_path)) {
                Storage::disk('public')->delete($style->image_path);
            }

            // Store new image
            $updateData['image_path'] = $request->file('image')->store('styles', 'public');
        }

        $style->update($updateData);

        return ApiResponse::success(
            'Style updated successfully',
            new StyleResource($style)
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $style = $request->user()->styles()->findOrFail($id);

        // Check if style is linked to any orders
        if ($style->orders()->exists()) {
            return ApiResponse::error(
                'Cannot delete style. It is linked to one or more orders.',
                null,
                400
            );
        }

        $style->delete();

        return ApiResponse::success('Style deleted successfully');
    }
}