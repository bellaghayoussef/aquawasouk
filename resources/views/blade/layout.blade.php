<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AquawaSouk | Marketplace en Tunisie')</title>

    <!-- SEO Meta Tags -->
    <meta name="robots" content="@yield('meta_robots', 'index, follow, max-image-preview:large')">
    <meta name="googlebot" content="@yield('meta_googlebot', 'index, follow')">
    <meta name="author" content="AquawaSouk">
    <meta name="description"
        content="@yield('meta_description', 'Découvrez AquawaSouk, votre marketplace en Tunisie. Achetez et vendez facilement une large gamme de produits au meilleur prix.')">
    <meta name="keywords"
        content="@yield('meta_keywords', 'boutique en ligne Tunisie, marketplace Tunisie, achat en ligne, vente en ligne, e-commerce Tunisie')">

    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/webp" href="{{ asset('logo/logo-96.webp') }}">
    <link rel="icon" type="image/png" href="{{ asset('logo/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/logo-96.webp') }}">

    <!-- Theme -->
    <meta name="theme-color" content="#ffffff">

    @php
        $rawOgImage = trim($__env->yieldContent('og_image'));
        if (!empty($rawOgImage)) {
            $ogImage = \Illuminate\Support\Str::startsWith($rawOgImage, ['http://', 'https://'])
                ? $rawOgImage
                : asset(ltrim($rawOgImage, '/'));
        } else {
            $ogImage = asset('images/og-image.jpg');
        }

        // Force HTTPS in production or if current request is secure
        if (request()->isSecure() || str_contains(url()->current(), 'https://') || str_contains(config('app.url'), 'https://')) {
            $ogImage = preg_replace('/^http:/i', 'https:', $ogImage);
        }
        $ogImageSecure = preg_replace('/^http:/i', 'https:', $ogImage);
        $pageTitle = View::hasSection('title') ? trim(View::getSection('title')) : 'AquawaSouk | Marketplace en Tunisie';
        $pageDescription = View::hasSection('meta_description') ? trim(View::getSection('meta_description')) : 'Découvrez des milliers de produits et profitez d\'une expérience d\'achat simple et rapide en Tunisie.';
    @endphp

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_TN' : 'fr_TN' }}">
    <meta property="og:site_name" content="AquawaSouk">
    <meta property="og:title" content="@yield('og_title', $pageTitle)">
    <meta property="og:description" content="@yield('og_description', $pageDescription)">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImageSecure }}">
    <meta property="og:image:alt" content="{{ $pageTitle }}">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', $pageTitle)">
    <meta name="twitter:description" content="@yield('twitter_description', $pageDescription)">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="{{ $pageTitle }}">

    <!-- JSON-LD Structured Data for Search Engines -->
    @if(View::hasSection('structured_data'))
        @yield('structured_data')
    @else
        <script type="application/ld+json">
                {
                  "@@context": "https://schema.org",
                  "@@type": "WebSite",
                  "name": "AquawaSouk",
                  "url": "{{ url('/') }}",
                  "description": "Marketplace n°1 en Tunisie pour l'achat et la vente en ligne.",
                  "potentialAction": {
                    "@@type": "SearchAction",
                    "target": "{{ url('/search') }}?q={search_term_string}",
                    "query-input": "required name=search_term_string"
                  }
                }
                </script>
    @endif


    <!-- Google Fonts: Preconnect & Asynchronous Loading (Eliminates Render Blocking & CLS) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    </noscript>

    <!-- Pre-compiled, Production-Grade Tailwind CSS via Vite (0 runtime overhead, 0 CLS, 0 render blocking) -->
    @vite(['resources/css/app.css'])

    <!-- Alpine.js for lightweight, reactive Blade micro-interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        html[dir="rtl"],
        html[lang="ar"],
        html[lang="ar"] body {
            font-family: 'Cairo', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
    @stack('styles')
    <!-- Meta Pixel Code -->
    <script>
        !function (f, b, e, v, n, t, s) {
            if (f.fbq) return; n = f.fbq = function () {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
            n.queue = []; t = b.createElement(e); t.async = !0;
            t.src = v; s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '2573875423109518');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=2573875423109518&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->
</head>

<body
    class="bg-slate-50/70 text-slate-800 antialiased flex flex-col min-h-screen selection:bg-sky-500 selection:text-white"
    x-data="{ mobileMenu: false, searchOpen: false }">

    <!-- 1. TOP ANNOUNCEMENT BAR (Desktop) -->
    <div class="hidden md:block bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Left: Reassurance -->
            <div class="flex items-center gap-2 text-slate-300">
                <span>🚚
                    {{ app()->getLocale() === 'ar' ? 'توصيل لكافة ولايات تونس (24-48 ساعة)' : 'Livraison 24-48h partout en Tunisie' }}</span>
                <span class="text-slate-600">•</span>
                <span>💵 {{ __('messages.cash_on_delivery') }}</span>
            </div>

            <!-- Right: Links & Language -->
            <div class="flex items-center gap-4 text-xs font-medium">
                <a href="{{ route('orders.track') }}" class="hover:text-white transition">
                    {{ app()->getLocale() === 'ar' ? '📦 تتبع طلبي' : '📦 Suivi commande' }}
                </a>
                <a href="{{ route('become-vendor') }}"
                    class="text-amber-400 hover:text-amber-300 font-semibold transition">
                    {{ __('messages.become_a_vendor') }}
                </a>
                <a href="{{ route('contact') }}" class="hover:text-white transition">
                    {{ __('messages.contact') }}
                </a>

                <!-- Language Selector -->
                <div class="relative" x-data="{ langOpen: false }">
                    <button @click="langOpen = !langOpen"
                        class="hover:text-white transition flex items-center gap-1 cursor-pointer bg-slate-800 px-2 py-0.5 rounded text-xs">
                        @if(app()->getLocale() === 'ar')
                            <span>🇹🇳 العربية</span>
                        @elseif(app()->getLocale() === 'en')
                            <span>🇬🇧 EN</span>
                        @else
                            <span>🇫🇷 FR</span>
                        @endif
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="langOpen" @click.away="langOpen = false" x-cloak
                        class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-1 w-28 bg-white text-slate-800 rounded-lg shadow-lg border border-slate-200 py-1 z-50 text-xs">
                        <a href="{{ route('language.switch', 'fr') }}"
                            class="block px-3 py-1.5 hover:bg-slate-100 {{ app()->getLocale() === 'fr' ? 'text-sky-600 font-bold' : '' }}">🇫🇷
                            Français</a>
                        <a href="{{ route('language.switch', 'ar') }}"
                            class="block px-3 py-1.5 hover:bg-slate-100 {{ app()->getLocale() === 'ar' ? 'text-sky-600 font-bold' : '' }}">🇹🇳
                            العربية</a>
                        <a href="{{ route('language.switch', 'en') }}"
                            class="block px-3 py-1.5 hover:bg-slate-100 {{ app()->getLocale() === 'en' ? 'text-sky-600 font-bold' : '' }}">🇬🇧
                            English</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. MAIN HEADER -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-18 gap-4">

                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                    <img src="{{ asset('logo/logo-96.png') }}" alt="AquawaSouk" width="38" height="38"
                        class="w-9 h-9 sm:w-10 sm:h-10 object-contain">
                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Aquawa<span class="text-sky-600">Souk</span>
                    </span>
                </a>

                <!-- Search Bar (Desktop with Live Autocomplete) -->
                <div class="hidden md:flex flex-1 max-w-lg mx-4 relative"
                    x-data="liveSearchComponent('{{ app()->getLocale() }}')">
                    <form action="{{ route('products.index') }}" method="GET" class="w-full relative"
                        @submit="isOpen = false">
                        <input type="text" name="search" x-model="query" @input.debounce.250ms="search()"
                            @focus="if(results.length > 0) isOpen = true" @click.away="isOpen = false"
                            @keydown.escape="isOpen = false" value="{{ request('search') }}"
                            placeholder="{{ __('messages.live_search_placeholder') }}" autocomplete="off"
                            class="w-full {{ app()->getLocale() === 'ar' ? 'pr-10 pl-20' : 'pl-10 pr-20' }} py-2 text-sm bg-slate-100 hover:bg-slate-100/90 focus:bg-white rounded-full border border-transparent focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none transition placeholder-slate-400">
                        <svg class="w-4 h-4 text-slate-400 absolute {{ app()->getLocale() === 'ar' ? 'right-3.5' : 'left-3.5' }} top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button type="submit"
                            class="absolute {{ app()->getLocale() === 'ar' ? 'left-1' : 'right-1' }} top-1/2 -translate-y-1/2 px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-full transition cursor-pointer">
                            {{ __('messages.search') }}
                        </button>
                    </form>

                    <!-- Live Autocomplete Dropdown -->
                    <div x-show="isOpen && query.length >= 2" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        class="absolute top-full mt-2 inset-x-0 bg-white rounded-2xl shadow-2xl border border-slate-200/90 overflow-hidden z-50 divide-y divide-slate-100 text-slate-800">

                        <!-- Loading State -->
                        <div x-show="loading" class="p-4 flex items-center justify-center gap-2 text-xs text-slate-400">
                            <svg class="animate-spin w-4 h-4 text-sky-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>{{ app()->getLocale() === 'ar' ? 'جاري البحث...' : 'Recherche en cours...' }}</span>
                        </div>

                        <!-- Results List -->
                        <div x-show="!loading && results.length > 0"
                            class="max-h-80 overflow-y-auto divide-y divide-slate-50">
                            <div
                                class="px-3.5 py-1.5 bg-slate-50/80 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                                <span>{{ __('messages.quick_results') }}</span>
                                <span x-text="results.length + ' {{ __('messages.products') }}'"></span>
                            </div>
                            <template x-for="item in results" :key="item.id">
                                <a :href="item.url"
                                    class="flex items-center gap-3 p-2.5 hover:bg-sky-50/50 transition group">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/80 p-0.5 shrink-0 flex items-center justify-center overflow-hidden">
                                        <img :src="item.image || '{{ asset('logo/logo.png') }}'" :alt="item.name"
                                            class="w-full h-full object-contain group-hover:scale-110 transition duration-200"
                                            onerror="this.onerror=null; this.src='{{ asset('logo/logo.png') }}';">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-800 group-hover:text-sky-600 transition truncate"
                                            x-text="item.name"></p>
                                        <p class="text-[10px] text-slate-400 truncate"
                                            x-text="item.category_name + (item.vendor_name ? ' • ' + item.vendor_name : '')">
                                        </p>
                                    </div>
                                    <div class="text-end shrink-0">
                                        <span class="text-xs font-black text-slate-900 block"
                                            x-text="item.current_price.toFixed(2) + ' ' + (item.currency || 'DT')"></span>
                                        <template x-if="item.has_discount">
                                            <span class="text-[10px] text-rose-500 font-bold block"
                                                x-text="'-' + item.discount_percent + '%'"></span>
                                        </template>
                                    </div>
                                </a>
                            </template>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!loading && results.length === 0 && query.length >= 2"
                            class="p-5 text-center text-xs text-slate-400">
                            <span class="text-base block mb-1">🔍</span>
                            <span>{{ __('messages.live_search_no_results') }}</span>
                        </div>

                        <!-- Footer: View All -->
                        <div class="p-2 bg-slate-50 text-center">
                            <a :href="'{{ route('products.index') }}?search=' + encodeURIComponent(query)"
                                class="text-xs font-bold text-sky-600 hover:text-sky-700 block py-1">
                                {{ __('messages.live_search_view_all') }} "<span x-text="query"></span>" &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-2 sm:gap-3">

                    <!-- Search (Mobile toggle) -->
                    <button @click="searchOpen = !searchOpen"
                        class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    <!-- Wishlist (Desktop) -->
                    <a href="{{ route('wishlist.index') }}"
                        class="hidden sm:flex relative p-2 text-slate-600 hover:text-rose-600 hover:bg-slate-100 rounded-full transition"
                        title="{{ app()->getLocale() === 'ar' ? 'المفضلة' : 'Favoris' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        @auth
                            @php $headerWishCount = auth()->user()->wishlists()->count(); @endphp
                            <span id="header-wishlist-count"
                                class="{{ $headerWishCount > 0 ? '' : 'hidden' }} absolute top-0.5 {{ app()->getLocale() === 'ar' ? 'left-0.5' : 'right-0.5' }} bg-rose-600 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">
                                {{ $headerWishCount }}
                            </span>
                        @endauth
                    </a>

                    <!-- Cart -->
                    <a href="{{ route('cart.index') }}"
                        class="relative p-2 text-slate-700 hover:text-sky-600 hover:bg-slate-100 rounded-full transition flex items-center"
                        title="{{ __('messages.shopping_cart') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span id="header-cart-count"
                            class="absolute top-0.5 {{ app()->getLocale() === 'ar' ? 'left-0.5' : 'right-0.5' }} bg-sky-600 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">
                            {{ $cartCount ?? 0 }}
                        </span>
                    </a>

                    @auth
                        @php
                            $unreadCount = auth()->user()->unreadNotifications()->count();
                            $recentNotifications = auth()->user()->notifications()->latest()->take(5)->get();
                        @endphp
                        <!-- Notifications -->
                        <div class="relative" x-data="{ notifOpen: false }">
                            <button @click="notifOpen = !notifOpen"
                                class="relative p-2 text-slate-700 hover:text-sky-600 hover:bg-slate-100 rounded-full transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @if($unreadCount > 0)
                                    <span
                                        class="absolute top-1 {{ app()->getLocale() === 'ar' ? 'left-1' : 'right-1' }} bg-red-500 w-2 h-2 rounded-full"></span>
                                @endif
                            </button>

                            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                                class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-50 text-xs">
                                <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                                    <span class="font-bold text-slate-800">Notifications</span>
                                    @if($unreadCount > 0)
                                        <form action="{{ route('notifications.readAll') }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="text-sky-600 text-[11px] hover:underline cursor-pointer">Tout
                                                lire</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="max-h-64 overflow-y-auto divide-y divide-slate-50">
                                    @forelse($recentNotifications as $notif)
                                        <a href="{{ $notif->data['url'] ?? '#' }}"
                                            class="block p-3 hover:bg-slate-50 {{ $notif->read_at ? 'text-slate-500' : 'font-semibold text-slate-800' }}">
                                            <p class="text-xs">{{ $notif->data['title'] ?? 'Notification' }}</p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $notif->data['message'] ?? '' }}</p>
                                        </a>
                                    @empty
                                        <p class="p-4 text-center text-slate-400 text-xs">Aucune notification</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ userMenu: false }">
                            <button @click="userMenu = !userMenu"
                                class="flex items-center gap-1.5 p-1 rounded-full hover:bg-slate-100 transition cursor-pointer">
                                <div
                                    class="w-8 h-8 rounded-full bg-sky-600 text-white font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="hidden lg:inline text-xs font-semibold text-slate-700 max-w-[90px] truncate">
                                    {{ auth()->user()->name }}
                                </span>
                            </button>
                            <div x-show="userMenu" @click.away="userMenu = false" x-cloak
                                class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5 z-50 text-xs">
                                <div class="px-3 py-2 border-b border-slate-100">
                                    <p class="font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('dashboard') }}"
                                    class="block px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">{{ __('messages.dashboard') }}</a>
                                <a href="{{ route('profile.edit') }}"
                                    class="block px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">{{ __('messages.settings') ?? 'Paramètres' }}</a>
                                <div class="border-t border-slate-100 pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full text-left px-3 py-2 hover:bg-rose-50 text-rose-600 font-semibold cursor-pointer">{{ __('messages.logout') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Guest Auth -->
                        <a href="{{ route('login') }}"
                            class="text-xs font-semibold text-slate-700 hover:text-sky-600 px-2 py-1 transition hidden sm:inline">
                            {{ __('messages.login') }}
                        </a>
                        <a href="{{ route('register') }}"
                            class="text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white px-3.5 py-1.5 rounded-full transition hidden sm:inline">
                            {{ __('messages.register') }}
                        </a>
                    @endauth

                    <!-- Mobile Menu Hamburger -->
                    <button @click="mobileMenu = !mobileMenu"
                        class="md:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar (Collapsible with Live Search) -->
            <div x-show="searchOpen" x-cloak class="md:hidden pb-3 relative"
                x-data="liveSearchComponent('{{ app()->getLocale() }}')">
                <form action="{{ route('products.index') }}" method="GET" class="relative" @submit="isOpen = false">
                    <input type="text" name="search" x-model="query" @input.debounce.250ms="search()"
                        @focus="if(results.length > 0) isOpen = true" @click.away="isOpen = false"
                        @keydown.escape="isOpen = false" value="{{ request('search') }}"
                        placeholder="{{ __('messages.live_search_placeholder') }}" autocomplete="off"
                        class="w-full {{ app()->getLocale() === 'ar' ? 'pr-9 pl-16' : 'pl-9 pr-16' }} py-2 text-xs bg-slate-100 rounded-full border border-transparent focus:border-sky-500 focus:bg-white outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute {{ app()->getLocale() === 'ar' ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <button type="submit"
                        class="absolute {{ app()->getLocale() === 'ar' ? 'left-1' : 'right-1' }} top-1/2 -translate-y-1/2 px-3 py-1 bg-sky-600 text-white rounded-full text-xs font-semibold cursor-pointer">
                        {{ __('messages.search') }}
                    </button>
                </form>

                <!-- Mobile Live Autocomplete Dropdown -->
                <div x-show="isOpen && query.length >= 2" x-cloak
                    class="absolute top-full mt-1.5 inset-x-0 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-50 divide-y divide-slate-100 text-slate-800">
                    <div x-show="loading" class="p-3 text-center text-xs text-slate-400">
                        {{ app()->getLocale() === 'ar' ? 'جاري البحث...' : 'Recherche en cours...' }}
                    </div>
                    <div x-show="!loading && results.length > 0"
                        class="max-h-60 overflow-y-auto divide-y divide-slate-50">
                        <template x-for="item in results" :key="item.id">
                            <a :href="item.url" class="flex items-center gap-2.5 p-2 hover:bg-sky-50 transition">
                                <img :src="item.image || '{{ asset('logo/logo.png') }}'" :alt="item.name"
                                    class="w-8 h-8 rounded-lg object-contain bg-slate-100 shrink-0"
                                    onerror="this.onerror=null; this.src='{{ asset('logo/logo.png') }}';">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate" x-text="item.name"></p>
                                    <p class="text-[10px] text-sky-600 font-bold"
                                        x-text="item.current_price.toFixed(2) + ' ' + (item.currency || 'DT')"></p>
                                </div>
                            </a>
                        </template>
                    </div>
                    <div x-show="!loading && results.length === 0" class="p-3 text-center text-xs text-slate-400">
                        {{ __('messages.live_search_no_results') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. CATEGORIES NAVBAR -->
        <nav class="border-t border-slate-100 bg-white hidden md:block">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="flex items-center gap-1 overflow-x-auto no-scrollbar py-2 text-xs font-medium text-slate-600">
                    <a href="{{ route('products.index') }}"
                        class="px-3 py-1.5 rounded-lg text-sky-700 bg-sky-50 hover:bg-sky-100 font-bold whitespace-nowrap flex items-center gap-1.5 transition">
                        <span>☰</span>
                        <span>{{ __('messages.all_categories') }}</span>
                    </a>

                    @if(isset($navCategories) && $navCategories->count())
                        @foreach($navCategories->take(7) as $cat)
                            <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                                class="px-3 py-1.5 rounded-lg hover:text-sky-600 hover:bg-slate-50 whitespace-nowrap transition flex items-center gap-1.5">
                                @if($cat->image_url)
                                    <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}"
                                        class="w-4 h-4 rounded object-contain shrink-0">
                                @endif
                                <span>{{ $cat->name }}</span>
                            </a>
                        @endforeach
                    @else
                        <a href="{{ route('products.index') }}"
                            class="px-3 py-1.5 rounded-lg hover:text-sky-600 hover:bg-slate-50 whitespace-nowrap">{{ app()->getLocale() === 'ar' ? 'أجهزة وتكنولوجيا' : 'High-Tech' }}</a>
                        <a href="{{ route('products.index') }}"
                            class="px-3 py-1.5 rounded-lg hover:text-sky-600 hover:bg-slate-50 whitespace-nowrap">{{ app()->getLocale() === 'ar' ? 'موضة وملابس' : 'Mode' }}</a>
                        <a href="{{ route('products.index') }}"
                            class="px-3 py-1.5 rounded-lg hover:text-sky-600 hover:bg-slate-50 whitespace-nowrap">{{ app()->getLocale() === 'ar' ? 'منزل ومطبخ' : 'Maison' }}</a>
                        <a href="{{ route('products.index') }}"
                            class="px-3 py-1.5 rounded-lg hover:text-sky-600 hover:bg-slate-50 whitespace-nowrap">{{ app()->getLocale() === 'ar' ? 'جمال وعناية' : 'Beauté' }}</a>
                    @endif

                    <a href="{{ route('vendors.index') }}"
                        class="{{ app()->getLocale() === 'ar' ? 'mr-auto' : 'ml-auto' }} px-3 py-1.5 rounded-lg text-slate-700 hover:text-sky-600 hover:bg-slate-50 font-semibold whitespace-nowrap flex items-center gap-1 transition">
                        <span>🏪</span>
                        <span>{{ __('messages.vendors') }}</span>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- 4. MOBILE DRAWER MENU -->
    <div x-show="mobileMenu" x-cloak class="relative z-50 md:hidden">
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" @click="mobileMenu = false">
        </div>
        <div
            class="fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} max-w-xs w-full bg-white shadow-2xl p-5 flex flex-col justify-between overflow-y-auto">
            <div>
                <!-- Drawer Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <picture>
                            <source srcset="{{ asset('logo/logo-96.webp') }}" type="image/webp">
                            <img src="{{ asset('logo/logo-96.png') }}" alt="Logo" width="36" height="36"
                                class="w-9 h-9 rounded-xl object-contain border border-slate-100 p-0.5" loading="lazy">
                        </picture>
                        <span
                            class="font-black text-lg bg-gradient-to-r from-sky-600 to-indigo-600 bg-clip-text text-transparent">
                            AquawaSouk
                        </span>
                    </div>
                    <button @click="mobileMenu = false"
                        class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Auth block inside Drawer -->
                @auth
                    <div class="mt-4 p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2.5 truncate">
                            <div
                                class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 text-white font-extrabold flex items-center justify-center text-xs shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="truncate">
                                <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <a href="{{ route('dashboard') }}"
                            class="px-2.5 py-1 bg-white border border-slate-200 text-sky-600 rounded-lg text-xs font-bold shrink-0 shadow-2xs">
                            {{ __('messages.dashboard') }}
                        </a>
                    </div>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}"
                            class="py-2 text-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                            {{ __('messages.login') }}
                        </a>
                        <a href="{{ route('register') }}"
                            class="py-2 text-center rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 text-white text-xs font-extrabold shadow-xs transition">
                            {{ __('messages.register') }}
                        </a>
                    </div>
                @endauth

                <!-- Language Selector in Mobile Drawer -->
                <div class="mt-3 p-2 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 px-1">
                        {{ app()->getLocale() === 'ar' ? 'اختر اللغة' : 'Choisir la langue' }} :
                    </div>
                    <div class="grid grid-cols-3 gap-1.5 text-center">
                        <a href="{{ route('language.switch', 'fr') }}"
                            class="py-1.5 px-2 rounded-xl text-xs font-bold transition {{ app()->getLocale() === 'fr' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                            🇫🇷 FR
                        </a>
                        <a href="{{ route('language.switch', 'ar') }}"
                            class="py-1.5 px-2 rounded-xl text-xs font-bold transition {{ app()->getLocale() === 'ar' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                            🇹🇳 عربي
                        </a>
                        <a href="{{ route('language.switch', 'en') }}"
                            class="py-1.5 px-2 rounded-xl text-xs font-bold transition {{ app()->getLocale() === 'en' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                            🇬🇧 EN
                        </a>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="mt-4 flex flex-col gap-1 text-sm font-semibold">
                    <a href="{{ route('home') }}"
                        class="p-2.5 rounded-xl hover:bg-slate-100 text-slate-800 flex items-center gap-3 transition">
                        <span>🏠</span> <span>{{ __('messages.home') }}</span>
                    </a>
                    <a href="{{ route('products.index') }}"
                        class="p-2.5 rounded-xl hover:bg-slate-100 text-slate-800 flex items-center gap-3 transition">
                        <span>📦</span> <span>{{ __('messages.all_products') }}</span>
                    </a>
                    <a href="{{ route('cart.index') }}"
                        class="p-2.5 rounded-xl hover:bg-slate-100 text-slate-800 flex items-center justify-between transition">
                        <span class="flex items-center gap-3">
                            <span>🛒</span> <span>{{ __('messages.cart') }}</span>
                        </span>
                        @if(($cartCount ?? 0) > 0)
                            <span class="bg-sky-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('wishlist.index') }}"
                        class="p-2.5 rounded-xl hover:bg-slate-100 text-slate-800 flex items-center gap-3 transition">
                        <span>❤️</span> <span>{{ app()->getLocale() === 'ar' ? 'المفضلة' : 'Mes Favoris' }}</span>
                    </a>
                    <a href="{{ route('vendors.index') }}"
                        class="p-2.5 rounded-xl hover:bg-slate-100 text-slate-800 flex items-center gap-3 transition">
                        <span>🏪</span> <span>{{ __('messages.vendors') }}</span>
                    </a>
                    <a href="{{ route('become-vendor') }}"
                        class="p-2.5 rounded-xl bg-gradient-to-r from-amber-50 to-amber-100/50 text-amber-800 font-extrabold flex items-center gap-3 border border-amber-200/50 transition">
                        <span>🚀</span> <span>{{ __('messages.become_a_vendor') }}</span>
                    </a>
                    <a href="{{ route('orders.track') }}"
                        class="p-2.5 rounded-xl hover:bg-slate-100 text-slate-700 flex items-center gap-3 transition">
                        <span>🔍</span>
                        <span>{{ app()->getLocale() === 'ar' ? 'تتبع طلبي' : 'Suivi de commande' }}</span>
                    </a>
                    <a href="{{ route('contact') }}"
                        class="p-2.5 rounded-xl hover:bg-slate-100 text-slate-700 flex items-center gap-3 transition">
                        <span>📞</span> <span>{{ __('messages.contact') }}</span>
                    </a>
                </div>
            </div>

            <!-- Drawer Footer (Logout / Info) -->
            @auth
                <div class="pt-4 border-t border-slate-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full p-2.5 rounded-xl hover:bg-rose-50 text-rose-600 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                            <span>🚪</span>
                            <span>{{ __('messages.logout') }}</span>
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div
                class="p-4 mb-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div
                class="p-4 mb-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- MAIN CONTENT SLOT -->
    <main class="flex-grow pb-16 md:pb-0">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-slate-950 text-slate-300 mt-20 border-t border-slate-800/80">

        <!-- Trust Elements Bar -->
        <div class="border-b border-slate-800/60 py-10 bg-slate-900/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="flex flex-col items-center">
                    <div
                        class="w-13 h-13 rounded-2xl bg-sky-500/10 text-sky-400 flex items-center justify-center mb-3.5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <p class="text-sm font-extrabold text-white">
                        {{ app()->getLocale() === 'ar' ? 'توصيل سريع 24-48 ساعة' : (app()->getLocale() === 'en' ? 'Fast Delivery 24-48h' : 'Livraison Express 24-48h') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ app()->getLocale() === 'ar' ? 'في كامل تراب الجمهورية' : 'Dans les 24 gouvernorats' }}
                    </p>
                </div>

                <div class="flex flex-col items-center">
                    <div
                        class="w-13 h-13 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-3.5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-extrabold text-white">{{ __('messages.cash_on_delivery') }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ app()->getLocale() === 'ar' ? 'ادفع نقداً عند استلام الطرد' : 'Réglez le livreur en espèces' }}
                    </p>
                </div>

                <div class="flex flex-col items-center">
                    <div
                        class="w-13 h-13 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-3.5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <p class="text-sm font-extrabold text-white">
                        {{ app()->getLocale() === 'ar' ? 'بائعون معتمدون 100%' : 'Vendeurs 100% Vérifiés' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ app()->getLocale() === 'ar' ? 'منتجات أصلية ومضمونة' : 'Articles conformes & testés' }}
                    </p>
                </div>

                <div class="flex flex-col items-center">
                    <div
                        class="w-13 h-13 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-3.5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-extrabold text-white">
                        {{ app()->getLocale() === 'ar' ? 'خدمة حرفاء 6 أيام / 7' : 'Assistance 6j/7' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ app()->getLocale() === 'ar' ? 'عبر الهاتف والواتساب' : 'Par Téléphone & WhatsApp' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Links Columns -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <!-- Col 1: About -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2.5">
                        <picture>
                            <source srcset="{{ asset('logo/logo-96.webp') }}" type="image/webp">
                            <img src="{{ asset('logo/logo-96.png') }}" alt="AquawaSouk" width="40" height="40"
                                class="w-10 h-10 rounded-xl bg-white p-1 object-contain" loading="lazy">
                        </picture>
                        <span class="text-xl font-black text-white">AquawaSouk</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ app()->getLocale() === 'ar' ? 'منصة التسوق الإلكتروني الرائدة في تونس. نربط أفضل التجار مع المشترين الباحثين عن الجودة والتميز.' : 'La marketplace e-commerce multi-catégories de référence en Tunisie. Nous connectons les meilleurs marchands avec des acheteurs à la recherche de qualité et d\'excellence.' }}
                    </p>
                    <!-- <div class="pt-2 text-xs text-slate-400 space-y-1">
                        <p>📍 {{ app()->getLocale() === 'ar' ? 'تونس، الجمهورية التونسية' : 'Tunis, Tunisie' }}</p>
                        <p>📞 +216 26 260 262</p>
                        <p>✉️ contact@aquawasouk.com</p>
                    </div> -->
                </div>

                <!-- Col 2: Navigation -->
                <div>
                    <p class="text-xs font-extrabold text-white uppercase tracking-wider mb-4">
                        {{ app()->getLocale() === 'ar' ? 'روابط سريعة' : 'Navigation' }}
                    </p>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ route('home') }}"
                                class="hover:text-sky-400 transition">{{ __('messages.home') }}</a></li>
                        <li><a href="{{ route('products.index') }}"
                                class="hover:text-sky-400 transition">{{ __('messages.all_products') }}</a></li>
                        <li><a href="{{ route('vendors.index') }}"
                                class="hover:text-sky-400 transition">{{ __('messages.vendors') }}</a></li>
                        <li><a href="{{ route('cart.index') }}"
                                class="hover:text-sky-400 transition">{{ __('messages.cart') }}</a></li>
                        <li><a href="{{ route('become-vendor') }}"
                                class="hover:text-amber-300 font-bold transition text-amber-400">{{ __('messages.become_a_vendor') }}</a>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Assistance & Infos -->
                <div>
                    <p class="text-xs font-extrabold text-white uppercase tracking-wider mb-4">
                        {{ app()->getLocale() === 'ar' ? 'المساعدة والأمان' : 'Assistance & Sécurité' }}
                    </p>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ route('about') }}"
                                class="hover:text-sky-400 transition">{{ __('messages.about') }}</a></li>
                        <li><a href="{{ route('contact') }}"
                                class="hover:text-sky-400 transition">{{ __('messages.contact') }}</a></li>
                        <li><a href="{{ route('privacy-policy') }}"
                                class="hover:text-sky-400 transition">{{ __('messages.privacy_policy') }}</a></li>
                        <li><a href="{{ route('terms-of-service') }}"
                                class="hover:text-sky-400 transition">{{ __('messages.terms_of_service') }}</a></li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter -->
                <div>
                    <p class="text-xs font-extrabold text-white uppercase tracking-wider mb-4">
                        {{ app()->getLocale() === 'ar' ? 'النشرة البريدية' : 'Restez Informé' }}
                    </p>
                    <p class="text-xs text-slate-400 mb-3 leading-relaxed">
                        {{ app()->getLocale() === 'ar' ? 'اشترك لتصلك أحدث العروض والخصومات الحصرية أولاً بأول:' : 'Recevez nos offres exclusives et réductions exclusives par e-mail :' }}
                    </p>
                    <form
                        onsubmit="event.preventDefault(); alert('{{ app()->getLocale() === 'ar' ? 'شكراً لاشتراكك !' : 'Merci pour votre inscription !' }}');"
                        class="space-y-2">
                        <input type="email"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'بريدك الإلكتروني' : 'Votre adresse e-mail' }}"
                            required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-hidden focus:ring-2 focus:ring-sky-500">
                        <button type="submit"
                            class="w-full py-2.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            {{ app()->getLocale() === 'ar' ? 'اشتراك' : 'S\'abonner' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div
                class="border-t border-slate-800/80 mt-12 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} AquawaSouk Tunisie. {{ __('messages.all_rights_reserved') }}.</p>
                <div class="flex items-center gap-4 text-slate-400">
                    <a href="{{ route('privacy-policy') }}"
                        class="hover:text-white transition">{{ __('messages.privacy_policy') }}</a>
                    <span>•</span>
                    <a href="{{ route('terms-of-service') }}"
                        class="hover:text-white transition">{{ __('messages.terms_of_service') }}</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ============================================== -->
    <!-- MOBILE BOTTOM NAVBAR (ONLY IN MOBILE VIEW)     -->
    <!-- ============================================== -->
    @unless(request()->routeIs('products.show'))
        <nav
            class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-slate-200/90 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] px-2 py-1.5 transition-all">
            <div class="grid grid-cols-5 items-center text-center">
                <!-- 1. Accueil -->
                <a href="{{ route('home') }}"
                    class="flex flex-col items-center justify-center py-1 transition group {{ request()->routeIs('home') ? 'text-sky-600 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
                    <div class="relative">
                        <svg class="w-5 h-5 {{ request()->routeIs('home') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 leading-none">{{ __('messages.home') }}</span>
                </a>

                <!-- 2. Explorer / Boutique -->
                <a href="{{ route('products.index') }}"
                    class="flex flex-col items-center justify-center py-1 transition group {{ request()->routeIs('products.*') ? 'text-sky-600 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
                    <div class="relative">
                        <svg class="w-5 h-5 {{ request()->routeIs('products.*') ? 'stroke-[2.5]' : 'stroke-2' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 leading-none">{{ __('messages.products') }}</span>
                </a>

                <!-- 3. Favoris -->
                <a href="{{ route('wishlist.index') }}"
                    class="flex flex-col items-center justify-center py-1 transition group {{ request()->routeIs('wishlist.*') ? 'text-rose-600 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
                    <div class="relative">
                        <svg class="w-5 h-5 {{ request()->routeIs('wishlist.*') ? 'stroke-[2.5] fill-rose-100 text-rose-600' : 'stroke-2' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        @auth
                            @php $mobWishCount = auth()->user()->wishlists()->count(); @endphp
                            <span id="mobile-navbar-wishlist-count"
                                class="{{ $mobWishCount > 0 ? '' : 'hidden' }} absolute -top-1.5 -right-2 bg-rose-600 text-white font-black text-[9px] w-4 h-4 rounded-full flex items-center justify-center ring-2 ring-white">
                                {{ $mobWishCount }}
                            </span>
                        @endauth
                    </div>
                    <span
                        class="text-[10px] tracking-tight mt-0.5 leading-none">{{ app()->getLocale() === 'ar' ? 'المفضلة' : 'Favoris' }}</span>
                </a>

                <!-- 4. Panier -->
                <a href="{{ route('cart.index') }}"
                    class="flex flex-col items-center justify-center py-1 transition group {{ request()->routeIs('cart.*') ? 'text-sky-600 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
                    <div class="relative">
                        <svg class="w-5 h-5 {{ request()->routeIs('cart.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span id="mobile-navbar-cart-count"
                            class="absolute -top-1.5 -right-2 bg-gradient-to-r from-rose-500 to-red-600 text-white font-black text-[9px] w-4 h-4 rounded-full flex items-center justify-center ring-2 ring-white shadow-xs">
                            {{ $cartCount ?? 0 }}
                        </span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 leading-none">{{ __('messages.cart') }}</span>
                </a>

                <!-- 5. Compte / Menu -->
                <button type="button" @click="mobileMenu = true"
                    class="flex flex-col items-center justify-center py-1 transition text-slate-500 hover:text-slate-900 font-medium cursor-pointer">
                    <div class="relative">
                        @auth
                            <div
                                class="w-5 h-5 rounded-full bg-gradient-to-tr from-sky-600 to-indigo-600 text-white font-bold flex items-center justify-center text-[9px] shadow-xs">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @else
                            <svg class="w-5 h-5 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        @endauth
                    </div>
                    <span
                        class="text-[10px] tracking-tight mt-0.5 leading-none">{{ auth()->check() ? (app()->getLocale() === 'ar' ? 'حسابي' : 'Compte') : __('messages.login') }}</span>
                </button>
            </div>
        </nav>
    @endunless

    <!-- AI Shopping Assistant Chatbot (Floating Widget) -->
    @include('blade.components.chatbot')



    <!-- Global Floating Toast Notification System (Cart & Flash) -->
    <div x-data="globalToastManager()" x-init="init()"
        class="fixed top-20 {{ app()->getLocale() === 'ar' ? 'left-4 sm:left-6' : 'right-4 sm:right-6' }} z-50 max-w-sm w-full pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-[-10px] scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-[-10px] scale-95"
                class="mb-3 p-4 rounded-2xl bg-white shadow-2xl border border-slate-200/90 pointer-events-auto flex items-start gap-3.5 backdrop-blur-md">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                    :class="toast.type === 'success' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="toast.type !== 'success'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </template>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-xs font-black text-slate-900" x-text="toast.title"></p>
                    <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2" x-text="toast.message"></p>

                    <template x-if="toast.isCart">
                        <div class="mt-2.5 flex items-center gap-2">
                            <a href="{{ route('cart.index') }}"
                                class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-[11px] font-bold rounded-xl transition shadow-xs">
                                {{ __('messages.cart_view_cart') }}
                            </a>
                            <button type="button" @click="dismiss(toast.id)"
                                class="px-2.5 py-1.5 text-slate-500 hover:text-slate-800 text-[11px] font-semibold cursor-pointer">
                                {{ __('messages.cart_continue_shopping') }}
                            </button>
                        </div>
                    </template>
                </div>

                <button type="button" @click="dismiss(toast.id)"
                    class="text-slate-400 hover:text-slate-600 text-sm font-bold cursor-pointer">&times;</button>
            </div>
        </template>
    </div>

    <script>
        function liveSearchComponent(locale) {
            return {
                query: '',
                results: [],
                loading: false,
                isOpen: false,
                search() {
                    const q = this.query.trim();
                    if (q.length < 2) {
                        this.results = [];
                        this.isOpen = false;
                        return;
                    }
                    this.loading = true;
                    this.isOpen = true;
                    fetch(`/shopping-assistant/products?q=${encodeURIComponent(q)}&limit=6`)
                        .then(res => res.json())
                        .then(data => {
                            this.results = (data && data.products) ? data.products : [];
                            this.loading = false;
                        })
                        .catch(() => {
                            this.loading = false;
                        });
                }
            };
        }

        function globalToastManager() {
            return {
                toasts: [],
                init() {
                    window.addEventListener('show-toast', (e) => {
                        this.add(e.detail);
                    });

                    @if(session('cart_added_toast'))
                        this.add({
                            title: "{{ __('messages.cart_added_toast_title') }}",
                            message: "{{ session('cart_added_toast')['name'] }} ({{ number_format(session('cart_added_toast')['price'], 2) }} DT)",
                            type: 'success',
                            isCart: true,
                        });
                    @endif
                },
                add(toast) {
                    const id = Date.now() + Math.random();
                    const newToast = {
                        id,
                        title: toast.title || 'Notification',
                        message: toast.message || '',
                        type: toast.type || 'success',
                        isCart: !!toast.isCart,
                        visible: true,
                    };
                    this.toasts.push(newToast);
                    setTimeout(() => {
                        this.dismiss(id);
                    }, 5000);
                },
                dismiss(id) {
                    const t = this.toasts.find(item => item.id === id);
                    if (t) {
                        t.visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(item => item.id !== id);
                        }, 300);
                    }
                }
            };
        }

        function addToCartAjax(event, productId, quantity = 1) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch("{{ route('cart.add') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity,
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        // Update header & mobile navbar cart counters
                        const headerCart = document.getElementById('header-cart-count');
                        if (headerCart && data.cart_count !== undefined) {
                            headerCart.textContent = data.cart_count;
                        }
                        const mobNavCart = document.getElementById('mobile-navbar-cart-count');
                        if (mobNavCart && data.cart_count !== undefined) {
                            mobNavCart.textContent = data.cart_count;
                        }

                        // Dispatch Global Toast
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: {
                                title: data.message || "{{ __('messages.cart_added_toast_title') }}",
                                message: `${data.product_name || ''} (${Number(data.product_price || 0).toFixed(2)} DT)`,
                                type: 'success',
                                isCart: true,
                            }
                        }));
                    } else if (data && data.message) {
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: {
                                title: 'Attention',
                                message: data.message,
                                type: 'error',
                                isCart: false,
                            }
                        }));
                    }
                })
                .catch(err => {
                    console.error(err);
                    // Fallback to native form submit if fetch fails
                    if (event && event.target && event.target.submit) {
                        event.target.submit();
                    }
                });
        }

        function toggleCardWishlist(event, productId) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const btn = document.getElementById(`card-wishlist-${productId}`);

            fetch(`/wishlist/toggle/${productId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
                .then(res => {
                    if (res.status === 401) {
                        window.location.href = "{{ route('login') }}";
                        return;
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && data.success) {
                        if (btn) {
                            if (data.favorited) {
                                btn.classList.add('text-rose-600', 'fill-current');
                                btn.classList.remove('text-slate-400');
                            } else {
                                btn.classList.remove('text-rose-600');
                                btn.classList.add('text-slate-400');
                            }
                        }
                        const headerCount = document.getElementById('header-wishlist-count');
                        if (headerCount) {
                            headerCount.textContent = data.count;
                            if (data.count > 0) {
                                headerCount.classList.remove('hidden');
                            } else {
                                headerCount.classList.add('hidden');
                            }
                        }
                        const mobNavCount = document.getElementById('mobile-navbar-wishlist-count');
                        if (mobNavCount) {
                            mobNavCount.textContent = data.count;
                            if (data.count > 0) {
                                mobNavCount.classList.remove('hidden');
                            } else {
                                mobNavCount.classList.add('hidden');
                            }
                        }
                    }
                })
                .catch(err => console.error(err));
        }
    </script>

    @stack('scripts')
</body>

</html>