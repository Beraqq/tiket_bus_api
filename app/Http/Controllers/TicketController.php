<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index()
    {
        try {
            $tickets = Ticket::with('booking')->get();

            return response()->json([
                'status' => 'success',
                'data' => $tickets
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

            // Cek apakah booking sudah memiliki ticket
            $existingTicket = Ticket::where('booking_id', $request->booking_id)->first();
            if ($existingTicket) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ticket already exists for this booking'
                ], 422);
            }

            // Generate unique ticket code
            $ticket_code = 'TIX-' . strtoupper(Str::random(8));

            $ticket = Ticket::create([
                'booking_id' => $request->booking_id,
                'ticket_code' => $ticket_code
            ]);

            // Load relasi booking
            $ticket->load('booking');

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket created successfully',
                'data' => $ticket
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
            $ticket = Ticket::with('booking')->find($id);

            if (!$ticket) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ticket not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $ticket
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
                'ticket_code' => 'required|unique:tickets,ticket_code,' . $id
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $ticket = Ticket::find($id);

            if (!$ticket) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ticket not found'
                ], 404);
            }

            $ticket->update([
                'ticket_code' => $request->ticket_code
            ]);

            // Load relasi booking
            $ticket->load('booking');

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket updated successfully',
                'data' => $ticket
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
            $ticket = Ticket::find($id);

            if (!$ticket) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ticket not found'
                ], 404);
            }

            $ticket->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket deleted successfully'
            ], 200);

        } catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $error->getMessage()
            ], 500);
        }
    }
}
