<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Tenant\Data\TenantSettingData;
use App\Domain\Tenant\Enums\TenantStatusEnum;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tenant> */
final class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'slug' => $this->faker->slug(nbWords: 2),
            'email' => $this->faker->unique()->companyEmail(),
            'status' => $this->faker->randomElement(TenantStatusEnum::cases()),
            'settings' => TenantSettingData::fromArray([
                'currency' => $this->faker->randomElement(['USD', 'EUR', 'SAR']),
                'timezone' => $this->faker->randomElement(['America/New_York', 'Europe/London', 'Asia/Riyadh']),
            ]),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TenantStatusEnum::ACTIVE,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TenantStatusEnum::CANCELLED,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TenantStatusEnum::SUSPENDED,
        ]);
    }

    /** @param array<string, string> $settings */
    public function withCustomSettings(array $settings): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => TenantSettingData::fromArray($settings),
        ]);
    }
}
