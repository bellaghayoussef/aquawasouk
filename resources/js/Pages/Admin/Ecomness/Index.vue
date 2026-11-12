<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    hasApiKey: Boolean,
    apiKey: String,
    rawApiKey: String,
    vendor: Object,
    categories: Array,
    ecomnessCategories: Array,
    governorates: Array,
    categoryMapping: Object,
    stats: Object,
    recentOrders: Array,
});

const activeTab = ref('products'); // 'products', 'mapping', 'settings', 'orders'
const isTesting = ref(false);
const isSyncing = ref(false);
const isTracking = ref(false);

// Settings Form
const settingsForm = useForm({
    api_key: props.rawApiKey || '',
});

const saveSettings = () => {
    settingsForm.post(route('admin.ecomness.settings'), {
        preserveScroll: true,
    });
};

// Test Connection
const testConnection = () => {
    isTesting.value = true;
    router.post(route('admin.ecomness.test'), {}, {
        preserveScroll: true,
        onFinish: () => {
            isTesting.value = false;
        },
    });
};

// Mapping Form
const mappingForm = useForm({
    mapping: { ...props.categoryMapping },
});

const saveMapping = () => {
    mappingForm.post(route('admin.ecomness.mapping'), {
        preserveScroll: true,
    });
};

// Sync Products
const syncAllProducts = () => {
    if (!confirm("Voulez-vous lancer la synchronisation de tous les produits Ecomness vers la boutique 'Aquawasouk eco' ? Cela peut prendre quelques instants.")) {
        return;
    }
    isSyncing.value = true;
    router.post(route('admin.ecomness.sync-products'), {}, {
        preserveScroll: true,
        onFinish: () => {
            isSyncing.value = false;
        },
    });
};

// Track Orders
const syncTrackOrders = () => {
    isTracking.value = true;
    router.post(route('admin.ecomness.track-orders'), {}, {
        preserveScroll: true,
        onFinish: () => {
            isTracking.value = false;
        },
    });
};

// Forward Single Order
const forwardOrder = (orderId) => {
    router.post(route('admin.ecomness.forward-order', orderId), {}, {
        preserveScroll: true,
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('fr-TN', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};
</script>

<template>
    <Head title="Ecomness DropShipping - AquawaSouk" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full" :class="hasApiKey ? 'bg-emerald-500 animate-pulse' : 'bg-amber-400'"></span>
                        <h2 class="text-xl sm:text-2xl font-black font-display text-slate-900">
                            Ecomness DropShipping Hub
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Catalogue automatisé, mapping des catégories et transmission directe des commandes vers la boutique 
                        <strong class="text-slate-800">"Aquawasouk eco"</strong>.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="testConnection"
                        :disabled="isTesting || !hasApiKey"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 transition shadow-2xs disabled:opacity-50"
                    >
                        <svg v-if="!isTesting" class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span v-else class="animate-spin text-xs">⏳</span>
                        <span>Tester API</span>
                    </button>

                    <button
                        @click="syncAllProducts"
                        :disabled="isSyncing || !hasApiKey"
                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 transition shadow-sm disabled:opacity-50"
                    >
                        <svg v-if="!isSyncing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span v-else class="animate-spin text-xs">⏳</span>
                        <span>{{ isSyncing ? 'Synchronisation...' : 'Synchroniser Produits' }}</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- 1. Stats Row -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <p class="text-[11px] sm:text-xs text-slate-500 font-semibold truncate">Produits Ecomness</p>
                    <p class="text-xl sm:text-2xl font-black font-display text-slate-900 mt-1">
                        {{ stats.total_products }}
                    </p>
                    <span class="text-[10px] text-slate-400 mt-0.5 block truncate">Boutique : Aquawasouk eco</span>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <p class="text-[11px] sm:text-xs text-slate-500 font-semibold truncate">Commandes Transmises</p>
                    <p class="text-xl sm:text-2xl font-black font-display text-emerald-600 mt-1">
                        {{ stats.total_orders }}
                    </p>
                    <span class="text-[10px] text-emerald-700 mt-0.5 block">Codes ENO- générés</span>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <p class="text-[11px] sm:text-xs text-slate-500 font-semibold truncate">En Attente d'Envoi</p>
                    <p class="text-xl sm:text-2xl font-black font-display text-amber-600 mt-1">
                        {{ stats.pending_orders }}
                    </p>
                    <span class="text-[10px] text-amber-700 mt-0.5 block">À router vers Ecomness</span>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <p class="text-[11px] sm:text-xs text-slate-500 font-semibold truncate">Dernière Synchronisation</p>
                    <p class="text-xs sm:text-sm font-bold text-slate-800 mt-2 truncate">
                        {{ formatDate(stats.last_sync_at) }}
                    </p>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Automatique ou manuel</span>
                </div>
            </div>

            <!-- 2. Tabs Navigation -->
            <div class="border-b border-slate-200 flex items-center gap-1 overflow-x-auto no-scrollbar">
                <button
                    @click="activeTab = 'products'"
                    :class="[
                        'px-4 py-3 text-xs font-bold border-b-2 transition whitespace-nowrap',
                        activeTab === 'products'
                            ? 'border-brand-600 text-brand-600 bg-white rounded-t-xl'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    📦 Synchronisation Produits
                </button>

                <button
                    @click="activeTab = 'mapping'"
                    :class="[
                        'px-4 py-3 text-xs font-bold border-b-2 transition whitespace-nowrap',
                        activeTab === 'mapping'
                            ? 'border-brand-600 text-brand-600 bg-white rounded-t-xl'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    🗂️ Mapping Catégories ({{ ecomnessCategories.length }})
                </button>

                <button
                    @click="activeTab = 'orders'"
                    :class="[
                        'px-4 py-3 text-xs font-bold border-b-2 transition whitespace-nowrap',
                        activeTab === 'orders'
                            ? 'border-brand-600 text-brand-600 bg-white rounded-t-xl'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    🚚 Commandes & Suivi Colis
                </button>

                <button
                    @click="activeTab = 'settings'"
                    :class="[
                        'px-4 py-3 text-xs font-bold border-b-2 transition whitespace-nowrap',
                        activeTab === 'settings'
                            ? 'border-brand-600 text-brand-600 bg-white rounded-t-xl'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    ⚙️ Clé API & Paramètres
                </button>
            </div>

            <!-- Tab Content: Synchronisation Produits -->
            <div v-if="activeTab === 'products'" class="space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Catalogue DropShipping Ecomness</h3>
                            <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
                                Les produits importés sont assignés automatiquement à la boutique partenaire 
                                <strong class="text-slate-800">"Aquawasouk eco"</strong>. 
                                Les prix de vente respectent le <em>minimumUnitPrice</em> d'Ecomness avec votre marge bénéficiaire.
                            </p>
                        </div>
                        <button
                            @click="syncAllProducts"
                            :disabled="isSyncing || !hasApiKey"
                            class="px-5 py-3 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-sm transition shrink-0 disabled:opacity-50"
                        >
                            {{ isSyncing ? 'Synchronisation en cours...' : 'Lancer l\'import / mise à jour' }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-6">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/60">
                            <span class="text-lg block mb-1">🏷️</span>
                            <h4 class="text-xs font-bold text-slate-800">Boutique Assignée</h4>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Tous les articles sont vendus sous l'enseigne <strong>"Aquawasouk eco"</strong> (ID: {{ vendor.id }}).
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/60">
                            <span class="text-lg block mb-1">📐</span>
                            <h4 class="text-xs font-bold text-slate-800">Variations & Stocks</h4>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Tailles, couleurs et stocks sont automatiquement importés et rattachés aux déclinaisons du produit.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/60">
                            <span class="text-lg block mb-1">⚡</span>
                            <h4 class="text-xs font-bold text-slate-800">Transmission Automatique</h4>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Dès qu'un client passe commande sur votre site, celle-ci est envoyée directement à Ecomness pour expédition.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Mapping Catégories -->
            <div v-if="activeTab === 'mapping'" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Correspondance des Catégories (Mapping)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Associez chaque catégorie fournie par l'API Ecomness à votre propre catégorie sur AquawaSouk :
                        </p>
                    </div>
                    <button
                        @click="saveMapping"
                        :disabled="mappingForm.processing"
                        class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-sm transition disabled:opacity-50"
                    >
                        {{ mappingForm.processing ? 'Enregistrement...' : 'Enregistrer le mapping' }}
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="ecomCat in ecomnessCategories"
                        :key="ecomCat"
                        class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <span class="inline-block px-2.5 py-1 rounded-md bg-amber-100 text-amber-900 font-mono font-bold text-xs">
                                {{ ecomCat }}
                            </span>
                        </div>

                        <div class="w-full sm:w-64">
                            <select
                                v-model="mappingForm.mapping[ecomCat]"
                                class="w-full text-xs font-semibold rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2"
                            >
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button
                        @click="saveMapping"
                        :disabled="mappingForm.processing"
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-sm transition disabled:opacity-50"
                    >
                        {{ mappingForm.processing ? 'Enregistrement...' : 'Enregistrer le mapping' }}
                    </button>
                </div>
            </div>

            <!-- Tab Content: Commandes & Suivi Colis -->
            <div v-if="activeTab === 'orders'" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Commandes & Suivi Ecomness</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Commandes de vos clients contenant des articles de la boutique "Aquawasouk eco" :
                        </p>
                    </div>

                    <button
                        @click="syncTrackOrders"
                        :disabled="isTracking"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition disabled:opacity-50"
                    >
                        <svg v-if="!isTracking" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span v-else class="animate-spin text-xs">⏳</span>
                        <span>Actualiser les statuts colis</span>
                    </button>
                </div>

                <div class="overflow-x-auto -mx-6 sm:mx-0 px-6 sm:px-0">
                    <table class="w-full text-left text-xs border-collapse min-w-[700px]">
                        <thead>
                            <tr class="text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                                <th class="py-3 pr-4">Commande AquawaSouk</th>
                                <th class="py-3 px-4">Client & Gouvernorat</th>
                                <th class="py-3 px-4">Code Ecomness (ENO)</th>
                                <th class="py-3 px-4">N° Colis & Suivi</th>
                                <th class="py-3 px-4">Statut Ecomness</th>
                                <th class="py-3 pl-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="order in recentOrders" :key="order.id" class="text-slate-700 hover:bg-slate-50/50">
                                <td class="py-3 pr-4 font-bold text-slate-900">
                                    #{{ order.order_number }}
                                    <span class="block text-[10px] text-slate-400 font-normal">{{ formatDate(order.created_at) }}</span>
                                </td>

                                <td class="py-3 px-4">
                                    <p class="font-semibold">{{ order.user?.name || 'Client' }}</p>
                                    <p class="text-[11px] text-slate-400">{{ order.shipping_city }} • {{ order.phone }}</p>
                                </td>

                                <td class="py-3 px-4 font-mono">
                                    <span v-if="order.ecomness_order_code" class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 font-bold border border-emerald-200">
                                        {{ order.ecomness_order_code }}
                                    </span>
                                    <span v-else class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">
                                        Non envoyé
                                    </span>
                                </td>

                                <td class="py-3 px-4">
                                    <span v-if="order.ecomness_tracking_number" class="font-mono text-slate-900 font-bold">
                                        {{ order.ecomness_tracking_number }}
                                    </span>
                                    <span v-else class="text-slate-400 text-[11px]">-</span>
                                </td>

                                <td class="py-3 px-4">
                                    <span v-if="order.ecomness_status" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">
                                        {{ order.ecomness_status }}
                                    </span>
                                    <span v-else-if="order.ecomness_error" class="text-[11px] text-rose-600 truncate block max-w-xs" :title="order.ecomness_error">
                                        ⚠️ Erreur: {{ order.ecomness_error }}
                                    </span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>

                                <td class="py-3 pl-4 text-right">
                                    <button
                                        v-if="!order.ecomness_order_code"
                                        @click="forwardOrder(order.id)"
                                        type="button"
                                        class="px-3 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-[11px] transition shadow-2xs"
                                    >
                                        Transmettre &rarr;
                                    </button>
                                    <span v-else class="text-[11px] text-emerald-600 font-semibold">
                                        ✓ Transmise
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!recentOrders || recentOrders.length === 0" class="text-center py-10">
                        <p class="text-xs font-semibold text-slate-500">Aucune commande Ecomness pour l'instant.</p>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Clé API & Paramètres -->
            <div v-if="activeTab === 'settings'" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Paramètres de Connexion Ecomness API</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Générez votre clé API dans votre espace Ecomness (<em>Intégrations &rarr; API Ecomness &rarr; Gérer les clés API</em>) 
                        et collez-la ci-dessous :
                    </p>
                </div>

                <form @submit.prevent="saveSettings" class="space-y-4 max-w-xl">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Clé API Ecomness (x-api-key) *</label>
                        <input
                            v-model="settingsForm.api_key"
                            type="password"
                            placeholder="ecm_..."
                            class="w-full p-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-brand-200 outline-hidden"
                            required
                        />
                        <p v-if="hasApiKey" class="text-[11px] text-emerald-600 mt-1 font-medium">
                            ✓ Une clé API est actuellement configurée ({{ apiKey }})
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-xs text-amber-900 space-y-1">
                        <p class="font-bold">Informations sur la boutique partenaire :</p>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-amber-800">
                            <li>Boutique cible : <strong>Aquawasouk eco</strong></li>
                            <li>URL API : <code>https://ecomness.com/api/v1</code></li>
                            <li>Format d'authentification : En-tête <code>x-api-key: ecm_...</code></li>
                        </ul>
                    </div>

                    <div class="pt-2 flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="settingsForm.processing"
                            class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-sm transition disabled:opacity-50"
                        >
                            {{ settingsForm.processing ? 'Enregistrement...' : 'Enregistrer la clé API' }}
                        </button>

                        <button
                            @click="testConnection"
                            type="button"
                            :disabled="isTesting || !settingsForm.api_key"
                            class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition disabled:opacity-50"
                        >
                            {{ isTesting ? 'Test en cours...' : 'Tester' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

