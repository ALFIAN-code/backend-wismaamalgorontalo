<?php

namespace Modules\Resident\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam room_id int required ID Kamar yang ingin disewa. Example: 1
 * @bodyParam start_date date required Tanggal mulai sewa (Format: YYYY-MM-DD). Example: 2025-12-20
 * @bodyParam duration_months int required Durasi sewa dalam bulan (1-12). Example: 3
 */
class StoreLeaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date|after_or_equal:today',
            'duration_months' => 'required|integer|min:1|max:12',
        ];
    }

    public function messages()
    {
        return [
            'room_id.exists' => 'Kamar yang dipilih tidak valid',
            'start_date.after_or_equal' => 'Tanggal mulai sewa minimal hari ini.',
            'duration_months.max' => 'Maksimal sewa adalah 12 bulan.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
