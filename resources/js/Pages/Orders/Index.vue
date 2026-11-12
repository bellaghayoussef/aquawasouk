<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    orders: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    canManageOrders: {
        type: Boolean,
        default: false,
    },
    userRole: {
        type: String,
        default: 'customer',
    },
});

// View mode state (stored in localStorage)
const STORAGE_KEY = 'aquawasouk_orders_view';
const currentView = ref('table');

onMounted(() => {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved && (saved === 'table' || saved === 'kanban')) {
        currentView.value = saved;
    }
});

const setView = (view) => {
    currentView.value = view;
    localStorage.setItem(STORAGE_KEY, view);

    // If switching to Kanban and page size is small, fetch a larger batch for seamless board experience
    if (view === 'kanban' && (props.orders?.per_page || 15) < 50) {
        applyFilters({ view: 'kanban', per_page: 100 });
    }
};

// Filter state
const search = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || 'all');
const selectedPaymentStatus = ref(props.filters?.payment_status || 'all');

let searchTimeout = null;
const onSearchInput = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
};

const applyFilters = (overrides = {}) => {
    const params = {
        search: search.value.trim() || undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        payment_status: selectedPaymentStatus.value !== 'all' ? selectedPaymentStatus.value : undefined,
        view: currentView.value,
        ...overrides,
    };

    router.get(route('orders.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    selectedStatus.value = 'all';
    selectedPaymentStatus.value = 'all';
    applyFilters({ search: undefined, status: undefined, payment_status: undefined });
};

const hasActiveFilters = computed(() => {
    return (
        search.value.trim() !== '' ||
        selectedStatus.value !== 'all' ||
        selectedPaymentStatus.value !== 'all'
    );
});

// Local orders copy for optimistic updates during drag and drop
const localOrders = ref([]);

watch(
    () => props.orders?.data,
    (newOrders) => {
        localOrders.value = (newOrders || []).map((o) => ({ ...o }));
    },
    { immediate: true, deep: true }
);

// Formatting helpers
const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-TN', {
        style: 'currency',
        currency: 'TND',
        minimumFractionDigits: 2,
    }).format(price || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('fr-TN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Status definitions (strictly matching database enum: pending, processing, shipped, delivered, cancelled)
const KANBAN_COLUMNS = [
    {
        key: 'pending',
        title: 'En attente',
        subtitle: 'Nouvelles commandes',
        headerBg: 'bg-amber-500',
        badgeBg: 'bg-amber-50 text-amber-700 border-amber-200',
        columnBg: 'bg-amber-50/30',
        borderHover: 'hover:border-amber-300',
        ringColor: 'ring-amber-400',
        icon: 'clock',
    },
    {
        key: 'processing',
        title: 'En préparation',
        subtitle: 'Emballage & logistique',
        headerBg: 'bg-blue-500',
        badgeBg: 'bg-blue-50 text-blue-700 border-blue-200',
        columnBg: 'bg-blue-50/30',
        borderHover: 'hover:border-blue-300',
        ringColor: 'ring-blue-400',
        icon: 'box',
    },
    {
        key: 'shipped',
        title: 'En cours d\'expédition',
        subtitle: 'Remis au transporteur',
        headerBg: 'bg-indigo-500',
        badgeBg: 'bg-indigo-50 text-indigo-700 border-indigo-200',
        columnBg: 'bg-indigo-50/30',
        borderHover: 'hover:border-indigo-300',
        ringColor: 'ring-indigo-400',
        icon: 'truck',
    },
    {
        key: 'delivered',
        title: 'Livrée',
        subtitle: 'Remise au client',
        headerBg: 'bg-emerald-500',
        badgeBg: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        columnBg: 'bg-emerald-50/30',
        borderHover: 'hover:border-emerald-300',
        ringColor: 'ring-emerald-400',
        icon: 'check',
    },
    {
        key: 'cancelled',
        title: 'Annulée',
        subtitle: 'Commandes annulées',
        headerBg: 'bg-rose-500',
        badgeBg: 'bg-rose-50 text-rose-700 border-rose-200',
        columnBg: 'bg-rose-50/30',
        borderHover: 'hover:border-rose-300',
        ringColor: 'ring-rose-400',
        icon: 'x',
    },
];

const getStatusBadge = (status) => {
    const found = KANBAN_COLUMNS.find((col) => col.key === status);
    if (found) {
        return { bg: found.badgeBg, label: found.title };
    }
    return { bg: 'bg-slate-100 text-slate-700 border-slate-200', label: status };
};

const getPaymentBadge = (status) => {
    const badges = {
        paid: { bg: 'bg-emerald-50 text-emerald-700 border-emerald-200', label: 'Payé' },
        pending: { bg: 'bg-amber-50 text-amber-700 border-amber-200', label: 'En attente' },
        failed: { bg: 'bg-rose-50 text-rose-700 border-rose-200', label: 'Échoué' },
        refunded: { bg: 'bg-purple-50 text-purple-700 border-purple-200', label: 'Remboursé' },
    };
    return badges[status] || { bg: 'bg-slate-100 text-slate-700 border-slate-200', label: status || 'Non spécifié' };
};

// Grouped orders for Kanban
const ordersByStatus = computed(() => {
    const map = {
        pending: [],
        processing: [],
        shipped: [],
        delivered: [],
        cancelled: [],
    };

    for (const order of localOrders.value) {
        const s = order.status || 'pending';
        if (map[s]) {
            map[s].push(order);
        } else {
            map.pending.push(order);
        }
    }
    return map;
});

// Calculate column totals
const columnTotals = computed(() => {
    const totals = {};
    for (const col of KANBAN_COLUMNS) {
        const list = ordersByStatus.value[col.key] || [];
        totals[col.key] = list.reduce((sum, o) => sum + Number(o.total_amount || 0), 0);
    }
    return totals;
});

// Toast notifications state
const toast = ref(null);
let toastTimer = null;

const showToast = (message, type = 'success') => {
    clearTimeout(toastTimer);
    toast.value = { message, type };
    toastTimer = setTimeout(() => {
        toast.value = null;
    }, 4500);
};

const closeToast = () => {
    clearTimeout(toastTimer);
    toast.value = null;
};

// Drag and drop state
const draggedOrder = ref(null);
const activeDropColumn = ref(null);
const updatingOrderIds = ref(new Set());

const isUpdating = (orderId) => updatingOrderIds.value.has(orderId);

const onDragStart = (event, order) => {
    draggedOrder.value = order;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(order.id));
};

const onDragEnd = () => {
    draggedOrder.value = null;
    activeDropColumn.value = null;
};

const onDragOver = (event, colKey) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    activeDropColumn.value = colKey;
};

const onDragLeave = (event, colKey) => {
    if (activeDropColumn.value === colKey) {
        activeDropColumn.value = null;
    }
};

const onDrop = async (event, targetStatus) => {
    event.preventDefault();
    activeDropColumn.value = null;

    if (!draggedOrder.value) return;
    const order = draggedOrder.value;
    const oldStatus = order.status;

    if (oldStatus === targetStatus) {
        draggedOrder.value = null;
        return;
    }

    await executeStatusChange(order, targetStatus);
    draggedOrder.value = null;
};

// Common status transition logic (used by Drag & Drop and Dropdown menu)
const executeStatusChange = async (order, newStatus) => {
    const oldStatus = order.status;

    // Business rules validation
    if (oldStatus === 'delivered' && newStatus !== 'delivered' && !props.canManageOrders) {
        showToast('Une commande livrée ne peut plus être modifiée.', 'error');
        return;
    }

    if (oldStatus === 'cancelled' && newStatus !== 'cancelled' && props.userRole !== 'admin') {
        showToast('Seul un administrateur peut réactiver une commande annulée.', 'error');
        return;
    }

    // Optimistic UI update
    order.status = newStatus;
    updatingOrderIds.value.add(order.id);

    try {
        const response = await axios.patch(
            route('orders.updateStatus', order.id),
            { status: newStatus },
            {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        );

        if (response.data && response.data.success) {
            showToast(response.data.message || `Commande #${order.order_number} mise à jour avec succès.`, 'success');
            // If backend returned refreshed order
            if (response.data.order) {
                const idx = localOrders.value.findIndex((o) => o.id === order.id);
                if (idx !== -1) {
                    localOrders.value[idx] = { ...localOrders.value[idx], ...response.data.order };
                }
            }
        } else {
            throw new Error(response.data?.message || 'Erreur lors de la mise à jour.');
        }
    } catch (err) {
        // Rollback on error
        order.status = oldStatus;
        const msg = err.response?.data?.message || err.message || 'Impossible de mettre à jour le statut.';
        showToast(msg, 'error');
    } finally {
        updatingOrderIds.value.delete(order.id);
    }
};
</script>

<template>
    <Head title="Gestion des Commandes - AquawaSouk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="font-bold text-2xl font-display text-slate-900 tracking-tight">
                            Commandes
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                            {{ orders?.total || localOrders.length }} au total
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Suivez, filtrez et gérez le statut de vos commandes en mode Liste ou Tableau Kanban.
                    </p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <!-- View Switcher -->
                    <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 shadow-2xs">
                        <button
                            type="button"
                            @click="setView('table')"
                            :class="[
                                'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
                                currentView === 'table'
                                    ? 'bg-white text-slate-900 shadow-xs border border-slate-200/60'
                                    : 'text-slate-600 hover:text-slate-900'
                            ]"
                            title="Affichage classique en tableau"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <span>Tableau</span>
                        </button>

                        <button
                            type="button"
                            @click="setView('kanban')"
                            :class="[
                                'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
                                currentView === 'kanban'
                                    ? 'bg-white text-slate-900 shadow-xs border border-slate-200/60'
                                    : 'text-slate-600 hover:text-slate-900'
                            ]"
                            title="Affichage Kanban interactif par statuts"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                            </svg>
                            <span>Kanban</span>
                        </button>
                    </div>

                    <Link
                        :href="route('products.index')"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-xl shadow-xs transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Boutique
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Filters Bar -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 mb-6 shadow-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <!-- Search input -->
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input
                                v-model="search"
                                @input="onSearchInput"
                                type="text"
                                placeholder="N° commande, client, tél..."
                                class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-slate-50/50"
                            />
                            <button
                                v-if="search"
                                @click="search = ''; applyFilters();"
                                type="button"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Status select -->
                        <div>
                            <select
                                v-model="selectedStatus"
                                @change="applyFilters()"
                                class="w-full py-2 px-3 text-xs rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-slate-50/50 text-slate-700"
                            >
                                <option value="all">Tous les statuts</option>
                                <option value="pending">En attente</option>
                                <option value="processing">En préparation</option>
                                <option value="shipped">En cours d'expédition</option>
                                <option value="delivered">Livrée</option>
                                <option value="cancelled">Annulée</option>
                            </select>
                        </div>

                        <!-- Payment status select -->
                        <div>
                            <select
                                v-model="selectedPaymentStatus"
                                @change="applyFilters()"
                                class="w-full py-2 px-3 text-xs rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-slate-50/50 text-slate-700"
                            >
                                <option value="all">Tous les paiements</option>
                                <option value="pending">Paiement en attente</option>
                                <option value="paid">Payé</option>
                                <option value="failed">Échoué</option>
                                <option value="refunded">Remboursé</option>
                            </select>
                        </div>

                        <!-- Reset / Stats -->
                        <div class="flex items-center justify-between sm:justify-end gap-2">
                            <button
                                v-if="hasActiveFilters"
                                @click="resetFilters"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Réinitialiser
                            </button>
                            <span class="text-xs text-slate-500 font-medium">
                                {{ localOrders.length }} affichée(s)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Empty state when no orders match -->
                <div
                    v-if="!localOrders || localOrders.length === 0"
                    class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center max-w-lg mx-auto shadow-xs"
                >
                    <div class="w-20 h-20 mx-auto rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-5">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold font-display text-slate-900">
                        {{ hasActiveFilters ? 'Aucun résultat pour cette recherche' : 'Aucune commande pour le moment' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1.5 max-w-xs mx-auto leading-relaxed">
                        {{ hasActiveFilters ? 'Essayez de modifier vos filtres ou termes de recherche pour afficher les commandes.' : 'Vous n\'avez pas encore de commande enregistrée sur AquawaSouk.' }}
                    </p>
                    <div class="mt-6">
                        <button
                            v-if="hasActiveFilters"
                            @click="resetFilters"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl shadow-xs transition"
                        >
                            Effacer les filtres
                        </button>
                        <Link
                            v-else
                            :href="route('products.index')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-xl shadow-xs transition"
                        >
                            Découvrir les offres
                        </Link>
                    </div>
                </div>

                <!-- 1. TABLE VIEW (Preserved original cards table with quick action) -->
                <div v-else-if="currentView === 'table'" class="space-y-6">
                    <div
                        v-for="order in localOrders"
                        :key="order.id"
                        class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden hover:border-slate-300 transition"
                    >
                        <!-- Order card top bar -->
                        <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-6">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">N° de commande</p>
                                    <p class="font-bold text-sm text-slate-900">#{{ order.order_number }}</p>
                                </div>
                                <div v-if="order.user?.name">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Client</p>
                                    <p class="font-semibold text-xs text-slate-800">{{ order.user.name }}</p>
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

                            <div class="flex items-center gap-2">
                                <!-- Status update shortcut for admins/vendors -->
                                <div v-if="canManageOrders" class="relative">
                                    <select
                                        :value="order.status"
                                        @change="executeStatusChange(order, $event.target.value)"
                                        :disabled="isUpdating(order.id)"
                                        class="text-xs py-1.5 pl-2.5 pr-7 rounded-lg border-slate-200 bg-white font-semibold text-slate-700 focus:ring-brand-500 focus:border-brand-500 shadow-2xs"
                                    >
                                        <option value="pending">En attente</option>
                                        <option value="processing">En préparation</option>
                                        <option value="shipped">Expédiée</option>
                                        <option value="delivered">Livrée</option>
                                        <option value="cancelled">Annulée</option>
                                    </select>
                                </div>

                                <Link
                                    :href="route('orders.show', order.id)"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-lg text-xs font-semibold shadow-2xs transition"
                                >
                                    Détails & Facture
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
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
                                        <span v-if="item.product?.vendor?.shop_name || item.vendor?.shop_name" class="text-slate-400">
                                            &bull; {{ item.product?.vendor?.shop_name || item.vendor?.shop_name }}
                                        </span>
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
                        <div class="px-6 py-3 bg-slate-50/50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                            <div class="flex items-center gap-4 flex-wrap">
                                <div>
                                    Paiement :
                                    <span class="font-semibold capitalize text-slate-700">
                                        {{ order.payment_method === 'cod' ? 'À la livraison (Cash on Delivery)' : order.payment_method }}
                                    </span>
                                </div>
                                <span :class="['inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full border', getPaymentBadge(order.payment_status).bg]">
                                    {{ getPaymentBadge(order.payment_status).label }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div v-if="order.phone">
                                    Tél :
                                    <a :href="`tel:${order.phone}`" class="font-semibold text-brand-600 hover:underline">
                                        {{ order.phone }}
                                    </a>
                                </div>
                                <div>
                                    Livraison :
                                    <span class="font-semibold text-slate-700">
                                        {{ (order.shipping_cost || 0) > 0 ? formatPrice(order.shipping_cost) : 'Gratuit' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders?.links && orders.links.length > 3" class="flex justify-center pt-6">
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

                <!-- 2. KANBAN VIEW (Modern Task Manager Board) -->
                <div v-else class="pb-10">
                    <!-- Board Info Bar -->
                    <div class="mb-4 flex items-center justify-between text-xs text-slate-500">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Glissez-déposez les cartes entre les colonnes pour changer leur statut</span>
                        </div>
                        <div class="hidden sm:block text-[11px] text-slate-400">
                            Astuce : Utilisez aussi le menu sur chaque carte pour un déplacement rapide
                        </div>
                    </div>

                    <!-- Board Columns (Horizontal scroll on mobile/tablet) -->
                    <div class="flex gap-4 overflow-x-auto pb-6 pt-1 snap-x scrollbar-thin">
                        <div
                            v-for="column in KANBAN_COLUMNS"
                            :key="column.key"
                            @dragover="onDragOver($event, column.key)"
                            @dragleave="onDragLeave($event, column.key)"
                            @drop="onDrop($event, column.key)"
                            :class="[
                                'w-80 flex-shrink-0 flex flex-col rounded-2xl border transition-all duration-200 snap-start',
                                activeDropColumn === column.key
                                    ? `border-brand-500 bg-brand-50/40 ring-2 ${column.ringColor} shadow-md`
                                    : 'border-slate-200/90 bg-slate-100/60'
                            ]"
                        >
                            <!-- Column Header -->
                            <div class="p-3.5 border-b border-slate-200/70 bg-white/80 rounded-t-2xl backdrop-blur-xs">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span :class="['w-2.5 h-2.5 rounded-full', column.headerBg]"></span>
                                        <h3 class="font-bold text-xs text-slate-900 tracking-tight">
                                            {{ column.title }}
                                        </h3>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ (ordersByStatus[column.key] || []).length }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1">
                                    <span>{{ column.subtitle }}</span>
                                    <span class="font-bold text-slate-700 font-display">
                                        {{ formatPrice(columnTotals[column.key] || 0) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Column Cards Container -->
                            <div class="p-3 flex-1 flex flex-col gap-3 min-h-[380px] max-h-[calc(100vh-320px)] overflow-y-auto scrollbar-thin">
                                <!-- Order Cards in Column -->
                                <div
                                    v-for="order in (ordersByStatus[column.key] || [])"
                                    :key="order.id"
                                    :draggable="!isUpdating(order.id)"
                                    @dragstart="onDragStart($event, order)"
                                    @dragend="onDragEnd"
                                    :class="[
                                        'group relative bg-white rounded-xl border border-slate-200/80 p-3.5 shadow-2xs transition-all duration-200',
                                        isUpdating(order.id)
                                            ? 'opacity-60 pointer-events-none'
                                            : 'hover:shadow-md hover:border-slate-300 cursor-grab active:cursor-grabbing',
                                        draggedOrder?.id === order.id ? 'opacity-40 ring-2 ring-brand-400' : ''
                                    ]"
                                >
                                    <!-- Updating overlay spinner -->
                                    <div
                                        v-if="isUpdating(order.id)"
                                        class="absolute inset-0 bg-white/80 backdrop-blur-2xs rounded-xl flex items-center justify-center z-10"
                                    >
                                        <div class="flex items-center gap-2 text-xs font-semibold text-brand-600">
                                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span>Mise à jour...</span>
                                        </div>
                                    </div>

                                    <!-- Top Row: Ref & Date -->
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-xs text-slate-900 tracking-tight">
                                                #{{ order.order_number }}
                                            </span>
                                        </div>
                                        <span class="text-[10px] font-medium text-slate-400">
                                            {{ formatDate(order.created_at) }}
                                        </span>
                                    </div>

                                    <!-- Customer details -->
                                    <div class="space-y-1 mb-2.5">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-800 font-semibold truncate">
                                            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span class="truncate">{{ order.user?.name || 'Client' }}</span>
                                        </div>

                                        <div v-if="order.phone" class="flex items-center gap-1.5 text-[11px] text-slate-500">
                                            <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <a :href="`tel:${order.phone}`" class="hover:text-brand-600 hover:underline">
                                                {{ order.phone }}
                                            </a>
                                        </div>

                                        <div v-if="order.shipping_city" class="flex items-center gap-1.5 text-[11px] text-slate-500">
                                            <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="truncate">{{ order.shipping_city }}</span>
                                        </div>
                                    </div>

                                    <!-- Products summary & Vendor -->
                                    <div class="p-2 rounded-lg bg-slate-50 border border-slate-100 mb-3 flex items-center justify-between text-[11px]">
                                        <div class="flex items-center gap-1.5 text-slate-600 truncate">
                                            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                            <span class="font-medium">
                                                {{ order.items?.length || 0 }} article{{ (order.items?.length || 0) > 1 ? 's' : '' }}
                                            </span>
                                        </div>

                                        <div
                                            v-if="order.items?.[0]?.product?.vendor?.shop_name || order.items?.[0]?.vendor?.shop_name"
                                            class="text-[10px] text-slate-500 truncate max-w-[120px]"
                                            :title="order.items?.[0]?.product?.vendor?.shop_name || order.items?.[0]?.vendor?.shop_name"
                                        >
                                            🏪 {{ order.items?.[0]?.product?.vendor?.shop_name || order.items?.[0]?.vendor?.shop_name }}
                                        </div>
                                    </div>

                                    <!-- Payment & Total Price -->
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                        <div>
                                            <p class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Total</p>
                                            <p class="font-bold text-sm text-brand-600 font-display">
                                                {{ formatPrice(order.total_amount) }}
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <span
                                                :class="['px-2 py-0.5 text-[10px] font-semibold rounded-full border', getPaymentBadge(order.payment_status).bg]"
                                                :title="`Paiement: ${order.payment_method}`"
                                            >
                                                {{ getPaymentBadge(order.payment_status).label }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Bottom Action Bar -->
                                    <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2">
                                        <!-- Quick move status menu for accessibility / touch -->
                                        <div class="relative">
                                            <select
                                                :value="order.status"
                                                @change="executeStatusChange(order, $event.target.value)"
                                                :disabled="isUpdating(order.id)"
                                                class="text-[10px] py-1 pl-2 pr-6 rounded-md border-slate-200 bg-slate-50 font-semibold text-slate-600 hover:bg-slate-100 focus:ring-brand-500 focus:border-brand-500"
                                                title="Déplacer vers un autre statut"
                                            >
                                                <option value="pending">En attente</option>
                                                <option value="processing">En préparation</option>
                                                <option value="shipped">Expédiée</option>
                                                <option value="delivered">Livrée</option>
                                                <option value="cancelled">Annulée</option>
                                            </select>
                                        </div>

                                        <Link
                                            :href="route('orders.show', order.id)"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-600 hover:text-brand-700 hover:underline"
                                        >
                                            <span>Détails</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Empty column state -->
                                <div
                                    v-if="!ordersByStatus[column.key] || ordersByStatus[column.key].length === 0"
                                    class="h-44 border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center p-4 text-center text-slate-400"
                                >
                                    <svg class="w-6 h-6 mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <span class="text-xs font-semibold text-slate-500">Aucune commande</span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">Glissez une carte ici</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Toast Notification -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="toast"
                class="fixed bottom-5 right-5 z-50 max-w-sm w-full bg-white rounded-2xl shadow-xl border p-4 flex items-start gap-3"
                :class="toast.type === 'error' ? 'border-rose-200 bg-rose-50/50' : 'border-emerald-200 bg-emerald-50/50'"
            >
                <div
                    class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0"
                    :class="toast.type === 'error' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'"
                >
                    <svg v-if="toast.type === 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-xs font-bold" :class="toast.type === 'error' ? 'text-rose-900' : 'text-emerald-900'">
                        {{ toast.type === 'error' ? 'Erreur' : 'Statut mis à jour' }}
                    </p>
                    <p class="text-xs mt-0.5 leading-relaxed" :class="toast.type === 'error' ? 'text-rose-700' : 'text-emerald-700'">
                        {{ toast.message }}
                    </p>
                </div>
                <button
                    type="button"
                    @click="closeToast"
                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Subtle smooth scrollbar for Kanban board */
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: rgba(148, 163, 184, 0.4);
    border-radius: 9999px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background-color: rgba(100, 116, 139, 0.6);
}
</style>
