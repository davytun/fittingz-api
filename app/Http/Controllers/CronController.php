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
}