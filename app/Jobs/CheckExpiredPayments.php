<?php
// app/Jobs/CheckExpiredPayments.php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckExpiredPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        Payment::where('status', 'pending')
            ->where('payment_deadline', '<', now())
            ->chunk(100, function ($payments) {
                foreach ($payments as $payment) {
                    $payment->update(['status' => 'expired']);
                    $payment->booking->update(['status' => 'canceled']);
                }
            });
    }
}
