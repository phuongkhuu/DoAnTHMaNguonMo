<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function list()
    {
        return response()->json(Category::select('id', 'name', 'slug')->orderBy('sort_order')->get());
    }
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();
        return response()->json($categories);
    }

    
    public function destroy(Category $category)
    {
        if ($category->image && str_starts_with($category->image, '/storage/')) {
            $old = ltrim(str_replace('/storage/', '', $category->image), '/');
            Storage::disk('public')->delete($old);
        }
        $category->delete();
        return response()->json(['message' => 'deleted']);
    }
}
