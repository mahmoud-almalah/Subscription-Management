<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final readonly class FormRequestResponse implements Responsable
{
    public function __construct(
        private ?string $message,
        /** @var array<string, array<int, string>> $data */
        private array $data
    ) {}

    public function toResponse($request): JsonResponse
    {
        return Response::json(
            data: [
                'success' => false,
                'message' => $this->message ?? 'Validation failed',
                'errors' => $this->data,
            ],
            status: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
