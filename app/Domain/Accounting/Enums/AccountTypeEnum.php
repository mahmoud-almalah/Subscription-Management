<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Enums;

enum AccountTypeEnum: string
{
    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case EQUITY = 'equity';
    case REVENUE = 'revenue';
    case EXPENSE = 'expense';

    public function getLabel(): string
    {
        return match ($this) {
            self::ASSET => 'Asset',
            self::LIABILITY => 'Liability',
            self::EQUITY => 'Equity',
            self::REVENUE => 'Revenue',
            self::EXPENSE => 'Expense',
        };
    }

    public function getCOAPrefix(): int
    {
        return match ($this) {
            self::ASSET => 1000,
            self::LIABILITY => 2000,
            self::EQUITY => 3000,
            self::REVENUE => 4000,
            self::EXPENSE => 5000,
        };
    }

    public function getNormalBalance(): NormalBalanceEnum
    {
        return match ($this) {
            self::ASSET, self::EXPENSE => NormalBalanceEnum::DEBIT,
            self::LIABILITY, self::EQUITY, self::REVENUE => NormalBalanceEnum::CREDIT,
        };
    }
}
