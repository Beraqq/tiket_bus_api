<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'amount',
        'method',
        'virtual_account',
        'payment_proof',
        'payment_deadline',
        'status',
        'payment_details'
    ];

    protected $casts = [
        'payment_deadline' => 'datetime',
        'payment_details' => 'array'
    ];

    protected $dates = [
        'payment_deadline'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function isExpired()
    {
        return $this->payment_deadline && now()->isAfter($this->payment_deadline);
    }
}
