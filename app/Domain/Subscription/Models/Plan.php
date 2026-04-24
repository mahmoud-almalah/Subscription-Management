<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Models;

use App\Domain\Shared\Concerns\HasTenant;
use App\Domain\Tenant\Models\Tenant;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property string $currency // e.g., 'USD', 'EUR'
 * @property string $billing_cycle // e.g., 'monthly', 'yearly'
 * @property bool $is_active
 * @property array<string> $features
 * @property-read Tenant $tenant
 */
#[Table(name: 'plans', keyType: 'string', incrementing: false)]
#[Fillable(['tenant_id', 'name', 'description', 'price', 'currency', 'billing_cycle', 'is_active', 'features'])]
final class Plan extends Model
{
    use HasFactory, HasTenant, HasUlids;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'features' => 'array',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(related: Tenant::class, foreignKey: 'tenant_id', ownerKey: 'id');
    }

    public function getMonthlyPrice(): float
    {
        return match ($this->billing_cycle) {
            'yearly' => $this->price / 12,
            default => $this->price,
        };
    }

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }

    /**
     * @param  Builder<Plan>  $query
     * @return Builder<Plan>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
