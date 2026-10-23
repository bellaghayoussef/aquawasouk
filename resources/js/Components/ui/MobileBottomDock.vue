<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const cartCount = computed(() => page.props.cart_count || 0);
const authUser = computed(() => page.props.auth?.user);

const isActive = (routeName) => {
    try {
        return route().current(routeName);
    } catch (e) {
        return false;
    }
};
</script>

<template>
    <nav
        aria-label="Navigation mobile rapide"
        class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-950/95 backdrop-blur-xl border-t border-slate-200/90 dark:border-slate-800/90 px-3 py-2 shadow-2xl transition-transform duration-300"
    >
        <div class="grid grid-cols-5 items-center max-w-md mx-auto text-center">
            <!-- Home -->
            <Link
                :href="route('home')"
                class="flex flex-col items-center justify-center py-1 transition-colors relative group"
                :class="[isActive('home') ? 'text-brand-600 dark:text-sky-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white']"
            >
                <div class="relative">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span v-if="isActive('home')" class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-brand-600 dark:bg-sky-400 rounded-full animate-pulse"></span>
                </div>
                <span class="text-[10px] mt-1 tracking-tight">Accueil</span>
            </Link>

            <!-- Products -->
            <Link
                :href="route('products.index')"
                class="flex flex-col items-center justify-center py-1 transition-colors relative group"
                :class="[isActive('products.*') ? 'text-brand-600 dark:text-sky-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white']"
            >
                <div class="relative">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span v-if="isActive('products.*')" class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-brand-600 dark:bg-sky-400 rounded-full animate-pulse"></span>
                </div>
                <span class="text-[10px] mt-1 tracking-tight">Explorer</span>
            </Link>

            <!-- Cart (Elevated / Highlighted) -->
            <Link
                :href="route('cart.index')"
                class="flex flex-col items-center justify-center py-1 transition-colors relative group"
                :class="[isActive('cart.*') ? 'text-brand-600 dark:text-sky-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white']"
            >
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-brand-500/10 dark:bg-sky-500/20 text-brand-600 dark:text-sky-400 flex items-center justify-center -mt-3 shadow-md border border-brand-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <span
                        v-if="cartCount > 0"
                        class="absolute -top-3.5 -right-1 min-w-[18px] h-[18px] px-1 bg-amber-500 text-slate-950 font-black text-[10px] rounded-full flex items-center justify-center shadow-sm"
                    >
                        {{ cartCount }}
                    </span>
                </div>
                <span class="text-[10px] mt-0.5 tracking-tight">Panier</span>
            </Link>

            <!-- Vendors / Boutiques -->
            <Link
                :href="route('vendors.index')"
                class="flex flex-col items-center justify-center py-1 transition-colors relative group"
                :class="[isActive('vendors.*') ? 'text-brand-600 dark:text-sky-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white']"
            >
                <div class="relative">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span v-if="isActive('vendors.*')" class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-brand-600 dark:bg-sky-400 rounded-full animate-pulse"></span>
                </div>
                <span class="text-[10px] mt-1 tracking-tight">Boutiques</span>
            </Link>

            <!-- User Account / Login -->
            <Link
                :href="authUser ? (authUser.role === 'admin' ? route('admin.dashboard') : authUser.role === 'vendor' ? route('vendor.dashboard') : route('dashboard')) : route('login')"
                class="flex flex-col items-center justify-center py-1 transition-colors relative group"
                :class="[isActive('dashboard') || isActive('login') ? 'text-brand-600 dark:text-sky-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white']"
            >
                <div class="relative">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span v-if="authUser" class="absolute top-0 right-0 w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                </div>
                <span class="text-[10px] mt-1 tracking-tight truncate max-w-[56px]">{{ authUser ? 'Compte' : 'Connexion' }}</span>
            </Link>
        </div>
    </nav>
</template>
