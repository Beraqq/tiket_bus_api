<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class routes extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'route_id',
        'departure',
        'destination'
    ];

    public function schedules()
    {
        return $this->hasMany(schedules::class);
    }
}
