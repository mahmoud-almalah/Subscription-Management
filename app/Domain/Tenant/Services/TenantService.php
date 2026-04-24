<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Actions\RegisterTenantAction;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\User;
use Throwable;

final class TenantService
{
    public function __construct(
        private RegisterTenantAction $registerAction,
    ) {}

    /**
     * @param array{
     *     tenant_name: string,
     *     tenant_email: string,
     *     tenant_slug: string,
     *     name: string,
     *     email: string,
     *     password: string,
     * } $data
     * @return array{tenant: Tenant, user: User, token: string}
     *
     * @throws Throwable
     */
    public function register(array $data): array
    {
        return $this->registerAction->execute($data);
    }
}
