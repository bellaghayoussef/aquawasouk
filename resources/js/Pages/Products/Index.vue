<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BannerCarousel from '@/Components/BannerCarousel.vue';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
    banners: Array,
});

const searchQuery = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || '');
const sortBy = ref(props.filters?.sort || 'latest');
const minPrice = ref(props.filters?.min_price || '');
const maxPrice = ref(props.filters?.max_price || '');

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

const applyFilters = () => {
    router.get(route('products.index'), {
        search: searchQuery.value || undefined,
        category: selectedCategory.value || undefined,
        sort: sortBy.value || undefined,
        min_price: minPrice.value || undefined,
        max_price: maxPrice.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    sortBy.value = 'latest';
    minPrice.value = '';
    maxPrice.value = '';
    applyFilters();
};

const setCategory = (slug) => {
    selectedCategory.value = selectedCategory.value === slug ? '' : slug;
};

const setPriceRange = (min, max) => {
    minPrice.value = min !== null ? String(min) : '';
    maxPrice.value = max !== null ? String(max) : '';
    applyFilters();
};

watch([searchQuery, selectedCategory, sortBy], () => {
    applyFilters();
});

const activeCategoryName = computed(() => {
    if (!selectedCategory.value) return null;
    const cat = props.categories?.find(c => c.slug === selectedCategory.value);
    return cat ? cat.name : null;
});
</script>

<template>
    <Head title="Catalogue de Produits - AquawaSouk" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- Breadcrumbs -->
            <nav class="flex items-center text-xs font-semibold text-slate-400 mb-6 gap-2">
                <Link :href="route('home')" class="hover:text-brand-600 transition">{{ __('home') }}</Link>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200">Catalogue de produits</span>
                <span v-if="activeCategoryName">/</span>
                <span v-if="activeCategoryName" class="text-brand-600 dark:text-sky-400">{{ activeCategoryName }}</span>
            </nav>

            <!-- Page Title & Results Counter -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ activeCategoryName || __('all_products') || 'Tous les produits' }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ products?.total || products?.data?.length || 0 }} article(s) disponible(s) en commande directe
                    </p>
                </div>

                <!-- Active filters chips -->
                <div v-if="searchQuery || selectedCategory || sortBy !== 'latest' || minPrice || maxPrice" class="flex flex-wrap items-center gap-2">
                    <span v-if="selectedCategory" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                        <span>Catégorie: {{ activeCategoryName }}</span>
                        <button @click="selectedCategory = ''" class="hover:text-red-500 ml-1">×</button>
                    </span>

                    <span v-if="searchQuery" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        <span>Recherche: "{{ searchQuery }}"</span>
                        <button @click="searchQuery = ''" class="hover:text-red-500 ml-1">×</button>
                    </span>

                    <span v-if="minPrice || maxPrice" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <span>Prix: {{ minPrice ? minPrice + ' DT' : '0 DT' }} - {{ maxPrice ? maxPrice + ' DT' : 'Max' }}</span>
                        <button @click="setPriceRange(null, null)" class="hover:text-red-500 ml-1">×</button>
                    </span>

                    <button
                        @click="resetFilters"
                        class="text-xs font-bold text-red-600 hover:text-red-700 underline ml-1"
                    >
                        Effacer tout
                    </button>
                </div>
            </div>

            <!-- Quick Category Filter Pills -->
            <div class="mb-8 overflow-x-auto no-scrollbar pb-2">
                <div class="flex items-center gap-2 shrink-0">
                    <button
                        @click="selectedCategory = ''"
                        class="px-4 py-2 rounded-full text-xs font-bold transition shadow-sm"
                        :class="[!selectedCategory ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-slate-300']"
                    >
                        Tout afficher
                    </button>

                    <button
                        v-for="category in categories"
                        :key="category.id"
                        @click="setCategory(category.slug)"
                        class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 shadow-sm"
                        :class="[selectedCategory === category.slug ? 'bg-brand-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-brand-500']"
                    >
                        {{ category.name }}
                    </button>
                </div>
            </div>

            <!-- Main Filter Bar -->
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 mb-10 shadow-sm border border-slate-200/80 dark:border-slate-700/80">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <!-- Search Input -->
                    <div class="md:col-span-5 relative">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            Recherche de produit
                        </label>
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                :placeholder="__('search_products') || 'Mots-clés, référence, marque...'"
                                class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 transition"
                            />
                            <div class="absolute left-3.5 top-3 text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Category Dropdown -->
                    <div class="md:col-span-4">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            {{ __('category') }}
                        </label>
                        <select
                            v-model="selectedCategory"
                            class="w-full py-2.5 px-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 font-medium transition"
                        >
                            <option value="">{{ __('all_categories') || 'Toutes les catégories' }}</option>
                            <option v-for="category in categories" :key="category.id" :value="category.slug">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="md:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            {{ __('sort_by') }}
                        </label>
                        <select
                            v-model="sortBy"
                            class="w-full py-2.5 px-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 font-medium transition"
                        >
                            <option value="latest">{{ __('latest') || 'Plus récents' }}</option>
                            <option value="price_low">{{ __('price_low_high') || 'Prix: Moins cher au plus cher' }}</option>
                            <option value="price_high">{{ __('price_high_low') || 'Prix: Plus cher au moins cher' }}</option>
                            <option value="popular">{{ __('popular') || 'Plus populaires' }}</option>
                        </select>
                    </div>

                    <!-- Price Filter Row -->
                    <div class="md:col-span-12 pt-4 mt-2 border-t border-slate-100 dark:border-slate-700/70 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <!-- Inputs Min and Max -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Fourchette de prix :</span>
                            </span>
                            <div class="flex items-center gap-1.5">
                                <div class="relative w-24 sm:w-28">
                                    <input
                                        v-model="minPrice"
                                        type="number"
                                        min="0"
                                        step="any"
                                        placeholder="Min DT"
                                        @keyup.enter="applyFilters"
                                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 font-medium"
                                    />
                                </div>
                                <span class="text-slate-400 font-bold text-xs">—</span>
                                <div class="relative w-24 sm:w-28">
                                    <input
                                        v-model="maxPrice"
                                        type="number"
                                        min="0"
                                        step="any"
                                        placeholder="Max DT"
                                        @keyup.enter="applyFilters"
                                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 font-medium"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="applyFilters"
                                    class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-500 text-white font-bold text-xs shadow-sm transition active:scale-95 cursor-pointer"
                                >
                                    Appliquer
                                </button>
                                <button
                                    v-if="minPrice || maxPrice"
                                    type="button"
                                    @click="setPriceRange(null, null)"
                                    class="p-2 rounded-xl text-slate-400 hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition text-xs font-bold cursor-pointer"
                                    title="Réinitialiser le prix"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>

                        <!-- Quick Presets -->
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                            <span class="text-[11px] font-semibold text-slate-400 shrink-0">Suggestions :</span>
                            <button
                                type="button"
                                @click="setPriceRange(null, 20)"
                                :class="['px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition shrink-0 cursor-pointer',
                                    !minPrice && maxPrice == '20'
                                        ? 'bg-brand-600 text-white border-brand-600 shadow-sm'
                                        : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-brand-500']"
                            >
                                &lt; 20 DT
                            </button>
                            <button
                                type="button"
                                @click="setPriceRange(20, 50)"
                                :class="['px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition shrink-0 cursor-pointer',
                                    minPrice == '20' && maxPrice == '50'
                                        ? 'bg-brand-600 text-white border-brand-600 shadow-sm'
                                        : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-brand-500']"
                            >
                                20 - 50 DT
                            </button>
                            <button
                                type="button"
                                @click="setPriceRange(50, 100)"
                                :class="['px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition shrink-0 cursor-pointer',
                                    minPrice == '50' && maxPrice == '100'
                                        ? 'bg-brand-600 text-white border-brand-600 shadow-sm'
                                        : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-brand-500']"
                            >
                                50 - 100 DT
                            </button>
                            <button
                                type="button"
                                @click="setPriceRange(100, 200)"
                                :class="['px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition shrink-0 cursor-pointer',
                                    minPrice == '100' && maxPrice == '200'
                                        ? 'bg-brand-600 text-white border-brand-600 shadow-sm'
                                        : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-brand-500']"
                            >
                                100 - 200 DT
                            </button>
                            <button
                                type="button"
                                @click="setPriceRange(200, null)"
                                :class="['px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition shrink-0 cursor-pointer',
                                    minPrice == '200' && !maxPrice
                                        ? 'bg-brand-600 text-white border-brand-600 shadow-sm'
                                        : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-brand-500']"
                            >
                                &gt; 200 DT
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div v-if="products && products.data && products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="group rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
                >
                    <!-- Product Image -->
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

                    <!-- Product Content -->
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

                        <!-- Price and CTA -->
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

            <!-- Empty State -->
            <div v-else class="text-center py-20 bg-white dark:bg-slate-800/60 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 p-8 mb-12">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-50 dark:bg-brand-950/50 border border-brand-100 dark:border-brand-900 flex items-center justify-center text-brand-600 dark:text-brand-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
                    {{ __('no_products_found') || 'Aucun produit ne correspond à votre recherche' }}
                </h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-6">
                    {{ __('try_adjusting_filters') || 'Essayez de modifier vos mots-clés ou de réinitialiser vos filtres de catégorie.' }}
                </p>
                <button
                    @click="resetFilters"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-brand-600 text-white font-bold text-xs shadow hover:bg-brand-700 transition cursor-pointer"
                >
                    Réinitialiser les filtres
                </button>
            </div>

            <!-- Pagination -->
            <div v-if="products && products.data && products.data.length > 0 && products.links && products.links.length > 3" class="flex justify-center mb-10">
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

        <BannerCarousel v-if="banners && banners.length > 0" :banners="banners" />
    </AppLayout>
</template>
