<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('contact.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Contactez-nous - AquawaSouk" />

    <AppLayout>
        <div class="relative overflow-hidden bg-slate-50/50 dark:bg-slate-950/50 py-12 sm:py-16">
            <!-- Background ambient glow -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-72 bg-gradient-to-b from-brand-500/10 via-cyan-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-50 dark:bg-brand-950/60 border border-brand-200/80 dark:border-brand-800/80 text-brand-700 dark:text-brand-300 text-xs font-bold uppercase tracking-wider mb-4 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Service Client & Support
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black font-display text-slate-900 dark:text-white tracking-tight leading-tight">
                        {{ __('contact_us') }}
                    </h1>
                    <p class="mt-3 text-base sm:text-lg text-slate-600 dark:text-slate-300">
                        {{ __('contact_intro') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                    <!-- Left: Contact Details Card -->
                    <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none relative overflow-hidden">
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-brand-500/10 rounded-full blur-2xl pointer-events-none"></div>

                        <h2 class="text-xl sm:text-2xl font-bold font-display text-slate-900 dark:text-white mb-2">
                            {{ __('get_in_touch') }}
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-8">
                            Notre équipe vous répond sous 2 à 4 heures pendant nos heures d'ouverture.
                        </p>

                        <div class="space-y-6">
                            <!-- Phone -->
                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 transition hover:border-brand-300 dark:hover:border-brand-700">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-brand-500/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                        {{ __('phone') }}
                                    </span>
                                    <a href="tel:+21656818880" class="text-base font-bold text-slate-900 dark:text-white hover:text-brand-600 transition block mt-0.5">
                                        +216 56 818 880
                                    </a>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Lundi - Samedi : 09h00 - 19h00
                                    </span>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 transition hover:border-brand-300 dark:hover:border-brand-700">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-cyan-500 to-sky-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-cyan-500/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                        {{ __('email') }}
                                    </span>
                                    <a href="mailto:contact@aquawasouk.com" class="text-base font-bold text-slate-900 dark:text-white hover:text-brand-600 transition block mt-0.5 break-all">
                                        contact@aquawasouk.com
                                    </a>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">
                                        Support commercial et assistance technique
                                    </span>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 transition hover:border-brand-300 dark:hover:border-brand-700">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-500/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                        {{ __('address') }}
                                    </span>
                                    <span class="text-base font-bold text-slate-900 dark:text-white block mt-0.5">
                                        Grand Tunis, Tunisie
                                    </span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">
                                        Livraison disponible sur toute la République Tunisienne
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Trust reassurance -->
                        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800/80 flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Données sécurisées & respect strict de la confidentialité.</span>
                        </div>
                    </div>

                    <!-- Right: Form -->
                    <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 border border-slate-200/80 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none">
                        <div class="mb-6">
                            <h3 class="text-xl sm:text-2xl font-bold font-display text-slate-900 dark:text-white">
                                Envoyez-nous un message
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Remplissez le formulaire ci-dessous et nous prendrons contact avec vous sous peu.
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                        {{ __('name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="name"
                                        v-model="form.name"
                                        placeholder="Votre nom complet"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition text-sm shadow-xs"
                                        required
                                    >
                                    <span v-if="form.errors.name" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.name }}</span>
                                </div>

                                <div>
                                    <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                        {{ __('email') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        id="email"
                                        v-model="form.email"
                                        placeholder="nom@exemple.com"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition text-sm shadow-xs"
                                        required
                                    >
                                    <span v-if="form.errors.email" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.email }}</span>
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    {{ __('subject') }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="subject"
                                    v-model="form.subject"
                                    placeholder="Ex: Demande de renseignement sur une commande"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition text-sm shadow-xs"
                                    required
                                >
                                <span v-if="form.errors.subject" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.subject }}</span>
                            </div>

                            <div>
                                <label for="message" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    {{ __('message') }} <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="5"
                                    placeholder="Expliquez-nous en détail votre demande..."
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition text-sm shadow-xs resize-y"
                                    required
                                ></textarea>
                                <span v-if="form.errors.message" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.message }}</span>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3.5 px-6 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-brand-600 via-brand-700 to-brand-800 hover:from-brand-500 hover:to-brand-700 shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 active:scale-[0.99] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <span v-if="form.processing">{{ __('processing') }}</span>
                                <span v-else>{{ __('send_message') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
