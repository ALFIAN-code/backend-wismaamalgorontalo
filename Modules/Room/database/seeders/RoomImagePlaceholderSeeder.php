<?php

namespace Modules\Room\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class RoomImagePlaceholderSeeder extends Seeder
{
    /**
     * Generate placeholder images untuk room dummy
     * Catatan: Seeder ini opsional, hanya jika ingin generate gambar placeholder otomatis
     * Memerlukan package intervention/image
     */
    public function run(): void
    {
        // Pastikan direktori rooms ada
        if (!Storage::disk('public')->exists('rooms')) {
            Storage::disk('public')->makeDirectory('rooms');
        }

        $rooms = \Modules\Room\Models\Room::with('images')->get();

        foreach ($rooms as $room) {
            foreach ($room->images as $image) {
                $filename = basename($image->image_path);
                $path = storage_path('app/public/' . $image->image_path);

                // Skip jika file sudah ada
                if (file_exists($path)) {
                    continue;
                }

                // Buat direktori jika belum ada
                $directory = dirname($path);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Generate placeholder image sederhana (800x600)
                $width = 800;
                $height = 600;

                $img = imagecreatetruecolor($width, $height);

                // Background color (abu-abu terang)
                $bgColor = imagecolorallocate($img, 240, 240, 240);
                imagefill($img, 0, 0, $bgColor);

                // Text color (abu-abu gelap)
                $textColor = imagecolorallocate($img, 100, 100, 100);

                // Text
                $text = "Room " . $room->number;
                $text2 = $room->type;

                // Tulis text di tengah
                imagestring($img, 5, ($width / 2) - 50, ($height / 2) - 20, $text, $textColor);
                imagestring($img, 4, ($width / 2) - 40, ($height / 2) + 10, $text2, $textColor);

                // Save image
                imagejpeg($img, $path, 80);
                imagedestroy($img);
            }
        }

        $this->command->info('✅ Berhasil generate placeholder images untuk rooms!');
    }
}
