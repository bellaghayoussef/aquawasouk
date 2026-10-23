<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlowBorderCard from '@/Components/ui/GlowBorderCard.vue';

const page = usePage();
const isGuest = !page.props.auth.user;

const form = useForm({
    // User fields (only for guests)
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    // Vendor fields
    shop_name: '',
    description: '',
    address: '',
    city: '',
    postal_code: '',
    country: '',
    phone: '',
    terms: false,
});

const submit = () => {
    form.post(route('become-vendor.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Become a Vendor - Aquawasouk" />

    <AppLayout>
        <!-- Hero Section -->
        <div class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 text-white py-16 lg:py-24 border-b border-slate-800">
            <!-- Vengeance UI Aurora Mesh Effect -->
            <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-gradient-to-tr from-brand-600/15 via-cyan-400/10 to-teal-400/5 rounded-full blur-3xl animate-aurora-mesh pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-[450px] h-[450px] bg-gradient-to-br from-amber-500/10 via-brand-500/10 to-transparent rounded-full blur-3xl animate-aurora-mesh pointer-events-none" style="animation-delay: -6s;"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 space-y-4">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold tracking-wide shadow-xs">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>Opportunité Partenaires & Artisans</span>
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black tracking-tight leading-tight">
                    <span class="animate-shimmer-text bg-gradient-to-r from-white via-amber-200 to-white bg-clip-text text-transparent">
                        {{ __('become_a_vendor') }}
                    </span>
                </h1>
                <p class="text-base sm:text-xl text-slate-300 max-w-2xl mx-auto font-normal leading-relaxed">
                    {{ __('start_selling_intro') }}
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                <!-- Left: Benefits Column (Vengeance UI Glow Cards) -->
                <div class="lg:col-span-5 space-y-8">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-sky-400">Avantages Marketplace</span>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                            {{ __('why_sell_with_us') }}
                        </h2>
                    </div>

                    <div class="space-y-4">
                        <GlowBorderCard glowColor="from-brand-500/30 via-sky-500/20 to-transparent">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-sky-400 flex items-center justify-center shrink-0 border border-brand-100 dark:border-brand-800 shadow-sm">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('reach_more_customers') }}</h3>
                                    <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                                        {{ __('reach_more_customers_desc') }}
                                    </p>
                                </div>
                            </div>
                        </GlowBorderCard>

                        <GlowBorderCard glowColor="from-amber-500/30 via-orange-500/20 to-transparent">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-100 dark:border-amber-800 shadow-sm">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('easy_management') }}</h3>
                                    <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                                        {{ __('easy_management_desc') }}
                                    </p>
                                </div>
                            </div>
                        </GlowBorderCard>

                        <GlowBorderCard glowColor="from-emerald-500/30 via-teal-500/20 to-transparent">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100 dark:border-emerald-800 shadow-sm">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('secure_payments') }}</h3>
                                    <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                                        {{ __('secure_payments_desc') }}
                                    </p>
                                </div>
                            </div>
                        </GlowBorderCard>
                    </div>
                </div>

                <!-- Right: Application Form -->
                <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200/80 dark:border-slate-700/80 p-8 sm:p-10">
                    <div class="mb-6 pb-4 border-b border-slate-100 dark:border-slate-700">
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ __('apply_now') }}</h2>
                        <p class="text-xs text-slate-400 mt-1">Remplissez les informations ci-dessous pour créer votre boutique.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Guest User Registration Fields -->
                        <div v-if="isGuest" class="border-b border-slate-100 dark:border-slate-700 pb-6 mb-6">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-brand-600 dark:text-sky-400 mb-4">{{ __('create_account') }}</h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('name') }} *</label>
                                    <input type="text" id="name" v-model="form.name" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required>
                                    <span v-if="form.errors.name" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.name }}</span>
                                </div>

                                <div>
                                    <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('email') }} *</label>
                                    <input type="email" id="email" v-model="form.email" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required>
                                    <span v-if="form.errors.email" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.email }}</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('password') }} *</label>
                                        <input type="password" id="password" v-model="form.password" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required>
                                        <span v-if="form.errors.password" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.password }}</span>
                                    </div>

                                    <div>
                                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('confirm_password') }} *</label>
                                        <input type="password" id="password_confirmation" v-model="form.password_confirmation" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shop Information -->
                        <div>
                            <h3 v-if="isGuest" class="text-sm font-bold uppercase tracking-wider text-brand-600 dark:text-sky-400 mb-4">{{ __('shop_information') }}</h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="shop_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('shop_name') }} *</label>
                                    <input type="text" id="shop_name" v-model="form.shop_name" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required>
                                    <span v-if="form.errors.shop_name" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.shop_name }}</span>
                                </div>

                                <div>
                                    <label for="description" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('shop_description') }} *</label>
                                    <textarea id="description" v-model="form.description" rows="3" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required></textarea>
                                    <span v-if="form.errors.description" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.description }}</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label for="city" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('city') }} *</label>
                                        <input type="text" id="city" v-model="form.city" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required>
                                    </div>
                                    <div>
                                        <label for="postal_code" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('postal_code') }} *</label>
                                        <input type="text" id="postal_code" v-model="form.postal_code" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required>
                                    </div>
                                    <div>
                                        <label for="country" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('country') }} *</label>
                                        <input type="text" id="country" v-model="form.country" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required>
                                    </div>
                                </div>

                                <div>
                                    <label for="address" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('address') }} *</label>
                                    <input type="text" id="address" v-model="form.address" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required>
                                    <span v-if="form.errors.address" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.address }}</span>
                                </div>

                                <div>
                                    <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('phone') }} *</label>
                                    <input type="tel" id="phone" v-model="form.phone" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm" required>
                                    <span v-if="form.errors.phone" class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ form.errors.phone }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start pt-2">
                            <div class="flex items-center h-5">
                                <input id="terms" v-model="form.terms" type="checkbox" class="focus:ring-brand-500 h-4 w-4 text-brand-600 border-slate-300 rounded cursor-pointer" required>
                            </div>
                            <div class="ml-3 text-xs">
                                <label for="terms" class="font-medium text-slate-700 dark:text-slate-300 cursor-pointer">{{ __('agree_terms') }}</label>
                            </div>
                        </div>

                        <button type="submit" :disabled="form.processing" class="w-full bg-slate-900 hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black py-4 px-6 rounded-2xl shadow-xl transition-all duration-200 transform active:scale-[0.99] flex items-center justify-center cursor-pointer">
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="form.processing">{{ __('processing') }}</span>
                            <span v-else>{{ __('submit_application') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
