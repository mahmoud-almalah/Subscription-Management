<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Tenant\Models\User;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Support\Responsable;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Authentication')]
final class LoginController
{
    #[Endpoint(
        title: 'Login',
        description: 'Authenticate a user and return an access token.',
    )]
    #[BodyParam(
        name: 'email',
        description: 'The email address of the user.',
        example: 'test@gmail.com'
    )]
    #[BodyParam(
        name: 'password',
        description: 'The password of the user.',
        example: '12345678'
    )]
    public function __invoke(LoginRequest $request): Responsable
    {
        $user = User::query()
            ->firstWhere('email', $request->string('email')->value());

        if (! $user || ! password_verify($request->string('password')->value(), $user->password)) {
            return ApiResponse::error(
                message: 'Invalid credentials.',
                status: 401,
            );
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::model(
            key: 'user',
            resource: UserResource::make($user)->withToken($token),
            message: 'Login successful.',
        );
    }
}
