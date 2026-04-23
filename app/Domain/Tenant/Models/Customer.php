<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Models;

use App\Domain\Tenant\Casts\LocationCast;
use App\Domain\Tenant\Collections\CustomerMetadataCollection;
use App\Domain\Tenant\Data\CustomerMetadataData;
use App\Domain\Tenant\Data\LocationData;
use App\Domain\Tenant\Enums\CustomerStatusEnum;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property LocationData $address
 * @property CustomerStatusEnum $status
 * @property CustomerMetadataCollection<int, CustomerMetadataData> $metadata
 * @property-read Tenant $tenant
 */
#[Table(name: 'customers', keyType: 'string', incrementing: false)]
#[Fillable(['tenant_id', 'name', 'email', 'phone', 'address', 'status', 'metadata'])]
final class Customer extends Model
{
    use HasFactory, HasUlids;

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

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
