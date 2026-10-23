<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TableFilters from '@/Components/TableFilters.vue';

defineProps({
    categories: Object,
    filters: Object,
});

const deleteCategory = (categoryId) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('admin.categories.destroy', categoryId), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Manage Categories" />

    <DashboardLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Manage Categories
                </h2>
                <Link :href="route('admin.categories.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Category
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <TableFilters
                    :filters="filters"
                    :show-search="true"
                    :show-date-range="true"
                    placeholder="Search by name or description..."
                />

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Slug
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
                                    <tr v-for="category in categories.data" :key="category.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <span v-if="category.parent" class="text-gray-400 mr-2">↳</span>
                                                {{ category.name }}
                                            </div>
                                            <div v-if="category.parent" class="text-xs text-blue-500 dark:text-blue-400">
                                                Subcategory of: {{ category.parent.name }}
                                            </div>
                                            <div v-if="category.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ category.description }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ category.slug }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ category.products_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('admin.categories.edit', category.id)" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 mr-3">
                                                Edit
                                            </Link>
                                            <button @click="deleteCategory(category.id)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="categories.links && categories.links.length > 3" class="mt-6">
                            <nav class="flex items-center justify-center space-x-2">
                                <Link 
                                    v-for="(link, index) in categories.links" 
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
