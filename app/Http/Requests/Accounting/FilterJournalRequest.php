<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use App\Domain\Accounting\Enums\JournalEntryTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FilterJournalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'types' => ['sometimes', 'array'],
            'types.*' => ['string', Rule::enum(JournalEntryTypeEnum::class)],
            'date_from' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'date_to' => ['sometimes', 'date', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'order_by' => ['sometimes', 'string', Rule::in(['entry_date', 'entry_number', 'created_at'])],
            'order_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
