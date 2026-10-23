<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TableFilters from '@/Components/TableFilters.vue';

defineProps({
    vendors: Object,
    filters: Object,
});

const updateStatus = (vendorId, status) => {
    if (confirm(`Are you sure you want to change this vendor's status to ${status}?`)) {
        router.patch(route('admin.vendors.status', vendorId), {
            status: status,
        }, {
            preserveScroll: true,
        });
    }
};

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        inactive: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Manage Vendors" />

    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manage Vendors
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <TableFilters
                    :filters="filters"
                    :show-search="true"
                    :show-status="true"
                    :show-date-range="true"
                    :status-options="[
                        { value: 'active', label: 'Active' },
                        { value: 'inactive', label: 'Inactive' },
                        { value: 'pending', label: 'Pending' }
                    ]"
                    placeholder="Search by shop, city, country, owner..."
                />

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Vendor
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Owner
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Products
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="vendor in vendors.data" :key="vendor.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                                        {{ vendor.shop_name.charAt(0) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ vendor.shop_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ vendor.city }}, {{ vendor.country }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">{{ vendor.user.name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ vendor.user.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusColor(vendor.status)]">
                                                {{ vendor.status.charAt(0).toUpperCase() + vendor.status.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ vendor.products_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link 
                                                    :href="route('admin.vendors.edit', vendor.id)"
                                                    class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                                                    Edit
                                                </Link>
                                                <button 
                                                    v-if="vendor.status !== 'active'"
                                                    @click="updateStatus(vendor.id, 'active')"
                                                    class="text-green-600 hover:text-green-900 dark:hover:text-green-400">
                                                    Approve
                                                </button>
                                                <button 
                                                    v-if="vendor.status !== 'inactive'"
                                                    @click="updateStatus(vendor.id, 'inactive')"
                                                    class="text-red-600 hover:text-red-900 dark:hover:text-red-400">
                                                    Suspend
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div v-if="vendors.links && vendors.links.length > 3" class="mt-6">
                            <nav class="flex items-center justify-center space-x-2">
                                <Link 
                                    v-for="(link, index) in vendors.links" 
                                    :key="index"
                                    :href="link.url"
                                    :class="[
                                        'px-4 py-2 rounded-md text-sm font-medium transition',
                                        link.active 
                                            ? 'bg-blue-600 text-white' 
                                            : link.url 
                                                ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' 
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
                                    ]"
                                    :disabled="!link.url"
                                    v-html="link.label"
                                ></Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
