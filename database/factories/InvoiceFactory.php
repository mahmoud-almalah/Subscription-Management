<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Billing\Enums\InvoiceStatusEnum;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Invoice> */
final class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'subscription_id' => Subscription::factory(),
            'customer_id' => Customer::factory(),
            'invoice_number' => $this->faker->unique()->numerify('INV-#####'),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => $this->faker->currencyCode(),
            'status' => $this->faker->randomElement(InvoiceStatusEnum::class),
            'period_start' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'period_end' => $this->faker->dateTimeBetween('now', '+1 month'),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'paid_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'revenue_recognized_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatusEnum::PAID,
            'paid_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatusEnum::DRAFT,
            'paid_at' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatusEnum::OVERDUE,
            'due_date' => now()->subDays(5),
            'paid_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatusEnum::CANCELLED,
            'paid_at' => null,
        ]);
    }
}
