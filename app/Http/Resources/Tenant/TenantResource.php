<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read Tenant $resource */
final class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'email' => $this->resource->email,
            'status' => [
                'id' => $this->resource->status->value,
                'name' => $this->resource->status->getLabel(),
            ],
            'settings' => $this->resource->settings?->toArray(),
        ];
    }
}
