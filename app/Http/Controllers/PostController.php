<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 12);
        $query = Post::orderBy('published_at', 'desc');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($qb) => $qb->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"));
        }

        $paginated = $query->paginate($perPage)->withQueryString();
        return response()->json($paginated);
    }

    
    public function destroy(Post $post)
    {
        if ($post->image && str_starts_with($post->image, '/storage/')) {
            $old = ltrim(str_replace('/storage/', '', $post->image), '/');
            Storage::disk('public')->delete($old);
        }
        $post->delete();
        return response()->json(['message' => 'deleted']);
    }
}
