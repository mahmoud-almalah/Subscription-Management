<?php

declare(strict_types=1);

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\Services\InvoiceService;
use App\Domain\Subscription\Enums\SubscriptionStatusEnum;
use App\Domain\Subscription\Models\Subscription;
use Illuminate\Support\Collection;
use Throwable;

final class GenerateMonthlyInvoicesAction
{
    public function __construct(
        private InvoiceService $invoiceService,
    ) {}

    /**
     * @return array{generated: int, skipped: int, errors: array<string>}
     */
    public function execute(): array
    {
        $result = ['generated' => 0, 'skipped' => 0, 'errors' => []];

        $this->getSubscriptionsDue()->each(function (Subscription $subscription) use (&$result): void {
            try {
                $this->invoiceService->generate($subscription);
                $result['generated']++;
            } catch (Throwable $e) {
                $result['skipped']++;
                $result['errors'][] = "Subscription {$subscription->id}: {$e->getMessage()}";
            }
        });

        return $result;
    }

    /**
     * @return Collection<int, Subscription>
     */
    private function getSubscriptionsDue(): Collection
    {
        return Subscription::withoutGlobalScopes()
            ->with(['plan', 'customer'])
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->whereDate('next_billing_date', '<=', now()->toDateString())
            ->get();
    }
}
