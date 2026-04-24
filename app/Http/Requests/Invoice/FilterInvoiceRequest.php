<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoice;

use App\Domain\Billing\Enums\InvoiceStatusEnum;
use App\Domain\Subscription\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FilterInvoiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'subscriptions_ids' => ['sometimes', 'array'],
            'subscriptions_ids.*' => ['string', 'string', 'ulid', Rule::exists(Subscription::class, 'id')],
            'amount_from' => ['sometimes', 'numeric', 'min:0'],
            'amount_to' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'statuses' => ['sometimes', 'array'],
            'statuses.*' => ['string', Rule::enum(InvoiceStatusEnum::class)],
            'period_start' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'period_end' => ['sometimes', 'date', 'date_format:Y-m-d', 'after_or_equal:period_start'],
            'is_paid' => ['sometimes', 'boolean'],
            'order_by' => ['sometimes', 'string', Rule::in(['amount', 'created_at', 'due_date', 'period_start', 'period_end'])],
            'order_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
