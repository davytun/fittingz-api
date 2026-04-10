<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\MeasurementResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientProfileController extends Controller
{
    public function show(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        $client->loadCount('measurements')->load([
            'defaultMeasurement',
            'measurements' => function ($query) {
                $query->orderBy('measurement_date', 'desc')->limit(3);
            },
        ]);

        $defaultMeasurement = $client->defaultMeasurement;
        $latestMeasurements = $client->measurements->reject(
            fn ($m) => $defaultMeasurement && $m->id === $defaultMeasurement->id
        )->take(2)->values();

        return ApiResponse::success(
            'Client profile retrieved successfully',
            [
                'client'       => new ClientResource($client),
                'measurements' => [
                    'default'     => $defaultMeasurement ? new MeasurementResource($defaultMeasurement) : null,
                    'latest'      => MeasurementResource::collection($latestMeasurements),
                    'total_count' => $client->measurements_count,
                ],
            ]
        );
    }
}
