@extends('blade.layout')

@section('title', __('messages.products') . ' - AquawaSouk Tunisie')
@section('meta_description', __('messages.discover_quality_products') . ' AquawaSouk.')

@section('content')

    @php
        $isAr = app()->getLocale() === 'ar';
        $activeCategory = request('category') ? $categories->firstWhere('slug', request('category')) : null;
        $hasActiveFilters = request()->hasAny(['search', 'category', 'min_price', 'max_price', 'sort']);
    @endphp

    <!-- Breadcrumb & Title Header -->
    <div class="bg-white border-b border-slate-200 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                <a href="{{ route('home') }}" class="hover:text-sky-600 transition">{{ __('messages.home') }}</a>
                <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                <span class="text-slate-700 font-semibold">{{ __('messages.products') }}</span>
                @if($activeCategory)
                    <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                    <span class="text-sky-600 font-bold">{{ $activeCategory->name }}</span>
                @endif
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    @if($activeCategory && $activeCategory->image_url)
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-slate-50 border border-slate-200 p-1.5 shrink-0 shadow-xs flex items-center justify-center">
                            <img src="{{ $activeCategory->image_url }}" alt="{{ $activeCategory->name }}" class="w-full h-full object-contain">
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            {{ $activeCategory ? $activeCategory->name : __('messages.all_products') }}
                        </h1>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $products->total() }} {{ __('messages.products') }} {{ __('messages.in_stock') }}
                        </p>
                    </div>
                </div>

                <!-- Mobile Filter Drawer Toggle Button -->
                <div class="lg:hidden" x-data>
                    <button type="button" @click="$dispatch('open-mobile-filters')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-900 text-white text-xs font-bold shadow-xs hover:bg-slate-800 transition cursor-pointer">
                        <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>{{ __('messages.filter') }}</span>
                        @if($hasActiveFilters)
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Catalog Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ mobileFiltersOpen: false }" @open-mobile-filters.window="mobileFiltersOpen = true">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Filters Sidebar (Desktop) -->
            <div class="hidden lg:block lg:col-span-1">
                <form action="{{ route('products.index') }}" method="GET" class="bg-white rounded-3xl border border-slate-200/90 p-5 space-y-6 shadow-xs sticky top-28">
                    
                    <!-- Search inside filters -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">{{ __('messages.search') }}</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_products') }}..." class="w-full pl-9 pr-3 rtl:pr-9 rtl:pl-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 focus:border-sky-500 outline-hidden bg-slate-50/50">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 rtl:left-auto rtl:right-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">{{ __('messages.categories') }}</label>
                        <div class="space-y-1.5 max-h-56 overflow-y-auto no-scrollbar pr-1">
                            <label class="flex items-center gap-2 text-xs cursor-pointer py-1.5 px-2 rounded-xl hover:bg-slate-50 transition text-slate-700 hover:text-sky-600">
                                <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()" class="text-sky-600 focus:ring-sky-500">
                                <span class="font-bold">{{ __('messages.all_categories') }}</span>
                            </label>
                            @foreach($categories as $category)
                                <label class="flex items-center gap-2 text-xs cursor-pointer py-1.5 px-2 rounded-xl hover:bg-slate-50 transition text-slate-700 hover:text-sky-600 {{ request('category') === $category->slug ? 'bg-sky-50 text-sky-700 font-bold' : '' }}">
                                    <input type="radio" name="category" value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'checked' : '' }} onchange="this.form.submit()" class="text-sky-600 focus:ring-sky-500">
                                    @if($category->image_url)
                                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-4 h-4 rounded-md object-contain shrink-0 border border-slate-200">
                                    @endif
                                    <span class="truncate">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">{{ __('messages.price') }} (DT)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full p-2 rounded-xl border border-slate-300 text-xs bg-slate-50/50">
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full p-2 rounded-xl border border-slate-300 text-xs bg-slate-50/50">
                        </div>
                    </div>

                    <!-- Sorting -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">{{ __('messages.sort_by') }}</label>
                        <select name="sort" onchange="this.form.submit()" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 focus:border-sky-500 outline-hidden bg-slate-50/80 text-slate-700">
                            <option value="latest" {{ request('sort') == 'latest' || !request('sort') ? 'selected' : '' }}>✨ {{ __('messages.new_arrivals') }}</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>🔥 {{ __('messages.popular') }}</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>📉 {{ __('messages.price_low_to_high') }}</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>📈 {{ __('messages.price_high_to_low') }}</option>
                        </select>
                    </div>

                    <!-- Submit & Reset Buttons -->
                    <div class="space-y-2 pt-2">
                        <button type="submit" class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            {{ __('messages.filter') }}
                        </button>
                        @if($hasActiveFilters)
                            <a href="{{ route('products.index') }}" class="block w-full py-2 text-center text-rose-500 hover:text-rose-700 text-xs font-bold transition">
                                {{ __('messages.clear_filters') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Mobile Filter Drawer -->
            <div x-show="mobileFiltersOpen" x-cloak class="relative z-50 lg:hidden">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="mobileFiltersOpen = false"></div>
                <div class="fixed inset-y-0 {{ $isAr ? 'left-0' : 'right-0' }} max-w-xs w-full bg-white p-6 shadow-2xl overflow-y-auto">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <h2 class="text-base font-black text-slate-900">{{ __('messages.filter') }}</h2>
                        <button type="button" @click="mobileFiltersOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
                    </div>
                    <form action="{{ route('products.index') }}" method="GET" class="mt-4 space-y-5">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_products') }}..." class="w-full p-2.5 rounded-xl border border-slate-300 text-xs">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('messages.categories') }}</label>
                            <select name="category" class="w-full p-2 rounded-xl border border-slate-300 text-xs">
                                <option value="">{{ __('messages.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('messages.sort_by') }}</label>
                            <select name="sort" class="w-full p-2 rounded-xl border border-slate-300 text-xs">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('messages.new_arrivals') }}</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>{{ __('messages.popular') }}</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>{{ __('messages.price_low_to_high') }}</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>{{ __('messages.price_high_to_low') }}</option>
                            </select>
                        </div>

                        <div class="pt-4 flex gap-2">
                            <button type="submit" class="flex-1 py-2.5 bg-sky-600 text-white rounded-xl text-xs font-bold">
                                {{ __('messages.filter') }}
                            </button>
                            <a href="{{ route('products.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold text-center">
                                ✕
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="lg:col-span-3 space-y-6">

                <!-- Top banner if available -->
                @if(isset($banners) && $banners->count() > 0)
                    @php $pb = $banners->first(); @endphp
                    <a href="{{ $pb->link_url ?: '#' }}" class="block group relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-xs border border-slate-200/80 hover:shadow-md transition">
                        <div class="w-full aspect-[21/9] sm:aspect-[24/7] max-h-48 bg-slate-100 overflow-hidden relative">
                            <img src="{{ $pb->image_url }}" alt="{{ $pb->title ?? 'Bannière' }}" class="w-full h-full object-cover object-center group-hover:scale-102 transition duration-500" loading="lazy">
                            @if($pb->title)
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent flex items-end p-4 sm:p-5">
                                    <span class="text-white text-sm sm:text-base font-black drop-shadow-xs">{{ $pb->title }}</span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endif

                @if($products->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-3.5 sm:gap-6">
                        @foreach($products as $product)
                            @include('blade.components.product-card', ['product' => $product])
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-10 flex justify-center">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-3xl border border-slate-200/90 p-12 text-center max-w-md mx-auto shadow-xs">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mb-4 text-3xl">
                            🔍
                        </div>
                        <h3 class="text-base font-bold text-slate-800">{{ __('messages.no_products_found') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ __('messages.try_adjusting_filters') }}</p>
                        <a href="{{ route('products.index') }}" class="inline-block mt-4 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            {{ __('messages.all_products') }}
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>

@endsection
