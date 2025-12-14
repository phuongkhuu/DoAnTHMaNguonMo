<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    protected $fillable = ['title','slug','excerpt','content','image','published_at'];

    public function categories() {
        return $this->belongsToMany(Category::class, 'category_post', 'post_id', 'category_id');
    }
}

