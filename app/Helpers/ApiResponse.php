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
        $response = [
            'status' => 'success',
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        // Add version in non-production or if explicitly requested
        if (config('app.env') !== 'production' || request()->has('include_version')) {
            $response['version'] = config('app.version');
        }

        return response()->json($response, $statusCode);
    }

    public static function error(
        string $message,
        mixed $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    public static function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        // Flatten validation errors to simple key-value pairs
        $formattedErrors = [];
        
        foreach ($errors as $field => $messages) {
            // Take only the first error message for each field
            $formattedErrors[$field] = is_array($messages) ? $messages[0] : $messages;
        }

        return self::error($message, $formattedErrors, 422);
    }

    public static function paginated(
        string $message,
        $paginatedData
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $paginatedData->items(),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total(),
                'from' => $paginatedData->firstItem(),
                'to' => $paginatedData->lastItem(),
            ],
        ], 200);
    }
}