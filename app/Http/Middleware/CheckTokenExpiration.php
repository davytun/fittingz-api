<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\CarbonInterface;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiration
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $token = $user->currentAccessToken();

        if (! $token) {
            return $next($request);
        }

        $createdAt = $token->created_at;

        if ($createdAt instanceof CarbonInterface && $createdAt->copy()->addDays(7)->isPast()) {
            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }

            return ApiResponse::error('Token has expired. Please login again.', null, 401);
        }

        return $next($request);
    }
}
