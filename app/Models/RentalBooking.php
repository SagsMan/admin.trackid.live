<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_id',
        'renter_id',
        'renter_name',
        'renter_email',
        'renter_phone',
        'start_date',
        'end_date',
        'total_days',
        'price_per_day',
        'total_price',
        'currency',
        'status',
        'notes',
        'cancellation_reason',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'start_date'   => 'datetime',
        'end_date'     => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_price'  => 'decimal:2',
        'price_per_day' => 'decimal:2',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
}
