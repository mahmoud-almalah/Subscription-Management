<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Tenant\Data\CustomerMetadataData;
use App\Domain\Tenant\Data\LocationData;
use App\Domain\Tenant\Enums\CustomerStatusEnum;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class CustomerSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $acme = Tenant::query()->where('slug', 'acme-corp')->first();
        $techstart = Tenant::query()->where('slug', 'techstart')->first();
        $saudiDigital = Tenant::query()->where('slug', 'saudi-digital')->first();

        if (! $acme || ! $techstart || ! $saudiDigital) {
            return;
        }

        $customersData = [
            [
                'tenant_id' => $acme->id,
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1-555-0101',
                'address' => LocationData::fromArray([
                    'lat' => 40.7128,
                    'lng' => -74.0060,
                    'address' => '123 Broadway, New York, NY 10001',
                ]),
                'status' => CustomerStatusEnum::ACTIVE,
            ],
            [
                'tenant_id' => $acme->id,
                'name' => 'Sarah Johnson',
                'email' => 'sarah.j@company.net',
                'phone' => '+1-555-0102',
                'address' => LocationData::fromArray([
                    'lat' => 34.0522,
                    'lng' => -118.2437,
                    'address' => '456 Sunset Blvd, Los Angeles, CA 90028',
                ]),
                'status' => CustomerStatusEnum::ACTIVE,
            ],
            [
                'tenant_id' => $acme->id,
                'name' => 'Michael Brown',
                'email' => 'mbrown@enterprise.org',
                'phone' => '+1-555-0103',
                'address' => LocationData::fromArray([
                    'lat' => 41.8781,
                    'lng' => -87.6298,
                    'address' => '789 Lake Shore Dr, Chicago, IL 60611',
                ]),
                'status' => CustomerStatusEnum::INACTIVE,
            ],
            [
                'tenant_id' => $techstart->id,
                'name' => 'Emma Weber',
                'email' => 'emma.weber@webdesign.de',
                'phone' => '+49-30-12345678',
                'address' => LocationData::fromArray([
                    'lat' => 52.5200,
                    'lng' => 13.4050,
                    'address' => 'Unter den Linden 10, 10117 Berlin',
                ]),
                'status' => CustomerStatusEnum::ACTIVE,
            ],
            [
                'tenant_id' => $techstart->id,
                'name' => 'Hans Mueller',
                'email' => 'hans.mueller@techstart.de',
                'phone' => '+49-89-98765432',
                'address' => LocationData::fromArray([
                    'lat' => 48.1351,
                    'lng' => 11.5820,
                    'address' => 'Maximilianstraße 35, 80539 München',
                ]),
                'status' => CustomerStatusEnum::ACTIVE,
            ],
            [
                'tenant_id' => $saudiDigital->id,
                'name' => 'Ahmed Mohammed',
                'email' => 'ahmed.m@saudidigital.sa',
                'phone' => '+966-50-1234567',
                'address' => LocationData::fromArray([
                    'lat' => 24.7136,
                    'lng' => 46.6753,
                    'address' => 'Riyadh, Al-Aziziyah',
                ]),
                'status' => CustomerStatusEnum::ACTIVE,
            ],
            [
                'tenant_id' => $saudiDigital->id,
                'name' => 'Khaled Omar',
                'email' => 'khaled.o@saudidigital.sa',
                'phone' => '+966-55-9876543',
                'address' => LocationData::fromArray([
                    'lat' => 21.5433,
                    'lng' => 39.1728,
                    'address' => 'Jeddah, Al-Rawdah',
                ]),
                'status' => CustomerStatusEnum::ACTIVE,
            ],
        ];

        $metadataMap = [
            'john.smith@example.com' => new CustomerMetadataData(
                key: 'loyalty_points',
                value: '1500',
                type: 'integer',
                description: 'Loyalty points earned'
            ),
            'sarah.j@company.net' => new CustomerMetadataData(
                key: 'loyalty_points',
                value: '750',
                type: 'integer',
                description: 'Loyalty points earned'
            ),
            'mbrown@enterprise.org' => new CustomerMetadataData(
                key: 'loyalty_points',
                value: '200',
                type: 'integer',
                description: 'Loyalty points earned'
            ),
            'emma.weber@webdesign.de' => new CustomerMetadataData(
                key: 'loyalty_points',
                value: '500',
                type: 'integer',
                description: 'Loyalty points earned'
            ),
            'hans.mueller@techstart.de' => new CustomerMetadataData(
                key: 'loyalty_points',
                value: '3200',
                type: 'integer',
                description: 'Loyalty points earned'
            ),
            'ahmed.m@saudidigital.sa' => new CustomerMetadataData(
                key: 'loyalty_points',
                value: '1000',
                type: 'integer',
                description: 'نقاط الولاء'
            ),
            'khaled.o@saudidigital.sa' => new CustomerMetadataData(
                key: 'loyalty_points',
                value: '2500',
                type: 'integer',
                description: 'نقاط الولاء'
            ),
        ];

        foreach ($customersData as $data) {
            $email = $data['email'];
            $customer = Customer::query()->updateOrCreate(
                ['tenant_id' => $data['tenant_id'], 'email' => $email],
                $data
            );

            if (isset($metadataMap[$email])) {
                $customer->metadata = collect([$metadataMap[$email]]);
                $customer->save();
            }
        }
    }
}
