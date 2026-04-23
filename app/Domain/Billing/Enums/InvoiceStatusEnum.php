<?php

declare(strict_types=1);

namespace App\Domain\Billing\Enums;

enum InvoiceStatusEnum: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::PAID => 'Paid',
            self::OVERDUE => 'Overdue',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isSent(): bool
    {
        return $this === self::SENT;
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isOverdue(): bool
    {
        return $this === self::OVERDUE;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
}
