<?php

namespace Modules\Resident\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Resident\Models\Resident;
use Illuminate\Support\Facades\Storage;

class ResidentController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        $resident = Resident::where('user_id', $user->id)->first();

        if (!$resident) {
            return response()->json([
                'status' => false,
                'message' => 'Anda belum melengkapi biodata',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data profile penghuni',
            'data' => $resident
        ], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'id_card_number' => 'required|string|max:20',
            'phone_number' => 'required|string|max:20',
            'gender' => 'required|in:male,female',
            'job' => 'nullable|string',
            'address_ktp' => 'required|string',
            'ktp_photo' => 'nullable|image|max:2048',
        ]);

        $resident = Resident::where('user_id', $user->id)->first();

        $ktpPath = $resident ? $resident->ktp_photo_path : null;

        if ($request->hasFile('ktp_photo')) {
            if ($resident && $resident->ktp_photo_path) {
                Storage::disk('public')->delete($resident->ktp_photo_path);
            }

            $ktpPath = $request->file('ktp_photo')->store('ktp_images', 'public');
        }

        $resident = Resident::updateOrCreate(
            ['user_id' => $user->id],
            [
                'id_card_number' => $request->id_card_number,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                'job' => $request->job,
                'address_ktp' => $request->address_ktp,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_name,
                'ktp_photo_path' => $ktpPath,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Biodata berhasil disimpan',
            'data' => $resident
        ], 200);
    }
}
