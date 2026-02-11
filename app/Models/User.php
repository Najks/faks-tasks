<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use HasApiTokens, Notifiable;
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'username',
    ];

    protected $hidden = [
        "password",
    ];

    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];
}
