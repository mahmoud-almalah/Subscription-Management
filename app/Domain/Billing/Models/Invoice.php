<?php

declare(strict_types=1);

namespace App\Domain\Billing\Models;

use App\Domain\Billing\Enums\InvoiceStatusEnum;
use App\Domain\Shared\Concerns\HasTenant;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Database\Factories\InvoiceFactory;
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
 * @property string $subscription_id
 * @property string $customer_id
 * @property string $invoice_number
 * @property float $amount
 * @property string $currency
 * @property InvoiceStatusEnum $status
 * @property Carbon $period_start
 * @property Carbon $period_end
 * @property Carbon $due_date
 * @property Carbon|null $paid_at
 * @property Carbon|null $revenue_recognized_at
 * @property string|null $notes
 * @property-read Tenant $tenant
 * @property-read Subscription $subscription
 * @property-read Customer $customer
 * @property-read Collection<int, Payment> $payments
 */
#[Table(name: 'invoices', keyType: 'string', incrementing: false)]
#[Fillable([
    'tenant_id', 'subscription_id', 'customer_id', 'invoice_number', 'amount',
    'currency', 'status', 'period_start', 'period_end', 'due_date', 'paid_at',
    'revenue_recognized_at', 'notes',
])]
final class Invoice extends Model
{
    use HasFactory, HasTenant, HasUlids;

    /* @return array<string, string> */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'revenue_recognized_at' => 'datetime',
            'status' => InvoiceStatusEnum::class,
            'amount' => 'float',
        ];
    }

    public function isPaid(): bool
    {
        return $this->status->isPaid() && $this->paid_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->status->isOverdue() && $this->due_date->isPast() && ! $this->isPaid();
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => InvoiceStatusEnum::PAID,
            'paid_at' => now(),
        ]);
    }

    /* @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(related: Tenant::class, foreignKey: 'tenant_id', ownerKey: 'id');
    }

    /* @return BelongsTo<Subscription, $this> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(related: Subscription::class, foreignKey: 'subscription_id', ownerKey: 'id');
    }

    /* @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(related: Customer::class, foreignKey: 'customer_id', ownerKey: 'id');
    }

    /* @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(related: Payment::class, foreignKey: 'invoice_id', localKey: 'id');
    }

    protected static function newFactory(): InvoiceFactory
    {
        return InvoiceFactory::new();
    }
}
