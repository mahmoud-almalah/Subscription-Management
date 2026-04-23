<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonException;

final readonly class TenantSettingData implements Arrayable, Jsonable
{
    public function __construct(
        public string $currency = 'SAR',
        public string $timezone = 'Asia/Riyadh',
    ) {}

    /**
     * Create a TenantSettingData instance from an array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            currency: data_get(target: $data, key: 'currency', default: 'SAR'),
            timezone: data_get(target: $data, key: 'timezone', default: 'Asia/Riyadh'),
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'currency' => $this->currency,
            'timezone' => $this->timezone,
        ];
    }

    /**
     * Convert the tenant settings to a JSON string.
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
