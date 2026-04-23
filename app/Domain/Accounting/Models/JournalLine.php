<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Models;

use App\Domain\Accounting\Enums\NormalBalanceEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $journal_entry_id
 * @property string $account_id
 * @property NormalBalanceEnum $type
 * @property float $amount
 * @property string|null $description
 * @property-read JournalEntry $entry
 * @property-read Account $account
 */
#[Table(name: 'journal_lines', keyType: 'string', incrementing: false)]
#[Fillable(['journal_entry_id', 'account_id', 'type', 'amount', 'description'])]
final class JournalLine extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'type' => NormalBalanceEnum::class,
        ];
    }

    /* @return BelongsTo<JournalEntry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(related: JournalEntry::class, foreignKey: 'journal_entry_id', ownerKey: 'id');
    }

    /* @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(related: Account::class, foreignKey: 'account_id', ownerKey: 'id');
    }
}
