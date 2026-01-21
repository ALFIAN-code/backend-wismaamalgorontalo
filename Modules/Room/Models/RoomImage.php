<?php

namespace Modules\Room\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class RoomImage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['image_url'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            if ($image->image_path && Storage::exists($image->image_path)) {
                Storage::delete($image->image_path);
            }
        });
    }
}
