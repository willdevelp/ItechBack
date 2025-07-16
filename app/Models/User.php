<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'address',
        'country',
        'role',
        'postal_code',
        'phone',
        'password',
        'phone_verified_at',
        'newsletter_opt_in',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'newsletter_opt_in' => 'boolean',
        ];
    }

    public function sendPasswordResetNotification($token)
    {
        $url = config('app.frontend_url').'/reset-password?token='.$token.'&email='.$this->email;
        $this->notify(new ResetPasswordNotification($url));
    }

    // Nouvelle méthode pour la vérification par téléphone
    public function markPhoneAsVerified()
    {
        $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function paniers() {
        return $this->hasMany(Panier::class);
    }

    public function commands() {
        return $this->hasMany(Command::class);
    }

    public function favorites() {
        return $this->hasMany(Favorite::class);
    }
}