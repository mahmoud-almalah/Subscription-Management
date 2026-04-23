<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Models;

use App\Domain\Accounting\Enums\AccountTypeEnum;
use App\Domain\Accounting\Enums\NormalBalanceEnum;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $code
 * @property AccountTypeEnum $type
 * @property string $name
 * @property NormalBalanceEnum $normal_balance
 * @property bool $is_system
 * @property-read Tenant $tenant
 */
#[Table(name: 'accounts', keyType: 'string', incrementing: false)]
#[Fillable(['tenant_id', 'code', 'type', 'name', 'normal_balance', 'is_system'])]
final class Account extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'type' => AccountTypeEnum::class,
            'normal_balance' => NormalBalanceEnum::class,
        ];
    }

    /* @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(related: Tenant::class, foreignKey: 'tenant_id', ownerKey: 'id');
    }
}
