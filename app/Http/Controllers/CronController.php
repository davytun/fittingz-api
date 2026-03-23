<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CronController extends Controller
{
    public function processQueue(string $secret): JsonResponse
    {
        if ($secret !== config('app.cron_secret')) {
            return ApiResponse::error('Unauthorized', null, 403);
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
        if ($secret !== config('app.cron_secret')) {
            return ApiResponse::error('Unauthorized', null, 403);
        }

        $allowedCommands = [
            'migrate' => 'migrate --force',
            'storage-link' => 'storage:link',
            'optimize' => 'optimize',
            'cache-clear' => 'cache:clear',
            'route-clear' => 'route:clear',
            'config-clear' => 'config:clear',
        ];

        if (!isset($allowedCommands[$command])) {
            return ApiResponse::error('Command not allowed', null, 400);
        }

        try {
            Artisan::call($allowedCommands[$command]);
            $output = Artisan::output();
            return ApiResponse::success("Command executed: {$command}", ['output' => $output]);
        } catch (\Exception $e) {
            return ApiResponse::error('Command failed: ' . $e->getMessage(), null, 500);
        }
    }
}