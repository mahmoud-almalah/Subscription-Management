<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Auth;

use App\Domain\Tenant\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:dns', Rule::exists(User::class, 'email')],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
