<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Tenant\Services\TenantService;
use App\Http\Requests\Api\Auth\RegisterTenantRequest;
use App\Http\Resources\Tenant\TenantResource;
use App\Http\Resources\User\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Support\Responsable;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Authentication')]
final class RegisterTenantController
{
    public function __construct(
        private TenantService $tenantService,
    ) {}

    #[Endpoint(
        title: 'Register Tenant',
        description: 'Register a new tenant along with an admin user and return the tenant details and access token.',
    )]
    #[BodyParam(
        name: 'tenant_name',
        description: 'The name of the tenant.',
        example: 'Acme Corporation'
    )]
    #[BodyParam(
        name: 'tenant_email',
        description: 'The email address of the tenant.',
        example: 'tenant@gmail.com'
    )]
    #[BodyParam(
        name: 'tenant_slug',
        description: 'The unique slug for the tenant.',
        example: 'acme-corp'
    )]
    #[BodyParam(
        name: 'name',
        description: 'The name of the admin user.',
        example: 'John Doe'
    )]
    #[BodyParam(
        name: 'email',
        description: 'The email address of the admin user.',
        example: 'test@gmail.com'
    )]
    #[BodyParam(
        name: 'password',
        description: 'The password for the admin user.',
        example: '12345678'
    )]
    public function __invoke(RegisterTenantRequest $request): Responsable
    {
        [
            'tenant' => $tenant,
            'token' => $token,
            'user' => $admin,
        ] = $this->tenantService->register($request->validated());

        return ApiResponse::collection(
            key: 'tenant',
            resource: [
                'tenant' => TenantResource::make($tenant),
                'admin' => UserResource::make($admin),
                'token' => $token,
            ],
            message: 'Tenant registered successfully.',
        );
    }
}
