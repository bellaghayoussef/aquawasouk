<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isVisible = ref(false);
const scrollProgress = ref(0);

const calculateScroll = () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    
    if (docHeight > 0) {
        scrollProgress.value = Math.min(100, Math.max(0, Math.round((scrollTop / docHeight) * 100)));
    }
    
    isVisible.value = scrollTop > 320;
};

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};

onMounted(() => {
    window.addEventListener('scroll', calculateScroll, { passive: true });
    calculateScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', calculateScroll);
});
</script>

<template>
    <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-6 scale-75"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-6 scale-75"
    >
        <button
            v-if="isVisible"
            @click="scrollToTop"
            type="button"
            aria-label="Retour en haut de page"
            class="fixed bottom-20 right-4 sm:bottom-8 sm:right-8 z-40 w-12 h-12 rounded-full flex items-center justify-center bg-white/90 dark:bg-slate-900/90 text-slate-700 dark:text-sky-400 backdrop-blur-md shadow-xl border border-slate-200/80 dark:border-slate-700/80 hover:border-brand-500 dark:hover:border-sky-400 hover:scale-110 active:scale-95 transition-transform duration-200 group"
        >
            <!-- Circular Progress Ring (SVG) -->
            <svg class="absolute inset-0 w-full h-full -rotate-90 pointer-events-none p-0.5" viewBox="0 0 36 36">
                <circle
                    cx="18"
                    cy="18"
                    r="15"
                    class="stroke-slate-200/50 dark:stroke-slate-800/80"
                    stroke-width="2.5"
                    fill="none"
                />
                <circle
                    cx="18"
                    cy="18"
                    r="15"
                    class="stroke-brand-500 dark:stroke-sky-400 transition-all duration-150 ease-out"
                    stroke-width="2.5"
                    stroke-dasharray="94.25"
                    :stroke-dashoffset="94.25 - (94.25 * scrollProgress) / 100"
                    stroke-linecap="round"
                    fill="none"
                />
            </svg>

            <!-- Arrow Icon -->
            <svg
                class="w-5 h-5 relative z-10 group-hover:-translate-y-0.5 transition-transform duration-200"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
        </button>
    </transition>
</template>
