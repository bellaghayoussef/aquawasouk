<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    vendor: Object,
    financials: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-TN', {
        style: 'currency',
        currency: 'TND',
        minimumFractionDigits: 2,
    }).format(price || 0);
};

// General Vendor Info Form
const generalForm = useForm({
    shop_name: props.vendor.shop_name,
    description: props.vendor.description || '',
    address: props.vendor.address || '',
    city: props.vendor.city || '',
    country: props.vendor.country || 'Tunisie',
    phone: props.vendor.phone || '',
    status: props.vendor.status,
    shipping_price: props.vendor.shipping_price || 7.00,
});

const submitGeneral = () => {
    generalForm.patch(route('admin.vendors.update', props.vendor.id));
};

// Margins Form
const marginRules = ref(
    props.vendor.profit_margins && props.vendor.profit_margins.length > 0
        ? props.vendor.profit_margins.map(m => ({
            id: m.id,
            min_order_price: m.min_order_price,
            max_order_price: m.max_order_price ?? '',
            margin_amount: m.margin_amount,
        }))
        : [
            { min_order_price: 0, max_order_price: 50, margin_amount: 5 },
            { min_order_price: 50.01, max_order_price: 150, margin_amount: 10 },
            { min_order_price: 150.01, max_order_price: '', margin_amount: 15 },
        ]
);

const marginsForm = useForm({
    commission_rate: props.vendor.commission_rate || 10,
    margins: [],
});

const addMarginRow = () => {
    const lastRow = marginRules.value[marginRules.value.length - 1];
    const nextMin = lastRow && lastRow.max_order_price ? parseFloat(lastRow.max_order_price) + 0.01 : 0;
    marginRules.value.push({
        min_order_price: nextMin,
        max_order_price: '',
        margin_amount: 5,
    });
};

const removeMarginRow = (index) => {
    marginRules.value.splice(index, 1);
};

const submitMargins = () => {
    marginsForm.margins = marginRules.value;
    marginsForm.post(route('admin.vendors.margins', props.vendor.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Modifier ${vendor.shop_name} - AquawaSouk`" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold font-display text-slate-900 tracking-tight">
                            Boutique : {{ vendor.shop_name }}
                        </h1>
                        <span
                            :class="[
                                'inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full border',
                                vendor.status === 'active'
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                    : vendor.status === 'pending'
                                        ? 'bg-amber-50 text-amber-700 border-amber-200'
                                        : 'bg-rose-50 text-rose-700 border-rose-200'
                            ]"
                        >
                            {{ vendor.status.toUpperCase() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Propriétaire : <strong>{{ vendor.user?.name }}</strong> ({{ vendor.user?.email }})
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.cash-register', { vendor_id: vendor.id })"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 rounded-xl transition"
                    >
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Voir Caisse & Retraits
                    </Link>
                    <Link
                        :href="route('admin.vendors')"
                        class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl transition"
                    >
                        &larr; Retour Vendeurs
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Financial Snapshot of this Vendor -->
            <div v-if="financials" class="grid grid-cols-2 sm:grid-cols-5 gap-3 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Ventes Brutes</span>
                    <span class="text-base font-bold text-slate-900 font-display mt-0.5 block">
                        {{ formatPrice(financials.total_sales) }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Profit Admin (Marge)</span>
                    <span class="text-base font-bold text-emerald-600 font-display mt-0.5 block">
                        +{{ formatPrice(financials.admin_profit) }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Part Vendeur</span>
                    <span class="text-base font-bold text-slate-700 font-display mt-0.5 block">
                        {{ formatPrice(financials.net_earnings) }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Retraits Sortis</span>
                    <span class="text-base font-bold text-slate-700 font-display mt-0.5 block">
                        {{ formatPrice(financials.total_payouts) }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 block">Solde Restant Dû</span>
                    <span :class="['text-base font-black font-display mt-0.5 block', financials.balance > 0 ? 'text-amber-600' : 'text-slate-500']">
                        {{ formatPrice(financials.balance) }}
                    </span>
                </div>
            </div>

            <!-- SECTION 1: MARGES DE PROFIT ADMIN PAR CONDITIONS DE PRIX -->
            <div class="bg-white rounded-2xl border border-brand-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-brand-50 to-white border-b border-brand-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>
                            <h2 class="text-base font-bold text-slate-900">
                                Marge de Profit Admin (en Dinar Tunisien - TND)
                            </h2>
                        </div>
                        <p class="text-xs text-slate-600 mt-1">
                            Définissez les conditions de marge prélevée par l'administrateur selon le montant (prix) de la commande en Dinars Tunisiens (TND).
                        </p>
                    </div>

                    <button
                        @click="addMarginRow"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-brand-700 text-xs font-bold rounded-lg border border-brand-300 hover:bg-brand-50 shadow-xs transition self-start sm:self-auto"
                    >
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Ajouter une condition
                    </button>
                </div>

                <form @submit.prevent="submitMargins" class="p-6 space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200/80 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                    <th class="p-3"># Condition</th>
                                    <th class="p-3">Prix Commande Min (TND)</th>
                                    <th class="p-3">Prix Commande Max (TND)</th>
                                    <th class="p-3">Marge Admin Fixe (TND)</th>
                                    <th class="p-3 text-center">Aperçu</th>
                                    <th class="p-3 text-center">Supprimer</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(rule, idx) in marginRules" :key="idx" class="hover:bg-slate-50/50">
                                    <td class="p-3 font-bold text-slate-400">
                                        Palier {{ idx + 1 }}
                                    </td>
                                    <td class="p-3">
                                        <div class="relative w-36">
                                            <input
                                                v-model="rule.min_order_price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="w-full text-xs font-semibold rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-1.5 pl-2.5 pr-8"
                                                placeholder="0.00"
                                                required
                                            />
                                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400">DT</span>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <div class="relative w-36">
                                            <input
                                                v-model="rule.max_order_price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="w-full text-xs font-semibold rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-1.5 pl-2.5 pr-8"
                                                placeholder="Illimité"
                                            />
                                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400">DT</span>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <div class="relative w-36">
                                            <input
                                                v-model="rule.margin_amount"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="w-full text-xs font-bold text-emerald-700 rounded-lg border-emerald-300 bg-emerald-50/30 focus:border-emerald-500 focus:ring-emerald-500 py-1.5 pl-2.5 pr-8"
                                                placeholder="5.00"
                                                required
                                            />
                                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-bold text-emerald-600">DT</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center">
                                        <span class="inline-flex px-2.5 py-1 text-[11px] font-medium rounded-md bg-slate-100 text-slate-700">
                                            De {{ rule.min_order_price || 0 }} à {{ rule.max_order_price ? rule.max_order_price + ' DT' : 'l\'infini' }} &rarr; <strong>+{{ rule.margin_amount || 0 }} DT</strong>
                                        </span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button
                                            @click="removeMarginRow(idx)"
                                            type="button"
                                            class="text-rose-400 hover:text-rose-600 p-1 rounded hover:bg-rose-50 transition"
                                            title="Supprimer cette condition"
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

                    <!-- Fallback percentage -->
                    <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <label class="text-xs font-semibold text-slate-600">
                                Commission de secours (%) si aucune condition ne s'applique :
                            </label>
                            <div class="relative w-28">
                                <input
                                    v-model="marginsForm.commission_rate"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="100"
                                    class="w-full text-xs font-semibold rounded-lg border-slate-300 py-1 pl-2.5 pr-6"
                                />
                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">%</span>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="marginsForm.processing"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition disabled:opacity-50"
                        >
                            <span v-if="marginsForm.processing">Enregistrement...</span>
                            <span v-else>Sauvegarder les Conditions de Marge</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- SECTION 2: INFORMATIONS GÉNÉRALES DE LA BOUTIQUE -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <h2 class="text-base font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100">
                    Coordonnées & Paramètres de la Boutique
                </h2>

                <form @submit.prevent="submitGeneral" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Shop Name -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Nom de la Boutique *
                            </label>
                            <input
                                v-model="generalForm.shop_name"
                                type="text"
                                class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2.5"
                                required
                            />
                            <div v-if="generalForm.errors.shop_name" class="text-rose-500 text-xs mt-1">
                                {{ generalForm.errors.shop_name }}
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Statut de la Boutique *
                            </label>
                            <select
                                v-model="generalForm.status"
                                class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2.5"
                                required
                            >
                                <option value="active">Active (Visible)</option>
                                <option value="pending">En attente de validation</option>
                                <option value="inactive">Suspendue / Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Description
                        </label>
                        <textarea
                            v-model="generalForm.description"
                            rows="3"
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Phone -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Téléphone
                            </label>
                            <input
                                v-model="generalForm.phone"
                                type="text"
                                class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2.5"
                            />
                        </div>

                        <!-- City -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Ville / Région
                            </label>
                            <input
                                v-model="generalForm.city"
                                type="text"
                                class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2.5"
                            />
                        </div>

                        <!-- Country -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Pays
                            </label>
                            <input
                                v-model="generalForm.country"
                                type="text"
                                class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2.5"
                            />
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Adresse Physique
                        </label>
                        <input
                            v-model="generalForm.address"
                            type="text"
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2.5"
                        />
                    </div>

                    <!-- Buttons -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button
                            type="submit"
                            :disabled="generalForm.processing"
                            class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-xs transition disabled:opacity-50"
                        >
                            <span v-if="generalForm.processing">Mise à jour...</span>
                            <span v-else>Enregistrer les Informations</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
