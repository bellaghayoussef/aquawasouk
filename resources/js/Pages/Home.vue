<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import BannerCarousel from '@/Components/BannerCarousel.vue';
import SpotlightCard from '@/Components/ui/SpotlightCard.vue';
import InfiniteMarquee from '@/Components/ui/InfiniteMarquee.vue';
import GlowBorderCard from '@/Components/ui/GlowBorderCard.vue';
import AccordionFaq from '@/Components/ui/AccordionFaq.vue';

const props = defineProps({
    featuredProducts: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    vendors: {
        type: Array,
        default: () => [],
    },
    structuredData: Object,
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

// Calculate discount percentage
const getDiscountPercentage = (price, discountPrice) => {
    if (!discountPrice || discountPrice >= price) return 0;
    return Math.round(((price - discountPrice) / price) * 100);
};

onMounted(() => {
    // Inject structured data into head
    if (props.structuredData) {
        document.querySelectorAll('script[data-schema-type]').forEach(el => el.remove());

        if (props.structuredData.organization) {
            const orgScript = document.createElement('script');
            orgScript.type = 'application/ld+json';
            orgScript.setAttribute('data-schema-type', 'organization');
            orgScript.textContent = props.structuredData.organization;
            document.head.appendChild(orgScript);
        }

        if (props.structuredData.website) {
            const websiteScript = document.createElement('script');
            websiteScript.type = 'application/ld+json';
            websiteScript.setAttribute('data-schema-type', 'website');
            websiteScript.textContent = props.structuredData.website;
            document.head.appendChild(websiteScript);
        }
    }
});
</script>

<template>
    <Head title="Accueil - AquawaSouk Marketplace Multi-Vendeurs" />

    <AppLayout>
        <!-- Hero Section: Editorial & High-Converting (Vengeance UI Aurora & Animaster Lib Shimmer) -->
        <section class="relative overflow-hidden bg-gradient-to-b from-white via-slate-50/50 to-white dark:from-slate-900 dark:via-slate-950 dark:to-slate-900 border-b border-slate-200/60 dark:border-slate-800">
            <!-- Vengeance UI Aurora Wave Mesh Background -->
            <div class="absolute -top-32 -left-32 w-[550px] h-[550px] bg-gradient-to-tr from-brand-600/15 via-cyan-400/10 to-teal-400/5 rounded-full blur-3xl animate-aurora-mesh pointer-events-none"></div>
            <div class="absolute top-1/3 -right-20 w-[450px] h-[450px] bg-gradient-to-br from-amber-500/10 via-brand-500/10 to-transparent rounded-full blur-3xl animate-aurora-mesh pointer-events-none" style="animation-delay: -5s;"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    <!-- Left: Content & Call to Actions -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-50 dark:bg-brand-950/60 border border-brand-200/80 dark:border-brand-800/60 text-brand-700 dark:text-brand-300 text-xs font-bold tracking-wide shadow-xs">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>La marketplace multi-vendeurs n°1 en Tunisie</span>
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.12]">
                            Trouvez tout ce que vous aimez, 
                            <span class="animate-shimmer-text bg-gradient-to-r from-brand-600 via-sky-400 to-brand-700 bg-clip-text text-transparent">
                                au meilleur prix.
                            </span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl font-normal">
                            Explorez des milliers de produits authentiques proposés par des commerçants et artisans certifiés. Commandez en direct avec paiement en espèces lors de la livraison.
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-4 pt-2">
                            <Link
                                :href="route('products.index')"
                                class="inline-flex items-center gap-2.5 px-8 py-4 rounded-xl bg-slate-900 hover:bg-brand-600 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200 text-white font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                            >
                                <span>Parcourir le catalogue</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </Link>

                            <Link
                                :href="route('vendors.index')"
                                class="inline-flex items-center gap-2 px-6 py-4 rounded-xl border border-slate-300 dark:border-slate-700 hover:border-slate-400 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-sm shadow-sm hover:shadow transition-all duration-200 cursor-pointer"
                            >
                                <span>Découvrir nos boutiques</span>
                            </Link>
                        </div>

                        <!-- Micro Metrics -->
                        <div class="grid grid-cols-3 gap-4 pt-6 border-t border-slate-200/80 dark:border-slate-800">
                            <div>
                                <p class="text-2xl font-black text-slate-900 dark:text-white">24-48h</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Livraison rapide</p>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">100%</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Paiement à réception</p>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-brand-600 dark:text-sky-400">Vérifiés</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Commerçants agréés</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Skiper UI Spotlight Card -->
                    <div class="lg:col-span-5 relative">
                        <SpotlightCard class="bg-white dark:bg-slate-800 p-6 shadow-2xl border border-slate-200/80 dark:border-slate-700/80">
                            <!-- Card header -->
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 flex items-center gap-1.5 border border-amber-200/80 dark:border-amber-800/60">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.316.492-.59 1.054-.863 1.62-.53 1.1-.986 2.122-1.636 2.87-.63.725-1.47 1.34-2.45 1.554a1 1 0 00-.73 1.18c.08.384.34.7.7.838.412.158.85.244 1.295.253.25.006.49-.03.73-.086.18-.042.36-.094.53-.158.2-.075.4-.16.59-.257.34-.176.66-.379.95-.606.07-.054.14-.11.21-.167.14-.114.28-.23.42-.353.2-.176.41-.36.62-.553.1-.09.2-.182.3-.277.16-.153.33-.314.5-.484.23-.23.47-.474.72-.727.18-.182.37-.37.56-.566.24-.25.5-.508.76-.774a1 1 0 00.18-1.157c-.12-.236-.31-.43-.54-.56z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Coup de cœur du jour</span>
                                </span>
                                <span class="text-xs font-semibold text-slate-400">Offre limitée</span>
                            </div>

                            <!-- Featured product showcase -->
                            <div v-if="featuredProducts.length > 0" class="pt-5">
                                <Link :href="route('products.show', featuredProducts[0].slug)" class="group block">
                                    <div class="aspect-square w-full rounded-2xl bg-slate-100 dark:bg-slate-700/50 overflow-hidden relative mb-4 border border-slate-100 dark:border-slate-700">
                                        <img
                                            v-if="featuredProducts[0].primary_image"
                                            :src="'/uploads/' + featuredProducts[0].primary_image.image_path"
                                            :alt="featuredProducts[0].name"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>

                                        <!-- Discount Badge -->
                                        <div
                                            v-if="featuredProducts[0].discount_price"
                                            class="absolute top-3 right-3 px-3 py-1 bg-red-600 text-white font-black text-xs rounded-full shadow-md"
                                        >
                                            -{{ getDiscountPercentage(featuredProducts[0].price, featuredProducts[0].discount_price) }}%
                                        </div>
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-brand-600 dark:text-sky-400 mb-1">
                                            {{ featuredProducts[0].category ? featuredProducts[0].category.name : 'Catalogue' }}
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-brand-600 transition line-clamp-2">
                                            {{ featuredProducts[0].name }}
                                        </h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            Vendu par <strong class="text-slate-700 dark:text-slate-200">{{ featuredProducts[0].vendor ? featuredProducts[0].vendor.shop_name : 'Boutique Partenaire' }}</strong>
                                        </p>

                                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                                            <div>
                                                <span v-if="featuredProducts[0].discount_price" class="text-xs text-slate-400 line-through mr-2">
                                                    {{ formatPrice(featuredProducts[0].price) }}
                                                </span>
                                                <span class="text-xl font-black text-slate-900 dark:text-white">
                                                    {{ formatPrice(featuredProducts[0].discount_price || featuredProducts[0].price) }}
                                                </span>
                                            </div>
                                            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-md hover:bg-emerald-700 transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                                <span>Commander</span>
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </SpotlightCard>
                    </div>
                </div>
            </div>
        </section>

        <!-- Skiper UI Infinite Trust Marquee Ticker -->
        <div class="border-b border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/60 py-3">
            <InfiniteMarquee speed="26s">
                <span class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 px-4 py-1.5 rounded-full bg-slate-100/80 dark:bg-slate-800/80">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4zM2 5h11v9H2V5zm11 3h4.172a2 2 0 011.414.586l2.828 2.828A2 2 0 0122 12.828V14h-3M2 14h2m7 0h3"/></svg>
                    Livraison Express 24-48h sur toute la Tunisie
                </span>
                <span class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 px-4 py-1.5 rounded-full bg-slate-100/80 dark:bg-slate-800/80">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Paiement 100% en Espèces à la Livraison
                </span>
                <span class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 px-4 py-1.5 rounded-full bg-slate-100/80 dark:bg-slate-800/80">
                    <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Boutiques Agréées & Produits Contrôlés
                </span>
                <span class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 px-4 py-1.5 rounded-full bg-slate-100/80 dark:bg-slate-800/80">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Assistance Client 6j/7 (+216 56 818 880)
                </span>
            </InfiniteMarquee>
        </div>

        <!-- Categories Showcase Grid -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-sky-400">Rayons & Univers</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                        Explorez par Catégorie
                    </h2>
                </div>
                <Link
                    :href="route('products.index')"
                    class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-sky-400 flex items-center gap-1.5 group"
                >
                    <span>Voir tout le catalogue</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </Link>
            </div>

            <!-- Categories Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="route('products.index', { category: category.slug })"
                    class="group relative rounded-2xl p-5 bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between overflow-hidden cursor-pointer"
                >
                    <!-- Icon container -->
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-brand-50 to-sky-50 dark:from-slate-700/80 dark:to-slate-800/80 border border-brand-100 dark:border-slate-700 flex items-center justify-center text-brand-600 dark:text-sky-400 mb-4 group-hover:bg-brand-600 group-hover:text-white dark:group-hover:bg-brand-500 dark:group-hover:text-white group-hover:scale-110 transition-all duration-300 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-sky-400 transition">
                            {{ category.name }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
                            {{ category.description || 'Découvrez nos articles sélectionnés' }}
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs font-semibold text-slate-400 group-hover:text-brand-600 transition">
                        <span>Découvrir</span>
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Dynamic Promotional Banners (if any exist) -->
        <BannerCarousel v-if="banners && banners.length > 0" :banners="banners" />

        <!-- Featured Products ("Sélection & Meilleurs Articles") -->
        <section class="bg-white dark:bg-slate-900/60 py-16 border-y border-slate-200/60 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Sélection Exclusive</span>
                        <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                            Articles Vedettes & Tendances
                        </h2>
                    </div>
                    <Link
                        :href="route('products.index', { sort: 'popular' })"
                        class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-sky-400 flex items-center gap-1.5 group cursor-pointer"
                    >
                        <span>Tous les produits populaires</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="product in featuredProducts"
                        :key="product.id"
                        class="group rounded-2xl bg-[#FAFBFD] dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
                    >
                        <!-- Image Container with Link -->
                        <Link :href="route('products.show', product.slug)" class="block relative aspect-square bg-slate-100 dark:bg-slate-700/50 overflow-hidden cursor-pointer">
                            <img
                                v-if="product.primary_image"
                                :src="'/uploads/' + product.primary_image.image_path"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>

                            <!-- Discount Tag -->
                            <div
                                v-if="product.discount_price"
                                class="absolute top-2.5 left-2.5 px-2.5 py-1 bg-red-600 text-white font-black text-[11px] rounded-full shadow"
                            >
                                -{{ getDiscountPercentage(product.price, product.discount_price) }}%
                            </div>

                            <!-- Stock Indicator -->
                            <div v-if="product.stock > 0" class="absolute bottom-2.5 left-2.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/95 dark:bg-slate-900/95 text-emerald-700 dark:text-emerald-400 backdrop-blur border border-emerald-200/80 dark:border-emerald-800/60 shadow-sm inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>En stock</span>
                            </div>
                        </Link>

                        <!-- Product Info -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                                    <span class="font-semibold text-brand-600 dark:text-sky-400">
                                        {{ product.category ? product.category.name : 'Article' }}
                                    </span>
                                    <span v-if="product.vendor" class="truncate max-w-[120px]">
                                        {{ product.vendor.shop_name }}
                                    </span>
                                </div>

                                <Link :href="route('products.show', product.slug)" class="cursor-pointer">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-brand-600 transition line-clamp-2 leading-snug">
                                        {{ product.name }}
                                    </h3>
                                </Link>
                            </div>

                            <!-- Price & CTA -->
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
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-brand-600 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200 text-white font-bold text-xs shadow-sm hover:shadow transition-all duration-200 active:scale-95 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <span>Commander</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <Link
                        :href="route('products.index')"
                        class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full border-2 border-slate-900 dark:border-white text-slate-900 dark:text-white font-bold text-xs uppercase tracking-wider hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition-all duration-300 cursor-pointer"
                    >
                        <span>Voir tous les articles ({{ featuredProducts.length }}+)</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Top Verified Vendors Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Écosystème Marketplace</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                        Boutiques & Vendeurs Vedettes
                    </h2>
                </div>
                <Link
                    :href="route('vendors.index')"
                    class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-sky-400 flex items-center gap-1.5 group cursor-pointer"
                >
                    <span>Découvrir tous les vendeurs</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </Link>
            </div>

            <!-- Vendor Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="vendor in vendors"
                    :key="vendor.id"
                    class="group rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300"
                >
                    <Link :href="route('vendors.show', vendor.slug)" class="block cursor-pointer">
                        <!-- Cover Banner -->
                        <div class="h-28 bg-gradient-to-r from-slate-800 via-slate-900 to-brand-900 relative">
                            <div class="absolute inset-0 bg-black/10"></div>
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/95 text-slate-900 backdrop-blur shadow-sm inline-flex items-center gap-1 border border-slate-200/60">
                                <svg class="w-3 h-3 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4zM2 5h11v9H2V5zm11 3h4.172a2 2 0 011.414.586l2.828 2.828A2 2 0 0122 12.828V14h-3M2 14h2m7 0h3"/>
                                </svg>
                                <span>{{ Number(vendor.shipping_price || 0) > 0 ? formatPrice(vendor.shipping_price) : 'Livraison offerte' }}</span>
                            </span>
                        </div>

                        <!-- Info & Logo -->
                        <div class="p-6 -mt-10 relative">
                            <div class="w-16 h-16 rounded-2xl bg-white dark:bg-slate-700 border-2 border-white dark:border-slate-800 shadow-md overflow-hidden flex items-center justify-center text-xl font-black text-brand-600 mb-3">
                                <img v-if="vendor.logo" :src="'/uploads/' + vendor.logo" :alt="vendor.shop_name" class="w-full h-full object-cover" />
                                <span v-else>{{ vendor.shop_name.charAt(0) }}</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-brand-600 transition">
                                    {{ vendor.shop_name }}
                                </h3>
                                <span class="inline-flex items-center text-sky-500" title="Vendeur certifié">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            </div>

                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ vendor.city }}, {{ vendor.country }}</span>
                            </p>

                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-3 line-clamp-2 leading-relaxed">
                                {{ vendor.description || 'Boutique vérifiée proposant des articles de haute qualité.' }}
                            </p>

                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs font-bold text-brand-600 group-hover:text-brand-700 transition">
                                <span>Visiter la boutique</span>
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Interactive FAQ Section (Skiper UI & shadcn-ui) -->
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="text-center mb-10">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-brand-500/10 text-brand-600 dark:text-sky-400 border border-brand-500/20 mb-3">
                    Questions Fréquentes
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    Tout ce que vous devez savoir sur vos commandes
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-md mx-auto">
                    Paiement à la livraison, délais express 24/48h et garanties : retrouvez les réponses essentielles.
                </p>
            </div>

            <AccordionFaq />
        </section>

        <!-- Become a Vendor CTA Strip -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 text-white p-8 sm:p-12 shadow-2xl relative overflow-hidden border border-slate-800">
                <!-- Ambient glow effects -->
                <div class="absolute -right-20 -top-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-3xl space-y-5">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>Opportunité Vendeurs & Artisans</span>
                    </span>

                    <h2 class="text-3xl sm:text-4xl font-black tracking-tight leading-tight">
                        Vous vendez des produits ? Développez votre activité sur AquawaSouk.
                    </h2>

                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed font-normal">
                        Rejoignez des dizaines de boutiques à succès. Bénéficiez d'un tableau de bord de gestion complet, d'une visibilité nationale et encaissez vos paiements facilement en toute sérénité.
                    </p>

                    <!-- Trust indicators -->
                    <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-300 pt-1">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Inscription sans frais cachés</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Tableau de bord commandes & stock</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Paiement à la livraison garanti</span>
                        </span>
                    </div>

                    <div class="pt-3">
                        <Link
                            :href="route('become-vendor')"
                            class="inline-flex items-center gap-2.5 px-8 py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-sm shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                        >
                            <span>Ouvrir ma boutique gratuitement</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
