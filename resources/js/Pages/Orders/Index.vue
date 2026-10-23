<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    orders: Object,
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
            label: 'En attente',
        },
        processing: {
            bg: 'bg-blue-50 text-blue-700 border-blue-200',
            label: 'En préparation',
        },
        shipped: {
            bg: 'bg-indigo-50 text-indigo-700 border-indigo-200',
            label: 'En cours d\'expédition',
        },
        delivered: {
            bg: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            label: 'Livrée',
        },
        cancelled: {
            bg: 'bg-rose-50 text-rose-700 border-rose-200',
            label: 'Annulée',
        },
    };
    return badges[status] || { bg: 'bg-slate-100 text-slate-700 border-slate-200', label: status };
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-TN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Mes Commandes - AquawaSouk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="font-bold text-2xl font-display text-slate-900 tracking-tight">
                        Mes Commandes
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Consultez l'historique et le statut d'expédition de vos achats.
                    </p>
                </div>
                <Link
                    :href="route('products.index')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-xl shadow-sm transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Explorer la boutique
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Empty state -->
                <div v-if="!orders?.data || orders.data.length === 0" class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center max-w-lg mx-auto shadow-sm">
                    <div class="w-20 h-20 mx-auto rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-5">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold font-display text-slate-900">Aucune commande pour le moment</h3>
                    <p class="text-xs text-slate-500 mt-1.5 max-w-xs mx-auto leading-relaxed">
                        Vous n'avez pas encore passé de commande sur AquawaSouk. Parcourez nos milliers d'articles vérifiés !
                    </p>
                    <div class="mt-6">
                        <Link
                            :href="route('products.index')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-xl shadow-sm hover:shadow transition"
                        >
                            Découvrir les offres
                        </Link>
                    </div>
                </div>

                <!-- Orders list -->
                <div v-else class="space-y-6">
                    <div
                        v-for="order in orders.data"
                        :key="order.id"
                        class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden hover:border-slate-300 transition"
                    >
                        <!-- Order card top bar -->
                        <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-6">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">N° de commande</p>
                                    <p class="font-bold text-sm text-slate-900">#{{ order.order_number }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</p>
                                    <p class="font-semibold text-xs text-slate-700">{{ formatDate(order.created_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Montant total</p>
                                    <p class="font-bold text-sm text-brand-600 font-display">{{ formatPrice(order.total_amount) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Statut</p>
                                    <span :class="['inline-flex px-2 py-0.5 text-[11px] font-semibold rounded-full border', getStatusBadge(order.status).bg]">
                                        {{ getStatusBadge(order.status).label }}
                                    </span>
                                </div>
                            </div>

                            <Link
                                :href="route('orders.show', order.id)"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-lg text-xs font-semibold shadow-xs transition"
                            >
                                Détails & Facture
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>

                        <!-- Order items snapshot -->
                        <div class="p-6 divide-y divide-slate-100">
                            <div
                                v-for="item in order.items?.slice(0, 3)"
                                :key="item.id"
                                class="flex items-center gap-4 py-3 first:pt-0 last:pb-0"
                            >
                                <div class="w-14 h-14 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center flex-shrink-0">
                                    <img
                                        v-if="item.product?.primary_image"
                                        :src="`/uploads/${item.product.primary_image.image_path}`"
                                        :alt="item.product.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <svg v-else class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-slate-900 text-xs truncate">{{ item.product?.name || 'Produit' }}</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        {{ item.quantity }} × {{ formatPrice(item.price) }}
                                        <span v-if="item.product?.vendor" class="text-slate-400"> &bull; {{ item.product.vendor.shop_name }}</span>
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-bold text-xs text-slate-900">{{ formatPrice(item.price * item.quantity) }}</p>
                                </div>
                            </div>

                            <div v-if="order.items && order.items.length > 3" class="pt-3 text-center">
                                <Link
                                    :href="route('orders.show', order.id)"
                                    class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline"
                                >
                                    + {{ order.items.length - 3 }} autre(s) article(s) dans cette commande
                                </Link>
                            </div>
                        </div>

                        <!-- Card footer -->
                        <div class="px-6 py-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <div>
                                Paiement :
                                <span class="font-semibold capitalize text-slate-700">
                                    {{ order.payment_method === 'cod' ? 'À la livraison (Cash on Delivery)' : order.payment_method }}
                                </span>
                            </div>
                            <div>
                                Livraison :
                                <span class="font-semibold text-slate-700">
                                    {{ (order.shipping_cost || 0) > 0 ? formatPrice(order.shipping_cost) : 'Gratuit' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.links && orders.links.length > 3" class="flex justify-center pt-6">
                        <nav class="inline-flex rounded-xl bg-white p-1 border border-slate-200/80 shadow-xs space-x-1">
                            <Link
                                v-for="(link, index) in orders.links"
                                :key="index"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
                                    link.active
                                        ? 'bg-brand-600 text-white shadow-xs'
                                        : link.url
                                            ? 'text-slate-700 hover:bg-slate-100'
                                            : 'text-slate-300 pointer-events-none'
                                ]"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
