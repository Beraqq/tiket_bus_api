<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Route extends Model
{
    use HasFactory;

    protected $table = 'routes';
    protected $primaryKey = 'route_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'route_id',
        'departure',
        'destination'
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'route_id', 'route_id');
    }
}
