<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const form = useForm({
    title: '',
    content: '',
    excerpt: '',
    image: null,
    is_published: true,
});

const submit = () => {
    form.post(route('admin.blog.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Post" />

    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('create_post') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('title') }}</label>
                                <input v-model="form.title" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                                <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">{{ form.errors.title }}</div>
                            </div>

                            <!-- Image -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('cover_image') }}</label>
                                <input @input="form.image = $event.target.files[0]" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-white" />
                                <div v-if="form.errors.image" class="text-red-600 text-sm mt-1">{{ form.errors.image }}</div>
                            </div>

                            <!-- Excerpt -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('excerpt') }}</label>
                                <textarea v-model="form.excerpt" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                <div v-if="form.errors.excerpt" class="text-red-600 text-sm mt-1">{{ form.errors.excerpt }}</div>
                            </div>

                            <!-- Content -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('content') }}</label>
                                <textarea v-model="form.content" rows="10" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                <div v-if="form.errors.content" class="text-red-600 text-sm mt-1">{{ form.errors.content }}</div>
                            </div>

                            <!-- Status -->
                            <div class="flex items-center">
                                <input v-model="form.is_published" type="checkbox" id="is_published" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                <label for="is_published" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">{{ __('publish_immediately') }}</label>
                            </div>

                            <div class="flex justify-end gap-3">
                                <Link :href="route('admin.blog.index')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-md hover:bg-gray-300 transition">{{ __('cancel') }}</Link>
                                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50">
                                    {{ form.processing ? __('processing') : __('create_post') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
