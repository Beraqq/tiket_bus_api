<?php

namespace App\Http\Controllers;

use App\Models\routes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoutesController extends Controller
{
    // Get Data
    public function index()
    {
        return response()->json(routes::all(), 200);
    }

    // Post Data
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'route_id'=> 'required|string|max:255|unique:routes,route_id',
                'departure' => 'required|string|max:255',
                'destination' => 'required|string|max:255'
            ]);

            // Jika validasi gagal, kirimkan pesan error
            if ($validator->fails()) {
                $validatorMessage = $validator->errors()->first();
                return response()->json(['status' => 'failed', 'message' => $validatorMessage]);
            }

            // Simpan data bus ke database jika validasi berhasil
            $bus = routes::create([
                'route_id' => $request->route_id,
                'departure' => $request->departure,
                'destination' => $request->destination,
            ]);

            // Kembalikan response sukses dengan status 201 (Created)
            return response()->json(['status' => 'success', 'message' => 'Routes created successfully', 'data' => $bus], 201);

        } catch (\Throwable $error) {
            // Tangani error jika ada masalah
            return response()->json(['status' => 'failed', 'message' => 'Error: ' . $error->getMessage()]);
        }
    }

    // GET Data Routes
    public function show($id)
    {
        $routes = routes::find($id);

        if (!$routes) {
            return response()->json(['message' => 'Route not found'], 404);
        }

        return response()->json($routes, 200);
    }

    // PUT Data Routes
    public function update(Request $request, $id)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'route_id' => 'required|string|max:255|unique:routes,route_id,' . $id, // Update validasi untuk mengecualikan ID saat ini
                'departure' => 'required|string|max:255',
                'destination' => 'required|string|max:255',
            ]);

            // Jika validasi gagal, kirimkan pesan error
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Cari route berdasarkan ID yang diberikan
            $route = routes::find($id);

            // Jika route tidak ditemukan
            if (!$route) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Route not found'
                ], 404);
            }

            // Perbarui data route yang ada
            $route->update([
                'route_id' => $request->route_id,
                'departure' => $request->departure,
                'destination' => $request->destination,
            ]);

            // Kembalikan response sukses dengan status 200 (OK)
            return response()->json([
                'status' => 'success',
                'message' => 'Route updated successfully',
                'data' => $route
            ], 200);

        } catch (\Throwable $error) {
            // Tangani error jika ada masalah
            return response()->json([
                'status' => 'failed',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }


    // DESTROY Data Routes
    public function destroy($id)
    {
        $routes = routes::find($id);

        if (!$routes) {
            return response()->json(['message' => 'Bus not found'], 404);
        }

        $routes->delete();

        return response()->json(['message' => 'Bus deleted successfully'], 200);
    }
}
