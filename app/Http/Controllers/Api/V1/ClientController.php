<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->clients();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        $clients = $query->latest()->paginate(15);

        return ApiResponse::success(
            'Clients retrieved successfully',
            [
                'clients' => ClientResource::collection($clients),
                'meta' => [
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'per_page' => $clients->perPage(),
                    'total' => $clients->total(),
                ],
            ]
        );
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $request->user()->clients()->create($request->validated());

        return ApiResponse::success(
            'Client created successfully',
            new ClientResource($client),
            201
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $client = $request->user()->clients()->findOrFail($id);

        return ApiResponse::success(
            'Client retrieved successfully',
            new ClientResource($client)
        );
    }

    public function update(UpdateClientRequest $request, string $id): JsonResponse
    {
        $client = $request->user()->clients()->findOrFail($id);
        $client->update($request->validated());

        return ApiResponse::success(
            'Client updated successfully',
            new ClientResource($client)
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $client = $request->user()->clients()->findOrFail($id);
        $client->delete();

        return ApiResponse::success('Client deleted successfully');
    }
}