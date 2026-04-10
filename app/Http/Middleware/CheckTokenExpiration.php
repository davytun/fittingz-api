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
        $token = $request->user()->currentAccessToken();
        $createdAt = $token?->created_at;

        if ($token && $createdAt instanceof CarbonInterface && $createdAt->copy()->addDays(7)->isPast()) {
            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }

            return ApiResponse::error('Token has expired. Please login again.', null, 401);
        }

        return $next($request);
    }
}
