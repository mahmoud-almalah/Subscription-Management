<?php

declare(strict_types=1);

namespace App\Domain\Billing\Models;

use App\Domain\Billing\Enums\PaymentMethodEnum;
use App\Domain\Shared\Concerns\HasTenant;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $invoice_id
 * @property string $customer_id
 * @property float $amount
 * @property string $currency
 * @property PaymentMethodEnum $payment_method
 * @property Carbon $payment_date
 * @property string|null $reference_number
 * @property string|null $notes
 * @property-read Tenant $tenant
 * @property-read Invoice $invoice
 * @property-read Customer $customer
 */
#[Table(name: 'payments', keyType: 'string', incrementing: false)]
#[Fillable([
    'tenant_id', 'invoice_id', 'customer_id', 'amount', 'currency',
    'payment_method', 'payment_date', 'reference_number', 'notes',
])]
final class Payment extends Model
{
    use HasFactory, HasTenant, HasUlids;

    /* @return array<string, string> */
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'payment_method' => PaymentMethodEnum::class,
        ];
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2);
    }

    /* @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(related: Tenant::class, foreignKey: 'tenant_id', ownerKey: 'id');
    }

    /* @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(related: Invoice::class, foreignKey: 'invoice_id', ownerKey: 'id');
    }

    /* @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(related: Customer::class, foreignKey: 'customer_id', ownerKey: 'id');
    }

    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }
}
