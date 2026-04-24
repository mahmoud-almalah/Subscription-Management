<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Domain\Accounting\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read JournalEntry $resource */
final class JournalEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'entry_number' => $this->resource->entry_number,
            'type' => [
                'id' => $this->resource->type->value,
                'name' => $this->resource->type->getLabel(),
            ],
            'description' => $this->resource->description,
            'entry_date' => $this->resource->entry_date->toDateString(),
            'is_balanced' => $this->resource->isBalanced(),
            'total_debits' => $this->resource->getTotalDebits(),
            'reference' => [
                'id' => $this->resource->reference_id,
                'type' => $this->resource->reference_type,
            ],
            'lines' => $this->whenLoaded(
                relationship: 'lines',
                value: JournalLineResource::collection($this->resource->lines),
            ),
            'created_at' => $this->resource->created_at->toDateTimeString(),
        ];
    }
}
