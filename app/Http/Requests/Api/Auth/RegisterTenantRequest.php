<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Auth;

use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RegisterTenantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenant_name' => ['required', 'string'],
            'tenant_email' => ['required', 'string', 'email:dns', Rule::unique(Tenant::class, 'email')],
            'tenant_slug' => ['required', 'string', Rule::unique(Tenant::class, 'slug')],
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email:dns', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
