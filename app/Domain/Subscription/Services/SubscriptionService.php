<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Services;

use App\Domain\Subscription\Enums\SubscriptionStatusEnum;
use App\Domain\Subscription\Models\Plan;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Tenant\Models\Customer;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final class SubscriptionService
{
    /**
     * @param array{
     *     customer_id: string,
     *     plan_id: string,
     *     started_at: string|null,
     * } $data
     *
     * @throws Throwable
     */
    public function create(array $data, string $tenantId): Subscription
    {
        return DB::transaction(function () use ($data, $tenantId): Subscription {
            [
                'customer_id' => $customerId,
                'plan_id' => $planId,
                'started_at' => $startedAt,
            ] = $data;

            $plan = Plan::findOrFail($planId);
            $customer = Customer::findOrFail($customerId);

            throw_unless(
                $plan->is_active,
                new InvalidArgumentException('Cannot subscribe to an inactive plan.')
            );

            return Subscription::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatusEnum::ACTIVE,
                'started_at' => $startedAt ?? now()->toDateString(),
                'next_billing_date' => now()->startOfMonth()->toDateString(),
            ]);
        });
    }

    public function cancel(Subscription $subscription, ?string $reason = null): Subscription
    {
        throw_if(
            $subscription->status->isCancelled(),
            new InvalidArgumentException('Subscription is already cancelled.')
        );

        $subscription->cancel($reason);

        return $subscription->fresh();
    }

    public function changePlan(Subscription $subscription, Plan $newPlan): Subscription
    {
        throw_unless(
            $newPlan->is_active,
            new InvalidArgumentException('Cannot switch to an inactive plan.')
        );

        throw_if(
            $subscription->plan_id === $newPlan->id,
            new InvalidArgumentException('Subscription is already on this plan.')
        );

        $subscription->update(['plan_id' => $newPlan->id]);

        return $subscription->fresh();
    }
}
