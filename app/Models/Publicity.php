<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class Publicity extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    protected static function booted()
    {
        static::deleted(function ($category) {
            if ($category->image_public_id) {
                Cloudinary::destroy($category->image_public_id);
            }
        });
    }
}
