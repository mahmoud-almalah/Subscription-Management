<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ApiExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public static function render(Throwable $e): Response
    {
        if ($e instanceof ValidationException) {
            return ApiResponse::validation($e->errors(), $e->getMessage())->toResponse(request());
        }

        if ($e instanceof ModelNotFoundException) {
            return ApiResponse::error(
                message: 'Resource not found',
                status: Response::HTTP_NOT_FOUND
            )->toResponse(request());
        }

        if ($e instanceof AuthenticationException) {
            return ApiResponse::error(
                message: 'Unauthenticated',
                status: Response::HTTP_UNAUTHORIZED
            )->toResponse(request());
        }

        // Default to 500 for unknown errors, but respect the exception's code if valid connection reset or similar
        $statusCode = $e->getCode();

        // Ensure status code is a valid HTTP status (100-599)
        // PDOExceptions often have alphanumeric codes
        if (! is_int($statusCode) || $statusCode < 100 || $statusCode > 599) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        // If specific HTTP interface is used
        if (method_exists($e, 'getStatusCode')) {
            /** @var int $statusCode */
            $statusCode = $e->getStatusCode();
        }

        $message = $e->getMessage();

        // Hide internal error details in production generally, but here we just pass message.
        // Users can override this trait or add conditional logic if needed.
        if (app()->environment('production') && $statusCode === Response::HTTP_INTERNAL_SERVER_ERROR) {
            $message = 'Server Error';
        }

        $debug = null;
        if (! app()->environment('production')) {
            $currentRequest = request();

            $debug = [
                'exception' => [
                    'class' => $e::class,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->take(5)->toArray(), // Limit trace
                ],
                'request' => [
                    'method' => $currentRequest->method(),
                    'url' => $currentRequest->fullUrl(),
                    'input' => $currentRequest->all(),
                ],
                'time' => now()->toIso8601String(),
            ];

            // Attempt to get query logs if enabled
            // We can't easily force enable them here as it might be too late,
            // but we can check if they exist.
            if (method_exists(DB::class, 'getQueryLog')) {
                $queries = DB::getQueryLog();
                if (! empty($queries)) {
                    $debug['queries'] = $queries;
                }
            }
        }

        return ApiResponse::error(
            message: $message,
            status: (int) $statusCode,
            debug: $debug
        )->toResponse(request());
    }
}
