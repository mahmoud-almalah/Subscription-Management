<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Customer;

use App\Domain\Tenant\Enums\CustomerStatusEnum;
use App\Domain\Tenant\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:dns', Rule::unique(Customer::class, 'email')
                ->where('tenant_id', $this->user('user')?->tenant_id)
                ->ignore($this->route('customer')),
            ],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique(Customer::class, 'phone')
                ->where('tenant_id', $this->user('user')?->tenant_id)
                ->ignore($this->route('customer'))],
            'address' => ['required', 'array'],
            'address.lat' => ['required', 'numeric'],
            'address.lng' => ['required', 'numeric'],
            'address.address' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::enum(CustomerStatusEnum::class)],
            'metadata' => ['nullable', 'array'],
            'metadata.*.key' => ['required', 'string', 'max:255'],
            'metadata.*.value' => ['required'],
            'metadata.*.type' => ['nullable', 'string', 'max:255'],
            'metadata.*.description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
