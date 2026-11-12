<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        // Fetch only active items with necessary fields
        $products = Product::where('status', 'active')
            ->with('images')
            ->select('id', 'slug', 'name', 'updated_at')
            ->get();
            
        $categories = Category::select('id', 'slug', 'name', 'updated_at')->get();
        
        $vendors = Vendor::where('status', 'active')
            ->select('id', 'slug', 'shop_name', 'updated_at')
            ->get();

        $posts = BlogPost::published()
            ->select('id', 'slug', 'title', 'published_at', 'updated_at')
            ->get();

        return response()->view('sitemap.index', [
            'products' => $products,
            'categories' => $categories,
            'vendors' => $vendors,
            'posts' => $posts,
        ])->header('Content-Type', 'text/xml');
    }

    public function robots()
    {
        return response()->view('robots')->header('Content-Type', 'text/plain');
    }

    public function feed()
{
    $products = Product::with(['vendor', 'primaryImage'])
        ->where('status', 'active')
        ->get();

    $xml = new \SimpleXMLElement('<rss/>');
    $xml->addAttribute('version', '2.0');
    $xml->addAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

    $channel = $xml->addChild('channel');
    $channel->addChild('title', 'AquaSouk Products');
    $channel->addChild('link', url('/'));
    $channel->addChild('description', 'Product feed for Facebook Catalog');

    foreach ($products as $product) {

        $item = $channel->addChild('item');

        $item->addChild('g:id', $product->id, 'http://base.google.com/ns/1.0');
        $item->addChild('g:title', htmlspecialchars($product->name), 'http://base.google.com/ns/1.0');
        $item->addChild('g:description', htmlspecialchars($product->description), 'http://base.google.com/ns/1.0');
        $item->addChild('g:link', url('/product/' . $product->slug), 'http://base.google.com/ns/1.0');

        if ($product->primaryImage && !empty($product->primaryImage->image_path)) {
            $imagePath = $product->primaryImage->image_path;
            $imageUrl = (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://'))
                ? $imagePath
                : url('/uploads/' . ltrim(preg_replace('#^/?uploads/#', '', $imagePath), '/'));

            $item->addChild(
                'g:image_link',
                $imageUrl,
                'http://base.google.com/ns/1.0'
            );
        }

        $price = $product->discount_price ?? $product->price;
        $item->addChild('g:price', number_format($price, 2) . ' TND', 'http://base.google.com/ns/1.0');

        $availability = $product->stock > 0 ? 'in stock' : 'out of stock';
        $item->addChild('g:availability', $availability, 'http://base.google.com/ns/1.0');

        $item->addChild('g:brand', $product->vendor->shop_name ?? 'AquaSouk', 'http://base.google.com/ns/1.0');
        $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');
        $item->addChild('g:mpn', $product->sku, 'http://base.google.com/ns/1.0');
    }

    return response($xml->asXML(), 200)
        ->header('Content-Type', 'application/xml');
}
}
