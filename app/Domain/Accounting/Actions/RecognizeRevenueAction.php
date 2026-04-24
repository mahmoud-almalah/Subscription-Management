<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Actions;

use App\Domain\Accounting\Services\AccountingService;
use App\Domain\Billing\Models\Invoice;
use Illuminate\Support\Collection;
use Throwable;

final class RecognizeRevenueAction
{
    public function __construct(
        private AccountingService $accountingService,
    ) {}

    /**
     * @return array{recognized: int, skipped: int, errors: array<string>}
     */
    public function execute(): array
    {
        $result = ['recognized' => 0, 'skipped' => 0, 'errors' => []];

        $this->getUnrecognizedInvoices()->each(function (Invoice $invoice) use (&$result): void {
            try {
                $this->accountingService->recognizeRevenue($invoice);

                $invoice->update([
                    'revenue_recognized_at' => now(),
                ]);

                $result['recognized']++;
            } catch (Throwable $e) {
                $result['skipped']++;
                $result['errors'][] = "Invoice {$invoice->invoice_number}: {$e->getMessage()}";
            }
        });

        return $result;
    }

    /**
     * Get all invoices that have been paid but not yet recognized as revenue, and whose period has ended.
     *
     * @return Collection<int, Invoice>
     */
    private function getUnrecognizedInvoices(): Collection
    {
        return Invoice::withoutGlobalScopes()
            ->whereNotNull('paid_at')
            ->whereNull('revenue_recognized_at')
            ->whereDate('period_end', '<=', now()->toDateString())
            ->get();
    }
}
