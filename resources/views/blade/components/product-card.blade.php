@props(['product'])

@php
    $imageUrl = $product->image_url ?? asset('uploads/cover.jpeg');
    $hasDiscount = $product->hasDiscount();
    $currentPrice = $product->current_price;
    $discountPercent = $product->discount_percentage;
    $currencyLabel = app()->getLocale() === 'ar' ? 'د.ت' : 'DT';
    $isOutOfStock = !is_null($product->stock) && $product->stock <= 0;
    $isWishlisted = false;
    if (auth()->check()) {
        $isWishlisted = auth()->user()->wishlists()->where('product_id', $product->id)->exists();
    }
@endphp

<div
    class="group relative bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 hover:border-emerald-500/40 hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden">

    <!-- 1. TOP: Product Image & Floating Badges -->
    <div
        class="relative w-full aspect-square bg-slate-50/70  flex items-center justify-center overflow-hidden border-b border-slate-100">

        <!-- Clickable image link -->
        <a href="{{ route('products.show', $product->slug) }}" class="w-full h-full flex items-center justify-center">
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy"
                onerror="this.onerror=null; this.src='{{ asset('logo/logo.png') }}';"
                class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300 ease-out">
        </a>

        <!-- Top Badges Overlay -->
        <div
            class="absolute top-2.5 {{ app()->getLocale() === 'ar' ? 'right-2.5' : 'left-2.5' }} flex flex-col gap-1 z-10 pointer-events-none">
            @if($hasDiscount)
                <span
                    class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-rose-600 text-white font-extrabold text-[10px] sm:text-xs shadow-xs">
                    -{{ $discountPercent }}%
                </span>
            @endif

            @if($product->is_featured)
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[9px] uppercase tracking-wider shadow-2xs">
                    ★ {{ __('messages.featured') }}
                </span>
            @endif

            @if($isOutOfStock)
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-800 text-white font-bold text-[9px] uppercase tracking-wider shadow-2xs">
                    {{ app()->getLocale() === 'ar' ? 'نفذ' : 'Épuisé' }}
                </span>
            @endif
        </div>

        <!-- Wishlist Button -->
        <button type="button" onclick="toggleCardWishlist(event, {{ $product->id }})"
            class="absolute top-2.5 {{ app()->getLocale() === 'ar' ? 'left-2.5' : 'right-2.5' }} z-20 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs {{ $isWishlisted ? 'text-rose-600 fill-current' : 'text-slate-400' }} hover:text-rose-600 hover:bg-white flex items-center justify-center shadow-xs transition cursor-pointer"
            id="card-wishlist-{{ $product->id }}" title="Favoris">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path
                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
            </svg>
        </button>

        <!-- Reassurance Cash On Delivery Micro Badge -->
        <div
            class="absolute bottom-2 {{ app()->getLocale() === 'ar' ? 'right-2' : 'left-2' }} z-10 pointer-events-none">
            <span
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/90 backdrop-blur-xs text-slate-600 text-[10px] font-semibold border border-slate-200/80 shadow-2xs">
                <span>💵</span>
                <span>{{ app()->getLocale() === 'ar' ? 'الدفع عند الاستلام' : 'Paiement à la livraison' }}</span>
            </span>
        </div>

        <!-- Quick View Trigger Button (Desktop Hover) -->
        <button 
            type="button"
            @click.prevent.stop="$dispatch('open-quick-view', {{ json_encode([
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $imageUrl,
                'price' => number_format($currentPrice, 2),
                'old_price' => $hasDiscount ? number_format($product->price, 2) : null,
                'discount' => $discountPercent,
                'currency' => $currencyLabel,
                'category' => $product->category?->name,
                'vendor' => $product->vendor?->shop_name,
                'rating' => $product->average_rating,
                'in_stock' => !$isOutOfStock,
                'show_url' => route('products.show', $product->slug),
                'direct_order_url' => route('products.show', $product->slug) . '#form-commande-directe',
            ]) }})"
            class="absolute inset-x-3 bottom-7 py-1.5 px-3 bg-white/95 hover:bg-white text-slate-900 text-xs font-bold rounded-xl shadow-md opacity-0 group-hover:opacity-100 transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 hidden sm:flex items-center justify-center gap-1.5 cursor-pointer z-20 backdrop-blur-xs border border-slate-100"
            title="{{ app()->getLocale() === 'ar' ? 'معاينة سريعة' : 'Aperçu rapide' }}"
        >
            <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span>{{ app()->getLocale() === 'ar' ? 'معاينة سريعة' : 'Aperçu rapide' }}</span>
        </button>
    </div>

    <!-- 2. BOTTOM: Product Info & Actions -->
    <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between space-y-3">

        <div>
            <!-- Category & Vendor row -->
            <div class="flex items-center justify-between text-[11px] gap-2 mb-1">
                @if($product->category)
                    <span class="text-slate-400 font-semibold uppercase tracking-wider truncate">
                        {{ $product->category->name }}
                    </span>
                @endif

                @if($product->vendor)
                    <a href="{{ route('vendors.show', $product->vendor->slug) }}"
                        class="text-sky-600 hover:text-sky-700 font-bold truncate flex items-center gap-1 shrink-0">
                        <span>🏪</span> {{ $product->vendor->shop_name }}
                    </a>
                @endif
            </div>

            <!-- Product Title -->
            <h3
                class="text-xs sm:text-sm font-bold text-slate-800 hover:text-emerald-700 transition-colors duration-200 line-clamp-2 leading-snug">
                <a href="{{ route('products.show', $product->slug) }}">
                    {{ $product->name }}
                </a>
            </h3>

            <!-- Rating Stars (Dynamic) -->
            <div class="flex items-center gap-1 mt-1.5">
                <div class="flex text-amber-400 text-xs">
                    @php $stars = round($product->average_rating ?: 5.0); @endphp
                    @for($s = 1; $s <= 5; $s++)
                        <span>{{ $s <= $stars ? '★' : '☆' }}</span>
                    @endfor
                </div>
                <span
                    class="text-[10px] text-slate-400 font-bold">({{ number_format($product->average_rating ?: 5.0, 1) }})</span>
            </div>
        </div>

        <!-- Price Display -->
        <div class="pt-2 border-t border-slate-100 flex items-baseline justify-between gap-2">
            <div>
                @if($hasDiscount)
                    <span class="text-[11px] text-slate-400 line-through block leading-none mb-0.5">
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

            <!-- Stock status indicator -->
            <span class="text-[10px] font-semibold {{ $isOutOfStock ? 'text-rose-500' : 'text-emerald-600' }}">
                {{ $isOutOfStock ? (app()->getLocale() === 'ar' ? 'غير متوفر' : 'Épuisé') : (app()->getLocale() === 'ar' ? 'متوفر' : 'En stock') }}
            </span>
        </div>

        <!-- Action Buttons: Commande Directe + Panier -->
        <div class="flex items-center gap-1.5 pt-1">
            @if($isOutOfStock)
                <button disabled
                    class="w-full py-2 px-3 bg-slate-100 text-slate-400 rounded-xl text-xs font-bold flex items-center justify-center gap-1 cursor-not-allowed">
                    <span>🚫</span>
                    <span>{{ app()->getLocale() === 'ar' ? 'غير متوفر حالياً' : 'Rupture de stock' }}</span>
                </button>
            @else
                <!-- Commande Directe Button -->
                <a href="{{ route('products.show', $product->slug) }}#form-commande-directe"
                    class="flex-1 py-2 sm:py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-black flex items-center justify-center gap-1.5 shadow-xs hover:shadow-md transition-all duration-200 cursor-pointer"
                    title="{{ __('messages.direct_order_title') }}">
                    <span class="text-amber-300">⚡</span>
                    <span>{{ __('messages.direct_order') }}</span>
                </a>

                <!-- Add to cart icon button with AJAX Toast -->
                <form action="{{ route('cart.add') }}" method="POST" class="inline shrink-0" @submit.prevent="addToCartAjax($event, {{ $product->id }})">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit"
                        class="p-2 sm:p-2.5 bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-600 border border-slate-200 hover:border-sky-300 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center shadow-2xs hover:shadow-xs active:scale-95 cursor-pointer"
                        title="{{ __('messages.add_to_cart') }}">
                        <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </button>
                </form>
            @endif
        </div>

    </div>
</div>