@extends('blade.layout')

@section('title', __('messages.our_trusted_vendors') . ' - AquawaSouk Tunisie')
@section('meta_description', __('messages.discover_quality_products') . ' AquawaSouk.')

@section('content')

    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <!-- Header & Breadcrumb -->
    <div class="bg-white border-b border-slate-200 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                <a href="{{ route('home') }}" class="hover:text-sky-600 transition">{{ __('messages.home') }}</a>
                <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                <span class="text-slate-700 font-semibold">{{ __('messages.vendors') }}</span>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ __('messages.our_trusted_vendors') }}</h1>
                    <p class="text-xs text-slate-500 mt-1">{{ __('messages.discover_quality_products') }}</p>
                </div>
                <a href="{{ route('become-vendor') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white text-xs font-bold rounded-2xl shadow-sm hover:shadow-md transition">
                    <span>🚀 {{ __('messages.become_a_vendor') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Search filter -->
        <div class="bg-white rounded-3xl border border-slate-200 p-4 shadow-xs">
            <form action="{{ route('vendors.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="{{ __('messages.search_shop_placeholder') ?? 'Rechercher une boutique par nom...' }}" 
                        class="w-full pl-10 pr-4 rtl:pr-10 rtl:pl-4 py-2.5 rounded-2xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden bg-slate-50/50"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 rtl:left-auto rtl:right-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-2xl text-xs font-bold transition shadow-xs">
                    {{ __('messages.filter') }}
                </button>
            </form>
        </div>

        <!-- Vendors Grid -->
        @if($vendors->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($vendors as $vendor)
                    <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col justify-between group hover:-translate-y-1">
                        <!-- Top banner background -->
                        <div class="h-28 relative overflow-hidden bg-gradient-to-r from-sky-600 via-sky-700 to-indigo-800">
                            @if($vendor->banner)
                                <img src="{{ asset('uploads/' . $vendor->banner) }}" alt="{{ $vendor->shop_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-black/30"></div>
                            @else
                                <div class="absolute inset-0 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:16px_16px] opacity-20"></div>
                            @endif

                            <!-- Verified Badge -->
                            <span class="absolute top-3 {{ $isAr ? 'left-3' : 'right-3' }} px-2.5 py-1 rounded-full bg-slate-950/80 backdrop-blur-md text-[10px] font-bold text-white border border-white/20 shadow-xs flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                {{ __('messages.verified_partner') }}
                            </span>
                        </div>

                        <!-- Logo & Details -->
                        <div class="px-5 pb-5 -mt-10 flex-1 flex flex-col justify-between relative z-10">
                            <div>
                                <!-- Logo Link -->
                                <a href="{{ route('vendors.show', $vendor->slug) }}" class="inline-block">
                                    <div class="w-20 h-20 rounded-2xl bg-white border-4 border-white shadow-lg p-1 overflow-hidden flex items-center justify-center mb-3 shrink-0 group-hover:scale-105 transition-transform duration-300">
                                        @if($vendor->logo)
                                            <img src="{{ asset('uploads/' . $vendor->logo) }}" alt="{{ $vendor->shop_name }}" class="w-full h-full object-contain rounded-xl">
                                        @else
                                            <div class="w-full h-full rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 flex items-center justify-center text-white font-black text-2xl shadow-inner">
                                                {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </a>

                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('vendors.show', $vendor->slug) }}" class="text-base font-extrabold text-slate-900 group-hover:text-sky-600 transition truncate">
                                        {{ $vendor->shop_name }}
                                    </a>
                                    <span class="text-sky-500 shrink-0" title="{{ __('messages.verified_partner') }}">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    </span>
                                </div>

                                @if($vendor->city)
                                    <div class="flex items-center gap-1 text-[11px] text-slate-400 mt-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="truncate">{{ $vendor->city }}</span>
                                    </div>
                                @endif

                                <p class="text-xs text-slate-500 line-clamp-2 mt-2 leading-relaxed">
                                    {{ $vendor->description ?? __('messages.discover_quality_products') . ' ' . $vendor->shop_name }}
                                </p>
                            </div>

                            <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-slate-500">
                                    📦 {{ $vendor->products_count ?? $vendor->products()->count() }} {{ __('messages.store_products_count') }}
                                </span>
                                <a href="{{ route('vendors.show', $vendor->slug) }}" class="px-3.5 py-1.5 bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                                    <span>{{ __('messages.visit_shop') }}</span>
                                    <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex justify-center">
                {{ $vendors->links() }}
            </div>
        @else
            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-md mx-auto shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto mb-4 text-3xl">🏪</div>
                <h3 class="text-base font-bold text-slate-800">{{ __('messages.no_products_found') }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ __('messages.try_adjusting_filters') }}</p>
                <a href="{{ route('vendors.index') }}" class="inline-block mt-4 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                    {{ __('messages.all_shops') }}
                </a>
            </div>
        @endif

    </div>

@endsection
