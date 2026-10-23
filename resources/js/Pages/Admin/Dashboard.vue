<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    stats: Object,
    recentOrders: Array,
    recentProducts: Array,
    vendorFinancials: Array,
    vendors: Array,
});

const showWithdrawalModal = ref(false);

const withdrawalForm = useForm({
    vendor_id: '',
    amount: '',
    payout_date: new Date().toISOString().split('T')[0],
    description: '',
});

const selectedVendorSummary = computed(() => {
    if (!withdrawalForm.vendor_id) return null;
    return props.vendorFinancials?.find(v => Number(v.id) === Number(withdrawalForm.vendor_id)) || null;
});

const openWithdrawalModal = (vendorId = '') => {
    withdrawalForm.reset();
    withdrawalForm.vendor_id = vendorId ? String(vendorId) : '';
    withdrawalForm.payout_date = new Date().toISOString().split('T')[0];

    if (vendorId) {
        const v = props.vendorFinancials?.find(item => Number(item.id) === Number(vendorId));
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
</script>

<template>
    <Head title="Tableau de Bord Admin - AquawaSouk" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold font-display text-slate-900 tracking-tight">
                        Tableau de Bord Administrateur
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Vue d'ensemble des ventes, des profits admin par vendeur et de la caisse.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.cash-register')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Ouvrir la Caisse & Retraits
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Top Financial & Operational KPIs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Chiffre d'affaires total -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Volume Total des Ventes</p>
                    <p class="text-2xl font-black font-display text-slate-900 mt-1">
                        {{ formatPrice(stats.total_revenue) }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ stats.total_orders }} commande(s) enregistrée(s)</p>
                </div>

                <!-- Profit Total Admin (Marge en TND) -->
                <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-2xl p-5 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-100">Profit Total Admin</p>
                    <p class="text-2xl font-black font-display text-white mt-1">
                        {{ formatPrice(stats.total_admin_profit) }}
                    </p>
                    <p class="text-[11px] text-emerald-100 mt-0.5">Marges prélevées en Dinars Tunisiens</p>
                </div>

                <!-- Retraits Vendeurs Sortis -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Retraits Caisse Vendeurs Encaissés</p>
                    <p class="text-2xl font-black font-display text-emerald-700 mt-1">
                        {{ formatPrice(stats.total_vendor_payouts) }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Profits déjà versés par les vendeurs</p>
                </div>

                <!-- Solde Restant en Caisse pour les Vendeurs -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Reste à Encaisser auprès des Vendeurs</p>
                    <p :class="['text-2xl font-black font-display mt-1', stats.total_vendor_balance > 0 ? 'text-amber-600' : 'text-slate-700']">
                        {{ formatPrice(stats.total_vendor_balance) }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">En attente de versement par les vendeurs (COD)</p>
                </div>
            </div>

            <!-- SECTION : PROFIT TOTAL DE CHAQUE VENDEUR -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <h2 class="text-base font-bold text-slate-900">
                                Profit Total Admin par Vendeur (Marge en Dinars Tunisiens)
                            </h2>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            Calcul automatique des marges selon les tranches de prix et gestion des retraits de caisse pour chaque boutique.
                        </p>
                    </div>

                    <Link
                        :href="route('admin.cash-register')"
                        class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline flex items-center gap-1"
                    >
                        Gérer les retraits de caisse &rarr;
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="p-4">Vendeur / Boutique</th>
                                <th class="p-4 text-right">Ventes Livrées (COD)</th>
                                <th class="p-4 text-right">Marge Due (Livrées)</th>
                                <th class="p-4 text-right">Part Vendeur</th>
                                <th class="p-4 text-right">Déjà Versé à l'Admin</th>
                                <th class="p-4 text-right">Reste Retirable</th>
                                <th class="p-4 text-center">Paliers Configurés</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="vendor in vendorFinancials" :key="vendor.id" class="hover:bg-slate-50/70 transition">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-50 to-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs border border-brand-200">
                                            {{ vendor.shop_name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs">{{ vendor.shop_name }}</p>
                                            <p class="text-[10px] text-slate-400">{{ vendor.owner_name }} &bull; {{ vendor.owner_email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-right font-semibold text-slate-800">
                                    {{ formatPrice(vendor.total_sales) }}
                                </td>
                                <td class="p-4 text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black text-emerald-800 bg-emerald-50 border border-emerald-200 shadow-2xs">
                                        +{{ formatPrice(vendor.admin_profit) }}
                                    </span>
                                    <div v-if="vendor.pending_profit > 0" class="text-[10px] text-slate-400 mt-0.5">
                                        +{{ formatPrice(vendor.pending_profit) }} en cours
                                    </div>
                                </td>
                                <td class="p-4 text-right font-medium text-slate-600">
                                    {{ formatPrice(vendor.vendor_earnings) }}
                                </td>
                                <td class="p-4 text-right font-medium text-slate-600">
                                    {{ formatPrice(vendor.total_payouts) }}
                                </td>
                                <td class="p-4 text-right">
                                    <span :class="['font-bold text-sm', vendor.balance > 0 ? 'text-amber-600' : 'text-slate-400']">
                                        {{ formatPrice(vendor.balance) }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span v-if="vendor.margins_count > 0" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ vendor.margins_count }} palier(s) DT
                                    </span>
                                    <span v-else class="text-[10px] text-slate-400 italic">
                                        Commission % par défaut
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            @click="openWithdrawalModal(vendor.id)"
                                            type="button"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition shadow-2xs"
                                            title="Enregistrer un retrait de caisse pour ce vendeur"
                                        >
                                            + Retrait
                                        </button>
                                        <Link
                                            :href="route('admin.vendors.edit', vendor.id)"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                                        >
                                            Marges DT
                                        </Link>
                                        <Link
                                            :href="route('admin.cash-register', { vendor_id: vendor.id })"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 transition"
                                        >
                                            Caisse
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Navigation & Secondary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link :href="route('admin.vendors')" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Vendeurs</p>
                            <p class="text-lg font-bold text-slate-900 mt-1">{{ stats.active_vendors }} Actifs / {{ stats.total_vendors }} Total</p>
                            <p class="text-xs text-brand-600 mt-1 font-semibold">Gérer les boutiques &rarr;</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                    </div>
                </Link>

                <Link :href="route('admin.products')" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Produits</p>
                            <p class="text-lg font-bold text-slate-900 mt-1">{{ stats.total_products }} Références</p>
                            <p class="text-xs text-brand-600 mt-1 font-semibold">Gérer le catalogue &rarr;</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        </div>
                    </div>
                </Link>

                <Link :href="route('admin.orders')" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Commandes</p>
                            <p class="text-lg font-bold text-slate-900 mt-1">{{ stats.total_orders }} Commandes</p>
                            <p class="text-xs text-brand-600 mt-1 font-semibold">Voir toutes les commandes &rarr;</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Recent Orders & Recent Products -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Orders -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-sm text-slate-900">Dernières Commandes</h3>
                        <Link :href="route('admin.orders')" class="text-xs font-semibold text-brand-600 hover:underline">Voir tout</Link>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="order in recentOrders?.slice(0, 5)" :key="order.id" class="py-3 flex justify-between items-center first:pt-0 last:pb-0">
                            <div>
                                <p class="font-bold text-xs text-slate-900">#{{ order.order_number }}</p>
                                <p class="text-[11px] text-slate-400">{{ order.user?.name }} &bull; {{ formatDate(order.created_at) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-xs font-display text-slate-900">{{ formatPrice(order.total_amount) }}</p>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-700">
                                    {{ order.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Products -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-sm text-slate-900">Nouveaux Produits</h3>
                        <Link :href="route('admin.products')" class="text-xs font-semibold text-brand-600 hover:underline">Voir tout</Link>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="product in recentProducts?.slice(0, 5)" :key="product.id" class="py-3 flex justify-between items-center first:pt-0 last:pb-0">
                            <div>
                                <p class="font-bold text-xs text-slate-900">{{ product.name }}</p>
                                <p class="text-[11px] text-slate-400">Vendeur : {{ product.vendor?.shop_name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-xs font-display text-slate-900">{{ formatPrice(product.price) }}</p>
                                <span class="text-[10px] text-slate-400">Stock : {{ product.stock }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL : Nouveau Retrait de Caisse Vendeur (Encaissement Profit Admin) -->
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
                    <!-- Boutique Vendeur -->
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

                    <!-- Fiche Situation Vendeur -->
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
                                * Retrait uniquement sur commandes livrées
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

                    <!-- Montant -->
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

                    <!-- Date -->
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

                    <!-- Description / Motif -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Motif / Référence du Versement (Facultatif)
                        </label>
                        <textarea
                            v-model="withdrawalForm.description"
                            rows="2"
                            placeholder="Ex: Versement en espèces en main propre, virement bancaire réf #..., règlement hebdomadaire..."
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2"
                        ></textarea>
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
