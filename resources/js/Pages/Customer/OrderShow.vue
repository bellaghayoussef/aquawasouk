<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    order: Object,
});

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
</script>

<template>
    <Head :title="'Order #' + order.order_number" />

    <DashboardLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Order Details
                </h2>
                <Link :href="route('customer.orders')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    ← Back to Orders
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Order Header -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row justify-between md:items-center">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    Order #{{ order.order_number }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Placed on {{ new Date(order.created_at).toLocaleDateString() }} at {{ new Date(order.created_at).toLocaleTimeString() }}
                                </p>
                            </div>
                            <div class="mt-4 md:mt-0 flex items-center">
                                <span class="mr-2 text-sm text-gray-500 dark:text-gray-400">Status:</span>
                                <span :class="['px-3 py-1 text-sm font-semibold rounded-full', getStatusColor(order.status)]">
                                    {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Order Items -->
                    <div class="col-span-1 md:col-span-2 space-y-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Items Ordered</h4>
                                <div class="flow-root">
                                    <ul role="list" class="-my-6 divide-y divide-gray-200 dark:divide-gray-700">
                                        <li v-for="item in order.items" :key="item.id" class="py-6 flex">
                                            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700">
                                                <img
                                                    v-if="item.product && item.product.primary_image"
                                                    :src="'/uploads/' + item.product.primary_image.image_path"
                                                    :alt="item.product.name"
                                                    class="h-full w-full object-cover object-center"
                                                />
                                                <div v-else class="h-full w-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            </div>

                                            <div class="ml-4 flex flex-1 flex-col">
                                                <div>
                                                    <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                        <h3>
                                                            <a :href="item.product ? route('products.show', item.product.slug) : '#'">
                                                                {{ item.product ? item.product.name : 'Product Unavailable' }}
                                                            </a>
                                                        </h3>
                                                        <p class="ml-4">{{ formatPrice(item.price * item.quantity) }}</p>
                                                    </div>
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                        Vendor: {{ item.vendor ? item.vendor.shop_name : 'Unknown Vendor' }}
                                                    </p>
                                                </div>
                                                <div class="flex flex-1 items-end justify-between text-sm">
                                                    <p class="text-gray-500 dark:text-gray-400">Qty {{ item.quantity }} x {{ formatPrice(item.price) }}</p>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary & Shipping -->
                    <div class="col-span-1 space-y-6">
                        <!-- Order Summary -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <p class="text-gray-500 dark:text-gray-400">Subtotal</p>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ formatPrice(order.total_amount - order.shipping_cost) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <p class="text-gray-500 dark:text-gray-400">Shipping</p>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ formatPrice(order.shipping_cost) }}
                                        </p>
                                    </div>
                                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex items-center justify-between">
                                        <p class="text-base font-semibold text-gray-900 dark:text-white">Total</p>
                                        <p class="text-base font-semibold text-gray-900 dark:text-white">
                                            {{ formatPrice(order.total_amount) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Shipping Information</h4>
                                <div class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                                    <p class="font-medium text-gray-900 dark:text-gray-300">
                                        {{ order.payment_method === 'cod' ? 'Cash on Delivery' : 'Online Payment' }}
                                    </p>
                                    <p class="mt-3">{{ order.shipping_address }}</p>
                                    <p>{{ order.shipping_city }}, {{ order.shipping_postal_code }}</p>
                                    <p>{{ order.shipping_country }}</p>
                                    <p class="mt-3">Phone: {{ order.phone }}</p>
                                    <div v-if="order.notes" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                        <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Order Notes:</p>
                                        <p class="italic">{{ order.notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
