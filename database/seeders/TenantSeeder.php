<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Tenant\Enums\TenantStatusEnum;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class TenantSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tenants = [
            [
                'name' => 'Acme Corporation',
                'slug' => 'acme-corp',
                'email' => 'admin@acmecorp.com',
                'status' => TenantStatusEnum::ACTIVE,
                'settings' => [
                    'currency' => 'USD',
                    'timezone' => 'America/New_York',
                ],
                'user' => [
                    'name' => 'Admin User',
                    'email' => 'acmecorp@gmail.com',
                    'password' => 'password123',
                    'role' => 'admin',
                ],
            ],
            [
                'name' => 'TechStart GmbH',
                'slug' => 'techstart',
                'email' => 'hello@techstart.de',
                'status' => TenantStatusEnum::ACTIVE,
                'settings' => [
                    'currency' => 'EUR',
                    'timezone' => 'Europe/Berlin',
                ],
                'user' => [
                    'name' => 'TechStart Admin',
                    'email' => 'techstart@gmail.com',
                    'password' => 'password123',
                    'role' => 'admin',
                ]
            ],
            [
                'name' => 'Saudi Digital Co',
                'slug' => 'saudi-digital',
                'email' => 'info@saudidigital.sa',
                'status' => TenantStatusEnum::ACTIVE,
                'settings' => [
                    'currency' => 'SAR',
                    'timezone' => 'Asia/Riyadh',
                ],
                'user' => [
                    'name' => 'Saudi Digital Admin',
                    'email' => 'saudi-digital@gmail.com',
                    'password' => 'password123',
                    'role' => 'admin',
                ]
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::updateOrCreate([
                'slug' => $tenantData['slug'],
            ], [
                'name' => $tenantData['name'],
                'slug' => $tenantData['slug'],
                'email' => $tenantData['email'],
                'status' => $tenantData['status'],
                'settings' => $tenantData['settings'],
            ]);

            $tenant->users()->updateOrCreate([
                'email' => $tenantData['user']['email'],
            ], [
                'name' => $tenantData['user']['name'],
                'email' => $tenantData['user']['email'],
                'password' => bcrypt($tenantData['user']['password']),
                'role' => $tenantData['user']['role'],
            ]);
        }
    }
}
