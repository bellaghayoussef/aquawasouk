<script setup>
import { nextTick, ref, watch } from 'vue'
import ShoppingAssistantProductCard from './ShoppingAssistantProductCard.vue'
import ShoppingAssistantQuickActions from './ShoppingAssistantQuickActions.vue'
import ShoppingAssistantCartSummary from './ShoppingAssistantCartSummary.vue'

const props = defineProps({
    messages: {
        type: Array,
        required: true,
    },
    isTyping: {
        type: Boolean,
        default: false,
    },
    cart: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['add-to-cart', 'select-action', 'update-cart-quantity', 'remove-cart-item'])

const scrollContainer = ref(null)

const scrollToBottom = async () => {
    await nextTick()
    if (scrollContainer.value) {
        scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight
    }
}

watch(
    () => [props.messages.length, props.isTyping],
    () => {
        scrollToBottom()
    },
    { deep: true }
)
</script>

<template>
    <div ref="scrollContainer" class="flex-1 overflow-y-auto p-3.5 space-y-4 scroll-smooth">
        <div
            v-for="(msg, index) in messages"
            :key="index"
            :class="[
                'flex flex-col',
                msg.sender === 'user' ? 'items-end' : 'items-start'
            ]"
        >
            <!-- User Message Bubble -->
            <div
                v-if="msg.sender === 'user'"
                class="max-w-[85%] px-3.5 py-2.5 rounded-2xl rounded-br-sm bg-brand-600 text-white text-xs sm:text-sm font-medium shadow-sm leading-relaxed"
            >
                {{ msg.text }}
            </div>

            <!-- Assistant Message Container -->
            <div
                v-else
                class="w-full space-y-2.5"
                :dir="msg.direction || 'ltr'"
            >
                <!-- Avatar & Text Bubble -->
                <div class="flex items-start gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-500/20 flex items-center justify-center text-xs shrink-0 font-bold mt-0.5">
                        🤖
                    </div>

                    <div class="flex-1 max-w-[92%] bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl rounded-tl-sm px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 dark:text-slate-100 shadow-sm leading-relaxed whitespace-pre-line text-left">
                        {{ msg.text }}

                        <!-- If AI generated flag -->
                        <span
                            v-if="msg.ai_generated"
                            class="inline-flex items-center gap-1 text-[10px] text-brand-600 dark:text-brand-400 font-semibold mt-1 block opacity-80"
                        >
                            <span>✨ Enrichi avec IA</span>
                        </span>
                    </div>
                </div>

                <!-- Order Tracking Card if attached -->
                <div
                    v-if="msg.order_tracking && msg.order_tracking.found"
                    class="ml-9 p-3 bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm text-left text-xs space-y-2"
                >
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
                        <span class="font-bold text-slate-900 dark:text-white">
                            Commande #{{ msg.order_tracking.order_number }}
                        </span>
                        <span
                            :class="[
                                'px-2 py-0.5 rounded-full text-[10px] font-bold',
                                msg.order_tracking.status === 'delivered' ? 'bg-emerald-100 text-emerald-800' :
                                msg.order_tracking.status === 'shipped' ? 'bg-indigo-100 text-indigo-800' :
                                msg.order_tracking.status === 'processing' ? 'bg-blue-100 text-blue-800' :
                                'bg-amber-100 text-amber-800'
                            ]"
                        >
                            {{ msg.order_tracking.status_label }}
                        </span>
                    </div>

                    <div class="text-[11px] text-slate-500 dark:text-slate-400 space-y-1">
                        <p>📅 Date : {{ msg.order_tracking.date }}</p>
                        <p>📍 Ville : {{ msg.order_tracking.city }}</p>
                        <p>💵 Total : <strong class="text-slate-800 dark:text-slate-200">{{ Number(msg.order_tracking.total_amount).toFixed(2) }} {{ msg.order_tracking.currency }}</strong> ({{ msg.order_tracking.payment_status }})</p>
                    </div>

                    <div v-if="msg.order_tracking.items && msg.order_tracking.items.length" class="pt-1.5 border-t border-slate-100 dark:border-slate-700">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Articles :</p>
                        <ul class="text-[11px] text-slate-600 dark:text-slate-300 space-y-0.5">
                            <li v-for="(it, i) in msg.order_tracking.items" :key="i" class="flex justify-between">
                                <span class="truncate">{{ it.name }} (x{{ it.quantity }})</span>
                                <span class="font-medium shrink-0 ml-2">{{ it.vendor_name }}</span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="msg.order_tracking.details_url" class="pt-1 text-right">
                        <a
                            :href="msg.order_tracking.details_url"
                            class="text-[11px] font-semibold text-brand-600 dark:text-brand-400 hover:underline inline-flex items-center gap-1"
                        >
                            <span>Voir le détail complet</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>

                <!-- Products Vertical Stack -->
                <div
                    v-if="msg.products && msg.products.length"
                    class="ml-9 space-y-2 pt-0.5"
                >
                    <ShoppingAssistantProductCard
                        v-for="product in msg.products"
                        :key="product.id"
                        :product="product"
                        @add-to-cart="emit('add-to-cart', $event)"
                    />
                </div>

                <!-- Mini Cart Card if cart intent triggered -->
                <div v-if="msg.cart" class="ml-9">
                    <ShoppingAssistantCartSummary
                        :cart="msg.cart"
                        @update-quantity="emit('update-cart-quantity', $event)"
                        @remove-item="emit('remove-cart-item', $event)"
                        @continue-shopping="emit('select-action', { query: 'meilleures ventes' })"
                    />
                </div>

                <!-- Contextual Quick Action Chips -->
                <div v-if="msg.quick_actions && msg.quick_actions.length" class="ml-9 pt-0.5">
                    <ShoppingAssistantQuickActions
                        :actions="msg.quick_actions"
                        @select="emit('select-action', $event)"
                    />
                </div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div v-if="isTyping" class="flex items-center gap-2 text-slate-400 text-xs pl-1">
            <div class="w-6 h-6 rounded-full bg-brand-500/10 text-brand-600 border border-brand-500/20 flex items-center justify-center text-xs shrink-0">
                🤖
            </div>
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl px-3 py-2 flex items-center gap-1.5 shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-bounce"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-bounce [animation-delay:0.2s]"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-bounce [animation-delay:0.4s]"></span>
            </div>
        </div>
    </div>
</template>
