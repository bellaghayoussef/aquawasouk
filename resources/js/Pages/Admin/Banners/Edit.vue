<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    banner: Object,
});

const form = useForm({
    _method: 'PUT',
    title: props.banner.title || '',
    page: props.banner.page || 'home',
    image: null,
    link_url: props.banner.link_url || '',
    sort_order: props.banner.sort_order || 0,
    is_active: Boolean(props.banner.is_active),
});

const submit = () => {
    form.post(route('admin.banners.update', props.banner.id), {
        onSuccess: () => {}, // Stay on page or redirect handled by controller
    });
};
</script>

<template>
    <Head title="Edit Banner" />

    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Banner
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Current Image Preview -->
                            <div v-if="banner.image_url" class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Image</label>
                                <img :src="banner.image_url" alt="Current Banner" class="h-32 w-auto object-cover rounded shadow-md">
                            </div>

                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title (Optional)</label>
                                <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                                <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">{{ form.errors.title }}</div>
                            </div>

                            <!-- Page -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Display Page</label>
                                <select v-model="form.page" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="home">Home Page</option>
                                    <option value="vendors">Vendors Page</option>
                                    <option value="products">Products Page</option>
                                    <option value="product_detail">Product Detail Page</option>
                                </select>
                                <div v-if="form.errors.page" class="text-red-600 text-sm mt-1">{{ form.errors.page }}</div>
                            </div>

                            <!-- Image -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Image (Optional)</label>
                                <input @input="form.image = $event.target.files[0]" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-white" />
                                <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image.</p>
                                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="mt-2 w-full h-2 rounded overflow-hidden">
                                    {{ form.progress.percentage }}%
                                </progress>
                                <div v-if="form.errors.image" class="text-red-600 text-sm mt-1">{{ form.errors.image }}</div>
                            </div>

                            <!-- Link URL -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Link URL (Optional)</label>
                                <input v-model="form.link_url" type="url" placeholder="https://..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                                <div v-if="form.errors.link_url" class="text-red-600 text-sm mt-1">{{ form.errors.link_url }}</div>
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
                                <input v-model="form.sort_order" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            </div>

                            <!-- Status -->
                            <div class="flex items-center">
                                <input v-model="form.is_active" type="checkbox" id="is_active" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Active</label>
                            </div>

                            <div class="flex justify-end gap-3">
                                <Link :href="route('admin.banners.index')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-md hover:bg-gray-300 transition">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50">
                                    {{ form.processing ? 'Saving...' : 'Update Banner' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
