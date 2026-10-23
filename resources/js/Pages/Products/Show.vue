<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SEO from '@/Components/SEO.vue';
import BannerCarousel from '@/Components/BannerCarousel.vue';

const props = defineProps({
    product: Object,
    relatedProducts: {
        type: Array,
        default: () => [],
    },
    structuredData: String,
    seo: Object,
    banners: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const form = useForm({
    product_id: props.product.id,
    quantity: 1,
});

const tunisianGovernorates = [
    'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan',
    'Bizerte', 'Béja', 'Jendouba', 'Le Kef', 'Siliana', 'Sousse',
    'Monastir', 'Mahdia', 'Sfax', 'Kairouan', 'Kasserine', 'Sidi Bouzid',
    'Gabès', 'Medenine', 'Tataouine', 'Gafsa', 'Tozeur', 'Kebili'
];

const directOrderForm = useForm({
    product_id: props.product.id,
    quantity: 1,
    customer_name: authUser.value?.name || '',
    phone: '',
    shipping_city: 'Tunis',
    shipping_address: '',
    shipping_postal_code: '',
    notes: '',
});

const selectedImage = ref(
    props.product.images?.[0]?.image_path ||
    props.product.primary_image?.image_path ||
    null
);

const activeTab = ref('description');

const formatPrice = (price) => {
    return new Intl.NumberFormat(usePage().props.locale === 'ar' ? 'ar-TN' : 'fr-TN', {
        style: 'currency',
        currency: 'TND',
    }).format(price);
};

const getDiscountPercentage = (price, discountPrice) => {
    if (!discountPrice || discountPrice >= price) return 0;
    return Math.round(((price - discountPrice) / price) * 100);
};

const unitPrice = computed(() => {
    return props.product.discount_price > 0 ? Number(props.product.discount_price) : Number(props.product.price);
});

const shippingFee = computed(() => {
    return Number(props.product.vendor?.shipping_price || 0);
});

const directSubtotal = computed(() => {
    return unitPrice.value * directOrderForm.quantity;
});

const directTotal = computed(() => {
    return directSubtotal.value + shippingFee.value;
});

const parsedSpecifications = computed(() => {
    const specs = props.product?.specifications;
    if (!specs) return {};
    if (typeof specs === 'object') return specs;
    if (typeof specs === 'string') {
        try {
            const parsed = JSON.parse(specs);
            return typeof parsed === 'object' && parsed !== null ? parsed : {};
        } catch (e) {
            return {};
        }
    }
    return {};
});

const incrementQty = () => {
    if (directOrderForm.quantity < props.product.stock) {
        directOrderForm.quantity++;
        form.quantity = directOrderForm.quantity;
    }
};

const decrementQty = () => {
    if (directOrderForm.quantity > 1) {
        directOrderForm.quantity--;
        form.quantity = directOrderForm.quantity;
    }
};

const addToCart = () => {
    form.quantity = directOrderForm.quantity;
    form.post(route('cart.add'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('quantity');
        },
    });
};

const submitDirectOrder = () => {
    directOrderForm.post(route('orders.direct'), {
        preserveScroll: true,
    });
};

const selectImage = (imagePath) => {
    selectedImage.value = imagePath;
};

// Inject structured data into head
onMounted(() => {
    if (props.structuredData) {
        document.querySelectorAll('script[data-schema-type="product"]').forEach(el => el.remove());
        const productScript = document.createElement('script');
        productScript.type = 'application/ld+json';
        productScript.setAttribute('data-schema-type', 'product');
        productScript.textContent = props.structuredData;
        document.head.appendChild(productScript);
    }
});
</script>

<template>
    <SEO
        v-if="seo"
        :title="seo.title"
        :description="seo.description"
        :keywords="seo.keywords"
        :image="seo.image"
        :url="seo.canonical"
    />
    <Head v-else :title="product.name + ' - AquawaSouk'" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <!-- Breadcrumb Navigation -->
            <nav class="mb-6 flex items-center text-xs font-semibold text-slate-400 gap-2 overflow-x-auto no-scrollbar">
                <Link :href="route('home')" class="hover:text-brand-600 transition">{{ __('home') }}</Link>
                <span>/</span>
                <Link :href="route('products.index')" class="hover:text-brand-600 transition">{{ __('products') }}</Link>
                <span v-if="product.category">/</span>
                <Link
                    v-if="product.category"
                    :href="route('products.index', { category: product.category.slug })"
                    class="hover:text-brand-600 transition truncate max-w-[150px]"
                >
                    {{ product.category.name }}
                </Link>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200 truncate max-w-[200px]">{{ product.name }}</span>
            </nav>

            <!-- Product Showcase Grid (Sticky Two-Column) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 mb-16">
                <!-- Left: Media & Image Gallery -->
                <div class="lg:col-span-6 space-y-4">
                    <!-- Main Featured Image -->
                    <div class="relative bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 aspect-square flex items-center justify-center overflow-hidden shadow-sm">
                        <img
                            v-if="selectedImage"
                            :src="'/uploads/' + selectedImage"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                            <svg class="w-28 h-28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>

                        <!-- Discount Pill -->
                        <div
                            v-if="product.discount_price"
                            class="absolute top-4 left-4 px-3 py-1.5 bg-red-600 text-white font-black text-xs rounded-full shadow-lg"
                        >
                            -{{ getDiscountPercentage(product.price, product.discount_price) }}% REMISE
                        </div>

                        <!-- Featured Badge -->
                        <div
                            v-if="product.is_featured"
                            class="absolute top-4 right-4 px-3 py-1 bg-amber-500 text-slate-950 font-bold text-xs rounded-full shadow-md flex items-center gap-1.5"
                        >
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span>Vedette</span>
                        </div>
                    </div>

                    <!-- Thumbnails Gallery -->
                    <div v-if="product.images && product.images.length > 1" class="grid grid-cols-5 gap-3">
                        <button
                            v-for="image in product.images"
                            :key="image.id"
                            type="button"
                            @click="selectImage(image.image_path)"
                            class="aspect-square rounded-2xl overflow-hidden border-2 bg-white dark:bg-slate-800 transition shadow-sm p-0.5 cursor-pointer"
                            :class="[
                                selectedImage === image.image_path
                                    ? 'border-brand-600 ring-2 ring-brand-500/20'
                                    : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
                            ]"
                        >
                            <img :src="'/uploads/' + image.image_path" :alt="product.name" class="w-full h-full object-cover rounded-xl" />
                        </button>
                    </div>

                    <!-- Vendor Mini Spotlight -->
                    <div v-if="product.vendor" class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-sm flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center font-black text-brand-600 text-lg border border-slate-200 dark:border-slate-600 overflow-hidden shrink-0">
                                <img v-if="product.vendor.logo" :src="'/uploads/' + product.vendor.logo" :alt="product.vendor.shop_name" class="w-full h-full object-cover" />
                                <span v-else>{{ product.vendor.shop_name.charAt(0) }}</span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium">Boutique officielle</p>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span>{{ product.vendor.shop_name }}</span>
                                    <span class="inline-flex items-center text-sky-500" title="Vendeur certifié">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                </h4>
                                <p class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ product.vendor.city }}, {{ product.vendor.country }}</span>
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="route('vendors.show', product.vendor.slug)"
                            class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-brand-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 hover:text-brand-600 font-bold text-xs transition shrink-0 cursor-pointer"
                        >
                            Voir la boutique
                        </Link>
                    </div>
                </div>

                <!-- Right: Product Info & Purchase Form -->
                <div class="lg:col-span-6 space-y-6">
                    <div>
                        <!-- Category Tag -->
                        <span class="inline-block text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-sky-400 mb-2">
                            {{ product.category ? product.category.name : 'Article' }}
                        </span>

                        <!-- Main Title -->
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-3">
                            {{ product.name }}
                        </h1>

                        <!-- Price Section -->
                        <div class="flex items-baseline gap-3 mb-4">
                            <span class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                                {{ formatPrice(product.discount_price || product.price) }}
                            </span>
                            <span v-if="product.discount_price" class="text-lg text-slate-400 line-through">
                                {{ formatPrice(product.price) }}
                            </span>
                            <span v-if="product.discount_price" class="px-2.5 py-1 rounded-full text-xs font-black bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400">
                                Économisez {{ formatPrice(product.price - product.discount_price) }}
                            </span>
                        </div>

                        <!-- Stock Status -->
                        <div class="flex items-center gap-2 mb-6">
                            <span v-if="product.stock > 10" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>{{ __('in_stock') }} ({{ product.stock }} disponibles)</span>
                            </span>
                            <span v-else-if="product.stock > 0" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-3 py-1 rounded-full border border-amber-200 dark:border-amber-800">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>{{ __('low_stock') }} (plus que {{ product.stock }} en stock !)</span>
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-3 py-1 rounded-full border border-red-200 dark:border-red-800">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                <span>{{ __('out_of_stock') }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Express Direct Order Card (Cash on Delivery) -->
                    <div class="p-6 rounded-3xl bg-gradient-to-br from-white via-slate-50 to-brand-50/20 dark:from-slate-800 dark:via-slate-800 dark:to-slate-800 border-2 border-brand-500/30 hover:border-brand-500/60 shadow-xl hover:shadow-2xl hover:shadow-brand-500/10 transition-all duration-300 relative overflow-hidden">
                        <!-- Top Header -->
                        <div class="flex items-center justify-between gap-2 mb-5 pb-4 border-b border-slate-200/80 dark:border-slate-700">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-500 text-slate-950 font-black text-lg flex items-center justify-center shadow-md shadow-amber-500/20">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white leading-tight">
                                        {{ __('direct_order_title') }}
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ __('direct_order_subtitle') }}
                                    </p>
                                </div>
                            </div>
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 text-xs font-black rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>{{ __('cash_on_delivery_badge') }}</span>
                            </span>
                        </div>

                        <!-- Live Price & Quantity Counter -->
                        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 mb-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <!-- Quantity Counter -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                                        {{ __('quantity') }}
                                    </label>
                                    <div class="flex items-center border border-slate-300 dark:border-slate-600 rounded-xl overflow-hidden bg-white dark:bg-slate-800 shadow-inner">
                                        <button
                                            type="button"
                                            @click="decrementQty"
                                            :disabled="directOrderForm.quantity <= 1 || product.stock === 0"
                                            class="px-3.5 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed text-base font-black transition cursor-pointer"
                                        >
                                            -
                                        </button>
                                        <input
                                            type="number"
                                            v-model.number="directOrderForm.quantity"
                                            min="1"
                                            :max="product.stock"
                                            :disabled="product.stock === 0"
                                            class="w-14 text-center border-0 py-1.5 text-sm font-bold text-slate-900 dark:text-white dark:bg-slate-800 focus:ring-0"
                                        />
                                        <button
                                            type="button"
                                            @click="incrementQty"
                                            :disabled="directOrderForm.quantity >= product.stock || product.stock === 0"
                                            class="px-3.5 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed text-base font-black transition cursor-pointer"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>

                                <!-- Live Price Summary -->
                                <div class="text-right">
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ __('shipping_fee') }}:
                                        <span class="font-bold text-slate-800 dark:text-slate-200">
                                            {{ shippingFee > 0 ? formatPrice(shippingFee) : __('free_shipping') }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">
                                        {{ __('total_to_pay') }}:
                                    </div>
                                    <div class="text-2xl sm:text-3xl font-black text-brand-600 dark:text-sky-400">
                                        {{ formatPrice(directTotal) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Order Input Fields -->
                        <form @submit.prevent="submitDirectOrder" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
                                    {{ __('full_name') }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="directOrderForm.customer_name"
                                    type="text"
                                    required
                                    :placeholder="__('full_name') || 'Votre Nom et Prénom'"
                                    :disabled="product.stock === 0 || directOrderForm.processing"
                                    class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm"
                                />
                                <div v-if="directOrderForm.errors.customer_name" class="text-red-500 text-xs mt-1">
                                    {{ directOrderForm.errors.customer_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
                                    {{ __('phone_number') }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="directOrderForm.phone"
                                    type="tel"
                                    required
                                    placeholder="Ex: 98 123 456 / 20 123 456"
                                    :disabled="product.stock === 0 || directOrderForm.processing"
                                    class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm"
                                />
                                <div v-if="directOrderForm.errors.phone" class="text-red-500 text-xs mt-1">
                                    {{ directOrderForm.errors.phone }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
                                        {{ __('city_governorate') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="directOrderForm.shipping_city"
                                        required
                                        :disabled="product.stock === 0 || directOrderForm.processing"
                                        class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm"
                                    >
                                        <option v-for="gov in tunisianGovernorates" :key="gov" :value="gov">
                                            {{ gov }}
                                        </option>
                                    </select>
                                    <div v-if="directOrderForm.errors.shipping_city" class="text-red-500 text-xs mt-1">
                                        {{ directOrderForm.errors.shipping_city }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
                                        {{ __('delivery_address') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="directOrderForm.shipping_address"
                                        type="text"
                                        required
                                        placeholder="Rue, Quartier, N°..."
                                        :disabled="product.stock === 0 || directOrderForm.processing"
                                        class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm"
                                    />
                                    <div v-if="directOrderForm.errors.shipping_address" class="text-red-500 text-xs mt-1">
                                        {{ directOrderForm.errors.shipping_address }}
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                                    {{ __('shipping_notes') }}
                                </label>
                                <input
                                    v-model="directOrderForm.notes"
                                    type="text"
                                    placeholder="Ex: Appeler avant d'arriver..."
                                    :disabled="product.stock === 0 || directOrderForm.processing"
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 shadow-sm"
                                />
                            </div>

                            <!-- Big Primary Action Button -->
                            <button
                                type="submit"
                                :disabled="product.stock === 0 || directOrderForm.processing"
                                class="w-full mt-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-6 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-200 transform active:scale-[0.99] flex items-center justify-center gap-2.5 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <svg v-if="directOrderForm.processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span v-if="directOrderForm.processing">{{ __('processing') }}</span>
                                <span v-else-if="product.stock === 0">{{ __('out_of_stock') }}</span>
                                <span v-else class="flex items-center gap-2 text-base">
                                    <svg class="w-5 h-5 text-emerald-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <span>Confirmer la commande (Paiement à la livraison)</span>
                                    <span class="text-sm opacity-90 font-bold">({{ formatPrice(directTotal) }})</span>
                                </span>
                            </button>

                            <!-- Micro Trust Badges -->
                            <div class="grid grid-cols-3 gap-2 pt-4 text-center border-t border-slate-200/60 dark:border-slate-700/60 text-[11px] text-slate-500 dark:text-slate-400 font-semibold">
                                <div class="flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4zM2 5h11v9H2V5zm11 3h4.172a2 2 0 011.414.586l2.828 2.828A2 2 0 0122 12.828V14h-3M2 14h2m7 0h3"/>
                                    </svg>
                                    <span>{{ __('fast_delivery_badge') }}</span>
                                </div>
                                <div class="flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span>{{ __('cash_on_delivery_badge') }}</span>
                                </div>
                                <div class="flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span>{{ __('phone_confirmation_badge') }}</span>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Divider: Or Add to Cart -->
                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                        <span class="flex-shrink mx-4 text-xs uppercase tracking-wider text-slate-400 font-bold">
                            {{ __('or_add_to_cart') }}
                        </span>
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    </div>

                    <!-- Secondary Add to Cart Button -->
                    <form @submit.prevent="addToCart">
                        <button
                            type="submit"
                            :disabled="product.stock === 0 || form.processing"
                            class="w-full bg-white hover:bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white font-bold py-3.5 px-6 rounded-2xl transition border-2 border-slate-200 dark:border-slate-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2.5 shadow-sm"
                        >
                            <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span v-if="form.processing">{{ __('loading') }}</span>
                            <span v-else>{{ __('add_to_cart') }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabs Section: Description, Specifications & Reviews -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 p-6 sm:p-10 mb-16 shadow-sm">
                <!-- Tab Buttons -->
                <div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-700 pb-4 mb-8">
                    <button
                        @click="activeTab = 'description'"
                        class="text-sm font-bold pb-2 border-b-2 transition"
                        :class="[activeTab === 'description' ? 'border-brand-600 text-brand-600 dark:text-sky-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-white']"
                    >
                        {{ __('description') || 'Description du produit' }}
                    </button>

                    <button
                        v-if="product.specifications && Object.keys(product.specifications).length > 0"
                        @click="activeTab = 'specifications'"
                        class="text-sm font-bold pb-2 border-b-2 transition"
                        :class="[activeTab === 'specifications' ? 'border-brand-600 text-brand-600 dark:text-sky-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-white']"
                    >
                        Fiche Technique & Détails
                    </button>

                    <button
                        @click="activeTab = 'reviews'"
                        class="text-sm font-bold pb-2 border-b-2 transition"
                        :class="[activeTab === 'reviews' ? 'border-brand-600 text-brand-600 dark:text-sky-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-white']"
                    >
                        Avis Clients ({{ product.reviews ? product.reviews.length : 0 }})
                    </button>
                </div>

                <!-- Description Content -->
                <div v-show="activeTab === 'description'" class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 leading-relaxed text-sm sm:text-base">
                    <p class="whitespace-pre-line">{{ product.description }}</p>
                </div>

                <!-- Specifications Content -->
                <div v-show="activeTab === 'specifications'">
                    <div v-if="parsedSpecifications && Object.keys(parsedSpecifications).length > 0" class="max-w-2xl divide-y divide-slate-100 dark:divide-slate-700/80">
                        <div
                            v-for="(val, key) in parsedSpecifications"
                            :key="key"
                            class="grid grid-cols-2 py-3 text-sm"
                        >
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ key }}</span>
                            <span class="text-slate-600 dark:text-slate-400">{{ val }}</span>
                        </div>
                    </div>
                    <div v-else class="text-sm text-slate-500 dark:text-slate-400 py-4">
                        Aucune spécification technique disponible pour ce produit.
                    </div>
                </div>

                <!-- Reviews Content -->
                <div v-show="activeTab === 'reviews'">
                    <div v-if="product.reviews && product.reviews.length > 0" class="space-y-4">
                        <div v-for="review in product.reviews" :key="review.id" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-700/50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-slate-900 dark:text-white text-sm">{{ review.user ? review.user.name : 'Client Vérifié' }}</span>
                                <span class="text-amber-500 text-sm">★★★★★</span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">{{ review.comment }}</p>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-slate-400 text-sm">
                        Aucun avis pour l'instant. Soyez le premier à commander ce produit !
                    </div>
                </div>
            </div>

            <!-- Related Products Section -->
            <div v-if="relatedProducts && relatedProducts.length > 0" class="mt-16 pt-12 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-sky-400">Recommandations</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                            {{ __('related_products') || 'Produits similaires qui pourraient vous plaire' }}
                        </h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="related in relatedProducts"
                        :key="related.id"
                        class="group rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
                    >
                        <Link :href="route('products.show', related.slug)" class="block relative aspect-square bg-slate-100 dark:bg-slate-700/50 overflow-hidden">
                            <img
                                v-if="related.primary_image"
                                :src="'/uploads/' + related.primary_image.image_path"
                                :alt="related.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </Link>

                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-brand-600 transition line-clamp-2 leading-snug">
                                    {{ related.name }}
                                </h3>
                                <p v-if="related.vendor" class="text-xs text-slate-400 mt-1">
                                    {{ related.vendor.shop_name }}
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
                                <div class="text-base font-black text-slate-900 dark:text-white">
                                    {{ formatPrice(related.discount_price || related.price) }}
                                </div>
                                <Link
                                    :href="route('products.show', related.slug)"
                                    class="text-xs font-bold text-brand-600 hover:text-brand-700"
                                >
                                    Voir →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <BannerCarousel v-if="banners && banners.length > 0" :banners="banners" />
    </AppLayout>
</template>
