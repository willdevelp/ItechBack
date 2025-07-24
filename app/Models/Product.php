<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class Product extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'promoprice',
        'image',
        'image_public_id',
        'category_id',
        'stock',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the category associated with the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the favorites associated with the product.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
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
