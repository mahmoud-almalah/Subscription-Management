<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Support\Responsable;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Authentication')]
#[Authenticated]
final class LogoutController
{
    #[Endpoint(
        title: 'Logout',
        description: 'Revoke the user\'s access token, effectively logging them out.',
    )]
    public function __invoke(): Responsable
    {
        auth('user')->user()->tokens()->delete();

        return ApiResponse::success(
            message: 'Logged out successfully.',
        );
    }
}
