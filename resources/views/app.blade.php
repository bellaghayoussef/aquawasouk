<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Aquawasouk') }}</title>
        
        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('logo/logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('logo/logo.png') }}">
        
        <!-- SEO Meta Tags -->
        <meta name="description" content="AquawaSouk - Votre marketplace e-commerce multi-catégories n°1 en Tunisie. Découvrez des milliers de produits : High-Tech, Mode & Vêtements, Maison & Cuisine, Beauté, Bricolage, Sport et plus. Meilleurs prix, livraison rapide partout en Tunisie et paiement à la livraison.">
        <meta name="keywords" content="marketplace Tunisie, e-commerce Tunisie, achat en ligne, vente en ligne, shopping Tunisie, high-tech, smartphones, mode homme femme, électroménager, maison et cuisine, parfums, cosmétiques, sport, bricolage, boutique en ligne, livraison domicile, paiement à la livraison">
        <meta name="author" content="AquawaSouk">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        
        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url()->current() }}">
        
        @php
            $currentProduct = $page['props']['product'] ?? null;
            $currentSeo = $page['props']['seo'] ?? null;
            
            $ogTitle = $currentSeo['title'] ?? ($currentProduct ? ($currentProduct['name'] . ' - AquawaSouk') : config('app.name', 'AquawaSouk') . ' - Marketplace E-commerce en Tunisie');
            $ogDescription = $currentSeo['description'] ?? ($currentProduct ? ($currentProduct['description'] ? \Illuminate\Support\Str::limit(strip_tags($currentProduct['description']), 160) : 'Achetez ' . $currentProduct['name'] . ' sur AquawaSouk avec livraison rapide partout en Tunisie et paiement à la livraison.') : 'Achetez et vendez en ligne en Tunisie sur AquawaSouk : High-Tech, Mode, Maison, Électroménager, Beauté, Sport et Bricolage. Livraison rapide dans tous les gouvernorats et paiement à la livraison.');
            $ogImage = $currentSeo['image'] ?? ($currentProduct && !empty($currentProduct['primary_image']['image_path']) ? asset('uploads/' . $currentProduct['primary_image']['image_path']) : asset('uploads/cover.jpeg'));
            $ogUrl = url()->current();
        @endphp

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $ogUrl }}">
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:site_name" content="{{ config('app.name', 'AquawaSouk') }}">
        <meta property="og:locale" content="fr_TN">
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ $ogUrl }}">
        <meta name="twitter:title" content="{{ $ogTitle }}">
        <meta name="twitter:description" content="{{ $ogDescription }}">
        <meta name="twitter:image" content="{{ $ogImage }}">
        
        <!-- Additional SEO -->
        <meta name="theme-color" content="#0ea5e9">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        
        <!-- Preconnect to external resources -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="dns-prefetch" href="https://fonts.bunny.net">
        
        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
           @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead

        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-4R8M0Q6V67"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'G-4R8M0Q6V67');
        </script>

        <!-- Meta Pixel Code -->
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '2573875423109518');
        fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=2573875423109518&ev=PageView&noscript=1"
        /></noscript>
        <!-- End Meta Pixel Code -->
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
