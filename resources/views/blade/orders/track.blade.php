@extends('blade.layout')

@section('title', 'Suivi de Commande en Direct - AquawaSouk Tunisie')
@section('meta_description', 'Suivez l\'état d\'avancement de votre commande AquawaSouk en direct avec notre système de suivi étape par étape.')

@section('content')
@php
    $isAr = app()->getLocale() === 'ar';
    $currency = $isAr ? 'د.ت' : 'DT';

    // Tracking Stepper logic
    $statusSteps = [
        'pending' => 1,
        'processing' => 2,
        'shipped' => 3,
        'out_for_delivery' => 4,
        'delivered' => 5,
        'cancelled' => 0,
    ];

    $currentStep = $order ? ($statusSteps[$order->status] ?? 1) : 0;
    $isCancelled = $order && $order->status === 'cancelled';
@endphp

<div class="bg-slate-50/70 py-8 sm:py-12 min-h-[75vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Page Header -->
        <div class="text-center max-w-xl mx-auto mb-8 sm:mb-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-sky-100 text-sky-700 uppercase tracking-wider mb-2">
                🚚 {{ $isAr ? 'تتبع فوري ومباشر' : 'Suivi en Temps Réel' }}
            </span>
            <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">
                {{ $isAr ? 'أين طلبيتي الآن؟' : 'Suivre ma commande' }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-2">
                {{ $isAr ? 'أدخل رقم طلبك ورقم هاتفك للاطلاع على مسار التوصيل خطوة بخطوة' : 'Entrez votre numéro de commande et votre numéro de téléphone pour vérifier l\'état de livraison.' }}
            </p>
        </div>

        <!-- Search Form Card -->
        <div class="bg-white rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-md mb-8">
            <form action="{{ route('orders.track') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="w-full sm:flex-1 relative">
                    <span class="absolute {{ $isAr ? 'right-3.5' : 'left-3.5' }} top-1/2 -translate-y-1/2 text-slate-400 text-sm">#</span>
                    <input 
                        type="text" 
                        name="order" 
                        required
                        value="{{ request('order', $orderNumber ?? '') }}"
                        placeholder="{{ $isAr ? 'رقم الطلب (مثال: ORD-XXXXXX)' : 'N° de commande (ex: ORD-XXXXX)' }}"
                        class="w-full {{ $isAr ? 'pr-9 pl-4' : 'pl-9 pr-4' }} py-3 rounded-2xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 outline-hidden transition"
                    >
                </div>
                <div class="w-full sm:flex-1 relative">
                    <span class="absolute {{ $isAr ? 'right-3.5' : 'left-3.5' }} top-1/2 -translate-y-1/2 text-slate-400 text-sm">📞</span>
                    <input 
                        type="text" 
                        name="phone" 
                        value="{{ request('phone', $phone ?? '') }}"
                        placeholder="{{ $isAr ? 'رقم الهاتف المسجل' : 'Numéro de téléphone' }}"
                        class="w-full {{ $isAr ? 'pr-9 pl-4' : 'pl-9 pr-4' }} py-3 rounded-2xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 outline-hidden transition"
                    >
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-black text-xs sm:text-sm shadow-md transition transform active:scale-95 shrink-0 cursor-pointer">
                    {{ $isAr ? 'تتبع الآن' : 'Rechercher' }}
                </button>
            </form>

            @if($error)
                <div class="mt-4 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ $error }}</span>
                </div>
            @endif
        </div>

        @if($order)
            <!-- Active Order Tracking Details -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-lg overflow-hidden divide-y divide-slate-100">
                
                <!-- Order Summary Header -->
                <div class="p-6 sm:p-8 bg-gradient-to-br from-slate-900 to-slate-950 text-white">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold text-sky-400 uppercase tracking-wider block">
                                {{ $isAr ? 'تفاصيل الطلبية' : 'Commande confirmée' }}
                            </span>
                            <h2 class="text-xl sm:text-2xl font-black text-white mt-1">
                                {{ $order->order_number }}
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $isAr ? 'تاريخ الطلب :' : 'Passée le' }} {{ $order->created_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="text-xs text-slate-400 block">{{ $isAr ? 'المجموع الإجمالي' : 'Total TTC' }}</span>
                            <span class="text-2xl font-black text-sky-400">
                                {{ number_format($order->total_amount, 2) }} {{ $currency }}
                            </span>
                            <span class="block text-[11px] text-emerald-400 font-semibold mt-0.5">
                                💵 {{ $isAr ? 'الدفع نقداً عند الاستلام' : 'Paiement à la livraison (COD)' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Visual Stepper Progress Bar -->
                <div class="p-6 sm:p-10">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-8 flex items-center gap-2">
                        <span>📍</span>
                        <span>{{ $isAr ? 'حالة التوصيل المباشرة' : 'Progression de l\'acheminement' }}</span>
                    </h3>

                    @if($isCancelled)
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-bold flex items-center gap-3">
                            <span class="text-2xl">❌</span>
                            <div>
                                <p>{{ $isAr ? 'هذه الطلبية تم إلغاؤها.' : 'Cette commande a été annulée.' }}</p>
                                <p class="text-xs font-normal text-rose-600 mt-0.5">{{ $isAr ? 'يرجى التواصل مع خدمة العملاء لمزيد من المعلومات.' : 'Contactez le service client pour toute question.' }}</p>
                            </div>
                        </div>
                    @else
                        <!-- 5-Step Desktop Timeline -->
                        <div class="relative">
                            <!-- Background connecting bar -->
                            <div class="hidden sm:block absolute top-5 left-8 right-8 h-1 bg-slate-200 -z-0">
                                <div class="h-full bg-gradient-to-r from-sky-500 to-emerald-500 transition-all duration-700" style="width: {{ (($currentStep - 1) / 4) * 100 }}%"></div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-5 gap-6 sm:gap-2 relative z-10">
                                
                                <!-- Step 1: Pending -->
                                <div class="flex sm:flex-col items-center gap-3 sm:gap-2 text-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $currentStep >= 1 ? 'bg-sky-600 text-white shadow-md ring-4 ring-sky-100' : 'bg-slate-200 text-slate-500' }}">
                                        ✓
                                    </div>
                                    <div class="sm:text-center text-left">
                                        <p class="text-xs font-black {{ $currentStep >= 1 ? 'text-slate-900' : 'text-slate-400' }}">
                                            {{ $isAr ? 'تم الاستلام' : 'Reçue' }}
                                        </p>
                                        <span class="text-[10px] text-slate-400 block">{{ $order->created_at->format('d/m H:i') }}</span>
                                    </div>
                                </div>

                                <!-- Step 2: Processing -->
                                <div class="flex sm:flex-col items-center gap-3 sm:gap-2 text-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $currentStep >= 2 ? 'bg-sky-600 text-white shadow-md ring-4 ring-sky-100' : 'bg-slate-200 text-slate-500' }}">
                                        {{ $currentStep >= 2 ? '✓' : '2' }}
                                    </div>
                                    <div class="sm:text-center text-left">
                                        <p class="text-xs font-black {{ $currentStep >= 2 ? 'text-slate-900' : 'text-slate-400' }}">
                                            {{ $isAr ? 'قيد التحضير' : 'En préparation' }}
                                        </p>
                                        <span class="text-[10px] text-slate-400 block">{{ $isAr ? 'تجهيز الطرد' : 'Par le vendeur' }}</span>
                                    </div>
                                </div>

                                <!-- Step 3: Shipped -->
                                <div class="flex sm:flex-col items-center gap-3 sm:gap-2 text-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $currentStep >= 3 ? 'bg-sky-600 text-white shadow-md ring-4 ring-sky-100' : 'bg-slate-200 text-slate-500' }}">
                                        {{ $currentStep >= 3 ? '✓' : '3' }}
                                    </div>
                                    <div class="sm:text-center text-left">
                                        <p class="text-xs font-black {{ $currentStep >= 3 ? 'text-slate-900' : 'text-slate-400' }}">
                                            {{ $isAr ? 'تم الشحن' : 'Expédiée' }}
                                        </p>
                                        <span class="text-[10px] text-slate-400 block">{{ $isAr ? 'مع شركة النقل' : 'Prise en charge' }}</span>
                                    </div>
                                </div>

                                <!-- Step 4: Out for Delivery -->
                                <div class="flex sm:flex-col items-center gap-3 sm:gap-2 text-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $currentStep >= 4 ? 'bg-amber-500 text-white shadow-md ring-4 ring-amber-100' : 'bg-slate-200 text-slate-500' }}">
                                        {{ $currentStep >= 4 ? '✓' : '4' }}
                                    </div>
                                    <div class="sm:text-center text-left">
                                        <p class="text-xs font-black {{ $currentStep >= 4 ? 'text-slate-900' : 'text-slate-400' }}">
                                            {{ $isAr ? 'في طريقها إليك' : 'En livraison' }}
                                        </p>
                                        <span class="text-[10px] text-slate-400 block">{{ $order->shipping_city }}</span>
                                    </div>
                                </div>

                                <!-- Step 5: Delivered -->
                                <div class="flex sm:flex-col items-center gap-3 sm:gap-2 text-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $currentStep >= 5 ? 'bg-emerald-600 text-white shadow-md ring-4 ring-emerald-100' : 'bg-slate-200 text-slate-500' }}">
                                        {{ $currentStep >= 5 ? '✓' : '5' }}
                                    </div>
                                    <div class="sm:text-center text-left">
                                        <p class="text-xs font-black {{ $currentStep >= 5 ? 'text-emerald-700 font-extrabold' : 'text-slate-400' }}">
                                            {{ $isAr ? 'تم التسليم' : 'Livrée' }}
                                        </p>
                                        <span class="text-[10px] text-slate-400 block">{{ $isAr ? 'شكراً لثقتكم' : 'Succès' }}</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endif
                </div>

                <!-- Delivery Destination & Details -->
                <div class="p-6 sm:p-8 bg-slate-50/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Destination Card -->
                        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <span>📍</span>
                                <span>{{ $isAr ? 'عنوان التوصيل' : 'Adresse de livraison' }}</span>
                            </h4>
                            <p class="text-sm font-bold text-slate-900">{{ $order->user ? $order->user->name : 'Client' }}</p>
                            <p class="text-xs text-slate-600 mt-1">{{ $order->shipping_address }}</p>
                            <p class="text-xs text-slate-600">{{ $order->shipping_city }}, Tunisie</p>
                            <p class="text-xs font-bold text-slate-800 mt-2">📞 {{ $order->phone }}</p>
                        </div>

                        <!-- Package Contents -->
                        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <span>📦</span>
                                <span>{{ $isAr ? 'محتويات الطرد' : 'Articles commandés' }}</span>
                            </h4>
                            <div class="space-y-3">
                                @foreach($order->items as $item)
                                    @php $p = $item->product; @endphp
                                    <div class="flex items-center gap-3">
                                        @if($p)
                                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-12 h-12 rounded-xl object-cover bg-slate-100 shrink-0">
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-slate-800 truncate">{{ $p ? $p->name : 'Produit' }}</p>
                                            <span class="text-[11px] text-slate-400">Qté: {{ $item->quantity }} × {{ number_format($item->price, 2) }} {{ $currency }}</span>
                                        </div>
                                        <span class="text-xs font-black text-slate-900">
                                            {{ number_format($item->quantity * $item->price, 2) }} {{ $currency }}
                                        </span>
                                    </div>
                                @endforeach

                                @if($order->discount_amount > 0)
                                    <div class="pt-2 border-t border-slate-100 flex justify-between text-xs font-bold text-emerald-600">
                                        <span>🏷️ {{ $isAr ? 'تخفيض الكوبون' : 'Remise code promo' }}</span>
                                        <span>-{{ number_format($order->discount_amount, 2) }} {{ $currency }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer support bar -->
                <div class="p-4 sm:p-6 bg-white flex flex-wrap items-center justify-between gap-4 text-xs">
                    <span class="text-slate-500">
                        {{ $isAr ? 'هل لديك أي استفسار حول هذه الطلبية؟' : 'Une question ou besoin d\'aide pour votre livraison ?' }}
                    </span>
                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-1.5 font-bold text-sky-600 hover:text-sky-700">
                        <span>💬</span>
                        <span>{{ $isAr ? 'تواصل مع الدعم الفني' : 'Contacter le support client' }}</span>
                    </a>
                </div>

            </div>
        @endif

    </div>
</div>
@endsection
