<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    product: Object,
    categories: Array,
});

const form = useForm({
    name: props.product.name,
    description: props.product.description,
    price: props.product.price,
    discount_price: props.product.discount_price || '',
    category_id: props.product.category_id,
    stock: props.product.stock,
    sku: props.product.sku || '',
    status: props.product.status,
    images: [],
    delete_images: [],
    is_featured: props.product.is_featured || false,
});

const existingImages = ref(props.product.images || []);
const newImagePreviews = ref([]);

const handleImageChange = (event) => {
    const files = Array.from(event.target.files);
    form.images = files;
    
    // Create previews for new images
    newImagePreviews.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            newImagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeNewImage = (index) => {
    const filesArray = Array.from(form.images);
    filesArray.splice(index, 1);
    form.images = filesArray;
    newImagePreviews.value.splice(index, 1);
};

const removeExistingImage = (imageId) => {
    form.delete_images.push(imageId);
    existingImages.value = existingImages.value.filter(img => img.id !== imageId);
};

const submit = () => {
    form.post(route('vendor.products.update', props.product.id), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const showShareModal = ref(false);
const shareData = ref({
    title: '',
    text: '',
    url: '',
    copied: false,
});

const getShareUrl = (path) => {
    const origin = (window.location.origin.includes('localhost') || window.location.origin.includes('127.0.0.1'))
        ? 'https://aquawasouk.com'
        : window.location.origin;
    return origin + path;
};

const copyToClipboard = async (text) => {
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
        } else {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
        shareData.value.copied = true;
        setTimeout(() => {
            shareData.value.copied = false;
        }, 3500);
    } catch (e) {
        console.error('Clipboard copy error', e);
    }
};

const shareProductOnFacebook = () => {
    const productUrl = getShareUrl('/products/' + props.product.slug);
    const priceText = (props.product.discount_price && props.product.discount_price > 0)
        ? `${props.product.discount_price} DT (Offre Spéciale)`
        : `${props.product.price} DT`;

    const cleanDescription = props.product.description 
        ? props.product.description.replace(/<[^>]*>?/gm, '').trim().substring(0, 160) + '...'
        : '';

    const shareText = `🔥 ${props.product.name}\n\n` +
        (cleanDescription ? `📝 ${cleanDescription}\n\n` : '') +
        `💰 Prix : ${priceText}\n` +
        `🚚 Livraison express partout en Tunisie\n` +
        `💵 Paiement en espèces à la livraison (COD)\n\n` +
        `👉 Pour commander directement :\n${productUrl}`;

    shareData.value = {
        title: props.product.name,
        text: shareText,
        url: productUrl,
        copied: false,
    };

    copyToClipboard(shareText);
    showShareModal.value = true;
};

const openFacebookSharer = () => {
    const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareData.value.url)}`;
    window.open(fbUrl, '_blank', 'width=620,height=600,scrollbars=yes,resizable=yes');
};

const openWhatsAppSharer = () => {
    const waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareData.value.text)}`;
    window.open(waUrl, '_blank');
};
</script>

<template>
    <Head title="Edit Product" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Modifier le Produit
                </h2>
                <div class="flex items-center gap-2.5">
                    <a :href="'/products/' + product.slug"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl shadow-xs transition">
                        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Voir la fiche</span>
                    </a>
                    <button @click="shareProductOnFacebook"
                            type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition transform active:scale-95">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Partager sur Facebook</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Global Error Message -->
                            <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 dark:bg-red-900/50 border-l-4 border-red-500 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                            There were {{ Object.keys(form.errors).length }} errors with your submission
                                        </h3>
                                        <div class="mt-2 text-sm text-red-700 dark:text-red-200">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li v-for="(error, key) in form.errors" :key="key">
                                                    {{ error }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Details -->

                            <div>
                                <InputLabel for="name" value="Product Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="description" value="Description" />
                                <textarea
                                    id="description"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.description"
                                    rows="4"
                                    required
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="price" value="Price" />
                                    <TextInput
                                        id="price"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.price"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.price" />
                                </div>

                                <div>
                                    <InputLabel for="discount_price" value="Discount Price (Optional)" />
                                    <TextInput
                                        id="discount_price"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.discount_price"
                                        placeholder="Leave empty if no discount"
                                    />
                                    <InputError class="mt-2" :message="form.errors.discount_price" />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">If set, this will be displayed as the sale price</p>
                                </div>

                                <div>
                                    <InputLabel for="stock" value="Stock Quantity" />
                                    <TextInput
                                        id="stock"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.stock"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.stock" />
                                </div>

                                <div>
                                    <InputLabel for="category_id" value="Category" />
                                    <select
                                        id="category_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        v-model="form.category_id"
                                        required
                                    >
                                        <option value="">Select Category</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.category_id" />
                                </div>

                                <div>
                                    <InputLabel for="sku" value="SKU (Optional)" />
                                    <TextInput
                                        id="sku"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.sku"
                                    />
                                    <InputError class="mt-2" :message="form.errors.sku" />
                                </div>

                                <div>
                                    <InputLabel for="status" value="Status" />
                                    <select
                                        id="status"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        v-model="form.status"
                                        required
                                    >
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.status" />
                                </div>
                            </div>
                             <!-- Is Featured -->
                            <div class="flex items-center">
                                <input
                                    id="is_featured"
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label for="is_featured" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Mark as Featured Product
                                </label>
                            </div>

                            <!-- Product Images -->
                            <div class="mt-6">
                                <InputLabel for="images" value="Product Images" />
                                
                                <!-- Existing Images -->
                                <div v-if="existingImages.length > 0" class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Existing Images</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div v-for="image in existingImages" :key="image.id" class="relative group">
                                            <img :src="'/uploads/' + image.image_path" class="w-full h-32 object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600" />
                                            <button
                                                type="button"
                                                @click="removeExistingImage(image.id)"
                                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                            <span v-if="image.is_primary" class="absolute bottom-1 left-1 bg-blue-500 text-white text-xs px-2 py-1 rounded">Primary</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Add New Images -->
                                <div class="mt-4">
                                    <input
                                        id="images"
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        @change="handleImageChange"
                                        class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-md file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-blue-50 file:text-blue-700
                                            hover:file:bg-blue-100
                                            dark:file:bg-blue-900 dark:file:text-blue-200
                                            dark:hover:file:bg-blue-800"
                                    />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add more images (PNG, JPG, GIF up to 10MB each)</p>
                                    <InputError class="mt-2" :message="form.errors.images" />
                                </div>
                                
                                <!-- New Images Preview -->
                                <div v-if="newImagePreviews.length > 0" class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Images to Upload</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div v-for="(preview, index) in newImagePreviews" :key="index" class="relative group">
                                            <img :src="preview" class="w-full h-32 object-cover rounded-lg border-2 border-green-300 dark:border-green-600" />
                                            <button
                                                type="button"
                                                @click="removeNewImage(index)"
                                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                            <span class="absolute bottom-1 left-1 bg-green-500 text-white text-xs px-2 py-1 rounded">New</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 mt-6">
                                <PrimaryButton :disabled="form.processing">Update Product</PrimaryButton>

                                <a :href="route('vendor.products')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                    Cancel
                                </a>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Social Share Modal -->
        <Modal :show="showShareModal" @close="showShareModal = false" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-[#1877F2]/10 flex items-center justify-center text-[#1877F2]">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Partager sur les réseaux</h3>
                    </div>
                    <button @click="showShareModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Automatic copy notice -->
                <div class="mt-4 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <div class="text-xs text-emerald-800 dark:text-emerald-200">
                        <p class="font-bold">Texte promotionnel copié automatiquement !</p>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-300 mt-0.5">
                            Sur Facebook, faites simplement <strong>Ctrl + V</strong> (ou clic droit &rarr; <em>Coller</em>) dans votre statut pour afficher le descriptif complet.
                        </p>
                    </div>
                </div>

                <!-- Preview Textarea -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Texte généré avec lien produit :
                        </label>
                        <span v-if="shareData.copied" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                            ✓ Copié dans le presse-papier !
                        </span>
                    </div>
                    <div class="relative">
                        <textarea
                            v-model="shareData.text"
                            rows="6"
                            class="w-full text-xs font-sans p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-500 leading-relaxed"
                        ></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-5 flex flex-col sm:flex-row items-center gap-2.5">
                    <button
                        @click="copyToClipboard(shareData.text)"
                        type="button"
                        class="w-full sm:w-auto py-2.5 px-3.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>{{ shareData.copied ? 'Copié !' : 'Re-copier le texte' }}</span>
                    </button>

                    <button
                        @click="openFacebookSharer"
                        type="button"
                        class="w-full sm:flex-1 py-2.5 px-4 bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-bold rounded-xl shadow-sm flex items-center justify-center gap-2 transition"
                    >
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Ouvrir Facebook &rarr;</span>
                    </button>

                    <button
                        @click="openWhatsAppSharer"
                        type="button"
                        class="w-full sm:w-auto py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm flex items-center justify-center gap-2 transition"
                    >
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 1.78.829 2.796.831 3.185 0 5.77-2.587 5.77-5.766.001-3.182-2.585-5.788-5.77-5.788zm3.36 8.163c-.144.405-.837.774-1.17.822-.312.045-.694.06-2.002-.482-1.674-.693-2.73-2.39-2.813-2.502-.083-.112-.676-.902-.676-1.72 0-.819.429-1.222.582-1.389.153-.167.334-.209.445-.209.112 0 .223.001.32.005.102.005.239-.039.374.286.143.344.489 1.192.531 1.28.043.088.072.19.014.305-.058.115-.088.187-.174.288-.088.102-.185.228-.264.306-.089.088-.182.184-.078.362.104.178.463.764.994 1.237.684.61 1.261.799 1.439.888.178.089.282.074.386-.045.105-.118.446-.519.565-.697.119-.178.238-.149.398-.089.16.059 1.018.48 1.192.568.174.088.29.132.334.208.044.075.044.436-.1.841z"/>
                        </svg>
                        <span>WhatsApp</span>
                    </button>
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>
