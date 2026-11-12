<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TableFilters from '@/Components/TableFilters.vue';

const props = defineProps({
    orders: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// View mode state (stored in localStorage)
const STORAGE_KEY = 'aquawasouk_admin_orders_view';
const currentView = ref('kanban');

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
    if (view === 'kanban' && (props.orders?.per_page || 20) < 50) {
        router.get(
            route('admin.orders'),
            { ...props.filters, view: 'kanban', per_page: 100 },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }
};

// Local orders copy for optimistic updates during drag and drop
const localOrders = ref([]);

watch(
    () => props.orders?.data,
    (newOrders) => {
        localOrders.value = (newOrders || []).map((o) => ({ ...o }));
    },
    { immediate: true, deep: true }
);

// Price formatting
const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-TN', {
        style: 'currency',
        currency: 'TND',
        minimumFractionDigits: 2,
    }).format(price || 0);
};

// Date formatting
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('fr-TN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Status definitions (matching database enum: pending, processing, shipped, delivered, cancelled)
const KANBAN_COLUMNS = [
    {
        key: 'pending',
        title: 'En attente',
        subtitle: 'Nouvelles commandes',
        headerBg: 'bg-amber-500',
        badgeBg: 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200 border-amber-200 dark:border-amber-700',
        columnBg: 'bg-amber-50/20 dark:bg-amber-950/10',
        ringColor: 'ring-amber-400',
    },
    {
        key: 'processing',
        title: 'En préparation',
        subtitle: 'Traitement & emballage',
        headerBg: 'bg-blue-500',
        badgeBg: 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200 border-blue-200 dark:border-blue-700',
        columnBg: 'bg-blue-50/20 dark:bg-blue-950/10',
        ringColor: 'ring-blue-400',
    },
    {
        key: 'shipped',
        title: 'En cours d\'expédition',
        subtitle: 'Remis au transporteur',
        headerBg: 'bg-purple-500',
        badgeBg: 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200 border-purple-200 dark:border-purple-700',
        columnBg: 'bg-purple-50/20 dark:bg-purple-950/10',
        ringColor: 'ring-purple-400',
    },
    {
        key: 'delivered',
        title: 'Livrée',
        subtitle: 'Remise au client',
        headerBg: 'bg-emerald-500',
        badgeBg: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200 border-emerald-200 dark:border-emerald-700',
        columnBg: 'bg-emerald-50/20 dark:bg-emerald-950/10',
        ringColor: 'ring-emerald-400',
    },
    {
        key: 'cancelled',
        title: 'Annulée',
        subtitle: 'Commandes annulées',
        headerBg: 'bg-rose-500',
        badgeBg: 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200 border-rose-200 dark:border-rose-700',
        columnBg: 'bg-rose-50/20 dark:bg-rose-950/10',
        ringColor: 'ring-rose-400',
    },
];

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200 border-amber-200',
        processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200 border-blue-200',
        shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200 border-purple-200',
        delivered: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200 border-emerald-200',
        cancelled: 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-200 border-rose-200',
    };
    return colors[status] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getStatusLabel = (status) => {
    const col = KANBAN_COLUMNS.find((c) => c.key === status);
    return col ? col.title : status;
};

const getPaymentBadge = (status) => {
    const badges = {
        paid: { bg: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300', label: 'Payé' },
        pending: { bg: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300', label: 'En attente' },
        failed: { bg: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300', label: 'Échoué' },
        refunded: { bg: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300', label: 'Remboursé' },
    };
    return badges[status] || { bg: 'bg-slate-100 text-slate-700 border-slate-200', label: status || 'N/A' };
};

// Group orders by status
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

// Column totals: order amount and admin margin profit
const columnStats = computed(() => {
    const stats = {};
    for (const col of KANBAN_COLUMNS) {
        const list = ordersByStatus.value[col.key] || [];
        const totalAmount = list.reduce((sum, o) => sum + Number(o.total_amount || 0), 0);
        const totalProfit = list.reduce((sum, o) => sum + Number(o.admin_profit || 0), 0);
        stats[col.key] = { totalAmount, totalProfit, count: list.length };
    }
    return stats;
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

// Execute status update (used by Drag & Drop and Dropdown select)
const executeStatusChange = async (order, newStatus) => {
    const oldStatus = order.status;

    // Optimistic UI update
    order.status = newStatus;
    updatingOrderIds.value.add(order.id);

    try {
        const response = await axios.patch(
            route('admin.orders.status', order.id),
            { status: newStatus },
            {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        );

        if (response.data && response.data.success) {
            showToast(response.data.message || `Commande #${order.order_number} mise à jour.`, 'success');
            if (response.data.order) {
                const idx = localOrders.value.findIndex((o) => o.id === order.id);
                if (idx !== -1) {
                    localOrders.value[idx] = { ...localOrders.value[idx], ...response.data.order };
                }
            }
        } else {
            showToast(`Statut de la commande #${order.order_number} mis à jour.`, 'success');
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
    <Head title="Gestion des Commandes - Admin" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
                        Gestion des Commandes
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Pilotez, validez et suivez l'avancement des commandes et des marges admin.
                    </p>
                </div>

                <!-- View Switcher -->
                <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-700/60 rounded-xl border border-gray-200 dark:border-gray-600 shadow-xs">
                    <button
                        type="button"
                        @click="setView('table')"
                        :class="[
                            'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
                            currentView === 'table'
                                ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-xs border border-gray-200/60 dark:border-gray-600'
                                : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'
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
                                ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-xs border border-gray-200/60 dark:border-gray-600'
                                : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'
                        ]"
                        title="Affichage Kanban interactif par statuts"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        <span>Kanban</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Filters Component (Synchronized across both views) -->
                <TableFilters
                    :filters="filters"
                    :show-search="true"
                    :show-status="true"
                    :show-date-range="true"
                    :status-options="[
                        { value: 'pending', label: 'En attente (Pending)' },
                        { value: 'processing', label: 'En préparation (Processing)' },
                        { value: 'shipped', label: 'Expédiée (Shipped)' },
                        { value: 'delivered', label: 'Livrée (Delivered)' },
                        { value: 'cancelled', label: 'Annulée (Cancelled)' }
                    ]"
                    placeholder="Recherche par N° commande, client, email..."
                />

                <!-- 1. TABLE VIEW (Original Admin Orders Table) -->
                <div v-if="currentView === 'table'" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="localOrders.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/60">
                                    <tr>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            N° Commande
                                        </th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Client
                                        </th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total
                                        </th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                                            Marge Profit Admin
                                        </th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Statut
                                        </th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="order in localOrders"
                                        :key="order.id"
                                        class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap font-bold text-sm text-gray-900 dark:text-white">
                                            #{{ order.order_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ order.user?.name || 'Client invité' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ order.user?.email || order.phone || 'Sans email' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            {{ formatDate(order.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white font-display">
                                            {{ formatPrice(order.total_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                +{{ formatPrice(order.admin_profit) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full border', getStatusColor(order.status)]">
                                                {{ getStatusLabel(order.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-3">
                                                <!-- Inline Quick Status changer for admin -->
                                                <select
                                                    :value="order.status"
                                                    @change="executeStatusChange(order, $event.target.value)"
                                                    :disabled="isUpdating(order.id)"
                                                    class="text-xs py-1 px-2 rounded-lg border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold focus:ring-blue-500"
                                                >
                                                    <option value="pending">En attente</option>
                                                    <option value="processing">En préparation</option>
                                                    <option value="shipped">Expédiée</option>
                                                    <option value="delivered">Livrée</option>
                                                    <option value="cancelled">Annulée</option>
                                                </select>

                                                <Link
                                                    :href="route('admin.orders.show', order.id)"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                                >
                                                    <span>Détails</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="text-center py-12">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Aucune commande trouvée</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Ajustez vos critères de recherche ou réinitialisez les filtres.
                            </p>
                        </div>

                        <!-- Pagination -->
                        <div v-if="orders?.links && orders.links.length > 3" class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-4">
                            <nav class="flex items-center justify-center space-x-1">
                                <Link 
                                    v-for="(link, index) in orders.links" 
                                    :key="index"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
                                        link.active 
                                            ? 'bg-blue-600 text-white shadow-xs' 
                                            : link.url 
                                                ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' 
                                                : 'text-gray-400 dark:text-gray-600 pointer-events-none'
                                    ]"
                                    v-html="link.label"
                                />
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- 2. KANBAN VIEW (Interactive Task Board with Profit Margin & Drag and Drop) -->
                <div v-else class="space-y-4">
                    <!-- Instruction bar -->
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 px-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>Glissez et déposez les cartes d'une colonne à l'autre pour actualiser le statut de la commande.</span>
                        </div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300">
                            {{ localOrders.length }} commande(s) affichée(s)
                        </span>
                    </div>

                    <!-- Kanban Columns -->
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
                                    ? `border-blue-500 bg-blue-50/40 dark:bg-blue-950/30 ring-2 ${column.ringColor} shadow-md`
                                    : 'border-gray-200 dark:border-gray-700 bg-gray-100/70 dark:bg-gray-800/60'
                            ]"
                        >
                            <!-- Column Header -->
                            <div class="p-3.5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-t-2xl shadow-2xs">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span :class="['w-2.5 h-2.5 rounded-full', column.headerBg]"></span>
                                        <h3 class="font-bold text-xs text-gray-900 dark:text-white tracking-tight">
                                            {{ column.title }}
                                        </h3>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                                        {{ columnStats[column.key]?.count || 0 }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                    <span class="font-bold text-gray-900 dark:text-gray-200 font-display">
                                        {{ formatPrice(columnStats[column.key]?.totalAmount || 0) }}
                                    </span>
                                    <span class="inline-flex items-center text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                        Marge: +{{ formatPrice(columnStats[column.key]?.totalProfit || 0) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Column Cards Container -->
                            <div class="p-3 flex-1 flex flex-col gap-3 min-h-[400px] max-h-[calc(100vh-320px)] overflow-y-auto scrollbar-thin">
                                <div
                                    v-for="order in (ordersByStatus[column.key] || [])"
                                    :key="order.id"
                                    :draggable="!isUpdating(order.id)"
                                    @dragstart="onDragStart($event, order)"
                                    @dragend="onDragEnd"
                                    :class="[
                                        'group relative bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-3.5 shadow-2xs transition-all duration-200',
                                        isUpdating(order.id)
                                            ? 'opacity-60 pointer-events-none'
                                            : 'hover:shadow-md hover:border-gray-300 dark:hover:border-gray-500 cursor-grab active:cursor-grabbing',
                                        draggedOrder?.id === order.id ? 'opacity-40 ring-2 ring-blue-400' : ''
                                    ]"
                                >
                                    <!-- Updating spinner overlay -->
                                    <div
                                        v-if="isUpdating(order.id)"
                                        class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-2xs rounded-xl flex items-center justify-center z-10"
                                    >
                                        <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 dark:text-blue-400">
                                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span>Mise à jour...</span>
                                        </div>
                                    </div>

                                    <!-- Top Row: Order Number & Date -->
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="font-bold text-xs text-gray-900 dark:text-white">
                                            #{{ order.order_number }}
                                        </span>
                                        <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">
                                            {{ formatDate(order.created_at) }}
                                        </span>
                                    </div>

                                    <!-- Customer details -->
                                    <div class="space-y-1 mb-2.5">
                                        <div class="flex items-center gap-1.5 text-xs text-gray-800 dark:text-gray-200 font-semibold truncate">
                                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span class="truncate">{{ order.user?.name || 'Client' }}</span>
                                        </div>

                                        <div v-if="order.user?.email" class="flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                            <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span class="truncate">{{ order.user.email }}</span>
                                        </div>

                                        <div v-if="order.phone" class="flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                                            <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <a :href="`tel:${order.phone}`" class="hover:text-blue-600 hover:underline">
                                                {{ order.phone }}
                                            </a>
                                        </div>

                                        <div v-if="order.shipping_city" class="flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                            <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="truncate">{{ order.shipping_city }}</span>
                                        </div>
                                    </div>

                                    <!-- Vendor snapshot & item count -->
                                    <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700 mb-3 flex items-center justify-between text-[11px]">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">
                                            {{ order.items?.length || 0 }} article{{ (order.items?.length || 0) > 1 ? 's' : '' }}
                                        </span>
                                        <span
                                            v-if="order.items?.[0]?.product?.vendor?.shop_name || order.items?.[0]?.vendor?.shop_name"
                                            class="text-[10px] text-gray-500 dark:text-gray-400 truncate max-w-[120px]"
                                            :title="order.items?.[0]?.product?.vendor?.shop_name || order.items?.[0]?.vendor?.shop_name"
                                        >
                                            🏪 {{ order.items?.[0]?.product?.vendor?.shop_name || order.items?.[0]?.vendor?.shop_name }}
                                        </span>
                                    </div>

                                    <!-- Price & Profit Row -->
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                                        <div>
                                            <p class="text-[9px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider">Total</p>
                                            <p class="font-bold text-sm text-gray-900 dark:text-white font-display">
                                                {{ formatPrice(order.total_amount) }}
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-[9px] uppercase font-bold text-emerald-600 dark:text-emerald-400 tracking-wider">Marge Admin</p>
                                            <span class="inline-flex items-center text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                                +{{ formatPrice(order.admin_profit) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Payment & Action Row -->
                                    <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                                        <!-- Quick Status Dropdown -->
                                        <select
                                            :value="order.status"
                                            @change="executeStatusChange(order, $event.target.value)"
                                            :disabled="isUpdating(order.id)"
                                            class="text-[10px] py-1 pl-2 pr-6 rounded-md border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 font-semibold text-gray-700 dark:text-gray-200 focus:ring-blue-500"
                                            title="Changer de statut"
                                        >
                                            <option value="pending">En attente</option>
                                            <option value="processing">En préparation</option>
                                            <option value="shipped">Expédiée</option>
                                            <option value="delivered">Livrée</option>
                                            <option value="cancelled">Annulée</option>
                                        </select>

                                        <Link
                                            :href="route('admin.orders.show', order.id)"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                        >
                                            <span>Détails &rarr;</span>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Empty column placeholder -->
                                <div
                                    v-if="!ordersByStatus[column.key] || ordersByStatus[column.key].length === 0"
                                    class="h-44 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl flex flex-col items-center justify-center p-4 text-center text-gray-400 dark:text-gray-500"
                                >
                                    <svg class="w-6 h-6 mb-1 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Aucune commande</span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">Glissez une commande ici</span>
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
                class="fixed bottom-5 right-5 z-50 max-w-sm w-full bg-white dark:bg-gray-800 rounded-2xl shadow-xl border p-4 flex items-start gap-3"
                :class="toast.type === 'error' ? 'border-rose-200 dark:border-rose-800 bg-rose-50/50 dark:bg-rose-950/40' : 'border-emerald-200 dark:border-emerald-800 bg-emerald-50/50 dark:bg-emerald-950/40'"
            >
                <div
                    class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0"
                    :class="toast.type === 'error' ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-300' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300'"
                >
                    <svg v-if="toast.type === 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-xs font-bold" :class="toast.type === 'error' ? 'text-rose-900 dark:text-rose-200' : 'text-emerald-900 dark:text-emerald-200'">
                        {{ toast.type === 'error' ? 'Erreur' : 'Statut mis à jour' }}
                    </p>
                    <p class="text-xs mt-0.5 leading-relaxed" :class="toast.type === 'error' ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300'">
                        {{ toast.message }}
                    </p>
                </div>
                <button
                    type="button"
                    @click="closeToast"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </transition>
    </DashboardLayout>
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
