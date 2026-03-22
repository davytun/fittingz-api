<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $startTime;

        // Only log in production if response is error or slow
        if (config('app.env') === 'production') {
            if ($response->getStatusCode() >= 400 || $duration > 1) {
                $this->logRequest($request, $response, $duration);
            }
        } else {
            // Log all requests in non-production
            $this->logRequest($request, $response, $duration);
        }

        return $response;
    }

    private function logRequest(Request $request, Response $response, float $duration): void
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'status' => $response->getStatusCode(),
            'duration' => round($duration * 1000, 2) . 'ms',
        ];

        $level = $response->getStatusCode() >= 500 ? 'error' : ($response->getStatusCode() >= 400 ? 'warning' : 'info');

        Log::channel('api')->{$level}('API Request', $logData);
    }
}