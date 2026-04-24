<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Domain\Billing\Enums\PaymentMethodEnum;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Tenant\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FilterPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invoice_id' => ['sometimes', 'ulid', Rule::exists(Invoice::class, 'id')],
            'customer_id' => ['sometimes', 'ulid', Rule::exists(Customer::class, 'id')],
            'amount_from' => ['sometimes', 'numeric', 'min:0'],
            'amount_to' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'payment_methods' => ['sometimes', 'array'],
            'payment_methods.*' => ['string', Rule::enum(PaymentMethodEnum::class)],
            'date_from' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'date_to' => ['sometimes', 'date', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'order_by' => ['sometimes', 'string', Rule::in(['amount', 'payment_date', 'created_at'])],
            'order_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
