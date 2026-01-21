<?php

namespace Modules\Room\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Room\Models\Room;
use App\Http\Controllers\Controller;
use Modules\Room\Http\Requests\StoreRoomRequest;
use Modules\Room\Transformers\RoomResource;
use App\Traits\ApiResponse;
use Modules\Room\Http\Requests\UpdateRoomRequest;

class RoomController extends Controller
{
    use ApiResponse;

    // menampilkan data semua kamar
    public function index()
    {
        $room = Room::with('images')->latest()->get();
        return $this->apiSuccess(RoomResource::collection($room), 'List data kamar');
    }


    // menambahkan data kamar baru
    public function store(StoreRoomRequest $request)
    {
        $room = Room::create($request->validated());

        return $this->apiSuccess($room, 'Kamar berhasil ditambahkan', 201);
    }

    // menampilkan data detail satu kamar
    public function show($id)
    {
        $room = Room::with('images')->find($id);
        if (!$room)
            return $this->apiError('Kamar tidak ditemukan', 404);

        return $this->apiSuccess(new RoomResource($room), 'Detail data kamar');
    }

    // memperbarui data kamar
    public function update(UpdateRoomRequest $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return $this->apiError('Kamar tidak ditemukan', 404);
        }

        $room->update($request->validated());

        return $this->apiSuccess($room, 'Data kamar berhasil diperbarui');
    }

    // menghapus data kamar
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

    // upload foto kamar (multiple)
    public function uploadImages(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return $this->apiError('Kamar tidak ditemukan', 404);
        }

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('rooms', 'public');

            $roomImage = $room->images()->create([
                'image_path' => $path,
                'order' => $room->images()->count() + $index,
            ]);

            $uploadedImages[] = [
                'id' => $roomImage->id,
                'url' => $roomImage->image_url,
                'order' => $roomImage->order,
            ];
        }

        return $this->apiSuccess($uploadedImages, 'Foto berhasil diupload', 201);
    }

    // hapus foto kamar
    public function deleteImage($roomId, $imageId)
    {
        $room = Room::find($roomId);

        if (!$room) {
            return $this->apiError('Kamar tidak ditemukan', 404);
        }

        $image = $room->images()->find($imageId);

        if (!$image) {
            return $this->apiError('Foto tidak ditemukan', 404);
        }

        $image->delete();

        return $this->apiSuccess(null, 'Foto berhasil dihapus');
    }
}
