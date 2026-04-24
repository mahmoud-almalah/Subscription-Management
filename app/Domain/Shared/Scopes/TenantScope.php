<?php

declare(strict_types=1);

namespace App\Domain\Shared\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth('user')->check()) {
            return;
        }

        $builder->where(
            column: 'tenant_id',
            operator: '=',
            value: auth('user')->user()->tenant_id,
        );
    }
}
