<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class bookings extends Model
{
    protected $fillable = [
        'schedule_id',
        'bus_code',
        'route_id',
        'departure_date',
        'departure_time',
        'available_seats',
    ];
}
