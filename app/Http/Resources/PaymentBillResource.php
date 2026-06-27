<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentBillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'house_resident_id' => $this->house_resident_id,
            'house_resident' => new HouseResidentResource($this->whenLoaded('houseResident')),
            'fee_type_id' => $this->fee_type_id,
            'fee_type' => new FeeTypeResource($this->whenLoaded('feeType')),
            'billing_month' => $this->billing_month?->format('Y-m-d'),
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'paid_at' => $this->paid_at?->format('Y-m-d'),
            'payment_group_id' => $this->payment_group_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
