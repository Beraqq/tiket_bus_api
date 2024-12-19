<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class buses extends Model
{
    use HasFactory;

    // Tentukan kolom yang dapat diisi
    protected $fillable = [
        'bus_code',
        'class',
        'facilities',
        'total_seats',
        'price_per_seat',
    ];


    public function schedules()
    {
        return $this->hasMany(schedules::class);
    }
}
