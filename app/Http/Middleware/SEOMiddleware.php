<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SEOMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Default SEO data
        $seoData = [
            'title' => config('app.name', 'Aquawasouk') . ' - Marketplace Aquariophile',
            'description' => 'Découvrez les meilleurs produits aquariophiles sur Aquawasouk. Achetez auprès de vendeurs vérifiés.',
            'keywords' => 'aquariophilie, aquarium, poissons, plantes aquatiques, matériel aquarium, marketplace',
            'image' => asset('logo/logo.png'),
            'url' => $request->url(),
            'type' => 'website',
            'site_name' => config('app.name', 'Aquawasouk'),
            'locale' => app()->getLocale(),
            'twitter_card' => 'summary_large_image',
            'canonical' => $request->url(),
        ];

        // Share SEO data with Inertia
        Inertia::share('seo', $seoData);

        return $next($request);
    }
}
