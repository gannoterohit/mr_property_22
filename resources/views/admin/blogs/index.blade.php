@extends('layouts.admin')

@section('title', 'Manage Blogs')

@section('admin-content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manage Blogs</h1>
            <p class="text-gray-600 text-sm">Create and publish articles for your audience.</p>
        </div>
        <a href="{{ route('admin.blogs.create') }}" class="admin-theme-bg px-4 py-2 rounded-lg shadow-sm transition flex items-center">
            <i class="fas fa-plus mr-2"></i> New Post
        </a>
    </div>

    <!-- Blog List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($blogs->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold tracking-wide">
                            <th class="p-4">Post</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Views</th>
                            <th class="p-4">Date</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($blogs as $blog)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                            @if($blog->image)
                                                <img src="{{ $blog->featured_image }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="h-full w-full flex items-center justify-center text-gray-300">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800 line-clamp-1">{{ $blog->title }}</p>
                                            <p class="text-xs text-gray-500">/{{ $blog->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if($blog->is_published)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm text-gray-600">
                                    - <!-- Could add view counter later -->
                                </td>
                                <td class="p-4 text-sm text-gray-500">
                                    {{ $blog->created_at->format('M d, Y') }}
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="p-2 admin-theme-text hover:bg-slate-50 rounded-lg transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.blogs.destroy', $blog->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">
                {{ $blogs->links() }}
            </div>
        @else
            <div class="p-12 text-center text-gray-500">
                <div class="mb-4 bg-gray-50 h-16 w-16 rounded-full flex items-center justify-center mx-auto text-gray-300">
                    <i class="fas fa-newspaper text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No blog posts yet</h3>
                <p class="mb-6">Get started by creating your first article.</p>
                <a href="{{ route('admin.blogs.create') }}" class="admin-theme-text font-medium hover:underline">Create Post</a>
            </div>
        @endif
    </div>
</div>
@endsection
