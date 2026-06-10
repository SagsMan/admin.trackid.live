<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'rental_id'           => $this->rental_id,
            'rental'              => $this->whenLoaded('rental', fn() => new RentalResource($this->rental)),
            'renter_id'           => $this->renter_id,
            'renter_name'         => $this->renter_name,
            'renter_email'        => $this->renter_email,
            'renter_phone'        => $this->renter_phone,
            'start_date'          => $this->start_date?->toDateString(),
            'end_date'            => $this->end_date?->toDateString(),
            'total_days'          => $this->total_days,
            'price_per_day'       => (float) $this->price_per_day,
            'total_price'         => (float) $this->total_price,
            'currency'            => $this->currency,
            'status'              => $this->status,
            'notes'               => $this->notes,
            'cancellation_reason' => $this->cancellation_reason,
            'confirmed_at'        => $this->confirmed_at?->toISOString(),
            'cancelled_at'        => $this->cancelled_at?->toISOString(),
            'created_at'          => $this->created_at?->toISOString(),
        ];
    }
}
