<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Collections;

use App\Domain\Tenant\Data\CustomerMetadataData;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/** @extends Collection<int, CustomerMetadataData> */
final class CustomerMetadataCollection extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct(
            collect($items)->map(function ($item) {
                if ($item instanceof CustomerMetadataData) {
                    return $item;
                }

                if (is_array($item)) {
                    return CustomerMetadataData::fromArray($item);
                }

                throw new InvalidArgumentException('Invalid metadata item');
            })->all()
        );
    }

    public function getByKey(string $key): ?CustomerMetadataData
    {
        return $this->first(fn (CustomerMetadataData $item) => $item->key === $key);
    }

    public function set(string $key, mixed $value, string $type = 'string', ?string $description = null): self
    {
        throw_if(
            $this->getByKey($key),
            new InvalidArgumentException("Metadata with key '{$key}' already exists")
        );

        $this->push(new CustomerMetadataData(key: $key, value: $value, type: $type, description: $description));

        return $this;
    }

    public function update(string $key, mixed $value, string $type = 'string', ?string $description = null): self
    {
        $metadata = $this->getByKey($key);

        throw_unless(
            $metadata,
            new InvalidArgumentException("Metadata with key '{$key}' does not exist")
        );

        $metadata->value = $value;
        $metadata->type = $type;
        $metadata->description = $description;

        return $this;
    }

    /** @return array<int, array<string, string>> */
    public function toArray(): array
    {
        return $this->map(fn (CustomerMetadataData $item) => $item->toArray())->values()->all();
    }
}
