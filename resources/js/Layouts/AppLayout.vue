<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import ShoppingAssistantWidget from '@/Components/ShoppingAssistant/ShoppingAssistantWidget.vue'
import NewsletterGlow from '@/Components/ui/NewsletterGlow.vue'
import BackToTop from '@/Components/ui/BackToTop.vue'
import MobileBottomDock from '@/Components/ui/MobileBottomDock.vue'
import LiveSalesToast from '@/Components/ui/LiveSalesToast.vue'

/* UI states */
const showingNavigationDropdown = ref(false)
const showLangMenu = ref(false)
const showUserMenu = ref(false)
const openCategory = ref(null)
const headerSearch = ref('')

/* Data */
const categories = ref([])
const cartCount = ref(0)

const page = usePage()
const authUser = computed(() => page.props.auth?.user)
const currentLocale = computed(() => (page.props.locale || 'fr').toUpperCase())

/* RTL handling */
const updateDirection = () => {
    const locale = page.props.locale || 'fr'
    document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr'
    document.documentElement.lang = locale
}

/* Toggle category dropdown */
const toggleCategory = (id) => {
    openCategory.value = openCategory.value === id ? null : id
}

/* Load categories from API */
const loadCategories = async () => {
    try {
        const { data } = await axios.get('/api/v1/categories')
        categories.value = data.data || []
    } catch (error) {
        console.error('Failed to load categories', error)
    }
}

const performHeaderSearch = () => {
    if (headerSearch.value.trim()) {
        router.get(route('products.index'), { search: headerSearch.value.trim() })
    }
}

onMounted(() => {
    updateDirection()
    loadCategories()
})

watch(() => page.props.locale, updateDirection)
</script>

<template>
    <div class="min-h-screen flex flex-col bg-[#FAFBFD] dark:bg-slate-950 font-sans selection:bg-brand-500 selection:text-white pb-16 md:pb-0">
        <!-- Top Announcement Bar -->
        <div class="bg-slate-950 text-slate-300 text-xs py-1.5 px-3 sm:px-4 border-b border-slate-800/80">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-2">
                <!-- Value props ticker -->
                <div class="flex items-center gap-3 overflow-x-auto no-scrollbar text-[11px] sm:text-xs tracking-wide whitespace-nowrap">
                    <span class="inline-flex items-center gap-1.5 font-semibold text-amber-400 shrink-0">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>Livraison express partout en Tunisie</span>
                    </span>
                    <span class="hidden md:inline text-slate-700">•</span>
                    <span class="hidden md:inline-flex items-center gap-1.5 text-slate-300 font-medium shrink-0">
                        <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Paiement en espèces à la livraison (COD)</span>
                    </span>
                    <span class="hidden lg:inline text-slate-700">•</span>
                    <span class="hidden lg:inline-flex items-center gap-1.5 text-slate-300 font-medium shrink-0">
                        <svg class="w-3.5 h-3.5 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Garantie vendeurs vérifiés</span>
                    </span>
                </div>

                <!-- Secondary utility links -->
                <div class="flex items-center gap-2 sm:gap-4 text-xs shrink-0">
                    <Link :href="route('become-vendor')" class="hidden sm:flex hover:text-amber-400 transition-colors items-center gap-1.5 text-slate-300 font-medium cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>{{ __('become_a_vendor') }}</span>
                    </Link>

                    <span class="hidden sm:inline text-slate-700">|</span>

                    <!-- Language Switcher Dropdown -->
                    <div class="relative">
                        <button
                            @click="showLangMenu = !showLangMenu"
                            class="flex items-center gap-1 sm:gap-1.5 font-semibold text-slate-200 hover:text-white transition-colors px-1.5 sm:px-2 py-0.5 rounded-md hover:bg-slate-800 cursor-pointer text-[11px] sm:text-xs"
                            aria-label="Sélectionner la langue"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                            </svg>
                            <span>{{ currentLocale }}</span>
                            <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="{'rotate-180': showLangMenu}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div
                            v-if="showLangMenu"
                            @click.away="showLangMenu = false"
                            class="absolute right-0 mt-1 w-36 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 py-1 z-50 overflow-hidden"
                        >
                            <Link :href="route('language.switch', 'fr')" class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                                <span>Français</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-mono">FR</span>
                            </Link>
                            <Link :href="route('language.switch', 'ar')" class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                                <span>العربية</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-mono">AR</span>
                            </Link>
                            <Link :href="route('language.switch', 'en')" class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                                <span>English</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-mono">EN</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header & Navigation -->
        <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 transition-colors shadow-sm">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 sm:h-20 gap-2 sm:gap-4">
                    <!-- Brand Logo -->
                    <div class="flex items-center shrink-0 min-w-0">
                        <Link :href="route('home')" class="flex items-center gap-2 sm:gap-3 group">
                            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-tr from-brand-700 via-brand-600 to-sky-400 p-0.5 shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform duration-300 shrink-0">
                                <div class="w-full h-full bg-white dark:bg-slate-900 rounded-[10px] flex items-center justify-center overflow-hidden">
                                    <img src="/logo/logo.png" alt="Aquawasouk" class="w-6 h-6 sm:w-8 sm:h-8 object-contain" />
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white leading-none whitespace-nowrap">
                                    Aquawa<span class="text-brand-600 dark:text-sky-400">Souk</span>
                                </span>
                                <span class="hidden sm:inline-block text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 mt-1">
                                    Place de Marché
                                </span>
                            </div>
                        </Link>
                    </div>

                    <!-- Search Bar (Desktop) -->
                    <div class="hidden lg:flex flex-1 max-w-xl mx-6">
                        <form @submit.prevent="performHeaderSearch" class="w-full relative">
                            <div class="relative flex items-center">
                                <input
                                    v-model="headerSearch"
                                    type="text"
                                    :placeholder="__('search_products') || 'Rechercher un produit, une marque, une boutique...'"
                                    class="w-full pl-11 pr-24 py-2.5 text-sm rounded-full bg-slate-100/90 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-900 focus:border-brand-600 focus:ring-4 focus:ring-brand-500/10 text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all shadow-inner"
                                />
                                <div class="absolute left-4 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <button
                                    type="submit"
                                    class="absolute right-1.5 px-4 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-full text-xs font-semibold shadow-sm transition hover:scale-[1.02] active:scale-[0.98]"
                                >
                                    {{ __('search') || 'Trouver' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Navigation Links & Action Buttons -->
                    <div class="flex items-center gap-1 sm:gap-3 shrink-0">
                        <!-- Navigation links (Desktop) -->
                        <nav class="hidden md:flex items-center gap-1 mr-2 text-sm font-semibold">
                            <Link
                                :href="route('products.index')"
                                class="px-3 py-2 rounded-lg transition-colors"
                                :class="[route().current('products.*') ? 'text-brand-600 dark:text-sky-400 bg-brand-50 dark:bg-slate-800' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60']"
                            >
                                {{ __('products') }}
                            </Link>
                            <Link
                                :href="route('vendors.index')"
                                class="px-3 py-2 rounded-lg transition-colors"
                                :class="[route().current('vendors.*') ? 'text-brand-600 dark:text-sky-400 bg-brand-50 dark:bg-slate-800' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60']"
                            >
                                {{ __('vendors') }}
                            </Link>
                            <Link
                                :href="route('blog.index')"
                                class="px-3 py-2 rounded-lg transition-colors"
                                :class="[route().current('blog.*') ? 'text-brand-600 dark:text-sky-400 bg-brand-50 dark:bg-slate-800' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60']"
                            >
                                {{ __('blog') }}
                            </Link>
                        </nav>

                        <!-- Cart Button -->
                        <Link
                            :href="route('cart.index')"
                            class="relative p-2 sm:p-2.5 rounded-full text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition group shrink-0"
                            title="Panier d'achats"
                        >
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span
                                v-if="cartCount > 0"
                                class="absolute top-0.5 right-0.5 sm:top-1 sm:right-1 min-w-[18px] sm:min-w-[20px] h-4 sm:h-5 px-1 bg-amber-500 text-slate-950 font-black text-[10px] sm:text-[11px] rounded-full flex items-center justify-center shadow-md animate-pulse"
                            >
                                {{ cartCount }}
                            </span>
                        </Link>

                        <!-- Authenticated User Dropdown -->
                        <div v-if="authUser" class="relative shrink-0">
                            <button
                                @click="showUserMenu = !showUserMenu"
                                class="flex items-center gap-1.5 sm:gap-2 p-1 sm:pl-2 sm:pr-3 sm:py-1.5 rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 hover:bg-white dark:hover:bg-slate-800 transition"
                            >
                                <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-brand-600 to-sky-400 text-white font-bold text-xs flex items-center justify-center shadow-sm shrink-0">
                                    {{ authUser.name.charAt(0).toUpperCase() }}
                                </div>
                                <span class="hidden sm:inline text-xs font-semibold text-slate-700 dark:text-slate-200 max-w-[100px] truncate">
                                    {{ authUser.name }}
                                </span>
                                <svg class="w-3.5 h-3.5 text-slate-400 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div
                                v-if="showUserMenu"
                                @click.away="showUserMenu = false"
                                class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 py-2 z-50 divide-y divide-slate-100 dark:divide-slate-800 overflow-hidden"
                            >
                                <div class="px-4 py-2.5">
                                    <p class="text-xs font-medium text-slate-400">Connecté en tant que</p>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ authUser.name }}</p>
                                    <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded uppercase tracking-wider bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                                        {{ authUser.role }}
                                    </span>
                                </div>

                                <div class="py-1">
                                    <Link
                                        :href="authUser.role === 'admin'
                                            ? route('admin.dashboard')
                                            : authUser.role === 'vendor'
                                                ? route('vendor.dashboard')
                                                : route('dashboard')"
                                        class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                    >
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                        <span>{{ __('dashboard') }}</span>
                                    </Link>
                                    <Link
                                        :href="route('orders.index')"
                                        class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                    >
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <span>Mes Commandes</span>
                                    </Link>
                                    <Link
                                        :href="route('profile.edit')"
                                        class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                    >
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>Mon Profil</span>
                                    </Link>
                                </div>

                                <div class="py-1">
                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition"
                                    >
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        <span>{{ __('logout') }}</span>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Guest Actions (Desktop & Tablet) -->
                        <div v-else class="hidden sm:flex items-center gap-2 shrink-0">
                            <Link
                                :href="route('login')"
                                class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 hover:text-brand-600 dark:hover:text-sky-400 px-3 py-2 transition"
                            >
                                {{ __('login') }}
                            </Link>
                            <Link
                                :href="route('register')"
                                class="inline-flex items-center text-xs sm:text-sm font-bold text-white bg-slate-900 hover:bg-brand-600 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200 px-4 py-2 rounded-full shadow-sm hover:shadow transition-all duration-200"
                            >
                                {{ __('register') }}
                            </Link>
                        </div>

                        <!-- Mobile User Icon (Guest on Phone) -->
                        <Link
                            v-if="!authUser"
                            :href="route('login')"
                            class="sm:hidden p-2 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0"
                            title="Connexion"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </Link>

                        <!-- Mobile Hamburger Button -->
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="md:hidden p-2 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0"
                            aria-label="Menu"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Search Bar -->
                <div class="lg:hidden pb-2.5 pt-0.5">
                    <form @submit.prevent="performHeaderSearch" class="relative">
                        <input
                            v-model="headerSearch"
                            type="text"
                            :placeholder="__('search_products') || 'Rechercher un produit...'"
                            class="w-full pl-10 pr-4 py-2 text-xs rounded-full bg-slate-100 dark:bg-slate-800 border-none text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 shadow-inner"
                        />
                        <div class="absolute left-3.5 top-2.5 text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Categories Horizontal Ribbon (Fluid & Clean) -->
            <div v-if="categories && categories.length > 0" class="border-t border-slate-100 dark:border-slate-800/80 bg-white dark:bg-slate-900 hidden sm:block">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-1 overflow-x-auto py-2 no-scrollbar text-xs font-semibold text-slate-600 dark:text-slate-300">
                        <Link
                            :href="route('products.index')"
                            class="px-3 py-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0"
                            :class="{'text-brand-600 dark:text-sky-400 bg-brand-50 dark:bg-slate-800 font-bold': !route().params?.category}"
                        >
                            {{ __('all_categories') || 'Toutes les catégories' }}
                        </Link>
                        <div v-for="cat in categories" :key="cat.id" class="relative group shrink-0">
                            <Link
                                :href="route('products.index', { category: cat.slug })"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                :class="{'text-brand-600 dark:text-sky-400 bg-brand-50 dark:bg-slate-800 font-bold': route().params?.category === cat.slug}"
                            >
                                <span>{{ cat.name }}</span>
                                <svg v-if="cat.children && cat.children.length" class="w-3 h-3 text-slate-400 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </Link>

                            <!-- Subcategories floating flyout -->
                            <div
                                v-if="cat.children && cat.children.length"
                                class="absolute top-full left-0 mt-1 w-52 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-2 z-50 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200"
                            >
                                <Link
                                    v-for="sub in cat.children"
                                    :key="sub.id"
                                    :href="route('products.index', { category: sub.slug })"
                                    class="block px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-brand-50 dark:hover:bg-slate-800 hover:text-brand-600 transition"
                                >
                                    {{ sub.name }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div v-if="showingNavigationDropdown" class="md:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 pt-3 pb-6 space-y-3">
                <nav class="space-y-1">
                    <Link
                        :href="route('home')"
                        class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        {{ __('home') }}
                    </Link>
                    <Link
                        :href="route('products.index')"
                        class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        {{ __('products') }}
                    </Link>
                    <Link
                        :href="route('vendors.index')"
                        class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        {{ __('vendors') }}
                    </Link>
                    <Link
                        :href="route('blog.index')"
                        class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        {{ __('blog') }}
                    </Link>
                    <Link
                        :href="route('contact')"
                        class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        {{ __('contact') }}
                    </Link>
                    <Link
                        :href="route('become-vendor')"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 transition-colors"
                    >
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>{{ __('become_a_vendor') }}</span>
                    </Link>
                </nav>

                <!-- Mobile Auth -->
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800">
                    <div v-if="authUser" class="space-y-1">
                        <div class="px-3 py-1 text-xs text-slate-400">
                            Connecté en tant que <strong class="text-slate-800 dark:text-white">{{ authUser.name }}</strong>
                        </div>
                        <Link
                            :href="authUser.role === 'admin' ? route('admin.dashboard') : authUser.role === 'vendor' ? route('vendor.dashboard') : route('dashboard')"
                            class="block px-3 py-2 rounded-lg text-sm font-semibold text-brand-600 dark:text-sky-400 hover:bg-brand-50"
                        >
                            {{ __('dashboard') }}
                        </Link>
                        <Link
                            :href="route('orders.index')"
                            class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 dark:text-white hover:bg-slate-100"
                        >
                            Mes Commandes
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="w-full text-left block px-3 py-2 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50"
                        >
                            {{ __('logout') }}
                        </Link>
                    </div>
                    <div v-else class="grid grid-cols-2 gap-2 pt-1">
                        <Link
                            :href="route('login')"
                            class="text-center py-2 text-sm font-semibold rounded-xl border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200"
                        >
                            {{ __('login') }}
                        </Link>
                        <Link
                            :href="route('register')"
                            class="text-center py-2 text-sm font-semibold rounded-xl bg-brand-600 text-white"
                        >
                            {{ __('register') }}
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- High-End E-Commerce Footer -->
        <footer class="bg-slate-950 text-slate-300 border-t border-slate-800 mt-20 pt-16 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Interactive Newsletter Glow Strip (Skiper UI & Vengeance UI) -->
                <NewsletterGlow />

                <!-- Reassurance 4-Grid Banner (Animaster Lib Ambient Pulse & Glassmorphism) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pb-14 border-b border-slate-800/80">
                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-900/80 backdrop-blur-md border border-slate-800 hover:border-brand-500/40 hover:shadow-lg hover:shadow-brand-500/10 transition-all duration-300 group">
                        <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-400 flex items-center justify-center shrink-0 border border-brand-500/20 group-hover:scale-105 group-hover:bg-brand-500/20 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 17a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4zM2 5h11v9H2V5zm11 3h4.172a2 2 0 011.414.586l2.828 2.828A2 2 0 0122 12.828V14h-3M2 14h2m7 0h3"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm">Livraison Rapide</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Expédition express sécurisée vers les 24 gouvernorats tunisiens.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-900/80 backdrop-blur-md border border-slate-800 hover:border-emerald-500/40 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 group">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20 group-hover:scale-105 group-hover:bg-emerald-500/20 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm">Paiement à la Livraison</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Payez en toute confiance en espèces dès la réception de votre colis.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-900/80 backdrop-blur-md border border-slate-800 hover:border-amber-500/40 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 group">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20 group-hover:scale-105 group-hover:bg-amber-500/20 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm">Vendeurs Vérifiés</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Boutiques certifiées avec produits de qualité et service garanti.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-900/80 backdrop-blur-md border border-slate-800 hover:border-purple-500/40 hover:shadow-lg hover:shadow-purple-500/10 transition-all duration-300 group">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center shrink-0 border border-purple-500/20 group-hover:scale-105 group-hover:bg-purple-500/20 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm">Support Client Réactif</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Une équipe à votre écoute par téléphone et e-mail 7j/7.</p>
                        </div>
                    </div>
                </div>

                <!-- Structured Columns -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 py-14 border-b border-slate-800/80">
                    <!-- Brand info -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex items-center gap-3">
                            <img src="/logo/logo.png" alt="Aquawasouk" class="w-9 h-9" />
                            <span class="text-2xl font-black text-white tracking-tight">
                                Aquawa<span class="text-sky-400">Souk</span>
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                            La place de marché multi-vendeurs de référence en Tunisie. Découvrez des milliers d'articles authentiques sélectionnés auprès de boutiques indépendantes certifiées.
                        </p>
                        
                        <!-- Contact info -->
                        <div class="pt-2 text-xs text-slate-400 space-y-2">
                            <p class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Tunisie (Livraison nationale 24 gouvernorats)</span>
                            </p>
                            <p class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <a href="mailto:contact@aquawasouk.com" class="hover:text-white transition">contact@aquawasouk.com</a>
                            </p>
                        </div>

                        <!-- Direct WhatsApp & Social Badges (Skiper UI Micro-Pills) -->
                        <div class="pt-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2.5">Assistance directe & Communauté</p>
                            <div class="flex flex-wrap items-center gap-2">
                                <a
                                    href="https://wa.me/21650000000"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-950/60 text-emerald-300 border border-emerald-500/30 text-xs font-bold hover:bg-emerald-900/80 hover:border-emerald-400 hover:scale-105 transition-all shadow-sm group"
                                    title="Discuter sur WhatsApp"
                                >
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                    <span>WhatsApp Direct</span>
                                </a>
                                <a
                                    href="https://facebook.com"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="p-2 rounded-full bg-slate-900 text-slate-400 hover:text-sky-400 hover:bg-slate-800 border border-slate-800 hover:border-sky-500/40 hover:scale-110 transition-all"
                                    aria-label="Facebook"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                <a
                                    href="https://instagram.com"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="p-2 rounded-full bg-slate-900 text-slate-400 hover:text-pink-400 hover:bg-slate-800 border border-slate-800 hover:border-pink-500/40 hover:scale-110 transition-all"
                                    aria-label="Instagram"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Marketplace navigation -->
                    <div>
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Marketplace</h4>
                        <ul class="space-y-2.5 text-xs text-slate-400">
                            <li><Link :href="route('products.index')" class="hover:text-white transition">Tous les produits</Link></li>
                            <li><Link :href="route('vendors.index')" class="hover:text-white transition">Nos boutiques partenaires</Link></li>
                            <li><Link :href="route('become-vendor')" class="hover:text-amber-400 transition font-semibold">Devenir Vendeur</Link></li>
                            <li><Link :href="route('blog.index')" class="hover:text-white transition">Blog & Astuces</Link></li>
                        </ul>
                    </div>

                    <!-- Customer Service -->
                    <div>
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Service Client</h4>
                        <ul class="space-y-2.5 text-xs text-slate-400">
                            <li><Link :href="route('contact')" class="hover:text-white transition">Contact & Assistance</Link></li>
                            <li><Link :href="route('orders.index')" class="hover:text-white transition">Suivre ma commande</Link></li>
                            <li><Link :href="route('cart.index')" class="hover:text-white transition">Mon Panier</Link></li>
                            <li><Link :href="route('login')" class="hover:text-white transition">Espace Membre</Link></li>
                        </ul>
                    </div>

                    <!-- Legal & Policies -->
                    <div>
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Informations</h4>
                        <ul class="space-y-2.5 text-xs text-slate-400">
                            <li><Link :href="route('privacy-policy')" class="hover:text-white transition">Politique de confidentialité</Link></li>
                            <li><Link :href="route('terms-of-service')" class="hover:text-white transition">Conditions Générales d'Utilisation</Link></li>
                            <li><span class="text-slate-500">Expédition 24/48h</span></li>
                            <li><span class="text-slate-500">Paiement à la réception</span></li>
                        </ul>
                    </div>
                </div>

                <!-- Copyright Bar with Reassurance Badges -->
                <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                    <p>© {{ new Date().getFullYear() }} AquawaSouk Marketplace. Tous droits réservés.</p>
                    <div class="flex items-center gap-4 text-slate-400 flex-wrap justify-center">
                        <span class="inline-flex items-center gap-1.5 text-slate-400 text-[11px] px-2.5 py-1 rounded-full bg-slate-900 border border-slate-800">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Espèces à la livraison</span>
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1.5 text-slate-400 text-[11px] px-2.5 py-1 rounded-full bg-slate-900 border border-slate-800">
                            <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                            </svg>
                            <span>Virement bancaire</span>
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1 text-slate-400 text-[11px] px-2.5 py-1 rounded-full bg-slate-900 border border-slate-800">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span>Garantie 100% Satisfait</span>
                        </span>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Floating UI Widgets (Skiper UI & Animaster Lib) -->
        <BackToTop />
        <MobileBottomDock />
        <LiveSalesToast />

        <!-- Intelligent AI Shopping Assistant Widget -->
        <ShoppingAssistantWidget />
    </div>
</template>
