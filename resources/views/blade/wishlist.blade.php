@extends('blade.layout')

@section('title', 'Mes Favoris - AquawaSouk Tunisie')
@section('meta_description', 'Retrouvez tous vos articles et produits coup de cœur enregistrés sur AquawaSouk.')

@section('content')
@php
    $isAr = app()->getLocale() === 'ar';
    $currency = $isAr ? 'د.ت' : 'DT';
@endphp

<div class="bg-slate-50/70 py-8 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200/80 mb-8">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-xs">
                        ❤️
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        {{ $isAr ? 'قائمة المفضلة' : 'Mes Favoris' }}
                    </h1>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    {{ $isAr ? 'المنتجات التي قمت بحفظها للشراء لاحقاً' : 'Les produits que vous avez enregistrés pour les retrouver facilement' }}
                </p>
            </div>

            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-sky-600 hover:border-sky-300 font-bold text-xs shadow-2xs transition">
                <span>←</span>
                <span>{{ $isAr ? 'مواصلة التسوق' : 'Continuer mes achats' }}</span>
            </a>
        </div>

        @if(!auth()->check())
            <!-- Guest Callout -->
            <div class="bg-white rounded-3xl p-8 sm:p-12 text-center max-w-xl mx-auto border border-slate-200/80 shadow-sm">
                <div class="w-16 h-16 rounded-3xl bg-sky-50 text-sky-600 flex items-center justify-center text-3xl mx-auto mb-4">
                    🔒
                </div>
                <h2 class="text-lg sm:text-xl font-black text-slate-900 mb-2">
                    {{ $isAr ? 'قم بتسجيل الدخول لعرض قائمة المفضلة' : 'Connectez-vous pour voir vos favoris' }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mb-6">
                    {{ $isAr ? 'احفظ منتجاتك المفضلة وتتبع عروضها عبر حسابك الشخصي.' : 'Sauvegardez vos coups de cœur et retrouvez-les sur tous vos appareils.' }}
                </p>
                <div class="flex items-center justify-center gap-3">
                    <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md transition">
                        {{ __('messages.login') }}
                    </a>
                    <a href="{{ route('register') }}" class="px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        {{ __('messages.register') }}
                    </a>
                </div>
            </div>
        @elseif($wishlists->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-3xl p-8 sm:p-16 text-center max-w-xl mx-auto border border-slate-200/80 shadow-sm">
                <div class="w-20 h-20 rounded-3xl bg-rose-50 text-rose-500 flex items-center justify-center text-4xl mx-auto mb-4">
                    🤍
                </div>
                <h2 class="text-lg sm:text-xl font-black text-slate-900 mb-2">
                    {{ $isAr ? 'قائمتك فارغة حتى الآن' : 'Votre liste de favoris est vide' }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mb-6">
                    {{ $isAr ? 'تصفح تشكيلة منتجاتنا المتنوعة واضغط على علامة القلب لحفظ أي منتج.' : 'Explorez notre catalogue et cliquez sur le cœur pour ajouter un produit à vos favoris.' }}
                </p>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-xs sm:text-sm shadow-md transition transform hover:-translate-y-0.5">
                    <span>✨</span>
                    <span>{{ $isAr ? 'اكتشف المنتجات' : 'Découvrir nos produits' }}</span>
                </a>
            </div>
        @else
            <!-- Wishlist Products Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($wishlists as $item)
                    @php
                        $product = $item->product;
                        if (!$product) continue;
                        $price = (float)($product->current_price ?? ($product->discount_price > 0 ? $product->discount_price : $product->price));
                        $oldPrice = (float)$product->price;
                        $hasDiscount = $product->discount_price > 0 && $product->discount_price < $product->price;
                    @endphp
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 hover:border-sky-300 hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden group relative" id="wishlist-item-{{ $product->id }}">
                        
                        <!-- Remove button -->
                        <button 
                            onclick="toggleWishlist({{ $product->id }})"
                            title="{{ $isAr ? 'حذف من المفضلة' : 'Retirer des favoris' }}"
                            class="absolute top-2.5 {{ $isAr ? 'left-2.5' : 'right-2.5' }} z-10 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </button>

                        <!-- Product Image -->
                        <a href="{{ route('products.show', $product->slug) }}" class="block relative aspect-square bg-slate-50 overflow-hidden">
                            <img 
                                src="{{ $product->image_url }}" 
                                alt="{{ $product->name }}" 
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                loading="lazy"
                            >
                            @if($hasDiscount)
                                <span class="absolute bottom-2 {{ $isAr ? 'right-2' : 'left-2' }} bg-rose-600 text-white text-[10px] font-black px-2 py-0.5 rounded-md shadow-xs">
                                    -{{ round((($oldPrice - $price) / $oldPrice) * 100) }}%
                                </span>
                            @endif
                        </a>

                        <!-- Content -->
                        <div class="p-3.5 sm:p-4 flex flex-col flex-1">
                            @if($product->vendor)
                                <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-slate-400 font-semibold mb-1 truncate">
                                    <span>🏪</span>
                                    <span class="truncate">{{ $product->vendor->shop_name }}</span>
                                </div>
                            @endif

                            <a href="{{ route('products.show', $product->slug) }}" class="font-bold text-xs sm:text-sm text-slate-800 hover:text-sky-600 line-clamp-2 transition mb-2">
                                {{ $product->name }}
                            </a>

                            <!-- Price -->
                            <div class="mt-auto pt-2 flex items-baseline gap-2">
                                <span class="text-sm sm:text-base font-black text-sky-700">
                                    {{ number_format($price, 2) }} {{ $currency }}
                                </span>
                                @if($hasDiscount)
                                    <span class="text-[11px] text-slate-400 line-through">
                                        {{ number_format($oldPrice, 2) }}
                                    </span>
                                @endif
                            </div>

                            <!-- Add to Cart Form -->
                            <form action="{{ route('cart.add') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-sky-600 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span>{{ $isAr ? 'أضف للسلة' : 'Ajouter au panier' }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
function toggleWishlist(productId) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(`/wishlist/toggle/${productId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && !data.favorited) {
            const el = document.getElementById(`wishlist-item-${productId}`);
            if (el) {
                el.classList.add('opacity-0', 'scale-95');
                setTimeout(() => el.remove(), 250);
            }
        }
    })
    .catch(err => console.error(err));
}
</script>
@endpush

@endsection
