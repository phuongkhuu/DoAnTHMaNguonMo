<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    // Hiển thị danh sách category
    public function index()
    {
        return Inertia::render('Categories/Index', [
            'categories' => Category::all()
        ]);
    }

    // Thêm category mới
    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:100',
            'description'   => 'nullable|string'
        ]);

        Category::create($request->all());

        return redirect()->route('categories.index')
            ->with('success', 'Category added successfully');
    }

    // Sửa category (sau này dùng)
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'category_name' => 'required|string|max:100',
            'description'   => 'nullable|string'
        ]);

        $category->update($request->all());

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully');
    }

    // Xoá category (sau này dùng)
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }
}