<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Services;

use App\Domain\Accounting\Enums\JournalEntryTypeEnum;
use App\Domain\Accounting\Enums\NormalBalanceEnum;
use App\Domain\Accounting\Models\Account;
use App\Domain\Accounting\Models\JournalEntry;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Billing\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class AccountingService
{
    /**
     * Create journal entries for a new invoice. This includes debiting accounts receivable and crediting deferred revenue.
     */
    public function createInvoiceEntries(Invoice $invoice): JournalEntry
    {
        return DB::transaction(function () use ($invoice): JournalEntry {
            [
                'accounts_receivable' => $accountsReceivableAccount,
                'deferred_revenue' => $deferredRevenueAccount,
            ] = $this->getSystemAccounts($invoice->tenant_id);

            return $this->createJournalEntry(
                tenantId: $invoice->tenant_id,
                type: JournalEntryTypeEnum::INVOICE_CREATED,
                reference: $invoice,
                description: "Invoice {$invoice->invoice_number} issued for period {$invoice->period_start->format('Y-m-d')} to {$invoice->period_end->format('Y-m-d')}",
                lines: [
                    [
                        'account_id' => $accountsReceivableAccount->id,
                        'type' => NormalBalanceEnum::DEBIT,
                        'amount' => $invoice->amount,
                        'description' => "Receivable for {$invoice->invoice_number}",
                    ],
                    [
                        'account_id' => $deferredRevenueAccount->id,
                        'type' => NormalBalanceEnum::CREDIT,
                        'amount' => $invoice->amount,
                        'description' => "Deferred revenue for {$invoice->invoice_number}",
                    ],
                ],
            );
        });
    }

    /**
     * Create journal entries for a received payment. This includes debiting cash and crediting accounts receivable.
     */
    public function createPaymentEntries(Payment $payment): JournalEntry
    {
        return DB::transaction(function () use ($payment): JournalEntry {
            [
                'cash' => $cashAccount,
                'accounts_receivable' => $accountsReceivableAccount,
            ] = $this->getSystemAccounts($payment->tenant_id);

            return $this->createJournalEntry(
                tenantId: $payment->tenant_id,
                type: JournalEntryTypeEnum::PAYMENT_RECEIVED,
                reference: $payment,
                description: "Payment received for invoice {$payment->invoice->invoice_number}",
                lines: [
                    [
                        'account_id' => $cashAccount->id,
                        'type' => NormalBalanceEnum::DEBIT,
                        'amount' => $payment->amount,
                        'description' => "Cash received ref#{$payment->reference_number}",
                    ],
                    [
                        'account_id' => $accountsReceivableAccount->id,
                        'type' => NormalBalanceEnum::CREDIT,
                        'amount' => $payment->amount,
                        'description' => "Receivable settled for invoice {$payment->invoice->invoice_number}",
                    ],
                ],
            );
        });
    }

    /**
     * Create journal entries to recognize revenue for an invoice. This includes debiting deferred revenue and crediting subscription revenue.
     */
    public function recognizeRevenue(Invoice $invoice): JournalEntry
    {
        return DB::transaction(function () use ($invoice): JournalEntry {
            [
                'deferred_revenue' => $deferredRevenueAccount,
                'subscription_revenue' => $subscriptionRevenueAccount,
            ] = $this->getSystemAccounts($invoice->tenant_id);

            return $this->createJournalEntry(
                tenantId: $invoice->tenant_id,
                type: JournalEntryTypeEnum::REVENUE_RECOGNIZED,
                reference: $invoice,
                description: "Revenue recognized for invoice {$invoice->invoice_number}",
                lines: [
                    [
                        'account_id' => $deferredRevenueAccount->id,
                        'type' => NormalBalanceEnum::DEBIT,
                        'amount' => $invoice->amount,
                        'description' => "Deferred revenue released for {$invoice->invoice_number}",
                    ],
                    [
                        'account_id' => $subscriptionRevenueAccount->id,
                        'type' => NormalBalanceEnum::CREDIT,
                        'amount' => $invoice->amount,
                        'description' => "Subscription revenue recognized for {$invoice->invoice_number}",
                    ],
                ],
            );
        });
    }

    /**
     * Create a journal entry with the given lines. Validates that the entry is balanced before saving.
     *
     * @param  array<int, array{account_id: int, type: NormalBalanceEnum, amount: float, description: string}>  $lines
     */
    private function createJournalEntry(
        string $tenantId,
        JournalEntryTypeEnum $type,
        Model $reference,
        string $description,
        array $lines,
    ): JournalEntry {
        $this->validateBalance($lines);

        $entry = JournalEntry::create([
            'tenant_id' => $tenantId,
            'entry_number' => $this->generateEntryNumber($tenantId),
            'type' => $type,
            'reference_id' => $reference->getKey(),
            'reference_type' => $reference->getMorphClass(),
            'description' => $description,
            'entry_date' => now()->toDateString(),
        ]);

        $entry->lines()->createMany(
            array_map(
                fn (array $line): array => [
                    ...$line,
                    'type' => $line['type']->value,
                ],
                $lines,
            )
        );

        return $entry;
    }

    /**
     * Validate that the total debits equal total credits for the journal entry lines. Allows for a small rounding tolerance.
     *
     * @param  array<int, array{type: NormalBalanceEnum, amount: float}>  $lines
     */
    private function validateBalance(array $lines): void
    {
        $debits = 0.0;
        $credits = 0.0;

        foreach ($lines as ['type' => $type, 'amount' => $amount]) {
            match ($type) {
                NormalBalanceEnum::DEBIT => $debits += $amount,
                NormalBalanceEnum::CREDIT => $credits += $amount,
            };
        }

        if (abs($debits - $credits) >= 0.01) {
            throw new RuntimeException(
                "Journal entry is not balanced. Debits: {$debits}, Credits: {$credits}"
            );
        }
    }

    /**
     * Get system accounts for the tenant. These accounts must be pre-seeded and marked as system accounts in the database.
     *
     * @return array{cash: Account, accounts_receivable: Account, deferred_revenue: Account, subscription_revenue: Account}
     */
    private function getSystemAccounts(string $tenantId): array
    {
        $accounts = Account::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_system', true)
            ->get()
            ->keyBy('code');

        return [
            'cash' => $accounts['1001'] ?? throw new RuntimeException('Cash account not found'),
            'accounts_receivable' => $accounts['1002'] ?? throw new RuntimeException('Accounts Receivable account not found'),
            'deferred_revenue' => $accounts['2001'] ?? throw new RuntimeException('Deferred Revenue account not found'),
            'subscription_revenue' => $accounts['4001'] ?? throw new RuntimeException('Subscription Revenue account not found'),
        ];
    }

    private function generateEntryNumber(string $tenantId): string
    {
        $count = JournalEntry::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->count();

        return 'JE-'.mb_str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);
    }
}
