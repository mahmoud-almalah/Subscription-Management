<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer;

use App\Domain\Tenant\Models\Customer;
use App\Http\Resources\Subscription\SubscriptionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read Customer $resource */
final class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'total_revenue' => $this->resource->getTotalRevenue(),
            'address' => $this->resource->address->toArray(),
            'status' => [
                'id' => $this->resource->status->value,
                'name' => $this->resource->status->getLabel(),
            ],
            'metadata' => $this->resource->metadata?->toArray(),
            'subscriptions' => $this->whenLoaded(
                relationship: 'subscriptions',
                value: SubscriptionResource::collection($this->resource->subscriptions)
            ),
        ];
    }
}
