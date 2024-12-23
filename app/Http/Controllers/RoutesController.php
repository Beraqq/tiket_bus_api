<?php

namespace App\Http\Controllers;

use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoutesController extends Controller
{
    public function index()
    {
        try {
            $routes = Route::all();
            return response()->json([
                'status' => 'success',
                'data' => $routes
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
                'route_id' => 'required|unique:routes|max:255',
                'departure' => 'required|string|max:255',
                'destination' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $route = Route::create([
                'route_id' => $request->route_id,
                'departure' => $request->departure,
                'destination' => $request->destination
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Route created successfully',
                'data' => $route
            ], 201);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function show($route_id)
    {
        try {
            $route = Route::where('route_id', $route_id)->first();

            if (!$route) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Route not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $route
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $route_id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'departure' => 'required|string|max:255',
                'destination' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $route = Route::where('route_id', $route_id)->first();

            if (!$route) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Route not found'
                ], 404);
            }

            $route->update([
                'departure' => $request->departure,
                'destination' => $request->destination
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Route updated successfully',
                'data' => $route
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }

    public function destroy($route_id)
    {
        try {
            $route = Route::where('route_id', $route_id)->first();

            if (!$route) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Route not found'
                ], 404);
            }

            $route->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Route deleted successfully'
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }
}
