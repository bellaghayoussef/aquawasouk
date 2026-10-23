<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    categories: Array,
});

const form = useForm({
    name: '',
    description: '',
    price: '',
    discount_price: '',
    category_id: '',
    stock: '',
    sku: '',
    images: [],
    is_featured: false,
});

const imagePreviews = ref([]);

const handleImageChange = (event) => {
    const files = Array.from(event.target.files);
    form.images = files;
    
    // Create previews
    imagePreviews.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    const filesArray = Array.from(form.images);
    filesArray.splice(index, 1);
    form.images = filesArray;
    imagePreviews.value.splice(index, 1);
};

const submit = () => {
    form.post(route('vendor.products.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Create Product" />

    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Create New Product
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Global Error Message -->
                            <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 dark:bg-red-900/50 border-l-4 border-red-500 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                            There were {{ Object.keys(form.errors).length }} errors with your submission
                                        </h3>
                                        <div class="mt-2 text-sm text-red-700 dark:text-red-200">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li v-for="(error, key) in form.errors" :key="key">
                                                    {{ error }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <InputLabel for="name" value="Product Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="description" value="Description" />
                                <textarea
                                    id="description"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.description"
                                    rows="4"
                                    required
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="price" value="Price" />
                                    <TextInput
                                        id="price"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.price"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.price" />
                                </div>

                                <div>
                                    <InputLabel for="discount_price" value="Discount Price (Optional)" />
                                    <TextInput
                                        id="discount_price"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.discount_price"
                                        placeholder="Leave empty if no discount"
                                    />
                                    <InputError class="mt-2" :message="form.errors.discount_price" />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">If set, this will be displayed as the sale price</p>
                                </div>

                                <div>
                                    <InputLabel for="stock" value="Stock Quantity" />
                                    <TextInput
                                        id="stock"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.stock"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.stock" />
                                </div>

                                <div>
                                    <InputLabel for="category_id" value="Category" />
                                    <select
                                        id="category_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        v-model="form.category_id"
                                        required
                                    >
                                        <option value="">Select Category</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.category_id" />
                                </div>

                                <div>
                                    <InputLabel for="sku" value="SKU (Optional)" />
                                    <TextInput
                                        id="sku"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.sku"
                                    />
                                    <InputError class="mt-2" :message="form.errors.sku" />
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

                            <div>
                                <InputLabel for="images" value="Product Images (Multiple)" />
                                <input
                                    id="images"
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    @change="handleImageChange"
                                    class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100
                                        dark:file:bg-blue-900 dark:file:text-blue-200
                                        dark:hover:file:bg-blue-800"
                                />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select multiple images (PNG, JPG, GIF up to 10MB each). First image will be the primary image.</p>
                                <InputError class="mt-2" :message="form.errors.images" />
                                
                                <!-- Image Previews -->
                                <div v-if="imagePreviews.length > 0" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div v-for="(preview, index) in imagePreviews" :key="index" class="relative group">
                                        <img :src="preview" class="w-full h-32 object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600" />
                                        <button
                                            type="button"
                                            @click="removeImage(index)"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                        <span v-if="index === 0" class="absolute bottom-1 left-1 bg-blue-500 text-white text-xs px-2 py-1 rounded">Primary</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Create Product</PrimaryButton>

                                <a :href="route('vendor.products')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
