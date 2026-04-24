<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Domain\Accounting\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read JournalLine $resource */
final class JournalLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'type' => $this->resource->type->value,
            'amount' => $this->resource->amount,
            'description' => $this->resource->description,
            'account' => $this->whenLoaded(
                relationship: 'account',
                value: [
                    'id' => $this->resource->account->id,
                    'code' => $this->resource->account->code,
                    'name' => $this->resource->account->name,
                    'type' => $this->resource->account->type->value,
                    'normal_balance' => $this->resource->account->normal_balance->value,
                ],
            ),
        ];
    }
}
