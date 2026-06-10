<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->description,
            'category'         => $this->category,
            'rental_category'  => $this->whenLoaded('rentalCategory', fn() => [
                'id'   => $this->rentalCategory->id,
                'name' => $this->rentalCategory->name,
                'slug' => $this->rentalCategory->slug,
                'icon' => $this->rentalCategory->icon,
            ]),
            'price_per_day'    => (float) $this->price_per_day,
            'currency'         => $this->currency,
            'location'         => $this->location,
            'latitude'         => $this->latitude ? (float) $this->latitude : null,
            'longitude'        => $this->longitude ? (float) $this->longitude : null,
            'images'           => $this->images ?? [],
            'owner_id'         => $this->owner_id,
            'owner_name'       => $this->owner_name,
            'owner_phone'      => $this->owner_phone,
            'owner_email'      => $this->owner_email,
            'status'           => $this->status,
            'is_active'        => $this->is_active,
            'min_days'         => $this->min_days,
            'max_days'         => $this->max_days,
            'terms'            => $this->terms,
            'features'         => $this->features ?? [],
            'distance'         => isset($this->distance) ? round((float) $this->distance, 2) : null,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
