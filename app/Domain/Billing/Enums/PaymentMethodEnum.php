<?php

declare(strict_types=1);

namespace App\Domain\Billing\Enums;

enum PaymentMethodEnum: string
{
    case BANK_TRANSFER = 'bank_transfer';
    case CASH = 'cash';
    case CREDIT_CARD = 'credit_card';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CASH => 'Cash',
            self::CREDIT_CARD => 'Credit Card',
            self::OTHER => 'Other',
        };
    }

    public function isBankTransfer(): bool
    {
        return $this === self::BANK_TRANSFER;
    }

    public function isCash(): bool
    {
        return $this === self::CASH;
    }

    public function isCreditCard(): bool
    {
        return $this === self::CREDIT_CARD;
    }

    public function isOther(): bool
    {
        return $this === self::OTHER;
    }
}
