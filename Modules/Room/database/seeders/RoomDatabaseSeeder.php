<?php

namespace Modules\Room\database\seeders;

use Illuminate\Database\Seeder;

class RoomDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            [
                'number' => '101',
                'type' => 'Standard',
                'price' => 500000,
                'status' => 'available',
                'description' => 'Kamar standar dengan fasilitas lengkap, nyaman untuk penghuni jangka panjang',
                'facilities' => ['AC', 'WiFi', 'Kasur Single', 'Lemari', 'Meja Belajar'],
                'images_count' => 3,
            ],
            [
                'number' => '102',
                'type' => 'Standard',
                'price' => 500000,
                'status' => 'available',
                'description' => 'Kamar standar dengan pemandangan taman',
                'facilities' => ['AC', 'WiFi', 'Kasur Single', 'Lemari'],
                'images_count' => 2,
            ],
            [
                'number' => '201',
                'type' => 'Deluxe',
                'price' => 750000,
                'status' => 'occupied',
                'description' => 'Kamar deluxe dengan kamar mandi dalam dan balkon pribadi',
                'facilities' => ['AC', 'WiFi', 'Kasur Queen', 'Lemari', 'Meja Belajar', 'Kamar Mandi Dalam', 'Balkon', 'TV'],
                'images_count' => 4,
            ],
            [
                'number' => '202',
                'type' => 'Deluxe',
                'price' => 750000,
                'status' => 'available',
                'description' => 'Kamar deluxe dengan interior modern',
                'facilities' => ['AC', 'WiFi', 'Kasur Queen', 'Lemari', 'Meja Belajar', 'Kamar Mandi Dalam', 'TV'],
                'images_count' => 3,
            ],
            [
                'number' => '301',
                'type' => 'Suite',
                'price' => 1200000,
                'status' => 'available',
                'description' => 'Kamar suite mewah dengan ruang tamu terpisah dan dapur kecil',
                'facilities' => ['AC', 'WiFi', 'Kasur King', 'Lemari Besar', 'Meja Kerja', 'Kamar Mandi Dalam', 'Balkon', 'TV', 'Kulkas', 'Dapur Kecil', 'Sofa'],
                'images_count' => 5,
            ],
            [
                'number' => '103',
                'type' => 'Standard',
                'price' => 500000,
                'status' => 'maintenance',
                'description' => 'Kamar sedang dalam perbaikan AC',
                'facilities' => ['WiFi', 'Kasur Single', 'Lemari', 'Meja Belajar'],
                'images_count' => 2,
            ],
            [
                'number' => '104',
                'type' => 'Standard',
                'price' => 500000,
                'status' => 'available',
                'description' => 'Kamar standar dekat dengan area parkir',
                'facilities' => ['AC', 'WiFi', 'Kasur Single', 'Lemari'],
                'images_count' => 2,
            ],
            [
                'number' => '203',
                'type' => 'Deluxe',
                'price' => 750000,
                'status' => 'occupied',
                'description' => 'Kamar deluxe dengan jendela besar dan pencahayaan alami',
                'facilities' => ['AC', 'WiFi', 'Kasur Queen', 'Lemari', 'Meja Belajar', 'Kamar Mandi Dalam', 'Balkon'],
                'images_count' => 3,
            ],
        ];

        foreach ($rooms as $roomData) {
            $imagesCount = $roomData['images_count'];
            unset($roomData['images_count']);

            $room = \Modules\Room\Models\Room::create($roomData);

            // Buat dummy images untuk setiap room
            for ($i = 0; $i < $imagesCount; $i++) {
                \Modules\Room\Models\RoomImage::create([
                    'room_id' => $room->id,
                    'image_path' => 'rooms/dummy-room-' . $room->number . '-' . ($i + 1) . '.jpg',
                    'order' => $i,
                ]);
            }
        }

        $this->command->info('✅ Berhasil membuat ' . count($rooms) . ' data room dengan facilities dan images!');
    }
}
