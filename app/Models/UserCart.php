<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCart extends Model
{
    protected $table = 'user_carts';
    protected $fillable = ['user_id','product_id','name','price','quantity','meta','image','receipt_id'];
    protected $casts = [
        'meta' => 'array',
        'price' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id');
    }
}
