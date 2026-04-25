<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Tenant\Collections\CustomerMetadataCollection;
use App\Domain\Tenant\Data\CustomerMetadataData;
use App\Domain\Tenant\Data\LocationData;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;

final class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $acme = Tenant::withoutGlobalScopes()->where('slug', 'acme-corp')->first();
        $globex = Tenant::withoutGlobalScopes()->where('slug', 'globex-inc')->first();

        $acmeCustomers = [
            ['name' => 'Sarah Connor', 'email' => 'sarah@connor.com', 'phone' => '+1-555-0101'],
            ['name' => 'John Wick', 'email' => 'john@continental.com', 'phone' => '+1-555-0102'],
            ['name' => 'Tony Stark', 'email' => 'tony@starkinc.com', 'phone' => '+1-555-0103'],
            ['name' => 'Bruce Wayne', 'email' => 'bruce@wayneent.com', 'phone' => '+1-555-0104'],
            ['name' => 'Peter Parker', 'email' => 'peter@dailybugle.com', 'phone' => '+1-555-0105'],
            ['name' => 'Diana Prince', 'email' => 'diana@themyscira.com', 'phone' => '+1-555-0106'],
            ['name' => 'Steve Rogers', 'email' => 'steve@shield.gov', 'phone' => '+1-555-0107'],
            ['name' => 'Natasha Romanov', 'email' => 'nat@shield.gov', 'phone' => '+1-555-0108'],
        ];

        $globexCustomers = [
            ['name' => 'Walter White', 'email' => 'walter@greymat.com', 'phone' => '+1-555-0201'],
            ['name' => 'Jesse Pinkman', 'email' => 'jesse@chem101.com', 'phone' => '+1-555-0202'],
            ['name' => 'Saul Goodman', 'email' => 'saul@bettercall.com', 'phone' => '+1-555-0203'],
            ['name' => 'Mike Ehrmantraut', 'email' => 'mike@cleaner.com', 'phone' => '+1-555-0204'],
            ['name' => 'Gus Fring', 'email' => 'gus@pollos.com', 'phone' => '+1-555-0205'],
        ];

        $this->createCustomers($acme, $acmeCustomers);
        $this->createCustomers($globex, $globexCustomers);

        $this->command->info('  Customers seeded: 8 for Acme, 5 for Globex');
    }

    private function createCustomers(Tenant $tenant, array $customers): void
    {
        foreach ($customers as $data) {
            Customer::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'status' => 'active',
                'address' => LocationData::fromArray([
                    'lat' => fake()->latitude(),
                    'lng' => fake()->longitude(),
                    'address' => fake()->streetAddress(),
                ]),
                'metadata' => CustomerMetadataCollection::make([
                    CustomerMetadataData::fromArray([
                        'key' => 'source',
                        'value' => fake()->randomElement(['website', 'referral', 'direct']),
                        'type' => 'string',
                        'description' => 'Acquisition source',
                    ]),
                ]),
            ]);
        }
    }
}
