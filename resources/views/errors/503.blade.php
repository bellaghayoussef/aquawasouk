@extends('blade.layout')

@section('title', __('messages.503_title') . ' — AquawaSouk')
@section('meta_description', __('messages.503_meta_desc'))

@section('content')
@php $isAr = app()->getLocale() === 'ar'; @endphp

<div class="min-h-[70vh] flex flex-col items-center justify-center px-4 py-20 text-center">

    <div class="relative mb-8 select-none">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-72 h-72 rounded-full bg-indigo-100 blur-3xl opacity-70"></div>
        </div>
        <div class="relative">
            <span class="text-[9rem] sm:text-[12rem] font-black leading-none text-transparent bg-clip-text bg-gradient-to-br from-indigo-400 via-violet-500 to-purple-600 select-none drop-shadow-sm">
                503
            </span>
        </div>
        <div class="absolute -top-4 -right-2 sm:-right-6 bg-indigo-100 rounded-full w-12 h-12 sm:w-14 sm:h-14 flex items-center justify-center shadow-lg animate-bounce">
            <span class="text-2xl sm:text-3xl">🛠️</span>
        </div>
    </div>

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-3">
        {{ __('messages.503_heading') }}
    </h1>

    <p class="text-sm sm:text-base text-slate-500 max-w-md mb-8 leading-relaxed">
        {{ __('messages.503_subtext') }}
    </p>

    {{-- Maintenance progress indicator --}}
    <div class="w-full max-w-xs bg-slate-100 rounded-full h-2 mb-8 overflow-hidden">
        <div class="h-2 bg-gradient-to-r from-indigo-400 to-violet-500 rounded-full animate-pulse" style="width: 65%"></div>
    </div>

    <div class="flex flex-wrap items-center justify-center gap-3">
        <button onclick="window.location.reload()"
                class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-sm rounded-xl transition shadow-md">
            🔄 {{ __('messages.503_retry') }}
        </button>
    </div>

</div>
@endsection

