@extends('blade.layout')

@section('title', $product->name . ' - AquawaSouk Tunisie')
@section('meta_description', Str::limit(strip_tags($product->description ?? 'Achetez ' . $product->name . ' sur AquawaSouk au meilleur prix en Tunisie avec paiement à la livraison.'), 160))
@php
    $prodImage = null;
    if ($product->primaryImage && !empty($product->primaryImage->image_path)) {
        $prodImage = $product->primaryImage->image_path;
    } elseif ($product->images && $product->images->isNotEmpty()) {
        $prodImage = $product->images->first()->image_path;
    }
    $prodOgUrl = $prodImage
        ? (Str::startsWith($prodImage, ['http://', 'https://']) ? $prodImage : (Str::startsWith($prodImage, 'uploads/') ? asset($prodImage) : asset('uploads/' . $prodImage)))
        : asset('images/og-image.jpg');
@endphp
@section('og_image', $prodOgUrl)
@section('og_type', 'product')

@section('content')

    @php
        $isAr = app()->getLocale() === 'ar';
        $currency = $isAr ? 'د.ت' : 'DT';
        $unitPrice = (float)($product->current_price ?? ($product->discount_price && $product->discount_price < $product->price ? $product->discount_price : $product->price));
        $vendorShipping = (float)($product->vendor && $product->vendor->shipping_price !== null ? $product->vendor->shipping_price : 7.00);
        $discountPct = ($product->discount_price && $product->discount_price < $product->price && $product->price > 0)
            ? round((($product->price - $product->discount_price) / $product->price) * 100)
            : 0;
        
        $activeVariations = $product->variations ? $product->variations->where('status', 'active')->values() : collect();
        $hasVariations = $activeVariations->isNotEmpty();

        $availableColors = $activeVariations->pluck('color')->filter(fn($c) => filled($c))->unique()->values();
        $availableSizes = $activeVariations->pluck('size')->filter(fn($s) => filled($s))->unique()->values();

        $hasColors = $availableColors->isNotEmpty();
        $hasSizes = $availableSizes->isNotEmpty();

        $colorHexMap = [
            'noir' => '#111827', 'black' => '#111827', 'أسود' => '#111827',
            'blanc' => '#ffffff', 'white' => '#ffffff', 'أبيض' => '#ffffff',
            'rouge' => '#ef4444', 'red' => '#ef4444', 'أحمر' => '#ef4444',
            'bleu' => '#3b82f6', 'blue' => '#3b82f6', 'أزرق' => '#3b82f6',
            'vert' => '#22c55e', 'green' => '#22c55e', 'أخضر' => '#22c55e',
            'jaune' => '#eab308', 'yellow' => '#eab308', 'أصفر' => '#eab308',
            'orange' => '#f97316', 'برتقالي' => '#f97316',
            'rose' => '#ec4899', 'pink' => '#ec4899', 'وردي' => '#ec4899',
            'violet' => '#a855f7', 'purple' => '#a855f7', 'بنفسجي' => '#a855f7',
            'gris' => '#6b7280', 'gray' => '#6b7280', 'grey' => '#6b7280', 'رمادي' => '#6b7280',
            'marron' => '#92400e', 'brown' => '#92400e', 'بني' => '#92400e',
            'beige' => '#d4b896', 'بيج' => '#d4b896',
            'bleu marine' => '#1e3a8a', 'navy' => '#1e3a8a',
        ];

        $tunisianGovernorates = [
            'Tunis' => ['fr' => 'Tunis', 'ar' => 'تونس'],
            'Ariana' => ['fr' => 'Ariana', 'ar' => 'أريانة'],
            'Ben Arous' => ['fr' => 'Ben Arous', 'ar' => 'بن عروس'],
            'Manouba' => ['fr' => 'Manouba', 'ar' => 'منوبة'],
            'Nabeul' => ['fr' => 'Nabeul', 'ar' => 'نابل'],
            'Zaghouan' => ['fr' => 'Zaghouan', 'ar' => 'زغوان'],
            'Bizerte' => ['fr' => 'Bizerte', 'ar' => 'بنزرت'],
            'Béja' => ['fr' => 'Béja', 'ar' => 'باجة'],
            'Jendouba' => ['fr' => 'Jendouba', 'ar' => 'جندوبة'],
            'Le Kef' => ['fr' => 'Le Kef', 'ar' => 'الكاف'],
            'Siliana' => ['fr' => 'Siliana', 'ar' => 'سليانة'],
            'Sousse' => ['fr' => 'Sousse', 'ar' => 'سوسة'],
            'Monastir' => ['fr' => 'Monastir', 'ar' => 'المنستير'],
            'Mahdia' => ['fr' => 'Mahdia', 'ar' => 'المهدية'],
            'Sfax' => ['fr' => 'Sfax', 'ar' => 'صفاقس'],
            'Kairouan' => ['fr' => 'Kairouan', 'ar' => 'القيروان'],
            'Kasserine' => ['fr' => 'Kasserine', 'ar' => 'القصرين'],
            'Sidi Bouzid' => ['fr' => 'Sidi Bouzid', 'ar' => 'سيدي بوزيد'],
            'Gabès' => ['fr' => 'Gabès', 'ar' => 'قابس'],
            'Médenine' => ['fr' => 'Médenine', 'ar' => 'مدنين'],
            'Tataouine' => ['fr' => 'Tataouine', 'ar' => 'تطاوين'],
            'Gafsa' => ['fr' => 'Gafsa', 'ar' => 'قفصة'],
            'Tozeur' => ['fr' => 'Tozeur', 'ar' => 'توزر'],
            'Kébili' => ['fr' => 'Kébili', 'ar' => 'قبلي'],
        ];

        $productUrl = request()->fullUrl();
        $shareText = $product->name . ' - ' . number_format($unitPrice, 2) . ' ' . $currency . ' sur AquawaSouk Tunisie';
    @endphp

    <!-- BREADCRUMB NAVIGATION -->
    <div class="bg-white border-b border-slate-200/80 py-2.5 sm:py-3.5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="hidden sm:flex items-center gap-2 text-xs text-slate-400 overflow-x-auto no-scrollbar" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-sky-600 transition shrink-0">{{ __('messages.home') }}</a>
                <span class="text-slate-300 shrink-0">{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                <a href="{{ route('products.index') }}" class="hover:text-sky-600 transition shrink-0">{{ __('messages.products') }}</a>
                @if($product->category)
                    <span class="text-slate-300 shrink-0">{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-sky-600 transition shrink-0 inline-flex items-center gap-1.5">
                        @if($product->category->image_url)
                            <img src="{{ $product->category->image_url }}" alt="{{ $product->category->name }}" class="w-4 h-4 rounded-md object-contain shrink-0 border border-slate-200">
                        @endif
                        <span>{{ $product->category->name }}</span>
                    </a>
                @endif
                <span class="text-slate-300 shrink-0">{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                <span class="text-slate-700 font-bold truncate">{{ $product->name }}</span>
            </nav>
            <div class="flex sm:hidden items-center justify-between text-xs">
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 font-bold text-slate-600 hover:text-sky-600 transition">
                    <span>{!! $isAr ? '&rarr;' : '&larr;' !!}</span>
                    <span>{{ $isAr ? 'العودة للمنتجات' : 'Tous les produits' }}</span>
                </a>
                @if($product->category)
                    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="px-2.5 py-0.5 rounded-full bg-slate-100 text-[10px] font-bold text-slate-600 inline-flex items-center gap-1.5">
                        @if($product->category->image_url)
                            <img src="{{ $product->category->image_url }}" alt="{{ $product->category->name }}" class="w-3.5 h-3.5 rounded object-contain shrink-0">
                        @endif
                        <span>{{ $product->category->name }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- MAIN PRODUCT DETAIL CONTAINER (Alpine State) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8" x-data="{
        activeImage: '{{ $product->image_url }}',
        qty: 1,
        showMoreNotes: false,
        baseUnitPrice: {{ $unitPrice }},
        unitPrice: {{ $unitPrice }},
        shippingPrice: {{ $vendorShipping }},
        copied: false,
        activeTab: 'description',
        hasVariations: {{ $hasVariations ? 'true' : 'false' }},
        hasColors: {{ $hasColors ? 'true' : 'false' }},
        hasSizes: {{ $hasSizes ? 'true' : 'false' }},
        variations: {{ Js::from($activeVariations->map(fn($v) => [
            'id' => $v->id,
            'color' => $v->color,
            'size' => $v->size,
            'stock' => (int)$v->stock,
            'price' => $v->price ? (float)$v->price : null,
        ])) }},
        selectedColor: null,
        selectedSize: null,
        selectedVariation: null,
        stockCount: {{ (int)$product->stock }},
        isReady() {
            if (!this.hasVariations) return true;
            if (this.hasColors && !this.selectedColor) return false;
            if (this.hasSizes && !this.selectedSize) return false;
            return true;
        },
        selectColor(color) {
            this.selectedColor = (this.selectedColor === color ? null : color);
            this.updateVariation();
        },
        selectSize(size) {
            this.selectedSize = (this.selectedSize === size ? null : size);
            this.updateVariation();
        },
        updateVariation() {
            if (!this.hasVariations) return;
            const match = this.variations.find(v => {
                const colorOk = !this.hasColors || v.color === this.selectedColor;
                const sizeOk = !this.hasSizes || v.size === this.selectedSize;
                return colorOk && sizeOk;
            });
            this.selectedVariation = match || null;
            if (match) {
                if (match.price && match.price > 0) {
                    this.unitPrice = match.price;
                } else {
                    this.unitPrice = this.baseUnitPrice;
                }
                this.stockCount = match.stock;
            } else {
                this.unitPrice = this.baseUnitPrice;
                this.stockCount = {{ (int)$product->stock }};
            }
            if (this.qty > this.stockCount && this.stockCount > 0) {
                this.qty = 1;
            }
        }
    }">

        <!-- Flash alerts -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center gap-3 shadow-xs">
                <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-sm shrink-0">✓</span>
                <div>
                    <p class="font-extrabold text-sm">{{ $isAr ? 'تم تأكيد طلبك بنجاح !' : 'Commande confirmée avec succès !' }}</p>
                    <p class="text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs shadow-xs">
                <div class="flex items-center gap-2 font-extrabold text-rose-900 mb-1.5">
                    <span class="text-base">⚠️</span>
                    <span>{{ $isAr ? 'يرجى التثبت من البيانات التالية :' : 'Veuillez vérifier les informations suivantes :' }}</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-rose-700 text-[11px]">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 p-4 sm:p-8 lg:p-10 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12 items-start">
                
                <!-- ============================================== -->
                <!-- LEFT COLUMN: PRODUCT GALLERY & TRUST BADGES    -->
                <!-- ============================================== -->
                <div class="lg:col-span-6 lg:sticky lg:top-24 space-y-4 sm:space-y-5">
                    <!-- Main Stage Image Box -->
                    <div class="relative w-full aspect-square bg-slate-50/80 rounded-2xl sm:rounded-3xl border border-slate-200/80 overflow-hidden flex items-center justify-center p-4 sm:p-6 shadow-inner group">
                        <img 
                            :src="activeImage" 
                            alt="{{ $product->name }}" 
                            onerror="this.onerror=null; this.src='{{ asset('logo/logo.png') }}';" 
                            class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105"
                        >
                        
                        <!-- Discount Badge -->
                        @if($discountPct > 0)
                            <div class="absolute top-3 left-3 sm:top-4 sm:left-4 rtl:left-auto rtl:right-3 sm:rtl:right-4 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-full bg-gradient-to-r from-rose-600 to-pink-600 text-white font-black text-xs shadow-lg shadow-rose-600/30">
                                -{{ $discountPct }}% {{ __('messages.save') }}
                            </div>
                        @endif

                        <!-- Views Counter -->
                        <div class="absolute bottom-3 right-3 sm:bottom-4 sm:right-4 rtl:right-auto rtl:left-3 sm:rtl:left-4 px-2.5 py-1 sm:px-3 sm:py-1 rounded-full bg-white/90 backdrop-blur-md text-[10px] sm:text-[11px] font-bold text-slate-700 shadow-sm border border-slate-200/80 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>👁️ {{ $product->views ?? 0 }} {{ $isAr ? 'مشاهدة' : 'vues' }}</span>
                        </div>
                    </div>

                    <!-- Thumbnails Carousel / Strip -->
                    @php
                        $resolveImg = function($path) {
                            if (empty($path)) return null;
                            return (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
                                ? $path
                                : asset('uploads/' . ltrim($path, '/'));
                        };

                        $allImages = collect();
                        if ($product->primaryImage && !empty($product->primaryImage->image_path)) {
                            $allImages->push($resolveImg($product->primaryImage->image_path));
                        }
                        if ($product->images && count($product->images)) {
                            foreach($product->images as $extraImg) {
                                if (!empty($extraImg->image_path)) {
                                    $allImages->push($resolveImg($extraImg->image_path));
                                }
                            }
                        }
                        if ($product->image_url) {
                            $allImages->push($product->image_url);
                        }
                        $allImages = $allImages->filter()->unique()->values();
                    @endphp

                    @if($allImages->count() > 1)
                        <div class="flex items-center gap-2 sm:gap-3 overflow-x-auto no-scrollbar py-1">
                            @foreach($allImages as $idx => $imgSrc)
                                <button 
                                    type="button"
                                    style="max-width:fit-content;"
                                    @click="activeImage = '{{ $imgSrc }}'" 
                                    :class="activeImage === '{{ $imgSrc }}' ? 'ring-2 ring-sky-600 border-sky-600' : 'opacity-70 hover:opacity-100 border-slate-200'" 
                                    class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl border bg-slate-50 p-1.5 shrink-0 overflow-hidden transition-all duration-200 cursor-pointer shadow-xs"
                                >
                                    <img src="{{ $imgSrc }}" alt="{{ $product->name }} thumb {{ $idx + 1 }}" class="w-full h-full object-contain">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- Mobile Reassurance Strip -->
                    <div class="sm:hidden flex items-center justify-around py-2 px-3 rounded-xl bg-slate-50 border border-slate-200/70 text-[10px] font-bold text-slate-700">
                        <span class="flex items-center gap-1"><span>🚚</span> {{ $isAr ? '24-48 ساعة' : 'Express 24-48h' }}</span>
                        <span class="text-slate-300">•</span>
                        <span class="flex items-center gap-1"><span>💵</span> {{ $isAr ? 'الدفع نقداً' : 'Paiement cash' }}</span>
                        <span class="text-slate-300">•</span>
                        <span class="flex items-center gap-1"><span>🛡️</span> {{ $isAr ? 'منتج معتمد' : 'Vérifié' }}</span>
                    </div>

                    <!-- Gallery Trust / Reassurance Bar (Desktop) -->
                    <div class="hidden sm:grid grid-cols-3 gap-2.5 pt-2 text-center text-slate-600 text-xs">
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70">
                            <span class="text-lg block mb-0.5">🚚</span>
                            <p class="font-bold text-slate-900 text-[11px]">{{ $isAr ? 'شحن 24-48 ساعة' : 'Livraison Express' }}</p>
                            <p class="text-[10px] text-slate-500">{{ $isAr ? '24 ولاية' : '24 Gouvernorats' }}</p>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70">
                            <span class="text-lg block mb-0.5">💵</span>
                            <p class="font-bold text-slate-900 text-[11px]">{{ $isAr ? 'الدفع عند الاستلام' : 'Paiement à l\'arrivée' }}</p>
                            <p class="text-[10px] text-slate-500">{{ $isAr ? 'نقداً 100%' : 'En espèces 100%' }}</p>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70">
                            <span class="text-lg block mb-0.5">🛡️</span>
                            <p class="font-bold text-slate-900 text-[11px]">{{ $isAr ? 'منتج معتمد' : 'Produit Vérifié' }}</p>
                            <p class="text-[10px] text-slate-500">{{ $isAr ? 'مطابق للوصف' : 'Conforme garanti' }}</p>
                        </div>
                    </div>

                    <!-- Social Share Strip -->
                    <div class="hidden sm:flex pt-2 items-center justify-between border-t border-slate-100">
                        <span class="text-xs font-bold text-slate-500">{{ $isAr ? 'مشاركة المنتج :' : 'Partager ce produit :' }}</span>
                        <div class="flex items-center gap-2">
                            <!-- Facebook Share (Populates status with URL) -->
                            <a 
                                href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($productUrl) }}&quote={{ urlencode($shareText) }}" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#1877F2]/10 hover:bg-[#1877F2] text-[#1877F2] hover:text-white text-xs font-bold transition"
                                title="Partager sur Facebook"
                            >
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.592 0 9 1.583 9 4.615V8z"/></svg>
                                <span>Facebook</span>
                            </a>

                            <!-- WhatsApp Share -->
                            <a 
                                href="https://api.whatsapp.com/send?text={{ urlencode($shareText . ' ' . $productUrl) }}" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-600 text-emerald-600 hover:text-white text-xs font-bold transition"
                                title="Partager sur WhatsApp"
                            >
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>WhatsApp</span>
                            </a>

                            <!-- Copy Link -->
                            <button 
                                type="button"
                                @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => copied = false, 2500)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span x-show="!copied">{{ $isAr ? 'نسخ الرابط' : 'Copier' }}</span>
                                <span x-show="copied" x-cloak class="text-emerald-600">✓ {{ $isAr ? 'تم النسخ' : 'Copié !' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- RIGHT COLUMN: PRODUCT INFO & DIRECT ORDER FORM -->
                <!-- ============================================== -->
                <div class="lg:col-span-6 space-y-6">
                    
                    <!-- Vendor Chip -->
                    @if($product->vendor)
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400">{{ $isAr ? 'يباع ويشحن من قِبل :' : 'Vendu et expédié par :' }}</span>
                                <a href="{{ route('vendors.show', $product->vendor->slug) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-bold transition">
                                    <span>🏪 {{ $product->vendor->shop_name }}</span>
                                    <span class="text-[10px] text-emerald-600 bg-emerald-100/80 px-1.5 py-0.5 rounded-full">✓ {{ __('messages.verified_partner') }}</span>
                                </a>
                            </div>
                            <span class="text-xs text-slate-400">📍 {{ $product->vendor->city ?? 'Tunisie' }}</span>
                        </div>
                    @endif

                    <!-- Title & Stock Status -->
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-snug tracking-tight">
                            {{ $product->name }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-3 mt-2.5 text-xs text-slate-500">
                            @if($product->sku)
                                <span>{{ $isAr ? 'رمز المنتج :' : 'Réf :' }} <strong class="text-slate-700">{{ $product->sku }}</strong></span>
                                <span>•</span>
                            @endif

                            @if($product->stock > 0)
                                <span class="text-emerald-700 font-bold flex items-center gap-1.5 bg-emerald-50 border border-emerald-200/80 px-2.5 py-0.5 rounded-full">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>{{ __('messages.in_stock') }} ({{ $product->stock }} {{ $isAr ? 'قطعة متوفرة' : 'unités' }})</span>
                                </span>
                            @else
                                <span class="text-rose-700 font-bold flex items-center gap-1.5 bg-rose-50 border border-rose-200/80 px-2.5 py-0.5 rounded-full">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <span>{{ __('messages.out_of_stock') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Price Card -->
                    <div class="p-5 rounded-3xl bg-gradient-to-br from-slate-50 via-sky-50/30 to-indigo-50/30 border border-slate-200/80 flex flex-wrap items-baseline justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">
                                {{ __('messages.price') }} :
                            </span>
                            <div class="flex items-baseline gap-3">
                                @if($discountPct > 0)
                                    <span class="text-3xl sm:text-4xl font-black text-rose-600 tracking-tight" x-text="unitPrice.toFixed(2) + ' {{ $currency }}'">
                                        {{ number_format($product->discount_price, 2) }} <span class="text-base font-bold">{{ $currency }}</span>
                                    </span>
                                    <span class="text-base text-slate-400 line-through" x-show="!selectedVariation || !selectedVariation.price">
                                        {{ number_format($product->price, 2) }} {{ $currency }}
                                    </span>
                                    <span class="text-xs font-black text-emerald-700 bg-emerald-100 border border-emerald-200 px-2.5 py-1 rounded-full" x-show="!selectedVariation || !selectedVariation.price">
                                        -{{ number_format($product->price - $product->discount_price, 2) }} {{ $currency }}
                                    </span>
                                @else
                                    <span class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight" x-text="unitPrice.toFixed(2) + ' {{ $currency }}'">
                                        {{ number_format($product->price, 2) }} <span class="text-base font-bold">{{ $currency }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Shipping fee pill -->
                        <div class="text-right rtl:text-left">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-xs">
                                <span>🚚</span>
                                <span>{{ __('messages.shipping_fee') }} : <strong>{{ number_format($vendorShipping, 2) }} {{ $currency }}</strong></span>
                            </span>
                        </div>
                    </div>

                    <!-- Short Description Snippet -->
                    @if($product->description)
                        <div class="text-xs sm:text-sm text-slate-600 leading-relaxed bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                            {{ Str::limit(strip_tags($product->description), 200) }}
                        </div>
                    @endif

                    <!-- VARIATIONS (COLORS & SIZES) SELECTOR IF EXIST -->
                    @if($hasVariations)
                        <div class="p-5 rounded-3xl bg-slate-50/90 border-2 border-sky-500/30 shadow-xs space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                                <span class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                    <span>✨</span>
                                    <span>{{ $isAr ? 'خيارات المنتج المتوفرة' : 'Options & Déclinaisons' }}</span>
                                </span>
                                <span class="text-[11px] font-bold text-slate-500">
                                    {{ $activeVariations->count() }} {{ $isAr ? 'خيارات متوفرة' : 'variantes' }}
                                </span>
                            </div>

                            <!-- Color Choice -->
                            @if($hasColors)
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                            <span>🎨</span>
                                            <span>{{ $isAr ? 'اللون :' : 'Couleur :' }}</span>
                                            <span x-show="selectedColor" x-cloak class="text-sky-600 font-black" x-text="selectedColor"></span>
                                        </span>
                                        <span x-show="!selectedColor" class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                            {{ $isAr ? 'إجباري' : 'Obligatoire' }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex flex-wrap gap-2.5">
                                        @foreach($availableColors as $col)
                                            @php
                                                $hex = $colorHexMap[mb_strtolower($col)] ?? null;
                                                $isWhiteOrLight = in_array(mb_strtolower($col), ['blanc', 'white', 'أبيض', 'beige', 'بيج', 'jaune']);
                                            @endphp
                                            <button
                                                type="button"
                                                @click="selectColor('{{ addslashes($col) }}')"
                                                :class="selectedColor === '{{ addslashes($col) }}' ? 'ring-2 ring-sky-600 ring-offset-2 border-sky-600 scale-105 bg-sky-50 text-sky-900' : 'hover:scale-105 opacity-90 border-slate-300 bg-white text-slate-800'"
                                                class="relative inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border shadow-2xs transition-all duration-150 cursor-pointer text-xs font-bold"
                                                title="{{ $col }}"
                                            >
                                                @if($hex)
                                                    <span class="w-4 h-4 rounded-full border {{ $isWhiteOrLight ? 'border-slate-300' : 'border-black/10' }} shrink-0 shadow-2xs" style="background-color: {{ $hex }}"></span>
                                                @else
                                                    <span class="w-3.5 h-3.5 rounded-full bg-slate-400 shrink-0"></span>
                                                @endif
                                                <span>{{ $col }}</span>
                                                <span x-show="selectedColor === '{{ addslashes($col) }}'" x-cloak class="text-sky-600 text-xs font-black">✓</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Size Choice -->
                            @if($hasSizes)
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                            <span>📏</span>
                                            <span>{{ $isAr ? 'المقاس / الحجم :' : 'Taille / Pointure :' }}</span>
                                            <span x-show="selectedSize" x-cloak class="text-sky-600 font-black" x-text="selectedSize"></span>
                                        </span>
                                        <span x-show="!selectedSize" class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                            {{ $isAr ? 'إجباري' : 'Obligatoire' }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        @foreach($availableSizes as $sz)
                                            <button
                                                type="button"
                                                @click="selectSize('{{ addslashes($sz) }}')"
                                                :class="selectedSize === '{{ addslashes($sz) }}' ? 'bg-sky-600 text-white border-sky-600 shadow-md shadow-sky-600/20 scale-105' : 'bg-white text-slate-700 border-slate-300 hover:border-sky-500'"
                                                class="min-w-10 h-10 px-3 rounded-xl border text-xs font-black transition-all duration-150 cursor-pointer flex items-center justify-center shadow-xs"
                                            >
                                                {{ $sz }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Selected Variation feedback (Stock & Custom Price) -->
                            <div x-show="isReady() && selectedVariation" x-cloak class="pt-2 border-t border-slate-200/70 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div>
                                    <template x-if="selectedVariation && selectedVariation.stock > 0">
                                        <span class="text-emerald-700 font-bold flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>{{ $isAr ? 'متوفر بالمخزون' : 'En stock' }} (<span x-text="selectedVariation.stock"></span> {{ $isAr ? 'قطعة' : 'disponibles' }})</span>
                                        </span>
                                    </template>
                                    <template x-if="selectedVariation && selectedVariation.stock <= 0">
                                        <span class="text-rose-700 font-bold flex items-center gap-1.5 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-full">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            <span>{{ __('messages.out_of_stock') }}</span>
                                        </span>
                                    </template>
                                </div>
                                <template x-if="selectedVariation && selectedVariation.price">
                                    <span class="text-xs font-black text-sky-700 bg-sky-50 border border-sky-200 px-2.5 py-1 rounded-full">
                                        {{ $isAr ? 'سعر الخيار :' : 'Prix pour ce choix :' }} <span x-text="selectedVariation.price.toFixed(2) + ' {{ $currency }}'"></span>
                                    </span>
                                </template>
                            </div>

                            <!-- Warning if variations exist but not chosen -->
                            <div x-show="!isReady()" class="p-3 rounded-2xl bg-amber-50 border border-amber-200/90 text-amber-900 text-xs flex items-center gap-2">
                                <span class="text-base">⚠️</span>
                                <span class="font-bold">
                                    {{ $isAr ? 'يرجى اختيار اللون والمقاس المطلوبين قبل إتمام الطلب.' : 'Veuillez sélectionner vos options (couleur / taille) ci-dessus avant de commander.' }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <!-- ============================================== -->
                    <!-- FORMULAIRE DE COMMANDE DIRECTE (CASH ON DELIVERY) -->
                    <!-- ============================================== -->
                    <div id="form-commande-directe" class="rounded-2xl sm:rounded-3xl bg-white border-2 border-emerald-500/40 shadow-lg sm:shadow-xl overflow-hidden scroll-mt-20">
                        <!-- Top Banner -->
                        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 p-3.5 sm:p-5 text-white flex flex-wrap items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2.5 sm:gap-3">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-amber-400 text-slate-950 font-black text-base sm:text-xl flex items-center justify-center shadow-md shrink-0">
                                    ⚡
                                </div>
                                <div>
                                    <h3 class="text-sm sm:text-base font-black tracking-tight leading-snug">
                                        {{ __('messages.direct_order_title') }}
                                    </h3>
                                    <p class="text-[11px] sm:text-xs text-emerald-100 font-medium">
                                        {{ __('messages.direct_order_subtitle') }}
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-[11px] sm:text-xs font-bold border border-white/20">
                                <span>💵</span>
                                <span>{{ __('messages.cash_on_delivery_badge') }}</span>
                            </span>
                        </div>

                        <!-- Form Content -->
                        <form action="{{ route('orders.direct') }}" method="POST" class="p-4 sm:p-7 space-y-4 sm:space-y-5 bg-gradient-to-b from-slate-50/50 to-white">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" :value="qty">
                            <input type="hidden" name="color" :value="selectedColor">
                            <input type="hidden" name="size" :value="selectedSize">
                            <input type="hidden" name="variation_id" :value="selectedVariation ? selectedVariation.id : ''">

                            <!-- Quantity Stepper & Live Total Panel -->
                            <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-white border border-slate-200/90 shadow-xs flex items-center justify-between gap-3">
                                <!-- Stepper -->
                                <div>
                                    <label class="block text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                        {{ __('messages.quantity') }} :
                                    </label>
                                    <div class="inline-flex items-center rounded-xl border border-slate-300 bg-slate-50 overflow-hidden shadow-inner">
                                        <button 
                                            type="button" 
                                            @click="if (qty > 1) qty--" 
                                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 text-slate-700 hover:bg-slate-200 font-black text-sm sm:text-base transition cursor-pointer select-none"
                                            :disabled="qty <= 1 || stockCount <= 0"
                                        >
                                            &minus;
                                        </button>
                                        <input 
                                            type="number" 
                                            x-model.number="qty" 
                                            min="1" 
                                            :max="stockCount > 0 ? stockCount : 1" 
                                            :disabled="stockCount <= 0"
                                            class="w-12 sm:w-14 text-center border-0 bg-transparent py-1 text-sm font-extrabold text-slate-900 focus:ring-0"
                                        />
                                        <button 
                                            type="button" 
                                            @click="if (qty < stockCount) qty++" 
                                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 text-slate-700 hover:bg-slate-200 font-black text-sm sm:text-base transition cursor-pointer select-none"
                                            :disabled="qty >= stockCount || stockCount <= 0"
                                        >
                                            &plus;
                                        </button>
                                    </div>
                                </div>

                                <!-- Live Price Computed Box -->
                                <div class="text-right rtl:text-left">
                                    <div class="text-[11px] sm:text-xs text-slate-500">
                                        {{ __('messages.shipping_fee') }} : 
                                        <strong class="text-slate-800">{{ number_format($vendorShipping, 2) }} {{ $currency }}</strong>
                                    </div>
                                    <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                        {{ __('messages.total_to_pay') }} :
                                    </div>
                                    <div class="text-xl sm:text-3xl font-black text-emerald-600 tracking-tight" x-text="((unitPrice * qty) + shippingPrice).toFixed(2) + ' {{ $currency }}'">
                                        {{ number_format($unitPrice + $vendorShipping, 2) }} {{ $currency }}
                                    </div>
                                </div>
                            </div>

                            <!-- Inputs: Name & Phone -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        {{ __('messages.full_name') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 {{ $isAr ? 'right-0 pr-3.5' : 'left-0 pl-3.5' }} flex items-center pointer-events-none text-slate-400 text-sm">👤</span>
                                        <input 
                                            type="text" 
                                            id="direct_order_name"
                                            name="customer_name" 
                                            value="{{ old('customer_name', auth()->user()?->name ?? '') }}" 
                                            required 
                                            placeholder="{{ __('messages.full_name') }}" 
                                            class="w-full {{ $isAr ? 'pr-10 pl-3.5' : 'pl-10 pr-3.5' }} py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 placeholder-slate-400 text-xs font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-xs outline-hidden transition"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        {{ __('messages.phone_number') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 {{ $isAr ? 'right-0 pr-3.5' : 'left-0 pl-3.5' }} flex items-center pointer-events-none text-slate-400 text-sm">📞</span>
                                        <input 
                                            type="tel" 
                                            name="phone" 
                                            value="{{ old('phone', auth()->user()?->phone ?? '') }}" 
                                            required 
                                            placeholder="Ex: 98 123 456 / 20 123 456" 
                                            class="w-full {{ $isAr ? 'pr-10 pl-3.5' : 'pl-10 pr-3.5' }} py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 placeholder-slate-400 text-xs font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-xs outline-hidden transition"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Inputs: Governorate & Delivery Address -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        {{ __('messages.city_governorate') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 {{ $isAr ? 'right-0 pr-3.5' : 'left-0 pl-3.5' }} flex items-center pointer-events-none text-slate-400 text-sm">📍</span>
                                        <select 
                                            name="shipping_city" 
                                            required 
                                            class="w-full {{ $isAr ? 'pr-10 pl-3.5' : 'pl-10 pr-3.5' }} py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 text-xs font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-xs outline-hidden transition cursor-pointer"
                                        >
                                            <option value="" disabled {{ old('shipping_city') ? '' : 'selected' }}>
                                                {{ $isAr ? 'اختر الولاية' : 'Sélectionnez votre gouvernorat' }}
                                            </option>
                                            @foreach($tunisianGovernorates as $govKey => $govNames)
                                                <option value="{{ $govKey }}" {{ old('shipping_city') === $govKey ? 'selected' : '' }}>
                                                    {{ $isAr ? $govNames['ar'] : $govNames['fr'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        {{ __('messages.delivery_address') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 {{ $isAr ? 'right-0 pr-3.5' : 'left-0 pl-3.5' }} flex items-center pointer-events-none text-slate-400 text-sm">🏠</span>
                                        <input 
                                            type="text" 
                                            name="shipping_address" 
                                            value="{{ old('shipping_address') }}" 
                                            required 
                                            placeholder="{{ $isAr ? 'الشارع، الحي، رقم المنزل...' : 'Rue, Quartier, Bâtiment, N°...' }}" 
                                            class="w-full {{ $isAr ? 'pr-10 pl-3.5' : 'pl-10 pr-3.5' }} py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 placeholder-slate-400 text-xs font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-xs outline-hidden transition"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Optional Toggle: Postal Code & Notes -->
                            <div>
                                <button 
                                    type="button" 
                                    @click="showMoreNotes = !showMoreNotes" 
                                    class="text-xs text-slate-500 hover:text-emerald-700 font-bold flex items-center gap-1.5 py-0.5 cursor-pointer"
                                >
                                    <span x-text="showMoreNotes ? '▲' : '▼'" class="text-[9px]"></span>
                                    <span>{{ $isAr ? 'رمز بريدي أو ملاحظات للموزع (اختياري)' : 'Code postal ou remarques de livraison (optionnel)' }}</span>
                                </button>
                                <div x-show="showMoreNotes" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                            {{ __('messages.postal_code') }}
                                        </label>
                                        <input 
                                            type="text" 
                                            name="shipping_postal_code" 
                                            value="{{ old('shipping_postal_code') }}" 
                                            placeholder="Ex: 1000, 2080..." 
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-slate-900 placeholder-slate-400 text-xs font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-xs outline-hidden transition"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                            {{ __('messages.shipping_notes') }}
                                        </label>
                                        <input 
                                            type="text" 
                                            name="notes" 
                                            value="{{ old('notes') }}" 
                                            placeholder="{{ $isAr ? 'مثال: اتصل بي قبل الوصول...' : 'Ex: Appelez avant d\'arriver...' }}" 
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-slate-900 placeholder-slate-400 text-xs font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-xs outline-hidden transition"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method Notice (Cash on Delivery) -->
                            <div class="space-y-1">
                                <div class="p-2.5 sm:p-3.5 rounded-xl sm:rounded-2xl border border-emerald-500 bg-emerald-50/70 ring-1 ring-emerald-500/20 flex items-center gap-2.5 sm:gap-3">
                                    <input type="hidden" name="payment_method" value="cod">
                                    <span class="text-xl">💵</span>
                                    <div class="text-left rtl:text-right">
                                        <span class="block text-xs font-black text-slate-900">{{ $isAr ? 'الدفع عند الاستلام (نقداً)' : 'Paiement à la livraison (Espèces)' }}</span>
                                        <span class="block text-[10px] text-slate-600">{{ $isAr ? 'الدفع نقداً عند استلام الطرد' : 'Réglez directement le livreur à la réception de votre colis' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Master Submit CTA Button -->
                            <button 
                                type="submit" 
                                :disabled="!isReady() || (hasVariations && selectedVariation && selectedVariation.stock <= 0) || stockCount <= 0"
                                :class="(!isReady() || (hasVariations && selectedVariation && selectedVariation.stock <= 0) || stockCount <= 0) ? 'opacity-50 cursor-not-allowed' : 'hover:from-emerald-500 hover:to-teal-500 cursor-pointer'"
                                class="w-full py-3.5 sm:py-4 px-5 sm:px-6 rounded-xl sm:rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-600 text-white font-black text-xs sm:text-sm uppercase tracking-wide shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 active:scale-98 flex flex-col sm:flex-row items-center justify-center gap-1.5 sm:gap-2"
                            >
                                <span class="flex items-center gap-1.5 text-sm sm:text-base">
                                    <span>⚡</span>
                                    <span x-show="isReady() && stockCount > 0">{{ __('messages.confirm_direct_order') }}</span>
                                    <span x-show="!isReady()" x-cloak>{{ $isAr ? 'يرجى اختيار اللون / المقاس' : 'Veuillez choisir vos options' }}</span>
                                    <span x-show="isReady() && stockCount <= 0" x-cloak>{{ __('messages.out_of_stock') }}</span>
                                </span>
                                <span x-show="isReady() && stockCount > 0" class="text-emerald-100 text-[11px] sm:text-xs font-extrabold bg-emerald-700/60 px-2.5 py-0.5 rounded-full">
                                    {{ __('messages.total') }} : <span x-text="((unitPrice * qty) + shippingPrice).toFixed(2) + ' {{ $currency }}'"></span>
                                </span>
                            </button>

                            <!-- Micro reassurance below button -->
                            <div class="grid grid-cols-3 gap-1.5 sm:gap-2 pt-2.5 sm:pt-3 border-t border-slate-200/80 text-center text-[10px] sm:text-[11px] font-bold text-slate-600">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-emerald-600">🚚</span>
                                    <span>{{ __('messages.fast_delivery_badge') }}</span>
                                </div>
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-emerald-600">💵</span>
                                    <span>{{ __('messages.cash_on_delivery_badge') }}</span>
                                </div>
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-emerald-600">📞</span>
                                    <span>{{ __('messages.phone_confirmation_badge') }}</span>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Divider: Or Add to Cart -->
                    <div class="relative flex py-1 sm:py-2 items-center">
                        <div class="flex-grow border-t border-slate-200"></div>
                        <span class="flex-shrink mx-3 sm:mx-4 text-[11px] sm:text-xs uppercase tracking-wider text-slate-400 font-bold">
                            {{ __('messages.or_add_to_cart') }}
                        </span>
                        <div class="flex-grow border-t border-slate-200"></div>
                    </div>

                    <!-- Secondary Standard Add to Cart Form -->
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <form action="{{ route('cart.add') }}" method="POST" class="flex-1 w-full">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" :value="qty">
                            <input type="hidden" name="color" :value="selectedColor">
                            <input type="hidden" name="size" :value="selectedSize">
                            <input type="hidden" name="variation_id" :value="selectedVariation ? selectedVariation.id : ''">
                            <button 
                                type="submit" 
                                :disabled="!isReady() || (hasVariations && selectedVariation && selectedVariation.stock <= 0) || stockCount <= 0"
                                :class="(!isReady() || (hasVariations && selectedVariation && selectedVariation.stock <= 0) || stockCount <= 0) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-50 cursor-pointer'"
                                class="w-full py-3 sm:py-3.5 px-4 sm:px-6 rounded-xl sm:rounded-2xl bg-white text-slate-800 font-bold text-xs sm:text-sm border-2 border-slate-300 hover:border-slate-400 shadow-xs flex items-center justify-center gap-2 transition active:scale-98"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span>{{ __('messages.add_to_cart') }}</span>
                            </button>
                        </form>

                        <!-- Wishlist Toggle Button -->
                        <button 
                            type="button"
                            onclick="toggleProductWishlist({{ $product->id }})"
                            id="btn-wishlist-detail"
                            class="py-3 sm:py-3.5 px-3.5 sm:px-4 rounded-xl sm:rounded-2xl border-2 border-slate-200 hover:border-rose-300 hover:bg-rose-50 text-slate-700 font-bold text-xs flex items-center justify-center gap-1.5 transition cursor-pointer shadow-xs {{ ($isFavorited ?? false) ? 'bg-rose-50 border-rose-300 text-rose-600' : '' }}"
                            title="{{ ($isFavorited ?? false) ? 'Retirer des favoris' : 'Ajouter aux favoris' }}"
                        >
                            <span class="text-base" id="wishlist-icon">{{ ($isFavorited ?? false) ? '❤️' : '🤍' }}</span>
                            <span id="wishlist-text" class="hidden sm:inline">{{ ($isFavorited ?? false) ? ($isAr ? 'في المفضلة' : 'Favoris') : ($isAr ? 'حفظ' : 'Favoris') }}</span>
                        </button>
                    </div>

                </div>

            </div>
        </div>

        <!-- ============================================== -->
        <!-- DETAILS, SPECIFICATIONS & REVIEWS TABS SECTION -->
        <!-- ============================================== -->
        <div class="mt-8 sm:mt-12 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 p-4 sm:p-8 shadow-sm">
            <div class="flex items-center gap-1.5 sm:gap-6 border-b border-slate-200 pb-3 sm:pb-4 overflow-x-auto no-scrollbar">
                <button 
                    type="button"
                    @click="activeTab = 'description'" 
                    :class="activeTab === 'description' ? 'bg-sky-50 sm:bg-transparent text-sky-600 sm:border-sky-600 font-black' : 'text-slate-500 hover:text-slate-800 sm:border-transparent font-bold'" 
                    class="px-3 py-1.5 sm:px-0 sm:py-0 rounded-xl sm:rounded-none sm:pb-2 sm:border-b-2 text-xs sm:text-sm transition cursor-pointer whitespace-nowrap"
                >
                    {{ __('messages.description') }}
                </button>

                @if(!empty($product->specifications))
                    <button 
                        type="button"
                        @click="activeTab = 'specs'" 
                        :class="activeTab === 'specs' ? 'bg-sky-50 sm:bg-transparent text-sky-600 sm:border-sky-600 font-black' : 'text-slate-500 hover:text-slate-800 sm:border-transparent font-bold'" 
                        class="px-3 py-1.5 sm:px-0 sm:py-0 rounded-xl sm:rounded-none sm:pb-2 sm:border-b-2 text-xs sm:text-sm transition cursor-pointer whitespace-nowrap"
                    >
                        {{ $isAr ? 'المواصفات والخصائص' : 'Fiche Technique & Spécifications' }}
                    </button>
                @endif

                <button 
                    type="button"
                    @click="activeTab = 'reviews'" 
                    :class="activeTab === 'reviews' ? 'bg-sky-50 sm:bg-transparent text-sky-600 sm:border-sky-600 font-black' : 'text-slate-500 hover:text-slate-800 sm:border-transparent font-bold'" 
                    class="px-3 py-1.5 sm:px-0 sm:py-0 rounded-xl sm:rounded-none sm:pb-2 sm:border-b-2 text-xs sm:text-sm transition cursor-pointer whitespace-nowrap flex items-center gap-1.5"
                >
                    <span>{{ $isAr ? 'التقييمات والآراء' : 'Avis Clients' }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-sky-100 text-sky-800 font-extrabold">{{ $product->reviews_count }}</span>
                </button>

                @if($product->vendor)
                    <button 
                        type="button"
                        @click="activeTab = 'vendor'" 
                        :class="activeTab === 'vendor' ? 'bg-sky-50 sm:bg-transparent text-sky-600 sm:border-sky-600 font-black' : 'text-slate-500 hover:text-slate-800 sm:border-transparent font-bold'" 
                        class="px-3 py-1.5 sm:px-0 sm:py-0 rounded-xl sm:rounded-none sm:pb-2 sm:border-b-2 text-xs sm:text-sm transition cursor-pointer whitespace-nowrap"
                    >
                        {{ $isAr ? 'عن البائع والشحن' : 'À propos du Vendeur' }}
                    </button>
                @endif
            </div>

            <!-- Tab 1: Description -->
            <div x-show="activeTab === 'description'" class="pt-6 text-xs sm:text-sm text-slate-700 leading-relaxed space-y-3 prose prose-slate max-w-none [&_ul]:list-disc [&_ul]:ps-5 [&_ul]:my-2 [&_ul]:space-y-1.5 [&_ol]:list-decimal [&_ol]:ps-5 [&_ol]:my-2 [&_ol]:space-y-1.5 [&_li]:leading-relaxed [&_li>p]:inline [&_p]:mb-2 [&_strong]:text-slate-900 [&_strong]:font-bold">
                @if($product->description)
                    @php
                        $desc = $product->description;
                        if (str_contains($desc, '&lt;') && !str_contains($desc, '<')) {
                            $desc = html_entity_decode($desc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        }
                        $hasHtml = strip_tags($desc) !== $desc;
                    @endphp
                    @if($hasHtml)
                        {!! strip_tags($desc, '<p><br><b><strong><i><em><u><ul><ol><li><span><div><h1><h2><h3><h4><h5><h6><table><thead><tbody><tr><th><td><hr><a>') !!}
                    @else
                        {!! nl2br(e($desc)) !!}
                    @endif
                @else
                    <p class="text-slate-400 italic">
                        {{ $isAr ? 'لا يوجد وصف مفصل لهذا المنتج حالياً.' : 'Aucune description détaillée n\'est renseignée pour cet article.' }}
                    </p>
                @endif
            </div>

            <!-- Tab 2: Specifications -->
            @php
                $rawSpecs = $product->specifications;
                if (is_string($rawSpecs)) {
                    $specsArray = json_decode($rawSpecs, true) ?: [];
                } elseif (is_array($rawSpecs)) {
                    $specsArray = $rawSpecs;
                } else {
                    $specsArray = [];
                }
            @endphp
            @if(!empty($specsArray))
                <div x-show="activeTab === 'specs'" x-cloak class="pt-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        @foreach($specsArray as $key => $val)
                            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                                <span class="font-bold text-slate-600">{{ $key }}</span>
                                <span class="text-slate-900 font-semibold">{{ is_array($val) ? implode(', ', $val) : $val }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Tab 3: Customer Reviews -->
            <div x-show="activeTab === 'reviews'" x-cloak class="pt-6 space-y-8" x-data="{ reviewFormOpen: false, selectedStars: 5 }">
                
                <!-- Rating Score Summary Header -->
                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-5 text-center sm:text-left rtl:sm:text-right">
                        <div>
                            <span class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight">
                                {{ number_format($product->average_rating, 1) }}
                            </span>
                            <span class="text-sm font-bold text-slate-400">/ 5</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-1 text-amber-400 text-lg">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= round($product->average_rating) ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            <p class="text-xs text-slate-500 font-semibold mt-1">
                                {{ $isAr ? 'مبني على' : 'Basé sur' }} {{ $product->reviews_count }} {{ $isAr ? 'تقييم' : 'avis client(s)' }}
                            </p>
                        </div>
                    </div>

                    <button 
                        @click="reviewFormOpen = !reviewFormOpen" 
                        type="button"
                        class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-sky-600 text-white font-bold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer"
                    >
                        <span>✍️</span>
                        <span>{{ $isAr ? 'أضف تقييمك' : 'Donner mon avis' }}</span>
                    </button>
                </div>

                <!-- Review Submission Form (Expandable) -->
                <div x-show="reviewFormOpen" x-cloak class="p-6 sm:p-7 rounded-3xl bg-sky-50/50 border border-sky-100 shadow-sm transition">
                    @auth
                        <form action="{{ route('products.reviews.store', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 max-w-xl">
                            @csrf
                            <h4 class="text-sm font-black text-slate-900">
                                {{ $isAr ? 'شاركنا تجربتك مع هذا المنتج' : 'Votre avis compte pour notre communauté' }}
                            </h4>

                            <!-- Interactive Star Picker -->
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">
                                    {{ $isAr ? 'تقييمك الإجمالي :' : 'Votre note globale :' }}
                                </label>
                                <div class="flex items-center gap-2 text-2xl text-amber-400 cursor-pointer select-none">
                                    <template x-for="star in 5" :key="star">
                                        <button 
                                            type="button" 
                                            @click="selectedStars = star"
                                            class="hover:scale-125 transition"
                                        >
                                            <span x-text="star <= selectedStars ? '★' : '☆'"></span>
                                        </button>
                                    </template>
                                    <input type="hidden" name="rating" :value="selectedStars">
                                </div>
                            </div>

                            <!-- Title -->
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">
                                    {{ $isAr ? 'عنوان التعليق (اختياري) :' : 'Titre de votre avis (ex: Très satisfait) :' }}
                                </label>
                                <input 
                                    type="text" 
                                    name="title" 
                                    placeholder="{{ $isAr ? 'ملخص تجربتك...' : 'Résumez votre expérience en quelques mots' }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 outline-hidden bg-white"
                                >
                            </div>

                            <!-- Comment -->
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">
                                    {{ $isAr ? 'تفاصيل تعليقك :' : 'Votre commentaire détaillé :' }}
                                </label>
                                <textarea 
                                    name="comment" 
                                    rows="3" 
                                    required
                                    placeholder="{{ $isAr ? 'اكتب انطباعك عن الجودة والتوصيل...' : 'Parlez de la qualité, de la conformité du produit et des délais...' }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 outline-hidden bg-white"
                                ></textarea>
                            </div>

                            <!-- Photos upload -->
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">
                                    📷 {{ $isAr ? 'إضافة صور للمنتج المستلم (اختياري) :' : 'Ajouter des photos réelles (max 3 images) :' }}
                                </label>
                                <input 
                                    type="file" 
                                    name="images[]" 
                                    multiple 
                                    accept="image/*"
                                    class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-100 file:text-sky-700 hover:file:bg-sky-200"
                                >
                            </div>

                            @if($hasPurchased ?? false)
                                <div class="p-2.5 rounded-xl bg-emerald-100 text-emerald-800 text-[11px] font-bold flex items-center gap-1.5">
                                    <span>✓</span>
                                    <span>{{ $isAr ? 'تم التحقق من شرائك لهذا المنتج - سيظهر تعليقك بشارة "شراء مؤكد"' : 'Vous avez acheté ce produit. Votre avis portera le badge "Achat Vérifié".' }}</span>
                                </div>
                            @endif

                            <div class="flex items-center gap-3 pt-2">
                                <button type="submit" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                    {{ $isAr ? 'نشر التقييم الآن' : 'Publier mon avis' }}
                                </button>
                                <button type="button" @click="reviewFormOpen = false" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl transition">
                                    {{ __('messages.cancel') ?? 'Annuler' }}
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-4 space-y-3">
                            <p class="text-xs text-slate-600 font-bold">
                                {{ $isAr ? 'يرجى تسجيل الدخول لكتابة تعليق وتقييم هذا المنتج.' : 'Connectez-vous pour donner votre avis sur ce produit.' }}
                            </p>
                            <a href="{{ route('login') }}" class="inline-block px-5 py-2 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                {{ __('messages.login') }}
                            </a>
                        </div>
                    @endauth
                </div>

                <!-- Reviews List -->
                @if($product->reviews->isEmpty())
                    <div class="text-center py-8 text-slate-400">
                        <span class="text-3xl block mb-2">⭐</span>
                        <p class="text-xs sm:text-sm font-semibold">
                            {{ $isAr ? 'كن أول من يقيم هذا المنتج ويشارك تجربته مع المشترين !' : 'Soyez le premier à donner votre avis sur cet article !' }}
                        </p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($product->reviews as $rev)
                            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-3">
                                
                                <!-- Review Header -->
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-slate-900">{{ $rev->user ? $rev->user->name : 'Client AquawaSouk' }}</span>
                                            @if($rev->verified_purchase)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                                    <span>✓</span>
                                                    <span>{{ $isAr ? 'شراء مؤكد' : 'Achat Vérifié' }}</span>
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] text-slate-400 mt-0.5 block">{{ $rev->created_at->format('d/m/Y') }}</span>
                                    </div>

                                    <!-- Stars -->
                                    <div class="text-amber-400 text-sm">
                                        @for($s = 1; $s <= 5; $s++)
                                            <span>{{ $s <= $rev->rating ? '★' : '☆' }}</span>
                                        @endfor
                                    </div>
                                </div>

                                <!-- Title & Comment -->
                                @if($rev->title)
                                    <h5 class="text-xs font-black text-slate-800">{{ $rev->title }}</h5>
                                @endif
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $rev->comment }}</p>

                                <!-- Customer uploaded photos -->
                                @if(!empty($rev->images) && is_array($rev->images))
                                    <div class="flex items-center gap-2 pt-1">
                                        @foreach($rev->images as $img)
                                            <a href="{{ asset($img) }}" target="_blank" class="block w-14 h-14 rounded-xl overflow-hidden border border-slate-200 shadow-2xs hover:scale-105 transition">
                                                <img src="{{ asset($img) }}" alt="Photo avis" class="w-full h-full object-cover">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Vendor Response Box -->
                                @if(!empty($rev->vendor_reply))
                                    <div class="mt-3 p-3.5 rounded-xl bg-sky-50/80 border border-sky-100 text-xs text-sky-950 space-y-1">
                                        <p class="font-bold flex items-center gap-1.5 text-sky-900">
                                            <span>🏪</span>
                                            <span>{{ $isAr ? 'رد البائع :' : 'Réponse du Vendeur :' }}</span>
                                        </p>
                                        <p class="text-slate-700 leading-relaxed text-[11px]">{{ $rev->vendor_reply }}</p>
                                    </div>
                                @endif

                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

            <!-- Tab 4: Vendor Details -->
            @if($product->vendor)
                <div x-show="activeTab === 'vendor'" x-cloak class="pt-6 space-y-4 text-xs sm:text-sm text-slate-700">
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 p-1 flex items-center justify-center shrink-0 shadow-xs">
                                @if($product->vendor->logo)
                                    <img src="{{ (str_starts_with($product->vendor->logo, 'http://') || str_starts_with($product->vendor->logo, 'https://')) ? $product->vendor->logo : asset('uploads/' . $product->vendor->logo) }}" alt="{{ $product->vendor->shop_name }}" class="w-full h-full object-contain rounded-xl">
                                @else
                                    <span class="font-black text-xl text-sky-600">{{ strtoupper(substr($product->vendor->shop_name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-base font-extrabold text-slate-900">{{ $product->vendor->shop_name }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $product->vendor->description ?? __('messages.discover_quality_products') }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('vendors.show', $product->vendor->slug) }}" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition shadow-xs whitespace-nowrap">
                            {{ __('messages.visit_shop') }} {!! $isAr ? '&larr;' : '&rarr;' !!}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- ============================================== -->
        <!-- RELATED PRODUCTS SECTION                       -->
        <!-- ============================================== -->
        @if(isset($relatedProducts) && $relatedProducts->count())
            <div class="mt-16">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        {{ __('messages.related_products') }}
                    </h2>
                    <a href="{{ route('products.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1">
                        <span>{{ __('messages.view_all') }}</span>
                        <span>{!! $isAr ? '&larr;' : '&rarr;' !!}</span>
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($relatedProducts as $rel)
                        @include('blade.components.product-card', ['product' => $rel])
                    @endforeach
                </div>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- STICKY MOBILE FLOATING DIRECT ORDER BAR        -->
        <!-- ============================================== -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/90 px-3 py-2 shadow-2xl flex items-center justify-between gap-2.5">
            <!-- Home Shortcut -->
            <a href="{{ route('home') }}" class="p-2.5 rounded-xl border border-slate-200 text-slate-600 hover:text-sky-600 hover:bg-slate-50 transition shrink-0" title="{{ __('messages.home') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>

            <!-- Cart Shortcut -->
            <a href="{{ route('cart.index') }}" class="relative p-2.5 rounded-xl border border-slate-200 text-slate-600 hover:text-sky-600 hover:bg-slate-50 transition shrink-0" title="{{ __('messages.cart') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span class="absolute -top-1.5 -right-1.5 bg-gradient-to-r from-rose-500 to-red-600 text-white font-black text-[9px] w-4 h-4 rounded-full flex items-center justify-center ring-2 ring-white shadow-xs">
                    {{ $cartCount ?? 0 }}
                </span>
            </a>

            <!-- Total Price & Direct Order CTA -->
            <button 
                type="button" 
                @click="document.getElementById('form-commande-directe').scrollIntoView({ behavior: 'smooth' }); setTimeout(() => document.getElementById('direct_order_name')?.focus(), 350);"
                class="flex-1 py-2.5 px-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs uppercase tracking-wide shadow-md active:scale-95 flex items-center justify-between gap-2 cursor-pointer"
            >
                <span class="flex items-center gap-1.5 truncate">
                    <span>⚡</span>
                    <span>{{ __('messages.confirm_direct_order') }}</span>
                </span>
                <span class="text-white font-black text-xs bg-black/20 px-2 py-0.5 rounded-lg shrink-0" x-text="((unitPrice * qty) + shippingPrice).toFixed(2) + ' {{ $currency }}'">
                    {{ number_format($unitPrice + $vendorShipping, 2) }} {{ $currency }}
                </span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleProductWishlist(productId) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const btn = document.getElementById('btn-wishlist-detail');
    const icon = document.getElementById('wishlist-icon');
    const text = document.getElementById('wishlist-text');

    fetch(`/wishlist/toggle/${productId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    })
    .then(res => {
        if (res.status === 401) {
            window.location.href = "{{ route('login') }}";
            return;
        }
        return res.json();
    })
    .then(data => {
        if (data && data.success) {
            if (data.favorited) {
                btn?.classList.add('bg-rose-50', 'border-rose-300', 'text-rose-600');
                if (icon) icon.textContent = '❤️';
                if (text) text.textContent = "{{ $isAr ? 'في المفضلة' : 'Dans mes favoris' }}";
            } else {
                btn?.classList.remove('bg-rose-50', 'border-rose-300', 'text-rose-600');
                if (icon) icon.textContent = '🤍';
                if (text) text.textContent = "{{ $isAr ? 'حفظ' : 'Ajouter aux favoris' }}";
            }

            // Update header and mobile navbar counts if elements exist
            const headerCount = document.getElementById('header-wishlist-count');
            if (headerCount) {
                headerCount.textContent = data.count;
                if (data.count > 0) {
                    headerCount.classList.remove('hidden');
                } else {
                    headerCount.classList.add('hidden');
                }
            }
            const mobCount = document.getElementById('mobile-navbar-wishlist-count');
            if (mobCount) {
                mobCount.textContent = data.count;
                if (data.count > 0) {
                    mobCount.classList.remove('hidden');
                } else {
                    mobCount.classList.add('hidden');
                }
            }
        }
    })
    .catch(err => console.error(err));
}

// Track recently viewed products in localStorage for homepage recommendations
try {
    let recent = JSON.parse(localStorage.getItem('aquawa_recent_products') || '[]');
    const currentProd = {
        id: {{ $product->id }},
        name: @json($product->name),
        slug: @json($product->slug),
        image: @json($product->image_url),
        price: '{{ number_format($product->current_price, 2) }}',
        currency: '{{ $isAr ? 'د.ت' : 'DT' }}',
        url: '{{ route('products.show', $product->slug) }}'
    };
    recent = recent.filter(p => p.id !== currentProd.id);
    recent.unshift(currentProd);
    if (recent.length > 8) recent = recent.slice(0, 8);
    localStorage.setItem('aquawa_recent_products', JSON.stringify(recent));
} catch (e) {}
</script>
@endpush

@endsection
