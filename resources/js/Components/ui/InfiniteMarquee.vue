<script setup>
defineProps({
    speed: {
        type: String,
        default: '30s',
    },
    pauseOnHover: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div class="relative w-full overflow-hidden [mask-image:linear-gradient(to_right,transparent,white_10%,white_90%,transparent)]">
        <div
            :class="[
                'animate-marquee-track flex items-center gap-6 sm:gap-10 py-3',
                pauseOnHover ? 'hover:[animation-play-state:paused]' : ''
            ]"
            :style="{ animationDuration: speed }"
        >
            <div class="flex items-center gap-6 sm:gap-10 shrink-0">
                <slot />
            </div>
            <!-- Duplicate for seamless looping -->
            <div class="flex items-center gap-6 sm:gap-10 shrink-0" aria-hidden="true">
                <slot />
            </div>
        </div>
    </div>
</template>
