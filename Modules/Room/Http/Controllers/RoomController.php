<?php

namespace Modules\Room\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Room\Models\Room;
use App\Http\Controllers\Controller;
use Modules\Room\Http\Requests\StoreRoomRequest;
use Modules\Room\Transformers\RoomResource;
use App\Traits\ApiResponse;

class RoomController extends Controller
{
    use \App\Traits\ApiResponse;

    public function index()
    {
        $room = Room::latest()->get();
        return $this->apiSuccess(RoomResource::collection($room), 'List data kamar');
    }

    public function store(StoreRoomRequest $request)
    {
        $room = Room::create($request->validated());

        return $this->apiSuccess($room, 'Kamar berhasil ditambahkan', 201);
    }

    public function show($id)
    {
        $room = Room::find($id);
        if (!$room) return $this->apiError('Kamar tidak ditemukan', 404);

        return $this->apiSuccess($room, 'Detail data kamar');
    }

    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return $this->apiError('Kamar tidak ditemukan', 404);
        }

        $validated = $request->validate([
            'number' => 'required|unique:rooms,number,' . $id,
            'type' => 'required|string',
            'price' => 'required|numeric',
            'status' => 'required|in:available,occupied,maintenance',
            'description' => 'nullable|string',
        ]);

        $room->update($validated);

        return $this->apiSuccess($room, 'Kamar berhasil diperbarui');
    }

    public function destroy($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return $this->apiError('Kamar tidak ditemukan', 404);
        }

        $room->delete();

        return response()->json([
            'status' => true,
            'message' => 'Kamar berhasil dihapus',
        ], 200);
    }
}
