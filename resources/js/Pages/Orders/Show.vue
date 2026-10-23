<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    order: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-TN', {
        style: 'currency',
        currency: 'TND',
        minimumFractionDigits: 2,
    }).format(price || 0);
};

const getStatusBadge = (status) => {
    const badges = {
        pending: {
            bg: 'bg-amber-50 text-amber-700 border-amber-200',
            label: 'En attente de confirmation',
            icon: 'clock',
        },
        processing: {
            bg: 'bg-blue-50 text-blue-700 border-blue-200',
            label: 'En cours de préparation',
            icon: 'cog',
        },
        shipped: {
            bg: 'bg-indigo-50 text-indigo-700 border-indigo-200',
            label: 'En cours de livraison',
            icon: 'truck',
        },
        delivered: {
            bg: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            label: 'Livré avec succès',
            icon: 'check',
        },
        cancelled: {
            bg: 'bg-rose-50 text-rose-700 border-rose-200',
            label: 'Commande annulée',
            icon: 'x',
        },
    };
    return badges[status] || { bg: 'bg-slate-100 text-slate-700 border-slate-200', label: status, icon: 'info' };
};

const steps = [
    { key: 'pending', label: 'Commande reçue' },
    { key: 'processing', label: 'Préparation' },
    { key: 'shipped', label: 'En expédition' },
    { key: 'delivered', label: 'Livrée' },
];

const currentStepIndex = computed(() => {
    const map = { pending: 0, processing: 1, shipped: 2, delivered: 3, cancelled: -1 };
    return map[props.order.status] ?? 0;
});

const printInvoice = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Commande #${order.order_number} - AquawaSouk`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold font-display text-slate-900 tracking-tight">
                            Commande #{{ order.order_number }}
                        </h1>
                        <span :class="['inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border', getStatusBadge(order.status).bg]">
                            {{ getStatusBadge(order.status).label }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Effectuée le {{ new Date(order.created_at).toLocaleDateString('fr-TN', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="printInvoice"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-sm"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimer
                    </button>
                    <Link
                        :href="route('orders.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-brand-600 bg-brand-50 border border-brand-200 rounded-lg hover:bg-brand-100 transition"
                    >
                        Mes commandes
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Timeline Progress Bar -->
                <div v-if="order.status !== 'cancelled'" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-6">Suivi d'acheminement</h2>
                    <div class="relative">
                        <!-- Progress Track -->
                        <div class="absolute top-1/2 left-0 right-0 h-1 bg-slate-100 -translate-y-1/2 z-0 hidden sm:block"></div>
                        <div
                            class="absolute top-1/2 left-0 h-1 bg-brand-500 -translate-y-1/2 z-0 transition-all duration-500 hidden sm:block"
                            :style="{ width: `${(Math.max(0, currentStepIndex) / (steps.length - 1)) * 100}%` }"
                        ></div>

                        <!-- Step Icons -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 relative z-10">
                            <div
                                v-for="(step, idx) in steps"
                                :key="step.key"
                                class="flex sm:flex-col items-center gap-3 sm:gap-2 sm:text-center"
                            >
                                <div
                                    :class="[
                                        'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all',
                                        idx <= currentStepIndex
                                            ? 'bg-brand-600 text-white shadow-md shadow-brand-500/20'
                                            : 'bg-white text-slate-400 border-2 border-slate-200'
                                    ]"
                                >
                                    <svg v-if="idx < currentStepIndex" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span v-else>{{ idx + 1 }}</span>
                                </div>
                                <div>
                                    <p :class="['text-xs font-bold', idx <= currentStepIndex ? 'text-slate-900' : 'text-slate-400']">
                                        {{ step.label }}
                                    </p>
                                    <p class="text-[10px] text-slate-400" v-if="idx === currentStepIndex">
                                        Étape actuelle
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancelled notification -->
                <div v-else class="bg-rose-50 border border-rose-200 rounded-2xl p-6 text-rose-800 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0 text-rose-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold">Cette commande a été annulée</h3>
                        <p class="text-xs text-rose-700 mt-0.5">Si vous avez des questions ou souhaitez recommander, n'hésitez pas à nous contacter.</p>
                    </div>
                </div>

                <!-- Main Order Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Products ordered (2 cols) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                                <h2 class="text-base font-bold text-slate-900">Articles commandés</h2>
                                <span class="text-xs text-slate-500 font-medium">{{ order.items?.length || 0 }} article(s)</span>
                            </div>

                            <div class="divide-y divide-slate-100">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4 hover:bg-slate-50/50 transition"
                                >
                                    <div class="w-20 h-20 rounded-xl bg-slate-100 overflow-hidden border border-slate-200 flex-shrink-0 flex items-center justify-center">
                                        <img
                                            v-if="item.product?.primary_image"
                                            :src="`/uploads/${item.product.primary_image.image_path}`"
                                            :alt="item.product.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <svg v-else class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-slate-900 text-sm leading-snug">
                                            <Link
                                                v-if="item.product?.id"
                                                :href="route('products.show', item.product.id)"
                                                class="hover:text-brand-600 transition"
                                            >
                                                {{ item.product.name }}
                                            </Link>
                                            <span v-else>{{ item.product?.name || 'Produit' }}</span>
                                        </h3>
                                        <p v-if="item.product?.vendor" class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            Vendeur :
                                            <Link :href="route('vendors.show', item.product.vendor.id)" class="font-semibold text-slate-700 hover:text-brand-600">
                                                {{ item.product.vendor.shop_name }}
                                            </Link>
                                        </p>
                                        <div class="flex items-center gap-3 text-xs text-slate-500 mt-2">
                                            <span>Quantité : <strong class="text-slate-800">{{ item.quantity }}</strong></span>
                                            <span>&bull;</span>
                                            <span>Prix unitaire : <strong class="text-slate-800">{{ formatPrice(item.price) }}</strong></span>
                                        </div>
                                    </div>

                                    <div class="text-right sm:self-center flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto mt-2 sm:mt-0">
                                        <p class="font-bold text-base text-slate-900 font-display">
                                            {{ formatPrice(item.price * item.quantity) }}
                                        </p>
                                        <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 text-slate-700 mt-1">
                                            {{ item.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Note if exists -->
                            <div v-if="order.notes" class="p-6 bg-slate-50 border-t border-slate-100">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Note client / Instructions</h4>
                                <p class="text-xs text-slate-700 italic">"{{ order.notes }}"</p>
                            </div>
                        </div>

                        <!-- Cash on Delivery Notice -->
                        <div class="bg-gradient-to-br from-amber-50 to-orange-50/50 border border-amber-200/80 rounded-2xl p-6 flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="text-xs text-amber-900 space-y-1">
                                <h4 class="font-bold text-sm text-amber-950">Paiement à la livraison (Cash on Delivery)</h4>
                                <p>Préparez le montant exact lors de la livraison par le coursier. Notre service client ou le vendeur vous appellera pour confirmer l'expédition.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar info: Summary & Addresses (1 col) -->
                    <div class="space-y-6">
                        <!-- Financial Summary -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                            <h2 class="text-base font-bold text-slate-900">Récapitulatif financier</h2>

                            <div class="space-y-2.5 text-xs">
                                <div class="flex justify-between text-slate-600">
                                    <span>Sous-total articles</span>
                                    <span class="font-semibold text-slate-900">
                                        {{ formatPrice(order.total_amount - (order.shipping_cost || 0)) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Frais de livraison</span>
                                    <span class="font-semibold text-slate-900">
                                        {{ (order.shipping_cost || 0) > 0 ? formatPrice(order.shipping_cost) : 'Gratuit' }}
                                    </span>
                                </div>
                                <div class="border-t border-slate-200 pt-3 flex justify-between items-baseline">
                                    <span class="text-sm font-bold text-slate-900">Total à payer</span>
                                    <span class="text-xl font-bold font-display text-brand-600">
                                        {{ formatPrice(order.total_amount) }}
                                    </span>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Statut du paiement</span>
                                <span
                                    :class="[
                                        'px-2 py-0.5 rounded-full font-semibold text-[10px]',
                                        order.payment_status === 'paid'
                                            ? 'bg-emerald-100 text-emerald-800'
                                            : 'bg-amber-100 text-amber-800'
                                    ]"
                                >
                                    {{ order.payment_status === 'paid' ? 'Payé' : 'À régler à la livraison' }}
                                </span>
                            </div>
                        </div>

                        <!-- Delivery Address & Contact -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Adresse de livraison
                            </h2>

                            <div class="text-xs text-slate-600 space-y-1.5 leading-relaxed">
                                <p class="font-bold text-slate-900 text-sm">{{ order.user?.name }}</p>
                                <p class="text-slate-700">{{ order.shipping_address }}</p>
                                <p class="text-slate-700">
                                    {{ order.shipping_city }}
                                    <span v-if="order.shipping_postal_code">, {{ order.shipping_postal_code }}</span>
                                </p>
                                <p class="text-slate-500">{{ order.shipping_country || 'Tunisie' }}</p>

                                <div class="pt-3 border-t border-slate-100 flex items-center gap-2 text-slate-700">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <span class="font-semibold">{{ order.phone }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Need Help -->
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 text-center space-y-2">
                            <p class="text-xs font-bold text-slate-900">Besoin d'aide avec cette commande ?</p>
                            <p class="text-[11px] text-slate-500">Contactez notre support 7j/7 pour modifier votre adresse ou annuler.</p>
                            <Link
                                :href="route('contact')"
                                class="inline-block mt-1 text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline"
                            >
                                Contacter l'assistance &rarr;
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
