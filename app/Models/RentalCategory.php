<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'image', 'is_active', 'order'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInorder($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
}
