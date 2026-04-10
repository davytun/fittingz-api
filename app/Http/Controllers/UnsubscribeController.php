<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;

class UnsubscribeController extends Controller
{
    public function unsubscribe(Request $request, string $userId)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired unsubscribe link.');
        }

        $user = User::find($userId);

        if ($user) {
            $user->update(['email_notifications' => false]);
        }

        // Always return success to avoid leaking user existence.
        return response()->view('unsubscribe.success');
    }
}
