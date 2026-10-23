<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50 relative overflow-hidden">
        <!-- Subtle background decorative accents -->
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-brand-500/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-coral-500/5 blur-3xl pointer-events-none"></div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
            <!-- Brand Logo -->
            <Link :href="route('home')" class="inline-flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-white flex items-center justify-center font-bold font-display text-2xl shadow-lg shadow-brand-500/25 group-hover:scale-105 transition-transform">
                    A
                </div>
                <div class="text-left">
                    <span class="block text-2xl font-black font-display text-slate-900 tracking-tight leading-none">
                        Aquawa<span class="text-brand-600">Souk</span>
                    </span>
                    <span class="text-[10px] uppercase font-bold tracking-widest text-slate-400">Marketplace Tunisie</span>
                </div>
            </Link>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4">
            <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-200/80">
                <slot />
            </div>

            <!-- Trust badge -->
            <div class="mt-6 text-center text-xs text-slate-400 flex items-center justify-center gap-4">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Connexion 100% Sécurisée
                </span>
                <span>&bull;</span>
                <Link :href="route('home')" class="text-brand-600 hover:text-brand-700 font-semibold hover:underline">
                    Retour à l'accueil
                </Link>
            </div>
        </div>
    </div>
</template>