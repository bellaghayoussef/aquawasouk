<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/Blog/Index', [
            'posts' => BlogPost::with('user')
                ->latest()
                ->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Blog/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            'is_published' => 'boolean',
        ], [
            'image.uploaded' => 'The file failed to upload. It may be larger than the server allows.',
        ]);

        $slug = Str::slug($validated['title']);
        // Ensure slug is unique
        $count = BlogPost::where('slug', 'like', $slug . '%')->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blog', 'public');
        }

        BlogPost::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'is_published' => $request->is_published ?? false,
            'published_at' => $request->is_published ? now() : null,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.blog.index')->with('success', 'Post created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BlogPost $blogPost) // Use $blogPost to match route binding
    {
        return Inertia::render('Admin/Blog/Edit', [
            'post' => $blogPost
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'is_published' => 'boolean',
        ]);

        // Update slug if title changed significantly? Usually better not to change slug to preserve SEO.
        // We will keep slug as is, or update if requested. For simplicity, keeping slug stable unless manual edit feature added. I won't update slug automatically.

        $data = [
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'is_published' => $request->is_published,
        ];

        if ($request->is_published && !$blogPost->published_at && !$blogPost->is_published) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            if ($blogPost->image_path) {
                Storage::disk('public')->delete($blogPost->image_path);
            }
            $data['image_path'] = $request->file('image')->store('blog', 'public');
        }

        $blogPost->update($data);

        return redirect()->route('admin.blog.index')->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlogPost $blogPost)
    {
        if ($blogPost->image_path) {
            Storage::disk('public')->delete($blogPost->image_path);
        }
        $blogPost->delete();

        return redirect()->back()->with('success', 'Post deleted successfully.');
    }
}
