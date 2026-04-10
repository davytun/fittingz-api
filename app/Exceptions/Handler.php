<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected function isApiRequest($request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Handle unauthenticated instances.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->isApiRequest($request)
            ? ApiResponse::error('Unauthenticated. Please login to continue.', null, 401)
            : redirect()->guest(route('login'));
    }

    /**
     * Handle API exceptions and return JSON responses
     */
    public function handleApiException(Throwable $exception, $request)
    {
        // Authentication exceptions
        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error(
                'Unauthenticated. Please login to continue.',
                null,
                401
            );
        }

        // Validation exceptions (already handled by BaseRequest, but just in case)
        if ($exception instanceof ValidationException) {
            return ApiResponse::validationError(
                $exception->errors(),
                'Validation failed'
            );
        }

        if ($exception instanceof AuthorizationException) {
            return ApiResponse::error(
                $exception->getMessage() ?: 'This action is unauthorized.',
                null,
                403
            );
        }

        // Model not found (e.g., Client::findOrFail())
        if ($exception instanceof ModelNotFoundException) {
            return ApiResponse::error(
                'Resource not found',
                null,
                404
            );
        }

        // 404 - Route not found
        if ($exception instanceof NotFoundHttpException) {
            if ($request->route() !== null) {
                return ApiResponse::error(
                    'Resource not found',
                    null,
                    404
                );
            }

            return ApiResponse::error(
                'Endpoint not found',
                null,
                404
            );
        }

        // 405 - Method not allowed
        if ($exception instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error(
                'Method not allowed',
                null,
                405
            );
        }

        if ($exception instanceof RouteNotFoundException) {
            if (str_contains($exception->getMessage(), '[login]')) {
                return ApiResponse::error(
                    'Unauthenticated. Please login to continue.',
                    null,
                    401
                );
            }

            return ApiResponse::error(
                'Route configuration error. Please contact support.',
                config('app.debug') ? ['error' => $exception->getMessage()] : null,
                500
            );
        }

        // Generic HTTP exceptions
        if ($exception instanceof HttpException) {
            return ApiResponse::error(
                $exception->getMessage() ?: 'An error occurred',
                null,
                $exception->getStatusCode()
            );
        }

        // Database exceptions
        if ($exception instanceof \Illuminate\Database\QueryException) {
            return ApiResponse::error(
                'Database error occurred',
                config('app.debug') ? ['error' => $exception->getMessage()] : null,
                500
            );
        }

        // Token expired or invalid (Sanctum)
        if ($exception instanceof \Laravel\Sanctum\Exceptions\MissingAbilityException) {
            return ApiResponse::error(
                'Invalid or expired token',
                null,
                401
            );
        }

        // Rate limit exceeded
        if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            return ApiResponse::error(
                'Too many requests. Please try again later.',
                null,
                429
            );
        }

        // All other exceptions
        return ApiResponse::error(
            config('app.debug') 
                ? $exception->getMessage() 
                : 'An unexpected error occurred. Please try again.',
            config('app.debug') 
                ? [
                    'exception' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]
                : null,
            500
        );
    }
}
