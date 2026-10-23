<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    order: Object,
    items: Array,
    vendor: Object,
});

// Assume all items have the same status for now, or take the first one
const currentStatus = computed(() => props.items[0]?.status || 'pending');
const newStatus = ref(currentStatus.value);

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
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const updateStatus = () => {
    // Update status for the first item, controller should handle updating all items for this vendor/order
    // OR we iterate and update all.
    // Better: Update the controller to handle bulk update or update each item here.
    // For now, let's just update the first item's route, but we need to ensure the backend updates ALL items for this order/vendor.
    // Let's stick to the existing route which takes an orderItem.

    router.patch(route('vendor.orders.status', props.items[0].id), {
        status: newStatus.value,
        update_all: true // Signal to backend to update all items in this group
    }, {
        preserveScroll: true,
    });
};

const totalAmount = computed(() => {
    return props.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const totalCommission = computed(() => {
    return props.items.reduce((sum, item) => sum + Number(item.commission), 0);
});
</script>

<template>
    <Head :title="`Commande #${order.order_number} - AquawaSouk`" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <Link :href="route('vendor.orders')" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-brand-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Retour aux commandes
                        </Link>
                    </div>
                    <h2 class="font-display font-black text-2xl text-slate-900 dark:text-white tracking-tight mt-1">
                        Commande <span class="font-mono text-brand-600 dark:text-brand-400">#{{ order.order_number }}</span>
                    </h2>
                </div>

                <div class="flex items-center gap-2.5">
                    <a
                        :href="route('vendor.orders.waybill', items[0].id)"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/20 transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Bordereau Colis
                    </a>
                    <a
                        :href="route('vendor.orders.invoice', items[0].id)"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-slate-700 dark:text-slate-200 rounded-xl font-bold text-xs shadow-xs transition cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Facture
                    </a>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Order Overview Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Date d'achat</span>
                            <p class="text-sm sm:text-base font-bold text-slate-900 dark:text-white mt-1">
                                {{ new Date(order.created_at).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                            </p>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Client</span>
                            <p class="text-sm sm:text-base font-bold text-slate-900 dark:text-white mt-1">
                                {{ order.user.name }}
                            </p>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Statut Commande</span>
                            <div class="mt-1">
                                <span :class="['inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full ring-1 ring-inset', getStatusColor(order.status)]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ order.status }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Montant Total</span>
                            <p class="text-base sm:text-lg font-black text-brand-600 dark:text-brand-400 mt-0.5">
                                {{ formatPrice(totalAmount) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Product Items -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="font-display font-bold text-base text-slate-900 dark:text-white">Articles à expédier ({{ items.length }})</h3>
                        <span class="text-xs font-semibold text-slate-500">AquawaSouk Marketplace</span>
                    </div>

                    <div class="p-6 divide-y divide-slate-100 dark:divide-slate-800">
                        <div v-for="item in items" :key="item.id" class="flex flex-col sm:flex-row items-start sm:items-center gap-5 py-4 first:pt-0 last:pb-0">
                            <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-2xl overflow-hidden flex items-center justify-center shrink-0 border border-slate-200/70 dark:border-slate-700">
                                <img
                                    v-if="item.product.primary_image"
                                    :src="`/uploads/${item.product.primary_image.image_path}`"
                                    :alt="item.product.name"
                                    class="w-full h-full object-cover"
                                />
                                <svg v-else class="w-8 h-8 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-1">{{ item.product.name }}</h4>
                                <div v-if="item.size || item.color" class="flex flex-wrap gap-2 my-1.5">
                                    <span v-if="item.size" class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800">
                                        Taille : {{ item.size }}
                                    </span>
                                    <span v-if="item.color" class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">
                                        Couleur : {{ item.color }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-4 text-xs text-slate-600 dark:text-slate-400 mt-2">
                                    <span>Prix unit. : <strong class="text-slate-800 dark:text-slate-200">{{ formatPrice(item.price) }}</strong></span>
                                    <span>Quantité : <strong class="text-slate-800 dark:text-slate-200">× {{ item.quantity }}</strong></span>
                                    <span>Sous-total : <strong class="text-brand-600 dark:text-brand-400">{{ formatPrice(item.price * item.quantity) }}</strong></span>
                                </div>
                            </div>
                            <div>
                                <span :class="['inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg ring-1 ring-inset', getStatusColor(item.status)]">
                                    {{ item.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Totals ribbon -->
                    <div class="bg-slate-50/70 dark:bg-slate-800/40 px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-500 dark:text-slate-400">Total commande vendeur (TND)</span>
                        <div class="text-lg font-black text-slate-900 dark:text-white">
                            {{ formatPrice(totalAmount) }}
                        </div>
                    </div>
                </div>

                <!-- Update Status Form -->
                <div v-if="order.status !== 'delivered'" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-display font-bold text-base text-slate-900 dark:text-white mb-1">Mettre à jour le statut d'expédition</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Cette action notifie le client et met à jour le suivi de colis.</p>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 max-w-lg">
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nouveau Statut</label>
                            <select
                                v-model="newStatus"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            >
                                <option value="pending">En attente (Pending)</option>
                                <option value="processing">En préparation (Processing)</option>
                                <option value="shipped">Expédiée / Prise en charge livreur (Shipped)</option>
                                <option value="delivered">Livrée au client (Delivered)</option>
                                <option value="cancelled">Annulée (Cancelled)</option>
                            </select>
                        </div>
                        <button
                            @click="updateStatus"
                            class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-500/20 transition cursor-pointer"
                        >
                            Appliquer le statut
                        </button>
                    </div>
                </div>

                <!-- Shipping Address and Recipient -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-950/60 text-brand-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                            </div>
                            <h4 class="font-display font-bold text-sm text-slate-900 dark:text-white">Adresse de livraison</h4>
                        </div>
                        <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1">
                            <p class="font-bold text-slate-900 dark:text-white text-sm">{{ order.user.name }}</p>
                            <p>{{ order.shipping_address }}</p>
                            <p>{{ order.shipping_city }}, {{ order.shipping_postal_code }}</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ order.shipping_country }}</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <h4 class="font-display font-bold text-sm text-slate-900 dark:text-white">Contact & Paiement</h4>
                        </div>
                        <div class="text-xs text-slate-600 dark:text-slate-300 space-y-2">
                            <p class="flex items-center gap-2">
                                <span class="text-slate-400">Téléphone :</span>
                                <a :href="`tel:${order.phone}`" class="font-bold text-brand-600 hover:underline">{{ order.phone }}</a>
                            </p>
                            <p class="flex items-center gap-2">
                                <span class="text-slate-400">Email :</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ order.user.email }}</span>
                            </p>
                            <p class="flex items-center gap-2" v-if="order.payment_method">
                                <span class="text-slate-400">Mode de paiement :</span>
                                <span class="capitalize font-bold text-emerald-600 dark:text-emerald-400">{{ order.payment_method }}</span>
                            </p>
                        </div>
                        <div v-if="order.notes" class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs">
                            <span class="block text-slate-400 font-semibold mb-1">Notes du client :</span>
                            <p class="text-slate-600 dark:text-slate-300 italic">{{ order.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
