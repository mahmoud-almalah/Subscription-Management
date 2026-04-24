<?php

declare(strict_types=1);

namespace App\Http\Resources\Subscription;

use App\Domain\Subscription\Models\Subscription;
use App\Http\Resources\Plan\PlanResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read Subscription $resource */
final class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'plan' => PlanResource::make($this->resource->plan),
            'status' => [
                'id' => $this->resource->status->value,
                'name' => $this->resource->status->getLabel(),
            ],
            'is_active' => $this->resource->isActive(),
            'started_at' => $this->resource->started_at->toDateString(),
            'ends_at' => $this->resource->ends_at?->toDateString(),
            'next_billing_date' => $this->resource->getNextBillingDate()?->toDateString(),
            'cancelled_at' => $this->resource->cancelled_at?->toDateString(),
            'cancellation_reason' => $this->resource->cancellation_reason,
            'created_at' => $this->resource->created_at->toDateString(),
        ];
    }
}
