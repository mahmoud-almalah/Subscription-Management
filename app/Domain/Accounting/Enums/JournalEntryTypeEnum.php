<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Enums;

enum JournalEntryTypeEnum: string
{
    case INVOICE_CREATED = 'invoice_created';
    case PAYMENT_RECEIVED = 'payment_received';
    case REVENUE_RECOGNIZED = 'revenue_recognized';

    public function getLabel(): string
    {
        return match ($this) {
            self::INVOICE_CREATED => 'Invoice Created',
            self::PAYMENT_RECEIVED => 'Payment Received',
            self::REVENUE_RECOGNIZED => 'Revenue Recognized',
        };
    }

    public function isInvoiceCreated(): bool
    {
        return $this === self::INVOICE_CREATED;
    }

    public function isPaymentReceived(): bool
    {
        return $this === self::PAYMENT_RECEIVED;
    }

    public function isRevenueRecognized(): bool
    {
        return $this === self::REVENUE_RECOGNIZED;
    }
}
