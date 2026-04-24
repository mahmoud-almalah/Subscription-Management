<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Customer;

use App\Domain\Tenant\Enums\CustomerStatusEnum;
use App\Domain\Tenant\Models\Customer;
use App\Http\Requests\Api\Customer\CustomerRequest;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Support\Responsable;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Customers')]
#[Authenticated]
final class CustomerController
{
    #[Endpoint(
        title: 'List Customers',
        description: 'Retrieve a paginated list of customers.',
    )]
    public function index(): Responsable
    {
        return ApiResponse::collection(
            key: 'customers',
            resource: CustomerResource::collection(
                resource: $customers = Customer::query()
                    ->simplePaginate(30)
            ),
            paginator: $customers,
        );
    }

    #[Endpoint(
        title: 'Get Customer',
        description: 'Retrieve details of a specific customer, including their subscriptions and associated plans.',
    )]
    public function show(Customer $customer): Responsable
    {
        $customer->load('subscriptions.plan');

        return ApiResponse::model(
            key: 'customer',
            resource: CustomerResource::make($customer),
        );
    }

    #[Endpoint(
        title: 'Create Customer',
        description: 'Create a new customer with the provided details.',
    )]
    #[BodyParam(
        name: 'name',
        description: 'The name of the customer.',
        example: 'John Doe'
    )]
    #[BodyParam(
        name: 'email',
        description: 'The email address of the customer.',
        example: 'test@gmail.com'
    )]
    #[BodyParam(
        name: 'phone',
        description: 'The phone number of the customer.',
        example: '+1234567890',
        nullable: true
    )]
    #[BodyParam(
        name: 'address',
        description: 'The address of the customer, including latitude, longitude, and formatted address.',
        example: [
            'lat' => 37.7749,
            'lng' => -122.4194,
            'address' => '1 Market St, San Francisco, CA 94105, USA',
        ]
    )]
    #[BodyParam(
        name: 'status',
        description: 'The status of the customer.',
        example: 'active',
        enum: CustomerStatusEnum::class
    )]
    #[BodyParam(
        name: 'metadata',
        description: 'Additional metadata for the customer as key-value pairs.',
        example: [
            [
                'key' => 'preferred_language',
                'value' => 'en',
                'type' => 'string',
                'description' => 'The preferred language of the customer.',
            ],
        ],
        nullable: true
    )]
    public function store(CustomerRequest $request): Responsable
    {
        $customer = Customer::query()->create($request->validated());

        return ApiResponse::model(
            key: 'customer',
            resource: CustomerResource::make($customer),
            message: 'Customer created successfully.',
        );
    }

    #[Endpoint(
        title: 'Update Customer',
        description: 'Update the details of an existing customer.',
    )]
    #[BodyParam(
        name: 'name',
        description: 'The name of the customer.',
        example: 'John Doe'
    )]
    #[BodyParam(
        name: 'email',
        description: 'The email address of the customer.',
        example: 'test@gmail.com'
    )]
    #[BodyParam(
        name: 'phone',
        description: 'The phone number of the customer.',
        example: '+1234567890',
        nullable: true
    )]
    #[BodyParam(
        name: 'address',
        description: 'The address of the customer, including latitude, longitude, and formatted address.',
        example: [
            'lat' => 37.7749,
            'lng' => -122.4194,
            'address' => '1 Market St, San Francisco, CA 94105, USA',
        ]
    )]
    #[BodyParam(
        name: 'status',
        description: 'The status of the customer.',
        example: 'active',
        enum: CustomerStatusEnum::class
    )]
    #[BodyParam(
        name: 'metadata',
        description: 'Additional metadata for the customer as key-value pairs.',
        example: [
            [
                'key' => 'preferred_language',
                'value' => 'en',
                'type' => 'string',
                'description' => 'The preferred language of the customer.',
            ],
        ],
        nullable: true
    )]
    public function update(CustomerRequest $request, Customer $customer): Responsable
    {
        $customer->update($request->validated());

        return ApiResponse::model(
            key: 'customer',
            resource: CustomerResource::make($customer),
            message: 'Customer updated successfully.',
        );
    }

    #[Endpoint(
        title: 'Delete Customer',
        description: 'Delete a specific customer from the system.',
    )]
    public function destroy(Customer $customer): Responsable
    {
        $customer->delete();

        return ApiResponse::success(
            message: 'Customer deleted successfully.',
        );
    }
}
