<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiration
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()->currentAccessToken();

        if ($token && $token->created_at->addDays(7)->isPast()) {
            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired. Please login again.',
            ], 401);
        }

        return $next($request);
    }
}