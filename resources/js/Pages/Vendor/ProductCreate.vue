<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({ categories: Array });

// ─── Active Tab ───────────────────────────────────────────────────────────────
const activeTab = ref('general');
const tabs = [
    { key: 'general',    label: 'Général',         icon: '📦' },
    { key: 'variations', label: 'Tailles & Couleurs', icon: '🎨' },
    { key: 'specs',      label: 'Fiche Technique', icon: '📋' },
    { key: 'images',     label: 'Photos',          icon: '🖼️' },
];

// ─── Main Form ────────────────────────────────────────────────────────────────
const form = useForm({
    name:           '',
    description:    '',
    price:          '',
    discount_price: '',
    category_id:    '',
    stock:          '',
    sku:            '',
    images:         [],
    is_featured:    false,
    specifications: {},   // { key: value }
    variations:     [],   // [{ color, size, stock, price }]
});

// ─── Images ───────────────────────────────────────────────────────────────────
const imagePreviews = ref([]);
const handleImageChange = (event) => {
    const files = Array.from(event.target.files);
    form.images = files;
    imagePreviews.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => imagePreviews.value.push(e.target.result);
        reader.readAsDataURL(file);
    });
};
const removeImage = (index) => {
    const arr = Array.from(form.images);
    arr.splice(index, 1);
    form.images = arr;
    imagePreviews.value.splice(index, 1);
};

// ─── Variations (Tailles & Couleurs) ─────────────────────────────────────────
const PRESET_COLORS = [
    { label: 'Noir',    hex: '#111827' },
    { label: 'Blanc',   hex: '#F9FAFB' },
    { label: 'Rouge',   hex: '#EF4444' },
    { label: 'Bleu',    hex: '#3B82F6' },
    { label: 'Vert',    hex: '#22C55E' },
    { label: 'Jaune',   hex: '#EAB308' },
    { label: 'Orange',  hex: '#F97316' },
    { label: 'Rose',    hex: '#EC4899' },
    { label: 'Violet',  hex: '#A855F7' },
    { label: 'Gris',    hex: '#6B7280' },
    { label: 'Marron',  hex: '#92400E' },
    { label: 'Beige',   hex: '#D4B896' },
];
const PRESET_SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL',
                      '36', '37', '38', '39', '40', '41', '42', '43', '44', '45',
                      'Unique'];

const newVariation = ref({ color: '', colorHex: '', size: '', stock: '', price: '' });

const addVariation = () => {
    if (!newVariation.value.color && !newVariation.value.size) return;
    form.variations.push({ ...newVariation.value });
    newVariation.value = { color: '', colorHex: '', size: '', stock: '', price: '' };
};

const removeVariation = (i) => form.variations.splice(i, 1);

const pickColor = (c) => {
    newVariation.value.color    = c.label;
    newVariation.value.colorHex = c.hex;
};

const pickSize = (s) => { newVariation.value.size = s; };

// ─── Specifications (Fiche Technique) ────────────────────────────────────────
const newSpecKey = ref('');
const newSpecVal = ref('');
const specsList = ref([]); // [{ key, value }]

const PRESET_SPECS = [
    'Marque', 'Matière', 'Poids', 'Dimensions', 'Origine', 'Garantie',
    'Référence', 'Capacité', 'Puissance', 'Tension', 'Modèle', 'Couleur principale',
];

const addSpec = () => {
    if (!newSpecKey.value.trim() || !newSpecVal.value.trim()) return;
    // Update or add
    const idx = specsList.value.findIndex(s => s.key === newSpecKey.value.trim());
    if (idx >= 0) specsList.value[idx].value = newSpecVal.value.trim();
    else specsList.value.push({ key: newSpecKey.value.trim(), value: newSpecVal.value.trim() });
    newSpecKey.value = '';
    newSpecVal.value = '';
};

const removeSpec = (i) => specsList.value.splice(i, 1);

const pickSpecKey = (k) => { newSpecKey.value = k; };

// ─── Submit ───────────────────────────────────────────────────────────────────
const submit = () => {
    // If the vendor entered values in the input row but forgot to click "+ Ajouter"
    if (newVariation.value.color || newVariation.value.size) {
        addVariation();
    }
    if (newSpecKey.value.trim() && newSpecVal.value.trim()) {
        addSpec();
    }

    // Build specifications object from list
    form.specifications = Object.fromEntries(specsList.value.map(s => [s.key, s.value]));
    form.post(route('vendor.products.store'), { forceFormData: true });
};

// Tab validation hints
const tabHasErrors = computed(() => ({
    general:    !!(form.errors.name || form.errors.description || form.errors.price || form.errors.category_id || form.errors.stock),
    variations: !!(form.errors.variations),
    specs:      !!(form.errors.specifications),
    images:     !!(form.errors.images),
}));
</script>

<template>
    <Head title="Créer un Produit" />
    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                ➕ Créer un nouveau produit
            </h2>
        </template>

        <div class="py-10">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <!-- Global error banner -->
                <div v-if="Object.keys(form.errors).length > 0"
                     class="mb-6 bg-red-50 dark:bg-red-900/40 border-l-4 border-red-500 p-4 rounded-xl">
                    <p class="text-sm font-semibold text-red-700 dark:text-red-300 mb-1">
                        {{ Object.keys(form.errors).length }} erreur(s) dans le formulaire
                    </p>
                    <ul class="list-disc pl-5 text-xs text-red-600 dark:text-red-400 space-y-0.5">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- ─── Tab Bar ─────────────────────────────────────── -->
                    <div class="flex gap-1 mb-6 bg-slate-100 dark:bg-slate-800 p-1 rounded-2xl">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            type="button"
                            @click="activeTab = tab.key"
                            class="relative flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-sm font-semibold transition"
                            :class="activeTab === tab.key
                                ? 'bg-white dark:bg-slate-700 text-sky-600 dark:text-sky-400 shadow'
                                : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'"
                        >
                            <span>{{ tab.icon }}</span>
                            <span class="hidden sm:inline">{{ tab.label }}</span>
                            <!-- Error dot -->
                            <span v-if="tabHasErrors[tab.key]"
                                  class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                    </div>

                    <!-- ═══════════════════════════════════════════════════
                         TAB 1 — GÉNÉRAL
                    ════════════════════════════════════════════════════ -->
                    <div v-show="activeTab === 'general'"
                         class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6 space-y-6">

                        <div>
                            <InputLabel for="name" value="Nom du produit *" />
                            <TextInput id="name" type="text" class="mt-1 block w-full"
                                       v-model="form.name" required autofocus />
                            <InputError class="mt-1" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Description *" />
                            <textarea id="description" v-model="form.description" rows="5" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900
                                       dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500
                                       rounded-md shadow-sm text-sm">
                            </textarea>
                            <InputError class="mt-1" :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="price" value="Prix (DT) *" />
                                <TextInput id="price" type="number" step="0.01" class="mt-1 block w-full"
                                           v-model="form.price" required />
                                <InputError class="mt-1" :message="form.errors.price" />
                            </div>
                            <div>
                                <InputLabel for="discount_price" value="Prix réduit (optionnel)" />
                                <TextInput id="discount_price" type="number" step="0.01" class="mt-1 block w-full"
                                           v-model="form.discount_price" placeholder="Laisser vide si aucune promotion" />
                                <InputError class="mt-1" :message="form.errors.discount_price" />
                            </div>
                            <div>
                                <InputLabel for="stock" value="Stock *" />
                                <TextInput id="stock" type="number" class="mt-1 block w-full"
                                           v-model="form.stock" required />
                                <InputError class="mt-1" :message="form.errors.stock" />
                            </div>
                            <div>
                                <InputLabel for="category_id" value="Catégorie *" />
                                <select id="category_id" v-model="form.category_id" required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900
                                           dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500
                                           rounded-md shadow-sm text-sm">
                                    <option value="">Sélectionner une catégorie</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.category_id" />
                            </div>
                            <div>
                                <InputLabel for="sku" value="SKU (optionnel)" />
                                <TextInput id="sku" type="text" class="mt-1 block w-full"
                                           v-model="form.sku" placeholder="Référence interne" />
                                <InputError class="mt-1" :message="form.errors.sku" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="is_featured" v-model="form.is_featured" type="checkbox"
                                   class="h-4 w-4 rounded border-gray-300 text-sky-600 focus:ring-sky-500" />
                            <label for="is_featured" class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                ⭐ Marquer comme produit vedette
                            </label>
                        </div>
                    </div>

                    <!-- ═══════════════════════════════════════════════════
                         TAB 2 — TAILLES & COULEURS
                    ════════════════════════════════════════════════════ -->
                    <div v-show="activeTab === 'variations'"
                         class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6 space-y-6">

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Ajoutez les déclinaisons de votre produit (couleur, taille, stock et prix optionnel par variante).
                        </p>

                        <!-- Color palette -->
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">Couleur rapide</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="c in PRESET_COLORS" :key="c.hex"
                                    type="button"
                                    @click="pickColor(c)"
                                    :title="c.label"
                                    class="w-8 h-8 rounded-full border-2 transition hover:scale-110"
                                    :style="{ backgroundColor: c.hex }"
                                    :class="newVariation.colorHex === c.hex
                                        ? 'border-sky-500 ring-2 ring-sky-300 scale-110'
                                        : 'border-white dark:border-slate-600 shadow'"
                                ></button>
                            </div>
                        </div>

                        <!-- Size presets -->
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">Taille rapide</p>
                            <div class="flex flex-wrap gap-1.5">
                                <button
                                    v-for="s in PRESET_SIZES" :key="s"
                                    type="button"
                                    @click="pickSize(s)"
                                    class="px-3 py-1 rounded-lg text-xs font-semibold border transition"
                                    :class="newVariation.size === s
                                        ? 'bg-sky-500 text-white border-sky-500'
                                        : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:bg-sky-50'"
                                >{{ s }}</button>
                            </div>
                        </div>

                        <!-- Manual input row -->
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 items-end">
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Couleur</label>
                                <input type="text" v-model="newVariation.color" placeholder="ex: Bleu Marine"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300
                                           focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Taille</label>
                                <input type="text" v-model="newVariation.size" placeholder="ex: XL"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300
                                           focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Stock</label>
                                <input type="number" v-model="newVariation.stock" placeholder="0" min="0"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300
                                           focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Prix (opt.)</label>
                                <input type="number" v-model="newVariation.price" placeholder="—" step="0.01" min="0"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300
                                           focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                            </div>
                            <div class="sm:col-span-1">
                                <button type="button" @click="addVariation"
                                    class="w-full h-9 bg-sky-500 hover:bg-sky-600 text-white font-bold text-sm rounded-lg transition flex items-center justify-center gap-1">
                                    ＋ Ajouter
                                </button>
                            </div>
                        </div>

                        <!-- Variations table -->
                        <div v-if="form.variations.length > 0" class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Couleur</th>
                                        <th class="px-4 py-3 text-left">Taille</th>
                                        <th class="px-4 py-3 text-left">Stock</th>
                                        <th class="px-4 py-3 text-left">Prix</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="(v, i) in form.variations" :key="i"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                        <td class="px-4 py-2.5">
                                            <div class="flex items-center gap-2">
                                                <span v-if="v.colorHex"
                                                      class="inline-block w-4 h-4 rounded-full border border-slate-300 shadow-sm flex-shrink-0"
                                                      :style="{ backgroundColor: v.colorHex }">
                                                </span>
                                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ v.color || '—' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">{{ v.size || '—' }}</td>
                                        <td class="px-4 py-2.5">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                                  :class="v.stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                                                {{ v.stock || 0 }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">
                                            {{ v.price ? v.price + ' DT' : 'Prix produit' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            <button type="button" @click="removeVariation(i)"
                                                class="text-red-400 hover:text-red-600 transition p-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p v-else class="text-center py-6 text-slate-400 text-sm border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                            Aucune variante ajoutée — le stock global du produit sera utilisé.
                        </p>
                    </div>

                    <!-- ═══════════════════════════════════════════════════
                         TAB 3 — FICHE TECHNIQUE
                    ════════════════════════════════════════════════════ -->
                    <div v-show="activeTab === 'specs'"
                         class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6 space-y-6">

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Ajoutez les caractéristiques techniques de votre produit. Elles seront affichées dans l'onglet <strong>Fiche Technique</strong> de la page produit.
                        </p>

                        <!-- Preset keys -->
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">Caractéristiques rapides</p>
                            <div class="flex flex-wrap gap-1.5">
                                <button
                                    v-for="k in PRESET_SPECS" :key="k"
                                    type="button"
                                    @click="pickSpecKey(k)"
                                    class="px-3 py-1 rounded-lg text-xs font-semibold border transition"
                                    :class="newSpecKey === k
                                        ? 'bg-indigo-500 text-white border-indigo-500'
                                        : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:bg-indigo-50'"
                                >{{ k }}</button>
                            </div>
                        </div>

                        <!-- Input row -->
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Caractéristique</label>
                                <input type="text" v-model="newSpecKey" placeholder="ex: Matière"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300
                                           focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Valeur</label>
                                <input type="text" v-model="newSpecVal" placeholder="ex: 100% Coton"
                                    @keydown.enter.prevent="addSpec"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300
                                           focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                            </div>
                            <button type="button" @click="addSpec"
                                class="h-9 px-4 bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-sm rounded-lg transition flex-shrink-0">
                                ＋ Ajouter
                            </button>
                        </div>

                        <!-- Specs table -->
                        <div v-if="specsList.length > 0" class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-4 py-3 text-left w-1/2">Caractéristique</th>
                                        <th class="px-4 py-3 text-left">Valeur</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="(spec, i) in specsList" :key="i"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                        <td class="px-4 py-2.5 font-semibold text-slate-700 dark:text-slate-300">{{ spec.key }}</td>
                                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">{{ spec.value }}</td>
                                        <td class="px-4 py-2.5 text-right">
                                            <button type="button" @click="removeSpec(i)"
                                                class="text-red-400 hover:text-red-600 transition p-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Preview -->
                        <div v-if="specsList.length > 0" class="mt-4">
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-3">Aperçu Fiche Technique</p>
                            <div class="max-w-lg divide-y divide-slate-100 dark:divide-slate-700/80 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                <div v-for="spec in specsList" :key="spec.key" class="grid grid-cols-2 py-3 px-4 text-sm bg-white dark:bg-slate-800">
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ spec.key }}</span>
                                    <span class="text-slate-600 dark:text-slate-400">{{ spec.value }}</span>
                                </div>
                            </div>
                        </div>

                        <p v-else class="text-center py-6 text-slate-400 text-sm border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                            Aucune caractéristique ajoutée — l'onglet Fiche Technique sera masqué sur la page produit.
                        </p>
                    </div>

                    <!-- ═══════════════════════════════════════════════════
                         TAB 4 — PHOTOS
                    ════════════════════════════════════════════════════ -->
                    <div v-show="activeTab === 'images'"
                         class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6 space-y-4">

                        <div>
                            <InputLabel for="images" value="Photos du produit (plusieurs autorisées)" />
                            <input id="images" type="file" accept="image/*" multiple @change="handleImageChange"
                                class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400
                                       file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0
                                       file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700
                                       hover:file:bg-sky-100 dark:file:bg-sky-900 dark:file:text-sky-200" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                PNG, JPG ou GIF — max 10 Mo chacune. La première image sera l'image principale.
                            </p>
                            <InputError class="mt-2" :message="form.errors.images" />
                        </div>

                        <div v-if="imagePreviews.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="(preview, index) in imagePreviews" :key="index" class="relative group">
                                <img :src="preview" class="w-full h-32 object-cover rounded-xl border-2 border-slate-200 dark:border-slate-600" />
                                <button type="button" @click="removeImage(index)"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <span v-if="index === 0"
                                      class="absolute bottom-1 left-1 bg-sky-500 text-white text-xs font-bold px-2 py-0.5 rounded-lg shadow">
                                    Principale
                                </span>
                            </div>
                        </div>

                        <div v-else
                             class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl py-14 text-slate-400">
                            <svg class="w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-sm">Aucune photo sélectionnée</p>
                        </div>
                    </div>

                    <!-- ─── Submit Bar ───────────────────────────────────── -->
                    <div class="mt-6 flex items-center justify-between gap-4 bg-white dark:bg-gray-800 shadow-sm rounded-2xl px-6 py-4">
                        <a :href="route('vendor.products')"
                           class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition font-medium">
                            ← Annuler
                        </a>
                        <PrimaryButton :disabled="form.processing" class="px-8">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            {{ form.processing ? 'Création...' : '✅ Créer le produit' }}
                        </PrimaryButton>
                    </div>
                </form>

            </div>
        </div>
    </DashboardLayout>
</template>
