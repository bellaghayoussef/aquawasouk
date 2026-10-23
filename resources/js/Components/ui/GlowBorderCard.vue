<script setup>
defineProps({
    glowColor: {
        type: String,
        default: 'from-sky-500/20 via-brand-500/10 to-transparent',
    },
});
</script>

<template>
    <div
        class="group relative rounded-3xl p-px overflow-hidden transition-all duration-300 hover:shadow-xl"
    >
        <!-- Vengeance UI Subtle Animated Gradient Border -->
        <div
            :class="[
                'absolute -inset-px rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none bg-gradient-to-br',
                glowColor
            ]"
        />
        
        <!-- Content Container -->
        <div class="relative h-full w-full rounded-[calc(1.5rem-1px)] bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7">
            <slot />
        </div>
    </div>
</template>
