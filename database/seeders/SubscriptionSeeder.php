<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Accounting\Services\AccountingService;
use App\Domain\Billing\Enums\PaymentMethodEnum;
use App\Domain\Billing\Services\InvoiceService;
use App\Domain\Billing\Services\PaymentService;
use App\Domain\Subscription\Enums\SubscriptionStatusEnum;
use App\Domain\Subscription\Models\Plan;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\User;
use Illuminate\Database\Seeder;

final class SubscriptionSeeder extends Seeder
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly PaymentService $paymentService,
    ) {}

    public function run(): void
    {
        $acme = Tenant::withoutGlobalScopes()->where('slug', 'acme-corp')->first();
        $globex = Tenant::withoutGlobalScopes()->where('slug', 'globex-inc')->first();

        $this->seedTenant($acme);
        $this->seedTenant($globex);

        $this->command->info('  Subscriptions, Invoices, Payments seeded');
    }

    private function seedTenant(Tenant $tenant): void
    {
        User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('role', 'admin')
            ->first();

        $customers = Customer::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->get();

        $plans = Plan::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('billing_cycle', 'monthly')
            ->get();

        $starter = $plans->firstWhere('name', 'Starter');
        $professional = $plans->firstWhere('name', 'Professional');
        $enterprise = $plans->firstWhere('name', 'Enterprise');

        foreach ($customers as $index => $customer) {
            $plan = match ($index % 3) {
                0 => $starter,
                1 => $professional,
                2 => $enterprise,
            };

            if ($index % 4 === 0) {
                $this->createFullCycleSubscription($tenant, $customer, $plan);

                continue;
            }

            if ($index % 4 === 1) {
                $this->createPaidSubscription($tenant, $customer, $plan);

                continue;
            }

            if ($index % 4 === 2) {
                $this->createPendingSubscription($tenant, $customer, $plan);

                continue;
            }

            $this->createCancelledSubscription($tenant, $customer, $plan);
        }
    }

    private function createFullCycleSubscription(Tenant $tenant, $customer, $plan): void
    {
        $subscription = $this->createSubscription($tenant, $customer, $plan, 'active', -35);

        $invoice = $this->invoiceService->generate($subscription);

        $invoice->update([
            'period_start' => now()->subDays(35)->toDateString(),
            'period_end' => now()->subDays(5)->toDateString(),
        ]);

        $this->paymentService->record($invoice, [
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'payment_method' => PaymentMethodEnum::BANK_TRANSFER->value,
            'payment_date' => now()->subDays(10)->toDateString(),
            'reference_number' => 'REF-'.mb_strtoupper(mb_substr(uniqid(), -6)),
            'notes' => 'Paid via bank transfer',
        ]);

        app(AccountingService::class)
            ->recognizeRevenue($invoice->fresh());

        $invoice->fresh()->update(['revenue_recognized_at' => now()->subDays(5)]);
    }

    private function createPaidSubscription(Tenant $tenant, $customer, $plan): void
    {
        $subscription = $this->createSubscription($tenant, $customer, $plan, 'active', -20);

        $invoice = $this->invoiceService->generate($subscription);

        $invoice->update([
            'period_start' => now()->subDays(20)->toDateString(),
            'period_end' => now()->subDays(1)->toDateString(),
        ]);

        $this->paymentService->record($invoice, [
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'payment_method' => PaymentMethodEnum::CASH->value,
            'payment_date' => now()->subDays(5)->toDateString(),
            'notes' => 'Paid in cash at the office',
            'reference_number' => 'REF-'.mb_strtoupper(mb_substr(uniqid('', true), -6)),
        ]);
    }

    private function createPendingSubscription(Tenant $tenant, $customer, $plan): void
    {
        $subscription = $this->createSubscription($tenant, $customer, $plan, 'active', -5);

        $this->invoiceService->generate($subscription);
    }

    private function createCancelledSubscription(Tenant $tenant, $customer, $plan): void
    {
        $subscription = $this->createSubscription($tenant, $customer, $plan, 'cancelled', -60);

        $subscription->update([
            'status' => SubscriptionStatusEnum::CANCELLED,
            'cancelled_at' => now()->subDays(30),
            'cancellation_reason' => 'Customer requested cancellation',
            'next_billing_date' => null,
        ]);
    }

    private function createSubscription(
        Tenant $tenant,
        $customer,
        $plan,
        string $status,
        int $startedDaysAgo,
    ): Subscription {
        return Subscription::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => $status,
            'started_at' => now()->addDays($startedDaysAgo)->toDateString(),
            'next_billing_date' => now()->addDays(25)->toDateString(),
        ]);
    }
}
