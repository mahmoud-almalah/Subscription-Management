<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Models;

use App\Domain\Tenant\Casts\TenantSettingCast;
use App\Domain\Tenant\Data\TenantSettingData;
use App\Domain\Tenant\Enums\TenantStatusEnum;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $email
 * @property TenantStatusEnum $status
 * @property TenantSettingData $settings
 * @property-read Collection<int, Customer> $customers
 * @property-read Collection<int, User> $users
 */
#[Table(name: 'tenants', keyType: 'string', incrementing: false)]
#[Fillable(['name', 'slug', 'email', 'status', 'settings'])]
final class Tenant extends Model
{
    use HasFactory, HasUlids;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'settings' => TenantSettingCast::class,
            'status' => TenantStatusEnum::class,
        ];
    }

    /** @return HasMany<Customer, $this> */
    public function customers(): HasMany
    {
        return $this->hasMany(related: Customer::class, foreignKey: 'tenant_id', localKey: 'id');
    }

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(related: User::class, foreignKey: 'tenant_id', localKey: 'id');
    }

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }
}
