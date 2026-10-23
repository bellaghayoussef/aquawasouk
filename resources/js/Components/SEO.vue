<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'AquawaSouk - Marketplace E-commerce Multi-Catégories en Tunisie'
    },
    description: {
        type: String,
        default: 'AquawaSouk - Votre marketplace e-commerce multi-catégories n°1 en Tunisie. Découvrez des milliers de produits : High-Tech, Mode & Vêtements, Maison & Cuisine, Beauté, Bricolage, Sport et plus au meilleur prix avec livraison partout en Tunisie.'
    },
    keywords: {
        type: String,
        default: 'marketplace Tunisie, e-commerce Tunisie, achat en ligne, vente en ligne, high-tech, smartphones, mode homme femme, électroménager, maison et cuisine, parfums, sport, bricolage, shopping Tunisie, boutique en ligne, livraison domicile, paiement à la livraison'
    },
    image: {
        type: String,
        default: '/uploads/cover.jpeg'
    },
    url: {
        type: String,
        default: ''
    },
});

const fullTitle = computed(() => {
    return props.title.includes('AquawaSouk') || props.title.includes('Aquawasouk')
        ? props.title
        : `${props.title} | AquawaSouk`;
});

const fullUrl = computed(() => {
    return props.url || window.location.href;
});
</script>

<template>
    <Head>
        <!-- Basic Meta Tags -->
        <title>{{ fullTitle }}</title>
        <meta name="description" :content="description" />
        <meta name="keywords" :content="keywords" />
        <meta name="author" content="AquawaSouk" />
        <meta name="robots" content="index, follow" />
        <link rel="canonical" :href="fullUrl" />

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="fullUrl" />
        <meta property="og:title" :content="fullTitle" />
        <meta property="og:description" :content="description" />
        <meta property="og:image" :content="image" />
        <meta property="og:site_name" content="AquawaSouk" />
        <meta property="og:locale" content="fr_TN" />

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:url" :content="fullUrl" />
        <meta property="twitter:title" :content="fullTitle" />
        <meta property="twitter:description" :content="description" />
        <meta property="twitter:image" :content="image" />

        <!-- Additional SEO -->
        <meta name="theme-color" content="#0284c7" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    </Head>
</template>
