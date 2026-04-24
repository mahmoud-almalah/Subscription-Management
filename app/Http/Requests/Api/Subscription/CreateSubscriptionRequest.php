<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Subscription;

use App\Domain\Subscription\Models\Plan;
use App\Domain\Tenant\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateSubscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'string', 'ulid', Rule::exists(Customer::class, 'id')],
            'plan_id' => ['required', 'string', 'ulid', Rule::exists(Plan::class, 'id')],
            'started_at' => ['required', 'date:Y-m-d'],
        ];
    }
}
