<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = page.props.auth.user;

const isAdmin = user.role === 'admin';
const isVendor = user.role === 'vendor';
const isCustomer = user.role === 'customer';
</script>

<template>
    <div class="min-h-screen bg-slate-100 flex flex-col md:flex-row font-sans">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-slate-300 hidden md:flex flex-col flex-shrink-0 border-r border-slate-800">
            <!-- Brand / Logo -->
            <div class="p-5 border-b border-slate-800 flex items-center justify-between h-16">
                <Link :href="route('home')" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center font-bold font-display text-base shadow-sm">
                        A
                    </div>
                    <div>
                        <span class="block text-base font-extrabold font-display text-white tracking-tight leading-none">
                            Aquawa<span class="text-brand-400">Souk</span>
                        </span>
                        <span class="text-[9px] uppercase font-bold tracking-widest text-slate-400">
                            {{ isAdmin ? 'Administration' : (isVendor ? 'Portail Vendeur' : 'Mon Compte') }}
                        </span>
                    </div>
                </Link>
            </div>

            <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto custom-scrollbar">
                <!-- Admin Links -->
                <template v-if="isAdmin">
                    <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Gestion Plateforme</p>
                    <Link :href="route('admin.dashboard')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.dashboard') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Tableau de bord
                    </Link>
                    <Link :href="route('admin.users')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.users*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Utilisateurs
                    </Link>
                    <Link :href="route('admin.vendors')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.vendors*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Vendeurs & Boutiques
                    </Link>
                    <Link :href="route('admin.cash-register')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.cash-register*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Caisse & Retraits
                    </Link>
                    <Link :href="route('admin.products')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.products') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Catalogue Produits
                    </Link>
                    <Link :href="route('admin.orders')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.orders') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Commandes
                    </Link>
                    <Link :href="route('admin.categories')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.categories*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Catégories
                    </Link>
                    <Link :href="route('admin.banners.index')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.banners*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Bannières
                    </Link>
                    <Link :href="route('admin.contact-messages')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.contact-messages*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Messages Contact
                    </Link>
                    <Link :href="route('admin.blog.index')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.blog*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                        Articles & Blog
                    </Link>
                    <Link :href="route('admin.shopping-assistant.index')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.shopping-assistant*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Assistant IA / Chat
                    </Link>
                    <Link :href="route('admin.social-bot.index')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('admin.social-bot*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        Bot Instagram & FB
                    </Link>
                </template>

                <!-- Vendor Links -->
                <template v-if="isVendor">
                    <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Ma Boutique</p>
                    <Link :href="route('vendor.dashboard')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('vendor.dashboard') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Tableau de bord
                    </Link>
                    <Link :href="route('vendor.products')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('vendor.products') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Mes Produits
                    </Link>
                    <Link :href="route('vendor.orders')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('vendor.orders') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Commandes reçues
                    </Link>
                    <Link :href="route('vendor.settings')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('vendor.settings') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Paramètres Boutique
                    </Link>
                </template>

                <!-- Customer Links -->
                <template v-if="isCustomer">
                    <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Mon Espace</p>
                    <Link :href="route('customer.dashboard')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('customer.dashboard') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Tableau de bord
                    </Link>
                    <Link :href="route('customer.orders')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('customer.orders*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Mes Commandes
                    </Link>
                    <Link :href="route('customer.settings')" :class="['flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition', route().current('customer.settings') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white']">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Paramètres
                    </Link>
                </template>

                <div class="border-t border-slate-800 my-4 pt-4">
                    <Link :href="route('home')" class="flex items-center px-3 py-2 text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Retour à la boutique
                    </Link>
                    <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center px-3 py-2 text-xs font-semibold text-rose-400 hover:bg-rose-950/40 rounded-xl transition text-left mt-1">
                        <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Déconnexion
                    </Link>
                </div>
            </nav>

            <!-- User footer -->
            <div class="p-3 border-t border-slate-800 bg-slate-950/40">
                <div class="flex items-center gap-2.5 px-2 py-1">
                    <div class="w-8 h-8 rounded-lg bg-brand-600/30 text-brand-300 border border-brand-500/30 flex items-center justify-center font-bold text-xs">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white truncate">{{ user.name }}</p>
                        <p class="text-[10px] text-slate-400 capitalize">{{ user.role }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen overflow-hidden">
            <!-- Top Navigation (Mobile / Quick access) -->
            <nav class="bg-white border-b border-slate-200/80 h-16 flex items-center">
                <div class="w-full px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <!-- Mobile Logo -->
                    <div class="flex items-center md:hidden">
                        <Link :href="route('home')" class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-brand-600 text-white font-bold flex items-center justify-center text-sm">A</div>
                            <span class="font-extrabold font-display text-slate-900">Aquawa<span class="text-brand-600">Souk</span></span>
                        </Link>
                    </div>

                    <div class="hidden md:flex items-center gap-2 text-xs text-slate-500">
                        <span>Bienvenue sur votre espace de gestion, </span>
                        <strong class="text-slate-800 font-semibold">{{ user.name }}</strong>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link :href="route('home')" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                            Voir le site
                        </Link>

                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 border border-slate-200 text-xs font-semibold rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition"
                                    >
                                        {{ user.name }}
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"> Profil & Paramètres </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button"> Déconnexion </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>

                        <!-- Mobile Hamburger -->
                        <div class="flex items-center md:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="p-2 rounded-lg text-slate-600 hover:bg-slate-100"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Responsive Navigation Menu -->
            <div v-if="showingNavigationDropdown" class="md:hidden bg-slate-900 text-white p-4 space-y-2 border-b border-slate-800">
                <template v-if="isAdmin">
                    <Link :href="route('admin.dashboard')" class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-slate-800">Tableau de bord</Link>
                    <Link :href="route('admin.users')" class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-slate-800">Utilisateurs</Link>
                    <Link :href="route('admin.vendors')" class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-slate-800">Vendeurs</Link>
                    <Link :href="route('admin.cash-register')" class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-slate-800">Caisse & Retraits</Link>
                    <Link :href="route('admin.products')" class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-slate-800">Produits</Link>
                    <Link :href="route('admin.orders')" class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-slate-800">Commandes</Link>
                </template>
                <template v-if="isVendor">
                    <Link :href="route('vendor.dashboard')" class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-slate-800">Tableau de bord</Link>
                    <Link :href="route('vendor.products')" class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-slate-800">Mes Produits</Link>
                    <Link :href="route('vendor.orders')" class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-slate-800">Commandes</Link>
                </template>
                <Link :href="route('profile.edit')" class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-slate-800">Profil</Link>
                <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-3 py-2 text-xs font-medium text-rose-400 hover:bg-slate-800">Déconnexion</Link>
            </div>

            <!-- Page Header -->
            <header class="bg-white border-b border-slate-200/80 py-5 px-4 sm:px-8" v-if="$slots.header">
                <slot name="header" />
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>