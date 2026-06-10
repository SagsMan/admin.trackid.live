<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rental extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rental_category_id',
        'title',
        'description',
        'category',
        'price_per_day',
        'currency',
        'location',
        'latitude',
        'longitude',
        'images',
        'owner_id',
        'owner_name',
        'owner_phone',
        'owner_email',
        'status',
        'is_active',
        'min_days',
        'max_days',
        'terms',
        'features',
    ];

    protected $casts = [
        'images'   => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'price_per_day' => 'decimal:2',
        'total_price'   => 'decimal:2',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'available');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeNearby($query, $latitude, $longitude, $radiusKm = 50)
    {
        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw("*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance", [$latitude, $longitude, $latitude])
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance');
    }

    // Relationships
    public function rentalCategory()
    {
        return $this->belongsTo(RentalCategory::class);
    }

    public function bookings()
    {
        return $this->hasMany(RentalBooking::class);
    }

    public function activeBookings()
    {
        return $this->hasMany(RentalBooking::class)->whereIn('status', ['confirmed', 'active']);
    }

    // Helpers
    public function isAvailableForDates($startDate, $endDate): bool
    {
        $conflicts = $this->bookings()
            ->whereIn('status', ['confirmed', 'active'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate);
                  });
            })->count();

        return $conflicts === 0;
    }
}
