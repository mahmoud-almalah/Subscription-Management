<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Subscription\Models\Plan;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;

final class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::withoutGlobalScopes()->get();

        $plans = [
            [
                'name' => 'Starter',
                'description' => 'Perfect for small teams just getting started.',
                'price' => 29.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'features' => ['Up to 5 users', '10GB storage', 'Email support'],
            ],
            [
                'name' => 'Professional',
                'description' => 'For growing businesses with advanced needs.',
                'price' => 99.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'features' => ['Up to 25 users', '100GB storage', 'Priority support', 'API access'],
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Unlimited scale for large organizations.',
                'price' => 299.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'features' => ['Unlimited users', '1TB storage', 'Dedicated support', 'API access', 'Custom integrations'],
            ],
            [
                'name' => 'Starter Annual',
                'description' => 'Starter plan billed annually — 2 months free.',
                'price' => 290.00,
                'currency' => 'USD',
                'billing_cycle' => 'yearly',
                'is_active' => true,
                'features' => ['Up to 5 users', '10GB storage', 'Email support'],
            ],
            [
                'name' => 'Legacy Basic',
                'description' => 'Deprecated plan — no longer available for new signups.',
                'price' => 19.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'is_active' => false,
                'features' => ['Up to 2 users', '5GB storage'],
            ],
        ];

        foreach ($tenants as $tenant) {
            foreach ($plans as $plan) {
                Plan::withoutGlobalScopes()->create([
                    ...$plan,
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        $this->command->info('  Plans seeded: 5 plans × 3 tenants = 15 plans');
    }
}
