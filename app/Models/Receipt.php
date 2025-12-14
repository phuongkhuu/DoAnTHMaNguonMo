<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Receipt extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'session_id',
        'status',
        'meta',
        'total',
        'momo_order_id',
        'payment_gateway',
        'payment_state',
        'paid_at',
        'transaction_id',
    ];

    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
        'total' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($r) {
            if (empty($r->uuid)) {
                $r->uuid = (string) Str::uuid();
            }
        });

        static::saving(function ($r) {
            if ($r->relationLoaded('items')) {
                $r->total = $r->items->sum(function ($item) {
                    return ($item->price ?? 0) * ($item->quantity ?? 0);
                });
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(UserCart::class, 'receipt_id');
    }
}

