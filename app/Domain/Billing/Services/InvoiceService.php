<?php

declare(strict_types=1);

namespace App\Domain\Billing\Services;

use App\Domain\Accounting\Services\AccountingService;
use App\Domain\Billing\Enums\InvoiceStatusEnum;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Subscription\Models\Subscription;
use Illuminate\Support\Facades\DB;

final class InvoiceService
{
    public function __construct(
        private AccountingService $accountingService,
    ) {}

    public function generate(Subscription $subscription): Invoice
    {
        return DB::transaction(function () use ($subscription): Invoice {

            $plan = $subscription->plan;
            $periodStart = now()->startOfMonth()->toDateString();
            $periodEnd = now()->endOfMonth()->toDateString();

            $invoice = Invoice::create([
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'customer_id' => $subscription->customer_id,
                'invoice_number' => $this->generateInvoiceNumber($subscription->tenant_id),
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'status' => InvoiceStatusEnum::SENT,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'due_date' => now()->addDays(30)->toDateString(),
            ]);

            // Update next billing date for the subscription
            $subscription->update([
                'next_billing_date' => now()->addMonth()->startOfMonth()->toDateString(),
            ]);

            // Create corresponding accounting entries for the invoice
            $this->accountingService->createInvoiceEntries($invoice);

            return $invoice;
        });
    }

    private function generateInvoiceNumber(string $tenantId): string
    {
        $count = Invoice::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->count();

        $year = now()->format('Y');

        return "INV-{$year}-".mb_str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
    }
}
