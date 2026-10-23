<script setup>
import { ref } from 'vue';

const email = ref('');
const isSubscribed = ref(false);
const isLoading = ref(false);

const handleSubscribe = () => {
    if (!email.value || !email.value.includes('@')) return;
    
    isLoading.value = true;
    setTimeout(() => {
        isLoading.value = false;
        isSubscribed.value = true;
        email.value = '';
    }, 700);
};
</script>

<template>
    <div class="relative overflow-hidden rounded-3xl p-8 sm:p-10 bg-gradient-to-br from-slate-900 via-slate-900/95 to-slate-950 border border-slate-800 shadow-2xl mb-14">
        <!-- Ambient radial glow (Vengeance UI style) -->
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8">
            <!-- Left Info -->
            <div class="max-w-xl text-center lg:text-left">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-brand-500/15 text-sky-400 border border-brand-500/25 mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Offres & Privilèges Exclusifs
                </span>
                <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    Recevez nos meilleures promotions de la semaine
                </h3>
                <p class="text-xs sm:text-sm text-slate-400 mt-2 leading-relaxed">
                    Inscrivez-vous pour bénéficier des réductions de nos vendeurs partenaires et des ventes flash en avant-première.
                </p>
            </div>

            <!-- Right Form -->
            <div class="w-full lg:max-w-md">
                <div v-if="isSubscribed" class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 text-xs sm:text-sm flex items-center gap-3 animate-fadeIn">
                    <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center shrink-0 text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold">Félicitations !</p>
                        <p class="text-emerald-400/80 text-xs">Votre adresse est enregistrée pour les prochaines promotions exclusives.</p>
                    </div>
                </div>

                <form v-else @submit.prevent="handleSubscribe" class="flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <input
                            v-model="email"
                            type="email"
                            required
                            placeholder="Votre adresse e-mail..."
                            class="w-full pl-11 pr-4 py-3 text-xs sm:text-sm rounded-xl bg-slate-950/80 border border-slate-700/80 focus:border-sky-400 focus:ring-4 focus:ring-sky-500/15 text-white placeholder-slate-500 transition shadow-inner outline-none"
                        />
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-sky-500 hover:from-brand-500 hover:to-sky-400 text-white font-bold text-xs sm:text-sm shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shrink-0 disabled:opacity-50"
                    >
                        <span v-if="!isLoading">S'abonner</span>
                        <svg v-else class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
                <p class="text-[11px] text-slate-500 mt-2 text-center lg:text-left flex items-center justify-center lg:justify-start gap-1">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Pas de spam. Vos données restent 100% confidentielles.
                </p>
            </div>
        </div>
    </div>
</template>
