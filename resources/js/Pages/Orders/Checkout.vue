<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    cart: Object,
    vendorId: Number,
    shippingPrice: Number,
});

const tunisianGovernorates = [
    'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan',
    'Bizerte', 'Béja', 'Jendouba', 'Le Kef', 'Siliana', 'Sousse',
    'Monastir', 'Mahdia', 'Sfax', 'Kairouan', 'Kasserine', 'Sidi Bouzid',
    'Gabès', 'Medenine', 'Tataouine', 'Gafsa', 'Tozeur', 'Kebili'
];

const form = useForm({
    shipping_address: '',
    shipping_city: 'Tunis',
    shipping_postal_code: '1000',
    shipping_country: 'Tunisie',
    phone: '',
    vendor_id: props.vendorId,
    notes: '',
});

const formatPrice = (price) => {
    return new Intl.NumberFormat(usePage().props.locale === 'ar' ? 'ar-TN' : 'fr-TN', { 
        style: 'currency', 
        currency: 'TND', 
    }).format(price);
};

const getCartTotal = () => {
    let total = 0;
    if (props.cart && props.cart.items) {
        props.cart.items.forEach(item => {
            total += item.quantity * item.price;
        });
    }
    return total;
};

const submit = () => {
    form.post(route('orders.store'));
};
</script>

<template>
    <Head title="Finaliser ma commande - AquawaSouk" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- Breadcrumbs -->
            <nav class="flex items-center text-xs font-semibold text-slate-400 mb-6 gap-2">
                <Link :href="route('home')" class="hover:text-brand-600 transition">{{ __('home') }}</Link>
                <span>/</span>
                <Link :href="route('cart.index')" class="hover:text-brand-600 transition">{{ __('cart') }}</Link>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200">{{ __('checkout') }}</span>
            </nav>

            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ __('checkout') || 'Validation de la Commande' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Complétez vos coordonnées de livraison ci-dessous. Paiement en espèces lors de la réception.
                </p>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left: Shipping & Details Forms -->
                <div class="lg:col-span-7 space-y-6">
                    <!-- Shipping Information Card -->
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-sm space-y-5">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-700">
                            <span class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-sm">1</span>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                                {{ __('shipping_info') || 'Coordonnées de Livraison' }}
                            </h2>
                        </div>

                        <div class="space-y-4">
                            <!-- Phone -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
                                    {{ __('phone') || 'Numéro de téléphone' }} <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="form.phone"
                                    type="tel" 
                                    required
                                    placeholder="Ex: 98 123 456 / 20 000 000"
                                    class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                                />
                                <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.phone }}
                                </div>
                            </div>

                            <!-- City & Postal Code -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
                                        {{ __('city') || 'Ville / Gouvernorat' }} <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.shipping_city"
                                        required
                                        class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                                    >
                                        <option v-for="gov in tunisianGovernorates" :key="gov" :value="gov">
                                            {{ gov }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.shipping_city" class="text-red-500 text-xs mt-1">
                                        {{ form.errors.shipping_city }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
                                        {{ __('postal_code') || 'Code Postal' }}
                                    </label>
                                    <input 
                                        v-model="form.shipping_postal_code"
                                        type="text"
                                        placeholder="Ex: 1000"
                                        class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                                    />
                                </div>
                            </div>

                            <!-- Full Address -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
                                    {{ __('address') || 'Adresse complète' }} <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="form.shipping_address"
                                    type="text" 
                                    required
                                    placeholder="Rue, Quartier, N° Bâtiment, Étage..."
                                    class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                                />
                                <div v-if="form.errors.shipping_address" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.shipping_address }}
                                </div>
                            </div>

                            <!-- Delivery Notes -->
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">
                                    {{ __('order_notes') || 'Instructions spécifiques de livraison (optionnel)' }}
                                </label>
                                <textarea 
                                    v-model="form.notes"
                                    rows="2"
                                    placeholder="Précisions pour le livreur (ex: sonnerie, heure souhaitée)..."
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Card -->
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-sm">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-700 mb-5">
                            <span class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-sm">2</span>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                                {{ __('payment_method') || 'Mode de Paiement' }}
                            </h2>
                        </div>

                        <!-- Cash on delivery prominent radio box -->
                        <div class="flex items-start gap-4 p-5 rounded-2xl border-2 border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20">
                            <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-black shrink-0 mt-0.5">
                                ✓
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                        {{ __('cash_on_delivery') || 'Paiement en espèces à la livraison (COD)' }}
                                    </h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-200 dark:bg-emerald-800 text-emerald-900 dark:text-emerald-100">
                                        100% Sécurisé
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                    Vous ne payez rien maintenant. Le montant exact sera remis en espèces au livreur lors de la réception de votre colis.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary Sticky Card -->
                <div class="lg:col-span-5 sticky top-24">
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-xl space-y-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-slate-700">
                            {{ __('order_summary') || 'Récapitulatif de la commande' }}
                        </h2>

                        <!-- Ordered Items -->
                        <div class="space-y-3.5 max-h-72 overflow-y-auto pr-1 divide-y divide-slate-100 dark:divide-slate-700/60">
                            <div
                                v-for="item in cart.items"
                                :key="item.id"
                                class="pt-3 first:pt-0 flex items-center justify-between text-xs gap-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 overflow-hidden shrink-0 border border-slate-200 dark:border-slate-600">
                                        <img
                                            v-if="item.product && item.product.primary_image"
                                            :src="`/uploads/${item.product.primary_image.image_path}`"
                                            :alt="item.product.name"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-slate-100 line-clamp-1">{{ item.product ? item.product.name : 'Article' }}</p>
                                        <p class="text-slate-400">Quantité: {{ item.quantity }}</p>
                                    </div>
                                </div>
                                <span class="font-black text-slate-900 dark:text-white shrink-0">
                                    {{ formatPrice(item.quantity * item.price) }}
                                </span>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t border-slate-200 dark:border-slate-700 pt-4 space-y-2.5 text-xs">
                            <div class="flex justify-between text-slate-500">
                                <span>{{ __('subtotal') || 'Sous-total articles' }}</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ formatPrice(getCartTotal()) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-500">
                                <span>{{ __('shipping') || 'Frais de livraison boutique' }}</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">
                                    {{ Number(shippingPrice || 0) > 0 ? formatPrice(shippingPrice) : 'Offerte' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-baseline pt-3 border-t border-slate-100 dark:border-slate-700">
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ __('total') || 'Total à la livraison' }}</span>
                                <span class="text-2xl font-black text-brand-600 dark:text-sky-400">
                                    {{ shippingPrice > 0 ? formatPrice(Number(getCartTotal()) + Number(shippingPrice)) : formatPrice(getCartTotal()) }}
                                </span>
                            </div>
                        </div>

                        <!-- Confirm Action Button -->
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-6 rounded-2xl shadow-xl transition-all duration-200 transform active:scale-[0.99] flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span v-if="form.processing">{{ __('processing') || 'Confirmation en cours...' }}</span>
                            <span v-else class="text-sm">
                                ⚡ {{ __('place_order') || 'Confirmer la commande (Paiement à la livraison)' }}
                            </span>
                        </button>

                        <p class="text-[11px] text-center text-slate-400 leading-normal">
                            En confirmant cette commande, vous acceptez les Conditions Générales de Vente d'AquawaSouk.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
