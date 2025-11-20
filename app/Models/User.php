<?php

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user_account';
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false; // 👈 thêm dòng này

    protected $fillable = [
        'fullname',
        'email',
        'password',
        'phone',
        'address',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
