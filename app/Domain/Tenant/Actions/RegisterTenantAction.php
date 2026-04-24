<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Actions;

use App\Domain\Tenant\Enums\TenantStatusEnum;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

final class RegisterTenantAction
{
    public function __construct(
        private CreateDefaultChartOfAccountsAction $createAccounts,
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
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            [
                'tenant_name' => $tenantName,
                'tenant_email' => $tenantEmail,
                'tenant_slug' => $tenantSlug,
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ] = $data;

            // 1. Create Tenant
            $tenant = Tenant::create([
                'name' => $tenantName,
                'email' => $tenantEmail,
                'slug' => $tenantSlug,
                'status' => TenantStatusEnum::ACTIVE,
            ]);

            // 2. Create Admin User
            $user = $tenant->users()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'admin',
            ]);

            // 3. Create Default Chart of Accounts
            $this->createAccounts->execute($tenant);

            // 4. Generate API Token
            $token = $user->createToken('api')->plainTextToken;

            return [
                'tenant' => $tenant,
                'user' => $user,
                'token' => $token,
            ];
        });
    }
}
