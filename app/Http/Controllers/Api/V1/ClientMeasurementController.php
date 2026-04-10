<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Measurement\StoreMeasurementRequest;
use App\Http\Requests\Measurement\UpdateMeasurementRequest;
use App\Http\Resources\MeasurementResource;
use App\Models\Client;
use App\Models\Measurement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ClientMeasurementController extends Controller
{
    public function index(Client $client): JsonResponse
    {
        $this->authorize('viewAny', [Measurement::class, $client]);

        $measurements = $client->measurements()
            ->orderBy('is_default', 'desc')
            ->orderBy('measurement_date', 'desc')
            ->get();

        return ApiResponse::success(
            'Measurements retrieved successfully',
            MeasurementResource::collection($measurements)
        );
    }

    public function store(StoreMeasurementRequest $request, Client $client): JsonResponse
    {
        $this->authorize('create', [Measurement::class, $client]);

        $measurement = $client->measurements()->create([
            'user_id'          => $request->user()->id,
            'name'             => $request->name,
            'fields'           => $request->fields,
            'unit'             => $request->unit,
            'notes'            => $request->notes,
            'measurement_date' => $request->measurement_date,
            'is_default'       => $request->boolean('is_default', false),
        ]);

        return ApiResponse::success(
            'Measurement created successfully',
            new MeasurementResource($measurement),
            201
        );
    }

    public function show(Client $client, Measurement $measurement): JsonResponse
    {
        if ($measurement->client_id !== $client->id) {
            abort(404);
        }

        $this->authorize('view', $measurement);

        return ApiResponse::success(
            'Measurement retrieved successfully',
            new MeasurementResource($measurement)
        );
    }

    public function update(UpdateMeasurementRequest $request, Client $client, Measurement $measurement): JsonResponse
    {
        if ($measurement->client_id !== $client->id) {
            abort(404);
        }

        $this->authorize('update', $measurement);

        $updateData = $request->validated();

        if (isset($updateData['fields'])) {
            $existingFields = $measurement->fields;
            $newFields = $updateData['fields'];

            foreach ($newFields as $key => $value) {
                if ($value === null) {
                    unset($existingFields[$key]);
                } else {
                    $existingFields[$key] = $value;
                }
            }

            if (empty($existingFields)) {
                return ApiResponse::error(
                    'Cannot remove all fields. At least one measurement field is required.',
                    null,
                    422
                );
            }

            $updateData['fields'] = $existingFields;
        }

        $measurement->update($updateData);

        return ApiResponse::success(
            'Measurement updated successfully',
            new MeasurementResource($measurement->fresh())
        );
    }

    public function destroy(Client $client, Measurement $measurement): JsonResponse
    {
        if ($measurement->client_id !== $client->id) {
            abort(404);
        }

        $this->authorize('delete', $measurement);

        $measurement->delete();

        return ApiResponse::success('Measurement deleted successfully');
    }

    public function setDefault(Client $client, Measurement $measurement): JsonResponse
    {
        if ($measurement->client_id !== $client->id) {
            abort(404);
        }

        $this->authorize('update', $measurement);

        DB::transaction(function () use ($client, $measurement) {
            Measurement::where('client_id', $client->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $measurement->update(['is_default' => true]);
        });

        return ApiResponse::success(
            'Default measurement updated successfully',
            new MeasurementResource($measurement->fresh())
        );
    }
}
