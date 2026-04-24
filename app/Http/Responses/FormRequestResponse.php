<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class FormRequestResponse implements Responsable
{
    public function __construct(
        private mixed $data,
        private string $message = 'Validation failed.',
    ) {}

    public function toResponse($request): JsonResponse
    {
        return new JsonResponse(
            data: [
                'success' => false,
                'message' => $this->message,
                'data' => [
                    'errors' => $this->data,
                ],
                'meta' => null,
            ],
            status: Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
