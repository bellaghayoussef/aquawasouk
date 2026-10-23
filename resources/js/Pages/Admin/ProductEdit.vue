<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    product: Object,
    categories: Array,
});

const form = useForm({
    name: props.product.name,
    description: props.product.description,
    price: props.product.price,
    discount_price: props.product.discount_price || '',
    stock: props.product.stock,
    category_id: props.product.category_id,
    status: props.product.status,
    is_featured: props.product.is_featured || false,
});

const submit = () => {
    form.patch(route('admin.products.update', props.product.id));
};
</script>

<template>
    <Head title="Edit Product" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Edit Product: {{ product.name }}
                </h2>
                <Link :href="route('admin.products')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">
                    ← Back to Products
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-6">
                            <!-- Vendor Info -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-200 mb-2">
                                    Vendor Information
                                </h3>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    <strong>Shop:</strong> {{ product.vendor.shop_name }}
                                </p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    <strong>Owner:</strong> {{ product.vendor.user.name }}
                                </p>
                            </div>

                            <!-- Product Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Product Name
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                    placeholder="Enter product description..."
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Price & Discount Price -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Price (TND)
                                    </label>
                                    <input
                                        id="price"
                                        v-model.number="form.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                    <div v-if="form.errors.price" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.price }}
                                    </div>
                                </div>

                                <div>
                                    <label for="discount_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Discount Price (TND) <span class="text-gray-400 text-xs">(Optional)</span>
                                    </label>
                                    <input
                                        id="discount_price"
                                        v-model.number="form.discount_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <div v-if="form.errors.discount_price" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.discount_price }}
                                    </div>
                                </div>
                            </div>

                            <!-- Stock & Category -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Stock Quantity
                                    </label>
                                    <input
                                        id="stock"
                                        v-model.number="form.stock"
                                        type="number"
                                        min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                    <div v-if="form.errors.stock" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.stock }}
                                    </div>
                                </div>

                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Category
                                    </label>
                                    <select
                                        id="category_id"
                                        v-model="form.category_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                        <option value="">Select a category</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.category_id }}
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Status
                                </label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.status }}
                                </div>
                            </div>

                            <!-- Is Featured -->
                            <div class="flex items-center">
                                <input
                                    id="is_featured"
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label for="is_featured" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Mark as Featured Product
                                </label>
                            </div>

                            <!-- Buttons -->
                            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <Link
                                    :href="route('admin.products')"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 transition"
                                >
                                    <span v-if="form.processing">Saving...</span>
                                    <span v-else>Save Changes</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
