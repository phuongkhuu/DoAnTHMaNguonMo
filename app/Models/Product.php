<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Tên bảng
    protected $table = 'product';

    // Khóa chính
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'category_id',
        'product_name',
        'description',
        'price',
        'discount',
        'stock',
        'image',
    ];

    public $timestamps = true;
}
