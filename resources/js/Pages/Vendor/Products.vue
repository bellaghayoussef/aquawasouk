<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TableFilters from '@/Components/TableFilters.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    products: Object,
    vendor: Object,
    categories: Array,
    filters: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-TN', {
        style: 'currency',
        currency: 'TND',
    }).format(price);
};

const getImageUrl = (path) => {
    if (!path) return '';
    return path.startsWith('http://') || path.startsWith('https://') ? path : '/uploads/' + path;
};

const deleteProduct = (productId) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('vendor.products.destroy', productId), {
            preserveScroll: true,
        });
    }
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

const shareProductOnFacebook = (product) => {
    const productUrl = getShareUrl('/products/' + product.slug);
    const priceText = (product.discount_price && product.discount_price > 0)
        ? `${product.discount_price} DT (Offre Spéciale)`
        : `${product.price} DT`;

    const cleanDescription = product.description 
        ? product.description.replace(/<[^>]*>?/gm, '').trim().substring(0, 160) + '...'
        : '';

    const shareText = `🔥 ${product.name}\n\n` +
        (cleanDescription ? `📝 ${cleanDescription}\n\n` : '') +
        `💰 Prix : ${priceText}\n` +
        `🚚 Livraison express partout en Tunisie\n` +
        `💵 Paiement en espèces à la livraison (COD)\n\n` +
        `👉 Pour commander directement :\n${productUrl}`;

    shareData.value = {
        title: product.name,
        text: shareText,
        url: productUrl,
        copied: false,
    };

    copyToClipboard(shareText);
    showShareModal.value = true;
};

const shareShopOnFacebook = () => {
    const shopSlug = props.vendor?.slug || '';
    const shopUrl = getShareUrl('/vendors/' + shopSlug);
    const shopName = props.vendor?.shop_name || 'Boutique';

    const shareText = `🌟 Découvrez notre boutique "${shopName}" sur AquawaSouk !\n\n` +
        `🛍️ Large choix de produits de qualité\n` +
        `🚚 Livraison rapide partout en Tunisie\n` +
        `💵 Paiement en espèces à la livraison (COD)\n\n` +
        `👉 Visitez notre boutique ici :\n${shopUrl}`;

    shareData.value = {
        title: shopName,
        text: shareText,
        url: shopUrl,
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
    <Head title="My Products" />

    <DashboardLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Mes Produits
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Gérez votre catalogue et partagez vos articles directement sur Facebook
                    </p>
                </div>
                <div class="flex items-center gap-2.5">
                    <button v-if="vendor?.slug"
                            @click="shareShopOnFacebook"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl shadow-xs transition">
                        <svg class="w-3.5 h-3.5 fill-[#1877F2]" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Partager Boutique</span>
                    </button>
                    <Link :href="route('vendor.products.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition">
                        <span>+</span> Ajouter un Produit
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <TableFilters
                    :filters="filters"
                    :show-search="true"
                    :show-status="true"
                    :show-category="true"
                    :show-date-range="true"
                    :status-options="[
                        { value: 'active', label: 'Active' },
                        { value: 'inactive', label: 'Inactive' }
                    ]"
                    :categories="categories"
                    placeholder="Search by name or SKU..."
                />

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Category
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Price
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Stock
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Vues (Visites)
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="product in products.data" :key="product.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <img v-if="product.primary_image" :src="getImageUrl(product.primary_image.image_path)" :alt="product.name" class="h-10 w-10 rounded object-cover" />
                                                    <div v-else class="h-10 w-10 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ product.name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2 mt-0.5">
                                                        <span>SKU: {{ product.sku || 'N/A' }}</span>
                                                        <span>•</span>
                                                        <a :href="'/products/' + product.slug" target="_blank" class="text-brand-600 dark:text-sky-400 hover:underline flex items-center gap-0.5" title="Voir la page détail">
                                                            <span>Page détail</span>
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ product.category.name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatPrice(product.price) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ product.stock }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 font-semibold text-xs border border-slate-200 dark:border-slate-600/50" :title="`${product.views || 0} utilisateurs ont vu la page détail de ce produit`">
                                                <svg class="w-3.5 h-3.5 text-brand-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>{{ (product.views || 0).toLocaleString('fr-FR') }}</span>
                                                <span class="text-[10px] text-slate-400 font-normal">vues</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                product.status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                            ]">
                                                {{ product.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <button @click="shareProductOnFacebook(product)"
                                                        type="button"
                                                        title="Partager sur Facebook"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-semibold rounded-lg shadow-xs transition transform active:scale-95">
                                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                    </svg>
                                                    <span>Partager FB</span>
                                                </button>
                                                <Link :href="route('vendor.products.edit', product.id)" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 text-xs px-2.5 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/40 transition">
                                                    Modifier
                                                </Link>
                                                <button @click="deleteProduct(product.id)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400 text-xs px-2 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition">
                                                    Supprimer
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="products.links && products.links.length > 3" class="mt-6">
                            <nav class="flex items-center justify-center space-x-2">
                                <component
                                    :is="link.url ? Link : 'span'"
                                    v-for="(link, index) in products.links"
                                    :key="index"
                                    :href="link.url"
                                    :class="[
                                        'px-4 py-2 rounded-md text-sm font-medium transition',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : link.url
                                                ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </nav>
                        </div>
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
