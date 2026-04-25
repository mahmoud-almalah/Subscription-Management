<?php

declare(strict_types=1);

namespace App\Http\Resources\Payment;

use App\Domain\Billing\Models\Payment;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Invoice\InvoiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read Payment $resource */
final class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'amount' => $this->resource->amount,
            'formatted_amount' => $this->resource->getFormattedAmount(),
            'currency' => $this->resource->currency,
            'payment_method' => [
                'id' => $this->resource->payment_method->value,
                'name' => $this->resource->payment_method->getLabel(),
            ],
            'payment_date' => $this->resource->payment_date->toDateString(),
            'reference_number' => $this->resource->reference_number,
            'notes' => $this->resource->notes,
            'invoice' => InvoiceResource::make($this->whenLoaded('invoice')),
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'created_at' => $this->resource->created_at->toDateTimeString(),
        ];
    }
}
