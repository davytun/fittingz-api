<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(
            'Profile retrieved successfully',
            new UserResource($request->user())
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $emailChanged = isset($data['email']) && $data['email'] !== $user->email;

        $user->update($data);

        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null])->save();
            $code = $user->generateVerificationCode();
            $user->notify(new VerifyEmailNotification($code));
        }

        return ApiResponse::success(
            $emailChanged
                ? 'Profile updated successfully. Please check your email to verify your new address.'
                : 'Profile updated successfully',
            new UserResource($user->fresh())
        );
    }
}
