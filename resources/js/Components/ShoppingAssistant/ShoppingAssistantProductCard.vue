<script setup>
import { ref } from 'vue'

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['add-to-cart'])

const quantity = ref(1)
const isAdding = ref(false)
const addedSuccess = ref(false)

const increase = () => {
    if (quantity.value < props.product.stock) {
        quantity.value++
    }
}

const decrease = () => {
    if (quantity.value > 1) {
        quantity.value--
    }
}

const handleAdd = async () => {
    if (isAdding.value || !props.product.in_stock) return
    isAdding.value = true
    try {
        await emit('add-to-cart', {
            productId: props.product.id,
            quantity: quantity.value,
        })
        addedSuccess.value = true
        setTimeout(() => {
            addedSuccess.value = false
        }, 2200)
    } finally {
        isAdding.value = false
    }
}
</script>

<template>
    <div class="group relative bg-white dark:bg-slate-800/95 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-2.5 shadow-sm hover:shadow-md hover:border-brand-500/50 transition-all duration-200 text-left">
        <div class="flex items-center gap-3">
            <!-- Product Thumbnail (Left) -->
            <a
                :href="product.url"
                target="_blank"
                class="relative w-20 h-20 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700 shrink-0 border border-slate-100 dark:border-slate-600/60 block"
            >
                <img
                    v-if="product.image"
                    :src="product.image"
                    :alt="product.name"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    loading="lazy"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-2xl text-slate-300 dark:text-slate-500">
                    📦
                </div>

                <!-- Discount Tag -->
                <span
                    v-if="product.has_discount"
                    class="absolute top-1 left-1 bg-red-600 text-white font-black text-[9px] px-1.5 py-0.5 rounded-md shadow-xs leading-none"
                >
                    -{{ product.discount_percent }}%
                </span>
            </a>

            <!-- Product Details (Middle & Right) -->
            <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                <div>
                    <!-- Vendor & Stock Pill -->
                    <div class="flex items-center justify-between gap-1 text-[10px] text-slate-400 mb-0.5">
                        <span class="truncate max-w-[130px] font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1">
                            <span>🏪</span>
                            <span>{{ product.vendor_name }}</span>
                        </span>
                        <span
                            v-if="product.in_stock"
                            class="text-emerald-600 dark:text-emerald-400 font-semibold shrink-0"
                        >
                            ✓ En stock
                        </span>
                        <span
                            v-else
                            class="text-rose-500 dark:text-rose-400 font-semibold shrink-0"
                        >
                            Épuisé
                        </span>
                    </div>

                    <!-- Title Link -->
                    <a
                        :href="product.url"
                        target="_blank"
                        class="text-xs font-bold text-slate-900 dark:text-white line-clamp-1 hover:text-brand-600 dark:hover:text-brand-400 transition leading-snug block"
                        :title="product.name"
                    >
                        {{ product.name }}
                    </a>
                </div>

                <!-- Price & Action Footer -->
                <div class="mt-2 flex items-center justify-between gap-2 flex-wrap">
                    <!-- Price -->
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-sm font-black text-slate-900 dark:text-white">
                            {{ Number(product.current_price).toFixed(2) }} <span class="text-xs font-bold text-brand-600 dark:text-brand-400">{{ product.currency }}</span>
                        </span>
                        <span
                            v-if="product.has_discount"
                            class="text-[10px] text-slate-400 line-through"
                        >
                            {{ Number(product.price).toFixed(2) }}
                        </span>
                    </div>

                    <!-- Add to Cart or Out-of-Stock Button -->
                    <div v-if="product.in_stock" class="flex items-center gap-1.5 shrink-0">
                        <!-- Compact Quantity Stepper -->
                        <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-700/60 p-0.5">
                            <button
                                type="button"
                                @click="decrease"
                                :disabled="quantity <= 1 || isAdding"
                                class="w-5 h-5 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition disabled:opacity-30"
                                title="Moins"
                            >
                                -
                            </button>
                            <span class="w-5 text-center text-[11px] font-bold text-slate-800 dark:text-slate-100">
                                {{ quantity }}
                            </span>
                            <button
                                type="button"
                                @click="increase"
                                :disabled="quantity >= product.stock || isAdding"
                                class="w-5 h-5 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-600 rounded transition disabled:opacity-30"
                                title="Plus"
                            >
                                +
                            </button>
                        </div>

                        <!-- Add Button -->
                        <button
                            type="button"
                            @click="handleAdd"
                            :disabled="isAdding"
                            :class="[
                                'px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm',
                                addedSuccess
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-slate-900 hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-500 text-white active:scale-95'
                            ]"
                        >
                            <svg v-if="isAdding" class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span v-else-if="addedSuccess">✓</span>
                            <span v-else class="flex items-center gap-1">
                                <span>🛒</span>
                                <span>Ajouter</span>
                            </span>
                        </button>
                    </div>

                    <div v-else>
                        <a
                            :href="product.url"
                            target="_blank"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-800 transition"
                        >
                            Voir détails
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
