<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Subscription;

use App\Domain\Subscription\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSubscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'new_plan_id' => ['required', 'string', 'ulid', Rule::exists(Plan::class, 'id')],
        ];
    }
}
