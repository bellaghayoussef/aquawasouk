<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    cart: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat(usePage().props.locale === 'ar' ? 'ar-TN' : 'fr-TN', {
        style: 'currency',
        currency: 'TND',
    }).format(price);
};

const updateQuantity = (itemId, quantity) => {
    if (quantity < 1) return;
    router.patch(route('cart.update', itemId), {
        quantity: quantity,
    }, {
        preserveScroll: true,
    });
};

const removeItem = (itemId) => {
    if (confirm('Voulez-vous vraiment retirer cet article du panier ?')) {
        router.delete(route('cart.remove', itemId), {
            preserveScroll: true,
        });
    }
};

// Group cart items by vendor
const itemsByVendor = computed(() => {
    if (!props.cart || !props.cart.items) return [];

    const groups = {};
    props.cart.items.forEach(item => {
        if (!item.product || !item.product.vendor) return;
        const vendorId = item.product.vendor.id;
        if (!groups[vendorId]) {
            groups[vendorId] = {
                vendor: item.product.vendor,
                items: [],
                total: 0
            };
        }
        groups[vendorId].items.push(item);
        groups[vendorId].total += item.quantity * item.price;
    });

    return Object.values(groups);
});

const getCartTotal = () => {
    if (!props.cart || !props.cart.items) return 0;
    return props.cart.items.reduce((total, item) => {
        return total + (item.quantity * item.price);
    }, 0);
};
</script>

<template>
    <Head title="Mon Panier - AquawaSouk" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- Breadcrumbs -->
            <nav class="flex items-center text-xs font-semibold text-slate-400 mb-6 gap-2">
                <Link :href="route('home')" class="hover:text-brand-600 transition">{{ __('home') }}</Link>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200">{{ __('shopping_cart') || 'Mon Panier' }}</span>
            </nav>

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ __('shopping_cart') || 'Mon Panier d\'Achats' }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Les articles sont expédiés séparément par chaque boutique partenaire
                    </p>
                </div>
            </div>

            <!-- Active Cart Groups -->
            <div v-if="cart && cart.items && cart.items.length > 0" class="space-y-8">
                <!-- Group by Vendor Card -->
                <div
                    v-for="vendorGroup in itemsByVendor"
                    :key="vendorGroup.vendor.id"
                    class="rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-sm overflow-hidden"
                >
                    <!-- Vendor Header Ribbon -->
                    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-amber-400 border border-white/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-bold text-white">{{ vendorGroup.vendor.shop_name }}</h2>
                                    <span class="inline-flex items-center gap-1 text-sky-400 text-xs font-bold">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Vérifié</span>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-300">
                                    {{ vendorGroup.items.length }} article(s) • Expédition depuis {{ vendorGroup.vendor.city }}
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="route('orders.checkout', vendorGroup.vendor.id)"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 shrink-0 cursor-pointer"
                        >
                            <span>Commander ce lot</span>
                            <span class="text-emerald-100 font-semibold">({{ formatPrice(Number(vendorGroup.total) + Number(vendorGroup.vendor.shipping_price || 0)) }})</span>
                        </Link>
                    </div>

                    <!-- Vendor Items List -->
                    <div class="divide-y divide-slate-100 dark:divide-slate-700/60 p-6 space-y-6 sm:space-y-0">
                        <div
                            v-for="item in vendorGroup.items"
                            :key="item.id"
                            class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                        >
                            <div class="flex items-center gap-4">
                                <!-- Product Image -->
                                <div class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-700/50 overflow-hidden shrink-0 border border-slate-200/80 dark:border-slate-700">
                                    <img
                                        v-if="item.product.primary_image"
                                        :src="`/uploads/${item.product.primary_image.image_path}`"
                                        :alt="item.product.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                </div>

                                <!-- Product Title and Actions -->
                                <div>
                                    <Link
                                        :href="route('products.show', item.product.slug)"
                                        class="text-sm font-bold text-slate-900 dark:text-white hover:text-brand-600 transition line-clamp-1 cursor-pointer"
                                    >
                                        {{ item.product.name }}
                                    </Link>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        Prix unitaire: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ formatPrice(item.price) }}</span>
                                    </div>

                                    <button
                                        @click="removeItem(item.id)"
                                        class="text-xs font-semibold text-red-500 hover:text-red-700 mt-2 flex items-center gap-1.5 transition cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span>{{ __('remove') || 'Supprimer' }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Quantity & Subtotal -->
                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-0 border-slate-100 dark:border-slate-700">
                                <!-- Quantity controls -->
                                <div class="flex items-center border border-slate-300 dark:border-slate-600 rounded-xl overflow-hidden bg-slate-50 dark:bg-slate-900">
                                    <button
                                        @click="updateQuantity(item.id, item.quantity - 1)"
                                        :disabled="item.quantity <= 1"
                                        class="px-3 py-1 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed font-bold transition cursor-pointer"
                                    >
                                        -
                                    </button>
                                    <input
                                        :value="item.quantity"
                                        @change="updateQuantity(item.id, Number($event.target.value))"
                                        type="number"
                                        min="1"
                                        class="w-12 text-center border-0 py-1 text-xs font-bold text-slate-900 dark:text-white dark:bg-slate-900 focus:ring-0"
                                    />
                                    <button
                                        @click="updateQuantity(item.id, item.quantity + 1)"
                                        :disabled="item.quantity >= item.product.stock"
                                        class="px-3 py-1 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed font-bold transition cursor-pointer"
                                    >
                                        +
                                    </button>
                                </div>

                                <!-- Subtotal -->
                                <div class="text-right">
                                    <div class="text-base font-black text-slate-900 dark:text-white">
                                        {{ formatPrice(item.quantity * item.price) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Order Footer Summary -->
                    <div class="p-5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-xs">
                        <div class="flex items-center gap-4 text-slate-500 dark:text-slate-400">
                            <span>Frais de livraison: <strong class="text-slate-800 dark:text-slate-200">{{ Number(vendorGroup.vendor.shipping_price || 0) > 0 ? formatPrice(vendorGroup.vendor.shipping_price) : 'Offerte' }}</strong></span>
                            <span>•</span>
                            <span>Sous-total articles: <strong class="text-slate-800 dark:text-slate-200">{{ formatPrice(vendorGroup.total) }}</strong></span>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-500">Total pour cette boutique:</span>
                            <span class="text-lg font-black text-brand-600 dark:text-sky-400">
                                {{ formatPrice(Number(vendorGroup.total) + Number(vendorGroup.vendor.shipping_price || 0)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Cart State -->
            <div v-else class="text-center py-20 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm">
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-brand-50 dark:bg-brand-950/50 border border-brand-100 dark:border-brand-900 flex items-center justify-center text-brand-600 dark:text-sky-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                    {{ __('cart_empty') || 'Votre panier est actuellement vide' }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 max-w-md mx-auto">
                    {{ __('start_shopping') || 'Parcourez nos rayons et découvrez des centaines de produits proposés par nos boutiques partenaires.' }}
                </p>
                <Link
                    :href="route('products.index')"
                    class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-slate-900 hover:bg-brand-600 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200 text-white font-bold text-xs uppercase tracking-wider shadow-lg transition cursor-pointer"
                >
                    <span>{{ __('browse_products') || 'Commencer mes achats' }}</span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
