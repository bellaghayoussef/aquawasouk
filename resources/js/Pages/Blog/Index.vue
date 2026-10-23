<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    posts: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <Head title="Blog - Aquawasouk" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ __('our_blog') }}</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">{{ __('blog_intro') }}</p>
            </div>

            <div v-if="posts.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="post in posts.data" :key="post.id" class="flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group">
                    <Link :href="route('blog.show', post.slug)" class="block overflow-hidden h-48 relative">
                        <img v-if="post.image_url" :src="post.image_url" :alt="post.title" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
                        <div v-else class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                            <span class="text-gray-400 text-4xl">📝</span>
                        </div>
                    </Link>
                    
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="text-sm text-blue-600 dark:text-blue-400 mb-2 font-medium">
                            {{ formatDate(post.published_at) }} &bull; {{ post.user ? post.user.name : 'Admin' }}
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-blue-600 transition line-clamp-2">
                            <Link :href="route('blog.show', post.slug)">
                                {{ post.title }}
                            </Link>
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-4 line-clamp-3 flex-1">
                            {{ post.excerpt || post.content.substring(0, 100) + '...' }}
                        </p>
                        <Link :href="route('blog.show', post.slug)" class="inline-flex items-center text-blue-600 font-semibold hover:underline mt-auto">
                            {{ __('read_more') }} &rarr;
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-16 text-gray-500">
                {{ __('no_posts_found') }}
            </div>

             <!-- Pagination -->
             <div v-if="posts.links && posts.links.length > 3" class="flex justify-center mt-12">
                <nav class="flex items-center space-x-2">
                    <Link
                        v-for="(link, index) in posts.links"
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
    </AppLayout>
</template>
