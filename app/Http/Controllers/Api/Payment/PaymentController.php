<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Payment;

use App\Domain\Billing\Models\Invoice;
use App\Domain\Billing\Models\Payment;
use App\Domain\Billing\Services\PaymentService;
use App\Http\Requests\Payment\FilterPaymentRequest;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\Payment\PaymentResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Support\Responsable;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Payments')]
#[Authenticated]
final class PaymentController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    #[Endpoint(
        title: 'List Payments',
        description: 'Retrieve a paginated list of payments with optional filtering.',
    )]
    public function index(FilterPaymentRequest $request): Responsable
    {
        $payments = Payment::query()
            ->when($request->filled('invoice_id'), fn ($q) => $q->where('invoice_id', $request->string('invoice_id')->value()))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->string('customer_id')->value()))
            ->when($request->filled('amount_from'), fn ($q) => $q->where('amount', '>=', $request->float('amount_from')))
            ->when($request->filled('amount_to'), fn ($q) => $q->where('amount', '<=', $request->float('amount_to')))
            ->when($request->filled('currency'), fn ($q) => $q->where('currency', $request->string('currency')->value()))
            ->when($request->filled('payment_methods'), fn ($q) => $q->whereIn('payment_method', $request->array('payment_methods')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('payment_date', '>=', $request->date('date_from')->format('Y-m-d')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('payment_date', '<=', $request->date('date_to')->format('Y-m-d')))
            ->when($request->filled('order_by'), fn ($q) => $q->orderBy(
                $request->string('order_by')->value(),
                $request->string('order_direction', 'desc')->value()
            ))
            ->latest()
            ->paginate(30);

        return ApiResponse::collection(
            key: 'payments',
            resource: PaymentResource::collection($payments),
            paginator: $payments,
        );
    }

    #[Endpoint(
        title: 'Get Payment',
        description: 'Retrieve details of a specific payment.',
    )]
    public function show(Payment $payment): Responsable
    {
        return ApiResponse::model(
            key: 'payment',
            resource: PaymentResource::make($payment->load(['invoice', 'customer'])),
        );
    }

    #[Endpoint(
        title: 'Record Payment',
        description: 'Record a payment against a specific invoice.',
    )]
    #[BodyParam(
        name: 'amount',
        description: 'Payment amount.',
        example: '100.00'
    )]
    #[BodyParam(
        name: 'currency',
        description: '3-letter ISO currency code.',
        example: 'USD'
    )]
    #[BodyParam(
        name: 'payment_method',
        description: 'bank_transfer | cash | credit_card | other',
        example: 'bank_transfer'
    )]
    #[BodyParam(
        name: 'payment_date',
        description: 'Date of payment (Y-m-d).',
        example: '2025-01-15'
    )]
    #[BodyParam(
        name: 'reference_number',
        description: 'External reference number (optional).',
        example: 'REF-001'
    )]
    #[BodyParam(
        name: 'notes',
        description: 'Optional notes.',
        example: 'Paid via wire transfer'
    )]
    public function store(Invoice $invoice, StorePaymentRequest $request): Responsable
    {
        $payment = $this->paymentService->record($invoice, $request->validated());

        return ApiResponse::model(
            key: 'payment',
            resource: PaymentResource::make($payment->load(['invoice', 'customer'])),
            message: 'Payment recorded successfully.',
            status: 201,
        );
    }
}
