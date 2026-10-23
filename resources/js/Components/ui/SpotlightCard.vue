<script setup>
import { ref } from 'vue';

defineProps({
    spotlightColor: {
        type: String,
        default: 'rgba(14, 165, 233, 0.12)', // brand sky-500 tint
    },
});

const cardRef = ref(null);
const mouseX = ref(0);
const mouseY = ref(0);
const isHovered = ref(false);

const handleMouseMove = (e) => {
    if (!cardRef.value) return;
    const rect = cardRef.value.getBoundingClientRect();
    mouseX.value = e.clientX - rect.left;
    mouseY.value = e.clientY - rect.top;
};
</script>

<template>
    <div
        ref="cardRef"
        @mousemove="handleMouseMove"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
        class="relative overflow-hidden rounded-3xl transition-all duration-300"
    >
        <!-- Skiper UI Dynamic Spotlight Hover Overlay -->
        <div
            v-if="isHovered"
            class="pointer-events-none absolute -inset-px transition-opacity duration-300 z-10 hidden sm:block"
            :style="{
                background: `radial-gradient(400px circle at ${mouseX}px ${mouseY}px, ${spotlightColor}, transparent 75%)`,
            }"
        />
        <slot />
    </div>
</template>
