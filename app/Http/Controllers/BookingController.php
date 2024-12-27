<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

            // Hitung total price berdasarkan price_per_seat dari bus
            $total_price = $schedule->bus->price_per_seat * $request->seat_number;

            // Buat booking
            $booking = Booking::create([
                'user_id' => 1, // Sementara hardcode untuk testing
                'schedule_id' => $request->schedule_id,
                'seat_number' => $request->seat_number,
                'total_price' => $total_price,
                'status' => $request->status
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
