<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import ShoppingAssistantMessages from './ShoppingAssistantMessages.vue'
import ShoppingAssistantQuickActions from './ShoppingAssistantQuickActions.vue'
import ShoppingAssistantCartSummary from './ShoppingAssistantCartSummary.vue'

const isOpen = ref(false)
const isMinimized = ref(false)
const activeTab = ref('chat') // 'chat' | 'cart' | 'tracking'
const isTyping = ref(false)
const inputMessage = ref('')
const trackingNumber = ref('')
const trackingPhone = ref('')
const trackingLoading = ref(false)
const trackingResult = ref(null)
const trackingError = ref('')

const messages = ref([])
const suggestedActions = ref([])
const cart = ref({
    items: [],
    total_items: 0,
    subtotal: 0,
    currency: 'DT',
    formatted_subtotal: '0.00 DT',
})
const config = ref({
    enabled: true,
    welcome_message: "Bonjour ! 👋 Comment puis-je vous aider aujourd'hui ?",
    currency: 'DT',
})

const cartCount = computed(() => cart.value?.total_items || 0)

// Initialize assistant state
const loadInitialConfig = async () => {
    try {
        const { data } = await axios.get('/shopping-assistant/config')
        config.value = data
        suggestedActions.value = data.suggested_actions || []
        if (data.cart) {
            cart.value = data.cart
        }

        // Add welcome message if empty
        if (messages.value.length === 0 && data.welcome_message) {
            messages.value.push({
                sender: 'assistant',
                text: data.welcome_message,
                quick_actions: data.suggested_actions || [],
            })
        }
    } catch (e) {
        console.error('Failed to load shopping assistant config:', e)
    }
}

// Send user message
const sendMessage = async (textToSend = null) => {
    const text = (textToSend || inputMessage.value).trim()
    if (!text || isTyping.value) return

    inputMessage.value = ''
    messages.value.push({
        sender: 'user',
        text: text,
    })

    isTyping.value = true

    try {
        const { data } = await axios.post('/shopping-assistant/chat', {
            message: text,
        })

        messages.value.push({
            sender: 'assistant',
            text: data.message,
            direction: data.direction || 'ltr',
            products: data.products || [],
            quick_actions: data.quick_actions || [],
            order_tracking: data.order_tracking || null,
            cart: data.cart || null,
            ai_generated: data.ai_generated || false,
        })

        if (data.cart_count !== undefined) {
            cart.value.total_items = data.cart_count
            cart.value.subtotal = data.cart_subtotal
        }
    } catch (e) {
        messages.value.push({
            sender: 'assistant',
            text: "Désolé, une erreur de communication est survenue. Veuillez réessayer dans quelques instants.",
            direction: 'ltr',
            quick_actions: [
                { label: '🔄 Réessayer', query: text },
                { label: '🔥 Meilleures ventes', query: 'meilleures ventes' },
            ],
        })
    } finally {
        isTyping.value = false
    }
}

// Handle action chip selection
const handleActionSelect = (action) => {
    if (action.action === 'view_cart' || action.action === 'open_cart') {
        activeTab.value = 'cart'
        fetchCart()
        return
    }

    if (action.url) {
        window.location.href = action.url
        return
    }

    if (action.query) {
        activeTab.value = 'chat'
        sendMessage(action.query)
    }
}

// Add to cart directly
const handleAddToCart = async ({ productId, quantity }) => {
    try {
        const { data } = await axios.post('/shopping-assistant/cart/add', {
            product_id: productId,
            quantity: quantity,
        })

        if (data.cart) {
            cart.value = data.cart
        }

        // Add a gentle confirmation message
        messages.value.push({
            sender: 'assistant',
            text: `✅ ${data.message}`,
            direction: 'ltr',
            quick_actions: [
                { label: '🛒 Voir le panier', action: 'open_cart' },
                { label: '💳 Commander', url: data.cart?.cart_url || '/cart' },
            ],
        })
    } catch (e) {
        const errorMsg = e.response?.data?.message || "Impossible d'ajouter cet article au panier."
        alert(errorMsg)
    }
}

// Refresh cart data
const fetchCart = async () => {
    try {
        const { data } = await axios.get('/shopping-assistant/cart')
        cart.value = data
    } catch (e) {
        console.error('Failed to fetch cart:', e)
    }
}

// Update cart quantity
const handleUpdateQuantity = async ({ id, quantity }) => {
    try {
        const { data } = await axios.patch(`/shopping-assistant/cart/${id}`, {
            quantity: quantity,
        })
        if (data.cart) {
            cart.value = data.cart
        }
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur mise à jour panier')
    }
}

// Remove item from cart
const handleRemoveItem = async (id) => {
    try {
        const { data } = await axios.delete(`/shopping-assistant/cart/${id}`)
        if (data.cart) {
            cart.value = data.cart
        }
    } catch (e) {
        console.error('Failed to remove cart item:', e)
    }
}

// Direct order tracking lookup
const submitTracking = async () => {
    if (!trackingNumber.value.trim()) return
    trackingLoading.value = true
    trackingError.value = ''
    trackingResult.value = null

    try {
        const { data } = await axios.get('/shopping-assistant/order-tracking', {
            params: {
                order_number: trackingNumber.value.trim(),
                phone: trackingPhone.value.trim() || undefined,
            },
        })

        if (data.found) {
            trackingResult.value = data
        } else {
            trackingError.value = data.message || 'Commande introuvable.'
        }
    } catch (e) {
        trackingError.value = "Erreur lors de la vérification de la commande."
    } finally {
        trackingLoading.value = false
    }
}

const toggleOpen = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        isMinimized.value = false
        if (messages.value.length === 0) {
            loadInitialConfig()
        }
    }
}

onMounted(() => {
    loadInitialConfig()
})
</script>

<template>
    <!-- Widget Container -->
    <div v-if="config.enabled && 1 === 0" class="fixed bottom-5 right-5 z-50 font-sans">
        <!-- Floating Trigger Button -->
        <button
            v-if="!isOpen"
            type="button"
            @click="toggleOpen"
            class="group relative flex items-center gap-2.5 px-4 py-3 rounded-full bg-slate-950 text-white border border-slate-800 shadow-2xl hover:bg-slate-900 hover:scale-105 active:scale-95 transition duration-300 focus:outline-none focus:ring-4 focus:ring-brand-500/30"
            aria-label="Ouvrir l'assistant shopping"
        >
            <div class="relative w-8 h-8 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center text-lg font-bold border border-brand-500/30 group-hover:rotate-12 transition duration-300">
                🤖
                <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-slate-950 animate-pulse"></span>
            </div>

            <div class="hidden sm:flex flex-col text-left">
                <span class="text-xs font-bold leading-tight">Assistant Shopping</span>
                <span class="text-[10px] text-slate-400 font-medium leading-tight">Besoin d'aide ? En ligne</span>
            </div>

            <!-- Cart Badge if has items -->
            <span
                v-if="cartCount > 0"
                class="absolute -top-1.5 -left-1.5 px-2 py-0.5 rounded-full bg-brand-600 text-white font-black text-[10px] shadow-sm animate-bounce"
            >
                {{ cartCount }}
            </span>
        </button>

        <!-- Chat Panel Window -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-6 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-6 scale-95"
        >
            <div
                v-if="isOpen"
                :class="[
                    'flex flex-col bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl overflow-hidden transition-all duration-300',
                    isMinimized
                        ? 'w-72 sm:w-80 h-16'
                        : 'w-[94vw] sm:w-[440px] h-[600px] max-h-[88vh]'
                ]"
            >
                <!-- Panel Header -->
                <div class="px-4 py-3 bg-slate-950 text-white flex items-center justify-between border-b border-slate-800 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center text-sm font-bold border border-brand-500/30">
                            🤖
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h3 class="text-xs font-bold text-white">Assistant AquawaSouk</h3>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            </div>
                            <p class="text-[10px] text-slate-400">Recherche, budget & commandes</p>
                        </div>
                    </div>

                    <!-- Window Controls -->
                    <div class="flex items-center gap-1 text-slate-400">
                        <button
                            type="button"
                            @click="isMinimized = !isMinimized"
                            class="p-1 hover:text-white rounded-lg hover:bg-slate-800 transition text-xs"
                            :title="isMinimized ? 'Agrandir' : 'Minimiser'"
                        >
                            {{ isMinimized ? '▢' : '—' }}
                        </button>
                        <button
                            type="button"
                            @click="isOpen = false"
                            class="p-1 hover:text-white rounded-lg hover:bg-slate-800 transition text-sm"
                            title="Fermer"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Main Body (Hidden if Minimized) -->
                <div v-show="!isMinimized" class="flex-1 flex flex-col min-h-0 bg-[#FAFBFD] dark:bg-slate-950">
                    <!-- Navigation Tabs -->
                    <div class="flex items-center justify-around px-3 py-1.5 bg-white dark:bg-slate-900 border-b border-slate-200/80 dark:border-slate-800 text-xs shrink-0">
                        <button
                            type="button"
                            @click="activeTab = 'chat'"
                            :class="[
                                'py-1 px-3 rounded-xl font-semibold transition flex items-center gap-1.5',
                                activeTab === 'chat'
                                    ? 'bg-brand-50 text-brand-600 dark:bg-slate-800 dark:text-brand-400'
                                    : 'text-slate-500 hover:text-slate-900 dark:text-slate-400'
                            ]"
                        >
                            <span>💬</span>
                            <span>Chat</span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'cart'; fetchCart()"
                            :class="[
                                'py-1 px-3 rounded-xl font-semibold transition flex items-center gap-1.5 relative',
                                activeTab === 'cart'
                                    ? 'bg-brand-50 text-brand-600 dark:bg-slate-800 dark:text-brand-400'
                                    : 'text-slate-500 hover:text-slate-900 dark:text-slate-400'
                            ]"
                        >
                            <span>🛒</span>
                            <span>Panier</span>
                            <span
                                v-if="cartCount > 0"
                                class="px-1.5 py-0.2 rounded-full bg-brand-600 text-white text-[9px] font-bold"
                            >
                                {{ cartCount }}
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'tracking'"
                            :class="[
                                'py-1 px-3 rounded-xl font-semibold transition flex items-center gap-1.5',
                                activeTab === 'tracking'
                                    ? 'bg-brand-50 text-brand-600 dark:bg-slate-800 dark:text-brand-400'
                                    : 'text-slate-500 hover:text-slate-900 dark:text-slate-400'
                            ]"
                        >
                            <span>📦</span>
                            <span>Suivi</span>
                        </button>
                    </div>

                    <!-- TAB 1: Chat Conversation -->
                    <div v-show="activeTab === 'chat'" class="flex-1 flex flex-col min-h-0">
                        <!-- Message stream -->
                        <ShoppingAssistantMessages
                            :messages="messages"
                            :is-typing="isTyping"
                            :cart="cart"
                            @add-to-cart="handleAddToCart"
                            @select-action="handleActionSelect"
                            @update-cart-quantity="handleUpdateQuantity"
                            @remove-cart-item="handleRemoveItem"
                        />

                        <!-- Bottom Quick Actions ticker -->
                        <div class="px-3 pt-1 pb-1.5 bg-white dark:bg-slate-900 border-t border-slate-200/80 dark:border-slate-800 shrink-0">
                            <ShoppingAssistantQuickActions
                                :actions="suggestedActions"
                                @select="handleActionSelect"
                            />
                        </div>

                        <!-- Chat Input Form -->
                        <form
                            @submit.prevent="sendMessage()"
                            class="p-3 bg-white dark:bg-slate-900 border-t border-slate-200/60 dark:border-slate-800 flex items-center gap-2 shrink-0"
                        >
                            <input
                                type="text"
                                v-model="inputMessage"
                                :disabled="isTyping"
                                placeholder="Posez votre question (ex: budget 50 DT, suivi...)"
                                class="flex-1 px-3.5 py-2 text-xs sm:text-sm rounded-2xl bg-slate-100 dark:bg-slate-800 border-none text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-brand-500"
                            />
                            <button
                                type="submit"
                                :disabled="!inputMessage.trim() || isTyping"
                                class="w-9 h-9 rounded-2xl bg-brand-600 hover:bg-brand-700 disabled:opacity-40 text-white flex items-center justify-center transition active:scale-95 shadow-sm shrink-0"
                                aria-label="Envoyer"
                            >
                                <svg class="w-4 h-4 transform rotate-90" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- TAB 2: Cart Summary View -->
                    <div v-show="activeTab === 'cart'" class="flex-1 p-4 overflow-y-auto">
                        <ShoppingAssistantCartSummary
                            :cart="cart"
                            @update-quantity="handleUpdateQuantity"
                            @remove-item="handleRemoveItem"
                            @continue-shopping="activeTab = 'chat'"
                        />
                    </div>

                    <!-- TAB 3: Order Tracking View -->
                    <div v-show="activeTab === 'tracking'" class="flex-1 p-4 overflow-y-auto space-y-4 text-left">
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1 flex items-center gap-1.5">
                                <span>📦</span>
                                <span>Suivi de Commande en direct</span>
                            </h4>
                            <p class="text-[11px] text-slate-500 mb-3">
                                Renseignez votre référence de commande pour consulter son statut d'expédition.
                            </p>

                            <form @submit.prevent="submitTracking" class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Numéro de commande (ex: ORD-XXXXXX)
                                    </label>
                                    <input
                                        type="text"
                                        v-model="trackingNumber"
                                        required
                                        placeholder="ORD-..."
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Numéro de téléphone (pour les invités)
                                    </label>
                                    <input
                                        type="tel"
                                        v-model="trackingPhone"
                                        placeholder="Numéro utilisé lors de la commande"
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    :disabled="trackingLoading"
                                    class="w-full py-2 px-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs flex items-center justify-center gap-1.5 shadow-sm transition disabled:opacity-50"
                                >
                                    <svg v-if="trackingLoading" class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Vérifier le statut</span>
                                </button>
                            </form>

                            <!-- Tracking Error -->
                            <div v-if="trackingError" class="mt-3 p-2.5 rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 text-xs border border-rose-200 dark:border-rose-800">
                                {{ trackingError }}
                            </div>

                            <!-- Tracking Result -->
                            <div v-if="trackingResult" class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-700">
                                    <span class="font-bold">#{{ trackingResult.order_number }}</span>
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-800">
                                        {{ trackingResult.status_label }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500">📅 Date : {{ trackingResult.date }}</p>
                                <p class="text-[11px] text-slate-500">📍 Destination : {{ trackingResult.city }}</p>
                                <p class="text-[11px] font-bold text-slate-800 dark:text-slate-200">
                                    Total : {{ Number(trackingResult.total_amount).toFixed(2) }} {{ trackingResult.currency }}
                                </p>
                                <a
                                    v-if="trackingResult.details_url"
                                    :href="trackingResult.details_url"
                                    class="inline-block mt-1 text-brand-600 dark:text-brand-400 font-semibold text-[11px] hover:underline"
                                >
                                    Consulter la commande complète →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
