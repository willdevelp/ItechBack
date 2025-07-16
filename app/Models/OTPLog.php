<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier',
        'method',
        'code',
        'ip_address',
        'user_agent',
        'verified_at'
    ];
}