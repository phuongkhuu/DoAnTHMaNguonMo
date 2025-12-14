<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BannerController extends Controller
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
        $parsed = parse_url($imageUrl);
        $path = $parsed['path'] ?? $imageUrl;
        $path = ltrim($path, '/');
        $publicPath = public_path($path);
        if (File::exists($publicPath)) {
            File::delete($publicPath);
        }
    }

    public function index()
    {
        $banners = Banner::orderBy('sort_order')->get();
        return response()->json($banners);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sort_order' => 'nullable|integer',
            'active' => 'sometimes|boolean',
            'image' => 'nullable|file|image|max:5120',
            'image_url' => 'nullable|url',
        ]);

        // always set alt to "place-holder"
        $data['alt'] = 'place-holder';

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = uniqid() . '.' . $ext;
            $file->move(public_path('image'), $filename);
            $data['image'] = '/image/' . $filename;
        } elseif (!empty($data['image_url'])) {
            try {
                $res = Http::timeout(15)->get($data['image_url']);
                if ($res->ok() && Str::startsWith($res->header('Content-Type', ''), 'image/')) {
                    $ext = explode('/', $res->header('Content-Type'))[1] ?? 'jpg';
                    $data['image'] = $this->saveContentToPublicImage($res->body(), $ext);
                } else {
                    return response()->json(['message' => 'URL không hợp lệ'], 422);
                }
            } catch (\Exception $e) {
                Log::error('Fetch banner image failed (store): ' . $e->getMessage());
                return response()->json(['message' => 'Lỗi khi tải ảnh từ URL'], 422);
            }
        } else {
            return response()->json(['message' => 'Ảnh là bắt buộc'], 422);
        }

        $banner = Banner::create($data);
        return response()->json($banner, 201);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'sort_order' => 'nullable|integer',
            'active' => 'sometimes|boolean',
            'image' => 'nullable|file|image|max:5120',
            'image_url' => 'nullable|url',
        ]);

        // always set alt to "place-holder"
        $data['alt'] = 'place-holder';

        if ($request->hasFile('image')) {
            $this->deletePublicImageIfExists($banner->image);
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = uniqid() . '.' . $ext;
            $file->move(public_path('image'), $filename);
            $data['image'] = '/image/' . $filename;
        } elseif (!empty($data['image_url'])) {
            try {
                $res = Http::timeout(15)->get($data['image_url']);
                if ($res->ok() && Str::startsWith($res->header('Content-Type', ''), 'image/')) {
                    $this->deletePublicImageIfExists($banner->image);
                    $ext = explode('/', $res->header('Content-Type'))[1] ?? 'jpg';
                    $data['image'] = $this->saveContentToPublicImage($res->body(), $ext);
                } else {
                    return response()->json(['message' => 'URL không hợp lệ'], 422);
                }
            } catch (\Exception $e) {
                Log::error('Fetch banner image failed (update): ' . $e->getMessage());
                return response()->json(['message' => 'Lỗi khi tải ảnh từ URL'], 422);
            }
        }

        $banner->update($data);
        return response()->json($banner);
    }

    public function destroy(Banner $banner)
    {
        $this->deletePublicImageIfExists($banner->image);
        $banner->delete();
        return response()->json(['message' => 'deleted']);
    }
}
