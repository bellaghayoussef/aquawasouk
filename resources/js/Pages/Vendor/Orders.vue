<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TableFilters from '@/Components/TableFilters.vue';

const props = defineProps({
    orders: Object,
    vendor: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// View mode state (stored in localStorage)
const STORAGE_KEY = 'aquawasouk_vendor_orders_view';
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

    // If switching to Kanban and page size is small, fetch a larger batch for smooth board experience
    if (view === 'kanban' && (props.orders?.per_page || 20) < 50) {
        router.get(
            route('vendor.orders'),
            { ...props.filters, view: 'kanban', per_page: 100 },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }
};

// Local orders copy for optimistic updates during drag and drop
const localOrderGroups = ref([]);

watch(
    () => props.orders?.data,
    (newData) => {
        localOrderGroups.value = (newData || []).map((og) => ({
            ...og,
            order: { ...og.order },
            items: og.items ? og.items.map((it) => ({ ...it })) : [],
        }));
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

// Status definitions (strictly matching database enum: pending, processing, shipped, delivered, cancelled)
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
        subtitle: 'Emballage & logistique',
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
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 ring-yellow-200 dark:ring-yellow-800',
        processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 ring-blue-200 dark:ring-blue-800',
        shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 ring-purple-200 dark:ring-purple-800',
        delivered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 ring-green-200 dark:ring-green-800',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 ring-red-200 dark:ring-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 ring-gray-200';
};

const getStatusLabel = (status) => {
    const col = KANBAN_COLUMNS.find((c) => c.key === status);
    return col ? col.title : status;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('fr-TN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Group orders for Kanban
const ordersByStatus = computed(() => {
    const map = {
        pending: [],
        processing: [],
        shipped: [],
        delivered: [],
        cancelled: [],
    };

    for (const group of localOrderGroups.value) {
        const s = group.order?.status || 'pending';
        if (map[s]) {
            map[s].push(group);
        } else {
            map.pending.push(group);
        }
    }
    return map;
});

// Column totals
const columnStats = computed(() => {
    const stats = {};
    for (const col of KANBAN_COLUMNS) {
        const list = ordersByStatus.value[col.key] || [];
        const totalAmount = list.reduce((sum, g) => sum + Number(g.total || 0), 0);
        const totalCommission = list.reduce((sum, g) => sum + Number(g.commission || 0), 0);
        stats[col.key] = { totalAmount, totalCommission, count: list.length };
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
const draggedOrderGroup = ref(null);
const activeDropColumn = ref(null);
const updatingOrderIds = ref(new Set());

const isUpdating = (orderId) => updatingOrderIds.value.has(orderId);

const onDragStart = (event, orderGroup) => {
    draggedOrderGroup.value = orderGroup;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(orderGroup.order.id));
};

const onDragEnd = () => {
    draggedOrderGroup.value = null;
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

    if (!draggedOrderGroup.value) return;
    const orderGroup = draggedOrderGroup.value;
    const oldStatus = orderGroup.order.status;

    if (oldStatus === targetStatus) {
        draggedOrderGroup.value = null;
        return;
    }

    await executeStatusChange(orderGroup, targetStatus);
    draggedOrderGroup.value = null;
};

// Execute status update (used by Drag & Drop and Dropdown select)
const executeStatusChange = async (orderGroup, newStatus) => {
    const oldStatus = orderGroup.order.status;
    const firstItemId = orderGroup.items?.[0]?.id;

    if (!firstItemId) {
        showToast('Article de commande introuvable.', 'error');
        return;
    }

    // Optimistic UI update
    orderGroup.order.status = newStatus;
    if (orderGroup.items) {
        orderGroup.items.forEach((it) => (it.status = newStatus));
    }
    updatingOrderIds.value.add(orderGroup.order.id);

    try {
        const response = await axios.patch(
            route('vendor.orders.status', firstItemId),
            {
                status: newStatus,
                update_all: true,
            },
            {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        );

        if (response.data && response.data.success) {
            showToast(response.data.message || `Commande #${orderGroup.order.order_number} mise à jour.`, 'success');
        } else {
            showToast(`Statut de la commande #${orderGroup.order.order_number} mis à jour.`, 'success');
        }
    } catch (err) {
        // Rollback on error
        orderGroup.order.status = oldStatus;
        if (orderGroup.items) {
            orderGroup.items.forEach((it) => (it.status = oldStatus));
        }
        const msg = err.response?.data?.message || err.message || 'Impossible de mettre à jour le statut.';
        showToast(msg, 'error');
    } finally {
        updatingOrderIds.value.delete(orderGroup.order.id);
    }
};

// SMS Modal State
const showSmsModal = ref(false);
const activeOrderGroup = ref(null);
const smsForm = useForm({
    phone: '',
    message: '',
});

const smsCharCount = computed(() => smsForm.message.length);
const smsSegments = computed(() => {
    if (smsCharCount.value === 0) return 0;
    if (smsCharCount.value <= 160) return 1;
    return Math.ceil(smsCharCount.value / 153);
});

const applySmsTemplate = (type) => {
    if (!activeOrderGroup.value) return;
    const orderGroup = activeOrderGroup.value;
    const customerName = orderGroup.order.user?.name || 'Cher client';
    const orderNum = orderGroup.order.order_number;
    const shopName = props.vendor?.shop_name || 'AquawaSouk';
    const amountStr = formatPrice(orderGroup.total);

    switch (type) {
        case 'shipped':
            smsForm.message = `Bonjour ${customerName}, votre commande #${orderNum} chez ${shopName} est expédiée ! Montant à régler à la livraison : ${amountStr}.`;
            break;
        case 'processing':
            smsForm.message = `Bonjour ${customerName}, nous préparons votre commande #${orderNum} chez ${shopName}. Elle sera bientôt remise au livreur.`;
            break;
        case 'address':
            smsForm.message = `Bonjour ${customerName}, nous tentons de vous joindre pour confirmer l'adresse de votre commande #${orderNum}. Merci de nous rappeler. ${shopName}`;
            break;
        case 'delivery_today':
            smsForm.message = `Bonjour ${customerName}, votre commande #${orderNum} (${shopName}) vous sera livrée aujourd'hui. Merci de garder votre téléphone disponible.`;
            break;
    }
};

const openSmsModal = (orderGroup, templateType = 'shipped') => {
    activeOrderGroup.value = orderGroup;
    smsForm.phone = orderGroup.order.phone || '';
    applySmsTemplate(templateType);
    showSmsModal.value = true;
};

const submitSms = () => {
    if (!activeOrderGroup.value?.items?.[0]?.id) return;
    const orderItemId = activeOrderGroup.value.items[0].id;

    smsForm.post(route('vendor.orders.sms', orderItemId), {
        preserveScroll: true,
        onSuccess: () => {
            showSmsModal.value = false;
            activeOrderGroup.value = null;
            showToast('SMS envoyé avec succès !', 'success');
        },
        onError: () => {
            showToast('Échec de l\'envoi du SMS.', 'error');
        },
    });
};
</script>

<template>
    <Head title="Mes Commandes Vendeur - AquawaSouk" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-display font-black text-2xl text-slate-900 dark:text-white tracking-tight">
                        Commandes reçues
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Gérez vos expéditions, bordereaux et statuts de livraison en mode Liste ou Kanban
                    </p>
                </div>

                <!-- View Switcher -->
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                    <button
                        type="button"
                        @click="setView('table')"
                        :class="[
                            'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer',
                            currentView === 'table'
                                ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs border border-slate-200/60 dark:border-slate-700'
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'
                        ]"
                        title="Affichage classique en liste"
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
                            'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer',
                            currentView === 'kanban'
                                ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs border border-slate-200/60 dark:border-slate-700'
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'
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
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Filters Container (Synchronized across both views) -->
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
                <div v-if="!localOrderGroups || localOrderGroups.length === 0" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-12 text-center shadow-sm">
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

                <!-- 1. TABLE VIEW (Original Vendor Orders Cards List) -->
                <div v-else-if="currentView === 'table'" class="space-y-4">
                    <div
                        v-for="orderGroup in localOrderGroups"
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
                                        {{ getStatusLabel(orderGroup.order.status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <!-- Status update shortcut -->
                                <select
                                    :value="orderGroup.order.status"
                                    @change="executeStatusChange(orderGroup, $event.target.value)"
                                    :disabled="isUpdating(orderGroup.order.id)"
                                    class="text-xs py-1.5 px-2.5 rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold focus:ring-brand-500 shadow-2xs"
                                >
                                    <option value="pending">En attente</option>
                                    <option value="processing">En préparation</option>
                                    <option value="shipped">Expédiée</option>
                                    <option value="delivered">Livrée</option>
                                    <option value="cancelled">Annulée</option>
                                </select>

                                <button
                                    type="button"
                                    @click="openSmsModal(orderGroup, 'shipped')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 text-sky-700 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/40 text-xs font-bold rounded-xl transition cursor-pointer shadow-xs"
                                    title="Envoyer un SMS au client"
                                >
                                    <span>📱</span>
                                    <span>SMS</span>
                                </button>
                                <a
                                    v-if="orderGroup.items?.[0]?.id"
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
                                    v-if="orderGroup.items?.[0]?.id"
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
                                <div class="flex items-center gap-2">
                                    <span>Client : <strong class="text-slate-800 dark:text-slate-200">{{ orderGroup.order.user?.name || 'Client' }}</strong></span>
                                    <button
                                        v-if="orderGroup.order.phone"
                                        type="button"
                                        @click="openSmsModal(orderGroup, 'address')"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-sky-100 dark:hover:bg-sky-900/40 text-slate-600 dark:text-slate-400 hover:text-sky-600 font-mono text-[11px] transition cursor-pointer"
                                        title="Cliquer pour envoyer un SMS à ce numéro"
                                    >
                                        <span>📞</span>
                                        <span>{{ orderGroup.order.phone }}</span>
                                    </button>
                                </div>
                                <span>{{ orderGroup.items?.length || 0 }} article(s)</span>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="item in orderGroup.items?.slice(0, 3)"
                                    :key="item.id"
                                    class="flex items-center gap-4 p-2 rounded-xl bg-slate-50/50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800/60"
                                >
                                    <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 overflow-hidden">
                                        <img
                                            v-if="item.product?.primary_image"
                                            :src="`/uploads/${item.product.primary_image.image_path}`"
                                            :alt="item.product.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <svg v-else class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ item.product?.name || 'Produit' }}</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Qté: {{ item.quantity }} × {{ formatPrice(item.price) }}</p>
                                    </div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">
                                        {{ formatPrice(item.price * item.quantity) }}
                                    </div>
                                </div>
                                <p v-if="orderGroup.items?.length > 3" class="text-xs text-slate-500 dark:text-slate-400 italic text-right">
                                    + {{ orderGroup.items.length - 3 }} autre(s) article(s)
                                </p>
                            </div>
                        </div>

                        <!-- Order Footer -->
                        <div class="bg-slate-50/40 dark:bg-slate-800/20 px-6 py-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                            <div>
                                Paiement :
                                <span :class="orderGroup.order.payment_status === 'paid' ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-amber-600 dark:text-amber-400 font-bold'">
                                    {{ (orderGroup.order.payment_status || 'pending').toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                Commission : <span class="font-bold text-slate-900 dark:text-white">{{ formatPrice(orderGroup.commission) }}</span>
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

                <!-- 2. KANBAN VIEW (Vendor Board with Drag and Drop) -->
                <div v-else class="space-y-4">
                    <!-- Instruction bar -->
                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 px-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                            <span>Glissez et déposez les cartes d'une colonne à l'autre pour actualiser le statut de commande.</span>
                        </div>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">
                            {{ localOrderGroups.length }} commande(s) affichée(s)
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
                                    ? `border-brand-500 bg-brand-50/40 dark:bg-brand-950/30 ring-2 ${column.ringColor} shadow-md`
                                    : 'border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60'
                            ]"
                        >
                            <!-- Column Header -->
                            <div class="p-3.5 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-t-2xl shadow-2xs">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span :class="['w-2.5 h-2.5 rounded-full', column.headerBg]"></span>
                                        <h3 class="font-bold text-xs text-slate-900 dark:text-white tracking-tight">
                                            {{ column.title }}
                                        </h3>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                        {{ columnStats[column.key]?.count || 0 }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <span class="font-bold text-slate-900 dark:text-slate-200 font-display">
                                        {{ formatPrice(columnStats[column.key]?.totalAmount || 0) }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                        Com: {{ formatPrice(columnStats[column.key]?.totalCommission || 0) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Column Cards Container -->
                            <div class="p-3 flex-1 flex flex-col gap-3 min-h-[400px] max-h-[calc(100vh-320px)] overflow-y-auto scrollbar-thin">
                                <div
                                    v-for="orderGroup in (ordersByStatus[column.key] || [])"
                                    :key="orderGroup.order.id"
                                    :draggable="!isUpdating(orderGroup.order.id)"
                                    @dragstart="onDragStart($event, orderGroup)"
                                    @dragend="onDragEnd"
                                    :class="[
                                        'group relative bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 shadow-2xs transition-all duration-200',
                                        isUpdating(orderGroup.order.id)
                                            ? 'opacity-60 pointer-events-none'
                                            : 'hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700 cursor-grab active:cursor-grabbing',
                                        draggedOrderGroup?.order?.id === orderGroup.order.id ? 'opacity-40 ring-2 ring-brand-400' : ''
                                    ]"
                                >
                                    <!-- Updating spinner overlay -->
                                    <div
                                        v-if="isUpdating(orderGroup.order.id)"
                                        class="absolute inset-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xs rounded-xl flex items-center justify-center z-10"
                                    >
                                        <div class="flex items-center gap-2 text-xs font-semibold text-brand-600 dark:text-brand-400">
                                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span>Mise à jour...</span>
                                        </div>
                                    </div>

                                    <!-- Top Row: Order Number & Date -->
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="font-bold font-mono text-xs text-slate-900 dark:text-white">
                                            #{{ orderGroup.order.order_number }}
                                        </span>
                                        <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">
                                            {{ formatDate(orderGroup.created_at) }}
                                        </span>
                                    </div>

                                    <!-- Customer details & Phone -->
                                    <div class="space-y-1 mb-2.5">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-800 dark:text-slate-200 font-semibold truncate">
                                            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span class="truncate">{{ orderGroup.order.user?.name || 'Client' }}</span>
                                        </div>

                                        <div v-if="orderGroup.order.phone" class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                                <a :href="`tel:${orderGroup.order.phone}`" class="hover:text-brand-600 hover:underline">
                                                    {{ orderGroup.order.phone }}
                                                </a>
                                            </div>

                                            <button
                                                type="button"
                                                @click="openSmsModal(orderGroup, 'address')"
                                                class="text-[10px] px-1.5 py-0.5 rounded bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-100 font-semibold"
                                                title="Envoyer un SMS"
                                            >
                                                📱 SMS
                                            </button>
                                        </div>

                                        <div v-if="orderGroup.order.shipping_city" class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                            <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="truncate">{{ orderGroup.order.shipping_city }}</span>
                                        </div>
                                    </div>

                                    <!-- Products preview (up to 2 items) -->
                                    <div class="space-y-1.5 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 mb-3 text-[11px]">
                                        <div class="flex items-center justify-between font-medium text-slate-600 dark:text-slate-400 text-[10px] uppercase tracking-wider mb-1">
                                            <span>Articles ({{ orderGroup.items?.length || 0 }})</span>
                                        </div>
                                        <div
                                            v-for="item in orderGroup.items?.slice(0, 2)"
                                            :key="item.id"
                                            class="flex items-center gap-2 truncate"
                                        >
                                            <div class="w-6 h-6 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 overflow-hidden">
                                                <img
                                                    v-if="item.product?.primary_image"
                                                    :src="`/uploads/${item.product.primary_image.image_path}`"
                                                    :alt="item.product.name"
                                                    class="w-full h-full object-cover"
                                                />
                                                <span v-else class="text-[9px]">📦</span>
                                            </div>
                                            <span class="truncate text-slate-800 dark:text-slate-200 font-medium">
                                                {{ item.quantity }} × {{ item.product?.name }}
                                            </span>
                                        </div>
                                        <p v-if="(orderGroup.items?.length || 0) > 2" class="text-[10px] text-slate-400 italic">
                                            + {{ orderGroup.items.length - 2 }} autre(s)
                                        </p>
                                    </div>

                                    <!-- Price & Payment Status -->
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
                                        <div>
                                            <p class="text-[9px] uppercase font-bold text-slate-400 dark:text-slate-500 tracking-wider">Total Vendeur</p>
                                            <p class="font-bold text-sm text-brand-600 dark:text-brand-400 font-display">
                                                {{ formatPrice(orderGroup.total) }}
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <span
                                                :class="orderGroup.order.payment_status === 'paid' ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200' : 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border-amber-200'"
                                                class="px-2 py-0.5 text-[10px] font-bold rounded-full border inline-block"
                                            >
                                                {{ (orderGroup.order.payment_status || 'PENDING').toUpperCase() }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Bottom Action Row -->
                                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-1.5 flex-wrap">
                                        <!-- Quick Status Dropdown -->
                                        <select
                                            :value="orderGroup.order.status"
                                            @change="executeStatusChange(orderGroup, $event.target.value)"
                                            :disabled="isUpdating(orderGroup.order.id)"
                                            class="text-[10px] py-1 pl-2 pr-5 rounded-md border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-semibold text-slate-700 dark:text-slate-200 focus:ring-brand-500"
                                            title="Changer de statut"
                                        >
                                            <option value="pending">En attente</option>
                                            <option value="processing">En préparation</option>
                                            <option value="shipped">Expédiée</option>
                                            <option value="delivered">Livrée</option>
                                            <option value="cancelled">Annulée</option>
                                        </select>

                                        <div class="flex items-center gap-1.5">
                                            <a
                                                v-if="orderGroup.items?.[0]?.id"
                                                :href="route('vendor.orders.waybill', orderGroup.items[0].id)"
                                                target="_blank"
                                                class="text-[10px] px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 rounded font-semibold transition"
                                                title="Imprimer bordereau"
                                            >
                                                📄 Bordereau
                                            </a>
                                            <Link
                                                v-if="orderGroup.items?.[0]?.id"
                                                :href="route('vendor.orders.show', orderGroup.items[0].id)"
                                                class="text-[10px] font-bold text-brand-600 dark:text-brand-400 hover:underline"
                                            >
                                                Gérer &rarr;
                                            </Link>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty Column Placeholder -->
                                <div
                                    v-if="!ordersByStatus[column.key] || ordersByStatus[column.key].length === 0"
                                    class="h-44 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl flex flex-col items-center justify-center p-4 text-center text-slate-400 dark:text-slate-600"
                                >
                                    <svg class="w-6 h-6 mb-1 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Aucune commande</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-600 mt-0.5">Glissez une commande ici</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMS Modal (Shared between Table and Kanban views) -->
        <div 
            v-if="showSmsModal && activeOrderGroup" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click.self="showSmsModal = false"
        >
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 transform transition-all animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg">
                            📱
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">
                                Envoyer un SMS au client
                            </h3>
                            <p class="text-xs text-slate-500">
                                Commande #{{ activeOrderGroup.order.order_number }} • {{ activeOrderGroup.order.user?.name || 'Client' }}
                            </p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="showSmsModal = false" 
                        class="p-1 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitSms" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Numéro de téléphone du client
                        </label>
                        <input 
                            type="text" 
                            v-model="smsForm.phone" 
                            required
                            placeholder="ex: 26260262 ou +216..." 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition"
                        />
                    </div>

                    <!-- Quick Templates -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Modèles rapides
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                @click="applySmsTemplate('shipped')"
                                class="p-2 text-left rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-500 dark:hover:border-brand-500 bg-slate-50/50 dark:bg-slate-800/40 text-xs text-slate-700 dark:text-slate-300 hover:text-brand-600 transition cursor-pointer"
                            >
                                🚚 Expédiée + Montant
                            </button>
                            <button
                                type="button"
                                @click="applySmsTemplate('delivery_today')"
                                class="p-2 text-left rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-500 dark:hover:border-brand-500 bg-slate-50/50 dark:bg-slate-800/40 text-xs text-slate-700 dark:text-slate-300 hover:text-brand-600 transition cursor-pointer"
                            >
                                📦 Livraison aujourd'hui
                            </button>
                            <button
                                type="button"
                                @click="applySmsTemplate('processing')"
                                class="p-2 text-left rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-500 dark:hover:border-brand-500 bg-slate-50/50 dark:bg-slate-800/40 text-xs text-slate-700 dark:text-slate-300 hover:text-brand-600 transition cursor-pointer"
                            >
                                ⚙️ En préparation
                            </button>
                            <button
                                type="button"
                                @click="applySmsTemplate('address')"
                                class="p-2 text-left rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-500 dark:hover:border-brand-500 bg-slate-50/50 dark:bg-slate-800/40 text-xs text-slate-700 dark:text-slate-300 hover:text-brand-600 transition cursor-pointer"
                            >
                                📍 Confirmation adresse
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Message SMS
                            </label>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                                {{ smsCharCount }} car. ({{ smsSegments }} SMS)
                            </span>
                        </div>
                        <textarea
                            v-model="smsForm.message"
                            rows="4"
                            required
                            maxlength="459"
                            placeholder="Écrivez votre message..."
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs leading-relaxed focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="showSmsModal = false"
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            :disabled="smsForm.processing || !smsForm.message.trim()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
                        >
                            <span v-if="smsForm.processing">Envoi en cours...</span>
                            <span v-else>Envoyer le SMS 🚀</span>
                        </button>
                    </div>
                </form>
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
                class="fixed bottom-5 right-5 z-50 max-w-sm w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl border p-4 flex items-start gap-3"
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
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition"
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
