@extends('blade.layout')

@section('title', $vendor->shop_name . ' - ' . __('messages.official_shops') . ' | AquawaSouk')
@section('meta_description', Str::limit(strip_tags($vendor->description ?? __('messages.discover_quality_products') . ' - ' . $vendor->shop_name), 160))

@section('content')

    @php
        $isAr = app()->getLocale() === 'ar';
        $currencySymbol = $isAr ? 'د.ت' : 'DT';
        $cleanPhone = $vendor->phone ? preg_replace('/[^0-9]/', '', $vendor->phone) : null;
        if ($cleanPhone && strlen($cleanPhone) === 8) {
            $cleanPhone = '216' . $cleanPhone;
        }
    @endphp

    <!-- STORE HERO COVER & HEADER -->
    <div class="relative bg-gradient-to-br from-slate-950 via-sky-950 to-indigo-950 text-white overflow-hidden">
        <!-- Optional Vendor Custom Banner or Ambient Glowing Mesh -->
        @if($vendor->banner)
            <div class="absolute inset-0 z-0">
                <img src="{{ asset('uploads/' . $vendor->banner) }}" alt="{{ $vendor->shop_name }} cover" class="w-full h-full object-cover opacity-25 filter blur-xs">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent"></div>
            </div>
        @else
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:24px_24px] opacity-10 pointer-events-none"></div>
        @endif

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-14 relative z-10">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-slate-300/80 mb-8" aria-label="Breadcrumb">
                <a href="{{ route('blade.home') }}" class="hover:text-sky-300 transition">{{ __('messages.home') }}</a>
                <span class="text-slate-500">{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                <a href="{{ route('blade.vendors.index') }}" class="hover:text-sky-300 transition">{{ __('messages.vendors') }}</a>
                <span class="text-slate-500">{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                <span class="text-white font-semibold truncate">{{ $vendor->shop_name }}</span>
            </nav>

            <!-- Store Identity Card -->
            <div class="flex flex-col lg:flex-row items-center lg:items-start gap-8" x-data="{ copied: false }">
                <!-- Large Elevated Logo Avatar -->
                <div class="relative shrink-0 group">
                    <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl bg-white p-2.5 shadow-2xl shadow-black/50 border-4 border-white/20 backdrop-blur-md overflow-hidden flex items-center justify-center transition-transform duration-300 group-hover:scale-105">
                        @if($vendor->logo)
                            <img src="{{ asset('uploads/' . $vendor->logo) }}" alt="{{ $vendor->shop_name }}" class="w-full h-full object-contain rounded-2xl">
                        @else
                            <div class="w-full h-full rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 flex items-center justify-center text-white font-black text-4xl sm:text-5xl shadow-inner">
                                {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <span class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-emerald-500 border-4 border-slate-950 flex items-center justify-center text-white text-xs font-bold shadow-lg" title="{{ __('messages.verified_partner') }}">
                        ✓
                    </span>
                </div>

                <!-- Store Info & Meta Details -->
                <div class="flex-1 text-center lg:text-left rtl:lg:text-right space-y-4">
                    <div class="flex flex-wrap items-center justify-center lg:justify-start rtl:lg:justify-start gap-2.5">
                        <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight drop-shadow-sm">
                            {{ $vendor->shop_name }}
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-xs font-bold backdrop-blur-md">
                            <svg class="w-3.5 h-3.5 fill-current text-emerald-400" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>{{ __('messages.verified_partner') }}</span>
                        </span>
                        @if($vendor->shipping_price !== null)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-sky-500/20 border border-sky-400/30 text-sky-200 text-xs font-bold backdrop-blur-md">
                                🚚 {{ __('messages.shipping_fee_from', ['price' => number_format($vendor->shipping_price, 2) . ' ' . $currencySymbol]) }}
                            </span>
                        @endif
                    </div>

                    <!-- Store Bio / Description -->
                    <p class="text-xs sm:text-sm text-slate-300 max-w-3xl leading-relaxed">
                        {{ $vendor->description ?? __('messages.discover_quality_products') . ' ' . $vendor->shop_name . '. ' . __('messages.reassurance_store_desc') }}
                    </p>

                    <!-- Trust Tags & Badges -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start rtl:lg:justify-start gap-3 pt-1 text-xs text-slate-300">
                        @if($vendor->city || $vendor->address)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10 border border-white/10">
                                📍 <strong>{{ $vendor->city ?? $vendor->address }}</strong>
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10 border border-white/10">
                            📦 <strong>{{ $products->total() }}</strong> {{ __('messages.store_products_count') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10 border border-white/10 text-emerald-300 font-semibold">
                            💵 {{ __('messages.cash_on_delivery') }}
                        </span>
                    </div>

                    <!-- Action CTAs (Call, WhatsApp, Share) -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start rtl:lg:justify-start gap-3 pt-3">
                        @if($vendor->phone)
                            <a href="tel:{{ $vendor->phone }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-sky-500/25 transition transform active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>{{ __('messages.call_store') }} ({{ $vendor->phone }})</span>
                            </a>

                            @if($cleanPhone)
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Bonjour, je vous contacte depuis AquawaSouk à propos de vos produits.') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/25 transition transform active:scale-95">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>{{ __('messages.whatsapp_chat') }}</span>
                                </a>
                            @endif
                        @endif

                        <!-- Share Button with Toast -->
                        <button 
                            @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => copied = false, 2500)"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 backdrop-blur-md transition cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            <span x-show="!copied">{{ __('messages.share_store') }}</span>
                            <span x-show="copied" x-cloak class="text-emerald-300 font-extrabold">✓ {{ __('messages.link_copied') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VALUE PROPOSITIONS STRIP -->
    <div class="bg-white border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center sm:text-left rtl:sm:text-right">
                <div class="flex items-center gap-3 justify-center sm:justify-start rtl:sm:justify-start p-2">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">{{ $products->total() }} {{ __('messages.store_products_count') }}</h4>
                        <p class="text-[11px] text-slate-500">{{ __('messages.in_stock') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 justify-center sm:justify-start rtl:sm:justify-start p-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">{{ __('messages.fast_delivery_badge') }}</h4>
                        <p class="text-[11px] text-slate-500">24-48h {{ __('messages.city_governorate') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 justify-center sm:justify-start rtl:sm:justify-start p-2">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">{{ __('messages.cash_on_delivery') }}</h4>
                        <p class="text-[11px] text-slate-500">{{ __('messages.total_to_pay') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 justify-center sm:justify-start rtl:sm:justify-start p-2">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">{{ __('messages.satisfaction_guaranteed') }}</h4>
                        <p class="text-[11px] text-slate-500">{{ __('messages.verified_partner') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN STORE PRODUCTS CONTENT -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        <!-- STORE FILTER & SEARCH CONTROL BAR -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs space-y-4">
            <form action="{{ route('blade.vendors.show', $vendor->slug) }}" method="GET" class="space-y-4">
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <!-- Search Input -->
                    <div class="relative flex-1 w-full">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="{{ __('messages.search_in_store') }}" 
                            class="w-full pl-10 pr-4 rtl:pr-10 rtl:pl-4 py-2.5 rounded-2xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden bg-slate-50/50"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 rtl:left-auto rtl:right-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <!-- Category Dropdown -->
                    @if(isset($vendorCategories) && $vendorCategories->count() > 0)
                        <div class="w-full md:w-56 shrink-0">
                            <select name="category" onchange="this.form.submit()" class="w-full p-2.5 rounded-2xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden bg-slate-50 font-medium">
                                <option value="">{{ __('messages.all_store_categories') }}</option>
                                @foreach($vendorCategories as $cat)
                                    <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Sort Dropdown -->
                    <div class="w-full md:w-48 shrink-0">
                        <select name="sort" onchange="this.form.submit()" class="w-full p-2.5 rounded-2xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden bg-slate-50 font-medium">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('messages.latest') }}</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>{{ __('messages.popular') }}</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>{{ __('messages.price_low_high') }}</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>{{ __('messages.price_high_low') }}</option>
                        </select>
                    </div>

                    <!-- Submit & Reset -->
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-2xl text-xs font-bold transition shadow-xs cursor-pointer">
                            {{ __('messages.search') }}
                        </button>
                        @if(request()->hasAny(['search', 'category', 'sort']))
                            <a href="{{ route('blade.vendors.show', $vendor->slug) }}" class="p-2.5 text-slate-500 hover:text-rose-600 rounded-2xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="{{ __('messages.reset_store_filters') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Category Pills (Quick Click) -->
                @if(isset($vendorCategories) && $vendorCategories->count() > 0)
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-1">
                        <a href="{{ route('blade.vendors.show', array_merge(['slug' => $vendor->slug], request()->except('category', 'page'))) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition {{ !request('category') ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            {{ __('messages.all_store_categories') }}
                        </a>
                        @foreach($vendorCategories as $vCat)
                            <a href="{{ route('blade.vendors.show', array_merge(['slug' => $vendor->slug, 'category' => $vCat->slug], request()->except('category', 'page'))) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition {{ request('category') === $vCat->slug ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                {{ $vCat->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </form>
        </div>

        <!-- PRODUCTS GRID SECTION -->
        <section>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>{{ __('messages.store_catalog') }}</span>
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-700">
                            {{ $products->total() }}
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('messages.discover_quality_products') }}
                    </p>
                </div>
            </div>

            @if($products->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($products as $product)
                        @include('blade.components.product-card', ['product' => $product])
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-xl mx-auto shadow-xs">
                    <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">{{ __('messages.no_store_products') }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ __('messages.try_adjusting_filters') }}</p>
                    <div class="mt-5">
                        <a href="{{ route('blade.vendors.show', $vendor->slug) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            <span>{{ __('messages.reset_store_filters') }}</span>
                        </a>
                    </div>
                </div>
            @endif
        </section>

        <!-- STORE REASSURANCE BANNER FOOTER -->
        <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-indigo-950 rounded-3xl p-8 sm:p-10 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10 max-w-3xl space-y-3">
                <span class="inline-block px-3 py-1 rounded-full bg-emerald-400 text-slate-950 text-xs font-black uppercase tracking-wider">
                    ✓ {{ __('messages.verified_partner') }}
                </span>
                <h3 class="text-xl sm:text-2xl font-black tracking-tight">
                    {{ __('messages.reassurance_store_title') }}
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    {{ __('messages.reassurance_store_desc') }}
                </p>
                <div class="pt-2 flex flex-wrap gap-3">
                    <a href="{{ route('blade.products.index') }}" class="px-5 py-2.5 bg-white text-slate-950 font-extrabold text-xs rounded-xl hover:bg-slate-100 transition shadow-md">
                        {{ __('messages.browse_products') }} {!! $isAr ? '&larr;' : '&rarr;' !!}
                    </a>
                    <a href="{{ route('blade.vendors.index') }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition">
                        {{ __('messages.our_trusted_vendors') }}
                    </a>
                </div>
            </div>
        </div>

    </div>

@endsection
