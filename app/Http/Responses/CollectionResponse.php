<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class CollectionResponse implements Responsable
{
    public function __construct(
        private string $key,
        private mixed $data,
        private string $message = 'Success',
        private int $status = Response::HTTP_OK,
        private ?array $meta = null,
    ) {}

    public function toResponse($request): JsonResponse
    {
        return new JsonResponse(
            data: [
                'success' => $this->status >= 200 && $this->status < 300,
                'message' => $this->message,
                'data' => [
                    $this->key => $this->data,
                ],
                'meta' => $this->meta,
            ],
            status: $this->status
        );
    }
}
