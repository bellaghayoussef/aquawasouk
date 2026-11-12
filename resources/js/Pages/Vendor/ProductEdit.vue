<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    product: Object,
    categories: Array,
});

// ─── Active Tab ───────────────────────────────────────────────────────────────
const activeTab = ref('general');
const tabs = [
    { key: 'general',    label: 'Général',           icon: '📦' },
    { key: 'variations', label: 'Tailles & Couleurs', icon: '🎨' },
    { key: 'specs',      label: 'Fiche Technique',    icon: '📋' },
    { key: 'images',     label: 'Photos',             icon: '🖼️' },
];

// ─── Main Form ────────────────────────────────────────────────────────────────
const form = useForm({
    name:           props.product.name,
    description:    props.product.description,
    price:          props.product.price,
    discount_price: props.product.discount_price || '',
    category_id:    props.product.category_id,
    stock:          props.product.stock,
    sku:            props.product.sku || '',
    status:         props.product.status,
    images:         [],
    delete_images:  [],
    is_featured:    props.product.is_featured || false,
    specifications: {},
    variations:     [],
    delete_variations: [],
});

// ─── Images ───────────────────────────────────────────────────────────────────
const existingImages   = ref(props.product.images || []);
const newImagePreviews = ref([]);

const handleImageChange = (event) => {
    const files = Array.from(event.target.files);
    form.images = files;
    newImagePreviews.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => newImagePreviews.value.push(e.target.result);
        reader.readAsDataURL(file);
    });
};
const removeNewImage = (index) => {
    const arr = Array.from(form.images);
    arr.splice(index, 1);
    form.images = arr;
    newImagePreviews.value.splice(index, 1);
};
const removeExistingImage = (imageId) => {
    form.delete_images.push(imageId);
    existingImages.value = existingImages.value.filter(img => img.id !== imageId);
};

// ─── Variations ───────────────────────────────────────────────────────────────
const PRESET_COLORS = [
    { label: 'Noir',   hex: '#111827' }, { label: 'Blanc',  hex: '#F9FAFB' },
    { label: 'Rouge',  hex: '#EF4444' }, { label: 'Bleu',   hex: '#3B82F6' },
    { label: 'Vert',   hex: '#22C55E' }, { label: 'Jaune',  hex: '#EAB308' },
    { label: 'Orange', hex: '#F97316' }, { label: 'Rose',   hex: '#EC4899' },
    { label: 'Violet', hex: '#A855F7' }, { label: 'Gris',   hex: '#6B7280' },
    { label: 'Marron', hex: '#92400E' }, { label: 'Beige',  hex: '#D4B896' },
];
const PRESET_SIZES = ['XS','S','M','L','XL','XXL','XXXL',
                      '36','37','38','39','40','41','42','43','44','45','Unique'];

// Existing variations (from DB), editable in-place
const existingVariations = ref(
    (props.product.variations || []).map(v => ({ ...v, _dirty: false }))
);

const newVariation = ref({ color: '', colorHex: '', size: '', stock: '', price: '' });

const addVariation = () => {
    if (!newVariation.value.color && !newVariation.value.size) return;
    form.variations.push({ ...newVariation.value });
    newVariation.value = { color: '', colorHex: '', size: '', stock: '', price: '' };
};
const removeNewVariation  = (i) => form.variations.splice(i, 1);
const removeExistingVar   = (v) => {
    form.delete_variations.push(v.id);
    existingVariations.value = existingVariations.value.filter(x => x.id !== v.id);
};
const pickColor = (c) => { newVariation.value.color = c.label; newVariation.value.colorHex = c.hex; };
const pickSize  = (s) => { newVariation.value.size  = s; };

// ─── Specifications ───────────────────────────────────────────────────────────
const PRESET_SPECS = [
    'Marque','Matière','Poids','Dimensions','Origine','Garantie',
    'Référence','Capacité','Puissance','Tension','Modèle','Couleur principale',
];

// Pre-load existing specs
const rawSpecs = props.product.specifications;
const initSpecs = rawSpecs
    ? (typeof rawSpecs === 'object' ? rawSpecs : (() => { try { return JSON.parse(rawSpecs); } catch { return {}; } })())
    : {};

const specsList   = ref(Object.entries(initSpecs).map(([key, value]) => ({ key, value })));
const newSpecKey  = ref('');
const newSpecVal  = ref('');

const addSpec = () => {
    if (!newSpecKey.value.trim() || !newSpecVal.value.trim()) return;
    const idx = specsList.value.findIndex(s => s.key === newSpecKey.value.trim());
    if (idx >= 0) specsList.value[idx].value = newSpecVal.value.trim();
    else specsList.value.push({ key: newSpecKey.value.trim(), value: newSpecVal.value.trim() });
    newSpecKey.value = '';
    newSpecVal.value = '';
};
const removeSpec  = (i) => specsList.value.splice(i, 1);
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

    form.specifications = Object.fromEntries(specsList.value.map(s => [s.key, s.value]));
    form.post(route('vendor.products.update', props.product.id), {
        forceFormData: true,
        preserveScroll: true,
    });
};

// Tab error hints
const tabHasErrors = computed(() => ({
    general:    !!(form.errors.name || form.errors.description || form.errors.price || form.errors.category_id || form.errors.stock || form.errors.status),
    variations: !!(form.errors.variations),
    specs:      !!(form.errors.specifications),
    images:     !!(form.errors.images),
}));

// ─── Facebook Share ───────────────────────────────────────────────────────────
const showShareModal = ref(false);
const shareData = ref({ title: '', text: '', url: '', copied: false });

const getShareUrl = (path) => {
    const origin = (window.location.origin.includes('localhost') || window.location.origin.includes('127.0.0.1'))
        ? 'https://aquawasouk.com' : window.location.origin;
    return origin + path;
};
const copyToClipboard = async (text) => {
    try {
        if (navigator.clipboard && window.isSecureContext) await navigator.clipboard.writeText(text);
        else {
            const ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.left = '-999999px';
            document.body.appendChild(ta); ta.select(); document.execCommand('copy');
            document.body.removeChild(ta);
        }
        shareData.value.copied = true;
        setTimeout(() => { shareData.value.copied = false; }, 3500);
    } catch (e) { console.error(e); }
};
const shareProductOnFacebook = () => {
    const productUrl = getShareUrl('/products/' + props.product.slug);
    const priceText  = (props.product.discount_price && props.product.discount_price > 0)
        ? `${props.product.discount_price} DT (Offre Spéciale)` : `${props.product.price} DT`;
    const cleanDesc  = props.product.description
        ? props.product.description.replace(/<[^>]*>?/gm, '').trim().substring(0, 160) + '...' : '';
    const shareText  = `🔥 ${props.product.name}\n\n`
        + (cleanDesc ? `📝 ${cleanDesc}\n\n` : '')
        + `💰 Prix : ${priceText}\n🚚 Livraison express partout en Tunisie\n`
        + `💵 Paiement en espèces à la livraison (COD)\n\n👉 Pour commander :\n${productUrl}`;
    shareData.value = { title: props.product.name, text: shareText, url: productUrl, copied: false };
    copyToClipboard(shareText);
    showShareModal.value = true;
};
const openFacebookSharer = () => window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareData.value.url)}`, '_blank', 'width=620,height=600');
const openWhatsAppSharer = () => window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(shareData.value.text)}`, '_blank');
</script>

<template>
    <Head title="Modifier le Produit" />
    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    ✏️ Modifier le Produit
                </h2>
                <div class="flex items-center gap-2">
                    <a :href="'/products/' + product.slug" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Voir la fiche
                    </a>
                    <button @click="shareProductOnFacebook" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-bold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Partager
                    </button>
                </div>
            </div>
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

                    <!-- ─── Tab Bar ──────────────────────────────────────────── -->
                    <div class="flex gap-1 mb-6 bg-slate-100 dark:bg-slate-800 p-1 rounded-2xl">
                        <button v-for="tab in tabs" :key="tab.key" type="button"
                                @click="activeTab = tab.key"
                                class="relative flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-sm font-semibold transition"
                                :class="activeTab === tab.key
                                    ? 'bg-white dark:bg-slate-700 text-sky-600 dark:text-sky-400 shadow'
                                    : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'">
                            <span>{{ tab.icon }}</span>
                            <span class="hidden sm:inline">{{ tab.label }}</span>
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
                                       rounded-md shadow-sm text-sm"></textarea>
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
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.category_id" />
                            </div>
                            <div>
                                <InputLabel for="sku" value="SKU (optionnel)" />
                                <TextInput id="sku" type="text" class="mt-1 block w-full"
                                           v-model="form.sku" placeholder="Référence interne" />
                                <InputError class="mt-1" :message="form.errors.sku" />
                            </div>
                            <div>
                                <InputLabel for="status" value="Statut *" />
                                <select id="status" v-model="form.status" required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900
                                           dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500
                                           rounded-md shadow-sm text-sm">
                                    <option value="active">✅ Actif</option>
                                    <option value="inactive">⏸️ Inactif</option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.status" />
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

                        <!-- Existing variations -->
                        <div v-if="existingVariations.length > 0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-3">Variantes existantes</p>
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        <tr>
                                            <th class="px-4 py-3 text-left">Couleur</th>
                                            <th class="px-4 py-3 text-left">Taille</th>
                                            <th class="px-4 py-3 text-left">Stock</th>
                                            <th class="px-4 py-3 text-left">Prix</th>
                                            <th class="px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                        <tr v-for="v in existingVariations" :key="v.id"
                                            class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                            <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-slate-200">{{ v.color || '—' }}</td>
                                            <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">{{ v.size  || '—' }}</td>
                                            <td class="px-4 py-2.5">
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                                      :class="v.stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                                                    {{ v.stock }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">
                                                {{ v.price ? v.price + ' DT' : 'Prix produit' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-right">
                                                <button type="button" @click="removeExistingVar(v)"
                                                    class="text-red-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Add new variations -->
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-3">Ajouter une variante</p>

                            <!-- Color presets -->
                            <div class="mb-3">
                                <p class="text-xs text-slate-400 mb-2">Couleur rapide</p>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="c in PRESET_COLORS" :key="c.hex" type="button"
                                            @click="pickColor(c)" :title="c.label"
                                            class="w-8 h-8 rounded-full border-2 transition hover:scale-110"
                                            :style="{ backgroundColor: c.hex }"
                                            :class="newVariation.colorHex === c.hex
                                                ? 'border-sky-500 ring-2 ring-sky-300 scale-110'
                                                : 'border-white dark:border-slate-600 shadow'">
                                    </button>
                                </div>
                            </div>

                            <!-- Size presets -->
                            <div class="mb-4">
                                <p class="text-xs text-slate-400 mb-2">Taille rapide</p>
                                <div class="flex flex-wrap gap-1.5">
                                    <button v-for="s in PRESET_SIZES" :key="s" type="button"
                                            @click="pickSize(s)"
                                            class="px-3 py-1 rounded-lg text-xs font-semibold border transition"
                                            :class="newVariation.size === s
                                                ? 'bg-sky-500 text-white border-sky-500'
                                                : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:bg-sky-50'">
                                        {{ s }}
                                    </button>
                                </div>
                            </div>

                            <!-- Manual row -->
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 items-end">
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Couleur</label>
                                    <input type="text" v-model="newVariation.color" placeholder="ex: Bleu Marine"
                                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Taille</label>
                                    <input type="text" v-model="newVariation.size" placeholder="ex: XL"
                                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Stock</label>
                                    <input type="number" v-model="newVariation.stock" placeholder="0" min="0"
                                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Prix (opt.)</label>
                                    <input type="number" v-model="newVariation.price" placeholder="—" step="0.01" min="0"
                                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                                </div>
                                <div class="sm:col-span-1">
                                    <button type="button" @click="addVariation"
                                        class="w-full h-9 bg-sky-500 hover:bg-sky-600 text-white font-bold text-sm rounded-lg transition flex items-center justify-center gap-1">
                                        ＋ Ajouter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- New variations preview -->
                        <div v-if="form.variations.length > 0" class="overflow-x-auto rounded-xl border border-sky-200 dark:border-sky-800">
                            <p class="text-xs font-semibold text-sky-600 dark:text-sky-400 px-4 pt-3 pb-1">Nouvelles variantes à ajouter</p>
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="(v, i) in form.variations" :key="i"
                                        class="hover:bg-sky-50 dark:hover:bg-sky-900/20">
                                        <td class="px-4 py-2 font-medium text-slate-800 dark:text-slate-200">{{ v.color || '—' }}</td>
                                        <td class="px-4 py-2 text-slate-700 dark:text-slate-300">{{ v.size  || '—' }}</td>
                                        <td class="px-4 py-2 text-slate-700">{{ v.stock || 0 }}</td>
                                        <td class="px-4 py-2 text-slate-700">{{ v.price ? v.price + ' DT' : '—' }}</td>
                                        <td class="px-4 py-2 text-right">
                                            <button type="button" @click="removeNewVariation(i)"
                                                class="text-red-400 hover:text-red-600 p-1 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p v-if="existingVariations.length === 0 && form.variations.length === 0"
                           class="text-center py-6 text-slate-400 text-sm border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                            Aucune variante — le stock global du produit sera utilisé.
                        </p>
                    </div>

                    <!-- ═══════════════════════════════════════════════════
                         TAB 3 — FICHE TECHNIQUE
                    ════════════════════════════════════════════════════ -->
                    <div v-show="activeTab === 'specs'"
                         class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6 space-y-6">

                        <!-- Preset keys -->
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">Caractéristiques rapides</p>
                            <div class="flex flex-wrap gap-1.5">
                                <button v-for="k in PRESET_SPECS" :key="k" type="button"
                                        @click="pickSpecKey(k)"
                                        class="px-3 py-1 rounded-lg text-xs font-semibold border transition"
                                        :class="newSpecKey === k
                                            ? 'bg-indigo-500 text-white border-indigo-500'
                                            : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:bg-indigo-50'">
                                    {{ k }}
                                </button>
                            </div>
                        </div>

                        <!-- Input row -->
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Caractéristique</label>
                                <input type="text" v-model="newSpecKey" placeholder="ex: Matière"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Valeur</label>
                                <input type="text" v-model="newSpecVal" placeholder="ex: 100% Coton"
                                    @keydown.enter.prevent="addSpec"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm h-9 px-3" />
                            </div>
                            <button type="button" @click="addSpec"
                                class="h-9 px-4 bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-sm rounded-lg flex-shrink-0 transition">
                                ＋ Ajouter
                            </button>
                        </div>

                        <!-- Specs table -->
                        <div v-if="specsList.length > 0" class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-4 py-3 text-left w-1/2">Caractéristique</th>
                                        <th class="px-4 py-3 text-left">Valeur</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="(spec, i) in specsList" :key="i"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                        <td class="px-4 py-2.5 font-semibold text-slate-700 dark:text-slate-300">
                                            <input type="text" v-model="spec.key"
                                                class="w-full bg-transparent border-0 p-0 text-sm font-semibold focus:ring-0 text-slate-700 dark:text-slate-300" />
                                        </td>
                                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">
                                            <input type="text" v-model="spec.value"
                                                class="w-full bg-transparent border-0 p-0 text-sm focus:ring-0 text-slate-600 dark:text-slate-400" />
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            <button type="button" @click="removeSpec(i)"
                                                class="text-red-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Live preview -->
                        <div v-if="specsList.length > 0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-3">Aperçu Fiche Technique</p>
                            <div class="max-w-lg divide-y divide-slate-100 dark:divide-slate-700/80 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                <div v-for="spec in specsList" :key="spec.key" class="grid grid-cols-2 py-3 px-4 text-sm bg-white dark:bg-slate-800">
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ spec.key }}</span>
                                    <span class="text-slate-600 dark:text-slate-400">{{ spec.value }}</span>
                                </div>
                            </div>
                        </div>

                        <p v-else class="text-center py-6 text-slate-400 text-sm border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                            Aucune caractéristique — l'onglet Fiche Technique sera masqué sur la page produit.
                        </p>
                    </div>

                    <!-- ═══════════════════════════════════════════════════
                         TAB 4 — PHOTOS
                    ════════════════════════════════════════════════════ -->
                    <div v-show="activeTab === 'images'"
                         class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6 space-y-6">

                        <!-- Existing images -->
                        <div v-if="existingImages.length > 0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-3">Photos actuelles</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div v-for="image in existingImages" :key="image.id" class="relative group">
                                    <img :src="image.image_path?.startsWith('http') ? image.image_path : '/uploads/' + image.image_path"
                                         class="w-full h-32 object-cover rounded-xl border-2 border-slate-200 dark:border-slate-600" />
                                    <button type="button" @click="removeExistingImage(image.id)"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <span v-if="image.is_primary"
                                          class="absolute bottom-1 left-1 bg-sky-500 text-white text-xs font-bold px-2 py-0.5 rounded-lg shadow">
                                        Principale
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Upload new -->
                        <div>
                            <InputLabel for="images" value="Ajouter de nouvelles photos" />
                            <input id="images" type="file" accept="image/*" multiple @change="handleImageChange"
                                class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400
                                       file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0
                                       file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700
                                       hover:file:bg-sky-100 dark:file:bg-sky-900 dark:file:text-sky-200" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG ou GIF — max 10 Mo chacune.</p>
                            <InputError class="mt-2" :message="form.errors.images" />
                        </div>

                        <!-- New previews -->
                        <div v-if="newImagePreviews.length > 0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-3">Nouvelles photos à uploader</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div v-for="(preview, index) in newImagePreviews" :key="index" class="relative group">
                                    <img :src="preview" class="w-full h-32 object-cover rounded-xl border-2 border-green-300 dark:border-green-700" />
                                    <button type="button" @click="removeNewImage(index)"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <span class="absolute bottom-1 left-1 bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded-lg shadow">New</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ─── Submit Bar ───────────────────────────────────────── -->
                    <div class="mt-6 flex items-center justify-between gap-4 bg-white dark:bg-gray-800 shadow-sm rounded-2xl px-6 py-4">
                        <a :href="route('vendor.products')"
                           class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition font-medium">
                            ← Annuler
                        </a>
                        <div class="flex items-center gap-3">
                            <Transition enter-active-class="transition ease-in-out"
                                        enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out"
                                        leave-to-class="opacity-0">
                                <p v-if="form.recentlySuccessful" class="text-sm text-emerald-600 dark:text-emerald-400 font-semibold">✅ Sauvegardé !</p>
                            </Transition>
                            <PrimaryButton :disabled="form.processing" class="px-8">
                                <svg v-if="form.processing" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                {{ form.processing ? 'Sauvegarde...' : '💾 Enregistrer les modifications' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ─── Facebook Share Modal ─────────────────────────────────────────── -->
        <Modal :show="showShareModal" @close="showShareModal = false" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-[#1877F2]/10 flex items-center justify-center text-[#1877F2]">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Partager sur les réseaux</h3>
                    </div>
                    <button @click="showShareModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <div class="text-xs text-emerald-800 dark:text-emerald-200">
                        <p class="font-bold">Texte promotionnel copié automatiquement !</p>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-300 mt-0.5">
                            Sur Facebook, faites simplement <strong>Ctrl + V</strong> dans votre statut.
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Texte généré avec lien produit :</label>
                        <span v-if="shareData.copied" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">✓ Copié !</span>
                    </div>
                    <textarea v-model="shareData.text" rows="6"
                        class="w-full text-xs font-sans p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-500 leading-relaxed">
                    </textarea>
                </div>

                <div class="mt-5 flex flex-col sm:flex-row items-center gap-2.5">
                    <button @click="copyToClipboard(shareData.text)" type="button"
                        class="w-full sm:w-auto py-2.5 px-3.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        {{ shareData.copied ? 'Copié !' : 'Re-copier le texte' }}
                    </button>
                    <button @click="openFacebookSharer" type="button"
                        class="w-full sm:flex-1 py-2.5 px-4 bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-bold rounded-xl shadow-sm flex items-center justify-center gap-2 transition">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Ouvrir Facebook &rarr;
                    </button>
                    <button @click="openWhatsAppSharer" type="button"
                        class="w-full sm:w-auto py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm flex items-center justify-center gap-2 transition">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 1.78.829 2.796.831 3.185 0 5.77-2.587 5.77-5.766.001-3.182-2.585-5.788-5.77-5.788zm3.36 8.163c-.144.405-.837.774-1.17.822-.312.045-.694.06-2.002-.482-1.674-.693-2.73-2.39-2.813-2.502-.083-.112-.676-.902-.676-1.72 0-.819.429-1.222.582-1.389.153-.167.334-.209.445-.209.112 0 .223.001.32.005.102.005.239-.039.374.286.143.344.489 1.192.531 1.28.043.088.072.19.014.305-.058.115-.088.187-.174.288-.088.102-.185.228-.264.306-.089.088-.182.184-.078.362.104.178.463.764.994 1.237.684.61 1.261.799 1.439.888.178.089.282.074.386-.045.105-.118.446-.519.565-.697.119-.178.238-.149.398-.089.16.059 1.018.48 1.192.568.174.088.29.132.334.208.044.075.044.436-.1.841z"/></svg>
                        WhatsApp
                    </button>
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>
