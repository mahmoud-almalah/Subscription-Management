<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Subscription\Enums\SubscriptionStatusEnum;
use App\Domain\Subscription\Models\Plan;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Tenant\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class SubscriptionSeeder extends Seeder
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

        $acmeBasic = Plan::query()->where('tenant_id', $acme->id)->where('name', 'Basic')->first();
        $acmePro = Plan::query()->where('tenant_id', $acme->id)->where('name', 'Pro')->first();
        $techstartStarter = Plan::query()->where('tenant_id', $techstart->id)->where('name', 'Starter')->first();
        $techstartBusiness = Plan::query()->where('tenant_id', $techstart->id)->where('name', 'Business')->first();
        $saudiBasic = Plan::query()->where('tenant_id', $saudiDigital->id)->where('name', 'أساسي')->first();
        $saudiPremium = Plan::query()->where('tenant_id', $saudiDigital->id)->where('name', 'متميز')->first();

        if (! $acmeBasic || ! $acmePro || ! $techstartStarter || ! $techstartBusiness || ! $saudiBasic || ! $saudiPremium) {
            return;
        }

        $john = Customer::query()->where('tenant_id', $acme->id)->where('email', 'john.smith@example.com')->first();
        $sarah = Customer::query()->where('tenant_id', $acme->id)->where('email', 'sarah.j@company.net')->first();
        $michael = Customer::query()->where('tenant_id', $acme->id)->where('email', 'mbrown@enterprise.org')->first();
        $emma = Customer::query()->where('tenant_id', $techstart->id)->where('email', 'emma.weber@webdesign.de')->first();
        $hans = Customer::query()->where('tenant_id', $techstart->id)->where('email', 'hans.mueller@techstart.de')->first();
        $ahmed = Customer::query()->where('tenant_id', $saudiDigital->id)->where('email', 'ahmed.m@saudidigital.sa')->first();
        $khaled = Customer::query()->where('tenant_id', $saudiDigital->id)->where('email', 'khaled.o@saudidigital.sa')->first();

        $subscriptions = [
            [
                'tenant_id' => $acme->id,
                'customer_id' => $john?->id,
                'plan_id' => $acmePro->id,
                'status' => SubscriptionStatusEnum::ACTIVE,
                'started_at' => Carbon::now()->subDays(15),
                'ends_at' => Carbon::now()->addDays(20),
                'next_billing_date' => Carbon::now()->addDays(20),
            ],
            [
                'tenant_id' => $acme->id,
                'customer_id' => $sarah?->id,
                'plan_id' => $acmeBasic->id,
                'status' => SubscriptionStatusEnum::ACTIVE,
                'started_at' => Carbon::now()->subDays(5),
                'ends_at' => Carbon::now()->addDays(25),
                'next_billing_date' => Carbon::now()->addDays(25),
            ],
            [
                'tenant_id' => $acme->id,
                'customer_id' => $michael?->id,
                'plan_id' => $acmeBasic->id,
                'status' => SubscriptionStatusEnum::CANCELLED,
                'started_at' => Carbon::now()->subDays(60),
                'ends_at' => Carbon::now()->subDays(30),
                'next_billing_date' => Carbon::now()->subDays(30),
                'cancelled_at' => Carbon::now()->subDays(30),
                'cancellation_reason' => 'Customer requested cancellation',
            ],
            [
                'tenant_id' => $techstart->id,
                'customer_id' => $emma?->id,
                'plan_id' => $techstartBusiness->id,
                'status' => SubscriptionStatusEnum::ACTIVE,
                'started_at' => Carbon::now()->subDays(10),
                'ends_at' => Carbon::now()->addDays(20),
                'next_billing_date' => Carbon::now()->addDays(20),
            ],
            [
                'tenant_id' => $techstart->id,
                'customer_id' => $hans?->id,
                'plan_id' => $techstartStarter->id,
                'status' => SubscriptionStatusEnum::ACTIVE,
                'started_at' => Carbon::now()->subDays(20),
                'ends_at' => Carbon::now()->addDays(10),
                'next_billing_date' => Carbon::now()->addDays(10),
            ],
            [
                'tenant_id' => $saudiDigital->id,
                'customer_id' => $ahmed?->id,
                'plan_id' => $saudiPremium->id,
                'status' => SubscriptionStatusEnum::ACTIVE,
                'started_at' => Carbon::now()->subDays(7),
                'ends_at' => Carbon::now()->addDays(23),
                'next_billing_date' => Carbon::now()->addDays(23),
            ],
            [
                'tenant_id' => $saudiDigital->id,
                'customer_id' => $khaled?->id,
                'plan_id' => $saudiBasic->id,
                'status' => SubscriptionStatusEnum::ACTIVE,
                'started_at' => Carbon::now()->subDays(3),
                'ends_at' => Carbon::now()->addDays(27),
                'next_billing_date' => Carbon::now()->addDays(27),
            ],
        ];

        foreach ($subscriptions as $subData) {
            if (empty($subData['customer_id'])) {
                continue;
            }

            Subscription::query()->updateOrCreate(
                [
                    'tenant_id' => $subData['tenant_id'],
                    'customer_id' => $subData['customer_id'],
                    'plan_id' => $subData['plan_id'],
                ],
                $subData
            );
        }
    }
}
