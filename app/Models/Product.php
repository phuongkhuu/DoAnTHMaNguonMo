<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    protected $fillable = ['category_id','name','slug','short_description','description','price','image','is_best_seller'];
    public function category() { return $this->belongsTo(Category::class); }
    public function getRouteKeyName()
    {
        return 'slug';
    }
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }
}
