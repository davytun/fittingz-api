<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        $healthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');

        return response()->json([
            'success' => $healthy,
            'message' => $healthy ? 'Health check passed' : 'Health check failed',
            'data' => [
                'status' => $healthy ? 'healthy' : 'unhealthy',
                'timestamp' => now()->toIso8601String(),
                'checks' => $checks,
            ],
        ], $healthy ? 200 : 503);
    }

    private function checkApp(): array
    {
        return [
            'status' => 'ok',
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'ok',
                'connection' => 'active',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $writable = Storage::disk('public')->put('health-check.txt', 'test');
            Storage::disk('public')->delete('health-check.txt');

            return [
                'status' => $writable ? 'ok' : 'error',
                'writable' => $writable,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage not writable',
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $jobCount = DB::table('jobs')->count();
            $failedCount = DB::table('failed_jobs')->count();

            return [
                'status' => 'ok',
                'pending_jobs' => $jobCount,
                'failed_jobs' => $failedCount,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed',
            ];
        }
    }
}
