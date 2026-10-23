<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SEO from '@/Components/SEO.vue';

const props = defineProps({
    post: Object,
    seo: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <SEO v-if="seo" v-bind="seo" />
    <Head v-else :title="post.title + ' - Aquawasouk'" />

    <AppLayout>
        <article>
            <!-- Header -->
            <div class="bg-gray-50 dark:bg-gray-900 py-16 border-b border-gray-200 dark:border-gray-800">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div class="text-blue-600 dark:text-blue-400 font-medium mb-4 tracking-wide uppercase text-sm">
                        {{ formatDate(post.published_at || post.created_at) }} • {{ __('by') }} {{ post.user ? post.user.name : 'Aquawasouk' }}
                    </div>
                    <h1 class="text-3xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                        {{ post.title }}
                    </h1>
                </div>
            </div>

            <!-- Content Container -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
                <!-- Featured Image -->
                <div v-if="post.image_url" class="relative z-10 mb-12">
                    <img :src="post.image_url" :alt="post.title" class="w-full h-auto rounded-2xl shadow-2xl">
                </div>

                <!-- Post Content -->
                <div class="prose prose-lg dark:prose-invert max-w-none pb-16">
                    <!-- Displaying content with line breaks preserved -->
                    <div class="whitespace-pre-wrap text-gray-800 dark:text-gray-200 leading-relaxed">{{ post.content }}</div>
                </div>

                <!-- Footer / Navigation -->
                <div class="pb-16 border-t border-gray-200 dark:border-gray-700 pt-8 flex justify-between items-center">
                    <Link :href="route('blog.index')" class="flex items-center text-blue-600 hover:text-blue-800 font-semibold transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        {{ __('back_to_blog') }}
                    </Link>
                </div>
            </div>
        </article>
    </AppLayout>
</template>

<style>
/* Add rudimentary typography styles if Tailwind Typography plugin is not fully configured */
.prose {
    line-height: 1.75;
}
.prose p {
    margin-bottom: 1.5em;
}
</style>
