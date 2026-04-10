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

/**
 * @group Clients
 */
class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

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

        // Add counts without loading full relationships
        $query->withCount(['orders', 'measurements']);

        $clients = $query->latest()->paginate(15);

        return ApiResponse::paginated(
            'Clients retrieved successfully',
            $clients->setCollection(
                $clients->getCollection()->map(fn($client) => new ClientResource($client))
            )
        );
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $client = $request->user()->clients()->create($request->validated());

        return ApiResponse::success(
            'Client created successfully',
            new ClientResource($client),
            201
        );
    }

    public function show(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        return ApiResponse::success(
            'Client retrieved successfully',
            new ClientResource($client)
        );
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $this->authorize('update', $client);

        $client->update($request->validated());

        return ApiResponse::success(
            'Client updated successfully',
            new ClientResource($client)
        );
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return ApiResponse::success('Client deleted successfully');
    }
}
