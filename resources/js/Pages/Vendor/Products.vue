<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TableFilters from '@/Components/TableFilters.vue';

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

const deleteProduct = (productId) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('vendor.products.destroy', productId), {
            preserveScroll: true,
        });
    }
};

const shareProductOnFacebook = (product) => {
    const productUrl = window.location.origin + '/products/' + product.slug;
    const priceText = (product.discount_price && product.discount_price > 0) ? `${product.discount_price} DT (Promo)` : `${product.price} DT`;
    const shareText = `🔥 Découvrez "${product.name}" à ${priceText} sur AquawaSouk ! Commandez avec livraison rapide partout en Tunisie et paiement à la livraison.`;
    const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(productUrl)}&quote=${encodeURIComponent(shareText)}`;
    window.open(fbUrl, '_blank', 'width=620,height=580,scrollbars=yes,resizable=yes');
};

const shareShopOnFacebook = () => {
    const shopSlug = props.vendor?.slug || '';
    const shopUrl = window.location.origin + '/vendors/' + shopSlug;
    const shareText = `Découvrez tous les produits de notre boutique "${props.vendor?.shop_name || 'Boutique'}" sur AquawaSouk !`;
    const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shopUrl)}&quote=${encodeURIComponent(shareText)}`;
    window.open(fbUrl, '_blank', 'width=620,height=580,scrollbars=yes,resizable=yes');
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
                                                    <img v-if="product.primary_image" :src="'/uploads/' + product.primary_image.image_path" :alt="product.name" class="h-10 w-10 rounded object-cover" />
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
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        SKU: {{ product.sku || 'N/A' }}
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
    </DashboardLayout>
</template>
