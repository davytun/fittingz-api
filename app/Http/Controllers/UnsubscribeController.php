<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnsubscribeController extends Controller
{
    /**
     * Show unsubscribe confirmation form
     */
    public function showUnsubscribeForm(Request $request, string $userId)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired unsubscribe link.');
        }

        // Store the signed URL for the POST request
        $signedUrl = $request->fullUrl();

        return response()->view('unsubscribe.confirm', ['signedUrl' => $signedUrl]);
    }

    /**
     * Process unsubscribe request (POST)
     */
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
