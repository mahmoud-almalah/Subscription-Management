<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Subscription\Enums\SubscriptionStatusEnum;
use App\Domain\Subscription\Models\Plan;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subscription> */
final class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => Customer::factory(),
            'plan_id' => Plan::factory(),
            'status' => $this->faker->randomElement(SubscriptionStatusEnum::class),
            'started_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'ends_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'next_billing_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'cancelled_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'cancellation_reason' => $this->faker->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatusEnum::ACTIVE,
            'started_at' => now()->subDays(10),
            'ends_at' => now()->addDays(20),
            'next_billing_date' => now()->addDays(20),
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatusEnum::CANCELLED,
            'started_at' => now()->subDays(30),
            'ends_at' => now()->addDays(10),
            'next_billing_date' => null,
            'cancelled_at' => now()->subDays(5),
            'cancellation_reason' => $this->faker->sentence(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatusEnum::EXPIRED,
            'started_at' => now()->subDays(30),
            'ends_at' => now()->subDays(1),
            'next_billing_date' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }
}
