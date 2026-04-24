<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Models;

use App\Domain\Billing\Models\Invoice;
use App\Domain\Shared\Concerns\HasTenant;
use App\Domain\Subscription\Enums\SubscriptionStatusEnum;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $customer_id
 * @property string $plan_id
 * @property SubscriptionStatusEnum $status
 * @property Carbon $started_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $next_billing_date
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property-read Tenant $tenant
 * @property-read Customer $customer
 * @property-read Plan $plan
 * @property-read Collection<int, Invoice> $invoices
 */
#[Table(name: 'subscriptions', keyType: 'string', incrementing: false)]
#[Fillable([
    'tenant_id', 'customer_id', 'plan_id', 'status', 'started_at', 'ends_at',
    'next_billing_date', 'cancelled_at', 'cancellation_reason',
])]
final class Subscription extends Model
{
    use HasFactory, HasTenant, HasUlids;

    /* @return array<string, string> */
    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ends_at' => 'date',
            'next_billing_date' => 'date',
            'cancelled_at' => 'timestamp',
            'status' => SubscriptionStatusEnum::class,
        ];
    }

    public function isActive(): bool
    {
        return $this->status->isActive()
            && $this->started_at->isPast()
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => SubscriptionStatusEnum::CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function getNextBillingDate(): ?Carbon
    {
        if ($this->status->isCancelled() || $this->next_billing_date === null) {
            return null;
        }

        return $this->next_billing_date;
    }

    /* @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(related: Tenant::class, foreignKey: 'tenant_id', ownerKey: 'id');
    }

    /* @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(related: Customer::class, foreignKey: 'customer_id', ownerKey: 'id');
    }

    /* @return BelongsTo<Plan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(related: Plan::class, foreignKey: 'plan_id', ownerKey: 'id');
    }

    /* @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'subscription_id', 'id')
            ->latest();
    }

    protected static function newFactory(): SubscriptionFactory
    {
        return SubscriptionFactory::new();
    }
}
