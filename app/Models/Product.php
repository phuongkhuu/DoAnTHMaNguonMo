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

    // Các cột cho phép mass assignment
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

    // Quan hệ với Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
}
