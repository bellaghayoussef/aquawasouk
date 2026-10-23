<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { router } from '@inertiajs/vue3';

defineProps({
    banners: Array,
});

const deleteBanner = (id) => {
    if (confirm('Are you sure you want to delete this banner?')) {
        router.delete(route('admin.banners.destroy', id));
    }
};
</script>

<template>
    <Head title="Banners Management" />

    <DashboardLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Banners Management
                </h2>
                <Link :href="route('admin.banners.create')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                    + Add Banner
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b dark:border-gray-700 text-gray-500 dark:text-gray-400">
                                    <th class="p-3">Order</th>
                                    <th>page</th>
                                    <th class="p-3">Image</th>
                                    <th class="p-3">Title</th>
                                    <th class="p-3">Link</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="banners.length === 0">
                                    <td colspan="6" class="p-6 text-center text-gray-500">No banners found.</td>
                                </tr>
                                <tr v-for="banner in banners" :key="banner.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="p-3">{{ banner.sort_order }}</td>
                                    <td class="p-3">{{ banner.page }}</td>
                                    <td class="p-3">
                                        <img :src="banner.image_url" alt="Banner" class="h-16 w-32 object-cover rounded shadow-sm">
                                    </td>
                                    <td class="p-3 font-medium text-gray-900 dark:text-white">{{ banner.title || '-' }}</td>
                                    <td class="p-3 text-sm font-mono text-gray-600 dark:text-gray-400">{{ banner.link_url || '-' }}</td>
                                    <td class="p-3">
                                        <span :class="['px-2 py-1 rounded-full text-xs font-semibold', banner.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                                            {{ banner.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="p-3 space-x-2">
                                        <Link :href="route('admin.banners.edit', banner.id)" class="text-blue-600 hover:text-blue-800">Edit</Link>
                                        <button @click="deleteBanner(banner.id)" class="text-red-600 hover:text-red-800">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
