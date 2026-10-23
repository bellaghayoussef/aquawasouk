<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/Banners/Index', [
            'banners' => Banner::orderBy('sort_order')->orderBy('created_at', 'desc')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Banners/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'page' => 'required|string|in:home,vendors,products,product_detail',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            'link_url' => 'nullable|url',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ], [
            'image.uploaded' => 'The file failed to upload. It may be larger than the server allows (upload_max_filesize).',
        ]);

        $imagePath = $request->file('image')->store('banners', 'public');
        Banner::create([
            'title' => $validated['title'],
            'page' => $validated['page'],
            'image_path' =>  $imagePath,
            'link_url' => $validated['link_url'],
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return Inertia::render('Admin/Banners/Edit', [
            'banner' => $banner
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'page' => 'required|string|in:home,vendors,products,product_detail',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            'link_url' => 'nullable|url',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ], [
            'image.uploaded' => 'The file failed to upload. It may be larger than the server allows.',
        ]);

        $data = $request->only(['title', 'page', 'link_url', 'sort_order', 'is_active']);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }
        $banner->delete();

        return redirect()->back()->with('success', 'Banner deleted successfully.');
    }
}
