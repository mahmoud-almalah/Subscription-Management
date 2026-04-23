<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Casts;

use App\Domain\Tenant\Data\LocationData;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class LocationCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?LocationData
    {
        if (blank($value)) {
            return null;
        }

        $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return LocationData::fromArray($data);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof LocationData) {
            return $value->toJson();
        }

        if (is_array($value)) {
            return LocationData::fromArray($value)->toJson();
        }

        if (is_string($value)) {
            json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return $value;
        }

        return null;
    }
}
