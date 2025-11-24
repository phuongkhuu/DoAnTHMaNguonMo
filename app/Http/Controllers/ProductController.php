<?php

namespace App\Http\Controllers;

use App\Models\Category;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product = Product::paginate(5);
        return view('indexProduct', compact('product'))->with('i', (request()->input('page', 1) -1) *5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('createProduct');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'product_name' => 'required|string|max:255',
        'price'        => 'required|numeric',
        'discount'     => 'nullable|numeric',
        'stock'        => 'nullable|integer',
        'image'        => 'nullable|string',
    ]);

    Product::create($request->all());

    return redirect()->route('product.index')
                     ->with('thongbao','Thêm thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('editProduct', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $product->update($request->all());
        return redirect()->route('product.index')->with('thongbao', 'Cập nhật thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('product.index')->with('thongbao', 'Xóa thành công');
    }
}
