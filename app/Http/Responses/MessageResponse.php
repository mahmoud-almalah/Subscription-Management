<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class MessageResponse implements Responsable
{
    public function __construct(
        /** @var array<string, mixed>|null $data */
        private ?array $data = null,
        private string $message = 'Success',
        private int $status = Response::HTTP_OK,
        /** @var array<string, mixed>|null $debug */
        private ?array $debug = null,
    ) {}

    public function toResponse($request): JsonResponse
    {
        $response = [
            'success' => $this->status >= 200 && $this->status < 300,
            'message' => $this->message,
            'data' => $this->data,
        ];

        if ($this->debug !== null) {
            $response['debug'] = $this->debug;
        }

        return new JsonResponse(
            data: $response,
            status: $this->status
        );
    }
}
