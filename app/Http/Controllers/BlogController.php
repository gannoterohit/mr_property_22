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
        $query = Blog::published();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $blogs = $query->latest()->paginate(10);
                     
        $recentBlogs = Blog::published()
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
                    ->published()
                    ->firstOrFail();

        $recentBlogs = Blog::published()
            ->where('id', '!=', $blog->id)
            ->latest()
            ->take(4)
            ->get();

        return view('blogs.show', compact('blog', 'recentBlogs'));
    }
}
