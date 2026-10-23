<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isVisible = ref(false);
const currentIndex = ref(0);
const isDismissed = ref(false);

const notifications = [
    { city: 'Tunis', item: 'Pack Soins & Bien-être', time: 'il y a 4 min', icon: 'truck' },
    { city: 'Sousse', item: 'Accessoire High-Tech vérifié', time: 'il y a 8 min', icon: 'bag' },
    { city: 'Sfax', item: 'Article artisanal certifié', time: 'il y a 12 min', icon: 'check' },
    { city: 'Nabeul', item: 'Commande confirmée (COD)', time: 'il y a 15 min', icon: 'truck' }
];

let timer = null;

const dismiss = () => {
    isVisible.value = false;
    isDismissed.value = true;
    try {
        sessionStorage.setItem('aquawasouk_dismiss_toast', 'true');
    } catch (e) {}
};

onMounted(() => {
    try {
        if (sessionStorage.getItem('aquawasouk_dismiss_toast') === 'true') {
            return;
        }
    } catch (e) {}

    // First display after 4 seconds
    setTimeout(() => {
        if (!isDismissed.value) {
            isVisible.value = true;
        }
    }, 4000);

    // Auto rotate every 16 seconds
    timer = setInterval(() => {
        if (isDismissed.value) return;
        
        isVisible.value = false;
        setTimeout(() => {
            if (!isDismissed.value) {
                currentIndex.value = (currentIndex.value + 1) % notifications.length;
                isVisible.value = true;
            }
        }, 600);
    }, 16000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <transition
        enter-active-class="transition-all duration-500 cubic-bezier(0.16, 1, 0.3, 1)"
        enter-from-class="opacity-0 translate-y-8 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-8 scale-95"
    >
        <div
            v-if="isVisible && !isDismissed"
            class="hidden sm:flex fixed bottom-6 left-6 z-40 max-w-sm items-center gap-3.5 p-3.5 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-200/90 dark:border-slate-800 shadow-2xl hover:shadow-brand-500/10 transition-all duration-300"
        >
            <!-- Animated Icon Beacon -->
            <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-sky-400 text-white shrink-0 shadow-md">
                <svg v-if="notifications[currentIndex].icon === 'truck'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4zM2 5h11v9H2V5zm11 3h4.172a2 2 0 011.414.586l2.828 2.828A2 2 0 0122 12.828V14h-3M2 14h2m7 0h3"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full animate-pulse"></span>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0 pr-1">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                        Activité en direct
                    </span>
                    <span class="text-[10px] text-slate-400">{{ notifications[currentIndex].time }}</span>
                </div>
                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate mt-0.5">
                    {{ notifications[currentIndex].item }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Acheteur à <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ notifications[currentIndex].city }}</strong>
                </p>
            </div>

            <!-- Dismiss Button -->
            <button
                @click="dismiss"
                type="button"
                aria-label="Fermer la notification"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </transition>
</template>
