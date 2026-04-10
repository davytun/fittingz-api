<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    public function processQueue(string $secret): JsonResponse
    {
        $configured = (string) config('app.cron_secret');
        if ($configured === '' || ! hash_equals($configured, (string) $secret)) {
            return ApiResponse::error('Unauthorized.', null, 403);
        }

        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--tries' => 3,
            '--max-time' => 50,
        ]);

        return ApiResponse::success('Queue processed');
    }

    public function runCommand(string $secret, string $command): JsonResponse
    {
        $configured = (string) config('app.cron_secret');
        if ($configured === '' || ! hash_equals($configured, (string) $secret)) {
            return ApiResponse::error('Unauthorized.', null, 403);
        }

        // 'migrate' is intentionally excluded — run migrations via SSH/CI only.
        $allowedCommands = [
            'storage-link' => ['storage:link', []],
            'optimize'     => ['optimize', []],
            'cache-clear'  => ['cache:clear', []],
            'route-clear'  => ['route:clear', []],
            'config-clear' => ['config:clear', []],
        ];

        if (! isset($allowedCommands[$command])) {
            return ApiResponse::error('Command not allowed.', null, 400);
        }

        try {
            [$artisanCommand, $args] = $allowedCommands[$command];
            Artisan::call($artisanCommand, $args);
            $output = Artisan::output();

            return ApiResponse::success("Command executed: {$command}", ['output' => $output]);
        } catch (\Exception $e) {
            Log::error("Cron command failed: {$command}", ['exception' => $e]);

            return ApiResponse::error('Command failed.', null, 500);
        }
    }
}