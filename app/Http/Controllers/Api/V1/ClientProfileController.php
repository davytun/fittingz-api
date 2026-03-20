<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\MeasurementResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientProfileController extends Controller
{
    public function show(Request $request, string $id): JsonResponse
    {
        $client = $request->user()->clients()->with([
            'defaultMeasurement',
            'measurements' => function ($query) {
                $query->orderBy('measurement_date', 'desc')->limit(2);
            }
        ])->findOrFail($id);

        $defaultMeasurement = $client->defaultMeasurement;
        $latestMeasurements = $client->measurements->reject(function ($measurement) use ($defaultMeasurement) {
            return $defaultMeasurement && $measurement->id === $defaultMeasurement->id;
        })->values();

        return ApiResponse::success(
            'Client profile retrieved successfully',
            [
                'client' => new ClientResource($client),
                'measurements' => [
                    'default' => $defaultMeasurement ? new MeasurementResource($defaultMeasurement) : null,
                    'latest' => MeasurementResource::collection($latestMeasurements),
                    'total_count' => $client->measurements()->count(),
                ],
            ]
        );
    }
}
