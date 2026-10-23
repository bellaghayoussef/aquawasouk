<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SEO from '@/Components/SEO.vue';
</script>

<template>
    <AppLayout>
        <SEO
            :title="__('terms_of_service')"
            :description="__('terms_of_service_description')"
        />

        <div class="py-12 bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-12 text-center">
                    <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-dolphin-blue to-dolphin-cyan bg-clip-text text-transparent mb-4">
                        Conditions d'utilisation
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 text-lg">
                        Dernière mise à jour : {{ new Date().toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                    </p>
                </div>

                <!-- Content -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 md:p-12 space-y-8">
                    <!-- Section 1 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            1. Acceptation des conditions
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                En accédant et en utilisant Aquawasouk, vous acceptez d'être lié par ces conditions d'utilisation, 
                                toutes les lois et réglementations applicables, et vous acceptez d'être responsable du respect de toutes 
                                les lois locales applicables. Si vous n'acceptez pas l'une de ces conditions, vous n'êtes pas autorisé 
                                à utiliser ou à accéder à ce site.
                            </p>
                        </div>
                    </section>

                    <!-- Section 2 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            2. Utilisation de la plateforme
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                                Aquawasouk est une plateforme de commerce électronique qui met en relation acheteurs et vendeurs. 
                                En utilisant notre plateforme, vous vous engagez à :
                            </p>
                            <div class="space-y-3">
                                <div class="flex items-start bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-4 rounded-lg border-l-4 border-green-500">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-200">Fournir des informations exactes et à jour lors de l'inscription</span>
                                </div>
                                <div class="flex items-start bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-4 rounded-lg border-l-4 border-green-500">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-200">Maintenir la sécurité de votre mot de passe et de votre compte</span>
                                </div>
                                <div class="flex items-start bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-4 rounded-lg border-l-4 border-green-500">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-200">Ne pas utiliser la plateforme à des fins illégales ou non autorisées</span>
                                </div>
                                <div class="flex items-start bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-4 rounded-lg border-l-4 border-green-500">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-200">Respecter les droits de propriété intellectuelle d'autrui</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section 3 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            3. Comptes utilisateurs
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                                Pour utiliser certaines fonctionnalités de notre plateforme, vous devez créer un compte. 
                                Vous êtes responsable de :
                            </p>
                            <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                                <li class="flex items-start">
                                    <span class="text-dolphin-blue mr-3 font-bold">→</span>
                                    <span>La confidentialité de vos informations de connexion</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-dolphin-blue mr-3 font-bold">→</span>
                                    <span>Toutes les activités effectuées sous votre compte</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-dolphin-blue mr-3 font-bold">→</span>
                                    <span>Notifier immédiatement Aquawasouk de toute utilisation non autorisée de votre compte</span>
                                </li>
                            </ul>
                        </div>
                    </section>

                    <!-- Section 4 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            4. Achats et paiements
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 p-6 rounded-xl border border-dolphin-blue/30">
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-3 text-lg">Conditions de commande :</h3>
                                <ul class="space-y-3 text-gray-700 dark:text-gray-200">
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Toutes les commandes sont soumises à disponibilité et confirmation du prix</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Les prix affichés incluent la TVA le cas échéant</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Le paiement doit être effectué au moment de la commande</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Nous nous réservons le droit de refuser ou d'annuler toute commande</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Section 5 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            5. Livraison et retours
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <div class="grid md:grid-cols-2 gap-4 mb-4">
                                <div class="bg-gradient-to-br from-dolphin-blue/10 to-dolphin-cyan/10 p-5 rounded-xl border border-dolphin-blue/20">
                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                                        <svg class="w-6 h-6 text-dolphin-blue mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        Livraison
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        Les délais de livraison varient selon le vendeur et votre localisation. 
                                        Consultez les détails lors de la commande.
                                    </p>
                                </div>
                                <div class="bg-gradient-to-br from-dolphin-blue/10 to-dolphin-cyan/10 p-5 rounded-xl border border-dolphin-blue/20">
                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                                        <svg class="w-6 h-6 text-dolphin-blue mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        Retours
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        Vous disposez de 14 jours pour retourner un produit non conforme. 
                                        Consultez notre politique de retour complète.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section 6 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            6. Vendeurs sur la plateforme
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                                Les vendeurs utilisant Aquawasouk s'engagent à :
                            </p>
                            <div class="space-y-3">
                                <div class="flex items-start bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg border-l-4 border-amber-500">
                                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-200">Fournir des descriptions de produits exactes et véridiques</span>
                                </div>
                                <div class="flex items-start bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg border-l-4 border-amber-500">
                                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-200">Respecter les délais de livraison annoncés</span>
                                </div>
                                <div class="flex items-start bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg border-l-4 border-amber-500">
                                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-200">Gérer les retours et remboursements de manière professionnelle</span>
                                </div>
                                <div class="flex items-start bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg border-l-4 border-amber-500">
                                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-200">Ne vendre que des produits légaux et conformes</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section 7 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            7. Propriété intellectuelle
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                Tout le contenu présent sur Aquawasouk, y compris mais sans s'y limiter, le texte, les graphiques, 
                                les logos, les icônes, les images, les clips audio, les téléchargements numériques, et les compilations 
                                de données, est la propriété d'Aquawasouk ou de ses fournisseurs de contenu et est protégé par les lois 
                                tunisiennes et internationales sur le droit d'auteur.
                            </p>
                        </div>
                    </section>

                    <!-- Section 8 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            8. Limitation de responsabilité
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <div class="bg-red-50 dark:bg-red-900/20 p-6 rounded-xl border-l-4 border-red-500">
                                <p class="text-gray-700 dark:text-gray-200 leading-relaxed">
                                    Aquawasouk agit en tant qu'intermédiaire entre acheteurs et vendeurs. Nous ne sommes pas responsables 
                                    de la qualité, de la sécurité, de la légalité ou de tout autre aspect des produits vendus par les vendeurs 
                                    tiers. Les transactions sont effectuées directement entre acheteurs et vendeurs.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Section 9 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            9. Résiliation
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                Nous nous réservons le droit de résilier ou de suspendre votre compte et votre accès à la plateforme, 
                                sans préavis ni responsabilité, pour toute raison, y compris, sans limitation, si vous violez les 
                                conditions d'utilisation.
                            </p>
                        </div>
                    </section>

                    <!-- Section 10 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            10. Modifications des conditions
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                Nous nous réservons le droit de modifier ces conditions à tout moment. Les modifications entreront en vigueur 
                                dès leur publication sur le site. Votre utilisation continue du site après la publication de modifications 
                                constitue votre acceptation de ces modifications.
                            </p>
                        </div>
                    </section>

                    <!-- Section 11 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            11. Droit applicable
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                Ces conditions d'utilisation sont régies et interprétées conformément aux lois de la Tunisie. 
                                Tout litige découlant de ces conditions sera soumis à la juridiction exclusive des tribunaux tunisiens.
                            </p>
                        </div>
                    </section>

                    <!-- Section 12 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            12. Contact
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                                Pour toute question concernant ces conditions d'utilisation, veuillez nous contacter :
                            </p>
                            <div class="bg-gradient-to-r from-dolphin-blue/10 to-dolphin-cyan/10 p-6 rounded-xl border border-dolphin-blue/30">
                                <div class="space-y-3">
                                    <div class="flex items-center text-gray-700 dark:text-gray-200">
                                        <svg class="w-6 h-6 text-dolphin-blue mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <a href="mailto:contact@aquawasouk.com" class="hover:text-dolphin-blue transition-colors font-medium">
                                            contact@aquawasouk.com
                                        </a>
                                    </div>
                                    <div class="flex items-center text-gray-700 dark:text-gray-200">
                                        <svg class="w-6 h-6 text-dolphin-blue mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <span class="font-medium">+216 XX XXX XXX</span>
                                    </div>
                                    <div class="flex items-center text-gray-700 dark:text-gray-200">
                                        <svg class="w-6 h-6 text-dolphin-blue mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>Aquawasouk, Tunis, Tunisie</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Agreement Notice -->
                    <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <div class="bg-gradient-to-r from-dolphin-blue/20 to-dolphin-cyan/20 p-6 rounded-xl border border-dolphin-blue/40">
                            <p class="text-center text-gray-700 dark:text-gray-200 font-medium">
                                En utilisant Aquawasouk, vous reconnaissez avoir lu, compris et accepté ces conditions d'utilisation.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
