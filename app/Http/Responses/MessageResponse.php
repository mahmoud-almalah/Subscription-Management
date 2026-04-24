<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class MessageResponse implements Responsable
{
    public function __construct(
        private string $message,
        private int $status = Response::HTTP_OK,
    ) {}

    public static function make(string $message, int $status = Response::HTTP_OK): self
    {
        return new self($message, $status);
    }

    public function toResponse($request): JsonResponse
    {
        return new JsonResponse(
            data: [
                'success' => $this->status >= 200 && $this->status < 300,
                'message' => $this->message,
                'data' => null,
                'meta' => null,
            ],
            status: $this->status
        );
    }
}
