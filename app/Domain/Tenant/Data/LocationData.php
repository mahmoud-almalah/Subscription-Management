<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonException;

final class LocationData implements Arrayable, Jsonable
{
    public function __construct(
        public float $lat,
        public float $lng,
        public ?string $address = null,
    ) {}

    /**
     * Create a LocationData instance from an array.
     *
     * @param  array{lat?: float|string, lng?: float|string, address?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            lat: (float) data_get(target: $data, key: 'lat', default: 0.0),
            lng: (float) data_get(target: $data, key: 'lng', default: 0.0),
            address: data_get(target: $data, key: 'address'),
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'address' => $this->address,
        ];
    }

    /**
     * Convert the location data to a JSON string.
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
