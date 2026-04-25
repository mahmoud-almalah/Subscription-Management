<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            TenantSeeder::class, // Tenants + Users + Chart of Accounts
            PlanSeeder::class, // Plans For All Tenant
            CustomerSeeder::class, // Customers For All Tenant
            SubscriptionSeeder::class, // Subscriptions + Invoices + Payments + Journal Entries
        ]);

        $this->command->newLine();

        $this->command->info('Sample Users:');

        $this->command->table(
            ['Tenant', 'Email', 'Password', 'Status'],
            [
                ['Acme Corp', 'acme-corp@gmail.com', 'password123', 'active'],
                ['Acme Corp', 'bob@gmail.com', 'password123', 'active (user)'],
                ['Globex Inc', 'globex-inc@gmail.com', 'password123', 'active'],
                ['Initech', 'initech@gmail.com', 'password123', 'suspended'],
            ]
        );

        $this->command->newLine();
    }
}
