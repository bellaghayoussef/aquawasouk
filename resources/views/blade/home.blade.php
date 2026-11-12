@extends('blade.layout')

@section('title', 'AquawaSouk - Marketplace en Tunisie | Mode, High-Tech, Maison & Bricolage')
@section('meta_description', 'Découvrez des milliers de produits aux meilleurs prix en Tunisie. Livraison express dans tous les gouvernorats et paiement à la livraison.')

@section('content')

    @php
        $isAr = app()->getLocale() === 'ar';
        $currency = $isAr ? 'د.ت' : 'DT';
        $heroList = isset($heroProducts) && $heroProducts->count() ? $heroProducts : $featuredProducts->take(4);
    @endphp

    <div x-data="{
                quickViewOpen: false,
                quickViewProduct: null,
                recentProducts: [],
                init() {
                    try {
                        this.recentProducts = JSON.parse(localStorage.getItem('aquawa_recent_products') || '[]');
                    } catch(e) {
                        this.recentProducts = [];
                    }
                },
                openQuickView(prod) {
                    this.quickViewProduct = prod;
                    this.quickViewOpen = true;
                },
                clearRecent() {
                    localStorage.removeItem('aquawa_recent_products');
                    this.recentProducts = [];
                }
            }" @open-quick-view.window="openQuickView($event.detail)" class="space-y-12 sm:space-y-16">

        <!-- ============================================================== -->
        <!-- 1. HERO SECTION & PROMO SPOTLIGHT                              -->
        <!-- ============================================================== -->
        <div class="relative bg-slate-950 text-white overflow-hidden py-10 sm:py-16 lg:py-20 border-b border-slate-800/80">
            <!-- Ambient Glowing Lights -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 -right-32 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none">
            </div>
            <div class="absolute -bottom-32 left-1/3 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none">
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                    <!-- Left: Hero Headline & Value Proposition -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left rtl:lg:text-right">

                        <!-- Top Pill Badge -->
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-sky-300 text-xs font-bold shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ $isAr ? '🇹🇳 منصة التسوق الأولى في تونس • دفع عند الاستلام' : '🇹🇳 Marketplace N°1 en Tunisie • Paiement à la livraison' }}</span>
                        </div>

                        <!-- Grand Headline -->
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.15]">
                            @if($isAr)
                                تسوق كل ما تحتاجه <br class="hidden sm:inline">
                                <span
                                    class="bg-gradient-to-r from-sky-400 via-teal-300 to-amber-300 bg-clip-text text-transparent">بأفضل
                                    الأسعار في تونس</span>
                            @else
                                Le meilleur du shopping <br class="hidden sm:inline">
                                <span
                                    class="bg-gradient-to-r from-sky-400 via-teal-300 to-amber-300 bg-clip-text text-transparent">au
                                    meilleur prix en Tunisie</span>
                            @endif
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-slate-300 text-sm sm:text-base max-w-xl mx-auto lg:mx-0 leading-relaxed font-normal">
                            {{ $isAr ? 'آلاف المنتجات المختارة من بائعين معتمدين: هواتف وتكنولوجيا، كهرومنزلي، أزياء ومستلزمات منزلية. اطلب مباشرة بدون بطاقة بنكية مع توصيل سريع لجميع الولايات (24-48 ساعة).' : 'Des milliers de produits de vendeurs certifiés : High-Tech, Maison, Mode et Bricolage. Commandez en 1 clic sans carte bancaire avec livraison rapide dans les 24 gouvernorats.' }}
                        </p>

                        <!-- Dual CTAs -->
                        <div
                            class="flex flex-col sm:flex-row items-center justify-center lg:justify-start rtl:lg:justify-start gap-3 pt-2">
                            <a href="{{ route('products.index') }}"
                                class="w-full sm:w-auto px-7 py-3.5 bg-gradient-to-r from-sky-500 via-sky-600 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-sky-500/25 hover:shadow-xl transition transform hover:-translate-y-0.5 text-center flex items-center justify-center gap-2 cursor-pointer">
                                <span>{{ __('messages.browse_products') }}</span>
                                <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                            </a>
                            <a href="{{ route('become-vendor') }}"
                                class="w-full sm:w-auto px-6 py-3.5 bg-white/10 hover:bg-white/15 text-white border border-white/20 font-bold text-sm rounded-2xl backdrop-blur-md transition text-center flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🚀</span>
                                <span>{{ __('messages.become_a_vendor') }}</span>
                            </a>
                        </div>

                        <!-- Trust Stats & Guarantees -->
                        <div class="grid grid-cols-3 gap-3 sm:gap-6 pt-6 border-t border-white/10 max-w-lg mx-auto lg:mx-0">
                            <div class="flex flex-col">
                                <span class="text-xl sm:text-2xl font-black text-white">24/24</span>
                                <span
                                    class="text-[11px] text-slate-400 font-medium">{{ $isAr ? 'ولاية مغطاة' : 'Gouvernorats livrés' }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xl sm:text-2xl font-black text-amber-400">100%</span>
                                <span
                                    class="text-[11px] text-slate-400 font-medium">{{ $isAr ? 'دفع عند الاستلام' : 'Paiement à réception' }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xl sm:text-2xl font-black text-emerald-400">24-48h</span>
                                <span
                                    class="text-[11px] text-slate-400 font-medium">{{ $isAr ? 'توصيل سريع' : 'Livraison express' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: High-Converting Spotlight Showcase (Rotating Deals) -->
                    <div class="lg:col-span-5" x-data="{
                                activeIdx: 0,
                                total: {{ $heroList->count() }},
                                timer: null,
                                start() {
                                    this.timer = setInterval(() => {
                                        this.activeIdx = (this.activeIdx + 1) % this.total;
                                    }, 5000);
                                },
                                stop() {
                                    clearInterval(this.timer);
                                }
                            }" x-init="start()" @mouseenter="stop()" @mouseleave="start()">

                        <div
                            class="relative bg-gradient-to-b from-white/15 to-white/5 backdrop-blur-xl border border-white/20 rounded-3xl p-5 sm:p-6 shadow-2xl text-white overflow-hidden">

                            <!-- Top Spotlight Header with Switcher Dots -->
                            <div class="flex items-center justify-between pb-3.5 border-b border-white/10">
                                <span
                                    class="inline-flex items-center gap-1.5 text-xs uppercase font-extrabold tracking-wider text-amber-300">
                                    <span>🔥</span>
                                    <span>{{ $isAr ? 'عروض اليوم المميزة' : 'Offres Vedettes du Jour' }}</span>
                                </span>

                                <!-- Indicator Dots -->
                                @if($heroList->count() > 1)
                                    <div class="flex items-center gap-1.5">
                                        @foreach($heroList as $idx => $hp)
                                            <button type="button" @click="activeIdx = {{ $idx }}"
                                                :class="activeIdx === {{ $idx }} ? 'w-6 bg-sky-400' : 'w-2 bg-white/40 hover:bg-white/70'"
                                                class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                                title="{{ $hp->name }}"></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Products Slides -->
                            @foreach($heroList as $idx => $prod)
                                @php
                                    $imgSrc = $prod->image_url;
                                    $hasDiscount = $prod->discount_price && $prod->discount_price < $prod->price;
                                    $pct = $hasDiscount && $prod->price > 0 ? round((($prod->price - $prod->discount_price) / $prod->price) * 100) : 0;
                                    $displayPrice = $hasDiscount ? $prod->discount_price : $prod->price;
                                @endphp

                                <div x-show="activeIdx === {{ $idx }}" x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    class="pt-4 flex flex-col justify-between">
                                    <!-- Vendor & Discount Badges -->
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        @if($prod->vendor)
                                            <a href="{{ route('vendors.show', $prod->vendor->slug) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold transition">
                                                <span>🏪 {{ $prod->vendor->shop_name }}</span>
                                            </a>
                                        @else
                                            <span></span>
                                        @endif

                                        @if($hasDiscount)
                                            <span
                                                class="px-2.5 py-0.5 rounded-full bg-rose-600 font-black text-xs text-white shadow-xs">
                                                -{{ $pct }}%
                                            </span>
                                        @else
                                            <span
                                                class="px-2.5 py-0.5 rounded-full bg-emerald-500 font-bold text-xs text-white shadow-xs">
                                                ★ {{ __('messages.featured') }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- White Product Image Card -->
                                    <a href="{{ route('products.show', $prod->slug) }}" class="block group">
                                        <div
                                            class="w-full h-48 sm:h-52 bg-white rounded-2xl p-4 flex items-center justify-center relative overflow-hidden shadow-md">
                                            <img src="{{ $imgSrc }}"
                                                onerror="this.onerror=null; this.src='{{ asset('logo/logo.png') }}';"
                                                alt="{{ $prod->name }}"
                                                class="max-h-full max-w-full object-contain group-hover:scale-106 transition-transform duration-300 ease-out">
                                            <span
                                                class="absolute bottom-2 {{ $isAr ? 'right-2' : 'left-2' }} px-2 py-0.5 rounded-md bg-slate-900/80 text-white text-[10px] font-medium">
                                                💵 {{ $isAr ? 'الدفع عند الاستلام' : 'Paiement à la livraison' }}
                                            </span>
                                        </div>
                                    </a>

                                    <!-- Product Info & Price -->
                                    <div class="mt-3.5 space-y-1 text-center">
                                        <h3
                                            class="text-sm sm:text-base font-black text-white truncate hover:text-sky-300 transition">
                                            <a href="{{ route('products.show', $prod->slug) }}">
                                                {{ $prod->name }}
                                            </a>
                                        </h3>

                                        <div class="flex items-baseline justify-center gap-2 pt-1">
                                            <span class="text-2xl sm:text-3xl font-black text-amber-300 tracking-tight">
                                                {{ number_format($displayPrice, 2) }} <span
                                                    class="text-xs font-bold">{{ $currency }}</span>
                                            </span>
                                            @if($hasDiscount)
                                                <span class="text-xs sm:text-sm text-slate-400 line-through">
                                                    {{ number_format($prod->price, 2) }} {{ $currency }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($prod->category)
                                            <p class="text-[11px] text-sky-200 font-medium">
                                                {{ $prod->category->name }}
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Action Buttons: Direct Order Primary CTA -->
                                    <div class="pt-4 grid grid-cols-2 gap-2">
                                        <a href="{{ route('products.show', $prod->slug) }}#form-commande-directe"
                                            class="py-2.5 px-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-black text-xs rounded-xl text-center shadow-lg shadow-emerald-500/25 transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                            <span class="text-amber-300">⚡</span>
                                            <span>{{ __('messages.direct_order') }}</span>
                                        </a>
                                        <a href="{{ route('products.show', $prod->slug) }}"
                                            class="py-2.5 px-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl text-center border border-white/20 transition flex items-center justify-center gap-1 cursor-pointer">
                                            <span>{{ __('messages.view_details') }}</span>
                                            <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 2. SMART CATEGORY NAVIGATION (Chips Bar)                       -->
        <!-- ============================================================== -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-2">
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-sky-600 text-white font-bold text-xs shrink-0 shadow-xs hover:bg-sky-700 transition">
                    <span>🛍️</span>
                    <span>{{ __('messages.all_categories') }}</span>
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-white border border-slate-200/90 hover:border-sky-400 text-slate-700 hover:text-sky-600 font-semibold text-xs shrink-0 transition shadow-2xs hover:shadow-xs group">
                        @if($cat->image_url)
                            <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}"
                                class="w-4 h-4 rounded object-contain shrink-0 group-hover:scale-110 transition">
                        @else
                            <span class="text-xs">📦</span>
                        @endif
                        <span>{{ $cat->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 3. PROMOTIONAL BANNERS CAROUSEL (Model: Banner)               -->
        <!-- ============================================================== -->
        @if(isset($banners) && $banners->count() > 0)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if($banners->count() === 1)
                    @php $singleBanner = $banners->first(); @endphp
                    <a href="{{ $singleBanner->link_url ?: '#' }}"
                        class="block group relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-md hover:shadow-xl border border-slate-200/80 transition-all duration-300">
                        <div
                            class="w-full aspect-[21/9] sm:aspect-[24/7] md:aspect-[3/1] max-h-72 bg-slate-100 overflow-hidden relative">
                            <img src="{{ $singleBanner->image_url }}" alt="{{ $singleBanner->title ?? 'Bannière Promotionnelle' }}"
                                class="w-full h-full object-cover object-center group-hover:scale-102 transition-transform duration-500 ease-out"
                                loading="lazy">
                            @if($singleBanner->title)
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent flex items-end p-4 sm:p-6">
                                    <h3 class="text-white text-base sm:text-xl font-black drop-shadow-md">
                                        {{ $singleBanner->title }}
                                    </h3>
                                </div>
                            @endif
                        </div>
                    </a>
                @elseif($banners->count() === 2)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        @foreach($banners as $b)
                            <a href="{{ $b->link_url ?: '#' }}"
                                class="block group relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-md hover:shadow-xl border border-slate-200/80 transition-all duration-300">
                                <div class="w-full aspect-[16/9] sm:aspect-[2/1] max-h-56 bg-slate-100 overflow-hidden relative">
                                    <img src="{{ $b->image_url }}" alt="{{ $b->title ?? 'Bannière' }}"
                                        class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500 ease-out"
                                        loading="lazy">
                                    @if($b->title)
                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent flex items-end p-4 sm:p-5">
                                            <h3 class="text-white text-sm sm:text-base font-black drop-shadow-md">
                                                {{ $b->title }}
                                            </h3>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <!-- 3+ banners slider -->
                    <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl shadow-md hover:shadow-xl border border-slate-200/80 group transition-all duration-300"
                        x-data="{
                                                    activeBanner: 0,
                                                    totalBanners: {{ $banners->count() }},
                                                    bannerTimer: null,
                                                    startBannerTimer() {
                                                        this.bannerTimer = setInterval(() => {
                                                            this.activeBanner = (this.activeBanner + 1) % this.totalBanners;
                                                        }, 5000);
                                                    },
                                                    stopBannerTimer() {
                                                        if (this.bannerTimer) clearInterval(this.bannerTimer);
                                                    }
                                                }" x-init="startBannerTimer()" @mouseenter="stopBannerTimer()"
                        @mouseleave="startBannerTimer()">
                        <div
                            class="relative w-full aspect-[21/9] sm:aspect-[24/7] md:aspect-[3/1] max-h-72 bg-slate-100 overflow-hidden">
                            @foreach($banners as $idx => $b)
                                <div x-show="activeBanner === {{ $idx }}" x-transition:enter="transition ease-out duration-500"
                                    x-transition:enter-start="opacity-0 scale-99" x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-300"
                                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-99"
                                    class="absolute inset-0 w-full h-full">
                                    <a href="{{ $b->link_url ?: '#' }}" class="block w-full h-full relative">
                                        <img src="{{ $b->image_url }}" alt="{{ $b->title ?? 'Bannière' }}"
                                            class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-500"
                                            loading="lazy">
                                        @if($b->title)
                                            <div
                                                class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent flex items-end p-4 sm:p-6">
                                                <h3 class="text-white text-base sm:text-xl font-black drop-shadow-md">
                                                    {{ $b->title }}
                                                </h3>
                                            </div>
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <!-- Previous / Next Controls -->
                        <button type="button" @click="activeBanner = (activeBanner - 1 + totalBanners) % totalBanners"
                            class="absolute top-1/2 {{ $isAr ? 'right-3' : 'left-3' }} -translate-y-1/2 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/80 hover:bg-white text-slate-800 shadow-md flex items-center justify-center transition opacity-0 group-hover:opacity-100 cursor-pointer"
                            title="Précédent">
                            <svg class="w-5 h-5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button type="button" @click="activeBanner = (activeBanner + 1) % totalBanners"
                            class="absolute top-1/2 {{ $isAr ? 'left-3' : 'right-3' }} -translate-y-1/2 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/80 hover:bg-white text-slate-800 shadow-md flex items-center justify-center transition opacity-0 group-hover:opacity-100 cursor-pointer"
                            title="Suivant">
                            <svg class="w-5 h-5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <!-- Indicator Dots -->
                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-20">
                            @foreach($banners as $idx => $b)
                                <button type="button" @click="activeBanner = {{ $idx }}"
                                    :class="activeBanner === {{ $idx }} ? 'w-6 bg-sky-500' : 'w-2 bg-white/70 hover:bg-white'"
                                    class="h-2 rounded-full transition-all duration-300 cursor-pointer shadow-xs"></button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- ============================================================== -->
        <!-- 4. FLASH DEALS & PROMOTIONS DU MOMENT (Avec compte à rebours)  -->
        <!-- ============================================================== -->
        @if(isset($flashProducts) && $flashProducts->count() > 0)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <section
                    class="bg-gradient-to-r from-rose-500/10 via-amber-500/10 to-transparent p-4 sm:p-6 rounded-3xl border border-rose-200/80">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-10 h-10 rounded-2xl bg-rose-600 text-white font-black text-xl flex items-center justify-center shadow-md">
                                ⚡
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                        {{ $isAr ? 'عروض وتخفيضات اليوم' : 'Ventes Flash & Promotions' }}
                                    </h2>
                                    <span
                                        class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-black uppercase tracking-wider animate-pulse">
                                        {{ $isAr ? 'تخفيضات حقيقية' : 'Jusqu\'à -50%' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $isAr ? 'كميات محدودة وأسعار مخفضة لفترة وجيزة' : 'Quantités limitées aux meilleurs prix de la marketplace' }}
                                </p>
                            </div>
                        </div>

                        <!-- Live Countdown Timer Simulation -->
                        <div x-data="{
                                            hours: 8,
                                            minutes: 42,
                                            seconds: 35,
                                            tick() {
                                                setInterval(() => {
                                                    if (this.seconds > 0) {
                                                        this.seconds--;
                                                    } else {
                                                        this.seconds = 59;
                                                        if (this.minutes > 0) {
                                                            this.minutes--;
                                                        } else {
                                                            this.minutes = 59;
                                                            if (this.hours > 0) this.hours--;
                                                        }
                                                    }
                                                }, 1000);
                                            }
                                        }" x-init="tick()"
                            class="inline-flex items-center gap-2 bg-white px-3.5 py-2 rounded-2xl border border-rose-200 shadow-2xs text-xs font-bold self-start sm:self-auto">
                            <span class="text-rose-600">⏳</span>
                            <span class="text-slate-600 text-[11px]">{{ $isAr ? 'ينتهي خلال :' : 'Fin dans :' }}</span>
                            <div class="flex items-center gap-1 font-mono text-slate-900 font-black">
                                <span class="bg-slate-100 px-1.5 py-0.5 rounded"
                                    x-text="String(hours).padStart(2, '0')">08</span>:
                                <span class="bg-slate-100 px-1.5 py-0.5 rounded"
                                    x-text="String(minutes).padStart(2, '0')">42</span>:
                                <span class="bg-slate-100 px-1.5 py-0.5 rounded"
                                    x-text="String(seconds).padStart(2, '0')">35</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
                        @foreach($flashProducts as $product)
                            @include('blade.components.product-card', ['product' => $product])
                        @endforeach
                    </div>
                </section>
            </div>
        @endif

        <!-- ============================================================== -->
        <!-- 5. 🔥 LES MEILLEURES VENTES (BEST SELLERS)                     -->
        <!-- ============================================================== -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <section>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                            🔥
                        </span>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                {{ $isAr ? 'المنتجات الأكثر مبيعاً وطلباً' : 'Les Meilleures Ventes' }}
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $isAr ? 'المنتجات الأعلى شعبية وتقييماً هذا الأسبوع' : 'Les articles plébiscités par nos clients en Tunisie' }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('products.index', ['sort' => 'popular']) }}"
                        class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1 transition">
                        <span>{{ __('messages.view_all') }}</span>
                        <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
                    @forelse($bestSellers as $product)
                        @include('blade.components.product-card', ['product' => $product])
                    @empty
                        <div class="col-span-full py-8 text-center text-slate-400">
                            {{ __('messages.no_products_found') }}
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- ============================================================== -->
        <!-- 6. 🛍️ NOS MEILLEURES CATÉGORIES (POPULAR DEPARTMENTS)          -->
        <!-- ============================================================== -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <section class="bg-slate-50/80 p-6 sm:p-8 rounded-3xl border border-slate-200/80">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            {{ __('messages.popular_departments') ?? 'Rayons & Catégories Populaires' }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('messages.browse_by_theme') ?? 'Explorez nos produits par univers' }}
                        </p>
                    </div>
                    <a href="{{ route('products.index') }}"
                        class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1 transition">
                        <span>{{ __('messages.view_all') }}</span>
                        <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                    @foreach($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                            class="group bg-white rounded-2xl p-4 border border-slate-200/90 hover:border-sky-400 hover:shadow-lg transition-all duration-300 text-center flex flex-col items-center justify-between">
                            <div
                                class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 group-hover:border-sky-200 p-1.5 flex items-center justify-center transition duration-300 mb-3 shadow-2xs overflow-hidden">
                                @if($category->image_url)
                                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}"
                                        class="w-full h-full object-contain group-hover:scale-110 transition duration-300"
                                        loading="lazy">
                                @else
                                    <div
                                        class="w-full h-full rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-600 group-hover:text-white flex items-center justify-center transition duration-200">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 group-hover:text-sky-600 transition">
                                    {{ $category->name }}
                                </h3>
                                <span class="text-[10px] text-slate-400 font-semibold mt-0.5 inline-block">
                                    {{ $category->products_count }} {{ $isAr ? 'منتج' : 'produits' }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        <!-- ============================================================== -->
        <!-- 7. ⭐ PRODUITS TENDANCE & SÉLECTION RECOMMANDÉE                 -->
        <!-- ============================================================== -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <section>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                            ⭐
                        </span>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                {{ $isAr ? 'منتجات رائجة ومختارة' : 'Produits Tendance & Sélection' }}
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $isAr ? 'تشكيلة مختارة من أفضل العروض المتاحة الآن' : 'Une sélection spéciale de produits de qualité supérieure' }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('products.index') }}"
                        class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1 transition">
                        <span>{{ __('messages.view_all') }}</span>
                        <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
                    @foreach($trendingProducts as $product)
                        @include('blade.components.product-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        </div>

        <!-- ============================================================== -->
        <!-- 8. 🛡️ PILLARS & REASSURANCE BANNER (4 piliers de confiance)   -->
        <!-- ============================================================== -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div
                            class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-xl shrink-0">
                            🚚
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white">Livraison 24-48h</h3>
                            <p class="text-xs text-slate-300 mt-0.5">Partout en Tunisie dans les 24 gouvernorats.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div
                            class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-xl shrink-0">
                            💵
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white">Paiement à la Livraison</h3>
                            <p class="text-xs text-slate-300 mt-0.5">Payez en espèces à réception en toute sécurité.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div
                            class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-xl shrink-0">
                            🛡️
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white">Vendeurs Vérifiés</h3>
                            <p class="text-xs text-slate-300 mt-0.5">Produits authentiques de commerçants agréés.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div
                            class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-xl shrink-0">
                            ⚡
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white">Commande Express</h3>
                            <p class="text-xs text-slate-300 mt-0.5">Achetez en 1 clic sans inscription obligatoire.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 9. 🆕 NOUVEAUTÉS (NEW ARRIVALS)                                -->
        <!-- ============================================================== -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <section>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                            🆕
                        </span>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                {{ $isAr ? 'أحدث المنتجات الوافدة' : 'Nouveaux Arrivages' }}
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $isAr ? 'منتجات جديدة أضيفت مؤخراً إلى السوق' : 'Découvrez les dernières nouveautés ajoutées sur la marketplace' }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('products.index', ['sort' => 'latest']) }}"
                        class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1 transition">
                        <span>{{ __('messages.view_all') }}</span>
                        <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
                    @foreach($newArrivals as $product)
                        @include('blade.components.product-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        </div>

        <!-- ============================================================== -->
        <!-- 10. 🏪 BOUTIQUES PARTENAIRES OFFICIELLES                      -->
        <!-- ============================================================== -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <section class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xs">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                            🏪
                        </span>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                {{ __('messages.official_shops') }}
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ __('messages.buy_direct_vendors') ?? 'Achetez directement auprès des meilleurs commerçants tunisiens' }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('vendors.index') }}"
                        class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1 transition">
                        <span>{{ __('messages.all_shops') ?? 'Toutes les boutiques' }}</span>
                        <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    @forelse($vendors as $vendor)
                        <div
                            class="group bg-slate-50/70 hover:bg-white rounded-2xl border border-slate-200/80 hover:border-indigo-300 p-4 sm:p-5 hover:shadow-lg transition-all duration-300 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5 truncate">
                                <div
                                    class="w-14 h-14 rounded-2xl bg-white border border-slate-200 p-1 shrink-0 overflow-hidden flex items-center justify-center shadow-2xs group-hover:scale-105 transition">
                                    @if($vendor->logo)
                                        <img src="{{ asset('uploads/' . $vendor->logo) }}" alt="{{ $vendor->shop_name }}"
                                            class="w-full h-full object-cover rounded-xl">
                                    @else
                                        <span class="text-xl">🏪</span>
                                    @endif
                                </div>
                                <div class="truncate">
                                    <h3
                                        class="font-bold text-sm text-slate-900 group-hover:text-indigo-600 transition truncate flex items-center gap-1.5">
                                        <span>{{ $vendor->shop_name }}</span>
                                        <span class="text-sky-500 text-xs" title="Vérifié">✓</span>
                                    </h3>
                                    <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                                        {{ $vendor->products_count }} {{ $isAr ? 'منتج متاح' : 'produits disponibles' }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('vendors.show', $vendor->slug) }}"
                                class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-indigo-600 hover:bg-indigo-600 hover:text-white font-bold text-xs transition shadow-2xs shrink-0">
                                {{ $isAr ? 'زيارة' : 'Visiter' }}
                            </a>
                        </div>
                    @empty
                        <div class="col-span-full py-8 text-center text-slate-400 text-sm">
                            Aucune boutique pour le moment.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- ============================================================== -->
        <!-- 11. 🚀 BANNER RECRUTEMENT VENDEURS (CTA)                       -->
        <!-- ============================================================== -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <section
                class="bg-gradient-to-r from-indigo-950 via-slate-900 to-sky-950 rounded-3xl p-8 sm:p-12 text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10 max-w-2xl space-y-4">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-amber-400 text-slate-950 text-xs font-black uppercase tracking-wider shadow-xs">
                        🚀 {{ __('messages.become_a_vendor') }}
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
                        {{ __('messages.vendor_banner_title') ?? 'Vous vendez des produits en Tunisie ? Développez votre chiffre d\'affaires sur AquawaSouk !' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-normal">
                        {{ __('messages.vendor_banner_desc') ?? 'Créez votre boutique gratuitement en 2 minutes. Bénéficiez de milliers de visiteurs quotidiens, d\'un tableau de bord moderne et de la gestion simplifiée des commandes.' }}
                    </p>
                    <div class="pt-2 flex flex-wrap gap-3">
                        <a href="{{ route('become-vendor') }}"
                            class="px-6 py-3 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl transition shadow-md flex items-center gap-1.5 active:scale-95">
                            <span>{{ __('messages.open_shop_free') ?? 'Ouvrir ma boutique gratuitement' }}</span>
                            <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                        </a>
                        <a href="{{ route('vendors.index') }}"
                            class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition">
                            {{ __('messages.our_trusted_vendors') }}
                        </a>
                    </div>
                </div>
            </section>
        </div>

        <!-- ============================================================== -->
        <!-- 12. 🕒 PRODUITS RÉCEMMENT CONSULTÉS (LocalStorage)             -->
        <!-- ============================================================== -->
        <div x-show="recentProducts && recentProducts.length > 0" x-cloak class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <section class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm shadow-xs">
                            🕒
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                                {{ $isAr ? 'المنتجات التي شاهدتها مؤخراً' : 'Récemment consultés' }}
                            </h2>
                            <p class="text-[11px] text-slate-500">
                                {{ $isAr ? 'استكمل تسوقك من حيث توقفت' : 'Reprenez votre shopping où vous vous étiez arrêté' }}
                            </p>
                        </div>
                    </div>
                    <button @click="clearRecent()"
                        class="text-xs font-semibold text-slate-400 hover:text-rose-500 transition cursor-pointer">
                        {{ $isAr ? 'مسح السجل' : 'Effacer l\'historique' }}
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                    <template x-for="p in recentProducts" :key="p.id">
                        <a :href="p.url"
                            class="group bg-slate-50 hover:bg-white p-2.5 rounded-2xl border border-slate-200/80 hover:border-sky-300 hover:shadow-md transition text-center flex flex-col justify-between">
                            <div
                                class="w-full aspect-square bg-white rounded-xl p-1 mb-2 flex items-center justify-center overflow-hidden">
                                <img :src="p.image" :alt="p.name"
                                    class="max-h-full max-w-full object-contain group-hover:scale-105 transition">
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-800 truncate" x-text="p.name"></p>
                                <p class="text-xs font-black text-sky-600 mt-0.5">
                                    <span x-text="p.price"></span> <span class="text-[9px]" x-text="p.currency"></span>
                                </p>
                            </div>
                        </a>
                    </template>
                </div>
            </section>
        </div>

        <!-- ============================================================== -->
        <!-- 13. QUICK VIEW MODAL (Aperçu Rapide Universel)                -->
        <!-- ============================================================== -->
        <div x-show="quickViewOpen" x-cloak class="relative z-50">
            <!-- Backdrop with smooth blur -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" @click="quickViewOpen = false">
            </div>

            <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-10 flex items-center justify-center">
                <div @click.away="quickViewOpen = false" x-show="quickViewOpen"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200 p-6 sm:p-8 overflow-hidden text-slate-900">

                    <!-- Close button -->
                    <button @click="quickViewOpen = false"
                        class="absolute top-4 {{ $isAr ? 'left-4' : 'right-4' }} p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <template x-if="quickViewProduct">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">

                            <!-- Product Image -->
                            <div
                                class="w-full aspect-square bg-slate-50 rounded-2xl p-4 flex items-center justify-center border border-slate-100 overflow-hidden">
                                <img :src="quickViewProduct.image" :alt="quickViewProduct.name"
                                    class="max-h-full max-w-full object-contain">
                            </div>

                            <!-- Product Info -->
                            <div class="space-y-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider"
                                            x-text="quickViewProduct.category"></span>
                                        <template x-if="quickViewProduct.vendor">
                                            <span class="text-xs font-bold text-sky-600">🏪 <span
                                                    x-text="quickViewProduct.vendor"></span></span>
                                        </template>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-black text-slate-900"
                                        x-text="quickViewProduct.name"></h3>

                                    <!-- Stars Rating -->
                                    <div class="flex items-center gap-1 text-amber-400 text-xs">
                                        <span>★★★★★</span>
                                        <span class="text-slate-400 font-bold text-[10px]"
                                            x-text="'(' + quickViewProduct.rating + ')'"></span>
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="flex items-baseline gap-2 pt-2 border-t border-slate-100">
                                    <span class="text-2xl font-black text-slate-900 tracking-tight">
                                        <span x-text="quickViewProduct.price"></span> <span class="text-xs font-bold"
                                            x-text="quickViewProduct.currency"></span>
                                    </span>
                                    <template x-if="quickViewProduct.old_price">
                                        <span class="text-xs text-slate-400 line-through">
                                            <span x-text="quickViewProduct.old_price"></span> <span
                                                x-text="quickViewProduct.currency"></span>
                                        </span>
                                    </template>
                                    <template x-if="quickViewProduct.discount > 0">
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-rose-600 text-white font-extrabold text-[10px]">
                                            -<span x-text="quickViewProduct.discount"></span>%
                                        </span>
                                    </template>
                                </div>

                                <!-- Reassurance point -->
                                <div
                                    class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-600 space-y-1">
                                    <p class="flex items-center gap-1.5 font-medium"><span>🚚</span> <span>Livraison 24-48h
                                            dans tous les gouvernorats</span></p>
                                    <p class="flex items-center gap-1.5 font-medium"><span>💵</span> <span>Paiement en
                                            espèces à la livraison (Sans carte)</span></p>
                                </div>

                                <!-- Quick Actions -->
                                <div class="space-y-2 pt-2">
                                    <a :href="quickViewProduct.direct_order_url"
                                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-xs sm:text-sm rounded-xl text-center shadow-md transition flex items-center justify-center gap-1.5">
                                        <span>⚡
                                            {{ $isAr ? 'طلب مباشر (دفع عند الاستلام)' : 'Commander Directement (Cash à la livraison)' }}</span>
                                    </a>

                                    <div class="grid grid-cols-2 gap-2">
                                        <form action="{{ route('cart.add') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" :value="quickViewProduct.id">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit"
                                                class="w-full py-2.5 bg-slate-100 hover:bg-sky-50 text-slate-800 hover:text-sky-600 font-bold text-xs rounded-xl border border-slate-200 transition flex items-center justify-center gap-1.5 cursor-pointer">
                                                <span>🛒</span> <span>{{ __('messages.add_to_cart') }}</span>
                                            </button>
                                        </form>

                                        <a :href="quickViewProduct.show_url"
                                            class="py-2.5 px-3 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 text-center transition flex items-center justify-center gap-1">
                                            <span>{{ __('messages.view_details') }}</span>
                                            <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </div>

    </div>

@endsection