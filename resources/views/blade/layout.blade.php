<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AquawaSouk - Marketplace n°1 en Tunisie | High-Tech, Mode, Maison & Plus')</title>
    <meta name="description" content="@yield('meta_description', 'AquawaSouk, la marketplace de référence en Tunisie : milliers de produits, vendeurs vérifiés, livraison express 24-48h et paiement à la livraison.')">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/logo.png') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans for Latin & Cairo for Arabic Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Alpine.js for lightweight, reactive Blade micro-interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS v4 Browser Runtime -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --color-brand-50: #f0f9ff;
            --color-brand-100: #e0f2fe;
            --color-brand-200: #bae6fd;
            --color-brand-300: #7dd3fc;
            --color-brand-400: #38bdf8;
            --color-brand-500: #0ea5e9;
            --color-brand-600: #0284c7;
            --color-brand-700: #0369a1;
            --color-brand-800: #075985;
            --color-brand-900: #0c4a6e;
            --color-brand-950: #082f49;
        }
    </style>

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        html[dir="rtl"], html[lang="ar"], html[lang="ar"] body {
            font-family: 'Cairo', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .glass-header {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50/70 text-slate-800 antialiased flex flex-col min-h-screen selection:bg-sky-500 selection:text-white" x-data="{ mobileMenu: false, searchOpen: false }">

    <!-- 1. TOP ANNOUNCEMENT & UTILITY BAR -->
    <div class="bg-slate-950 text-white text-xs py-2 px-4 border-b border-slate-800 shadow-sm relative z-50">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2.5">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-gradient-to-r from-sky-500 to-indigo-500 text-white uppercase tracking-wider shadow-xs">
                    AquawaSouk Tunisie
                </span>
                <span class="hidden sm:inline text-slate-300 text-[11px] font-medium">
                    🚚 {{ app()->getLocale() === 'ar' ? 'توصيل لكافة الولايات (24-48 ساعة)' : 'Livraison 24-48h dans tous les gouvernorats' }} • 💵 {{ __('messages.cash_on_delivery') }}
                </span>
            </div>

            <!-- Route navigation links -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 text-[11px]">
                <a href="{{ route('home') }}" class="px-2.5 py-1 rounded-lg transition font-semibold {{ request()->routeIs('home') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                    {{ __('messages.home') }}
                </a>
                <a href="{{ route('products.index') }}" class="px-2.5 py-1 rounded-lg transition font-semibold {{ request()->routeIs('products.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                    {{ __('messages.products') }}
                </a>
                <a href="{{ route('cart.index') }}" class="px-2.5 py-1 rounded-lg transition font-semibold {{ request()->routeIs('cart.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                    {{ __('messages.cart') }}
                </a>
                <a href="{{ route('wishlist.index') }}" class="px-2.5 py-1 rounded-lg transition font-semibold {{ request()->routeIs('wishlist.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                    ❤️ {{ app()->getLocale() === 'ar' ? 'المفضلة' : 'Favoris' }}
                </a>
                <a href="{{ route('orders.track') }}" class="px-2.5 py-1 rounded-lg transition font-semibold {{ request()->routeIs('orders.track') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                    🚚 {{ app()->getLocale() === 'ar' ? 'تتبع الطلب' : 'Suivi commande' }}
                </a>
                <a href="{{ route('vendors.index') }}" class="px-2.5 py-1 rounded-lg transition font-semibold {{ request()->routeIs('vendors.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                    {{ __('messages.vendors') }}
                </a>
                <a href="{{ route('about') }}" class="px-2.5 py-1 rounded-lg transition font-semibold {{ request()->routeIs('about') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                    {{ __('messages.about') }}
                </a>
                <a href="{{ route('contact') }}" class="px-2.5 py-1 rounded-lg transition font-semibold {{ request()->routeIs('contact') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                    {{ __('messages.contact') }}
                </a>
                <a href="{{ route('become-vendor') }}" class="px-2.5 py-1 rounded-lg transition font-semibold {{ request()->routeIs('become-vendor') ? 'bg-sky-600 text-white' : 'text-amber-400 hover:text-amber-300 hover:bg-slate-800' }}">
                    {{ __('messages.become_a_vendor') }}
                </a>
            </div>
        </div>
    </div>

    <!-- 2. ANNOUNCEMENT VALUE PROPOSITIONS BAR -->
    <div class="bg-gradient-to-r from-sky-600 via-sky-700 to-indigo-700 text-white text-xs py-2 px-4 shadow-xs">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-6 text-[11px] sm:text-xs font-medium">
                <span class="flex items-center gap-1.5">
                    <span class="text-sm">🚚</span>
                    <strong>{{ app()->getLocale() === 'ar' ? 'توصيل سريع' : (app()->getLocale() === 'en' ? 'Fast delivery' : 'Livraison express') }}</strong> 
                    <span>{{ app()->getLocale() === 'ar' ? 'في كامل ولايات تونس' : (app()->getLocale() === 'en' ? 'across all 24 governorates' : 'dans les 24 gouvernorats') }}</span>
                </span>
                <span class="hidden md:flex items-center gap-1.5">
                    <span class="text-sm">💵</span>
                    <strong>{{ app()->getLocale() === 'ar' ? 'الدفع عند الاستلام' : (app()->getLocale() === 'en' ? 'Cash on delivery' : 'Paiement à la livraison') }}</strong> 
                    <span>{{ app()->getLocale() === 'ar' ? '(نقداً)' : (app()->getLocale() === 'en' ? '(Cash)' : '(Espèces à réception)') }}</span>
                </span>
                <span class="hidden lg:flex items-center gap-1.5">
                    <span class="text-sm">🛡️</span>
                    <strong>{{ app()->getLocale() === 'ar' ? 'بائعون معتمدون' : (app()->getLocale() === 'en' ? 'Certified vendors' : 'Vendeurs certifiés') }}</strong>
                </span>
            </div>
            
            <div class="flex items-center gap-3.5 text-[11px]">
                <a href="{{ route('become-vendor') }}" class="hover:text-amber-300 font-bold transition flex items-center gap-1">
                    <span>🚀</span> {{ __('messages.become_a_vendor') }}
                </a>
                <span class="text-white/40">•</span>
                <a href="{{ route('contact') }}" class="hover:underline text-white/90">{{ __('messages.contact') }}</a>
                <span class="text-white/40">•</span>

                <!-- Topbar Language Selector -->
                <div class="relative" x-data="{ langOpenTop: false }">
                    <button @click="langOpenTop = !langOpenTop" class="hover:text-amber-300 font-extrabold transition flex items-center gap-1.5 cursor-pointer bg-white/10 px-2 py-0.5 rounded-md">
                        @if(app()->getLocale() === 'ar')
                            <span>🇹🇳</span> <span>العربية</span>
                        @elseif(app()->getLocale() === 'en')
                            <span>🇬🇧</span> <span>EN</span>
                        @else
                            <span>🇫🇷</span> <span>FR</span>
                        @endif
                        <svg class="w-3 h-3 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="langOpenTop" @click.away="langOpenTop = false" x-cloak class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-1.5 w-32 bg-slate-900 border border-slate-700 rounded-xl shadow-xl py-1 z-50 text-[11px]">
                        <a href="{{ route('language.switch', 'fr') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-slate-800 {{ app()->getLocale() === 'fr' ? 'text-amber-300 font-bold' : 'text-slate-300' }}">
                            <span class="flex items-center gap-1.5"><span>🇫🇷</span> Français</span>
                            @if(app()->getLocale() === 'fr') <span>✓</span> @endif
                        </a>
                        <a href="{{ route('language.switch', 'ar') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-slate-800 {{ app()->getLocale() === 'ar' ? 'text-amber-300 font-bold' : 'text-slate-300' }}">
                            <span class="flex items-center gap-1.5"><span>🇹🇳</span> العربية</span>
                            @if(app()->getLocale() === 'ar') <span>✓</span> @endif
                        </a>
                        <a href="{{ route('language.switch', 'en') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-slate-800 {{ app()->getLocale() === 'en' ? 'text-amber-300 font-bold' : 'text-slate-300' }}">
                            <span class="flex items-center gap-1.5"><span>🇬🇧</span> English</span>
                            @if(app()->getLocale() === 'en') <span>✓</span> @endif
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MAIN HEADER -->
    <header class="glass-header border-b border-slate-200/80 sticky top-0 z-40 shadow-xs transition-all duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 sm:h-20 gap-4">
                
                <!-- Logo & Brand Badge -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-white to-slate-50 border border-slate-200/80 p-1.5 shadow-xs group-hover:shadow-md group-hover:scale-105 transition-all duration-300 flex items-center justify-center">
                            <img src="{{ asset('logo/logo.png') }}" alt="AquawaSouk" class="w-full h-full object-contain">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xl sm:text-2xl font-black tracking-tight bg-gradient-to-r from-sky-600 via-sky-700 to-indigo-700 bg-clip-text text-transparent group-hover:opacity-90 transition">
                                AquawaSouk
                            </span>
                            <span class="text-[9px] sm:text-[10px] text-slate-400 font-bold tracking-widest uppercase -mt-0.5 hidden sm:block">
                                {{ app()->getLocale() === 'ar' ? 'سوق تونس الإلكتروني' : 'Marketplace Tunisie' }}
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Search Bar -->
                <div class="hidden md:flex flex-1 max-w-xl mx-4">
                    <form action="{{ route('products.index') }}" method="GET" class="w-full relative group">
                        <div class="relative flex items-center">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}"
                                placeholder="{{ __('messages.search_products') }}" 
                                class="w-full {{ app()->getLocale() === 'ar' ? 'pr-11 pl-28' : 'pl-11 pr-28' }} py-3 rounded-2xl border border-slate-300/90 focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 text-xs sm:text-sm transition-all duration-200 outline-hidden bg-slate-50/70 focus:bg-white shadow-2xs"
                            >
                            <svg class="w-5 h-5 text-slate-400 absolute {{ app()->getLocale() === 'ar' ? 'right-3.5' : 'left-3.5' }} top-1/2 -translate-y-1/2 group-focus-within:text-sky-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <button type="submit" class="absolute {{ app()->getLocale() === 'ar' ? 'left-1.5' : 'right-1.5' }} top-1/2 -translate-y-1/2 px-4 py-2 bg-gradient-to-r from-sky-600 to-sky-700 hover:from-sky-500 hover:to-sky-600 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow-md transition active:scale-95 cursor-pointer">
                                {{ __('messages.search') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Action Buttons: Language, Cart, Auth, Mobile toggle -->
                <div class="flex items-center gap-2 sm:gap-3">
                    
                    <!-- Header Language Selector Pill -->
                    <div class="relative" x-data="{ langOpenHeader: false }">
                        <button @click="langOpenHeader = !langOpenHeader" class="px-2.5 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 border border-slate-200/80 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                            @if(app()->getLocale() === 'ar')
                                <span>🇹🇳</span> <span class="hidden sm:inline">العربية</span>
                            @elseif(app()->getLocale() === 'en')
                                <span>🇬🇧</span> <span class="hidden sm:inline">EN</span>
                            @else
                                <span>🇫🇷</span> <span class="hidden sm:inline">FR</span>
                            @endif
                            <svg class="w-3 h-3 text-slate-400 transition transform" :class="langOpenHeader ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="langOpenHeader" @click.away="langOpenHeader = false" x-cloak class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-36 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-50 text-xs divide-y divide-slate-100">
                            <a href="{{ route('language.switch', 'fr') }}" class="flex items-center justify-between px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-semibold transition {{ app()->getLocale() === 'fr' ? 'text-sky-600 bg-sky-50/50 font-bold' : '' }}">
                                <span class="flex items-center gap-2"><span>🇫🇷</span> Français</span>
                                @if(app()->getLocale() === 'fr') <span class="text-sky-600 font-bold">✓</span> @endif
                            </a>
                            <a href="{{ route('language.switch', 'ar') }}" class="flex items-center justify-between px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-semibold transition {{ app()->getLocale() === 'ar' ? 'text-sky-600 bg-sky-50/50 font-bold' : '' }}">
                                <span class="flex items-center gap-2"><span>🇹🇳</span> العربية</span>
                                @if(app()->getLocale() === 'ar') <span class="text-sky-600 font-bold">✓</span> @endif
                            </a>
                            <a href="{{ route('language.switch', 'en') }}" class="flex items-center justify-between px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-semibold transition {{ app()->getLocale() === 'en' ? 'text-sky-600 bg-sky-50/50 font-bold' : '' }}">
                                <span class="flex items-center gap-2"><span>🇬🇧</span> English</span>
                                @if(app()->getLocale() === 'en') <span class="text-sky-600 font-bold">✓</span> @endif
                            </a>
                        </div>
                    </div>

                    <!-- Search button (Mobile only) -->
                    <button @click="searchOpen = !searchOpen" class="md:hidden p-2 text-slate-600 hover:text-sky-600 hover:bg-slate-100 rounded-xl transition cursor-pointer" title="{{ __('messages.search') }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    <!-- Wishlist Pill Button -->
                    <a href="{{ route('wishlist.index') }}" class="group relative px-2.5 sm:px-3 py-2 rounded-2xl bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 border border-slate-200/80 hover:border-rose-200 transition-all duration-200 flex items-center gap-1.5 shadow-2xs" title="{{ app()->getLocale() === 'ar' ? 'المفضلة' : 'Mes Favoris' }}">
                        <div class="relative flex items-center">
                            <span class="text-sm">❤️</span>
                            @auth
                                @php $headerWishCount = auth()->user()->wishlists()->count(); @endphp
                                <span id="header-wishlist-count" class="{{ $headerWishCount > 0 ? '' : 'hidden' }} absolute -top-2 {{ app()->getLocale() === 'ar' ? '-left-2' : '-right-2' }} bg-rose-600 text-white font-extrabold text-[10px] w-4.5 h-4.5 rounded-full flex items-center justify-center shadow-xs">
                                    {{ $headerWishCount }}
                                </span>
                            @endauth
                        </div>
                        <span class="hidden xl:inline text-xs font-bold text-slate-800 group-hover:text-rose-600 transition">{{ app()->getLocale() === 'ar' ? 'المفضلة' : 'Favoris' }}</span>
                    </a>

                    <!-- Cart Pill Button -->
                    <a href="{{ route('cart.index') }}" class="group relative px-3 py-2 rounded-2xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-600 border border-slate-200/80 hover:border-sky-200 transition-all duration-200 flex items-center gap-2 shadow-2xs" title="{{ __('messages.shopping_cart') }}">
                        <div class="relative">
                            <svg class="w-5 h-5 text-slate-700 group-hover:text-sky-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span class="absolute -top-2 {{ app()->getLocale() === 'ar' ? '-left-2' : '-right-2' }} bg-gradient-to-r from-rose-500 to-red-600 text-white font-extrabold text-[10px] w-4.5 h-4.5 rounded-full flex items-center justify-center shadow-xs">
                                {{ $cartCount ?? 0 }}
                            </span>
                        </div>
                        <span class="hidden sm:inline text-xs font-bold text-slate-800 group-hover:text-sky-600 transition">{{ __('messages.cart') }}</span>
                    </a>

                    @auth
                        @php
                            $unreadCount = auth()->user()->unreadNotifications()->count();
                            $recentNotifications = auth()->user()->notifications()->latest()->take(5)->get();
                        @endphp
                        <!-- Notifications Bell -->
                        <div class="relative" x-data="{ notifOpen: false }">
                            <button @click="notifOpen = !notifOpen" class="group relative p-2.5 rounded-2xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-600 border border-slate-200/80 hover:border-sky-200 transition flex items-center justify-center shadow-2xs cursor-pointer" title="Notifications">
                                <svg class="w-5 h-5 text-slate-700 group-hover:text-sky-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                @if($unreadCount > 0)
                                    <span class="absolute -top-1 {{ app()->getLocale() === 'ar' ? '-left-1' : '-right-1' }} bg-sky-600 text-white font-extrabold text-[10px] w-4.5 h-4.5 rounded-full flex items-center justify-center ring-2 ring-white">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </button>

                            <!-- Notifications Dropdown -->
                            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-80 bg-white rounded-3xl shadow-2xl border border-slate-200/90 py-3 z-50 text-xs divide-y divide-slate-100">
                                <div class="px-4 pb-2.5 flex items-center justify-between">
                                    <span class="font-extrabold text-slate-900 text-sm">Notifications</span>
                                    @if($unreadCount > 0)
                                        <form action="{{ route('notifications.readAll') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-[11px] text-sky-600 font-bold hover:underline cursor-pointer">Tout marquer comme lu</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="max-h-72 overflow-y-auto divide-y divide-slate-50">
                                    @forelse($recentNotifications as $notif)
                                        <a href="{{ $notif->data['url'] ?? '#' }}" class="block p-3.5 hover:bg-slate-50 transition {{ $notif->read_at ? 'opacity-70' : 'bg-sky-50/40 font-bold' }}">
                                            <p class="text-xs text-slate-900">{{ $notif->data['title'] ?? 'Notification' }}</p>
                                            <p class="text-[11px] text-slate-500 font-normal mt-0.5">{{ $notif->data['message'] ?? '' }}</p>
                                            <span class="text-[10px] text-slate-400 mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
                                        </a>
                                    @empty
                                        <div class="p-6 text-center text-slate-400">
                                            <span class="text-2xl block mb-1">🔔</span>
                                            <p>Aucune notification</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endauth

                    <!-- Account / Auth dropdown -->
                    @auth
                        <div class="relative" x-data="{ userMenu: false }">
                            <button @click="userMenu = !userMenu" class="flex items-center gap-2 p-1.5 rounded-2xl hover:bg-slate-100 transition border border-transparent hover:border-slate-200 cursor-pointer">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="hidden lg:inline text-xs font-bold text-slate-800">{{ auth()->user()->name }}</span>
                            </button>
                            <div x-show="userMenu" @click.away="userMenu = false" x-cloak class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-200/80 py-2 z-50 text-xs divide-y divide-slate-100">
                                <div class="px-4 py-2">
                                    <p class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="py-1">
                                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-sky-50 text-slate-700 hover:text-sky-600 font-semibold transition">{{ __('messages.dashboard') }}</a>
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-sky-50 text-slate-700 hover:text-sky-600 font-semibold transition">{{ __('messages.settings') ?? 'Paramètres' }}</a>
                                </div>
                                <div class="pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 hover:bg-rose-50 text-rose-600 font-bold transition cursor-pointer">{{ __('messages.logout') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-slate-700 hover:text-sky-600 hover:bg-slate-100 rounded-xl transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>{{ __('messages.login') }}</span>
                        </a>
                        <a href="{{ route('register') }}" class="hidden md:inline-flex items-center px-4 py-2 text-xs font-extrabold text-white bg-gradient-to-r from-sky-600 to-sky-700 hover:from-sky-500 hover:to-sky-600 rounded-xl shadow-xs transition transform hover:-translate-y-0.5">
                            {{ __('messages.register') }}
                        </a>
                    @endauth

                    <!-- Mobile Drawer Toggle -->
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar (Collapsible) -->
            <div x-show="searchOpen" x-cloak class="md:hidden pb-3.5">
                <form action="{{ route('products.index') }}" method="GET" class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="{{ __('messages.search_products') }}" 
                        class="w-full {{ app()->getLocale() === 'ar' ? 'pr-10 pl-20' : 'pl-10 pr-20' }} py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden bg-slate-50"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute {{ app()->getLocale() === 'ar' ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <button type="submit" class="absolute {{ app()->getLocale() === 'ar' ? 'left-1.5' : 'right-1.5' }} top-1/2 -translate-y-1/2 px-3 py-1.5 bg-sky-600 text-white rounded-lg text-xs font-bold">{{ __('messages.search') }}</button>
                </form>
            </div>
        </div>

        <!-- 4. CATEGORIES SUB-NAVBAR -->
        <nav class="bg-white/80 border-t border-slate-100 hidden md:block">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-2 text-xs font-semibold text-slate-600">
                    <a href="{{ route('products.index') }}" class="px-3 py-1.5 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 font-bold whitespace-nowrap flex items-center gap-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        <span>{{ __('messages.all_categories') }}</span>
                    </a>
                    
                    @if(isset($navCategories) && $navCategories->count())
                        @foreach($navCategories->take(6) as $cat)
                            <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="px-3 py-1.5 rounded-xl hover:bg-slate-100 hover:text-sky-600 whitespace-nowrap transition">
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    @else
                        <a href="{{ route('products.index') }}" class="px-3 py-1.5 rounded-xl hover:bg-slate-100 hover:text-sky-600 whitespace-nowrap">{{ app()->getLocale() === 'ar' ? 'أجهزة وتكنولوجيا' : 'High-Tech & Téléphonie' }}</a>
                        <a href="{{ route('products.index') }}" class="px-3 py-1.5 rounded-xl hover:bg-slate-100 hover:text-sky-600 whitespace-nowrap">{{ app()->getLocale() === 'ar' ? 'موضة وملابس' : 'Mode & Vêtements' }}</a>
                        <a href="{{ route('products.index') }}" class="px-3 py-1.5 rounded-xl hover:bg-slate-100 hover:text-sky-600 whitespace-nowrap">{{ app()->getLocale() === 'ar' ? 'منزل ومطبخ' : 'Maison & Cuisine' }}</a>
                        <a href="{{ route('products.index') }}" class="px-3 py-1.5 rounded-xl hover:bg-slate-100 hover:text-sky-600 whitespace-nowrap">{{ app()->getLocale() === 'ar' ? 'جمال وعناية' : 'Beauté & Cosmétiques' }}</a>
                    @endif

                    <a href="{{ route('vendors.index') }}" class="{{ app()->getLocale() === 'ar' ? 'mr-auto' : 'ml-auto' }} px-3.5 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold whitespace-nowrap flex items-center gap-1.5 transition">
                        <span>🏪</span>
                        <span>{{ __('messages.vendors') }}</span>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- MOBILE DRAWER MENU -->
    <div x-show="mobileMenu" x-cloak class="relative z-50 md:hidden">
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" @click="mobileMenu = false"></div>
        <div class="fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} max-w-xs w-full bg-white shadow-2xl p-6 flex flex-col justify-between overflow-y-auto">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('logo/logo.png') }}" alt="Logo" class="w-8 h-8 rounded-xl object-contain">
                        <span class="font-extrabold text-lg bg-gradient-to-r from-sky-600 to-indigo-600 bg-clip-text text-transparent">AquawaSouk</span>
                    </div>
                    <button @click="mobileMenu = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Language Selector in Mobile Drawer -->
                <div class="mt-4 p-2.5 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'اختر اللغة' : 'Choisir la langue' }} :
                    </div>
                    <div class="grid grid-cols-3 gap-1.5 text-center">
                        <a href="{{ route('language.switch', 'fr') }}" class="py-1.5 px-2 rounded-xl text-xs font-bold transition {{ app()->getLocale() === 'fr' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                            🇫🇷 FR
                        </a>
                        <a href="{{ route('language.switch', 'ar') }}" class="py-1.5 px-2 rounded-xl text-xs font-bold transition {{ app()->getLocale() === 'ar' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                            🇹🇳 عربي
                        </a>
                        <a href="{{ route('language.switch', 'en') }}" class="py-1.5 px-2 rounded-xl text-xs font-bold transition {{ app()->getLocale() === 'en' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                            🇬🇧 EN
                        </a>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-1.5 text-sm font-semibold">
                    <a href="{{ route('home') }}" class="p-3 rounded-2xl hover:bg-slate-100 text-slate-800 flex items-center gap-3">
                        <span>🏠</span> {{ __('messages.home') }}
                    </a>
                    <a href="{{ route('products.index') }}" class="p-3 rounded-2xl hover:bg-slate-100 text-slate-800 flex items-center gap-3">
                        <span>📦</span> {{ __('messages.all_products') }}
                    </a>
                    <a href="{{ route('cart.index') }}" class="p-3 rounded-2xl hover:bg-slate-100 text-slate-800 flex items-center gap-3">
                        <span>🛒</span> {{ __('messages.cart') }}
                    </a>
                    <a href="{{ route('vendors.index') }}" class="p-3 rounded-2xl hover:bg-slate-100 text-slate-800 flex items-center gap-3">
                        <span>🏪</span> {{ __('messages.vendors') }}
                    </a>
                    <a href="{{ route('become-vendor') }}" class="p-3 rounded-2xl bg-gradient-to-r from-sky-50 to-indigo-50 text-sky-700 font-extrabold flex items-center gap-3">
                        <span>🚀</span> {{ __('messages.become_a_vendor') }}
                    </a>
                    <a href="{{ route('about') }}" class="p-3 rounded-2xl hover:bg-slate-100 text-slate-700 flex items-center gap-3">
                        <span>ℹ️</span> {{ __('messages.about') }}
                    </a>
                    <a href="{{ route('contact') }}" class="p-3 rounded-2xl hover:bg-slate-100 text-slate-700 flex items-center gap-3">
                        <span>📞</span> {{ __('messages.contact') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-4 mb-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- MAIN CONTENT SLOT -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-slate-950 text-slate-300 mt-20 border-t border-slate-800/80">
        
        <!-- Trust Elements Bar -->
        <div class="border-b border-slate-800/60 py-10 bg-slate-900/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-13 h-13 rounded-2xl bg-sky-500/10 text-sky-400 flex items-center justify-center mb-3.5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>
                    <h4 class="text-sm font-extrabold text-white">{{ app()->getLocale() === 'ar' ? 'توصيل سريع 24-48 ساعة' : (app()->getLocale() === 'en' ? 'Fast Delivery 24-48h' : 'Livraison Express 24-48h') }}</h4>
                    <p class="text-xs text-slate-400 mt-0.5">{{ app()->getLocale() === 'ar' ? 'في كامل تراب الجمهورية' : 'Dans les 24 gouvernorats' }}</p>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-13 h-13 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-3.5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h4 class="text-sm font-extrabold text-white">{{ __('messages.cash_on_delivery') }}</h4>
                    <p class="text-xs text-slate-400 mt-0.5">{{ app()->getLocale() === 'ar' ? 'ادفع نقداً عند استلام الطرد' : 'Réglez le livreur en espèces' }}</p>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-13 h-13 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-3.5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h4 class="text-sm font-extrabold text-white">{{ app()->getLocale() === 'ar' ? 'بائعون معتمدون 100%' : 'Vendeurs 100% Vérifiés' }}</h4>
                    <p class="text-xs text-slate-400 mt-0.5">{{ app()->getLocale() === 'ar' ? 'منتجات أصلية ومضمونة' : 'Articles conformes & testés' }}</p>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-13 h-13 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-3.5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h4 class="text-sm font-extrabold text-white">{{ app()->getLocale() === 'ar' ? 'خدمة حرفاء 6 أيام / 7' : 'Assistance 6j/7' }}</h4>
                    <p class="text-xs text-slate-400 mt-0.5">{{ app()->getLocale() === 'ar' ? 'عبر الهاتف والواتساب' : 'Par Téléphone & WhatsApp' }}</p>
                </div>
            </div>
        </div>

        <!-- Links Columns -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <!-- Col 1: About -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('logo/logo.png') }}" alt="AquawaSouk" class="w-10 h-10 rounded-xl bg-white p-1 object-contain">
                        <span class="text-xl font-black text-white">AquawaSouk</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ app()->getLocale() === 'ar' ? 'منصة التسوق الإلكتروني الرائدة في تونس. نربط أفضل التجار مع المشترين الباحثين عن الجودة والتميز.' : 'La marketplace e-commerce multi-catégories de référence en Tunisie. Nous connectons les meilleurs marchands avec des acheteurs à la recherche de qualité et d\'excellence.' }}
                    </p>
                    <div class="pt-2 text-xs text-slate-400 space-y-1">
                        <p>📍 {{ app()->getLocale() === 'ar' ? 'تونس، الجمهورية التونسية' : 'Tunis, Tunisie' }}</p>
                        <p>📞 +216 26 260 262</p>
                        <p>✉️ contact@aquawasouk.com</p>
                    </div>
                </div>

                <!-- Col 2: Navigation -->
                <div>
                    <h5 class="text-xs font-extrabold text-white uppercase tracking-wider mb-4">{{ app()->getLocale() === 'ar' ? 'روابط سريعة' : 'Navigation' }}</h5>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ route('home') }}" class="hover:text-sky-400 transition">{{ __('messages.home') }}</a></li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-sky-400 transition">{{ __('messages.all_products') }}</a></li>
                        <li><a href="{{ route('vendors.index') }}" class="hover:text-sky-400 transition">{{ __('messages.vendors') }}</a></li>
                        <li><a href="{{ route('cart.index') }}" class="hover:text-sky-400 transition">{{ __('messages.cart') }}</a></li>
                        <li><a href="{{ route('become-vendor') }}" class="hover:text-amber-300 font-bold transition text-amber-400">{{ __('messages.become_a_vendor') }}</a></li>
                    </ul>
                </div>

                <!-- Col 3: Assistance & Infos -->
                <div>
                    <h5 class="text-xs font-extrabold text-white uppercase tracking-wider mb-4">{{ app()->getLocale() === 'ar' ? 'المساعدة والأمان' : 'Assistance & Sécurité' }}</h5>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ route('about') }}" class="hover:text-sky-400 transition">{{ __('messages.about') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-sky-400 transition">{{ __('messages.contact') }}</a></li>
                        <li><a href="{{ route('privacy-policy') }}" class="hover:text-sky-400 transition">{{ __('messages.privacy_policy') }}</a></li>
                        <li><a href="{{ route('terms-of-service') }}" class="hover:text-sky-400 transition">{{ __('messages.terms_of_service') }}</a></li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter -->
                <div>
                    <h5 class="text-xs font-extrabold text-white uppercase tracking-wider mb-4">{{ app()->getLocale() === 'ar' ? 'النشرة البريدية' : 'Restez Informé' }}</h5>
                    <p class="text-xs text-slate-400 mb-3 leading-relaxed">
                        {{ app()->getLocale() === 'ar' ? 'اشترك لتصلك أحدث العروض والخصومات الحصرية أولاً بأول:' : 'Recevez nos offres exclusives et réductions exclusives par e-mail :' }}
                    </p>
                    <form onsubmit="event.preventDefault(); alert('{{ app()->getLocale() === 'ar' ? 'شكراً لاشتراكك !' : 'Merci pour votre inscription !' }}');" class="space-y-2">
                        <input type="email" placeholder="{{ app()->getLocale() === 'ar' ? 'بريدك الإلكتروني' : 'Votre adresse e-mail' }}" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-hidden focus:ring-2 focus:ring-sky-500">
                        <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            {{ app()->getLocale() === 'ar' ? 'اشتراك' : 'S\'abonner' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="border-t border-slate-800/80 mt-12 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} AquawaSouk Tunisie. {{ __('messages.all_rights_reserved') }}.</p>
                <div class="flex items-center gap-4 text-slate-400">
                    <a href="{{ route('privacy-policy') }}" class="hover:text-white transition">{{ __('messages.privacy_policy') }}</a>
                    <span>•</span>
                    <a href="{{ route('terms-of-service') }}" class="hover:text-white transition">{{ __('messages.terms_of_service') }}</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- AI Shopping Assistant Chatbot (Floating Widget) -->
    @include('blade.components.chatbot')

    <script>
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
            }
        })
        .catch(err => console.error(err));
    }
    </script>

    @stack('scripts')
</body>
</html>
