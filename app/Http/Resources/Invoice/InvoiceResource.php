<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoice;

use App\Domain\Billing\Models\Invoice;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Payment\PaymentResource;
use App\Http\Resources\Subscription\SubscriptionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read Invoice $resource */
final class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'invoice_number' => $this->resource->invoice_number,
            'amount' => $this->resource->amount,
            'currency' => $this->resource->currency,
            'status' => [
                'id' => $this->resource->status->value,
                'name' => $this->resource->status->getLabel(),
            ],
            'customer' => $this->whenLoaded(
                relationship: 'customer',
                value: CustomerResource::make($this->resource->customer)
            ),
            'subscription' => $this->whenLoaded(
                relationship: 'subscription',
                value: SubscriptionResource::make($this->resource->subscription)
            ),
            'payments' => $this->whenLoaded(
                relationship: 'payments',
                value: PaymentResource::collection($this->resource->payments)
            ),
            'is_paid' => $this->resource->isPaid(),
            'is_overdue' => $this->resource->isOverdue(),
            'period_start' => $this->resource->period_start->toDateString(),
            'period_end' => $this->resource->period_end->toDateString(),
            'due_date' => $this->resource->due_date->toDateString(),
            'paid_at' => $this->resource->paid_at?->toDateString(),
            'revenue_recognized_at' => $this->resource->revenue_recognized_at?->toDateString(),
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at->toDateString(),
            'subscription_id' => $this->resource->subscription_id,
            'customer_id' => $this->resource->customer_id,
        ];
    }
}
