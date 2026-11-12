<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const showModal = ref(false);

const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const handleKeyDown = (e) => {
    if (e.key === 'Escape' && showModal.value) {
        closeModal();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50 relative overflow-hidden">
        <!-- Subtle background decorative accents -->
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-brand-500/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-coral-500/5 blur-3xl pointer-events-none"></div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
            <!-- Brand Logo (Opens modern exploration modal instead of direct redirect) -->
            <button
                type="button"
                @click="openModal"
                class="inline-flex items-center gap-3.5 group p-2 rounded-2xl hover:bg-slate-100/80 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                title="Cliquez pour ouvrir le menu de navigation"
            >
                <div class="w-13 h-13 rounded-2xl bg-white border border-slate-200/90 shadow-md shadow-brand-500/10 p-1.5 flex items-center justify-center group-hover:scale-105 group-hover:shadow-lg transition-all duration-300">
                    <img 
                        src="/logo/logo-96.webp" 
                        alt="AquawaSouk" 
                        class="w-full h-full object-contain" 
                        onerror="this.onerror=null; this.src='/logo/logo.png';" 
                    />
                </div>
                <div class="text-left">
                    <div class="flex items-center gap-1.5">
                        <span class="block text-2xl font-black font-display text-slate-900 tracking-tight leading-none group-hover:text-brand-600 transition-colors">
                            Aquawa<span class="text-brand-600">Souk</span>
                        </span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-sky-50 text-sky-700 border border-sky-200/60 group-hover:bg-sky-100 transition">
                            Menu ▾
                        </span>
                    </div>
                    <span class="text-[10px] uppercase font-bold tracking-widest text-slate-400">Marketplace Tunisie</span>
                </div>
            </button>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4">
            <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-200/80">
                <slot />
            </div>

            <!-- Trust badge & modal trigger -->
            <div class="mt-6 text-center text-xs text-slate-400 flex items-center justify-center gap-3">
                <span class="flex items-center gap-1 text-slate-500 font-medium">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Connexion 100% Sécurisée
                </span>
                <span>&bull;</span>
                <button
                    type="button"
                    @click="openModal"
                    class="text-brand-600 hover:text-brand-700 font-bold hover:underline cursor-pointer flex items-center gap-1"
                >
                    <span>Explorer le site</span>
                    <span>▾</span>
                </button>
            </div>
        </div>

        <!-- Navigation & Exploration Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <!-- Backdrop -->
                    <div
                        class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"
                        @click="closeModal"
                    ></div>

                    <!-- Modal Dialog -->
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <Transition
                            enter-active-class="transition duration-300 ease-out"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="transition duration-200 ease-in"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <div
                                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200"
                                @click.stop
                            >
                                <!-- Modal Header -->
                                <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 px-6 py-5 text-white flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 p-1 flex items-center justify-center shrink-0">
                                            <img src="/logo/logo-96.webp" alt="Logo" class="w-full h-full object-contain brightness-110" onerror="this.onerror=null; this.src='/logo/logo.png';" />
                                        </div>
                                        <div>
                                            <h3 class="text-base font-extrabold text-white leading-tight">
                                                AquawaSouk Tunisie
                                            </h3>
                                            <p class="text-xs text-slate-300">
                                                Où souhaitez-vous aller ?
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="rounded-xl p-1.5 text-slate-400 hover:text-white hover:bg-white/10 transition cursor-pointer"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Modal Body / Links Grid -->
                                <div class="p-6 space-y-3">
                                    <!-- 1. Accueil -->
                                    <a
                                        :href="route('home')"
                                        class="flex items-center gap-4 p-3.5 rounded-2xl border border-slate-200/90 hover:border-sky-500 hover:bg-sky-50/50 transition group"
                                    >
                                        <div class="w-11 h-11 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition">
                                            🏠
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-900 group-hover:text-sky-600 transition">
                                                Page d'Accueil
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                Retourner à la vitrine principale de la marketplace
                                            </p>
                                        </div>
                                        <span class="text-slate-400 group-hover:text-sky-600 group-hover:translate-x-1 transition text-sm font-bold">
                                            &rarr;
                                        </span>
                                    </a>

                                    <!-- 2. Catalogue Produits -->
                                    <a
                                        :href="route('products.index')"
                                        class="flex items-center gap-4 p-3.5 rounded-2xl border border-slate-200/90 hover:border-sky-500 hover:bg-sky-50/50 transition group"
                                    >
                                        <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition">
                                            🛍️
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-900 group-hover:text-sky-600 transition">
                                                Catalogue des Produits
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                Découvrir toutes les offres, promos et nouveautés
                                            </p>
                                        </div>
                                        <span class="text-slate-400 group-hover:text-sky-600 group-hover:translate-x-1 transition text-sm font-bold">
                                            &rarr;
                                        </span>
                                    </a>

                                    <!-- 3. Boutiques Partenaires -->
                                    <a
                                        :href="route('vendors.index')"
                                        class="flex items-center gap-4 p-3.5 rounded-2xl border border-slate-200/90 hover:border-sky-500 hover:bg-sky-50/50 transition group"
                                    >
                                        <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition">
                                            🏪
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-900 group-hover:text-sky-600 transition">
                                                Boutiques Vendeurs
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                Explorer les commerçants officiels et vérifiés en Tunisie
                                            </p>
                                        </div>
                                        <span class="text-slate-400 group-hover:text-sky-600 group-hover:translate-x-1 transition text-sm font-bold">
                                            &rarr;
                                        </span>
                                    </a>

                                    <!-- 4. Devenir Vendeur -->
                                    <a
                                        :href="route('become-vendor')"
                                        class="flex items-center gap-4 p-3.5 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 hover:border-amber-400 transition group"
                                    >
                                        <div class="w-11 h-11 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-xs group-hover:scale-110 transition">
                                            🚀
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-black text-amber-950">
                                                    Devenir Vendeur
                                                </p>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200/80 text-amber-900">
                                                    Gratuit
                                                </span>
                                            </div>
                                            <p class="text-xs text-amber-800/80">
                                                Vendez vos produits en ligne et touchez des milliers de clients
                                            </p>
                                        </div>
                                        <span class="text-amber-600 group-hover:translate-x-1 transition text-sm font-bold">
                                            &rarr;
                                        </span>
                                    </a>

                                    <!-- 5. Support WhatsApp -->
                                    <a
                                        href="https://wa.me/21626260262?text=Bonjour%20AquawaSouk,%20j'ai%20besoin%20d'aide%20avec%20mon%20compte"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center gap-4 p-3.5 rounded-2xl border border-slate-200/90 hover:border-emerald-500 hover:bg-emerald-50/50 transition group"
                                    >
                                        <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition">
                                            💬
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition">
                                                Assistance WhatsApp
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                +216 26 260 262 • Support réactif
                                            </p>
                                        </div>
                                        <span class="text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition text-sm font-bold">
                                            &rarr;
                                        </span>
                                    </a>
                                </div>

                                <!-- Modal Footer -->
                                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex items-center justify-between gap-3">
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-200/70 transition cursor-pointer"
                                    >
                                        Rester sur cette page
                                    </button>
                                    <a
                                        :href="route('home')"
                                        class="px-5 py-2.5 rounded-xl text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 transition shadow-xs flex items-center gap-1.5 cursor-pointer"
                                    >
                                        <span>Aller à l'accueil</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
