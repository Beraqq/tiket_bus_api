<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\schedules;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function index()
    {
        try {
            $schedules = Schedule::with(['bus', 'route'])->get();

            return response()->json([
                'status' => 'success',
                'data' => $schedules
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
                'schedule_id' => 'required|unique:schedules|max:255',
                'bus_code' => 'required|exists:buses,bus_code',
                'route_id' => 'required|exists:routes,route_id',
                'departure_date' => 'required|date',
                'departure_time' => 'required|date_format:H:i',
                'available_seats' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $schedule = Schedule::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Schedule created successfully',
                'data' => $schedule
            ], 201);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function show($schedule_id)
    {
        try {
            $schedule = Schedule::with(['bus', 'route'])
                ->where('schedule_id', $schedule_id)
                ->first();

            if (!$schedule) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Schedule not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $schedule
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $schedule_id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'bus_code' => 'required|exists:buses,bus_code',
                'route_id' => 'required|exists:routes,route_id',
                'departure_date' => 'required|date',
                'departure_time' => 'required|date_format:H:i',
                'available_seats' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $schedule = Schedule::where('schedule_id', $schedule_id)->first();

            if (!$schedule) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Schedule not found'
                ], 404);
            }

            $schedule->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Schedule updated successfully',
                'data' => $schedule
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function destroy($schedule_id)
    {
        try {
            $schedule = Schedule::where('schedule_id', $schedule_id)->first();

            if (!$schedule) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Schedule not found'
                ], 404);
            }

            $schedule->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Schedule deleted successfully'
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }
}
