<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Domain\Tenant\Models\User;
use App\Http\Resources\Tenant\TenantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read User $resource */
final class UserResource extends JsonResource
{
    private ?string $token = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'role' => $this->resource->role,
            'tenant' => $this->whenLoaded('tenant', TenantResource::make($this->resource->tenant)),
            'token' => $this->whenNotNull($this->token),
        ];
    }

    public function withToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
