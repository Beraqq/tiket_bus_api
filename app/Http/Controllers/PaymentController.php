<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        // Log konfigurasi untuk debugging
        Log::info('Midtrans Configuration:', [
            'serverKey' => config('midtrans.server_key'),
            'isProduction' => config('midtrans.is_production'),
            'merchantId' => config('midtrans.merchant_id')
        ]);
    }

    public function createPayment(Request $request)
{
    try {
        Log::info('Payment Request:', $request->all());

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric',
        ]);

        $booking = Booking::with(['user', 'schedule.bus'])->findOrFail($validated['booking_id']);

        $orderId = 'BOOK-' . $booking->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int)$validated['amount']
            ],
            'customer_details' => [
                'first_name' => $booking->user->name ?? 'Customer',
                'email' => $booking->user->email ?? '',
                'phone' => $booking->user->phone ?? ''
            ],
            'item_details' => [
                [
                    'id' => $booking->schedule->id,
                    'price' => (int)$validated['amount'],
                    'quantity' => 1,
                    'name' => 'Tiket Bus ' . ($booking->schedule->bus->name ?? 'Unknown') .
                             ' (Kursi: ' . $booking->seat_number . ')'
                ]
            ]
        ];

        Log::info('Midtrans Parameters:', $params);

        $snapToken = Snap::getSnapToken($params);
        Log::info('Snap Token generated:', ['token' => $snapToken]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $validated['amount'],
            'payment_deadline' => now()->addDay(),
            'status' => 'pending',
            'payment_details' => [
                'snap_token' => $snapToken,
                'order_id' => $orderId
            ]
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'snap_token' => $snapToken,
                'payment' => $payment
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Payment Creation Error:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function handleCallback(Request $request)
    {
        try {
            $notification = json_decode($request->getContent(), true);

            $transaction = $notification['transaction_status'];
            $type = $notification['payment_type'];
            $orderId = $notification['order_id'];
            $vaNumber = $notification['va_numbers'][0]['va_number'] ?? null;

            $payment = Payment::whereJsonContains('payment_details->order_id', $orderId)->first();

            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            // Update virtual account jika ada
            if ($vaNumber) {
                $payment->virtual_account = $vaNumber;
            }

            // Update status berdasarkan response Midtrans
            switch ($transaction) {
                case 'capture':
                case 'settlement':
                    $payment->status = 'completed';
                    break;
                case 'pending':
                    $payment->status = 'pending';
                    break;
                case 'deny':
                case 'cancel':
                    $payment->status = 'failed';
                    break;
                case 'expire':
                    $payment->status = 'expired';
                    break;
            }

            // Update payment details
            $paymentDetails = $payment->payment_details;
            $paymentDetails['transaction_status'] = $transaction;
            $paymentDetails['payment_type'] = $type;
            $payment->payment_details = $paymentDetails;

            $payment->save();

            // Update booking status
            if ($payment->status === 'completed') {
                $payment->booking->update(['status' => 'paid']);
            } else if (in_array($payment->status, ['failed', 'expired'])) {
                $payment->booking->update(['status' => 'canceled']);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadPaymentProof(Request $request, $paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);

            $request->validate([
                'payment_proof' => 'required|image|max:2048' // max 2MB
            ]);

            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $path = $file->store('payment_proofs', 'public');

                $payment->update([
                    'payment_proof' => $path,
                    'status' => 'pending' // Status menunggu verifikasi
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Bukti pembayaran berhasil diunggah',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function checkStatus($paymentId)
    {
        try {
            $payment = Payment::with('booking')->findOrFail($paymentId);

            if ($payment->isExpired() && $payment->status === 'pending') {
                $payment->update(['status' => 'expired']);
                $payment->booking->update(['status' => 'canceled']);
            }

            return response()->json([
                'status' => 'success',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
