<script setup>
import { ref } from 'vue';

const openIndex = ref(0);

const toggle = (idx) => {
    openIndex.value = openIndex.value === idx ? null : idx;
};

const faqs = [
    {
        q: 'Comment fonctionne le paiement à la livraison (Cash on Delivery) ?',
        a: 'Vous ne payez rien en ligne ! Lorsque le livreur arrive à votre adresse avec votre colis, vous vérifiez l\'état du paquet et vous réglez le montant exact en espèces en toute sérénité.'
    },
    {
        q: 'Quels sont les délais et frais de livraison en Tunisie ?',
        a: 'La livraison s\'effectue sous 24 à 48 heures ouvrables sur l\'ensemble des 24 gouvernorats tunisiens. Les frais de livraison sont clairement indiqués sur chaque fiche produit avant la confirmation de votre commande.'
    },
    {
        q: 'Comment puis-je suivre l\'expédition de mon colis ?',
        a: 'Dès que le vendeur confirme et expédie votre commande, vous recevez un SMS/E-mail de confirmation. Vous pouvez également suivre son acheminement en temps réel depuis votre espace client ou en contactant notre support.'
    },
    {
        q: 'Je souhaite vendre mes produits : comment devenir vendeur partenaire ?',
        a: 'Il vous suffit de cliquer sur "Devenir Vendeur" dans le menu et de remplir le formulaire d\'inscription. Notre équipe valide votre boutique sous 24h, et vous accédez immédiatement à votre tableau de bord pour ajouter vos produits.'
    }
];
</script>

<template>
    <div class="space-y-3.5">
        <div
            v-for="(faq, index) in faqs"
            :key="index"
            class="rounded-2xl border transition-all duration-200 overflow-hidden"
            :class="[
                openIndex === index
                    ? 'bg-white dark:bg-slate-800/90 border-brand-500/40 dark:border-sky-500/40 shadow-lg shadow-brand-500/5'
                    : 'bg-white/70 dark:bg-slate-800/40 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'
            ]"
        >
            <button
                @click="toggle(index)"
                type="button"
                class="w-full px-5 sm:px-6 py-4 sm:py-5 text-left flex items-center justify-between gap-4 transition-colors"
            >
                <span class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                    {{ faq.q }}
                </span>
                <span
                    class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-transform duration-300"
                    :class="[
                        openIndex === index
                            ? 'bg-brand-500/10 text-brand-600 dark:text-sky-400 rotate-180'
                            : 'bg-slate-100 dark:bg-slate-700/60 text-slate-500'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </span>
            </button>

            <div
                v-show="openIndex === index"
                class="px-5 sm:px-6 pb-5 pt-1 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-700/60"
            >
                {{ faq.a }}
            </div>
        </div>
    </div>
</template>
