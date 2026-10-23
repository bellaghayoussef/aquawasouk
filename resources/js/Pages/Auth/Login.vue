<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Connexion - AquawaSouk" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-black font-display text-slate-900 tracking-tight">
                Bon retour parmi nous
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Accédez à votre espace client ou vendeur en toute sécurité
            </p>
        </div>

        <div v-if="status" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700 rounded-xl">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Adresse Email" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nom@exemple.com"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Mot de passe" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline"
                    >
                        Mot de passe oublié ?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center cursor-pointer select-none">
                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-brand-600 focus:ring-brand-500" />
                    <span class="ms-2 text-xs font-medium text-slate-600">Se souvenir de moi</span>
                </label>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-sm transition disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Connexion en cours...</span>
                    <span v-else>Se connecter</span>
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

            <!-- Google Login Button -->
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
                    Continuer avec Google
                </a>
            </div>

            <div class="pt-4 text-center text-xs text-slate-500 border-t border-slate-100">
                Vous n'avez pas encore de compte ?
                <Link :href="route('register')" class="font-bold text-brand-600 hover:text-brand-700 hover:underline ml-1">
                    Créer un compte
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
