<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Plan;

use App\Domain\Subscription\Models\Plan;
use App\Http\Requests\Api\PlanRequest;
use App\Http\Resources\Plan\PlanResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Support\Responsable;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Plans')]
#[Authenticated]
final class PlanController
{
    #[Endpoint(
        title: 'List Plans',
        description: 'Retrieve a list of all active plans.',
    )]
    public function index(): Responsable
    {
        return ApiResponse::collection(
            key: 'plans',
            resource: PlanResource::collection(
                resource: Plan::query()
                    ->active()
                    ->get()
            ),
        );
    }

    #[Endpoint(
        title: 'Get Plan',
        description: 'Retrieve details of a specific plan.',
    )]
    public function show(Plan $plan): Responsable
    {
        return ApiResponse::model(
            key: 'plan',
            resource: PlanResource::make($plan),
        );
    }

    #[Endpoint(
        title: 'Create Plan',
        description: 'Create a new plan with the provided details.',
    )]
    #[BodyParam(
        name: 'name',
        description: 'The name of the plan.',
        example: 'Basic Plan'
    )]
    #[BodyParam(
        name: 'description',
        description: 'A brief description of the plan.',
        example: 'This plan includes basic features suitable for individuals.'
    )]
    #[BodyParam(
        name: 'price',
        description: 'The price of the plan.',
        example: 50
    )]
    #[BodyParam(
        name: 'billing_cycle',
        description: 'The billing cycle for the plan (e.g., monthly, yearly).',
        example: 'monthly',
        enum: ['monthly', 'yearly']
    )]
    #[BodyParam(
        name: 'features',
        description: 'An array of features included in the plan.',
        example: ['Feature 1', 'Feature 2', 'Feature 3']
    )]
    #[BodyParam(
        name: 'is_active',
        description: 'Indicates whether the plan is active.',
        example: true
    )]
    public function store(PlanRequest $request): Responsable
    {
        $plan = Plan::query()->create($request->validated());

        return ApiResponse::model(
            key: 'plan',
            resource: PlanResource::make($plan),
            message: 'Plan created successfully.',
        );
    }

    #[Endpoint(
        title: 'Update Plan',
        description: 'Update the details of an existing plan.',
    )]
    #[BodyParam(
        name: 'name',
        description: 'The name of the plan.',
        example: 'Basic Plan'
    )]
    #[BodyParam(
        name: 'description',
        description: 'A brief description of the plan.',
        example: 'This plan includes basic features suitable for individuals.'
    )]
    #[BodyParam(
        name: 'price',
        description: 'The price of the plan.',
        example: 50
    )]
    #[BodyParam(
        name: 'billing_cycle',
        description: 'The billing cycle for the plan (e.g., monthly, yearly).',
        example: 'monthly',
        enum: ['monthly', 'yearly']
    )]
    #[BodyParam(
        name: 'features',
        description: 'An array of features included in the plan.',
        example: ['Feature 1', 'Feature 2', 'Feature 3']
    )]
    #[BodyParam(
        name: 'is_active',
        description: 'Indicates whether the plan is active.',
        example: true
    )]
    public function update(PlanRequest $request, Plan $plan): Responsable
    {
        $plan->update($request->validated());

        return ApiResponse::model(
            key: 'plan',
            resource: PlanResource::make($plan),
            message: 'Plan updated successfully.',
        );
    }

    #[Endpoint(
        title: 'Delete Plan',
        description: 'Delete an existing plan.',
    )]
    public function destroy(Plan $plan): Responsable
    {
        $plan->delete();

        return ApiResponse::success(
            message: 'Plan deleted successfully.',
        );
    }
}
