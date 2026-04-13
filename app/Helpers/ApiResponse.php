<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        string $message,
        mixed $data = null,
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    public static function error(
        string $message,
        mixed $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $statusCode);
    }

    public static function validationError(
        array $errors,
        ?string $message = null
    ): JsonResponse {
        $formattedErrors = [];

        foreach ($errors as $field => $messages) {
            $formattedErrors[$field] = is_array($messages) ? $messages[0] : $messages;
        }

        $message = $message ?? (string) collect($formattedErrors)->first();

        return self::error($message, $formattedErrors, 422);
    }

    public static function paginated(
        string $message,
        $paginatedData
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginatedData->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $paginatedData->currentPage(),
                    'last_page' => $paginatedData->lastPage(),
                    'per_page' => $paginatedData->perPage(),
                    'total' => $paginatedData->total(),
                    'from' => $paginatedData->firstItem(),
                    'to' => $paginatedData->lastItem(),
                ],
            ],
        ], 200);
    }
}
