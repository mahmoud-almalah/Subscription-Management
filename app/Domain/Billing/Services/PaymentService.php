<?php

declare(strict_types=1);

namespace App\Domain\Billing\Services;

use App\Domain\Accounting\Services\AccountingService;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Billing\Models\Payment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PaymentService
{
    public function __construct(
        private readonly AccountingService $accountingService,
    ) {}

    /**
     * @param array{
     *     amount: float,
     *     currency: string,
     *     payment_method: string,
     *     payment_date: string,
     *     reference_number: string|null,
     *     notes: string|null,
     * } $data
     */
    public function record(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data): Payment {
            [
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
            ] = $data;

            $this->validatePaymentAmount($invoice, $amount);

            $payment = Payment::create([
                'tenant_id' => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount' => $amount,
                'currency' => $currency ?? $invoice->currency,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate ?? now()->toDateString(),
                'reference_number' => $referenceNumber ?? null,
                'notes' => $notes ?? null,
            ]);

            // Update invoice status if fully paid
            $invoice->markAsPaid();

            // Create corresponding accounting entries for the payment
            $this->accountingService->createPaymentEntries($payment);

            return $payment;
        });
    }

    private function validatePaymentAmount(Invoice $invoice, float $amount): void
    {
        throw_if(
            $invoice->isPaid(),
            new InvalidArgumentException("Invoice {$invoice->invoice_number} is already paid.")
        );

        throw_if(
            $amount <= 0,
            new InvalidArgumentException('Payment amount must be greater than zero.')
        );

        throw_if(
            $amount > $invoice->amount,
            new InvalidArgumentException("Payment amount ({$amount}) exceeds invoice amount ({$invoice->amount}).")
        );
    }
}
