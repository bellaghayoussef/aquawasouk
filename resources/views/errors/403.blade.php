@extends('blade.layout')

@section('title', __('messages.403_title') . ' — AquawaSouk')
@section('meta_description', __('messages.403_meta_desc'))

@section('content')
@php $isAr = app()->getLocale() === 'ar'; @endphp

<div class="min-h-[70vh] flex flex-col items-center justify-center px-4 py-20 text-center">

    <div class="relative mb-8 select-none">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-72 h-72 rounded-full bg-red-100 blur-3xl opacity-70"></div>
        </div>
        <div class="relative">
            <span class="text-[9rem] sm:text-[12rem] font-black leading-none text-transparent bg-clip-text bg-gradient-to-br from-red-400 via-rose-500 to-pink-500 select-none drop-shadow-sm">
                403
            </span>
        </div>
        <div class="absolute -top-4 -right-2 sm:-right-6 bg-red-100 rounded-full w-12 h-12 sm:w-14 sm:h-14 flex items-center justify-center shadow-lg animate-bounce">
            <span class="text-2xl sm:text-3xl">🔒</span>
        </div>
    </div>

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-3">
        {{ __('messages.403_heading') }}
    </h1>

    <p class="text-sm sm:text-base text-slate-500 max-w-md mb-8 leading-relaxed">
        {{ __('messages.403_subtext') }}
    </p>

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

</div>
@endsection

