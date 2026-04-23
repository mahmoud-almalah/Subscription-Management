<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Subscription\Models\Plan;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Plan> */
final class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 0, 100),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'SAR']),
            'billing_cycle' => $this->faker->randomElement(['monthly', 'yearly']),
            'is_active' => $this->faker->boolean(),
            'features' => $this->faker->words(nb: 3),
        ];
    }
}
