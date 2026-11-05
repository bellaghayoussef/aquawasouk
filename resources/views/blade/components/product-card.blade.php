@props(['product'])

@php
    $imageUrl = $product->image_url ?? asset('uploads/cover.jpeg');
    $hasDiscount = $product->hasDiscount();
    $currentPrice = $product->current_price;
    $discountPercent = $product->discount_percentage;
@endphp

<div class="group relative bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 hover:border-sky-300 hover:shadow-2xl hover:shadow-sky-500/10 transition-all duration-300 flex flex-col justify-between overflow-hidden">
    
    <!-- TOP SECTION: Product Image & Badges -->
    <div class="relative w-full aspect-square bg-gradient-to-b from-slate-50/80 via-white to-slate-50/40 p-4 sm:p-5 flex items-center justify-center overflow-hidden">
        
        <!-- Clickable image link -->
        <a href="{{ route('blade.products.show', $product->slug) }}" class="w-full h-full flex items-center justify-center">
            <img 
                src="{{ $imageUrl }}" 
                alt="{{ $product->name }}" 
                loading="lazy"
                onerror="this.onerror=null; this.src='{{ asset('logo/logo.png') }}';"
                class="max-h-full max-w-full object-contain group-hover:scale-108 transition-transform duration-500 ease-out drop-shadow-xs"
            >
        </a>

        <!-- Top Badges Overlay -->
        <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10 pointer-events-none">
            @if($hasDiscount)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gradient-to-r from-rose-600 to-red-600 text-white font-extrabold text-[10px] sm:text-xs shadow-md shadow-rose-600/30">
                    <span>-{{ $discountPercent }}%</span>
                </span>
            @endif

            @if($product->is_featured)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-extrabold text-[9px] uppercase tracking-wider shadow-xs">
                    ★ {{ __('messages.featured') }}
                </span>
            @endif
        </div>

        <!-- Wishlist Heart Button -->
        <button 
            type="button" 
            onclick="toggleCardWishlist(event, {{ $product->id }})"
            class="absolute top-3 {{ app()->getLocale() === 'ar' ? 'left-3' : 'right-3' }} z-20 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs text-slate-400 hover:text-rose-500 hover:bg-white flex items-center justify-center shadow-xs transition cursor-pointer"
            id="card-wishlist-{{ $product->id }}"
            title="Favoris"
        >
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        </button>

        <!-- Floating Views Badge -->
        <div class="absolute bottom-3 {{ app()->getLocale() === 'ar' ? 'left-3' : 'right-3' }} z-10 pointer-events-none">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-900/60 backdrop-blur-md text-white text-[10px] font-medium shadow-xs">
                <svg class="w-3 h-3 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>{{ number_format($product->views ?? 0) }}</span>
            </span>
        </div>

        <!-- Quick View Overlay on Hover (Desktop) -->
        <div class="absolute inset-0 bg-slate-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none hidden sm:block"></div>
    </div>

    <!-- BOTTOM SECTION: Product Info & Actions -->
    <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between space-y-4">
        
        <div>
            <!-- Category & Vendor row -->
            <div class="flex items-center justify-between text-[11px] gap-2 mb-1">
                @if($product->category)
                    <span class="text-slate-400 font-medium uppercase tracking-wider truncate">
                        {{ $product->category->name }}
                    </span>
                @endif

                @if($product->vendor)
                    <a href="{{ route('blade.vendors.show', $product->vendor->slug) }}" class="text-sky-600 hover:text-sky-700 font-bold truncate flex items-center gap-1 shrink-0">
                        <span>🏪</span> {{ $product->vendor->shop_name }}
                    </a>
                @endif
            </div>

            <!-- Product Title -->
            <h3 class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-sky-600 transition-colors duration-200 line-clamp-2 leading-snug">
                <a href="{{ route('blade.products.show', $product->slug) }}">
                    {{ $product->name }}
                </a>
            </h3>

            <!-- Rating Stars (Dynamic) -->
            <div class="flex items-center gap-1.5 mt-1.5">
                <div class="flex text-amber-400 text-xs">
                    @php $stars = round($product->average_rating ?: 5.0); @endphp
                    @for($s = 1; $s <= 5; $s++)
                        <span>{{ $s <= $stars ? '★' : '☆' }}</span>
                    @endfor
                </div>
                <span class="text-[10px] text-slate-400 font-bold">({{ number_format($product->average_rating ?: 5.0, 1) }})</span>
            </div>
        </div>

        <!-- Price & Action Button -->
        <div class="pt-3 border-t border-slate-100 flex items-end justify-between gap-2">
            <div>
                @php
                    $currencyLabel = app()->getLocale() === 'ar' ? 'د.ت' : 'DT';
                @endphp
                @if($hasDiscount)
                    <span class="text-[11px] text-slate-400 line-through block leading-none">
                        {{ number_format($product->price, 2) }} {{ $currencyLabel }}
                    </span>
                    <span class="text-base sm:text-lg font-black text-rose-600 tracking-tight leading-tight">
                        {{ number_format($currentPrice, 2) }} <span class="text-xs font-bold">{{ $currencyLabel }}</span>
                    </span>
                @else
                    <span class="text-base sm:text-lg font-black text-slate-900 tracking-tight leading-tight">
                        {{ number_format($currentPrice, 2) }} <span class="text-xs font-bold">{{ $currencyLabel }}</span>
                    </span>
                @endif
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-1.5 shrink-0">
                <!-- Add to cart form directly -->
                <form action="{{ route('cart.add') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button 
                        type="submit" 
                        class="p-2 sm:px-3 sm:py-2 bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white rounded-xl text-xs font-bold transition-all duration-200 flex items-center gap-1.5 group/btn shadow-2xs hover:shadow-md cursor-pointer"
                        title="{{ __('messages.add_to_cart') }}"
                    >
                        <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span class="hidden md:inline">{{ app()->getLocale() === 'ar' ? 'أضف' : (app()->getLocale() === 'en' ? 'Add' : 'Ajouter') }}</span>
                    </button>
                </form>

                <a 
                    href="{{ route('blade.products.show', $product->slug) }}" 
                    class="p-2 sm:px-3 sm:py-2 bg-slate-900 hover:bg-sky-600 text-white rounded-xl text-xs font-bold transition-all duration-200 shadow-2xs hover:shadow-md cursor-pointer"
                    title="{{ __('messages.view_details') }}"
                >
                    {!! app()->getLocale() === 'ar' ? '&larr;' : '&rarr;' !!}
                </a>
            </div>
        </div>

    </div>
</div>
