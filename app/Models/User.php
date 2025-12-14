<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        'provider_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_confirmed_at' => 'datetime',
    ];

    /**
     * Mutator to encrypt provider token before saving.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setProviderTokenAttribute($value): void
    {
        $this->attributes['provider_token'] = $value === null ? null : Crypt::encryptString($value);
    }

    /**
     * Accessor to decrypt provider token when reading.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getProviderTokenAttribute($value)
    {
        return $value === null ? null : Crypt::decryptString($value);
    }
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }
}
