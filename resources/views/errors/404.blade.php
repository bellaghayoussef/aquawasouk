@extends('blade.layout')

@section('title', __('messages.404_title') . ' — AquawaSouk')
@section('meta_description', __('messages.404_meta_desc'))

@section('content')
@php $isAr = app()->getLocale() === 'ar'; @endphp

<div class="min-h-[70vh] flex flex-col items-center justify-center px-4 py-20 text-center">

    {{-- Floating illustration --}}
    <div class="relative mb-8 select-none">
        {{-- Big blurred background blob --}}
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-72 h-72 rounded-full bg-sky-100 blur-3xl opacity-70"></div>
        </div>

        {{-- 404 number --}}
        <div class="relative">
            <span class="text-[9rem] sm:text-[12rem] font-black leading-none text-transparent bg-clip-text bg-gradient-to-br from-sky-400 via-sky-500 to-indigo-500 select-none drop-shadow-sm">
                404
            </span>
        </div>

        {{-- Floating emoji badge --}}
        <div class="absolute -top-4 -right-2 sm:-right-6 bg-amber-400 rounded-full w-12 h-12 sm:w-14 sm:h-14 flex items-center justify-center shadow-lg animate-bounce">
            <span class="text-2xl sm:text-3xl">📦</span>
        </div>
    </div>

    {{-- Heading --}}
    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-3">
        {{ __('messages.404_heading') }}
    </h1>

    {{-- Sub-text --}}
    <p class="text-sm sm:text-base text-slate-500 max-w-md mb-8 leading-relaxed">
        {{ __('messages.404_subtext') }}
    </p>

    {{-- CTA buttons --}}
    <div class="flex flex-wrap items-center justify-center gap-3">
        <a href="{{ route('home') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-sky-500 hover:bg-sky-600 text-white font-bold text-sm rounded-xl transition shadow-md">
            <svg class="w-4 h-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('messages.go_home') }}
        </a>

        <a href="{{ route('products.index') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm rounded-xl border border-slate-200 transition shadow-sm">
            🛍️ {{ __('messages.browse_products') }}
        </a>
    </div>

    {{-- Suggested popular categories --}}
    <div class="mt-14 w-full max-w-xl">
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">
            {{ __('messages.popular_departments') }}
        </p>
        <div class="flex flex-wrap justify-center gap-2">
            @foreach(\App\Models\Category::active()->whereNull('parent_id')->take(8)->get() as $cat)
                <a href="{{ route('products.index') }}?category={{ $cat->slug }}"
                   class="px-4 py-1.5 rounded-full bg-slate-100 hover:bg-sky-100 hover:text-sky-700 text-slate-600 text-xs font-semibold transition">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

</div>
@endsection
