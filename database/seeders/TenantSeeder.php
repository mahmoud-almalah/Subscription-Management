<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Tenant\Actions\CreateDefaultChartOfAccountsAction;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class TenantSeeder extends Seeder
{
    public function __construct(
        private readonly CreateDefaultChartOfAccountsAction $createAccounts,
    ) {}

    public function run(): void
    {
        $acme = Tenant::create([
            'name' => 'Acme Corp',
            'slug' => 'acme-corp',
            'email' => 'billing@acme-corp.com',
            'status' => 'active',
        ]);

        User::create([
            'tenant_id' => $acme->id,
            'name' => 'Alice Johnson',
            'email' => 'acme-corp@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        User::create([
            'tenant_id' => $acme->id,
            'name' => 'Bob Smith',
            'email' => 'bob@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $this->createAccounts->execute($acme);

        $globex = Tenant::create([
            'name' => 'Globex Inc',
            'slug' => 'globex-inc',
            'email' => 'billing@globex-inc.com',
            'status' => 'active',
        ]);

        User::create([
            'tenant_id' => $globex->id,
            'name' => 'Carol White',
            'email' => 'globex-inc@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->createAccounts->execute($globex);

        $initech = Tenant::create([
            'name' => 'Initech',
            'slug' => 'initech',
            'email' => 'billing@initech.com',
            'status' => 'suspended',
        ]);

        User::create([
            'tenant_id' => $initech->id,
            'name' => 'Dave Brown',
            'email' => 'initech@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->createAccounts->execute($initech);

        $this->command->info('  Tenants seeded: Acme Corp, Globex Inc, Initech');
    }
}
