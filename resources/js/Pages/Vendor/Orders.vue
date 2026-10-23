<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TableFilters from '@/Components/TableFilters.vue';

defineProps({
    orders: Object,
    vendor: Object,
    filters: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-TN', {
        style: 'currency',
        currency: 'TND',
    }).format(price);
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        delivered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Mes Commandes Vendeur - AquawaSouk" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display font-black text-2xl text-slate-900 dark:text-white tracking-tight">
                        Commandes reçues
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Gérez vos expéditions, bordereaux et statuts de livraison
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Filters Container -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5">
                    <TableFilters
                        :filters="filters"
                        :show-search="true"
                        :show-status="true"
                        :show-date-range="true"
                        :status-options="[
                            { value: 'pending', label: 'En attente' },
                            { value: 'processing', label: 'En préparation' },
                            { value: 'shipped', label: 'Expédiée' },
                            { value: 'delivered', label: 'Livrée' },
                            { value: 'cancelled', label: 'Annulée' }
                        ]"
                        placeholder="Rechercher par n° de commande ou client..."
                    />
                </div>

                <!-- Empty State -->
                <div v-if="orders.data.length === 0" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-12 text-center shadow-sm">
                    <div class="w-16 h-16 rounded-2xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Aucune commande pour le moment</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mt-1">
                        Les commandes passées par vos clients apparaîtront ici avec les bordereaux téléchargeables.
                    </p>
                </div>

                <!-- Orders List -->
                <div v-else class="space-y-4">
                    <div
                        v-for="orderGroup in orders.data"
                        :key="orderGroup.order.id"
                        class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden"
                    >
                        <!-- Order Header Bar -->
                        <div class="bg-slate-50/70 dark:bg-slate-800/40 px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-6">
                                <div>
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">N° Commande</span>
                                    <span class="font-bold font-mono text-sm text-slate-900 dark:text-white">#{{ orderGroup.order.order_number }}</span>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Date</span>
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ formatDate(orderGroup.created_at) }}</span>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Vendeur</span>
                                    <span class="text-sm font-black text-brand-600 dark:text-brand-400">{{ formatPrice(orderGroup.total) }}</span>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Statut</span>
                                    <span :class="['inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold ring-1 ring-inset', getStatusColor(orderGroup.order.status)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ orderGroup.order.status }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a
                                    :href="route('vendor.orders.waybill', orderGroup.items[0].id)"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-xs font-bold rounded-xl transition cursor-pointer shadow-xs"
                                    title="Imprimer le bordereau de livraison"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Bordereau
                                </a>
                                <Link
                                    :href="route('vendor.orders.show', orderGroup.items[0].id)"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
                                >
                                    <span>Gérer</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <!-- Order Items Preview -->
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-3">
                                <span>Client : <strong class="text-slate-800 dark:text-slate-200">{{ orderGroup.order.user.name }}</strong></span>
                                <span>{{ orderGroup.items.length }} article(s)</span>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="item in orderGroup.items.slice(0, 3)"
                                    :key="item.id"
                                    class="flex items-center gap-4 p-2 rounded-xl bg-slate-50/50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800/60"
                                >
                                    <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 overflow-hidden">
                                        <img
                                            v-if="item.product.primary_image"
                                            :src="`/uploads/${item.product.primary_image.image_path}`"
                                            :alt="item.product.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <svg v-else class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ item.product.name }}</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Qté: {{ item.quantity }} × {{ formatPrice(item.price) }}</p>
                                    </div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">
                                        {{ formatPrice(item.price * item.quantity) }}
                                    </div>
                                </div>
                                <p v-if="orderGroup.items.length > 3" class="text-xs text-slate-500 dark:text-slate-400 italic text-right">
                                    + {{ orderGroup.items.length - 3 }} autre(s) article(s)
                                </p>
                            </div>
                        </div>

                        <!-- Order Footer -->
                        <div class="bg-slate-50/40 dark:bg-slate-800/20 px-6 py-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                            <div>
                                Paiement :
                                <span :class="orderGroup.order.payment_status === 'paid' ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-amber-600 dark:text-amber-400 font-bold'">
                                    {{ orderGroup.order.payment_status.toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                Commission : <span class="font-bold text-slate-900 dark:text-white">{{ formatPrice(orderGroup.commission) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="orders.links && orders.links.length > 3" class="mt-8 flex justify-center">
                    <nav class="inline-flex items-center gap-1.5 p-1 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <Link
                            v-for="(link, index) in orders.links"
                            :key="index"
                            :href="link.url"
                            :class="[
                                'px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer',
                                link.active
                                    ? 'bg-brand-600 text-white shadow-xs'
                                    : link.url
                                        ? 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
                                        : 'text-slate-300 dark:text-slate-700 cursor-not-allowed'
                            ]"
                            :disabled="!link.url"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
