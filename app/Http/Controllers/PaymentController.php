<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index()
    {
        try {
            $payments = Payment::with('booking')->get();

            return response()->json([
                'status' => 'success',
                'data' => $payments
            ], 200);
        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'booking_id' => 'required|exists:bookings,id',
                'method' => 'required|string',
                'virtual_account' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Set payment deadline 24 jam dari sekarang
            $payment_deadline = Carbon::now()->addHours(24);

            $payment = Payment::create([
                'booking_id' => $request->booking_id,
                'method' => $request->method,
                'virtual_account' => $request->virtual_account,
                'payment_deadline' => $payment_deadline,
                'status' => 'pending'
            ]);

            // Load relasi booking
            $payment->load('booking');

            return response()->json([
                'status' => 'success',
                'message' => 'Payment created successfully',
                'data' => $payment
            ], 201);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $payment = Payment::with('booking')->find($id);

            if (!$payment) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $payment
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,completed,failed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $payment = Payment::find($id);

            if (!$payment) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment not found'
                ], 404);
            }

            // Update payment status
            $payment->update([
                'status' => $request->status
            ]);

            // Jika payment completed, update booking status jadi paid
            if ($request->status === 'completed') {
                $payment->booking->update(['status' => 'paid']);
            }

            // Load relasi booking
            $payment->load('booking');

            return response()->json([
                'status' => 'success',
                'message' => 'Payment updated successfully',
                'data' => $payment
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $payment = Payment::find($id);

            if (!$payment) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment not found'
                ], 404);
            }

            $payment->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment deleted successfully'
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }
}
