<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    webhookUrl: String,
    verifyToken: String,
});

const channel = ref('instagram'); // 'instagram' or 'facebook'
const senderId = ref('sim_user_001');
const senderName = ref('Mohamed Trabelsi');
const inputMessage = ref('');
const isSending = ref(false);
const chatHistory = ref([
    {
        sender: 'bot',
        text: '👋 Aslema ! Bienvenue sur le simulateur Social Bot (Instagram DM & Messenger). Vous pouvez tester la conversation et le passage automatique de commande comme si vous étiez sur Instagram ou Facebook !',
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }
]);

const chatContainer = ref(null);

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const sendMessage = async (customText = null) => {
    const text = customText || inputMessage.value;
    if (!text.trim() || isSending.value) return;

    // Add user message to UI
    chatHistory.value.push({
        sender: 'user',
        text: text,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    });

    inputMessage.value = '';
    isSending.value = true;
    await scrollToBottom();

    try {
        const response = await axios.post('/api/social-bot/simulate', {
            channel: channel.value,
            sender_id: senderId.value,
            sender_name: senderName.value,
            message: text,
        });

        if (response.data.success) {
            chatHistory.value.push({
                sender: 'bot',
                text: response.data.reply,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });
        }
    } catch (error) {
        chatHistory.value.push({
            sender: 'bot',
            text: '❌ Erreur de communication avec le bot.',
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        });
    } finally {
        isSending.value = false;
        await scrollToBottom();
    }
};

const resetChat = async () => {
    if (confirm('Réinitialiser la conversation et le panier du bot ?')) {
        await axios.post('/api/social-bot/reset', {
            channel: channel.value,
            sender_id: senderId.value,
        });
        chatHistory.value = [
            {
                sender: 'bot',
                text: '🔄 Conversation réinitialisée. Quel produit souhaitez-vous commander ?',
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            }
        ];
    }
};

const quickActions = [
    'Aslema ! Fama s24 ultra ?',
    'B9adech el prix ?',
    'Nheb ncommandi',
    'Mohamed Trabelsi',
    '98123456',
    'Ariana, Ennasr 2, Rue Hédi Nouira',
    'OUI'
];
</script>

<template>
    <Head title="Bot Social Media (Instagram & Facebook)" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight flex items-center gap-3">
                        <span class="p-2 rounded-xl bg-gradient-to-tr from-pink-500 to-indigo-600 text-white shadow-md">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </span>
                        Assistant Social Commerce (Instagram DM & Facebook Messenger)
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Génération automatique de commandes réelles par message privé.</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                    <!-- Left Column: Webhook Setup & Credentials -->
                    <div class="lg:col-span-5 space-y-6">
                        <!-- Setup Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <span>🔗</span> Paramètres Webhook Meta
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                                Renseignez ces informations dans votre <strong>Meta for Developers</strong> (Page Facebook & Instagram Business) pour connecter le bot en direct.
                            </p>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase mb-1">Callback URL (Webhook)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="text" readonly :value="webhookUrl" class="w-full text-xs font-mono bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 text-gray-800 dark:text-gray-200 select-all" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase mb-1">Verify Token</label>
                                    <input type="text" readonly :value="verifyToken" class="w-full text-xs font-mono bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 text-gray-800 dark:text-gray-200 select-all" />
                                </div>

                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Champs d'abonnement Meta :</h4>
                                    <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1.5 list-disc list-inside">
                                        <li><code>messages</code> (Messenger & Instagram)</li>
                                        <li><code>messaging_postbacks</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- How it Works Card -->
                        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-indigo-100 dark:border-gray-700 p-6">
                            <h3 class="text-base font-bold text-indigo-900 dark:text-indigo-300 mb-3 flex items-center gap-2">
                                <span>⚡</span> Étapes de conversion de commande
                            </h3>
                            <ol class="text-xs text-indigo-900/80 dark:text-gray-300 space-y-2 list-decimal list-inside leading-relaxed">
                                <li><strong>Discussion :</strong> Le client pose une question en Darija ou Français.</li>
                                <li><strong>Recherche :</strong> Le bot vérifie les stocks et le prix réel en DT.</li>
                                <li><strong>Collecte :</strong> Le bot demande le Nom, Téléphone tunisien et Gouvernorat.</li>
                                <li><strong>Création :</strong> Dès confirmation ("OUI"), la commande <code>ORD-XXXXX</code> est créée immédiatement en base de données avec paiement à la livraison (COD) !</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Right Column: Live Interactive Chat Simulator -->
                    <div class="lg:col-span-7">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col h-[700px]">
                            <!-- Simulator Header -->
                            <div class="bg-gray-900 text-white p-4 flex items-center justify-between border-b border-gray-800">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white shadow"
                                             :class="channel === 'instagram' ? 'bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600' : 'bg-blue-600'">
                                            <span v-if="channel === 'instagram'">📸</span>
                                            <span v-else>💬</span>
                                        </div>
                                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-gray-900 rounded-full"></span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm">AquawaSouk Social Bot</h4>
                                        <p class="text-xs text-gray-400">En ligne • Répond instantanément</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <select v-model="channel" class="text-xs bg-gray-800 border-gray-700 rounded-lg text-white py-1 px-2.5 focus:ring-1 focus:ring-indigo-500">
                                        <option value="instagram">Instagram Direct</option>
                                        <option value="facebook">Facebook Messenger</option>
                                    </select>
                                    <button @click="resetChat" class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 py-1 px-2.5 rounded-lg transition" title="Réinitialiser">
                                        🔄 Reset
                                    </button>
                                </div>
                            </div>

                            <!-- Chat Messages Window -->
                            <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50 dark:bg-gray-900/50">
                                <div v-for="(msg, index) in chatHistory" :key="index"
                                     class="flex flex-col"
                                     :class="msg.sender === 'user' ? 'items-end' : 'items-start'">
                                    <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm shadow-sm leading-relaxed whitespace-pre-line"
                                         :class="msg.sender === 'user'
                                            ? (channel === 'instagram' ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-br-none' : 'bg-blue-600 text-white rounded-br-none')
                                            : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-bl-none'">
                                        {{ msg.text }}
                                    </div>
                                    <span class="text-[10px] text-gray-400 mt-1 px-1">{{ msg.time }}</span>
                                </div>

                                <div v-if="isSending" class="flex items-center gap-2 text-xs text-gray-400">
                                    <div class="w-2 h-2 rounded-full bg-gray-400 animate-pulse"></div>
                                    <span>Le bot prépare sa réponse...</span>
                                </div>
                            </div>

                            <!-- Quick Action Simulation Buttons -->
                            <div class="p-2.5 bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 overflow-x-auto flex gap-2 no-scrollbar">
                                <button v-for="(action, idx) in quickActions" :key="idx"
                                        @click="sendMessage(action)"
                                        class="text-xs whitespace-nowrap bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-3 py-1.5 rounded-full border border-gray-300 dark:border-gray-600 shadow-sm transition">
                                    {{ action }}
                                </button>
                            </div>

                            <!-- Chat Input Bar -->
                            <form @submit.prevent="sendMessage()" class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2">
                                <input v-model="inputMessage"
                                       type="text"
                                       placeholder="Tapez un message en Darija ou Français..."
                                       class="flex-1 bg-gray-100 dark:bg-gray-900 border-0 rounded-full px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" />
                                <button type="submit"
                                        :disabled="isSending || !inputMessage.trim()"
                                        class="w-10 h-10 rounded-full flex items-center justify-center text-white transition disabled:opacity-50"
                                        :class="channel === 'instagram' ? 'bg-gradient-to-tr from-pink-500 to-purple-600' : 'bg-blue-600'">
                                    <svg class="w-4 h-4 transform rotate-90" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
