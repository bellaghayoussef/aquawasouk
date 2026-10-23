<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BannerCarousel from '@/Components/BannerCarousel.vue';

defineProps({
    vendors: Object,
    banners: {
        type: Array,
        default: () => [],
    },
});

const formatPrice = (price) => {
    return new Intl.NumberFormat(usePage().props.locale === 'ar' ? 'ar-TN' : 'fr-TN', {
        style: 'currency',
        currency: 'TND',
    }).format(price);
};
</script>

<template>
    <Head title="Nos Boutiques Partenaires - AquawaSouk" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- Breadcrumbs -->
            <nav class="flex items-center text-xs font-semibold text-slate-400 mb-6 gap-2">
                <Link :href="route('home')" class="hover:text-brand-600 transition">{{ __('home') }}</Link>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200">{{ __('vendors') }}</span>
            </nav>

            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-sky-400">Écosystème Certifié</span>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                        {{ __('our_trusted_vendors') || 'Nos Boutiques Partenaires' }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-2xl leading-relaxed">
                        {{ __('discover_quality_products') || 'Achetez en direct auprès de créateurs, importateurs et distributeurs agréés partout en Tunisie.' }}
                    </p>
                </div>

                <Link
                    :href="route('become-vendor')"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition-all duration-200 shrink-0 transform hover:-translate-y-0.5 cursor-pointer"
                >
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>{{ __('become_a_vendor') }}</span>
                </Link>
            </div>

            <!-- Vendors Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mb-16">
                <div
                    v-for="vendor in vendors.data"
                    :key="vendor.id"
                    class="group rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between"
                >
                    <div>
                        <!-- Cover Banner -->
                        <div class="h-32 bg-gradient-to-r from-slate-900 via-brand-950 to-slate-900 relative overflow-hidden">
                            <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors"></div>
                            <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-[11px] font-bold bg-white/95 text-slate-800 shadow-sm backdrop-blur flex items-center gap-1.5 border border-slate-200/60">
                                <svg class="w-3.5 h-3.5 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4zM2 5h11v9H2V5zm11 3h4.172a2 2 0 011.414.586l2.828 2.828A2 2 0 0122 12.828V14h-3M2 14h2m7 0h3"/>
                                </svg>
                                <span>{{ Number(vendor.shipping_price || 0) > 0 ? formatPrice(vendor.shipping_price) : 'Livraison offerte' }}</span>
                            </span>
                        </div>

                        <!-- Vendor Profile Details -->
                        <div class="p-6 -mt-12 relative">
                            <!-- Logo Avatar -->
                            <div class="w-20 h-20 rounded-2xl bg-white dark:bg-slate-700 border-4 border-white dark:border-slate-800 shadow-md flex items-center justify-center font-black text-2xl text-brand-600 mb-4 overflow-hidden">
                                <img v-if="vendor.logo" :src="'/uploads/' + vendor.logo" :alt="vendor.shop_name" class="w-full h-full object-cover" />
                                <span v-else>{{ vendor.shop_name.charAt(0) }}</span>
                            </div>

                            <div class="flex items-center gap-1.5 mb-1">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-brand-600 transition">
                                    {{ vendor.shop_name }}
                                </h3>
                                <span class="inline-flex items-center text-sky-500" title="Boutique vérifiée">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            </div>

                            <div class="flex items-center text-xs text-slate-400 font-medium mb-3 gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ vendor.city }}, {{ vendor.country }}</span>
                                <span v-if="vendor.phone" class="text-slate-300 dark:text-slate-600">•</span>
                                <span v-if="vendor.phone">{{ vendor.phone }}</span>
                            </div>

                            <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-3 leading-relaxed mb-4">
                                {{ vendor.description || 'Boutique vérifiée sur AquawaSouk proposant une sélection d\'articles certifiés.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Card Footer Actions -->
                    <div class="p-6 pt-0 border-t border-slate-100 dark:border-slate-700/60 mt-auto flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Boutique Ouverte</span>
                        </span>

                        <Link
                            :href="route('vendors.show', vendor.slug)"
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 group-hover:translate-x-0.5 transition cursor-pointer"
                        >
                            <span>Visiter la boutique</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="vendors && vendors.links && vendors.links.length > 3" class="flex justify-center mb-10">
                <nav class="flex items-center gap-1.5 bg-white dark:bg-slate-800 p-2 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, index) in vendors.links"
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

        <BannerCarousel v-if="banners && banners.length > 0" :banners="banners" />
    </AppLayout>
</template>
