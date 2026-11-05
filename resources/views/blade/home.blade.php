@extends('blade.layout')

@section('title', 'AquawaSouk - Marketplace en Tunisie | Mode, High-Tech, Maison & Bricolage')
@section('meta_description', 'Découvrez des milliers de produits aux meilleurs prix en Tunisie. Livraison express dans tous les gouvernorats et paiement à la livraison.')

@section('content')

    @php
        $isAr = app()->getLocale() === 'ar';
        $currency = $isAr ? 'د.ت' : 'DT';
        $heroList = isset($heroProducts) && $heroProducts->count() ? $heroProducts : $featuredProducts->take(4);
    @endphp

    <!-- HERO SECTION / BANNER -->
    <div class="relative bg-gradient-to-br from-slate-900 via-sky-950 to-indigo-950 text-white overflow-hidden py-12 md:py-20">
        <!-- Background glowing orbs -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <!-- Left: Hero Text -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left rtl:lg:text-right">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-sky-300 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>{{ $isAr ? 'منصة السوق الرسمية في تونس' : 'Plateforme Officielle Marketplace Tunisie' }}</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
                        {{ $isAr ? 'اشتري وبِع بكل' : 'Achetez & Vendez en toute' }} <span class="bg-gradient-to-r from-sky-400 via-cyan-300 to-indigo-300 bg-clip-text text-transparent">{{ $isAr ? 'سهولة وأمان' : 'simplicité' }}</span>.
                    </h1>

                    <p class="text-slate-300 text-sm sm:text-base max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        {{ $isAr ? 'آلاف المنتجات من بائعين معتمدين: تكنولوجيا، كهرومنزلي، موضة ومستلزمات منزلية. توصيل سريع لكافة الولايات والدفع نقداً عند الاستلام.' : 'Des milliers de produits de vendeurs vérifiés : High-Tech, Électroménager, Mode, Beauté et Bricolage. Livraison rapide à domicile avec paiement en espèces à la réception.' }}
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start rtl:lg:justify-start gap-3 pt-2">
                        <a href="{{ route('products.index') }}" class="w-full sm:w-auto px-7 py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-bold text-sm rounded-2xl shadow-lg shadow-sky-500/30 hover:shadow-xl transition transform hover:-translate-y-0.5 text-center flex items-center justify-center gap-2">
                            <span>{{ __('messages.browse_products') }}</span>
                            <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                        </a>
                        <a href="{{ route('become-vendor') }}" class="w-full sm:w-auto px-7 py-3.5 bg-white/10 hover:bg-white/15 text-white border border-white/20 font-bold text-sm rounded-2xl backdrop-blur-md transition text-center">
                            {{ __('messages.become_a_vendor') }}
                        </a>
                    </div>

                    <!-- Trust Stats -->
                    <div class="grid grid-cols-3 gap-4 pt-6 border-t border-white/10 max-w-lg mx-auto lg:mx-0">
                        <div>
                            <p class="text-xl sm:text-2xl font-extrabold text-white">24</p>
                            <p class="text-[11px] text-slate-400">{{ $isAr ? 'ولاية مغطاة' : 'Gouvernorats livrés' }}</p>
                        </div>
                        <div>
                            <p class="text-xl sm:text-2xl font-extrabold text-white">100%</p>
                            <p class="text-[11px] text-slate-400">{{ $isAr ? 'دفع عند الاستلام' : 'Paiement à réception' }}</p>
                        </div>
                        <div>
                            <p class="text-xl sm:text-2xl font-extrabold text-white">4.8/5</p>
                            <p class="text-[11px] text-slate-400">{{ $isAr ? 'رضا الحرفاء' : 'Satisfaction clients' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Dynamic Hero Product Showcase / Spotlight (Non-statique) -->
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
                    <div class="relative bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-6 shadow-2xl text-white overflow-hidden">
                        
                        <!-- Header Bar with Badge and Switcher Dots -->
                        <div class="flex items-center justify-between pb-4 border-b border-white/10">
                            <span class="inline-flex items-center gap-1.5 text-xs uppercase font-extrabold tracking-wider text-amber-300">
                                <span>🔥</span>
                                <span>{{ $isAr ? 'عروض مميزة مختارة' : 'Offres du Moment' }}</span>
                            </span>

                            <!-- Indicator Dots -->
                            @if($heroList->count() > 1)
                                <div class="flex items-center gap-1.5">
                                    @foreach($heroList as $idx => $hp)
                                        <button 
                                            type="button"
                                            @click="activeIdx = {{ $idx }}" 
                                            :class="activeIdx === {{ $idx }} ? 'w-6 bg-sky-400' : 'w-2 bg-white/40 hover:bg-white/70'" 
                                            class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                            title="{{ $hp->name }}"
                                        ></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Dynamic Products Rotation -->
                        @foreach($heroList as $idx => $prod)
                            @php
                                $imgSrc = $prod->image_url;
                                $hasDiscount = $prod->discount_price && $prod->discount_price < $prod->price;
                                $pct = $hasDiscount && $prod->price > 0 ? round((($prod->price - $prod->discount_price) / $prod->price) * 100) : 0;
                                $displayPrice = $hasDiscount ? $prod->discount_price : $prod->price;
                            @endphp

                            <div 
                                x-show="activeIdx === {{ $idx }}" 
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                class="py-5 flex flex-col justify-between"
                            >
                                <!-- Top Vendor & Discount Tag -->
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    @if($prod->vendor)
                                        <a href="{{ route('vendors.show', $prod->vendor->slug) }}" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[11px] font-semibold transition">
                                            <span>🏪 {{ $prod->vendor->shop_name }}</span>
                                        </a>
                                    @endif

                                    @if($hasDiscount)
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-500 font-black text-[11px] text-white shadow-xs">
                                            -{{ $pct }}%
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/80 font-bold text-[11px] text-white">
                                            ★ {{ __('messages.featured') }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Product Image Box -->
                                <a href="{{ route('products.show', $prod->slug) }}" class="block">
                                    <div class="w-48 h-48 sm:w-52 sm:h-52 mx-auto rounded-2xl bg-white/5 border border-white/15 p-4 flex items-center justify-center overflow-hidden mb-4 group cursor-pointer shadow-inner">
                                        <img 
                                            src="{{ $imgSrc }}" 
                                            onerror="this.onerror=null; this.src='{{ asset('logo/logo.png') }}';" 
                                            alt="{{ $prod->name }}" 
                                            class="max-h-full max-w-full object-contain rounded-xl group-hover:scale-105 transition-transform duration-500"
                                        >
                                    </div>
                                </a>

                                <!-- Product Details -->
                                <div class="text-center space-y-1.5 px-2">
                                    <h3 class="text-base sm:text-lg font-black text-white truncate hover:text-sky-300 transition">
                                        <a href="{{ route('products.show', $prod->slug) }}">
                                            {{ $prod->name }}
                                        </a>
                                    </h3>

                                    <!-- Price Line -->
                                    <div class="flex items-baseline justify-center gap-2 pt-1">
                                        <span class="text-2xl sm:text-3xl font-black text-amber-300 tracking-tight">
                                            {{ number_format($displayPrice, 2) }} <span class="text-xs font-bold">{{ $currency }}</span>
                                        </span>
                                        @if($hasDiscount)
                                            <span class="text-sm text-slate-400 line-through">
                                                {{ number_format($prod->price, 2) }} {{ $currency }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($prod->category)
                                        <p class="text-[11px] text-sky-200">
                                            {{ $prod->category->name }}
                                        </p>
                                    @endif
                                </div>

                                <!-- CTA Action Buttons -->
                                <div class="pt-4 grid grid-cols-2 gap-2">
                                    <a href="{{ route('products.show', $prod->slug) }}" class="py-2.5 px-4 bg-white text-slate-900 font-black text-xs uppercase tracking-wider rounded-xl text-center hover:bg-slate-100 transition shadow-md flex items-center justify-center gap-1.5">
                                        <span>⚡ {{ $isAr ? 'طلب مباشر' : 'Commander' }}</span>
                                    </a>
                                    <a href="{{ route('products.index') }}" class="py-2.5 px-4 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl text-center border border-white/20 transition flex items-center justify-center">
                                        <span>{{ __('messages.view_all') }}</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN WRAPPER -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">

        <!-- RAYONS & CATÉGORIES -->
        <section>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.popular_departments') ?? 'Rayons Populaires' }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('messages.browse_by_theme') ?? 'Parcourez nos catégories par thématique' }}</p>
                </div>
                <a href="{{ route('products.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1">
                    <span>{{ __('messages.view_all') }}</span>
                    <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                @forelse($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="group bg-white rounded-2xl p-4 border border-slate-200/80 hover:border-sky-300 hover:shadow-lg transition duration-200 text-center flex flex-col items-center justify-between">
                        <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 group-hover:bg-sky-600 group-hover:text-white flex items-center justify-center transition duration-200 mb-3 shadow-xs">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 group-hover:text-sky-600 transition">{{ $category->name }}</h3>
                            <span class="text-[10px] text-slate-400 mt-0.5 inline-block">{{ $category->products_count ?? $category->products()->count() }} {{ $isAr ? 'منتج' : 'produits' }}</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-8 text-center text-slate-400 text-sm">
                        {{ $isAr ? 'لا توجد فئات حالياً.' : 'Aucune catégorie disponible pour le moment.' }}
                    </div>
                @endforelse
            </div>
        </section>

        <!-- PRODUITS VEDETTES -->
        <section>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.featured_products_title') ?? 'Produits Vedettes' }}</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('messages.most_wanted_this_week') ?? 'Les articles les plus demandés cette semaine' }}</p>
                </div>
                <a href="{{ route('products.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1">
                    <span>{{ __('messages.browse_products') }}</span>
                    <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @forelse($featuredProducts as $product)
                    @include('blade.components.product-card', ['product' => $product])
                @empty
                    <div class="col-span-full py-12 text-center text-slate-400">
                        {{ __('messages.no_products_found') }}
                    </div>
                @endforelse
            </div>
        </section>

        <!-- BANNER DEVENIR VENDEUR (CTA) -->
        <section class="bg-gradient-to-r from-indigo-900 via-sky-900 to-slate-900 rounded-3xl p-8 sm:p-12 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10 max-w-2xl space-y-4">
                <span class="inline-block px-3 py-1 rounded-full bg-amber-400 text-slate-950 text-xs font-extrabold uppercase tracking-wider">
                    {{ __('messages.become_a_vendor') }}
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                    {{ __('messages.vendor_banner_title') ?? 'Vous vendez des produits en Tunisie ? Développez votre chiffre d\'affaires sur AquawaSouk !' }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    {{ __('messages.vendor_banner_desc') ?? 'Créez votre boutique gratuitement en 2 minutes. Bénéficiez de milliers de visiteurs quotidiens, d\'un tableau de bord moderne et de la gestion simplifiée des commandes.' }}
                </p>
                <div class="pt-2 flex flex-wrap gap-3">
                    <a href="{{ route('become-vendor') }}" class="px-6 py-3 bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-md flex items-center gap-1.5">
                        <span>{{ __('messages.open_shop_free') ?? 'Ouvrir ma boutique gratuitement' }}</span>
                        <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                    </a>
                    <a href="{{ route('vendors.index') }}" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition">
                        {{ __('messages.our_trusted_vendors') }}
                    </a>
                </div>
            </div>
        </section>

        <!-- BOUTIQUES PARTENAIRES EN VEDETTE -->
        <section>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.official_shops') }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('messages.buy_direct_vendors') ?? 'Achetez directement auprès des meilleurs commerçants' }}</p>
                </div>
                <a href="{{ route('vendors.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1">
                    <span>{{ __('messages.all_shops') ?? 'Toutes les boutiques' }}</span>
                    <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @forelse($vendors as $vendor)
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 hover:shadow-lg transition flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 p-1 shrink-0 overflow-hidden flex items-center justify-center">
                            @if($vendor->logo)
                                <img src="{{ asset('uploads/' . $vendor->logo) }}" alt="{{ $vendor->shop_name }}" class="w-full h-full object-cover rounded-xl">
                            @else
                                <span class="font-extrabold text-xl text-sky-600">{{ strtoupper(substr($vendor->shop_name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-slate-900 truncate">{{ $vendor->shop_name }}</h3>
                            <p class="text-xs text-slate-400 truncate mt-0.5">{{ $vendor->description ?? 'Boutique vérifiée sur AquawaSouk' }}</p>
                            <a href="{{ route('vendors.show', $vendor->slug) }}" class="inline-block mt-2 text-xs font-bold text-sky-600 hover:text-sky-700">
                                <span>{{ __('messages.visit_shop') }}</span> {!! $isAr ? '&larr;' : '&rarr;' !!}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-6 text-center text-slate-400 text-xs">
                        {{ $isAr ? 'لا يوجد بائعون حالياً.' : 'Aucun vendeur répertorié.' }}
                    </div>
                @endforelse
            </div>
        </section>

    </div>

@endsection
