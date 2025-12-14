<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    protected function ensureImageDir()
    {
        $dir = public_path('image');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    protected function saveContentToPublicImage(string $contents, string $ext): string
    {
        $this->ensureImageDir();
        $filename = uniqid() . '.' . $ext;
        $path = public_path('image/' . $filename);
        file_put_contents($path, $contents);
        return '/image/' . $filename; // public URL path
    }

    protected function deletePublicImageIfExists(?string $imageUrl)
    {
        if (!$imageUrl) return;
        // Expecting stored value like '/image/filename.ext' or full URL
        $parsed = parse_url($imageUrl);
        $path = $parsed['path'] ?? $imageUrl;
        // normalize leading slash
        $path = ltrim($path, '/');
        $publicPath = public_path($path);
        if (File::exists($publicPath)) {
            File::delete($publicPath);
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

        $paginated = $query->paginate($perPage)->withQueryString();
        return response()->json($paginated);
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

        // handle uploaded file first -> move to public/image
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $this->ensureImageDir();
            $filename = uniqid() . '.' . $ext;
            $file->move(public_path('image'), $filename);
            $data['image'] = '/image/' . $filename;
        }
        // if no uploaded file but image_url provided, fetch and store server-side into public/image
        elseif (!empty($data['image_url'])) {
            try {
                $res = Http::timeout(15)->get($data['image_url']);
                if (!$res->ok()) {
                    return response()->json(['message' => 'Không thể tải ảnh từ URL (upstream error)'], 422);
                }
                $contentType = $res->header('Content-Type', '');
                if (!Str::startsWith($contentType, 'image/')) {
                    return response()->json(['message' => 'URL không trả về ảnh'], 422);
                }
                $ext = explode('/', $contentType)[1] ?? 'jpg';
                $data['image'] = $this->saveContentToPublicImage($res->body(), $ext);
            } catch (\Exception $e) {
                Log::error('Fetch image failed (store): ' . $e->getMessage(), ['url' => $data['image_url'] ?? null]);
                return response()->json(['message' => 'Lỗi khi tải ảnh từ URL'], 422);
            }
        }

        $product = Product::create($data);
        return response()->json($product, 201);
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

        // regenerate slug if needed and avoid duplicates
        $data['slug'] = Str::slug($data['name']);
        $base = $data['slug']; $i = 1;
        while (Product::where('slug', $data['slug'])->where('id', '!=', $product->id)->exists()) {
            $data['slug'] = $base . '-' . $i++;
        }

        // handle uploaded file -> move to public/image and delete old if exists
        if ($request->hasFile('image')) {
            // delete old if in public/image
            $this->deletePublicImageIfExists($product->image);

            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $this->ensureImageDir();
            $filename = uniqid() . '.' . $ext;
            $file->move(public_path('image'), $filename);
            $data['image'] = '/image/' . $filename;
        }
        // if no uploaded file but image_url provided, fetch and store server-side (replace old)
        elseif (!empty($data['image_url'])) {
            try {
                $res = Http::timeout(15)->get($data['image_url']);
                if (!$res->ok()) {
                    return response()->json(['message' => 'Không thể tải ảnh từ URL (upstream error)'], 422);
                }
                $contentType = $res->header('Content-Type', '');
                if (!Str::startsWith($contentType, 'image/')) {
                    return response()->json(['message' => 'URL không trả về ảnh'], 422);
                }
                // delete old stored file if it exists in public/image
                $this->deletePublicImageIfExists($product->image);

                $ext = explode('/', $contentType)[1] ?? 'jpg';
                $data['image'] = $this->saveContentToPublicImage($res->body(), $ext);
            } catch (\Exception $e) {
                Log::error('Fetch image failed (update): ' . $e->getMessage(), ['url' => $data['image_url'] ?? null, 'product_id' => $product->id]);
                return response()->json(['message' => 'Lỗi khi tải ảnh từ URL'], 422);
            }
        }

        $product->update($data);
        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $this->deletePublicImageIfExists($product->image);

        $product->delete();
        return response()->json(['message' => 'deleted']);
    }
}
