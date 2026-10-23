<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    settings: Object,
})

const showApiKey = ref(false)

const form = useForm({
    enabled: Boolean(props.settings?.enabled ?? true),
    ai_enabled: Boolean(props.settings?.ai_enabled ?? false),
    ai_endpoint: props.settings?.ai_endpoint || 'https://api.openai.com/v1/chat/completions',
    ai_model: props.settings?.ai_model || 'gpt-4o-mini',
    ai_api_key: props.settings?.ai_api_key || '',
    ai_timeout: Number(props.settings?.ai_timeout || 6),
    max_recommendations: Number(props.settings?.max_recommendations || 6),
    currency: props.settings?.currency || 'DT',
    contact_phone: props.settings?.contact_phone || '+216 71 000 000',
    contact_email: props.settings?.contact_email || 'support@aquawasouk.tn',
    shipping_policy_fr: props.settings?.shipping_policy_fr || '',
    shipping_policy_ar: props.settings?.shipping_policy_ar || '',
    return_policy_fr: props.settings?.return_policy_fr || '',
    return_policy_ar: props.settings?.return_policy_ar || '',
})

const submit = () => {
    form.post(route('admin.shopping-assistant.update'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Configuration Assistant Shopping IA - AquawaSouk" />

    <DashboardLayout>
        <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6">
            <!-- Header -->
            <div class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-brand-500/10 text-brand-500 border border-brand-500/20 font-bold">
                            🤖
                        </span>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Assistant Shopping Intelligent
                        </h1>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Gérez le chatbot e-commerce, les réponses automatiques multilingues et la passerelle IA optionnelle.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold',
                            form.enabled
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                        ]"
                    >
                        <span class="w-2 h-2 rounded-full" :class="form.enabled ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                        {{ form.enabled ? 'Actif sur la boutique' : 'Désactivé' }}
                    </span>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Card 1: Activation & General Settings -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <span>⚙️</span>
                        <span>Fonctionnement Général</span>
                    </h2>

                    <div class="space-y-5">
                        <!-- Enable Widget Toggle -->
                        <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800">
                            <div>
                                <label class="font-semibold text-slate-800 dark:text-slate-200 text-sm block">
                                    Afficher le widget sur le storefront
                                </label>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Rend le bouton flottant et le chat accessibles à tous les visiteurs et clients.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.enabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Nombre max de produits recommandés
                                </label>
                                <input
                                    type="number"
                                    min="1"
                                    max="12"
                                    v-model.number="form.max_recommendations"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                />
                                <p class="text-[11px] text-slate-500 mt-1">Recommandé : 4 à 6 pour une fluidité optimale sur mobile.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Symbole de la monnaie
                                </label>
                                <input
                                    type="text"
                                    v-model="form.currency"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                />
                                <p class="text-[11px] text-slate-500 mt-1">Défaut: "DT" ou "TND".</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Téléphone du support
                                </label>
                                <input
                                    type="text"
                                    v-model="form.contact_phone"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Email du support
                                </label>
                                <input
                                    type="email"
                                    v-model="form.contact_email"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Hybrid AI Architecture -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span>🧠</span>
                                <span>Passerelle IA Optionnelle (OpenAI / Ollama / Compatible)</span>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Le moteur déterministe fonctionne à 100% sans clé d'API. L'activation de l'IA externe permet d'enrichir le langage naturel sans jamais outrepasser la base de données.
                            </p>
                        </div>
                    </div>

                    <!-- Enable AI Toggle -->
                    <div class="p-4 rounded-xl bg-brand-50/50 dark:bg-brand-950/20 border border-brand-200/50 dark:border-brand-900/40 mb-5 flex items-center justify-between">
                        <div>
                            <label class="font-semibold text-brand-900 dark:text-brand-200 text-sm block">
                                Activer le fournisseur d'IA externe
                            </label>
                            <p class="text-xs text-brand-700/80 dark:text-brand-300/70">
                                Si désactivé, le système utilise son moteur de règles déterministe gratuit (zéro coût d'API).
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.ai_enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <div v-show="form.ai_enabled" class="space-y-4 pt-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Endpoint compatible OpenAI
                                </label>
                                <input
                                    type="text"
                                    v-model="form.ai_endpoint"
                                    placeholder="https://api.openai.com/v1/chat/completions"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                />
                                <p class="text-[11px] text-slate-500 mt-1">Compatible OpenAI, Groq, Mistral, ou Ollama local.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Nom du modèle
                                </label>
                                <input
                                    type="text"
                                    v-model="form.ai_model"
                                    placeholder="gpt-4o-mini ou llama3:latest"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Clé secrète d'API (API Key)
                                </label>
                                <div class="relative">
                                    <input
                                        :type="showApiKey ? 'text' : 'password'"
                                        v-model="form.ai_api_key"
                                        placeholder="sk-..."
                                        class="w-full px-3 py-2 pr-10 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                    />
                                    <button
                                        type="button"
                                        @click="showApiKey = !showApiKey"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                    >
                                        {{ showApiKey ? 'Masquer' : 'Afficher' }}
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">La clé reste strictement confidentielle côté serveur.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Timeout requête (secondes)
                                </label>
                                <input
                                    type="number"
                                    min="2"
                                    max="30"
                                    v-model.number="form.ai_timeout"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                />
                                <p class="text-[11px] text-slate-500 mt-1">En cas de dépassement, le fallback local répond instantanément.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Verified Store Policies -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                        <span>📜</span>
                        <span>Politiques Vérifiées de la Boutique (Livraison & Retours)</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">
                        Ces textes sont utilisés par l'assistant pour répondre avec exactitude aux questions des acheteurs en Français et en Arabe.
                    </p>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Politique de livraison (Français)
                                </label>
                                <textarea
                                    v-model="form.shipping_policy_fr"
                                    rows="3"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 text-right" dir="rtl">
                                    معلومات التوصيل (العربية)
                                </label>
                                <textarea
                                    dir="rtl"
                                    v-model="form.shipping_policy_ar"
                                    rows="3"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 text-right"
                                ></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Politique de retour & remboursement (Français)
                                </label>
                                <textarea
                                    v-model="form.return_policy_fr"
                                    rows="3"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 text-right" dir="rtl">
                                    سياسة الإرجاع والاستبدال (العربية)
                                </label>
                                <textarea
                                    dir="rtl"
                                    v-model="form.return_policy_ar"
                                    rows="3"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 text-right"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm transition inline-flex items-center gap-2 disabled:opacity-50"
                    >
                        <svg v-if="form.processing" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Enregistrer les paramètres</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
