<?php

namespace App\Http\Controllers;

use App\Models\schedules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchedulesController extends Controller
{
        // Get Data
        public function index()
        {
            $schedules = schedules::with(['buses', 'routes'])->get();
            // return response()->json(schedules::all(), 200);
            return response()->json([
                'status' => 'success',
                'data' => $schedules
            ]);
        }

        // Post Data Schedules
        public function store(Request $request)
        {
            try {
                // Validasi input
                $validator = Validator::make($request->all(), [
                    'schedule_id' => 'required|unique:schedules|max:255',
                    'bus_code' => 'required|exists:buses,bus_code',
                    'route_id' => 'required|exists:routes,route_id',
                    'departure_date' => 'required|date',
                    'departure_time' => 'required|date_format:H:i',
                    'available_seats' => 'required|integer|min:1',
                ]);

                // Jika validasi gagal, kirimkan pesan error
                if ($validator->fails()) {
                    $validatorMessage = $validator->errors()->first();
                    return response()->json(['status' => 'failed', 'message' => $validatorMessage]);
                }
                // dd($request->route_id);
                // Simpan data bus ke database jika validasi berhasil
                $schedules = schedules::create([
                    'schedule_id' => $request->schedule_id,
                    'bus_code' => $request->bus_code,
                    'route_id', $request->route_id,
                    'departure_date', $request->departure_date,
                    'departure_time', $request->departure_time,
                    'available_seats', $request->available_seats,
                ]);

                // Kembalikan response sukses dengan status 201 (Created)
                return response()->json(['status' => 'success', 'message' => 'Schedule created successfully', 'data' => $schedules], 201);

            } catch (\Throwable $error) {
                // Tangani error jika ada masalah
                return response()->json(['status' => 'failed', 'message' => 'Error: ' . $error->getMessage()]);
            }
        }

        // GET Data Routes
        public function show($id)
        {
            $schedules = schedules::find($id);
            echo $schedules->bus_code;

            if (!$schedules) {
                return response()->json(['message' => 'Schedule not found'], 404);
            }

            return response()->json($schedules, 200);
        }

        // PUT Data Routes
        public function update(Request $request, $busid)
        {
            try {
                // Validasi input
                $validator = Validator::make($request->all(), [
                    'bus_code' => 'required|exists:buses,id',
                    'route_id' => 'required|exists:routes,id',
                    'departure_date' => 'required|date',
                    'departure_time' => 'required|date_format:H:i',
                    'available_seats' => 'required|integer|min:1',
                ]);

                // Jika validasi gagal, kirimkan pesan error
                if ($validator->fails()) {
                    $validatorMessage = $validator->errors()->first();
                    return response()->json(['status' => 'failed', 'message' => $validatorMessage]);
                }else

                // Cari bus berdasarkan ID yang diberikan
                $schedules = schedules::find($busid);

                // Perbarui data route yang ada
                $schedules->update([
                    'bus_code' => $request->bus_code,
                    'route_id', $request->route_id,
                    'departure_date', $request->departure_date,
                    'departure_time', $request->departure_time,
                    'available_seats', $request->available_seats,
                ]);

                // Kembalikan response sukses dengan status 200 (OK)
                return response()->json(['status' => 'success', 'message' => 'Route updated successfully', 'data' => $schedules], 200);

            } catch (\Throwable $error) {
                // Tangani error jika ada masalah
                return response()->json(['status' => 'failed', 'message' => 'Error: ' . $error->getMessage()]);
            }
        }

        // DESTROY Data Routes
        public function destroy($id)
        {
            $schedules = schedules::find($id);

            if (!$schedules) {
                return response()->json(['message' => 'Bus not found'], 404);
            }

            $schedules->delete();

            return response()->json(['message' => 'Bus deleted successfully'], 200);
        }
}
