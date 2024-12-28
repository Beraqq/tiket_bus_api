<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function index()
    {
        try {
            $bookings = Booking::with(['user', 'schedule'])->get();

            return response()->json([
                'status' => 'success',
                'data' => $bookings
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
            'schedule_id' => 'required|exists:schedules,schedule_id',
            'seat_number' => 'required|integer|min:1',
            'status' => 'required|in:pending,paid,canceled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Ambil data schedule beserta relasi bus
        $schedule = Schedule::with(['bus'])->where('schedule_id', $request->schedule_id)->first();

        if (!$schedule) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Schedule not found'
            ], 404);
        }

        // Cek ketersediaan kursi
        if ($request->seat_number > $schedule->available_seats) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Not enough available seats'
            ], 422);
        }

        // Generate booking code
        $bookingCode = 'BK' . date('YmdHis') . rand(1000, 9999);

        // Hitung total price berdasarkan price_per_seat dari bus
        $total_price = $schedule->bus->price_per_seat * $request->seat_number;

        // Buat booking
        $booking = Booking::create([
            'user_id' => 1, // Sementara hardcode untuk testing
            'schedule_id' => $request->schedule_id,
            'booking_code' => $bookingCode,  // Tambahkan booking code
            'seat_number' => $request->seat_number,
            'total_price' => $total_price,
            'status' => $request->status,
            'payment_status' => 'unpaid'  // Tambahkan default payment status
        ]);

        // Update available seats di schedule
        $schedule->update([
            'available_seats' => $schedule->available_seats - $request->seat_number
        ]);

        // Load relasi untuk response
        $booking->load(['user', 'schedule.bus']);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking created successfully',
            'data' => [
                'booking' => $booking,
                'price_details' => [
                    'price_per_seat' => $schedule->bus->price_per_seat,
                    'number_of_seats' => $request->seat_number,
                    'total_price' => $total_price
                ]
            ]
        ], 201);

    } catch (\Throwable $error) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Error: ' . $error->getMessage()
        ], 500);
    }
}

public function completePayment(Request $request, $id)
{
    try {
        $booking = Booking::findOrFail($id);

        // Validasi request
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'payment_method' => 'required|string'
        ]);

        // Set payment deadline 24 jam dari sekarang
        $paymentDeadline = now()->addHours(24);

        // Buat payment record
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $validated['amount'],
            'method' => $validated['payment_method'],
            'status' => 'pending',
            'payment_deadline' => $paymentDeadline,
            'payment_details' => [
                'bank_transfer' => [
                    'bank_name' => 'BCA',
                    'account_number' => '1234567890',
                    'account_name' => 'PT Bus Travel'
                ]
            ]
        ]);

        // Update booking status
        $booking->update([
            'payment_status' => 'pending',
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment initiated successfully',
            'data' => [
                'payment' => $payment,
                'booking' => $booking->fresh()->load('schedule.bus'),
                'payment_instructions' => [
                    'deadline' => $paymentDeadline->format('Y-m-d H:i:s'),
                    'bank_name' => 'BCA',
                    'account_number' => '1234567890',
                    'account_name' => 'PT Bus Travel',
                    'amount' => $validated['amount']
                ]
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Payment Error: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
public function getActiveBookings()
{
    try {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $bookings = Booking::with(['schedule.bus'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'pending'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'seat_number' => $booking->seat_number,
                    'total_price' => $booking->total_price,
                    'payment_status' => $booking->payment_status,
                    'status' => $booking->status,
                    'schedule_id' => $booking->schedule_id,
                    'schedule' => $booking->schedule,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $bookings
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function show($id)
    {
        try {
            $booking = Booking::with(['user', 'schedule'])->find($id);

            if (!$booking) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Booking not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $booking
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function checkAvailability($scheduleId)
{
    $schedule = Schedule::findOrFail($scheduleId);
    $bookedSeats = Booking::where('schedule_id', $scheduleId)
                         ->where('status', '!=', 'cancelled')
                         ->pluck('seat_number')
                         ->toArray();

    $totalSeats = $schedule->bus->total_seats;
    $availableSeats = array_diff(range(1, $totalSeats), $bookedSeats);

    return response()->json([
        'available_seats' => array_values($availableSeats)
    ]);
}

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,paid,canceled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $booking = Booking::find($id);

            if (!$booking) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Booking not found'
                ], 404);
            }

            $booking->update([
                'status' => $request->status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking updated successfully',
                'data' => $booking
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
            $booking = Booking::find($id);

            if (!$booking) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Booking not found'
                ], 404);
            }

            $booking->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking deleted successfully'
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }
}
