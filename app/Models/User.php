<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // 👇 Tell Laravel to use your custom table
    protected $table = 'user_account';

    // 👇 Set your primary key name
    protected $primaryKey = 'user_id';

    // 👇 If your primary key is not "id" and not an incrementing string
    public $incrementing = true;
    protected $keyType = 'int';

    // 👇 Define fillable fields for mass assignment
    protected $fillable = [
        'fullname',
        'email',
        'password',
        'phone',
        'address',
        'role',
    ];

    // 👇 Hide sensitive fields
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 👇 Cast attributes (auto hash password if you’re on Laravel 10+)
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
        ];
    }
}
