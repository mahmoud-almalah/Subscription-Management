<?php

declare(strict_types=1);

namespace App\Http\Resources\Plan;

use App\Domain\Subscription\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read Plan $resource */
final class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'price' => $this->resource->price,
            'currency' => $this->resource->currency,
            'billing_cycle' => $this->resource->billing_cycle,
            'features' => $this->resource->features,
        ];
    }
}
