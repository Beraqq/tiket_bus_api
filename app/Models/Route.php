<?php

namespace App\Models;


use Illuminate\Support\Str;
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->route_id)) {
                $model->route_id = 'RT-' . Str::random(8);
            }
        });
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'route_id', 'route_id');
    }
}
