<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    public function index()
    {
        try {
            $schedules = Schedule::with(['bus', 'route'])->get();
            return response()->json([
                'status' => 'success',
                'schedules' => $schedules
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching schedules: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching schedules'
            ], 500);
        }
    }

    public function getAvailable($busCode, $date)
    {
        try {
            Log::info('Searching schedules for bus: ' . $busCode . ' on date: ' . $date);

            $schedules = Schedule::where('bus_code', $busCode)
                ->whereDate('departure_date', $date)
                ->with(['bus', 'route'])
                ->get();

            Log::info('Found ' . $schedules->count() . ' schedules');

            return response()->json([
                'status' => 'success',
                'schedules' => $schedules,
                'message' => 'Schedules retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getAvailable: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'bus_code' => 'required|exists:buses,bus_code',
                'route_id' => 'required|exists:routes,route_id',
                'departure_date' => 'required|date',
                'departure_time' => 'required',
                'available_seats' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422);
            }

            // Generate unique schedule_id
            $scheduleId = 'SCH' . time() . rand(1000, 9999);

            $schedule = Schedule::create([
                'schedule_id' => $scheduleId,
                'bus_code' => $request->bus_code,
                'route_id' => $request->route_id,
                'departure_date' => $request->departure_date,
                'departure_time' => $request->departure_time,
                'available_seats' => $request->available_seats
            ]);

            Log::info('Schedule created: ' . $scheduleId);

            return response()->json([
                'status' => 'success',
                'message' => 'Schedule created successfully',
                'schedule' => $schedule
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating schedule: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($scheduleId)
    {
        try {
            $schedule = Schedule::where('schedule_id', $scheduleId)
                ->with(['bus', 'route'])
                ->first();

            if (!$schedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Schedule not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'schedule' => $schedule
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching schedule: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching schedule'
            ], 500);
        }
    }

    public function update(Request $request, $scheduleId)
    {
        try {
            $schedule = Schedule::where('schedule_id', $scheduleId)->first();

            if (!$schedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Schedule not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'bus_code' => 'required|exists:buses,bus_code',
                'route_id' => 'required|exists:routes,route_id',
                'departure_date' => 'required|date',
                'departure_time' => 'required',
                'available_seats' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422);
            }

            $schedule->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Schedule updated successfully',
                'schedule' => $schedule
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating schedule: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating schedule'
            ], 500);
        }
    }

    public function destroy($scheduleId)
    {
        try {
            $schedule = Schedule::where('schedule_id', $scheduleId)->first();

            if (!$schedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Schedule not found'
                ], 404);
            }

            $schedule->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Schedule deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting schedule: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting schedule'
            ], 500);
        }
    }
}
