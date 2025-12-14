<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected function saveContentToStorage(string $contents, string $ext): string
    {
        $filename = uniqid() . '.' . $ext;
        $path = "image/{$filename}";

        Storage::disk('public')->put($path, $contents);

        return "/storage/{$path}";
    }

    protected function deleteStorageImageIfExists(?string $imageUrl)
    {
        if (!$imageUrl) return;

        // Expecting '/storage/image/filename.jpg'
        $parsed = parse_url($imageUrl);
        $path = $parsed['path'] ?? $imageUrl;

        // remove leading '/storage/'
        $relative = ltrim(str_replace('/storage/', '', $path), '/');

        if (Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 12);
        $query = Product::with('category')->orderBy('created_at', 'desc');

        if ($request->has('best_seller')) {
            $query->where('is_best_seller', 1);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($qb) => $qb->where('name', 'like', "%{$q}%")
                ->orWhere('slug', 'like', "%{$q}%")
                ->orWhere('short_description', 'like', "%{$q}%"));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        return response()->json(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|integer|min:0',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'is_best_seller' => 'sometimes|boolean',
            'image' => 'nullable|file|image|max:5120',
            'image_url' => 'nullable|url',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            $base = $data['slug']; $i = 1;
            while (Product::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $base . '-' . $i++;
            }
        }

        // Upload file to storage/app/public/image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('image', 'public');
            $data['image'] = "/storage/{$path}";
        }
        // Fetch image from URL
        elseif (!empty($data['image_url'])) {
            try {
                $res = Http::timeout(15)->get($data['image_url']);
                if (!$res->ok()) {
                    return response()->json(['message' => 'Không thể tải ảnh từ URL'], 422);
                }

                $contentType = $res->header('Content-Type', '');
                if (!Str::startsWith($contentType, 'image/')) {
                    return response()->json(['message' => 'URL không trả về ảnh'], 422);
                }

                $ext = explode('/', $contentType)[1] ?? 'jpg';
                $data['image'] = $this->saveContentToStorage($res->body(), $ext);

            } catch (\Exception $e) {
                Log::error('Fetch image failed (store): ' . $e->getMessage());
                return response()->json(['message' => 'Lỗi khi tải ảnh từ URL'], 422);
            }
        }

        return response()->json(Product::create($data), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|integer|min:0',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'is_best_seller' => 'sometimes|boolean',
            'image' => 'nullable|file|image|max:5120',
            'image_url' => 'nullable|url',
        ]);

        // regenerate slug
        $data['slug'] = Str::slug($data['name']);
        $base = $data['slug']; $i = 1;
        while (Product::where('slug', $data['slug'])->where('id', '!=', $product->id)->exists()) {
            $data['slug'] = $base . '-' . $i++;
        }

        // Upload new file
        if ($request->hasFile('image')) {
            $this->deleteStorageImageIfExists($product->image);

            $path = $request->file('image')->store('image', 'public');
            $data['image'] = "/storage/{$path}";
        }
        // Replace with URL image
        elseif (!empty($data['image_url'])) {
            try {
                $res = Http::timeout(15)->get($data['image_url']);
                if (!$res->ok()) {
                    return response()->json(['message' => 'Không thể tải ảnh từ URL'], 422);
                }

                $contentType = $res->header('Content-Type', '');
                if (!Str::startsWith($contentType, 'image/')) {
                    return response()->json(['message' => 'URL không trả về ảnh'], 422);
                }

                $this->deleteStorageImageIfExists($product->image);

                $ext = explode('/', $contentType)[1] ?? 'jpg';
                $data['image'] = $this->saveContentToStorage($res->body(), $ext);

            } catch (\Exception $e) {
                Log::error('Fetch image failed (update): ' . $e->getMessage());
                return response()->json(['message' => 'Lỗi khi tải ảnh từ URL'], 422);
            }
        }

        $product->update($data);
        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $this->deleteStorageImageIfExists($product->image);
        $product->delete();

        return response()->json(['message' => 'deleted']);
    }
}
