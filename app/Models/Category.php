<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';          // tên bảng trong DB
    protected $primaryKey = 'category_id';  // khoá chính
    public $timestamps = false;             // bảng không có created_at, updated_at mặc định

    protected $fillable = [
        'category_name',
        'description',
    ];
}
