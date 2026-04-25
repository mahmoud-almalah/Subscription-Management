<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Subscription\Models\Plan;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class PlanSeeder extends Seeder
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

        $plans = [
            [
                'tenant_id' => $acme->id,
                'name' => 'Basic',
                'description' => 'Basic plan for small businesses',
                'price' => 9.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'features' => ['5 Users', '100 Invoices', 'Email Support'],
            ],
            [
                'tenant_id' => $acme->id,
                'name' => 'Pro',
                'description' => 'Professional plan with advanced features',
                'price' => 29.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'features' => ['25 Users', 'Unlimited Invoices', 'Priority Support', 'API Access'],
            ],
            [
                'tenant_id' => $acme->id,
                'name' => 'Enterprise',
                'description' => 'Enterprise plan for large organizations',
                'price' => 299.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'features' => ['Unlimited Users', 'Unlimited Invoices', '24/7 Support', 'API Access', 'Custom Integrations'],
            ],
            [
                'tenant_id' => $techstart->id,
                'name' => 'Starter',
                'description' => 'Starter plan for new businesses',
                'price' => 19.99,
                'currency' => 'EUR',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'features' => ['10 Users', '500 Invoices', 'Email Support'],
            ],
            [
                'tenant_id' => $techstart->id,
                'name' => 'Business',
                'description' => 'Business plan for growing companies',
                'price' => 59.99,
                'currency' => 'EUR',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'features' => ['50 Users', 'Unlimited Invoices', 'Priority Support', 'API Access'],
            ],
            [
                'tenant_id' => $saudiDigital->id,
                'name' => 'أساسي',
                'description' => 'الخطة الأساسية',
                'price' => 37.50,
                'currency' => 'SAR',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'features' => ['5 مستخدمين', '100 فاتورة', 'دعم عبر البريد'],
            ],
            [
                'tenant_id' => $saudiDigital->id,
                'name' => 'متميز',
                'description' => 'الخطة المميزة',
                'price' => 112.50,
                'currency' => 'SAR',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'features' => ['25 مستخدم', 'فاتورات غير محدودة', 'دعم أولوية', 'وصول API'],
            ],
        ];

        foreach ($plans as $planData) {
            Plan::query()->updateOrCreate(
                [
                    'tenant_id' => $planData['tenant_id'],
                    'name' => $planData['name'],
                ],
                $planData
            );
        }
    }
}
