<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CronController extends Controller
{
    public function processQueue(string $secret): JsonResponse
    {
        if ($secret !== config('app.cron_secret')) {
            abort(403, 'Unauthorized');
        }

        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--tries' => 3,
            '--max-time' => 50,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Queue processed',
        ]);
    }
}