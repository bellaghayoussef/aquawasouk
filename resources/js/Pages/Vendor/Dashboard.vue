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

const getStatusBadge = (status) => {
    switch (status) {
        case 'delivered':
            return { label: 'Livrée', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
        case 'shipped':
            return { label: 'Expédiée', class: 'bg-sky-50 text-sky-700 border-sky-200' };
        case 'processing':
            return { label: 'En cours', class: 'bg-indigo-50 text-indigo-700 border-indigo-200' };
        case 'cancelled':
            return { label: 'Annulée', class: 'bg-rose-50 text-rose-700 border-rose-200' };
        default:
            return { label: 'En attente', class: 'bg-amber-50 text-amber-700 border-amber-200' };
    }
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
    <Head title="Tableau de bord Vendeur - AquawaSouk" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <h2 class="font-black text-lg sm:text-2xl font-display text-slate-900 truncate">
                            {{ vendor.shop_name }}
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 truncate">
                        Tableau de bord vendeur • Suivi des commandes, ventes & caisse
                    </p>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <a v-if="vendor.slug"
                       :href="'/vendors/' + vendor.slug"
                       target="_blank"
                       class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Voir boutique</span>
                    </a>

                    <button @click="shareShopOnFacebook"
                            type="button"
                            class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95">
                        <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Partager FB</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- 1. Statistics Grid (Mobile 2 cols, Desktop 5 cols) -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                <!-- Ventes Totales -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 sm:p-5 shadow-2xs">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-slate-500 font-semibold truncate">Ventes Totales</p>
                            <p class="text-base sm:text-xl lg:text-2xl font-black font-display text-slate-900 mt-1 truncate">
                                {{ formatPrice(stats.total_sales) }}
                            </p>
                        </div>
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.51-1.31c-.562-.649-1.413-1.076-2.353-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Commandes -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 sm:p-5 shadow-2xs">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-slate-500 font-semibold truncate">Commandes</p>
                            <p class="text-base sm:text-xl lg:text-2xl font-black font-display text-slate-900 mt-1 truncate">
                                {{ stats.total_orders }}
                            </p>
                        </div>
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- En Attente -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 sm:p-5 shadow-2xs">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-slate-500 font-semibold truncate">En Attente</p>
                            <p class="text-base sm:text-xl lg:text-2xl font-black font-display text-amber-600 mt-1 truncate">
                                {{ stats.pending_orders }}
                            </p>
                        </div>
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Produits -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 sm:p-5 shadow-2xs">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-slate-500 font-semibold truncate">Mes Produits</p>
                            <p class="text-base sm:text-xl lg:text-2xl font-black font-display text-slate-900 mt-1 truncate">
                                {{ stats.total_products }}
                            </p>
                        </div>
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Vues Produits (Full width on mobile 2-col layout) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 sm:p-5 shadow-2xs col-span-2 lg:col-span-1">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-slate-500 font-semibold truncate">Vues Produits</p>
                            <p class="text-base sm:text-xl lg:text-2xl font-black font-display text-sky-600 mt-1 truncate">
                                {{ (stats.total_views || 0).toLocaleString('fr-FR') }}
                            </p>
                        </div>
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Caisse Vendeur & Marges Profit Admin (COD) -->
            <div v-if="financials" class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/80 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">
                                Caisse Vendeur & Marges Profit (COD)
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Vous encaissez l'argent en paiement à la livraison (COD) et reversez la marge de profit convenue à l'administrateur.
                        </p>
                    </div>
                    <div class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 text-[11px] font-bold text-slate-700">
                        Devise : TND
                    </div>
                </div>

                <!-- 4 Financial Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Total Encaissé -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 block">
                            Ventes Livrées Encaissées
                        </span>
                        <span class="text-xl sm:text-2xl font-black font-display text-slate-900 mt-1 block">
                            {{ formatPrice(financials.total_sales) }}
                        </span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">
                            Total encaissé sur colis livrés
                        </span>
                    </div>

                    <!-- Marge Profit Due -->
                    <div class="bg-amber-50/70 p-4 rounded-2xl border border-amber-200/80">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-amber-700 block">
                            Marge Profit Due (Livrées)
                        </span>
                        <span class="text-xl sm:text-2xl font-black font-display text-amber-900 mt-1 block">
                            {{ formatPrice(financials.admin_profit_due) }}
                        </span>
                        <span v-if="financials.pending_profit > 0" class="text-[11px] text-amber-700 mt-0.5 block font-medium">
                            +{{ formatPrice(financials.pending_profit) }} en cours de livraison
                        </span>
                        <span v-else class="text-[11px] text-amber-700 mt-0.5 block">
                            Part plateforme sur livrées
                        </span>
                    </div>

                    <!-- Déjà Versé à l'Admin -->
                    <div class="bg-emerald-50/70 p-4 rounded-2xl border border-emerald-200/80">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-emerald-700 block">
                            Déjà Versé à l'Admin
                        </span>
                        <span class="text-xl sm:text-2xl font-black font-display text-emerald-900 mt-1 block">
                            {{ formatPrice(financials.total_paid_to_admin) }}
                        </span>
                        <span class="text-[11px] text-emerald-700 mt-0.5 block">
                            Retraits validés par l'admin
                        </span>
                    </div>

                    <!-- Reste à Payer à l'Admin -->
                    <div :class="[
                        'p-4 rounded-2xl border',
                        financials.remaining_due_to_admin > 0 
                            ? 'bg-rose-50/80 border-rose-200' 
                            : 'bg-slate-50 border-slate-200/60'
                    ]">
                        <span :class="[
                            'text-[10px] sm:text-[11px] font-bold uppercase tracking-wider block',
                            financials.remaining_due_to_admin > 0 ? 'text-rose-700' : 'text-slate-400'
                        ]">
                            Reste à Payer à l'Admin
                        </span>
                        <span :class="[
                            'text-xl sm:text-2xl font-black font-display mt-1 block',
                            financials.remaining_due_to_admin > 0 ? 'text-rose-700' : 'text-slate-800'
                        ]">
                            {{ formatPrice(financials.remaining_due_to_admin) }}
                        </span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">
                            {{ financials.remaining_due_to_admin > 0 ? 'Montant à régler à l\'admin' : 'À jour (Aucun impayé)' }}
                        </span>
                    </div>
                </div>

                <!-- Payout History Table (Horizontal scroll safe on mobile) -->
                <div v-if="payoutHistory && payoutHistory.length > 0" class="pt-4 border-t border-slate-100">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                        Historique des Versements Enregistrés par l'Admin
                    </h4>
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="w-full text-left text-xs border-collapse min-w-[480px]">
                            <thead>
                                <tr class="text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                                    <th class="py-2.5 pr-4">Date</th>
                                    <th class="py-2.5 px-4">Montant Versé</th>
                                    <th class="py-2.5 px-4">Motif / Description</th>
                                    <th class="py-2.5 pl-4 text-right">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="payout in payoutHistory" :key="payout.id" class="text-slate-700">
                                    <td class="py-2.5 pr-4 font-semibold whitespace-nowrap">{{ formatDate(payout.payout_date) }}</td>
                                    <td class="py-2.5 px-4 font-bold text-emerald-600">{{ formatPrice(payout.amount) }}</td>
                                    <td class="py-2.5 px-4 text-slate-500">{{ payout.description || 'Paiement marge profit admin' }}</td>
                                    <td class="py-2.5 pl-4 text-right">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Validé par Admin
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. Quick Actions Cards (Fully responsive) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <Link :href="route('vendor.products')" class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md hover:border-brand-500/40 transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="text-xs text-brand-600 font-bold group-hover:translate-x-0.5 transition">&rarr;</span>
                    </div>
                    <h3 class="font-bold text-sm sm:text-base text-slate-900">Mes Produits</h3>
                    <p class="text-xs text-slate-500 mt-1">Ajouter, modifier vos prix, stock ou variations</p>
                </Link>

                <Link :href="route('vendor.orders')" class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md hover:border-blue-500/40 transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="text-xs text-blue-600 font-bold group-hover:translate-x-0.5 transition">&rarr;</span>
                    </div>
                    <h3 class="font-bold text-sm sm:text-base text-slate-900">Commandes Reçues</h3>
                    <p class="text-xs text-slate-500 mt-1">Suivre les livraisons et statuts de commandes</p>
                </Link>

                <Link :href="route('vendor.settings')" class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md hover:border-purple-500/40 transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs text-purple-600 font-bold group-hover:translate-x-0.5 transition">&rarr;</span>
                    </div>
                    <h3 class="font-bold text-sm sm:text-base text-slate-900">Paramètres Boutique</h3>
                    <p class="text-xs text-slate-500 mt-1">Logo, coordonnées, frais de port & politique</p>
                </Link>
            </div>

            <!-- 4. Recent Orders List -->
            <div class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-base sm:text-lg text-slate-900">Dernières Commandes</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Articles récemment commandés par vos clients</p>
                    </div>
                    <Link :href="route('vendor.orders')" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">
                        Tout voir &rarr;
                    </Link>
                </div>

                <div v-if="recentOrders && recentOrders.length > 0" class="divide-y divide-slate-100">
                    <div v-for="item in recentOrders" :key="item.id" class="py-3 flex items-center justify-between gap-3 hover:bg-slate-50/50 -mx-2 px-2 rounded-xl transition">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-700 shrink-0">
                                    #{{ item.order?.order_number || item.order_id }}
                                </span>
                                <p class="text-xs sm:text-sm font-semibold text-slate-800 truncate">
                                    {{ item.product?.name || 'Produit' }}
                                </p>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Qté : {{ item.quantity }} • Client : {{ item.order?.user?.name || 'Acheteur' }}
                            </p>
                        </div>

                        <div class="text-right shrink-0">
                            <p class="text-xs sm:text-sm font-bold text-slate-900">{{ formatPrice(item.price) }}</p>
                            <span :class="[
                                'inline-block text-[10px] font-bold px-2 py-0.5 rounded-full border mt-1',
                                getStatusBadge(item.status).class
                            ]">
                                {{ getStatusBadge(item.status).label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-xl">
                        📦
                    </div>
                    <p class="text-xs font-bold text-slate-700">Aucune commande pour le moment</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Vos nouvelles commandes apparaîtront automatiquement ici</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
