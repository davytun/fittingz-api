<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientBelongsToUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $clientId = $request->route('client');

        if ($clientId) {
            $client = Client::find($clientId);

            if (!$client || $client->user_id !== $request->user()->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client not found',
                ], 404);
            }
        }

        return $next($request);
    }
}