<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use HasFactory;

    protected $table = 'buses';
    protected $primaryKey = 'bus_code';
    public $incrementing = false;
    protected $keyType = 'string';
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
        return $this->hasMany(Schedule::class, 'bus_code', 'bus_code');
    }
}
