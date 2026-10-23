<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    posts: Object,
});

const deletePost = (id) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('admin.blog.destroy', id), {
             preserveScroll: true,
        });
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Blog Management" />

    <DashboardLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('blog_management') }}
                </h2>
                <Link :href="route('admin.blog.create')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                    + {{ __('add_post') }}
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
                                    <th class="p-3">{{ __('title') }}</th>
                                    <th class="p-3">{{ __('author') }}</th>
                                    <th class="p-3">{{ __('status') }}</th>
                                    <th class="p-3">{{ __('date') }}</th>
                                    <th class="p-3">{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="posts.data.length === 0">
                                    <td colspan="5" class="p-6 text-center text-gray-500">{{ __('no_posts_found') }}</td>
                                </tr>
                                <tr v-for="post in posts.data" :key="post.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="p-3 font-medium text-gray-900 dark:text-white">{{ post.title }}</td>
                                    <td class="p-3 text-gray-600 dark:text-gray-400">{{ post.user ? post.user.name : 'Unknown' }}</td>
                                    <td class="p-3">
                                        <span :class="['px-2 py-1 rounded-full text-xs font-semibold', post.is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">
                                            {{ post.is_published ? __('published') : __('draft') }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-500">{{ formatDate(post.published_at || post.created_at) }}</td>
                                    <td class="p-3 space-x-2">
                                        <Link :href="route('admin.blog.edit', post.id)" class="text-blue-600 hover:text-blue-800">{{ __('edit') }}</Link>
                                        <button @click="deletePost(post.id)" class="text-red-600 hover:text-red-800">{{ __('delete') }}</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Pagination -->
                         <div v-if="posts.links.length > 3" class="mt-6 flex justify-center">
                            <div class="flex flex-wrap gap-1">
                                <Link v-for="(link, k) in posts.links" :key="k" 
                                    :href="link.url" 
                                    v-html="link.label"
                                    class="px-3 py-1 border rounded text-sm"
                                    :class="{ 'bg-blue-600 text-white': link.active, 'bg-white text-gray-700': !link.active, 'opacity-50': !link.url }" 
                                />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
