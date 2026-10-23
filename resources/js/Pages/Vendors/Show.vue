<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SEO from '@/Components/SEO.vue';

const props = defineProps({
    vendor: Object,
    products: Object,
    structuredData: String,
    seo: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat(usePage().props.locale === 'ar' ? 'ar-TN' : 'fr-TN', {
        style: 'currency',
        currency: 'TND',
    }).format(price);
};

const getDiscountPercentage = (price, discountPrice) => {
    if (!discountPrice || discountPrice >= price) return 0;
    return Math.round(((price - discountPrice) / price) * 100);
};

onMounted(() => {
    if (props.structuredData) {
        document.querySelectorAll('script[data-schema-type="vendor"]').forEach(el => el.remove());
        const vendorScript = document.createElement('script');
        vendorScript.type = 'application/ld+json';
        vendorScript.setAttribute('data-schema-type', 'vendor');
        vendorScript.textContent = props.structuredData;
        document.head.appendChild(vendorScript);
    }
});
</script>

<template>
    <SEO
        v-if="seo"
        :title="seo.title"
        :description="seo.description"
        :keywords="seo.keywords"
        :image="seo.image"
        :url="seo.canonical"
    />
    <Head v-else :title="vendor.shop_name + ' - AquawaSouk'" />

    <AppLayout>
        <!-- Vendor Hero Banner Header -->
        <div class="relative bg-gradient-to-r from-slate-950 via-slate-900 to-brand-950 text-white border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
                <!-- Breadcrumbs -->
                <nav class="flex items-center text-xs font-semibold text-slate-400 mb-6 gap-2">
                    <Link :href="route('home')" class="hover:text-white transition">{{ __('home') }}</Link>
                    <span>/</span>
                    <Link :href="route('vendors.index')" class="hover:text-white transition">{{ __('vendors') }}</Link>
                    <span>/</span>
                    <span class="text-white font-bold">{{ vendor.shop_name }}</span>
                </nav>

                <div class="flex flex-col md:flex-row items-start md:items-center gap-6 lg:gap-8">
                    <!-- Vendor Big Avatar -->
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-white dark:bg-slate-800 text-slate-900 flex items-center justify-center font-black text-3xl shadow-2xl overflow-hidden shrink-0 border-4 border-slate-800">
                        <img v-if="vendor.logo" :src="'/uploads/' + vendor.logo" :alt="vendor.shop_name" class="w-full h-full object-cover" />
                        <span v-else class="text-brand-600">{{ vendor.shop_name.charAt(0) }}</span>
                    </div>

                    <div class="space-y-2 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white font-display">
                                {{ vendor.shop_name }}
                            </h1>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 shadow-xs">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Boutique Agréée</span>
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-5 text-xs sm:text-sm text-slate-300">
                            <span class="flex items-center gap-1.5 text-slate-300">
                                <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ vendor.address }}, {{ vendor.city }}, {{ vendor.country }}</span>
                            </span>
                            <span v-if="vendor.phone" class="flex items-center gap-1.5 text-slate-300">
                                <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <a :href="`tel:${vendor.phone}`" class="hover:text-white transition font-medium">{{ vendor.phone }}</a>
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-300 max-w-3xl leading-relaxed pt-1">
                            {{ vendor.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Key Stats Bar -->
        <div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 py-4 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center divide-x divide-slate-100 dark:divide-slate-800">
                    <div class="px-2">
                        <p class="text-xs font-medium text-slate-400">Articles en vente</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ products.total || products.data.length }}</p>
                    </div>
                    <div class="px-2">
                        <p class="text-xs font-medium text-slate-400">Frais de livraison</p>
                        <p class="text-xl font-black text-brand-600 dark:text-sky-400 mt-0.5">
                            {{ Number(vendor.shipping_price || 0) > 0 ? formatPrice(vendor.shipping_price) : 'Offerte' }}
                        </p>
                    </div>
                    <div class="px-2">
                        <p class="text-xs font-medium text-slate-400">Paiement</p>
                        <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">À la réception</p>
                    </div>
                    <div class="px-2">
                        <p class="text-xs font-medium text-slate-400">Responsable</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white mt-0.5 truncate">{{ vendor.user ? vendor.user.name : 'Vendeur' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor's Products Showcase -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="mb-8">
                <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Tous les articles de {{ vendor.shop_name }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Commandez directement auprès de ce vendeur avec frais de livraison groupés
                </p>
            </div>

            <!-- Products Grid -->
            <div v-if="products && products.data && products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="group rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
                >
                    <Link :href="route('products.show', product.slug)" class="block relative aspect-square bg-slate-100 dark:bg-slate-700/50 overflow-hidden">
                        <img
                            v-if="product.primary_image"
                            :src="'/uploads/' + product.primary_image.image_path"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>

                        <div
                            v-if="product.discount_price"
                            class="absolute top-2.5 left-2.5 px-2.5 py-1 bg-red-600 text-white font-black text-[11px] rounded-full shadow"
                        >
                            -{{ getDiscountPercentage(product.price, product.discount_price) }}%
                        </div>
                    </Link>

                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-semibold text-brand-600 dark:text-sky-400 block mb-1">
                                {{ product.category ? product.category.name : 'Article' }}
                            </span>
                            <Link :href="route('products.show', product.slug)">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-brand-600 transition line-clamp-2 leading-snug">
                                    {{ product.name }}
                                </h3>
                            </Link>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between gap-2">
                            <div>
                                <div v-if="product.discount_price" class="text-xs text-slate-400 line-through">
                                    {{ formatPrice(product.price) }}
                                </div>
                                <div class="text-base font-black text-slate-900 dark:text-white">
                                    {{ formatPrice(product.discount_price || product.price) }}
                                </div>
                            </div>

                            <Link
                                :href="route('products.show', product.slug)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-brand-600 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200 text-white font-bold text-xs shadow-xs hover:shadow-md transition cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Commander</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Products State -->
            <div v-else class="text-center py-16 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700">
                <p class="text-base font-bold text-slate-900 dark:text-white">Cette boutique n'a pas encore publié d'articles.</p>
                <p class="text-xs text-slate-400 mt-1">Revenez très bientôt pour découvrir leurs nouveautés.</p>
            </div>

            <!-- Pagination -->
            <div v-if="products && products.links && products.links.length > 3" class="flex justify-center mb-10">
                <nav class="flex items-center gap-1.5 bg-white dark:bg-slate-800 p-2 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, index) in products.links"
                        :key="index"
                        :href="link.url"
                        :class="[
                            'px-3.5 py-1.5 rounded-xl text-xs font-bold transition',
                            link.active
                                ? 'bg-brand-600 text-white shadow'
                                : link.url
                                    ? 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700'
                                    : 'text-slate-300 dark:text-slate-600 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
