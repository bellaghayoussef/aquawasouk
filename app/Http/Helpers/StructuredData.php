<?php

namespace App\Http\Helpers;

class StructuredData
{
    /**
     * Generate Product Schema.org structured data
     */
    public static function product($product, $vendor = null)
    {
        $data = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->description ?? $product->name,
            'sku' => $product->id,
            'image' => [],
        ];

        // Add images
        if ($product->images && $product->images->count() > 0) {
            foreach ($product->images as $image) {
                $data['image'][] = asset('uploads/' . $image->image_path);
            }
        }

        // Add offers (pricing)
        $price = $product->discount_price > 0 ? $product->discount_price : $product->price;
        $data['offers'] = [
            '@type' => 'Offer',
            'url' => route('products.show', $product->slug),
            'priceCurrency' => 'TND',
            'price' => $price,
            'availability' => $product->stock > 0
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
        ];

        // Add seller (vendor)
        if ($vendor) {
            $data['brand'] = [
                '@type' => 'Brand',
                'name' => $vendor->shop_name,
            ];
        }

        // Add aggregateRating if available
        if (isset($product->rating) && $product->rating > 0) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $product->rating,
                'ratingCount' => $product->reviews_count ?? 1,
            ];
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Generate Organization Schema.org structured data
     */
    public static function organization()
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Aquawasouk',
            'url' => url('/'),
            'logo' => asset('logo/logo.png'),
            'description' => 'Marketplace aquariophile en Tunisie - Découvrez les meilleurs produits pour votre aquarium',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'email' => config('mail.from.address'),
            ],
            'sameAs' => [
                // Add social media URLs here
                // 'https://www.facebook.com/aquawasouk',
                // 'https://www.instagram.com/aquawasouk',
            ],
        ];

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Generate Store/LocalBusiness Schema.org structured data for vendor
     */
    public static function vendor($vendor)
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Store',
            'name' => $vendor->shop_name,
            'description' => $vendor->description ?? $vendor->shop_name,
            'url' => route('vendors.show', $vendor->slug),
        ];

        // Add image if logo exists
        if ($vendor->logo) {
            $data['image'] = asset('uploads/' . $vendor->logo);
        }

        // Add address if available
        if ($vendor->address || $vendor->city) {
            $data['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $vendor->address ?? '',
                'addressLocality' => $vendor->city ?? '',
                'addressCountry' => 'TN',
            ];
        }

        // Add contact info
        if ($vendor->phone) {
            $data['telephone'] = $vendor->phone;
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Generate BreadcrumbList Schema.org structured data
     */
    public static function breadcrumbs($items)
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        $position = 1;
        foreach ($items as $item) {
            $data['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Generate WebSite Schema.org structured data with search functionality
     */
    public static function website()
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Aquawasouk',
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => url('/products?search={search_term_string}'),
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
