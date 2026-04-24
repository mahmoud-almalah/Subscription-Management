<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Actions;

use App\Domain\Accounting\Enums\AccountTypeEnum;
use App\Domain\Accounting\Enums\NormalBalanceEnum;
use App\Domain\Accounting\Models\Account;
use App\Domain\Tenant\Models\Tenant;

final class CreateDefaultChartOfAccountsAction
{
    private const array DEFAULT_ACCOUNTS = [
        [
            'code' => '1001',
            'name' => 'Cash',
            'type' => AccountTypeEnum::ASSET->value,
            'normal_balance' => NormalBalanceEnum::DEBIT->value,
            'is_system' => true,
        ],
        [
            'code' => '1002',
            'name' => 'Accounts Receivable',
            'type' => AccountTypeEnum::ASSET->value,
            'normal_balance' => NormalBalanceEnum::DEBIT->value,
            'is_system' => true,
        ],
        [
            'code' => '2001',
            'name' => 'Deferred Revenue',
            'type' => AccountTypeEnum::LIABILITY,
            'normal_balance' => NormalBalanceEnum::CREDIT->value,
            'is_system' => true,
        ],
        [
            'code' => '4001',
            'name' => 'Subscription Revenue',
            'type' => AccountTypeEnum::REVENUE,
            'normal_balance' => NormalBalanceEnum::CREDIT->value,
            'is_system' => true,
        ],
    ];

    public function execute(Tenant $tenant): void
    {
        $accounts = array_map(
            static fn (array $account): array => [
                ...$account,
                'tenant_id' => $tenant->id,
                'type' => data_get($account, 'type'),
                'normal_balance' => data_get($account, 'normal_balance'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            self::DEFAULT_ACCOUNTS,
        );

        Account::insert($accounts);
    }
}
