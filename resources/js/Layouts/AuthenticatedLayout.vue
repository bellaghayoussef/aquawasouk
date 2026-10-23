<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col md:flex-row">
        <!-- Sidebar for Desktop -->
        <aside class="w-64 bg-white border-r border-slate-200/80 hidden md:flex flex-col flex-shrink-0">
            <!-- Brand / Logo -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <Link :href="route('home')" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white flex items-center justify-center font-bold font-display text-lg shadow-sm">
                        A
                    </div>
                    <div>
                        <span class="block text-base font-extrabold font-display text-slate-900 tracking-tight leading-none">
                            Aquawa<span class="text-brand-600">Souk</span>
                        </span>
                        <span class="text-[9px] uppercase font-bold tracking-widest text-slate-400">Espace Client</span>
                    </div>
                </Link>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
                <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Menu Principal
                </div>

                <Link
                    :href="route('home')"
                    class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Boutique en ligne
                </Link>

                <Link
                    :href="route('products.index')"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold rounded-xl transition',
                        route().current('products.*')
                            ? 'bg-brand-50 text-brand-700'
                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Catalogue Produits
                </Link>

                <Link
                    :href="route('orders.index')"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold rounded-xl transition',
                        route().current('orders.*')
                            ? 'bg-brand-50 text-brand-700'
                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Mes Commandes
                </Link>

                <Link
                    :href="route('cart.index')"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold rounded-xl transition',
                        route().current('cart.*')
                            ? 'bg-brand-50 text-brand-700'
                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Mon Panier
                </Link>

                <div class="pt-4 border-t border-slate-100 mt-4">
                    <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Compte
                    </div>

                    <Link
                        :href="route('profile.edit')"
                        :class="[
                            'flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold rounded-xl transition',
                            route().current('profile.*')
                                ? 'bg-brand-50 text-brand-700'
                                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                        ]"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Mon Profil
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 text-xs font-semibold rounded-xl text-rose-600 hover:bg-rose-50 transition text-left"
                    >
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Déconnexion
                    </Link>
                </div>
            </nav>

            <!-- User bottom card -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ user.name }}</p>
                        <p class="text-[10px] text-slate-400 truncate">{{ user.email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-h-screen overflow-hidden">
            <!-- Mobile Navigation Bar -->
            <header class="md:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
                <Link :href="route('home')" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-600 text-white font-bold flex items-center justify-center text-sm">A</div>
                    <span class="font-extrabold font-display text-slate-900">Aquawa<span class="text-brand-600">Souk</span></span>
                </Link>

                <button
                    @click="showingNavigationDropdown = !showingNavigationDropdown"
                    class="p-2 rounded-lg text-slate-600 hover:bg-slate-100"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </header>

            <!-- Mobile Dropdown Menu -->
            <div v-if="showingNavigationDropdown" class="md:hidden bg-white border-b border-slate-200 p-4 space-y-2">
                <Link :href="route('home')" class="block px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 rounded-lg">Accueil</Link>
                <Link :href="route('products.index')" class="block px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 rounded-lg">Produits</Link>
                <Link :href="route('orders.index')" class="block px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 rounded-lg">Mes Commandes</Link>
                <Link :href="route('cart.index')" class="block px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 rounded-lg">Mon Panier</Link>
                <Link :href="route('profile.edit')" class="block px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 rounded-lg">Mon Profil</Link>
                <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg">Déconnexion</Link>
            </div>

            <!-- Page Header if present -->
            <div v-if="$slots.header" class="bg-white border-b border-slate-200/80 px-4 sm:px-8 py-5">
                <slot name="header" />
            </div>

            <!-- Main Scrollable Content -->
            <main class="flex-1 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>