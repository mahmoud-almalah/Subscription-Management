<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\Paginator as ContractsPaginator;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator as IlluminatePaginator;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponse
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function success(?array $data = null, string $message = 'Success', int $status = Response::HTTP_OK): Responsable
    {
        return new MessageResponse(
            data: $data,
            message: $message,
            status: $status,
        );
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @param  array<string, mixed>|null  $debug
     */
    public static function error(
        string $message = 'Error',
        int $status = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?array $data = null,
        ?array $debug = null
    ): Responsable {
        return new MessageResponse(
            data: $data,
            message: $message,
            status: $status,
            debug: $debug,
        );
    }

    public static function model(
        string $key,
        JsonResource $resource,
        string $message = 'Success',
        int $status = Response::HTTP_OK
    ): Responsable {
        return new ModelResponse(
            key: $key,
            resource: $resource,
            message: $message,
            status: $status,
        );
    }

    public static function collection(
        string $key,
        AnonymousResourceCollection|array $resource,
        string $message = 'Success',
        int $status = Response::HTTP_OK,
        null|IlluminatePaginator|ContractsPaginator|LengthAwarePaginator $paginator = null
    ): Responsable {
        return new CollectionResponse(
            key: $key,
            collection: $resource,
            paginator: $paginator,
            message: $message,
            status: $status,
        );
    }

    /**
     * @param  array<string, array<string>>  $errors
     */
    public static function validation(
        array $errors,
        string $message = 'Validation failed'
    ): Responsable {
        return new FormRequestResponse(
            message: $message,
            data: $errors,
        );
    }
}
