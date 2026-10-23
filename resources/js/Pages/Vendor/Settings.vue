<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const props = defineProps({
    vendor: Object,
});

const form = useForm({
    shop_name: props.vendor.shop_name,
    description: props.vendor.description,
    phone: props.vendor.phone,
    address: props.vendor.address,
    city: props.vendor.city,
    country: props.vendor.country,
    postal_code: props.vendor.postal_code,
    shipping_price: props.vendor.shipping_price || 0,
    logo: null,
});

const photoPreview = ref(props.vendor.logo ? `/uploads/${props.vendor.logo}` : null);

const handlePhotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.logo = file;
        // Preview
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('vendor.settings.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by Inertia
        },
    });
};
</script>

<template>
    <Head title="Shop Settings" />

    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Shop Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Shop Logo Upload -->
                            <div>
                                <InputLabel for="logo" value="Shop Logo" />
                                <div class="mt-2 flex items-center gap-6">
                                    <!-- Current Photo -->
                                    <div class="flex-shrink-0">
                                        <div v-if="photoPreview" class="w-32 h-32 rounded-lg overflow-hidden border-2 border-gray-300 dark:border-gray-600">
                                            <img :src="photoPreview" alt="Shop Logo" class="w-full h-full object-cover">
                                        </div>
                                        <div v-else class="w-32 h-32 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <!-- Upload Button -->
                                    <div class="flex-1">
                                        <input
                                            id="logo"
                                            type="file"
                                            accept="image/*"
                                            @change="handlePhotoChange"
                                            class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                                file:mr-4 file:py-2 file:px-4
                                                file:rounded-md file:border-0
                                                file:text-sm file:font-semibold
                                                file:bg-blue-50 file:text-blue-700
                                                hover:file:bg-blue-100
                                                dark:file:bg-blue-900 dark:file:text-blue-200
                                                dark:hover:file:bg-blue-800"
                                        />
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            Recommended: Square image, at least 400x400px. PNG, JPG up to 5MB.
                                        </p>
                                        <InputError class="mt-2" :message="form.errors.logo" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <InputLabel for="shop_name" value="Shop Name" />
                                <TextInput
                                    id="shop_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.shop_name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.shop_name" />
                            </div>

                            <div>
                                <InputLabel for="description" value="Description" />
                                <textarea
                                    id="description"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.description"
                                    rows="4"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="phone" value="Phone Number" />
                                    <TextInput
                                        id="phone"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.phone"
                                    />
                                    <InputError class="mt-2" :message="form.errors.phone" />
                                </div>

                                <div>
                                    <InputLabel for="address" value="Address" />
                                    <TextInput
                                        id="address"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.address"
                                    />
                                    <InputError class="mt-2" :message="form.errors.address" />
                                </div>

                                <div>
                                    <InputLabel for="city" value="City" />
                                    <TextInput
                                        id="city"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.city"
                                    />
                                    <InputError class="mt-2" :message="form.errors.city" />
                                </div>

                                <div>
                                    <InputLabel for="country" value="Country" />
                                    <TextInput
                                        id="country"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.country"
                                    />
                                    <InputError class="mt-2" :message="form.errors.country" />
                                </div>

                                <div>
                                    <InputLabel for="postal_code" value="Postal Code" />
                                    <TextInput
                                        id="postal_code"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.postal_code"
                                    />
                                    <InputError class="mt-2" :message="form.errors.postal_code" />
                                </div>

                                <div>
                                    <InputLabel for="shipping_price" value="Shipping Price (TND)" />
                                    <TextInput
                                        id="shipping_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full"
                                        v-model="form.shipping_price"
                                    />
                                    <InputError class="mt-2" :message="form.errors.shipping_price" />
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Set the shipping price for orders from your shop</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
