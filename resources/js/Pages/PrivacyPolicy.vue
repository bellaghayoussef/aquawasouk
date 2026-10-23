<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SEO from '@/Components/SEO.vue';
</script>

<template>
    <AppLayout>
        <SEO
            :title="__('privacy_policy')"
            :description="__('privacy_policy_description')"
        />

        <div class="py-12 bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-12 text-center">
                    <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-dolphin-blue to-dolphin-cyan bg-clip-text text-transparent mb-4">
                        Politique de confidentialité
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
                            1. Introduction
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                Bienvenue sur Aquawasouk. Nous respectons votre vie privée et nous nous engageons à protéger vos données personnelles. 
                                Cette politique de confidentialité vous informe sur la manière dont nous traitons vos données personnelles lorsque 
                                vous visitez notre site web et vous informe de vos droits en matière de confidentialité.
                            </p>
                        </div>
                    </section>

                    <!-- Section 2 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            2. Données que nous collectons
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                                Nous pouvons collecter, utiliser, stocker et transférer différents types de données personnelles vous concernant :
                            </p>
                            <ul class="space-y-3 text-gray-700 dark:text-gray-300">
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span><strong class="text-gray-900 dark:text-white">Données d'identité :</strong> nom, prénom, nom d'utilisateur</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span><strong class="text-gray-900 dark:text-white">Données de contact :</strong> adresse e-mail, numéro de téléphone, adresse postale</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span><strong class="text-gray-900 dark:text-white">Données financières :</strong> informations de paiement</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span><strong class="text-gray-900 dark:text-white">Données techniques :</strong> adresse IP, type de navigateur, fuseau horaire</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span><strong class="text-gray-900 dark:text-white">Données d'utilisation :</strong> informations sur la façon dont vous utilisez notre site web</span>
                                </li>
                            </ul>
                        </div>
                    </section>

                    <!-- Section 3 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            3. Comment nous utilisons vos données
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                                Nous utilisons vos données personnelles pour :
                            </p>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="bg-gradient-to-br from-dolphin-blue/5 to-dolphin-cyan/5 dark:from-dolphin-blue/10 dark:to-dolphin-cyan/10 p-4 rounded-lg border border-dolphin-blue/20">
                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Gestion des commandes</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Traiter et livrer vos commandes</p>
                                </div>
                                <div class="bg-gradient-to-br from-dolphin-blue/5 to-dolphin-cyan/5 dark:from-dolphin-blue/10 dark:to-dolphin-cyan/10 p-4 rounded-lg border border-dolphin-blue/20">
                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Communication</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Vous envoyer des notifications importantes</p>
                                </div>
                                <div class="bg-gradient-to-br from-dolphin-blue/5 to-dolphin-cyan/5 dark:from-dolphin-blue/10 dark:to-dolphin-cyan/10 p-4 rounded-lg border border-dolphin-blue/20">
                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Amélioration du service</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Optimiser notre plateforme</p>
                                </div>
                                <div class="bg-gradient-to-br from-dolphin-blue/5 to-dolphin-cyan/5 dark:from-dolphin-blue/10 dark:to-dolphin-cyan/10 p-4 rounded-lg border border-dolphin-blue/20">
                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Sécurité</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Protéger votre compte et nos services</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section 4 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            4. Partage de vos données
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                Nous ne vendons jamais vos données personnelles. Nous pouvons partager vos données uniquement avec :
                            </p>
                            <ul class="mt-4 space-y-2 text-gray-700 dark:text-gray-300">
                                <li class="flex items-start">
                                    <span class="text-dolphin-blue mr-2">•</span>
                                    <span>Les vendeurs pour le traitement de vos commandes</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-dolphin-blue mr-2">•</span>
                                    <span>Les prestataires de services de paiement</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-dolphin-blue mr-2">•</span>
                                    <span>Les services de livraison</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-dolphin-blue mr-2">•</span>
                                    <span>Les autorités légales si requis par la loi</span>
                                </li>
                            </ul>
                        </div>
                    </section>

                    <!-- Section 5 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            5. Sécurité des données
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                Nous avons mis en place des mesures de sécurité appropriées pour empêcher que vos données personnelles 
                                ne soient accidentellement perdues, utilisées, modifiées, divulguées ou consultées de manière non autorisée. 
                                Nous utilisons le cryptage SSL/TLS pour protéger toutes les transmissions de données sensibles.
                            </p>
                        </div>
                    </section>

                    <!-- Section 6 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            6. Vos droits
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                                Conformément au RGPD, vous disposez des droits suivants :
                            </p>
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-gray-700 dark:to-gray-600 p-6 rounded-xl border border-dolphin-blue/30">
                                <ul class="space-y-3 text-gray-700 dark:text-gray-200">
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span><strong>Droit d'accès</strong> : obtenir une copie de vos données personnelles</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span><strong>Droit de rectification</strong> : corriger des données inexactes ou incomplètes</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span><strong>Droit à l'effacement</strong> : demander la suppression de vos données</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span><strong>Droit à la portabilité</strong> : recevoir vos données dans un format structuré</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-dolphin-blue mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span><strong>Droit d'opposition</strong> : vous opposer au traitement de vos données</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Section 7 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            7. Cookies
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                Notre site utilise des cookies pour améliorer votre expérience. Les cookies sont de petits fichiers texte 
                                placés sur votre appareil pour collecter des informations de journal Internet standard et des informations 
                                sur le comportement des visiteurs. Vous pouvez configurer votre navigateur pour refuser tous les cookies 
                                ou pour indiquer quand un cookie est envoyé.
                            </p>
                        </div>
                    </section>

                    <!-- Section 8 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-2 h-8 bg-gradient-to-b from-dolphin-blue to-dolphin-cyan rounded-full mr-3"></span>
                            8. Contact
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                                Pour toute question concernant cette politique de confidentialité ou pour exercer vos droits, 
                                veuillez nous contacter à :
                            </p>
                            <div class="bg-gradient-to-r from-dolphin-blue/10 to-dolphin-cyan/10 p-6 rounded-xl border border-dolphin-blue/30">
                                <div class="space-y-3">
                                    <div class="flex items-center text-gray-700 dark:text-gray-200">
                                        <svg class="w-6 h-6 text-dolphin-blue mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <a href="mailto:contact@aquawasouk.com" class="hover:text-dolphin-blue transition-colors">
                                            contact@aquawasouk.com
                                        </a>
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

                    <!-- Last Update Notice -->
                    <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                            Cette politique de confidentialité peut être mise à jour périodiquement. 
                            Nous vous encourageons à consulter cette page régulièrement pour rester informé.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
