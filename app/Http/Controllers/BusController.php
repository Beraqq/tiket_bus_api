<?php
namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\buses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BusController extends Controller
{
    // GET /api/buses
    public function index()
    {
        try {
            $buses = Bus::all();
            return response()->json($buses, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch buses'], 500);
        }
    }

    public function getByClass($class)
    {
        try {
            $bus = Bus::where('class', $class)->first();

            if (!$bus) {
                return response()->json(['message' => 'Bus not found'], 404);
            }

            return response()->json($bus);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching bus'], 500);
        }
    }


    // POST /api/buses
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'bus_code' => 'required|unique:buses|max:255', // Pastikan bus_code unik
                'class' => 'required|in:economy,executive,VVIP',    // Kelas bus hanya boleh economy atau executive
                'facilities' => 'nullable|string',              // Fasilitas opsional dan bertipe string
                'total_seats' => 'required|integer|min:1',      // Total kursi harus lebih dari 0
                'price_per_seat' => 'required|numeric|min:0',   // Harga per kursi harus berupa angka positif
            ]);

            // Jika validasi gagal, kirimkan pesan error
            if ($validator->fails()) {
                $validatorMessage = $validator->errors()->first();
                return response()->json(['status' => 'failed', 'message' => $validatorMessage]);
            }

            // Simpan data bus ke database jika validasi berhasil
            $bus = Bus::create([
                'bus_code' => $request->bus_code,
                'class' => $request->class,
                'facilities' => $request->facilities,
                'total_seats' => $request->total_seats,
                'price_per_seat' => $request->price_per_seat,
            ]);

            // Kembalikan response sukses dengan status 201 (Created)
            return response()->json(['status' => 'success', 'message' => 'Bus created successfully', 'data' => $bus], 201);

        } catch (\Throwable $error) {
            // Tangani error jika ada masalah
            return response()->json(['status' => 'failed', 'message' => 'Error: ' . $error->getMessage()]);
        }
    }

    // GET /api/buses/{id}
    public function show($id)
    {
        $bus = Bus::find($id);
        return response()->json([
            'status' => 'success',
            'data' => [
                'bus_code' => $bus->bus_code,
                'class' => $bus->bus_class, // Pastikan field ini ada
                'total_seats' => $bus->total_seats
            ]
        ]);

        // if (!$bus) {
        //     return response()->json(['message' => 'Bus not found'], 404);
        // }

        // return response()->json($bus, 200);
    }

    // PUT /api/buses/{id}
    public function update(Request $request, $bus_code)
    {
        try {
            // Log untuk debugging
            // \Log::info('Updating bus: ' . $bus_code, $request->all());

            // Validasi input (tanpa bus_code)
            $validator = Validator::make($request->all(), [
                'class' => 'required|in:economy,executive,VVIP',
                'facilities' => 'nullable|string',
                'total_seats' => 'required|integer|min:1',
                'price_per_seat' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Cari bus berdasarkan bus_code
            $bus = Bus::where('bus_code', $bus_code)->first();

            if (!$bus) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Bus not found'
                ], 404);
            }

            // Update hanya field yang diperlukan
            $bus->update([
                'class' => $request->class,
                'facilities' => $request->facilities,
                'total_seats' => $request->total_seats,
                'price_per_seat' => $request->price_per_seat
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Bus updated successfully',
                'data' => $bus
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }


    // DELETE /api/buses/{id}
    public function destroy($id)
    {
        $bus = Bus::find($id);

        if (!$bus) {
            return response()->json(['message' => 'Bus not found'], 404);
        }

        $bus->delete();

        return response()->json(['message' => 'Bus deleted successfully'], 200);
    }
}
