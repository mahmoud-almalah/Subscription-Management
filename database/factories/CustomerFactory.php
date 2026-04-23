<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Tenant\Collections\CustomerMetadataCollection;
use App\Domain\Tenant\Data\CustomerMetadataData;
use App\Domain\Tenant\Data\LocationData;
use App\Domain\Tenant\Enums\CustomerStatusEnum;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Customer> */
final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => LocationData::fromArray([
                'lat' => $this->faker->latitude(),
                'lng' => $this->faker->longitude(),
                'address' => $this->faker->streetAddress(),
            ]),
            'status' => $this->faker->randomElement(CustomerStatusEnum::class),
            'metadata' => CustomerMetadataCollection::make([
                CustomerMetadataData::fromArray([
                    'key' => 'last_purchase_date',
                    'value' => $this->faker->dateTimeBetween('-1 year', 'now')->getTimestamp(),
                    'type' => 'timestamp',
                    'description' => 'The date of the customer\'s last purchase',
                ]),
                CustomerMetadataData::fromArray([
                    'key' => 'loyalty_points',
                    'value' => (string) $this->faker->numberBetween(0, 1000),
                    'type' => 'integer',
                    'description' => 'The number of loyalty points the customer has',
                ]),
            ]),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatusEnum::ACTIVE,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatusEnum::INACTIVE,
        ]);
    }
}
