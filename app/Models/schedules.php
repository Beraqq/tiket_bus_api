<?php

namespace App\Models;

use App\Models\buses;
use App\Models\routes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class schedules extends Model
{
    protected $fillable = [
        'schedule_id',
        'bus_code',
        'route_id',
        'departure_date',
        'departure_time',
        'available_seats',
    ];

    public function getBusCodeAttribute($value){
        return $this->buses ? $this->buses->bus_code : null;
    }
    public function getRouteIdAttribute($value){
        return $this->routes ? $this->routes->route_id : null;
    }

    // Relasi ke model Bus
    public function buses()
    {
        return $this->belongsTo(buses::class, 'id', 'bus_code');
    }

    // Relasi ke model Route
    public function routes()
    {
        return $this->belongsTo(routes::class, 'id', 'route_id');
    }
}
