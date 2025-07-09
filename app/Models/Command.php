<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'payment',
        'datecom',
        'total_price',
        'status',
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

        public function products()
    {
        return $this->belongsToMany(Product::class, 'commande_produit')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function panier() {
        return $this->belongsTo(Panier::class);
    }
}
