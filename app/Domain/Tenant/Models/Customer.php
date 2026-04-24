<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Models;

use App\Domain\Billing\Models\Invoice;
use App\Domain\Billing\Models\Payment;
use App\Domain\Shared\Concerns\HasTenant;
use App\Domain\Subscription\Enums\SubscriptionStatusEnum;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Tenant\Casts\LocationCast;
use App\Domain\Tenant\Collections\CustomerMetadataCollection;
use App\Domain\Tenant\Data\CustomerMetadataData;
use App\Domain\Tenant\Data\LocationData;
use App\Domain\Tenant\Enums\CustomerStatusEnum;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property LocationData $address
 * @property CustomerStatusEnum $status
 * @property CustomerMetadataCollection<int, CustomerMetadataData> $metadata
 * @property-read Tenant $tenant
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, Invoice> $invoices
 */
#[Table(name: 'customers', keyType: 'string', incrementing: false)]
#[Fillable(['tenant_id', 'name', 'email', 'phone', 'address', 'status', 'metadata', 'deleted_at'])]
final class Customer extends Model
{
    use HasFactory, HasTenant, HasUlids, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'address' => LocationCast::class,
            'metadata' => AsCollection::using(CustomerMetadataCollection::class),
            'status' => CustomerStatusEnum::class,
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(related: Tenant::class, foreignKey: 'tenant_id', ownerKey: 'id');
    }

    /* @return HasMany<Subscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(related: Subscription::class, foreignKey: 'customer_id', localKey: 'id')
            ->latest();
    }

    public function getActiveSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->first();
    }

    /* @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(related: Payment::class, foreignKey: 'customer_id', localKey: 'id')
            ->latest();
    }

    /* @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(related: Invoice::class, foreignKey: 'customer_id', localKey: 'id')
            ->latest();
    }

    public function getTotalRevenue(): float
    {
        return $this->payments()
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
