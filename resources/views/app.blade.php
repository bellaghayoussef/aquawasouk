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
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ config('app.name', 'AquawaSouk') }} - Marketplace E-commerce Multi-Catégories en Tunisie">
        <meta property="og:description" content="Achetez et vendez en ligne en Tunisie sur AquawaSouk : High-Tech, Mode, Maison, Électroménager, Beauté, Sport et Bricolage. Livraison rapide dans tous les gouvernorats et paiement à la livraison.">
        <meta property="og:image" content="{{ asset('uploads/cover.jpeg') }}">
        <meta property="og:site_name" content="{{ config('app.name', 'AquawaSouk') }}">
        <meta property="og:locale" content="fr_TN">
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="{{ config('app.name', 'AquawaSouk') }} - Marketplace E-commerce en Tunisie">
        <meta name="twitter:description" content="Votre destination shopping en ligne en Tunisie : High-Tech, Mode, Maison, Beauté, Électroménager et plus au meilleur prix avec livraison partout en Tunisie.">
        <meta name="twitter:image" content="{{ asset('uploads/cover.jpeg') }}">
        
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
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
