<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Billing\Enums\PaymentMethodEnum;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Billing\Models\Payment;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
final class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'invoice_id' => Invoice::factory(),
            'customer_id' => Customer::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => $this->faker->currencyCode(),
            'payment_method' => $this->faker->randomElement(PaymentMethodEnum::class),
            'payment_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'reference_number' => $this->faker->optional()->numerify('REF-#####'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
