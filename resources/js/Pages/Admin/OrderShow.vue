<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    order: Object,
});

const newStatus = ref(props.order.status);

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
    router.patch(route('admin.orders.status', props.order.id), {
        status: newStatus.value,
    }, {
        preserveScroll: true,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleString();
};
</script>

<template>
    <Head :title="`Order #${order.order_number}`" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Order #{{ order.order_number }}
                </h2>
                <Link :href="route('admin.orders')" class="text-sm text-dolphin-blue hover:text-dolphin-blue/80 dark:text-dolphin-cyan dark:hover:text-dolphin-cyan/80 font-medium">
                    ← Back to Orders
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Order Overview -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Order Date</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Customer</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ order.user.name }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ order.user.email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Amount</p>
                            <p class="text-2xl font-bold text-slate-900">
                                {{ formatPrice(order.total_amount) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-emerald-700">Marge Profit Admin</p>
                            <p class="text-2xl font-black text-emerald-600 font-display">
                                +{{ formatPrice(order.admin_profit) }}
                            </p>
                            <p class="text-[11px] text-slate-400">Encaissé dans la Caisse Admin</p>
                        </div>
                    </div>
                </div>

                <!-- Order Status Management -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Status</h3>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <select v-model="newStatus" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-dolphin-blue focus:ring focus:ring-dolphin-blue focus:ring-opacity-50">
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <button @click="updateStatus" class="px-6 py-2 bg-dolphin-blue text-white rounded-md hover:bg-dolphin-blue/90 focus:outline-none focus:ring-2 focus:ring-dolphin-blue focus:ring-offset-2 transition">
                                Update Status
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Items</h3>
                        <div class="space-y-4">
                            <div v-for="item in order.items" :key="item.id" class="flex items-center gap-6 pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0">
                                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0">
                                    <img
                                        v-if="item.product.primary_image"
                                        :src="`/uploads/${item.product.primary_image.image_path}`"
                                        :alt="item.product.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <svg v-else class="w-10 h-10 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ item.product.name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Vendor: {{ item.product.vendor.shop_name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                        {{ formatPrice(item.price) }} × {{ item.quantity }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg text-gray-900 dark:text-white">
                                        {{ formatPrice(item.price * item.quantity) }}
                                    </p>
                                    <p v-if="item.commission" class="text-xs font-bold text-emerald-600">
                                        Marge admin: +{{ formatPrice(item.commission) }}
                                    </p>
                                    <span :class="['inline-flex px-2 py-0.5 text-xs font-medium rounded-full mt-1', getStatusColor(item.status)]">
                                        {{ item.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary & Shipping Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Order Summary -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>Subtotal</span>
                                    <span>{{ formatPrice(order.total_amount - (order.shipping_cost || 0)) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>Shipping</span>
                                    <span>{{ formatPrice(order.shipping_cost || 0) }}</span>
                                </div>
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                    <div class="flex justify-between text-xl font-bold text-gray-900 dark:text-white">
                                        <span>Total</span>
                                        <span>{{ formatPrice(order.total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Shipping Information</h3>
                            <div class="text-gray-600 dark:text-gray-400">
                                <p class="font-medium text-gray-900 dark:text-white">{{ order.user.name }}</p>
                                <p>{{ order.shipping_address }}</p>
                                <p>{{ order.shipping_city }}, {{ order.shipping_postal_code }}</p>
                                <p>{{ order.shipping_country }}</p>
                                <p class="mt-2"><strong>Phone:</strong> {{ order.phone }}</p>
                            </div>
                            <div v-if="order.notes" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-2">Order Notes</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ order.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
