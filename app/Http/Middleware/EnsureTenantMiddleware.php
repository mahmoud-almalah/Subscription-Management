<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('user');

        if (! $user?->tenant_id) {
            return ApiResponse::error(
                message: 'User does not belong to any tenant.',
                status: Response::HTTP_FORBIDDEN
            )->toResponse($request);
        }

        if (! $user->tenant?->status->isActive()) {
            return ApiResponse::error(
                message: 'Tenant is not active.',
                status: Response::HTTP_FORBIDDEN
            )->toResponse($request);
        }

        return $next($request);
    }
}
