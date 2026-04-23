<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Models;

use App\Domain\Accounting\Enums\JournalEntryTypeEnum;
use App\Domain\Accounting\Enums\NormalBalanceEnum;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $entry_number
 * @property JournalEntryTypeEnum $type
 * @property string $reference_id
 * @property string $reference_type
 * @property string $description
 * @property Carbon $entry_date
 * @property-read Tenant $tenant
 */
#[Table(name: 'journal_entries', keyType: 'string', incrementing: false)]
#[Fillable(['tenant_id', 'entry_number', 'type', 'reference_id', 'reference_type', 'description', 'entry_date'])]
final class JournalEntry extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'type' => JournalEntryTypeEnum::class,
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
        return $this->hasMany(related: JournalLine::class, foreignKey: 'journal_entry_id', localKey: 'id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isBalanced(): bool
    {
        $totalDebits = $this->lines()->where('type', NormalBalanceEnum::DEBIT)->sum('amount');
        $totalCredits = $this->lines()->where('type', NormalBalanceEnum::CREDIT)->sum('amount');
        return abs($totalDebits - $totalCredits) < 0.01;
    }

    public function getTotalDebits(): float
    {
        return $this->lines()
            ->where('type', NormalBalanceEnum::DEBIT)
            ->sum('amount');
    }
}
