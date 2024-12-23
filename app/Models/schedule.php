<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'schedule_id',
        'bus_code',
        'route_id',
        'departure_date',
        'departure_time',
        'available_seats'
    ];

    // Perbaiki nama method relasi dari 'buses' menjadi 'bus'
    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_code', 'bus_code');
    }

    // Perbaiki nama method relasi dari 'routes' menjadi 'route'
    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id', 'route_id');
    }
}
