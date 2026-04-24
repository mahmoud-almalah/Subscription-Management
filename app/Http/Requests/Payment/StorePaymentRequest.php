<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Domain\Billing\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'payment_method' => ['required', 'string', Rule::enum(PaymentMethodEnum::class)],
            'payment_date' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'reference_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
