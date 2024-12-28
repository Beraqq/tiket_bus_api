<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'schedule_id',
        'booking_code',
        'seat_number',
        'total_price',
        'payment_status',
        'status'
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Schedule
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
