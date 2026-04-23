<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonException;

final class CustomerMetadataData implements Arrayable, Jsonable
{
    public function __construct(
        public string $key,
        public mixed $value,
        public string $type = 'string',
        public ?string $description = null,
    ) {}

    /**
     * Create a CustomerMetadataData instance from an array.
     *
     * @param  array{key: string, value: mixed, type?: string, description?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: data_get(target: $data, key: 'key'),
            value: data_get(target: $data, key: 'value'),
            type: data_get(target: $data, key: 'type', default: 'string'),
            description: data_get(target: $data, key: 'description'),
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'description' => $this->description,
        ];
    }

    /**
     * Convert the customer metadata to a JSON string.
     *
     * @param  int  $options  JSON encoding options (e.g., JSON_PRETTY_PRINT)
     *
     * @throws JsonException If encoding fails
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | $options);
    }
}
