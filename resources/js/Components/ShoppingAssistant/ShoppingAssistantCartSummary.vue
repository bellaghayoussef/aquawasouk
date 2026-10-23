<script setup>
import { computed } from 'vue'

const props = defineProps({
    cart: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update-quantity', 'remove-item', 'continue-shopping'])

const hasItems = computed(() => props.cart?.items?.length > 0)
</script>

<template>
    <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/80 dark:border-slate-800 text-left">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-slate-800 mb-3">
            <div class="flex items-center gap-2">
                <span class="text-base">🛒</span>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                    Mon Panier ({{ cart?.total_items || 0 }})
                </h3>
            </div>
            <span class="text-xs font-black text-brand-600 dark:text-brand-400">
                {{ cart?.formatted_subtotal || '0.00 DT' }}
            </span>
        </div>

        <!-- Empty State -->
        <div v-if="!hasItems" class="text-center py-6">
            <span class="text-3xl block mb-2 opacity-50">🛍️</span>
            <p class="text-xs text-slate-500 dark:text-slate-400">Votre panier est actuellement vide.</p>
            <button
                type="button"
                @click="emit('continue-shopping')"
                class="mt-3 px-3 py-1.5 rounded-xl bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 transition"
            >
                Découvrir des produits
            </button>
        </div>

        <!-- Items List -->
        <div v-else class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
            <div
                v-for="item in cart.items"
                :key="item.id"
                class="flex items-center justify-between gap-2.5 p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 shadow-2xs"
            >
                <!-- Thumbnail -->
                <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-700 shrink-0 flex items-center justify-center">
                    <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                    <span v-else class="text-xs">📦</span>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs font-semibold text-slate-900 dark:text-white truncate">
                        {{ item.name }}
                    </h4>
                    <p class="text-[10px] text-slate-400">
                        {{ item.vendor_name }} • {{ Number(item.price).toFixed(2) }} {{ cart.currency }}
                    </p>
                </div>

                <!-- Quantity Controls -->
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        @click="emit('update-quantity', { id: item.id, quantity: item.quantity - 1 })"
                        class="w-5 h-5 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded transition"
                        title="Diminuer"
                    >
                        -
                    </button>
                    <span class="text-xs font-bold w-4 text-center text-slate-800 dark:text-slate-100">
                        {{ item.quantity }}
                    </span>
                    <button
                        type="button"
                        @click="emit('update-quantity', { id: item.id, quantity: item.quantity + 1 })"
                        class="w-5 h-5 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded transition"
                        title="Augmenter"
                    >
                        +
                    </button>

                    <button
                        type="button"
                        @click="emit('remove-item', item.id)"
                        class="ml-1 text-slate-400 hover:text-rose-500 p-0.5 transition"
                        title="Supprimer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Checkout Actions -->
        <div v-if="hasItems" class="mt-4 pt-3 border-t border-slate-200/80 dark:border-slate-800 space-y-2">
            <!-- Vendor Checkout Links if multi-vendor -->
            <div v-if="cart.vendor_groups && cart.vendor_groups.length > 1" class="space-y-1.5 mb-2">
                <p class="text-[10px] text-slate-400 font-medium">Commande par boutique :</p>
                <a
                    v-for="v in cart.vendor_groups"
                    :key="v.vendor_id"
                    :href="v.checkout_url"
                    class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-brand-500 transition text-slate-700 dark:text-slate-200"
                >
                    <span class="truncate">{{ v.vendor_name }} ({{ v.items_count }} art.)</span>
                    <span class="font-bold text-brand-600 dark:text-brand-400">{{ Number(v.vendor_subtotal).toFixed(2) }} {{ cart.currency }} →</span>
                </a>
            </div>

            <!-- Global Cart Button -->
            <a
                :href="cart.cart_url"
                class="w-full py-2 px-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs flex items-center justify-center gap-1.5 shadow-sm transition active:scale-98"
            >
                <span>Finaliser ma commande</span>
                <span>({{ cart.formatted_subtotal }})</span>
            </a>
        </div>
    </div>
</template>
