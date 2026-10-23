<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    banners: {
        type: Array,
        default: () => [],
    },
});

const currentBannerIndex = ref(0);
let bannerInterval = null;

const startBannerTimer = () => {
    if (props.banners && props.banners.length > 1) {
        bannerInterval = setInterval(() => {
            currentBannerIndex.value = (currentBannerIndex.value + 1) % props.banners.length;
        }, 5000);
    }
};

onMounted(() => {
    startBannerTimer();
});

onUnmounted(() => {
    if (bannerInterval) clearInterval(bannerInterval);
});
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dynamic Banners -->
        <div v-if="banners && banners.length > 0" class="relative rounded-3xl overflow-hidden shadow-lg group h-48 md:h-96">
            <div v-for="(banner, index) in banners" :key="banner.id" 
                 class="absolute inset-0 transition-opacity duration-700 ease-in-out"
                 :class="currentBannerIndex === index ? 'opacity-100 z-10' : 'opacity-0 z-0'">
                <component :is="banner.link_url ? 'a' : 'div'" :href="banner.link_url" :target="banner.link_url ? '_blank' : null" class="h-full w-full block">
                     <img :src="banner.image_url" :alt="banner.title" class="w-full h-full object-cover" />
                </component>
            </div>
            
            <!-- Indicators -->
            <div v-if="banners.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 z-20">
                <button v-for="(_, index) in banners" :key="index" @click="currentBannerIndex = index" 
                    class="w-3 h-3 rounded-full transition-colors duration-300"
                    :class="currentBannerIndex === index ? 'bg-white' : 'bg-white/50 hover:bg-white/80'"></button>
            </div>
        </div>

        <!-- Fallback Static Banner -->
       
    </div>
</template>
