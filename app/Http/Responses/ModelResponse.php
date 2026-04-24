<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

final readonly class ModelResponse implements Responsable
{
    public function __construct(
        private string $key,
        private JsonResource $resource,
        private string $message = 'Success',
        private int $status = Response::HTTP_OK
    ) {}

    public function toResponse($request): JsonResponse
    {
        return new JsonResponse(
            data: [
                'success' => $this->status >= 200 && $this->status < 300,
                'message' => $this->message,
                'data' => [
                    $this->key => $this->resource,
                ],
            ],
            status: $this->status
        );
    }
}
