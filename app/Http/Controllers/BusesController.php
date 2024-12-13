<?php
namespace App\Http\Controllers;

use App\Models\buses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BusesController extends Controller
{
    // GET /api/buses
    public function index()
    {
        return response()->json(buses::all(), 200);
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
            $bus = buses::create([
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
        $bus = buses::find($id);

        if (!$bus) {
            return response()->json(['message' => 'Bus not found'], 404);
        }

        return response()->json($bus, 200);
    }

    // PUT /api/buses/{id}
    public function update(Request $request, $id)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'bus_code' => 'required|max:255|unique:buses,bus_code,' . $id,
                'class' => 'required|in:economy,executive,VVIP',
                'facilities' => 'nullable|string',
                'total_seats' => 'required|integer|min:1',
                'price_per_seat' => 'required|numeric|min:0',
            ]);

            // Jika validasi gagal, kirimkan pesan error
            if ($validator->fails()) {
                $validatorMessage = $validator->errors()->first();
                return response()->json(['status' => 'failed', 'message' => $validatorMessage]);
            }

            // Cari bus berdasarkan ID yang diberikan
            $bus = buses::find($id);

            // Perbarui data bus yang ada
            $bus->update([
                'bus_code' => $request->bus_code,
                'class' => $request->class,
                'facilities' => $request->facilities,
                'total_seats' => $request->total_seats,
                'price_per_seat' => $request->price_per_seat,
            ]);

            // Kembalikan response sukses dengan status 200 (OK)
            return response()->json(['status' => 'success', 'message' => 'Bus updated successfully', 'data' => $bus], 200);

        } catch (\Throwable $error) {
            // Tangani error jika ada masalah
            return response()->json(['status' => 'failed', 'message' => 'Error: ' . $error->getMessage()]);
        }
    }

    // DELETE /api/buses/{id}
    public function destroy($id)
    {
        $bus = buses::find($id);

        if (!$bus) {
            return response()->json(['message' => 'Bus not found'], 404);
        }

        $bus->delete();

        return response()->json(['message' => 'Bus deleted successfully'], 200);
    }
}
