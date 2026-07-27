<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Blog::where('is_published', true);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $blogs = $query->latest()->paginate(10);
                     
        $recentBlogs = Blog::where('is_published', true)
                           ->latest()
                           ->take(5)
                           ->get();

        return view('blogs.index', compact('blogs', 'recentBlogs'));
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)
                    ->where('is_published', true)
                    ->firstOrFail();

        $recentBlogs = Blog::where('is_published', true)
            ->where('id', '!=', $blog->id)
            ->latest()
            ->take(4)
            ->get();

        return view('blogs.show', compact('blog', 'recentBlogs'));
    }
}
