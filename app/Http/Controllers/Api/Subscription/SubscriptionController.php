<?php

namespace App\Http\Controllers\Api\Subscription;

use App\Domain\Subscription\Models\Plan;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Services\SubscriptionService;
use App\Http\Requests\Api\Subscription\CreateSubscriptionRequest;
use App\Http\Requests\Api\Subscription\UpdateSubscriptionRequest;
use App\Http\Resources\Subscription\SubscriptionResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Subscriptions')]
#[Authenticated]
class SubscriptionController
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {}

    #[Endpoint(
        title: 'List Subscriptions',
        description: 'Retrieve a list of all subscriptions with their associated plans and invoice counts.',
    )]
    public function index(): Responsable
    {
        return ApiResponse::collection(
            key: 'subscriptions',
            resource: SubscriptionResource::collection(
                resource: Subscription::query()
                    ->with('plan')
                    ->withCount('invoices')
                    ->get()
            ),
            message: 'Subscriptions retrieved successfully.',
        );
    }

    #[Endpoint(
        title: 'Get Subscription',
        description: 'Retrieve a specific subscription by its ID, including its associated plan, customer, and invoices.',
    )]
    public function show(Subscription $subscription): Responsable
    {
        return ApiResponse::model(
            key: 'subscription',
            resource: SubscriptionResource::make(
                resource: $subscription
                    ->load(['plan', 'customer', 'invoices'])
                    ->loadCount('invoices')
            ),
            message: 'Subscription retrieved successfully.',
        );
    }

    #[Endpoint(
        title: 'Create Subscription',
        description: 'Create a new subscription for a customer with a specified plan and start date.',
    )]
    #[BodyParam(
        name: 'customer_id',
        description: 'The ID of the customer to subscribe.',
        example: 'cus_1234567890'
    )]
    #[BodyParam(
        name: 'plan_id',
        description: 'The ID of the plan to subscribe to.',
        example: 'plan_1234567890'
    )]
    #[BodyParam(
        name: 'started_at',
        description: 'The start date of the subscription (optional, defaults to today).',
        example: '2024-01-01'
    )]
    public function store(CreateSubscriptionRequest $request): Responsable
    {
        $subscription = $this->subscriptionService->create([
            'customer_id' => $request->string('customer_id')->value(),
            'plan_id' => $request->string('plan_id')->value(),
            'started_at' => $request->date('started_at')?->toDateString(),
        ], tenantId: $request->user('user')->tenant_id);

        return ApiResponse::model(
            key: 'subscription',
            resource: SubscriptionResource::make($subscription),
            message: 'Subscription created successfully.',
            status: 201,
        );
    }

    #[Endpoint(
        title: 'Update Subscription',
        description: 'Change the plan of an existing subscription.',
    )]
    #[BodyParam(
        name: 'plan_id',
        description: 'The ID of the new plan to switch to.',
        example: 'plan_0987654321'
    )]
    public function update(Subscription $subscription, UpdateSubscriptionRequest $request): Responsable
    {
        $subscription = $this->subscriptionService->changePlan(
            subscription: $subscription,
            newPlan: Plan::findOrFail($request->string('plan_id')->value()),
        );

        return ApiResponse::model(
            key: 'subscription',
            resource: SubscriptionResource::make($subscription),
            message: 'Subscription updated successfully.',
        );
    }

    #[Endpoint(
        title: 'Cancel Subscription',
        description: 'Cancel an active subscription with an optional reason for cancellation.',
    )]
    #[BodyParam(
        name: 'reason',
        description: 'The reason for cancelling the subscription (optional).',
        example: 'No longer needed',
        nullable: true
    )]
    public function destroy(Subscription $subscription, Request $request): Responsable
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $subscription = $this->subscriptionService->cancel(
            subscription: $subscription,
            reason: $request->input('reason'),
        );

        return ApiResponse::model(
            key: 'subscription',
            resource: SubscriptionResource::make($subscription),
            message: 'Subscription cancelled successfully.',
        );
    }
}
