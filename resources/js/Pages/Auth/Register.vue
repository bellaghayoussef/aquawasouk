<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Créer un compte - AquawaSouk" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-black font-display text-slate-900 tracking-tight">
                Créer un compte
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Rejoignez des milliers d'acheteurs et de vendeurs vérifiés
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="Nom complet" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Ahmed Ben Ali"
                />
                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Adresse Email" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="nom@exemple.com"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Mot de passe" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirmer le mot de passe"
                    class="text-xs font-bold text-slate-700 uppercase tracking-wider"
                />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError
                    class="mt-1.5"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-sm transition disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Création du compte...</span>
                    <span v-else>Créer mon compte</span>
                </button>
            </div>

            <!-- OR Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase tracking-wider">
                    <span class="px-3 bg-white text-slate-400 font-semibold">Ou continuer avec</span>
                </div>
            </div>

            <!-- Google Sign Up Button -->
            <div>
                <a
                    :href="route('auth.google')"
                    class="w-full inline-flex items-center justify-center gap-2.5 px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition shadow-xs"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    S'inscrire avec Google
                </a>
            </div>

            <div class="pt-4 text-center text-xs text-slate-500 border-t border-slate-100">
                Vous avez déjà un compte ?
                <Link :href="route('login')" class="font-bold text-brand-600 hover:text-brand-700 hover:underline ml-1">
                    Se connecter
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
