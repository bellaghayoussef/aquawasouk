@extends('blade.layout')

@section('title', 'Mon Panier - AquawaSouk Tunisie')
@section('meta_description', 'Consultez votre panier sur AquawaSouk. Les commandes sont regroupées et expédiées séparément par chaque boutique partenaire.')

@section('content')

    <!-- Breadcrumb & Header -->
    <div class="bg-white border-b border-slate-200 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                <a href="{{ route('blade.home') }}" class="hover:text-sky-600">Accueil</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">Mon Panier</span>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Mon Panier d'Achats</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Les articles sont regroupés et expédiés directement par chaque boutique partenaire
                    </p>
                </div>
                @if($cart && $cart->items && $cart->items->count() > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-50 text-sky-700 text-xs font-bold w-fit">
                        📦 {{ $itemsByVendor->count() }} boutique(s) dans votre panier
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Cart Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($cart && $cart->items && $cart->items->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left: Vendor Groups (Séparé par vendeurs) -->
                <div class="lg:col-span-8 space-y-8">
                    @foreach($itemsByVendor as $vendorId => $vendorItems)
                        @php
                            $vendor = $vendorItems->first()->product ? $vendorItems->first()->product->vendor : null;
                            $vendorShopName = $vendor ? $vendor->shop_name : 'Boutique Partenaire';
                            $vendorCity = $vendor && $vendor->city ? $vendor->city : 'Tunisie';
                            $vendorShipping = $vendor && $vendor->shipping_price !== null ? (float)$vendor->shipping_price : 7.00;
                            $vendorSubtotal = $vendorItems->sum(fn($it) => $it->price * $it->quantity);
                            $vendorTotal = $vendorSubtotal + $vendorShipping;
                        @endphp

                        <!-- Single Vendor Batch Card -->
                        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden" x-data="{ checkoutOpen: false }">
                            
                            <!-- Vendor Header Ribbon -->
                            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 text-white p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-amber-400 shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h2 class="text-base font-extrabold text-white">{{ $vendorShopName }}</h2>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-emerald-400 bg-emerald-500/10 border border-emerald-400/20 px-2 py-0.5 rounded-full">
                                                ✓ Vendeur Vérifié
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-300 mt-0.5">
                                            {{ $vendorItems->count() }} article(s) • Expédition depuis {{ $vendorCity }} • Frais livraison : <strong>{{ number_format($vendorShipping, 2) }} DT</strong>
                                        </p>
                                    </div>
                                </div>

                                <button 
                                    @click="checkoutOpen = !checkoutOpen" 
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 shrink-0 cursor-pointer"
                                >
                                    <span>Commander ce lot</span>
                                    <span class="text-emerald-100">({{ number_format($vendorTotal, 2) }} DT)</span>
                                </button>
                            </div>

                            <!-- Items in this Vendor Group -->
                            <div class="divide-y divide-slate-100 p-6 space-y-5 sm:space-y-0">
                                @foreach($vendorItems as $item)
                                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex items-center gap-4">
                                            <!-- Image -->
                                            <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-2xl bg-slate-50 border border-slate-200/80 p-2 shrink-0 flex items-center justify-center overflow-hidden">
                                                @php
                                                    $itemImg = $item->product ? $item->product->image_url : asset('uploads/cover.jpeg');
                                                @endphp
                                                <img src="{{ $itemImg }}" alt="{{ $item->product->name ?? 'Produit' }}" onerror="this.onerror=null; this.src='{{ asset('logo/logo.png') }}';" class="max-h-full max-w-full object-contain">
                                            </div>

                                            <!-- Info -->
                                            <div>
                                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug">
                                                    @if($item->product)
                                                        <a href="{{ route('blade.products.show', $item->product->slug) }}" class="hover:text-sky-600 transition">
                                                            {{ $item->product->name }}
                                                        </a>
                                                    @else
                                                        Article indisponible
                                                    @endif
                                                </h3>
                                                <p class="text-xs text-slate-500 mt-1">
                                                    Prix unitaire : <strong class="text-slate-800">{{ number_format($item->price, 2) }} DT</strong>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Quantity and total -->
                                        <div class="flex items-center justify-between sm:justify-end gap-5 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                            <!-- Quantity form -->
                                            <div class="flex items-center gap-1.5 border border-slate-200 rounded-xl bg-slate-50 p-1">
                                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="quantity" value="{{ max(1, $item->quantity - 1) }}">
                                                    <button type="submit" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 flex items-center justify-center font-bold text-xs" {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                                        &minus;
                                                    </button>
                                                </form>
                                                <span class="font-extrabold text-xs px-2 text-slate-800">{{ $item->quantity }}</span>
                                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                                    <button type="submit" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 flex items-center justify-center font-bold text-xs">
                                                        &plus;
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Total Item Price -->
                                            <div class="text-right min-w-[80px]">
                                                <span class="text-sm font-black text-slate-900 block">
                                                    {{ number_format($item->price * $item->quantity, 2) }} DT
                                                </span>
                                            </div>

                                            <!-- Remove from cart -->
                                            <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition" title="Supprimer cet article">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Vendor Lot Summary Bar -->
                            <div class="bg-slate-50/80 border-t border-slate-100 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="space-y-0.5 text-xs text-slate-600">
                                    <p>Sous-total articles : <strong class="text-slate-800">{{ number_format($vendorSubtotal, 2) }} DT</strong></p>
                                    <p>Livraison {{ $vendorShopName }} : <strong class="text-slate-800">{{ number_format($vendorShipping, 2) }} DT</strong></p>
                                    <p class="text-sm font-extrabold text-slate-900 pt-1">
                                        Total pour ce vendeur : <span class="text-sky-600">{{ number_format($vendorTotal, 2) }} DT</span>
                                    </p>
                                </div>

                                <div class="w-full sm:w-auto flex items-center gap-2">
                                    <button 
                                        @click="checkoutOpen = !checkoutOpen" 
                                        type="button" 
                                        class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Valider ce lot maintenant</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Expandable Checkout Form for this specific Vendor -->
                            <div x-show="checkoutOpen" x-cloak class="p-6 bg-emerald-50/40 border-t border-emerald-100 transition-all">
                                <div class="max-w-xl mx-auto space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                            <span>⚡ Finaliser la commande auprès de {{ $vendorShopName }}</span>
                                        </h4>
                                        <button @click="checkoutOpen = false" class="text-slate-400 hover:text-slate-600 text-xs font-bold">Fermer &times;</button>
                                    </div>
                                    <p class="text-xs text-slate-600">
                                        Vous payez <strong class="text-emerald-700">{{ number_format($vendorTotal, 2) }} DT</strong> en espèces lors de la réception du colis.
                                    </p>

                                    <!-- If user is logged in, use standard checkout route; or direct checkout -->
                                    @if($vendor)
                                        <div class="pt-2">
                                            <a 
                                                href="{{ route('orders.checkout', $vendor->id) }}" 
                                                class="block w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs uppercase tracking-wider text-center shadow-lg shadow-emerald-600/30 transition transform active:scale-98"
                                            >
                                                Poursuivre la livraison pour ce vendeur &rarr;
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endforeach

                    <div class="pt-2">
                        <a href="{{ route('blade.products.index') }}" class="font-bold text-xs text-sky-600 hover:text-sky-700 flex items-center gap-1.5">
                            &larr; Continuer mes achats sur le catalogue
                        </a>
                    </div>
                </div>

                <!-- Right: Global Order Summary -->
                <div class="lg:col-span-4">
                    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-sm sticky top-28 space-y-5">
                        <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
                            <span>Récapitulatif Global</span>
                            <span class="text-xs font-bold text-sky-600">{{ $cart->items->sum('quantity') }} article(s)</span>
                        </h2>

                        @php
                            $globalSubtotal = $cart->items->sum(fn($it) => $it->price * $it->quantity);
                            // Sum shipping across all distinct vendors in the cart
                            $distinctVendorsCount = $itemsByVendor->count();
                            $totalEstimatedShipping = 0;
                            foreach($itemsByVendor as $vId => $vItems) {
                                $v = $vItems->first()->product ? $vItems->first()->product->vendor : null;
                                $totalEstimatedShipping += ($v && $v->shipping_price !== null ? (float)$v->shipping_price : 7.00);
                            }
                            $globalTotal = $globalSubtotal + $totalEstimatedShipping;
                        @endphp

                        <div class="space-y-3 text-xs text-slate-600">
                            <div class="flex items-center justify-between">
                                <span>Total articles :</span>
                                <span class="font-bold text-slate-900">{{ number_format($globalSubtotal, 2) }} DT</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span>Expéditions ({{ $distinctVendorsCount }} boutique{{ $distinctVendorsCount > 1 ? 's' : '' }}) :</span>
                                <span class="font-bold text-slate-900">{{ number_format($totalEstimatedShipping, 2) }} DT</span>
                            </div>

                            @php
                                $couponDiscount = 0.0;
                                if (session()->has('applied_coupon')) {
                                    $couponDiscount = (float)(session('applied_coupon')['discount'] ?? 0);
                                }
                                $finalGlobalTotal = max(0, $globalTotal - $couponDiscount);
                            @endphp

                            <!-- Promo Code / Coupon Section -->
                            <div class="border-t border-slate-100 pt-3 space-y-2">
                                <label class="text-[11px] font-bold text-slate-700 block">Code Promo / Bon de réduction</label>
                                @if(session()->has('applied_coupon'))
                                    @php $c = session('applied_coupon'); @endphp
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-emerald-800 uppercase tracking-wider">🏷️ {{ $c['code'] }}</span>
                                            <span class="text-[10px] bg-emerald-200/80 text-emerald-900 font-extrabold px-1.5 py-0.5 rounded-md">-{{ number_format($c['discount'], 2) }} DT</span>
                                        </div>
                                        <form action="{{ route('coupon.remove') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-black px-1.5 py-0.5 rounded hover:bg-rose-100 transition cursor-pointer" title="Retirer le code">&times;</button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('coupon.apply') }}" method="POST" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="code" placeholder="Code promo" required class="flex-1 px-3 py-2 uppercase text-xs rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 outline-hidden font-bold">
                                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-sky-600 text-white text-xs font-bold transition shrink-0 cursor-pointer">
                                            Appliquer
                                        </button>
                                    </form>
                                    @if(session('coupon_error'))
                                        <p class="text-[11px] text-rose-600 font-bold">⚠️ {{ session('coupon_error') }}</p>
                                    @endif
                                    @if(session('coupon_success'))
                                        <p class="text-[11px] text-emerald-600 font-bold">✓ {{ session('coupon_success') }}</p>
                                    @endif
                                @endif
                            </div>

                            @if($couponDiscount > 0)
                                <div class="flex items-center justify-between text-xs text-emerald-700 font-bold">
                                    <span>Remise coupon déduite :</span>
                                    <span>-{{ number_format($couponDiscount, 2) }} DT</span>
                                </div>
                            @endif

                            <div class="border-t border-slate-200 pt-3 flex items-baseline justify-between text-sm">
                                <span class="font-black text-slate-900">Total estimé :</span>
                                <span class="text-2xl font-black text-sky-600">{{ number_format($finalGlobalTotal, 2) }} <span class="text-xs">DT</span></span>
                            </div>
                        </div>

                        <!-- Marketplace explanation card -->
                        <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-xs text-amber-900 space-y-1.5">
                            <p class="font-bold flex items-center gap-1.5 text-amber-950">
                                <span>💡</span> Pourquoi plusieurs expéditions ?
                            </p>
                            <p class="text-[11px] text-amber-800 leading-relaxed">
                                Chaque vendeur prépare et expédie ses colis directement depuis sa boutique. Vous pouvez commander chaque lot individuellement pour être livré au plus vite.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 space-y-1">
                            <p class="font-bold flex items-center gap-1.5">
                                <span>💵</span> 100% Paiement à la livraison
                            </p>
                            <p class="text-[11px] text-emerald-700">Vous réglez chaque colis en espèces lors de son arrivée.</p>
                        </div>
                    </div>
                </div>

            </div>
        @else
            <!-- Empty Cart State -->
            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-xl mx-auto shadow-xs">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-sky-50 text-sky-600 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h2 class="text-xl font-extrabold text-slate-900">Votre panier est vide</h2>
                <p class="text-xs text-slate-500 mt-2 max-w-sm mx-auto">
                    Découvrez des milliers de produits de commerçants tunisiens et commencez votre shopping en toute sérénité.
                </p>
                <div class="mt-6">
                    <a href="{{ route('blade.products.index') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white text-xs font-bold rounded-2xl shadow-md transition">
                        <span>Explorer le catalogue des produits</span> &rarr;
                    </a>
                </div>
            </div>
        @endif
    </div>

@endsection
