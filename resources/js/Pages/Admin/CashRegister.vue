<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    withdrawals: Object,
    vendors: Array,
    vendorSummaries: Array,
    recentOrderProfits: Array,
    caisseTotals: Object,
    filters: Object,
});

const activeTab = ref('withdrawals'); // 'withdrawals', 'orders', 'vendors'
const showWithdrawalModal = ref(false);

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

// Filter form for withdrawals
const filterForm = ref({
    vendor_id: props.filters?.vendor_id || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const applyFilters = () => {
    router.get(route('admin.cash-register'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filterForm.value = { vendor_id: '', date_from: '', date_to: '' };
    applyFilters();
};

// Withdrawal Form (Retrait caisse vendeur / encaissement profit admin)
const withdrawalForm = useForm({
    vendor_id: '',
    amount: '',
    payout_date: new Date().toISOString().split('T')[0],
    description: '',
});

const selectedVendorSummary = computed(() => {
    if (!withdrawalForm.vendor_id) return null;
    return props.vendorSummaries?.find(v => Number(v.id) === Number(withdrawalForm.vendor_id)) || null;
});

const openWithdrawalModal = (vendorId = '') => {
    withdrawalForm.reset();
    withdrawalForm.vendor_id = vendorId ? String(vendorId) : '';
    withdrawalForm.payout_date = new Date().toISOString().split('T')[0];
    
    // Auto-fill amount if vendor has remaining balance
    if (vendorId) {
        const v = props.vendorSummaries?.find(item => Number(item.id) === Number(vendorId));
        if (v && v.balance > 0) {
            withdrawalForm.amount = String(v.balance);
        }
    }
    
    showWithdrawalModal.value = true;
};

const fillFullBalance = () => {
    if (selectedVendorSummary.value && selectedVendorSummary.value.balance > 0) {
        withdrawalForm.amount = String(selectedVendorSummary.value.balance);
    }
};

const submitWithdrawal = () => {
    withdrawalForm.post(route('admin.payouts.store'), {
        onSuccess: () => {
            showWithdrawalModal.value = false;
            withdrawalForm.reset();
        },
    });
};

const deleteWithdrawal = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette ligne de retrait ? Le montant sera réintégré au reste à payer par le vendeur.')) {
        router.delete(route('admin.payouts.destroy', id), {
            preserveScroll: true,
        });
    }
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('action') === 'withdraw' || params.get('open_modal') === '1') {
        openWithdrawalModal(params.get('vendor_id') || '');
    }
});
</script>

<template>
    <Head title="Caisse Marge Profit Admin & Retraits Vendeurs - AquawaSouk" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold font-display text-slate-900 tracking-tight">
                        Caisse Marge Profit Admin & Retraits Vendeurs
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Les vendeurs encaissent le montant des commandes en COD (espèces à la livraison) et reversent la marge profit à l'admin. Enregistrez ici chaque retrait de caisse vendeur.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="openWithdrawalModal()"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        + Nouveau Retrait Caisse Vendeur
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Bannière explicative modèle COD & Caisse Vendeur -->
            <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-emerald-50 border border-blue-200/80 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-xs text-slate-700 space-y-1">
                    <p class="font-bold text-slate-900">
                        Règle de Caisse : Retrait Uniquement sur Commandes Livrées (Delivered) :
                    </p>
                    <p class="text-slate-600 leading-relaxed">
                        En paiement à la livraison (COD), le client ne règle la commande qu'à la réception. Par conséquent, <strong>la marge de profit admin n'est exigible et retirable que sur les commandes au statut "Livrée" (delivered)</strong>.
                        Tant qu'une commande est en attente, en cours de traitement ou en transit, son montant n'est pas encore encaissé par le vendeur et n'entre pas dans le solde retirable.
                    </p>
                </div>
            </div>

            <!-- 3 Cartes Majeures de la Caisse -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Entrées : Marge Profit Admin Totale (Commandes Livrées) -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">
                                Marge Profit Admin (Commandes Livrées)
                            </span>
                            <span class="text-3xl font-black font-display text-slate-900 mt-1 block">
                                {{ formatPrice(caisseTotals.total_admin_profit) }}
                            </span>
                            <span class="text-[11px] text-emerald-600 font-semibold mt-1 block">
                                &uarr; Encaissable uniquement sur colis livrés
                            </span>
                            <span v-if="caisseTotals.total_pending_profit > 0" class="inline-block mt-2 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                +{{ formatPrice(caisseTotals.total_pending_profit) }} en cours de livraison
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Sorties : Retraits Déjà Effectués -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">
                                Retraits Caisse Vendeurs (Déjà Versés)
                            </span>
                            <span class="text-3xl font-black font-display text-emerald-700 mt-1 block">
                                {{ formatPrice(caisseTotals.total_withdrawals) }}
                            </span>
                            <span class="text-[11px] text-slate-500 mt-1 block">
                                &check; Sommes déjà versées par les vendeurs à l'admin
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Solde Net Restant en Caisse Vendeurs -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-6 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300 block">
                                Reste Retirable (Commandes Livrées)
                            </span>
                            <span class="text-3xl font-black font-display text-amber-400 mt-1 block">
                                {{ formatPrice(caisseTotals.cash_balance) }}
                            </span>
                            <span class="text-[11px] text-slate-300 mt-1 block">
                                Bénéfices actuellement encaissables auprès des vendeurs
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-white/10 text-amber-400 flex items-center justify-center backdrop-blur-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex items-center gap-2 border-b border-slate-200">
                <button
                    @click="activeTab = 'withdrawals'"
                    :class="[
                        'px-5 py-3 text-xs font-bold transition border-b-2',
                        activeTab === 'withdrawals'
                            ? 'border-brand-600 text-brand-600'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    Journal des Retraits de la Caisse ({{ withdrawals.total || 0 }})
                </button>
                <button
                    @click="activeTab = 'orders'"
                    :class="[
                        'px-5 py-3 text-xs font-bold transition border-b-2',
                        activeTab === 'orders'
                            ? 'border-brand-600 text-brand-600'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    Commandes & Marges Encaissées ({{ recentOrderProfits?.length || 0 }})
                </button>
                <button
                    @click="activeTab = 'vendors'"
                    :class="[
                        'px-5 py-3 text-xs font-bold transition border-b-2',
                        activeTab === 'vendors'
                            ? 'border-brand-600 text-brand-600'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    Marges par Boutique Vendeur ({{ vendorSummaries?.length || 0 }})
                </button>
            </div>

            <!-- ONGLET 1 : Journal des Retraits Effectués -->
            <div v-if="activeTab === 'withdrawals'" class="space-y-4">
                <!-- Filtres -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <select
                            v-model="filterForm.vendor_id"
                            @change="applyFilters"
                            class="text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-1.5"
                        >
                            <option value="">Tous les retraits (Généraux & Vendeurs)</option>
                            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.shop_name }}</option>
                        </select>

                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <span>Du :</span>
                            <input
                                v-model="filterForm.date_from"
                                @change="applyFilters"
                                type="date"
                                class="text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-1.5"
                            />
                        </div>

                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <span>Au :</span>
                            <input
                                v-model="filterForm.date_to"
                                @change="applyFilters"
                                type="date"
                                class="text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-1.5"
                            />
                        </div>

                        <button
                            v-if="filterForm.vendor_id || filterForm.date_from || filterForm.date_to"
                            @click="resetFilters"
                            type="button"
                            class="text-xs text-slate-500 hover:text-slate-800 underline"
                        >
                            Réinitialiser
                        </button>
                    </div>

                    <button
                        @click="openWithdrawalModal()"
                        type="button"
                        class="px-3.5 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition"
                    >
                        + Nouveau Retrait
                    </button>
                </div>

                <!-- Table des retraits -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div v-if="!withdrawals.data || withdrawals.data.length === 0" class="p-12 text-center text-slate-400">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="text-sm font-semibold text-slate-600">Aucun retrait enregistré dans la caisse</p>
                        <p class="text-xs text-slate-400 mt-1">Cliquez sur "Nouveau Retrait" pour déduire un montant de la marge profit admin.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                    <th class="p-4">Date Retrait</th>
                                    <th class="p-4">Boutique Vendeur</th>
                                    <th class="p-4">Montant Versé à l'Admin</th>
                                    <th class="p-4">Motif / Référence</th>
                                    <th class="p-4">Validé par</th>
                                    <th class="p-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="w in withdrawals.data" :key="w.id" class="hover:bg-slate-50/70 transition">
                                    <td class="p-4 font-semibold text-slate-700 whitespace-nowrap">
                                        {{ formatDate(w.payout_date) }}
                                    </td>
                                    <td class="p-4 text-slate-700">
                                        <span v-if="w.vendor" class="font-bold text-slate-900 inline-flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            {{ w.vendor.shop_name }}
                                        </span>
                                        <span v-else class="text-slate-400 italic">Caisse Générale</span>
                                    </td>
                                    <td class="p-4 font-black font-display text-emerald-700 text-sm whitespace-nowrap">
                                        {{ formatPrice(w.amount) }}
                                    </td>
                                    <td class="p-4 text-slate-600">
                                        <span v-if="w.description" class="italic">{{ w.description }}</span>
                                        <span v-else class="text-slate-300">-</span>
                                    </td>
                                    <td class="p-4 text-slate-500 whitespace-nowrap">
                                        {{ w.creator?.name || 'Admin' }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <button
                                            @click="deleteWithdrawal(w.id)"
                                            type="button"
                                            class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition"
                                            title="Supprimer ce retrait et réintégrer la somme à la caisse"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="withdrawals.links && withdrawals.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center">
                        <nav class="inline-flex rounded-xl bg-white p-1 border border-slate-200/80 shadow-xs space-x-1">
                            <Link
                                v-for="(link, index) in withdrawals.links"
                                :key="index"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-xs font-semibold transition',
                                    link.active
                                        ? 'bg-brand-600 text-white'
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

            <!-- ONGLET 2 : Commandes & Marge Profit Admin Encaissée -->
            <div v-if="activeTab === 'orders'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Commandes Récentes & Marges Alimentant la Caisse</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Chaque commande prélève sa marge de profit admin selon les conditions configurées.</p>
                    </div>
                    <Link :href="route('admin.orders')" class="text-xs font-semibold text-brand-600 hover:underline">
                        Voir toutes les commandes &rarr;
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="p-4">N° Commande</th>
                                <th class="p-4">Date</th>
                                <th class="p-4">Client</th>
                                <th class="p-4">Boutique(s) Vendeur</th>
                                <th class="p-4 text-center">Statut Livraison (COD)</th>
                                <th class="p-4 text-right">Montant Commande</th>
                                <th class="p-4 text-right font-bold text-emerald-700">Marge Profit Admin</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="order in recentOrderProfits" :key="order.id" class="hover:bg-slate-50/70 transition">
                                <td class="p-4 font-bold text-slate-900">
                                    #{{ order.order_number }}
                                </td>
                                <td class="p-4 text-slate-500 whitespace-nowrap">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="p-4 text-slate-700">
                                    {{ order.customer_name }}
                                </td>
                                <td class="p-4 text-slate-600">
                                    {{ order.vendor_names || '-' }}
                                </td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <span v-if="order.is_delivered" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        Livrée (Encaissée)
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ order.status }} (Non encaissée)
                                    </span>
                                </td>
                                <td class="p-4 text-right font-semibold text-slate-900">
                                    {{ formatPrice(order.total_amount) }}
                                </td>
                                <td class="p-4 text-right">
                                    <span v-if="order.is_delivered" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black text-emerald-800 bg-emerald-50 border border-emerald-200" title="Encaissée dans la caisse">
                                        +{{ formatPrice(order.admin_profit) }}
                                    </span>
                                    <span v-else class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-medium text-slate-500 bg-slate-100 border border-slate-200" title="Cette marge sera encaissée une fois la commande marquée 'delivered'">
                                        {{ formatPrice(order.admin_profit) }} <span class="text-[9px] ml-1 text-slate-400">(En attente)</span>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <Link
                                        :href="route('admin.orders.show', order.id)"
                                        class="text-brand-600 hover:text-brand-800 font-semibold"
                                    >
                                        Détails
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ONGLET 3 : Marges par Boutique Vendeur -->
            <div v-if="activeTab === 'vendors'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Répartition des Marges Profit Admin par Boutique</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Consultez le total des bénéfices apportés par chaque vendeur et configurez leurs paliers en Dinars Tunisiens.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="p-4">Boutique / Vendeur</th>
                                <th class="p-4 text-right">Ventes Livrées (COD)</th>
                                <th class="p-4 text-right font-bold text-emerald-700">Marge Profit (Livrées)</th>
                                <th class="p-4 text-right">Déjà Versé à l'Admin</th>
                                <th class="p-4 text-right font-bold text-amber-600">Reste Retirable</th>
                                <th class="p-4 text-center">Paliers DT</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="v in vendorSummaries" :key="v.id" class="hover:bg-slate-50/70 transition">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-xs">
                                            {{ v.shop_name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs">{{ v.shop_name }}</p>
                                            <p class="text-[10px] text-slate-400">{{ v.owner_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-right font-semibold text-slate-800">
                                    {{ formatPrice(v.total_sales) }}
                                </td>
                                <td class="p-4 text-right">
                                    <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-black text-emerald-800 bg-emerald-50 border border-emerald-200">
                                        +{{ formatPrice(v.admin_profit) }}
                                    </span>
                                    <div v-if="v.pending_profit > 0" class="text-[10px] text-slate-400 mt-0.5">
                                        +{{ formatPrice(v.pending_profit) }} en cours
                                    </div>
                                </td>
                                <td class="p-4 text-right font-semibold text-slate-700">
                                    {{ formatPrice(v.total_payouts) }}
                                </td>
                                <td class="p-4 text-right font-bold text-sm">
                                    <span :class="v.balance > 0 ? 'text-amber-600' : 'text-slate-400'">
                                        {{ formatPrice(v.balance) }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span v-if="v.margins_count > 0" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ v.margins_count }} palier(s)
                                    </span>
                                    <span v-else class="text-[10px] text-slate-400 italic">
                                        Défaut
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            @click="openWithdrawalModal(v.id)"
                                            type="button"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition"
                                            title="Enregistrer un retrait / paiement pour ce vendeur"
                                        >
                                            + Retrait
                                        </button>
                                        <Link
                                            :href="route('admin.vendors.edit', v.id)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                                        >
                                            Paliers
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL : Enregistrer un Retrait de la Caisse Vendeur (Versement Profit Admin) -->
        <div v-if="showWithdrawalModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <h3 class="text-lg font-bold font-display text-slate-900">Retrait de Caisse Vendeur</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Enregistrer le versement de la marge profit effectué par le vendeur à l'admin.</p>
                    </div>
                    <button @click="showWithdrawalModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitWithdrawal" class="space-y-4">
                    <!-- Vendeur Concerné (Sélection Principale) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Boutique Vendeur qui règle le profit *
                        </label>
                        <select
                            v-model="withdrawalForm.vendor_id"
                            class="w-full text-xs font-semibold rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2.5"
                            required
                        >
                            <option value="" disabled>-- Sélectionner la boutique vendeur --</option>
                            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.shop_name }}</option>
                        </select>
                        <div v-if="withdrawalForm.errors.vendor_id" class="text-rose-500 text-xs mt-1">
                            {{ withdrawalForm.errors.vendor_id }}
                        </div>
                    </div>

                    <!-- Fiche situation financière en temps réel du vendeur -->
                    <div v-if="selectedVendorSummary" class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                            <span>Boutique : {{ selectedVendorSummary.shop_name }}</span>
                            <span class="text-[11px] font-semibold text-emerald-700">{{ selectedVendorSummary.owner_name }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-emerald-200/60 text-center">
                            <div class="bg-white p-2 rounded-xl shadow-2xs">
                                <span class="text-[10px] text-slate-400 block uppercase font-medium">Marge (Livrées)</span>
                                <span class="text-xs font-bold text-slate-800">{{ formatPrice(selectedVendorSummary.admin_profit) }}</span>
                                <span v-if="selectedVendorSummary.pending_profit > 0" class="text-[9px] text-slate-400 block mt-0.5">
                                    +{{ formatPrice(selectedVendorSummary.pending_profit) }} en cours
                                </span>
                            </div>
                            <div class="bg-white p-2 rounded-xl shadow-2xs">
                                <span class="text-[10px] text-slate-400 block uppercase font-medium">Déjà Réglé</span>
                                <span class="text-xs font-bold text-emerald-600">{{ formatPrice(selectedVendorSummary.total_payouts) }}</span>
                            </div>
                            <div class="bg-white p-2 rounded-xl shadow-2xs border border-amber-300">
                                <span class="text-[10px] text-amber-600 block uppercase font-bold">Reste Retirable</span>
                                <span class="text-xs font-black text-amber-700">{{ formatPrice(selectedVendorSummary.balance) }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-[10px] text-slate-500 italic">
                                * Retrait possible uniquement sur commandes livrées
                            </span>
                            <button
                                v-if="selectedVendorSummary.balance > 0"
                                type="button"
                                @click="fillFullBalance"
                                class="text-[11px] font-bold text-emerald-700 hover:text-emerald-900 bg-white px-2.5 py-1 rounded-lg border border-emerald-300 shadow-2xs hover:bg-emerald-50 transition"
                            >
                                &rarr; Solder le reste dû ({{ formatPrice(selectedVendorSummary.balance) }})
                            </button>
                        </div>
                    </div>

                    <!-- Montant du retrait -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Montant Retiré / Versé à l'Admin (TND) *
                        </label>
                        <div class="relative">
                            <input
                                v-model="withdrawalForm.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                placeholder="0.00"
                                class="w-full text-sm font-black rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 pl-4 pr-14 text-slate-900"
                                required
                            />
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs font-bold text-slate-400">
                                TND
                            </div>
                        </div>
                        <div v-if="withdrawalForm.errors.amount" class="text-rose-500 text-xs mt-1">
                            {{ withdrawalForm.errors.amount }}
                        </div>
                    </div>

                    <!-- Date du retrait -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Date du Versement / Retrait *
                        </label>
                        <input
                            v-model="withdrawalForm.payout_date"
                            type="date"
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2.5"
                            required
                        />
                        <div v-if="withdrawalForm.errors.payout_date" class="text-rose-500 text-xs mt-1">
                            {{ withdrawalForm.errors.payout_date }}
                        </div>
                    </div>

                    <!-- Description / Motif (Nullable) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Motif / Référence du Versement (Facultatif)
                        </label>
                        <textarea
                            v-model="withdrawalForm.description"
                            rows="2"
                            placeholder="Ex: Versement en espèces en main propre, virement bancaire réf #4920, règlement hebdomadaire..."
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2"
                        ></textarea>
                        <div v-if="withdrawalForm.errors.description" class="text-rose-500 text-xs mt-1">
                            {{ withdrawalForm.errors.description }}
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button
                            @click="showWithdrawalModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            :disabled="withdrawalForm.processing"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition disabled:opacity-50 shadow-sm"
                        >
                            <span v-if="withdrawalForm.processing">Enregistrement...</span>
                            <span v-else>Valider le Retrait Caisse Vendeur</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
