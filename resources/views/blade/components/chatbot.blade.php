@php
    $isAr = app()->getLocale() === 'ar';
    $currentLocale = app()->getLocale();
@endphp

<!-- SHOPPING ASSISTANT CHATBOT (Blade & Alpine.js) -->
<div 
    x-data="shoppingChatbot('{{ $currentLocale }}', '{{ csrf_token() }}')" 
    x-init="init()"
    class="relative z-50"
    dir="{{ $isAr ? 'rtl' : 'ltr' }}"
>
    <!-- FLOATING LAUNCHER BUTTON -->
    <div 
        x-show="!isOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-75 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        class="fixed bottom-18 md:bottom-6 {{ $isAr ? 'left-4 sm:left-6' : 'right-4 sm:right-6' }} z-30 sm:z-50 flex items-center gap-3"
    >
        <!-- Proactive Speech Bubble Notification (shown on first visit) -->
        <div 
            x-show="showBubble" 
            x-transition 
            @click="openChat()" 
            class="hidden sm:flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-white text-slate-800 text-xs font-bold shadow-xl border border-slate-200 cursor-pointer hover:shadow-2xl transition transform hover:-translate-y-0.5"
        >
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            <span>{{ $isAr ? '👋 مرحباً ! كيف يمكنني مساعدتك اليوم؟' : '👋 Bonjour ! Une question ? Je suis là pour vous aider !' }}</span>
            <button @click.stop="showBubble = false" class="text-slate-400 hover:text-slate-600 text-xs font-bold ml-1">&times;</button>
        </div>

        <!-- Master Floating Trigger Button -->
        <button 
            @click="toggleChat()" 
            class="group relative w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gradient-to-r from-sky-600 via-indigo-600 to-sky-700 text-white shadow-2xl shadow-sky-600/40 hover:shadow-sky-600/60 transition-all duration-300 transform hover:scale-105 active:scale-95 flex items-center justify-center cursor-pointer border-2 border-white/40"
            aria-label="{{ $isAr ? 'فتح المحادثة' : 'Ouvrir l\'assistant shopping' }}"
        >
            <!-- Glowing Ambient Ring -->
            <span class="absolute inset-0 rounded-full bg-sky-400/30 animate-ping pointer-events-none"></span>

            <!-- Bot Icon (Normal state) -->
            <div class="relative flex items-center justify-center">
                <svg class="w-7 h-7 sm:w-8 sm:h-8 transition-transform group-hover:rotate-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <!-- Online Pulse Dot -->
                <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-400 border-2 border-white shadow-xs"></span>
            </div>
        </button>
    </div>

    <!-- MAIN CHATBOT WINDOW -->
    <div 
        x-show="isOpen" 
        x-cloak
        x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        class="fixed bottom-16 sm:bottom-6 {{ $isAr ? 'left-2 sm:left-6' : 'right-2 sm:right-6' }} z-50 w-[420px] max-w-[calc(100vw-1rem)] h-[600px] sm:h-[620px] max-h-[85vh] bg-white rounded-3xl shadow-2xl border border-slate-200/90 flex flex-col overflow-hidden text-slate-800"
    >
        <!-- 1. HEADER BAR -->
        <div class="bg-gradient-to-r from-slate-950 via-sky-950 to-indigo-950 text-white p-4 shrink-0 flex items-center justify-between border-b border-white/10">
            <div class="flex items-center gap-3">
                <!-- Bot Avatar -->
                <div class="relative w-11 h-11 rounded-2xl bg-white/10 border border-white/20 p-1 flex items-center justify-center shrink-0 shadow-inner">
                    <img src="{{ asset('logo/logo.png') }}" alt="Bot Avatar" class="w-full h-full object-contain">
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-400 border-2 border-slate-950"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-black tracking-tight text-white">AquawaBot AI</h3>
                        <span class="px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 text-[10px] font-bold border border-sky-400/30">
                            {{ $isAr ? 'مساعد ذكي' : 'Assistant IA' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-300 flex items-center gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>{{ $isAr ? 'متصل الآن • رد فوري' : 'En ligne • Répond instantanément' }}</span>
                    </p>
                </div>
            </div>

            <!-- Header Window Actions -->
            <div class="flex items-center gap-1 text-slate-300">
                <button 
                    @click="resetChat()" 
                    class="p-2 hover:text-white hover:bg-white/10 rounded-xl transition cursor-pointer"
                    title="{{ $isAr ? 'محادثة جديدة' : 'Nouvelle conversation' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
                <button 
                    @click="isOpen = false" 
                    class="p-2 hover:text-white hover:bg-white/10 rounded-xl transition cursor-pointer"
                    title="{{ $isAr ? 'إغلاق' : 'Fermer' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- 2. SUB-NAV TABS (Chat / Cart / Tracking) -->
        <div class="bg-slate-50 border-b border-slate-200 px-3 py-1.5 flex items-center justify-around text-xs font-bold shrink-0">
            <button 
                @click="activeTab = 'chat'" 
                :class="activeTab === 'chat' ? 'text-sky-600 bg-white shadow-xs border-slate-200/80' : 'text-slate-500 hover:text-slate-800 border-transparent'" 
                class="flex-1 py-1.5 px-2 rounded-xl transition flex items-center justify-center gap-1.5 border cursor-pointer"
            >
                <span>💬</span>
                <span>{{ $isAr ? 'المحادثة' : 'Chat' }}</span>
            </button>
            
            <button 
                @click="activeTab = 'cart'; fetchCart()" 
                :class="activeTab === 'cart' ? 'text-sky-600 bg-white shadow-xs border-slate-200/80' : 'text-slate-500 hover:text-slate-800 border-transparent'" 
                class="flex-1 py-1.5 px-2 rounded-xl transition flex items-center justify-center gap-1.5 border cursor-pointer"
            >
                <span>🛒</span>
                <span>{{ $isAr ? 'السلة' : 'Panier' }}</span>
                <span 
                    x-show="cart.total_items > 0" 
                    x-text="cart.total_items" 
                    class="px-1.5 py-0.2 rounded-full bg-rose-500 text-white text-[10px] font-black"
                ></span>
            </button>

            <button 
                @click="activeTab = 'tracking'" 
                :class="activeTab === 'tracking' ? 'text-sky-600 bg-white shadow-xs border-slate-200/80' : 'text-slate-500 hover:text-slate-800 border-transparent'" 
                class="flex-1 py-1.5 px-2 rounded-xl transition flex items-center justify-center gap-1.5 border cursor-pointer"
            >
                <span>📦</span>
                <span>{{ $isAr ? 'تتبع طلبي' : 'Suivi' }}</span>
            </button>
        </div>

        <!-- 3. TAB 1: CHAT MESSAGES BODY -->
        <div x-show="activeTab === 'chat'" class="flex-1 flex flex-col min-h-0 bg-slate-50/50">
            
            <!-- Messages Scroll Area -->
            <div 
                x-ref="messagesContainer" 
                class="flex-1 overflow-y-auto p-4 space-y-4 text-xs"
            >
                <template x-for="(msg, i) in messages" :key="i">
                    <div :class="msg.sender === 'user' ? 'flex justify-end' : 'flex justify-start items-start gap-2.5'">
                        
                        <!-- Assistant Avatar Icon -->
                        <template x-if="msg.sender === 'assistant'">
                            <div class="w-7 h-7 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0 mt-0.5">
                                🤖
                            </div>
                        </template>

                        <!-- Bubble Container -->
                        <div class="max-w-[85%] space-y-2.5">
                            <!-- Text Bubble -->
                            <div 
                                :class="msg.sender === 'user' 
                                    ? 'bg-gradient-to-r from-sky-600 to-indigo-600 text-white rounded-2xl rounded-br-xs px-4 py-2.5 shadow-sm font-medium' 
                                    : 'bg-white border border-slate-200/90 text-slate-800 rounded-2xl rounded-tl-xs px-4 py-3 shadow-xs leading-relaxed'"
                                :dir="msg.direction || 'auto'"
                            >
                                <p class="whitespace-pre-line" x-text="msg.text"></p>
                            </div>

                            <!-- Embedded Products Showcase in Chat (if any returned) -->
                            <template x-if="msg.products && msg.products.length > 0">
                                <div class="space-y-2 pt-1">
                                    <div class="text-[11px] font-bold text-slate-500">
                                        {{ $isAr ? 'المنتجات المقترحة :' : 'Produits trouvés :' }}
                                    </div>
                                    <div class="grid grid-cols-1 gap-2">
                                        <template x-for="prod in msg.products" :key="prod.id">
                                            <div class="bg-white rounded-2xl border border-slate-200 p-2.5 shadow-xs flex items-center gap-3 hover:border-sky-300 transition">
                                                <div class="w-14 h-14 rounded-xl bg-slate-50 border border-slate-100 p-1 shrink-0 flex items-center justify-center overflow-hidden">
                                                    <img :src="prod.image_url" :alt="prod.name" class="max-h-full max-w-full object-contain">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-bold text-slate-900 truncate" x-text="prod.name"></h4>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="font-extrabold text-sky-600 text-xs" x-text="prod.price_formatted || (prod.price + ' DT')"></span>
                                                        <template x-if="prod.vendor">
                                                            <span class="text-[10px] text-slate-400 truncate" x-text="'🏪 ' + prod.vendor.shop_name"></span>
                                                        </template>
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-2">
                                                        <button 
                                                            type="button"
                                                            @click="addToCartDirect(prod.id)"
                                                            class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[10px] font-bold transition flex items-center gap-1 cursor-pointer"
                                                        >
                                                            <span>🛒</span>
                                                            <span>{{ $isAr ? 'أضف للسلة' : 'Ajouter' }}</span>
                                                        </button>
                                                        <a 
                                                            :href="'/products/' + prod.slug" 
                                                            class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[10px] font-bold transition"
                                                        >
                                                            {{ $isAr ? 'تفاصيل' : 'Voir' }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Quick Action Chips Under Message -->
                            <template x-if="msg.quick_actions && msg.quick_actions.length > 0">
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <template x-for="(act, aIdx) in msg.quick_actions" :key="aIdx">
                                        <button 
                                            type="button"
                                            @click="handleAction(act)"
                                            class="px-3 py-1.5 rounded-full bg-white hover:bg-sky-50 text-sky-700 border border-sky-200/80 hover:border-sky-300 text-[11px] font-bold shadow-2xs transition transform active:scale-95 cursor-pointer flex items-center gap-1"
                                        >
                                            <span x-text="act.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>

                        </div>
                    </div>
                </template>

                <!-- Typing indicator -->
                <div x-show="isTyping" class="flex items-center gap-2 text-slate-400 text-xs">
                    <div class="w-7 h-7 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">
                        🤖
                    </div>
                    <div class="bg-white border border-slate-200 px-3.5 py-2.5 rounded-2xl rounded-tl-xs shadow-xs flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce [animation-delay:0.2s]"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce [animation-delay:0.4s]"></span>
                    </div>
                </div>
            </div>

            <!-- Quick Suggestions Bar (Top of Input) -->
            <div class="px-4 py-2 border-t border-slate-200 bg-white flex items-center gap-1.5 overflow-x-auto no-scrollbar shrink-0">
                <template x-for="(act, sIdx) in suggestedChips" :key="sIdx">
                    <button 
                        type="button"
                        @click="handleAction(act)" 
                        class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 text-[10px] font-bold whitespace-nowrap transition cursor-pointer"
                        x-text="act.label"
                    ></button>
                </template>
            </div>

            <!-- Input Bar -->
            <div class="p-3 bg-white border-t border-slate-200 shrink-0">
                <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                    <input 
                        type="text" 
                        x-model="inputMessage" 
                        x-ref="messageInput"
                        placeholder="{{ $isAr ? 'اطرح سؤالك أو ابحث عن منتج...' : 'Posez une question ou cherchez un produit...' }}" 
                        class="flex-1 py-2.5 px-4 rounded-2xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden bg-slate-50/50 focus:bg-white"
                        :disabled="isTyping"
                    >
                    <button 
                        type="submit" 
                        :disabled="!inputMessage.trim() || isTyping"
                        class="w-10 h-10 rounded-2xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white flex items-center justify-center transition disabled:opacity-40 disabled:cursor-not-allowed shadow-md cursor-pointer shrink-0"
                    >
                        <svg class="w-4 h-4 transform {{ $isAr ? '-scale-x-100' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- 4. TAB 2: LIVE CART SNAPSHOT -->
        <div x-show="activeTab === 'cart'" class="flex-1 flex flex-col p-4 bg-slate-50/50 overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <h4 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                    <span>🛒</span>
                    <span>{{ $isAr ? 'عربة التسوق الحالية' : 'Votre Panier en Cours' }}</span>
                </h4>
                <span class="text-xs font-bold text-sky-600" x-text="cart.total_items + ' {{ $isAr ? 'منتج' : 'article(s)' }}'"></span>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 py-3 space-y-3">
                <template x-if="cart.items && cart.items.length > 0">
                    <div class="space-y-2.5">
                        <template x-for="item in cart.items" :key="item.id">
                            <div class="bg-white rounded-2xl border border-slate-200 p-3 shadow-xs flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 p-1 shrink-0 flex items-center justify-center overflow-hidden">
                                        <img :src="item.image_url" :alt="item.name" class="max-h-full max-w-full object-contain">
                                    </div>
                                    <div class="min-w-0">
                                        <h5 class="font-bold text-xs text-slate-900 truncate" x-text="item.name"></h5>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            <span x-text="item.quantity + ' × ' + Number(item.price).toFixed(2) + ' DT'"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="font-black text-xs text-slate-900 shrink-0" x-text="(item.quantity * item.price).toFixed(2) + ' DT'"></div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!cart.items || cart.items.length === 0">
                    <div class="py-12 text-center text-slate-400 space-y-2">
                        <span class="text-3xl block">🛒</span>
                        <p class="font-bold text-xs text-slate-600">{{ $isAr ? 'عربة التسوق فارغة حالياً' : 'Votre panier est vide' }}</p>
                        <p class="text-[11px]">{{ $isAr ? 'تصفح الكتالوج لإضافة منتجاتك المفضلة' : 'Ajoutez des articles via l\'assistant ou la boutique' }}</p>
                    </div>
                </template>
            </div>

            <!-- Cart Summary & Checkout Link -->
            <div class="pt-4 border-t border-slate-200 space-y-3 shrink-0">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                    <span>{{ $isAr ? 'المجموع الفرعي :' : 'Sous-total :' }}</span>
                    <span class="text-base font-black text-sky-600" x-text="Number(cart.subtotal || 0).toFixed(2) + ' DT'"></span>
                </div>
                <a 
                    href="{{ route('cart.index') }}" 
                    class="block w-full py-3 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white rounded-2xl text-xs font-black uppercase tracking-wider text-center shadow-md transition"
                >
                    {{ $isAr ? 'إتمام الشراء والطلب ←' : 'Accéder au panier & Commander →' }}
                </a>
            </div>
        </div>

        <!-- 5. TAB 3: ORDER TRACKING -->
        <div x-show="activeTab === 'tracking'" class="flex-1 flex flex-col p-5 bg-slate-50/50 overflow-y-auto space-y-4">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                    <span>📦</span>
                    <span>{{ $isAr ? 'تتبع مسار شحنتك' : 'Suivre l\'état de votre commande' }}</span>
                </h4>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $isAr ? 'أدخل رقم الطلب أو رقم الهاتف المسجل لتتبع الشحنة في الوقت الفعلي :' : 'Saisissez votre numéro de commande (#ORD-...) ou téléphone pour vérifier l\'état :' }}
                </p>
            </div>

            <form @submit.prevent="trackOrder()" class="space-y-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        {{ $isAr ? 'رقم الطلب *' : 'Numéro de Commande *' }}
                    </label>
                    <input 
                        type="text" 
                        x-model="trackingOrderNumber" 
                        placeholder="Ex: #ORD-2026-..." 
                        required
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden"
                    >
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        {{ $isAr ? 'رقم الهاتف (اختياري للتأكيد)' : 'Numéro de téléphone (optionnel)' }}
                    </label>
                    <input 
                        type="tel" 
                        x-model="trackingPhone" 
                        placeholder="Ex: 98 123 456" 
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden"
                    >
                </div>
                <button 
                    type="submit" 
                    :disabled="trackingLoading"
                    class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span x-show="!trackingLoading">{{ $isAr ? 'بحث عن الطلب' : 'Vérifier la commande' }}</span>
                    <span x-show="trackingLoading" x-cloak>{{ $isAr ? 'جاري التحقق...' : 'Recherche en cours...' }}</span>
                </button>
            </form>

            <!-- Tracking Result Card -->
            <div x-show="trackingResult" x-cloak class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-xs text-slate-900" x-text="trackingResult?.order_number"></span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800" x-text="trackingResult?.status_label"></span>
                </div>
                <p class="text-xs text-slate-600" x-text="trackingResult?.status_description"></p>
                <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-500 space-y-1">
                    <p>{{ $isAr ? 'وجهة التوصيل :' : 'Destination :' }} <strong class="text-slate-700" x-text="trackingResult?.shipping_city"></strong></p>
                    <p>{{ $isAr ? 'المجموع عند الاستلام :' : 'Montant à régler :' }} <strong class="text-emerald-700 font-extrabold" x-text="trackingResult?.total_amount"></strong></p>
                </div>
            </div>

            <!-- Error message if not found -->
            <div x-show="trackingError" x-cloak class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs" x-text="trackingError"></div>
        </div>

    </div>
</div>

<!-- COMPONENT LOGIC SCRIPT -->
<script>
function shoppingChatbot(locale, csrfToken) {
    return {
        isOpen: false,
        showBubble: true,
        activeTab: 'chat',
        isTyping: false,
        inputMessage: '',
        locale: locale || 'fr',
        csrfToken: csrfToken,
        messages: [],
        suggestedChips: [
            { label: locale === 'ar' ? '🔥 أفضل المبيعات' : '🔥 Meilleures ventes', query: 'meilleures ventes' },
            { label: locale === 'ar' ? '🐟 أحواض ومستلزمات' : '🐟 Aquariums & Équipements', query: 'aquarium' },
            { label: locale === 'ar' ? '💳 طرق الدفع' : '💳 Comment payer ?', query: 'paiement à la livraison' },
            { label: locale === 'ar' ? '🚚 مدة التوصيل' : '🚚 Délais de livraison', query: 'délais de livraison' },
        ],
        cart: {
            items: [],
            total_items: 0,
            subtotal: 0
        },
        trackingOrderNumber: '',
        trackingPhone: '',
        trackingLoading: false,
        trackingResult: null,
        trackingError: '',
        configLoaded: false,

        init() {
            // Lazy load config during idle time so it doesn't block LCP/FCP
            if ('requestIdleCallback' in window) {
                requestIdleCallback(() => this.loadConfig(), { timeout: 4000 });
            } else {
                setTimeout(() => this.loadConfig(), 3500);
            }
        },

        async loadConfig() {
            if (this.configLoaded) return;
            this.configLoaded = true;
            try {
                const res = await fetch(`/shopping-assistant/config?locale=${this.locale}`);
                if (res.ok) {
                    const data = await res.json();
                    if (data.welcome_message) {
                        this.messages = [{
                            sender: 'assistant',
                            text: data.welcome_message,
                            quick_actions: data.suggested_actions || []
                        }];
                    }
                    if (data.cart) {
                        this.cart = data.cart;
                    }
                }
            } catch (e) {
                if (this.messages.length === 0) {
                    this.messages.push({
                        sender: 'assistant',
                        text: this.locale === 'ar' 
                            ? 'مرحباً بك في أكواسوق! كيف يمكنني مساعدتك اليوم؟' 
                            : "Bonjour ! Bienvenue sur AquawaSouk. Comment puis-je vous aider aujourd'hui ?"
                    });
                }
            }
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            this.showBubble = false;
            if (this.isOpen) {
                if (!this.configLoaded) this.loadConfig();
                this.$nextTick(() => {
                    this.scrollToBottom();
                    this.$refs.messageInput?.focus();
                });
            }
        },

        openChat() {
            this.isOpen = true;
            this.showBubble = false;
            if (!this.configLoaded) this.loadConfig();
            this.$nextTick(() => {
                this.scrollToBottom();
                this.$refs.messageInput?.focus();
            });
        },

        resetChat() {
            this.messages = [];
            this.configLoaded = false;
            this.loadConfig();
        },

        scrollToBottom() {
            if (this.$refs.messagesContainer) {
                this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
            }
        },

        async sendMessage(customText = null) {
            const text = (customText || this.inputMessage).trim();
            if (!text || this.isTyping) return;

            this.inputMessage = '';
            this.messages.push({ sender: 'user', text: text });
            this.isTyping = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || this.csrfToken || '';
                const res = await fetch('/shopping-assistant/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: text,
                        locale: this.locale
                    })
                });

                if (res.ok) {
                    const data = await res.json();
                    this.messages.push({
                        sender: 'assistant',
                        text: data.message || "Voici ce que j'ai trouvé :",
                        direction: data.direction || 'auto',
                        products: data.products || [],
                        quick_actions: data.quick_actions || []
                    });

                    if (data.cart_count !== undefined) {
                        this.cart.total_items = data.cart_count;
                        this.cart.subtotal = data.cart_subtotal;
                    }
                } else {
                    throw new Error('Chat request failed');
                }
            } catch (e) {
                this.messages.push({
                    sender: 'assistant',
                    text: this.locale === 'ar' 
                        ? 'عذراً، حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.' 
                        : "Désolé, une petite erreur est survenue. Veuillez réessayer."
                });
            } finally {
                this.isTyping = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        handleAction(act) {
            if (act.action === 'open_cart' || act.action === 'view_cart') {
                this.activeTab = 'cart';
                this.fetchCart();
                return;
            }
            if (act.url) {
                window.location.href = act.url;
                return;
            }
            if (act.query) {
                this.activeTab = 'chat';
                this.sendMessage(act.query);
            }
        },

        async addToCartDirect(productId) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || this.csrfToken || '';
                const res = await fetch('/shopping-assistant/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId, quantity: 1 })
                });

                const data = await res.json();
                if (res.ok) {
                    if (data.cart) {
                        this.cart = data.cart;
                    }
                    this.messages.push({
                        sender: 'assistant',
                        text: `✅ ${data.message || 'Produit ajouté au panier !'}`,
                        quick_actions: [
                            { label: this.locale === 'ar' ? '🛒 عرض السلة' : '🛒 Voir le panier', action: 'open_cart' },
                            { label: this.locale === 'ar' ? '💳 إتمام الشراء' : '💳 Passer commande', url: '/cart' }
                        ]
                    });
                    this.$nextTick(() => this.scrollToBottom());
                } else {
                    alert(data.message || 'Impossible d\'ajouter au panier');
                }
            } catch (e) {
                alert('Erreur lors de l\'ajout au panier');
            }
        },

        async fetchCart() {
            try {
                const res = await fetch('/shopping-assistant/cart');
                if (res.ok) {
                    this.cart = await res.json();
                }
            } catch (e) {
                console.error('Failed to fetch cart', e);
            }
        },

        async trackOrder() {
            this.trackingLoading = true;
            this.trackingResult = null;
            this.trackingError = '';

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || this.csrfToken || '';
                const res = await fetch('/shopping-assistant/order-tracking', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        order_number: this.trackingOrderNumber,
                        phone: this.trackingPhone
                    })
                });

                const data = await res.json();
                if (res.ok && data.found) {
                    this.trackingResult = data;
                } else {
                    this.trackingError = data.message || (this.locale === 'ar' ? 'لم يتم العثور على أي طلب يطابق هذه البيانات.' : 'Aucune commande trouvée avec ces coordonnées.');
                }
            } catch (e) {
                this.trackingError = this.locale === 'ar' ? 'خطأ في جلب بيانات التتبع.' : 'Erreur de communication lors du suivi.';
            } finally {
                this.trackingLoading = false;
            }
        }
    };
}
</script>
