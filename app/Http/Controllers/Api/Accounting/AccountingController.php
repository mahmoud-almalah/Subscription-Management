<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accounting;

use App\Domain\Accounting\Actions\RecognizeRevenueAction;
use App\Domain\Accounting\Models\JournalEntry;
use App\Http\Requests\Accounting\FilterJournalRequest;
use App\Http\Resources\Accounting\JournalEntryResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Support\Responsable;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Accounting')]
#[Authenticated]
final class AccountingController
{
    public function __construct(
        private readonly RecognizeRevenueAction $recognizeRevenueAction,
    ) {}

    #[Endpoint(
        title: 'List Journal Entries',
        description: 'Retrieve a paginated list of journal entries (Double-Entry Bookkeeping ledger).',
    )]
    #[BodyParam(
        name: 'types',
        description: 'Filter by entry types: invoice_created | payment_received | revenue_recognized',
        example: '["invoice_created"]'
    )]
    #[BodyParam(
        name: 'date_from',
        description: 'Start date filter (Y-m-d).',
        example: '2025-01-01'
    )]
    #[BodyParam(
        name: 'date_to',
        description: 'End date filter (Y-m-d).',
        example: '2025-01-31'
    )]
    #[BodyParam(
        name: 'order_by',
        description: 'Sort field.',
        example: 'entry_date'
    )]
    public function journal(FilterJournalRequest $request): Responsable
    {
        $entries = JournalEntry::query()
            ->with(['lines.account'])
            ->when($request->filled('types'), fn ($q) => $q->whereIn('type', $request->array('types')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('entry_date', '>=', $request->date('date_from')->format('Y-m-d')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('entry_date', '<=', $request->date('date_to')->format('Y-m-d')))
            ->when($request->filled('order_by'), fn ($q) => $q->orderBy(
                $request->string('order_by')->value(),
                $request->string('order_direction', 'desc')->value()
            ))
            ->latest()
            ->paginate(30);

        return ApiResponse::collection(
            key: 'journal_entries',
            resource: JournalEntryResource::collection($entries),
            paginator: $entries,
        );
    }

    #[Endpoint(
        title: 'Recognize Revenue',
        description: 'Simulates month-end processing. Recognizes revenue for all paid invoices where the service period has ended. Creates journal entry: DR Deferred Revenue → CR Subscription Revenue.',
    )]
    public function recognizeRevenue(): Responsable
    {
        $result = $this->recognizeRevenueAction->execute();

        return ApiResponse::success(
            data: [
                'recognized' => $result['recognized'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors'],
            ],
            message: "Revenue recognition complete. {$result['recognized']} invoice(s) recognized.",
        );
    }
}
