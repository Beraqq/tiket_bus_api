<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HistoryController extends Controller
{
    public function index()
    {
        try {
            $histories = History::with(['user', 'booking'])->get();

            return response()->json([
                'status' => 'success',
                'data' => $histories
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
                'booking_id' => 'required|exists:bookings,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Cek apakah history untuk booking ini sudah ada
            $existingHistory = History::where('booking_id', $request->booking_id)->first();
            if ($existingHistory) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'History already exists for this booking'
                ], 422);
            }

            $history = History::create([
                'user_id' => auth('api')->user()->id ?? 1, // Gunakan 1 untuk testing
                'booking_id' => $request->booking_id
            ]);

            // Load relasi
            $history->load(['user', 'booking']);

            return response()->json([
                'status' => 'success',
                'message' => 'History created successfully',
                'data' => $history
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
            $history = History::with(['user', 'booking'])->find($id);

            if (!$history) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'History not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $history
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
                'booking_id' => 'required|exists:bookings,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $history = History::find($id);

            if (!$history) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'History not found'
                ], 404);
            }

            $history->update([
                'booking_id' => $request->booking_id
            ]);

            // Load relasi
            $history->load(['user', 'booking']);

            return response()->json([
                'status' => 'success',
                'message' => 'History updated successfully',
                'data' => $history
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
            $history = History::find($id);

            if (!$history) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'History not found'
                ], 404);
            }

            $history->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'History deleted successfully'
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }
}
