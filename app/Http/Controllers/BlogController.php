<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()
            ->with('user') // Eager load author
            ->latest('published_at')
            ->paginate(12);

        return Inertia::render('Blog/Index', [
            'posts' => $posts
        ]);
    }

    public function show($slug)
    {
        $post = BlogPost::published()
            ->where('slug', $slug)
            ->with('user')
            ->firstOrFail();

        // Basic SEO data structure suitable for passing to SEO component
        $seoData = [
            'title' => $post->title . ' - Aquawasouk Blog',
            'description' => $post->excerpt, 
            'image' => $post->image_url,
            'type' => 'article',
            'canonical' => route('blog.show', $post->slug),
        ];

        return Inertia::render('Blog/Show', [
            'post' => $post,
            'seo' => $seoData
        ]);
    }
}
