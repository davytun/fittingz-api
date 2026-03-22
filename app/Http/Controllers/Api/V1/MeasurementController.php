<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Measurement\StoreMeasurementRequest;
use App\Http\Requests\Measurement\UpdateMeasurementRequest;
use App\Http\Resources\MeasurementResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Measurements
 */
class MeasurementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->measurements()->with('client');

        // Filter by client_id (required for flat structure)
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $measurements = $query->orderBy('measurement_date', 'desc')->get();

        return ApiResponse::success(
            'Measurements retrieved successfully',
            MeasurementResource::collection($measurements)
        );
    }

    public function store(StoreMeasurementRequest $request): JsonResponse
    {
        // Verify client belongs to user
        $client = $request->user()->clients()->findOrFail($request->client_id);

        $measurement = $client->measurements()->create([
            'user_id' => $request->user()->id,
            'measurements' => $request->measurements,
            'unit' => $request->unit,
            'notes' => $request->notes,
            'measurement_date' => $request->measurement_date,
        ]);

        return ApiResponse::success(
            'Measurement created successfully',
            new MeasurementResource($measurement),
            201
        );
    }

    public function latest(Request $request): JsonResponse
    {
        $query = $request->user()->measurements();

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $measurement = $query->orderBy('measurement_date', 'desc')->first();

        if (! $measurement) {
            return ApiResponse::error('No measurements found', null, 404);
        }

        return ApiResponse::success(
            'Latest measurement retrieved successfully',
            new MeasurementResource($measurement)
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $measurement = $request->user()->measurements()
            ->with('client:id,name') // Only load needed columns
            ->findOrFail($id);

        if ($measurement->user_id !== $request->user()->id) {
            return ApiResponse::error('Measurement not found', null, 404);
        }

        return ApiResponse::success(
            'Measurement retrieved successfully',
            new MeasurementResource($measurement)
        );
    }

    public function update(UpdateMeasurementRequest $request, string $id): JsonResponse
    {
        $measurement = $request->user()->measurements()->findOrFail($id);

        $updateData = $request->validated();

        // PATCH logic: Merge measurements, don't replace
        if (isset($updateData['measurements'])) {
            $existingMeasurements = $measurement->measurements;
            $newMeasurements = $updateData['measurements'];

            // Merge: add new fields, update existing fields, remove null fields
            foreach ($newMeasurements as $key => $value) {
                if ($value === null) {
                    // Remove field if value is null
                    unset($existingMeasurements[$key]);
                } else {
                    // Add or update field
                    $existingMeasurements[$key] = $value;
                }
            }

            // Ensure at least one measurement remains
            if (empty($existingMeasurements)) {
                return ApiResponse::error(
                    'Cannot remove all measurements. At least one field is required.',
                    null,
                    422
                );
            }

            $updateData['measurements'] = $existingMeasurements;
        }

        $measurement->update($updateData);

        return ApiResponse::success(
            'Measurement updated successfully',
            new MeasurementResource($measurement->fresh())
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $measurement = $request->user()->measurements()->findOrFail($id);

        $measurement->delete();

        return ApiResponse::success('Measurement deleted successfully');
    }

    public function setDefault(Request $request, string $id): JsonResponse
    {
        $measurement = $request->user()->measurements()->findOrFail($id);

        // Set as default (model event will handle unsetting others)
        $measurement->update(['is_default' => true]);

        return ApiResponse::success(
            'Default measurement updated successfully',
            new MeasurementResource($measurement->fresh())
        );
    }
}
