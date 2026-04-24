<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Invoice;

use App\Domain\Billing\Enums\InvoiceStatusEnum;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Billing\Services\InvoiceService;
use App\Http\Requests\Invoice\FilterInvoiceRequest;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Support\Responsable;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Invoices')]
#[Authenticated]
final class InvoiceController
{
    #[Endpoint(
        title: 'List Invoices',
        description: 'Retrieve a paginated list of invoices with optional filtering and sorting.',
    )]
    #[BodyParam(
        name: 'subscriptions_ids',
        description: 'Filter invoices by an array of subscription IDs.',
        example: '["01F8MECHZX3TBDSZ7XRADM79XV", "01F8MECHZX3TBDSZ7XRADM79XW"]'
    )]
    #[BodyParam(
        name: 'amount_from',
        description: 'Filter invoices with an amount greater than or equal to this value.',
        example: '10.00'
    )]
    #[BodyParam(
        name: 'amount_to',
        description: 'Filter invoices with an amount less than or equal to this value.',
        example: '100.00'
    )]
    #[BodyParam(
        name: 'currency',
        description: 'Filter invoices by currency (3-letter ISO code).',
        example: 'USD'
    )]
    #[BodyParam(
        name: 'statuses',
        description: 'Filter invoices by an array of statuses.',
        example: '["paid", "pending"]'
    )]
    #[BodyParam(
        name: 'period_start',
        description: 'Filter invoices with a period start date greater than or equal to this value (Y-m-d).',
        example: '2024-01-01'
    )]
    #[BodyParam(
        name: 'period_end',
        description: 'Filter invoices with a period end date less than or equal to this value (Y-m-d).',
        example: '2024-12-31'
    )]
    #[BodyParam(
        name: 'is_paid',
        description: 'Filter invoices based on payment status (true for paid, false for unpaid).',
        example: 'true'
    )]
    #[BodyParam(
        name: 'order_by',
        description: 'Field to sort by (amount, created_at, due_date, period_start, period_end).',
        example: 'created_at'
    )]
    #[BodyParam(
        name: 'order_direction',
        description: 'Sort direction (asc or desc).',
        example: 'desc'
    )]
    public function index(FilterInvoiceRequest $request): Responsable
    {
        $invoices = Invoice::query()
            ->when($request->filled('subscriptions_ids'), function ($query) use ($request) {
                $query->whereIn('subscription_id', $request->array('subscriptions_ids'));
            })
            ->when($request->filled('amount_from'), function ($query) use ($request) {
                $query->where('amount', '>=', $request->float('amount_from'));
            })
            ->when($request->filled('amount_to'), function ($query) use ($request) {
                $query->where('amount', '<=', $request->float('amount_to'));
            })
            ->when($request->filled('currency'), function ($query) use ($request) {
                $query->where('currency', $request->string('currency')->value());
            })
            ->when($request->filled('statuses'), function ($query) use ($request) {
                $query->whereIn('status', $request->array('statuses'));
            })
            ->when($request->filled('period_start'), function ($query) use ($request) {
                $query->whereDate('period_start', '>=', $request->date('period_start')->format('Y-m-d'));
            })
            ->when($request->filled('period_end'), function ($query) use ($request) {
                $query->whereDate('period_end', '<=', $request->date('period_end')->format('Y-m-d'));
            })
            ->when($request->filled('is_paid'), function ($query) use ($request) {
                if ($request->boolean('is_paid')) {
                    $query->where('status', InvoiceStatusEnum::PAID)
                        ->whereNotNull('paid_at');
                } else {
                    $query->whereNot('status', InvoiceStatusEnum::PAID)
                        ->orWhereNull('paid_at');
                }
            })
            ->when($request->filled('order_by'), function ($query) use ($request) {
                $query->orderBy(
                    $request->string('order_by')->value(),
                    $request->string('order_direction', 'asc')->value()
                );
            })
            ->paginate(30);

        return ApiResponse::collection(
            key: 'invoices',
            resource: InvoiceResource::collection($invoices),
            paginator: $invoices,
        );
    }

    #[Endpoint(
        title: 'Get Invoice',
        description: 'Retrieve details of a specific invoice.',
    )]
    public function show(Invoice $invoice): Responsable
    {
        return ApiResponse::model(
            key: 'invoice',
            resource: InvoiceResource::make($invoice->load([
                'subscription.plan',
                'payments',
                'customer',
            ])),
        );
    }

    #[Endpoint(
        title: 'Generate Monthly Invoices',
        description: 'Generate monthly invoices for a given subscription.',
    )]
    public function generateMonthlyInvoices(Invoice $invoice): Responsable
    {
        app(InvoiceService::class)
            ->generate($invoice->subscription);

        return ApiResponse::success(message: 'Monthly invoices generated successfully.');
    }
}
