<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    vendor: Object,
    stats: Object,
    financials: Object,
    payoutHistory: Array,
    recentOrders: Array,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-TN', { 
        style: 'currency', 
        currency: 'TND', 
        minimumFractionDigits: 2,
    }).format(price || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-TN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const shareShopOnFacebook = () => {
    const shopSlug = props.vendor?.slug || '';
    const shopUrl = window.location.origin + '/vendors/' + shopSlug;
    const shareText = `Découvrez notre boutique "${props.vendor?.shop_name || 'Boutique'}" sur AquawaSouk !`;
    const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shopUrl)}&quote=${encodeURIComponent(shareText)}`;
    window.open(fbUrl, '_blank', 'width=620,height=580,scrollbars=yes,resizable=yes');
};
</script>

<template>
    <Head title="Vendor Dashboard" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ vendor.shop_name }} Dashboard
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Gérez vos ventes, commandes et partagez votre boutique sur vos réseaux
                    </p>
                </div>
                <div class="flex items-center gap-2.5">
                    <a v-if="vendor.slug"
                       :href="'/vendors/' + vendor.slug"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl shadow-xs transition">
                        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Voir ma boutique</span>
                    </a>
                    <button @click="shareShopOnFacebook"
                            type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition transform active:scale-95">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Partager sur Facebook</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistics -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Ventes Totales</p>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ formatPrice(stats.total_sales) }}
                                </p>
                            </div>
                            <div class="p-2.5 bg-green-100 dark:bg-green-900/60 rounded-full shrink-0">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.51-1.31c-.562-.649-1.413-1.076-2.353-1.253V5z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Commandes</p>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total_orders }}</p>
                            </div>
                            <div class="p-2.5 bg-blue-100 dark:bg-blue-900/60 rounded-full shrink-0">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">En Attente</p>
                                <p class="text-xl sm:text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ stats.pending_orders }}</p>
                            </div>
                            <div class="p-2.5 bg-amber-100 dark:bg-amber-900/60 rounded-full shrink-0">
                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Produits</p>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total_products }}</p>
                            </div>
                            <div class="p-2.5 bg-purple-100 dark:bg-purple-900/60 rounded-full shrink-0">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 col-span-2 sm:col-span-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Vues Produits</p>
                                <p class="text-xl sm:text-2xl font-bold text-sky-600 dark:text-sky-400 mt-1">
                                    {{ (stats.total_views || 0).toLocaleString('fr-FR') }}
                                </p>
                            </div>
                            <div class="p-2.5 bg-sky-100 dark:bg-sky-900/60 rounded-full shrink-0">
                                <svg class="w-6 h-6 text-sky-600 dark:text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Caisse Vendeur & Marges Profit Admin (COD) -->
                <div v-if="financials" class="mb-8 space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-slate-200/80 dark:border-gray-700 shadow-xs">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-gray-700">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                        Caisse Vendeur & Marges Profit Admin
                                    </h3>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">
                                    En paiement à la livraison (COD), vous encaissez l'argent des clients. Vous reversez la marge de profit convenue à l'administrateur. L'admin enregistre ces versements sous forme de retraits.
                                </p>
                            </div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Devise : Dinar Tunisien (TND)
                            </div>
                        </div>

                        <!-- 4 Cartes Financières -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
                            <!-- Total Encaissé par Vendeur -->
                            <div class="bg-slate-50 dark:bg-gray-700/50 p-4 rounded-xl border border-slate-100 dark:border-gray-600">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-400 block">
                                    Ventes Livrées Encaissées
                                </span>
                                <span class="text-2xl font-black font-display text-slate-900 dark:text-white mt-1 block">
                                    {{ formatPrice(financials.total_sales) }}
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-gray-400 mt-0.5 block">
                                    Total encaissé sur colis livrés
                                </span>
                            </div>

                            <!-- Marge Profit Due à l'Admin -->
                            <div class="bg-amber-50/60 dark:bg-amber-950/20 p-4 rounded-xl border border-amber-100 dark:border-amber-900/40">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 block">
                                    Marge Profit Due (Livrées)
                                </span>
                                <span class="text-2xl font-black font-display text-amber-800 dark:text-amber-300 mt-1 block">
                                    {{ formatPrice(financials.admin_profit_due) }}
                                </span>
                                <span v-if="financials.pending_profit > 0" class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 block">
                                    +{{ formatPrice(financials.pending_profit) }} en cours de livraison
                                </span>
                                <span v-else class="text-[11px] text-amber-600/90 dark:text-amber-400/80 mt-0.5 block">
                                    Part plateforme sur commandes livrées
                                </span>
                            </div>

                            <!-- Déjà Réglé / Retiré par l'Admin -->
                            <div class="bg-emerald-50/60 dark:bg-emerald-950/20 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/40">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block">
                                    Déjà Versé à l'Admin
                                </span>
                                <span class="text-2xl font-black font-display text-emerald-800 dark:text-emerald-300 mt-1 block">
                                    {{ formatPrice(financials.total_paid_to_admin) }}
                                </span>
                                <span class="text-[11px] text-emerald-600/90 dark:text-emerald-400/80 mt-0.5 block">
                                    Retraits validés par l'admin
                                </span>
                            </div>

                            <!-- Reste à Payer à l'Admin -->
                            <div :class="[
                                'p-4 rounded-xl border',
                                financials.remaining_due_to_admin > 0 
                                    ? 'bg-rose-50/70 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/50' 
                                    : 'bg-slate-50 dark:bg-gray-700/50 border-slate-200 dark:border-gray-600'
                            ]">
                                <span :class="[
                                    'text-[11px] font-bold uppercase tracking-wider block',
                                    financials.remaining_due_to_admin > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-slate-400'
                                ]">
                                    Reste à Payer à l'Admin
                                </span>
                                <span :class="[
                                    'text-2xl font-black font-display mt-1 block',
                                    financials.remaining_due_to_admin > 0 ? 'text-rose-700 dark:text-rose-300' : 'text-slate-700 dark:text-slate-300'
                                ]">
                                    {{ formatPrice(financials.remaining_due_to_admin) }}
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-gray-400 mt-0.5 block">
                                    {{ financials.remaining_due_to_admin > 0 ? 'À régler à l\'administrateur' : 'À jour (Aucun impayé)' }}
                                </span>
                            </div>
                        </div>

                        <!-- Historique des retraits / règlements validés par l'admin -->
                        <div v-if="payoutHistory && payoutHistory.length > 0" class="mt-6 pt-5 border-t border-slate-100 dark:border-gray-700">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-3">
                                Historique des Retraits & Versements Enregistrés par l'Admin
                            </h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100 dark:border-gray-700">
                                            <th class="py-2 pr-4">Date</th>
                                            <th class="py-2 px-4">Montant Versé</th>
                                            <th class="py-2 px-4">Motif / Description</th>
                                            <th class="py-2 pl-4 text-right">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                                        <tr v-for="payout in payoutHistory" :key="payout.id" class="text-slate-700 dark:text-slate-300">
                                            <td class="py-2.5 pr-4 font-semibold whitespace-nowrap">{{ formatDate(payout.payout_date) }}</td>
                                            <td class="py-2.5 px-4 font-bold text-emerald-600 dark:text-emerald-400">{{ formatPrice(payout.amount) }}</td>
                                            <td class="py-2.5 px-4 text-slate-500 dark:text-gray-400">{{ payout.description || 'Paiement marge profit admin' }}</td>
                                            <td class="py-2.5 pl-4 text-right">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                                    Validé par Admin
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <Link :href="route('vendor.products')" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-white mb-2">Manage Products</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Add, edit, or remove your products</p>
                    </Link>

                    <Link :href="route('vendor.orders')" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-white mb-2">View Orders</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Track and fulfill your orders</p>
                    </Link>

                    <Link :href="route('vendor.settings')" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition">
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-white mb-2">Shop Settings</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Update your shop profile and settings</p>
                    </Link>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-white mb-4">Recent Orders</h3>
                        <div class="space-y-3">
                            <div v-for="item in recentOrders" :key="item.id" class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Order #{{ item.order.order_number }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ item.product.name }} (x{{ item.quantity }})</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ formatPrice(item.price) }}</p>
                                    <span :class="[
                                        'text-xs px-2 py-1 rounded-full',
                                        item.status === 'delivered' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                    ]">
                                        {{ item.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
