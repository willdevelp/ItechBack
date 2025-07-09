<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Panier extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [

        ];
    }

    /**
     * Get the product associated with the panier.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'panier_produit')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get the user that owns the panier.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
