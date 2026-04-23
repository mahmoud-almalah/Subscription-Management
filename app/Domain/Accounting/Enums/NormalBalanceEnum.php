<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Enums;

enum NormalBalanceEnum: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEBIT => 'Debit',
            self::CREDIT => 'Credit',
        };
    }

    public function isDebit(): bool
    {
        return $this === self::DEBIT;
    }

    public function isCredit(): bool
    {
        return $this === self::CREDIT;
    }
}
