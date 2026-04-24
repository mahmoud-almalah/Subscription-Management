<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Models;

use App\Domain\Accounting\Enums\AccountTypeEnum;
use App\Domain\Accounting\Enums\NormalBalanceEnum;
use App\Domain\Shared\Concerns\HasTenant;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $code
 * @property AccountTypeEnum $type
 * @property string $name
 * @property NormalBalanceEnum $normal_balance
 * @property bool $is_system
 * @property-read Tenant $tenant
 * @property-read Collection<int, JournalLine> $lines
 */
#[Table(name: 'accounts', keyType: 'string', incrementing: false)]
#[Fillable(['tenant_id', 'code', 'type', 'name', 'normal_balance', 'is_system'])]
final class Account extends Model
{
    use HasTenant, HasUlids;

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

    /* @return HasMany<JournalLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(related: JournalLine::class, foreignKey: 'account_id', localKey: 'id');
    }

    public function getBalance(): float
    {
        $debits = $this->lines()
            ->where('type', NormalBalanceEnum::DEBIT)
            ->sum('amount');

        $credits = $this->lines()
            ->where('type', NormalBalanceEnum::CREDIT)
            ->sum('amount');

        return match ($this->normal_balance) {
            NormalBalanceEnum::DEBIT => $debits - $credits,
            NormalBalanceEnum::CREDIT => $credits - $debits,
        };
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    #[Scope]
    protected function byType(Builder $query, AccountTypeEnum $type): Builder
    {
        return $query->where('type', $type);
    }
}
