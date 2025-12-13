<?php

namespace Modules\Room\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Room\Models\Room;
use App\Http\Controllers\Controller;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::latest()->get();

        return response()->json([
            'status' => true,
            'message' => "List Data Kamar",
            'data' => $rooms,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|unique:rooms,number',
            'type' => 'required|string',
            'price' => 'required|numeric',
            'status' => 'required|in:available,occupied,maintenance',
            'description' => 'nullable|string',
        ]);

        $room = Room::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Kamar berhasil ditambahkan',
            'data' => $room
        ], 201);
    }

    public function show($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'status' => false,
                'message' => 'Kamar tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail Kamar',
            'data' => $room
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'status' => false,
                'message' => 'Kamar tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'number' => 'required|unique:room,number,' . $id,
            'type' => 'required|string',
            'price' => 'required|numeric',
            'status' => 'required|in:available,occupied,maintenance',
            'description' => 'nullable|string',
        ]);

        $room->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data kamar berhasil diperbarui',
            'data' => $room
        ], 200);
    }

    public function destroy($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json(['status' => false, 'message' => 'Kamar tidak ditemukan'], 404);
        }

        $room->delete();

        return response()->json([
            'status' => true,
            'message' => 'Kamar berhasil dihapus',
        ], 200);
    }
}
