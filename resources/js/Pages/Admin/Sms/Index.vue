<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    logs: Object,
    stats: Object,
    config: Object,
    vendorsList: {
        type: Array,
        default: () => [],
    },
});

// Tab state: 'send' | 'test' | 'settings'
const activeTab = ref('send');

// Composer Form
const sendForm = useForm({
    recipient_type: 'selected_vendors', // selected_vendors, custom, vendors, customers, all
    custom_recipients: '',
    selected_vendor_ids: [],
    activate_vendors: true,
    message: '',
});

// Vendor selection search & filters
const vendorSearch = ref('');
const vendorStatusFilter = ref('all'); // 'all', 'pending', 'active'

const filteredVendors = computed(() => {
    let list = props.vendorsList || [];
    if (vendorStatusFilter.value !== 'all') {
        list = list.filter(v => v.status === vendorStatusFilter.value);
    }
    if (vendorSearch.value.trim()) {
        const q = vendorSearch.value.toLowerCase().trim();
        list = list.filter(v => 
            (v.shop_name && v.shop_name.toLowerCase().includes(q)) ||
            (v.owner_name && v.owner_name.toLowerCase().includes(q)) ||
            (v.phone && v.phone.includes(q)) ||
            (v.owner_email && v.owner_email.toLowerCase().includes(q))
        );
    }
    return list;
});

const isAllVisibleSelected = computed(() => {
    if (filteredVendors.value.length === 0) return false;
    return filteredVendors.value.every(v => sendForm.selected_vendor_ids.includes(v.id));
});

const toggleSelectAllVisible = () => {
    if (isAllVisibleSelected.value) {
        const visibleIds = filteredVendors.value.map(v => v.id);
        sendForm.selected_vendor_ids = sendForm.selected_vendor_ids.filter(id => !visibleIds.includes(id));
    } else {
        const newIds = new Set(sendForm.selected_vendor_ids);
        filteredVendors.value.forEach(v => newIds.add(v.id));
        sendForm.selected_vendor_ids = Array.from(newIds);
    }
};

const selectAllPending = () => {
    vendorStatusFilter.value = 'pending';
    const pendingIds = (props.vendorsList || []).filter(v => v.status === 'pending').map(v => v.id);
    sendForm.selected_vendor_ids = pendingIds;
    // Auto-fill account activated template
    applyTemplate(templates[0]);
};

// Settings Form
const settingsForm = useForm({
    device_id: props.config.full_device_id || '',
    api_key: props.config.full_api_key || '',
});

// Test Connection Form
const testForm = useForm({
    test_phone: '',
});

// Character and segment calculation
const charCount = computed(() => sendForm.message.length);
const smsCount = computed(() => {
    if (charCount.value === 0) return 0;
    if (charCount.value <= 160) return 1;
    return Math.ceil(charCount.value / 153);
});

// Quick templates
const templates = [
    {
        label: '✅ Compte Vendeur Activé (FR)',
        text: 'Cher Partenaire AquawaSouk, votre compte vendeur a ete active avec succes ! Connectez-vous des maintenant pour ajouter vos produits : https://aquawasouk.com/login',
    },
    {
        label: '✅ تفعيل حساب البائع (AR)',
        text: 'شريكنا العزيز في AquawaSouk، تم تفعيل حساب البائع الخاص بك بنجاح! يمكنك الان تسجيل الدخول واضافة منتجاتك: https://aquawasouk.com/login',
    },
    {
        label: '📦 Suivi Commande',
        text: 'Bonjour ! Votre commande sur AquawaSouk est confirmee et en cours de preparation. Livraison sous 24-48h avec paiement en especes. Merci !',
    },
    {
        label: '🏪 Alerte Vendeur',
        text: 'Cher Partenaire AquawaSouk, vous avez une nouvelle commande en attente sur votre tableau de bord. Merci de la traiter rapidement.',
    },
    {
        label: '🎉 Promotion Flash',
        text: 'AquawaSouk : Vente flash exclusive aujourd\'hui ! Profitez de remises jusqu\'a -30% sur une large selection de produits. A bientot !',
    },
];

const applyTemplate = (tpl) => {
    sendForm.message = tpl.text;
};

// Handlers
const submitSend = () => {
    if (sendForm.recipient_type === 'selected_vendors' && sendForm.selected_vendor_ids.length === 0) {
        alert('Veuillez sélectionner au moins un vendeur dans la liste.');
        return;
    }

    sendForm.post(route('admin.sms.send'), {
        preserveScroll: true,
        onSuccess: () => {
            sendForm.reset('message');
            if (sendForm.recipient_type === 'custom') {
                sendForm.reset('custom_recipients');
            }
        },
    });
};

const submitSettings = () => {
    settingsForm.post(route('admin.sms.settings'), {
        preserveScroll: true,
    });
};

const submitTest = () => {
    testForm.post(route('admin.sms.test'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Passerelle SMS TextBee - Administration" />

    <DashboardLayout>
        <div class="space-y-6 max-w-7xl mx-auto pb-12">
            
            <!-- HEADER -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl border border-slate-800 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-2xl shadow-inner">
                            📱
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">Passerelle SMS TextBee</h1>
                                <span v-if="config.is_configured" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Connecté
                                </span>
                                <span v-else class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30">
                                    Non configuré
                                </span>
                            </div>
                            <p class="text-xs text-slate-300 mt-1">
                                Envoyez des SMS directement depuis votre passerelle Android réelle sans frais par message avec TextBee.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Device Info Pill -->
                <div class="relative z-10 flex items-center gap-3 bg-slate-800/80 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-slate-700/80 text-xs">
                    <div>
                        <span class="text-slate-400 text-[10px] block uppercase font-bold tracking-wider">Device ID</span>
                        <span class="font-mono text-emerald-400 font-bold">{{ config.device_id || 'Non assigné' }}</span>
                    </div>
                    <div class="h-6 w-px bg-slate-700"></div>
                    <div>
                        <span class="text-slate-400 text-[10px] block uppercase font-bold tracking-wider">Passerelle</span>
                        <span class="text-slate-200 font-semibold">textbee.dev</span>
                    </div>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">SMS Envoyés</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-1">{{ stats.total_sent }}</h3>
                        <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Délivrés avec succès</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        ✅
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Échecs d'envoi</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-1">{{ stats.total_failed }}</h3>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Erreurs réseau / numéros</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                        ⚠️
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vendeurs avec Tél.</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-1">{{ stats.vendors_count }}</h3>
                        <p class="text-[11px] text-sky-600 font-medium mt-0.5">Boutiques joignables</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl">
                        🏪
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Clients Commandes</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-1">{{ stats.customers_count }}</h3>
                        <p class="text-[11px] text-indigo-600 font-medium mt-0.5">Numéros uniques enregistrés</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        👥
                    </div>
                </div>
            </div>

            <!-- MAIN WORKSPACE: COMPOSER + SETTINGS TABS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- LEFT COLUMN: SMS COMPOSER (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">Composer un SMS</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Envoyez des alertes instantanées ou des diffusions groupées.</p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700">
                            {{ smsCount }} SMS ({{ charCount }} car.)
                        </span>
                    </div>

                    <form @submit.prevent="submitSend" class="space-y-5">
                        <!-- Recipient Type Radios -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">Destinataires du message</label>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                <label 
                                    :class="sendForm.recipient_type === 'selected_vendors' ? 'border-sky-500 bg-sky-50/70 text-sky-700 shadow-xs ring-2 ring-sky-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="border-2 rounded-2xl p-2.5 flex flex-col items-center justify-center cursor-pointer text-center transition"
                                >
                                    <input type="radio" value="selected_vendors" v-model="sendForm.recipient_type" class="sr-only" />
                                    <span class="text-base mb-1">🎯</span>
                                    <span class="text-xs font-bold">Vendeurs Choisis</span>
                                    <span class="text-[10px] text-sky-600 font-extrabold mt-0.5">({{ sendForm.selected_vendor_ids.length }} sélec.)</span>
                                </label>

                                <label 
                                    :class="sendForm.recipient_type === 'custom' ? 'border-sky-500 bg-sky-50/70 text-sky-700 shadow-xs ring-2 ring-sky-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="border-2 rounded-2xl p-2.5 flex flex-col items-center justify-center cursor-pointer text-center transition"
                                >
                                    <input type="radio" value="custom" v-model="sendForm.recipient_type" class="sr-only" />
                                    <span class="text-base mb-1">✍️</span>
                                    <span class="text-xs font-bold">Numéro précis</span>
                                </label>

                                <label 
                                    :class="sendForm.recipient_type === 'vendors' ? 'border-sky-500 bg-sky-50/70 text-sky-700 shadow-xs ring-2 ring-sky-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="border-2 rounded-2xl p-2.5 flex flex-col items-center justify-center cursor-pointer text-center transition"
                                >
                                    <input type="radio" value="vendors" v-model="sendForm.recipient_type" class="sr-only" />
                                    <span class="text-base mb-1">🏪</span>
                                    <span class="text-xs font-bold">Tous Vendeurs</span>
                                    <span class="text-[10px] text-slate-400 font-semibold mt-0.5">({{ stats.vendors_count }})</span>
                                </label>

                                <label 
                                    :class="sendForm.recipient_type === 'customers' ? 'border-sky-500 bg-sky-50/70 text-sky-700 shadow-xs ring-2 ring-sky-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="border-2 rounded-2xl p-2.5 flex flex-col items-center justify-center cursor-pointer text-center transition"
                                >
                                    <input type="radio" value="customers" v-model="sendForm.recipient_type" class="sr-only" />
                                    <span class="text-base mb-1">👥</span>
                                    <span class="text-xs font-bold">Clients</span>
                                    <span class="text-[10px] text-slate-400 font-semibold mt-0.5">({{ stats.customers_count }})</span>
                                </label>

                                <label 
                                    :class="sendForm.recipient_type === 'all' ? 'border-sky-500 bg-sky-50/70 text-sky-700 shadow-xs ring-2 ring-sky-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="border-2 rounded-2xl p-2.5 flex flex-col items-center justify-center cursor-pointer text-center transition"
                                >
                                    <input type="radio" value="all" v-model="sendForm.recipient_type" class="sr-only" />
                                    <span class="text-base mb-1">📢</span>
                                    <span class="text-xs font-bold">Diffusion Globale</span>
                                    <span class="text-[10px] text-slate-400 font-semibold mt-0.5">Tous</span>
                                </label>
                            </div>
                        </div>

                        <!-- Selected Vendors Interactive Selection Panel -->
                        <div v-if="sendForm.recipient_type === 'selected_vendors'" class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-3.5">
                            
                            <!-- Search & Status Filters -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="relative flex-1">
                                    <input 
                                        type="text" 
                                        v-model="vendorSearch" 
                                        placeholder="Filtrer par boutique, nom du gérant ou tél..." 
                                        class="w-full pl-9 pr-3 py-2 bg-white rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500 outline-hidden font-medium"
                                    />
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button 
                                        type="button" 
                                        @click="vendorStatusFilter = 'all'" 
                                        :class="vendorStatusFilter === 'all' ? 'bg-slate-800 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer"
                                    >
                                        Tous ({{ vendorsList.length }})
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="vendorStatusFilter = 'pending'" 
                                        :class="vendorStatusFilter === 'pending' ? 'bg-amber-500 text-white shadow-2xs' : 'bg-white text-amber-700 hover:bg-amber-50 border border-slate-200'"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer"
                                    >
                                        ⏳ En attente ({{ vendorsList.filter(v => v.status === 'pending').length }})
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="vendorStatusFilter = 'active'" 
                                        :class="vendorStatusFilter === 'active' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-slate-200'"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer"
                                    >
                                        ✅ Actifs ({{ vendorsList.filter(v => v.status === 'active').length }})
                                    </button>
                                </div>
                            </div>

                            <!-- Fast Action Bar -->
                            <div class="flex flex-wrap items-center justify-between text-xs gap-2 pt-1 border-t border-slate-200">
                                <div class="flex items-center gap-2.5">
                                    <button 
                                        type="button" 
                                        @click="toggleSelectAllVisible" 
                                        class="text-sky-600 font-bold hover:underline cursor-pointer"
                                    >
                                        {{ isAllVisibleSelected ? 'Désélectionner tout' : 'Sélectionner tous les ' + filteredVendors.length + ' affichés' }}
                                    </button>
                                    <span class="text-slate-300">•</span>
                                    <button 
                                        type="button" 
                                        @click="selectAllPending" 
                                        class="text-amber-700 font-bold hover:underline cursor-pointer flex items-center gap-1"
                                    >
                                        <span>⚡</span> <span>Sélectionner tous les "En attente"</span>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-slate-800 bg-white px-2.5 py-1 rounded-lg border border-slate-200 text-xs">
                                        📌 {{ sendForm.selected_vendor_ids.length }} sélectionné(s)
                                    </span>
                                    <button 
                                        v-if="sendForm.selected_vendor_ids.length > 0"
                                        type="button" 
                                        @click="sendForm.selected_vendor_ids = []" 
                                        class="text-[11px] text-slate-400 hover:text-rose-600 font-medium cursor-pointer"
                                    >
                                        Effacer
                                    </button>
                                </div>
                            </div>

                            <!-- Auto-Activation Checkbox -->
                            <div class="p-3 bg-gradient-to-r from-amber-50 to-emerald-50 rounded-xl border border-amber-200/90 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <input 
                                        type="checkbox" 
                                        id="activate_vendors_cb" 
                                        v-model="sendForm.activate_vendors" 
                                        class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300 cursor-pointer"
                                    />
                                    <label for="activate_vendors_cb" class="text-xs font-bold text-slate-800 cursor-pointer select-none">
                                        ⚡ Activer automatiquement le statut des vendeurs sélectionnés lors de l'envoi
                                    </label>
                                </div>
                                <span class="hidden sm:inline-block text-[11px] font-semibold text-slate-500">
                                    Passe le statut à "Actif"
                                </span>
                            </div>

                            <!-- Scrollable Vendors Checklist -->
                            <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                <div 
                                    v-for="v in filteredVendors" 
                                    :key="v.id"
                                    class="p-2.5 hover:bg-slate-50 flex items-center justify-between gap-3 transition"
                                    :class="sendForm.selected_vendor_ids.includes(v.id) ? 'bg-sky-50/40' : ''"
                                >
                                    <label class="flex items-center gap-3 cursor-pointer flex-1 truncate select-none">
                                        <input 
                                            type="checkbox" 
                                            :value="v.id" 
                                            v-model="sendForm.selected_vendor_ids" 
                                            class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300 cursor-pointer"
                                        />
                                        <div class="truncate">
                                            <div class="flex items-center gap-2">
                                                <span class="font-black text-xs text-slate-900 truncate">{{ v.shop_name }}</span>
                                                <span 
                                                    class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                                                    :class="v.status === 'active' ? 'bg-emerald-100 text-emerald-800' : (v.status === 'pending' ? 'bg-amber-100 text-amber-800 animate-pulse' : 'bg-slate-100 text-slate-700')"
                                                >
                                                    {{ v.status === 'active' ? 'Actif' : (v.status === 'pending' ? 'En attente' : v.status) }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 truncate">
                                                {{ v.owner_name }} <span v-if="v.owner_email" class="text-slate-400">({{ v.owner_email }})</span>
                                            </p>
                                        </div>
                                    </label>

                                    <div class="text-right shrink-0">
                                        <span v-if="v.phone" class="font-mono text-xs font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded-lg">
                                            {{ v.phone }}
                                        </span>
                                        <span v-else class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200">
                                            ⚠️ Pas de tél.
                                        </span>
                                    </div>
                                </div>

                                <div v-if="filteredVendors.length === 0" class="p-8 text-center text-slate-400 text-xs">
                                    Aucun vendeur ne correspond à votre recherche.
                                </div>
                            </div>
                        </div>

                        <!-- Custom Recipients Field -->
                        <div v-if="sendForm.recipient_type === 'custom'">
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Numéro(s) de téléphone (séparés par virgule ou saut de ligne) :
                            </label>
                            <input 
                                type="text" 
                                v-model="sendForm.custom_recipients" 
                                placeholder="ex: 26260262, +21698765432"
                                required
                                class="w-full px-4 py-2.5 rounded-2xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500 outline-hidden font-mono"
                            />
                            <p class="text-[11px] text-slate-400 mt-1">
                                💡 Les numéros tunisiens à 8 chiffres sont automatiquement formatés en +216.
                            </p>
                        </div>

                        <!-- Quick Templates Chips -->
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">
                                Modèles de messages rapides :
                            </span>
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-for="(tpl, idx) in templates" 
                                    :key="idx"
                                    type="button" 
                                    @click="applyTemplate(tpl)"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-sky-50 hover:text-sky-700 text-slate-700 text-xs font-medium border border-slate-200/80 transition cursor-pointer"
                                >
                                    {{ tpl.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Message Textarea -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">Contenu du message SMS</label>
                                <span class="text-[11px] text-slate-400">
                                    {{ charCount }} / 160 caractères
                                </span>
                            </div>
                            <textarea 
                                v-model="sendForm.message" 
                                rows="4" 
                                required
                                placeholder="Saisissez votre message ici..."
                                class="w-full px-4 py-3 rounded-2xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500 outline-hidden leading-relaxed"
                            ></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end">
                            <button 
                                type="submit" 
                                :disabled="sendForm.processing"
                                class="px-6 py-3 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white rounded-2xl text-xs font-bold shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 cursor-pointer flex items-center gap-2"
                            >
                                <span v-if="sendForm.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span>🚀 Envoyer le SMS maintenant</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- RIGHT COLUMN: TEST & SETTINGS (1 col) -->
                <div class="space-y-6">
                    
                    <!-- TEST CONNECTION CARD -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl">⚡</span>
                            <h3 class="text-sm font-extrabold text-slate-900">Test Instantané</h3>
                        </div>
                        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                            Envoyez un SMS de test pour vérifier la passerelle sur votre appareil Android.
                        </p>

                        <form @submit.prevent="submitTest" class="space-y-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Votre numéro mobile :</label>
                                <input 
                                    type="text" 
                                    v-model="testForm.test_phone" 
                                    placeholder="ex: 26260262" 
                                    required
                                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500 outline-hidden font-mono"
                                />
                            </div>
                            <button 
                                type="submit" 
                                :disabled="testForm.processing"
                                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
                            >
                                <span v-if="testForm.processing" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span>Envoyer le SMS test</span>
                            </button>
                        </form>
                    </div>

                    <!-- PARAMÈTRES TEXTBEE CARD -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl">⚙️</span>
                            <h3 class="text-sm font-extrabold text-slate-900">Configuration Passerelle</h3>
                        </div>
                        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                            Clés d'API issues de votre compte sur <a href="https://textbee.dev" target="_blank" class="text-sky-600 font-bold hover:underline">textbee.dev</a>.
                        </p>

                        <form @submit.prevent="submitSettings" class="space-y-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Device ID :</label>
                                <input 
                                    type="text" 
                                    v-model="settingsForm.device_id" 
                                    required
                                    placeholder="6a6c6b..."
                                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500 outline-hidden font-mono"
                                />
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">API Key :</label>
                                <input 
                                    type="password" 
                                    v-model="settingsForm.api_key" 
                                    required
                                    placeholder="txb_..."
                                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500 outline-hidden font-mono"
                                />
                            </div>

                            <button 
                                type="submit" 
                                :disabled="settingsForm.processing"
                                class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
                            >
                                <span v-if="settingsForm.processing" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span>Enregistrer les paramètres</span>
                            </button>
                        </form>
                    </div>

                </div>

            </div>

            <!-- LOGS TABLE -->
            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Historique des SMS envoyés</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Suivi en direct des expéditions par la passerelle Android.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                            <tr>
                                <th class="px-6 py-3.5">Date & Heure</th>
                                <th class="px-6 py-3.5">Destinataire(s)</th>
                                <th class="px-6 py-3.5">Message</th>
                                <th class="px-6 py-3.5">Statut</th>
                                <th class="px-6 py-3.5">Expéditeur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="log in (logs.data || [])" :key="log.id" class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                    {{ new Date(log.created_at).toLocaleString('fr-FR') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-900">
                                    {{ log.recipient }}
                                    <span v-if="log.recipients_count > 1" class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                        {{ log.recipients_count }} tél.
                                    </span>
                                </td>
                                <td class="px-6 py-4 max-w-md truncate text-slate-600" :title="log.message">
                                    {{ log.message }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="log.status === 'sent'" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ✓ Envoyé
                                    </span>
                                    <span v-else class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" :title="log.error_message">
                                        ✕ Échec
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-400">
                                    {{ log.sender ? log.sender.name : 'Système' }}
                                </td>
                            </tr>
                            <tr v-if="!(logs.data && logs.data.length)">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    Aucun historique de SMS pour le moment.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.links && logs.links.length > 3" class="p-4 border-t border-slate-100 flex items-center justify-center gap-1">
                    <button 
                        v-for="(link, lIdx) in logs.links" 
                        :key="lIdx"
                        :disabled="!link.url || link.active"
                        @click="router.visit(link.url)"
                        v-html="link.label"
                        :class="link.active ? 'bg-sky-600 text-white font-bold' : 'bg-slate-50 text-slate-700 hover:bg-slate-100'"
                        class="px-3 py-1.5 rounded-xl text-xs transition disabled:opacity-40 cursor-pointer"
                    ></button>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>
