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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'image' => 'nullable|file|image|max:5120',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            $base = $data['slug']; $i = 1;
            while (Category::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $base . '-' . $i++;
            }
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = Storage::url($path);
        }

        $category = Category::create($data);
        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required','string','max:255', Rule::unique('categories','slug')->ignore($category->id)],
            'image' => 'nullable|file|image|max:5120',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            if ($category->image && str_starts_with($category->image, '/storage/')) {
                $old = ltrim(str_replace('/storage/', '', $category->image), '/');
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = Storage::url($path);
        }

        $category->update($data);
        return response()->json($category);
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
