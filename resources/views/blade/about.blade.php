@extends('blade.layout')

@section('title', 'À Propos - AquawaSouk Marketplace Tunisie')
@section('meta_description', 'Découvrez la vision d\'AquawaSouk, la marketplace e-commerce multi-catégories qui soutient les commerçants tunisiens.')

@section('content')

    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-900 to-sky-950 text-white py-16 text-center">
        <div class="max-w-4xl mx-auto px-4">
            <span class="text-xs uppercase font-extrabold tracking-widest text-sky-400">Notre Histoire & Mission</span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight mt-3">À Propos d'AquawaSouk</h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-4 leading-relaxed max-w-2xl mx-auto">
                La plateforme tunisienne qui modernise l'expérience d'achat et de vente en ligne, en alliant confiance, rapidité et proximité.
            </p>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
        
        <!-- Story section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
            <div class="space-y-4 text-xs sm:text-sm text-slate-600 leading-relaxed">
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Une Marketplace Conçue pour la Tunisie</h2>
                <p>
                    <strong>AquawaSouk</strong> est né d'un constat simple : les consommateurs tunisiens recherchent un espace unique où trouver des produits fiables, vérifiés, avec un service de livraison rapide et transparent.
                </p>
                <p>
                    Parallèlement, nous offrons aux commerçants, marques locales et artisans tunisiens une visibilité exceptionnelle pour écouler leurs stocks sans contraintes techniques complexes.
                </p>
                <p>
                    Notre modèle repose sur la confiance : vérification stricte des marchands, emballage soigné et <strong>paiement à la livraison</strong> dans l'ensemble des 24 gouvernorats.
                </p>
            </div>

            <div class="rounded-3xl bg-gradient-to-br from-sky-500/10 to-indigo-500/10 border border-sky-100 p-8 text-center space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div class="p-4 bg-white rounded-2xl shadow-xs">
                        <p class="text-3xl font-extrabold text-sky-600">24/24</p>
                        <p class="text-xs text-slate-500 mt-1">Gouvernorats couverts</p>
                    </div>
                    <div class="p-4 bg-white rounded-2xl shadow-xs">
                        <p class="text-3xl font-extrabold text-indigo-600">100%</p>
                        <p class="text-xs text-slate-500 mt-1">Paiement sécurisé COD</p>
                    </div>
                    <div class="p-4 bg-white rounded-2xl shadow-xs">
                        <p class="text-3xl font-extrabold text-emerald-600">+10K</p>
                        <p class="text-xs text-slate-500 mt-1">Clients satisfaits</p>
                    </div>
                    <div class="p-4 bg-white rounded-2xl shadow-xs">
                        <p class="text-3xl font-extrabold text-amber-500">48h</p>
                        <p class="text-xs text-slate-500 mt-1">Délai moyen livraison</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Values -->
        <div class="pt-8">
            <h2 class="text-2xl font-extrabold text-slate-900 text-center tracking-tight mb-8">Nos Piliers Fondamentaux</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
                    <span class="text-3xl">🛡️</span>
                    <h3 class="text-base font-bold text-slate-900">Transparence & Qualité</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Chaque fiche produit est détaillée avec exactitude. Pas de surprises désagréables à la réception.
                    </p>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
                    <span class="text-3xl">⚡</span>
                    <h3 class="text-base font-bold text-slate-900">Rapidité d'Expédition</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Nous collaborons avec les meilleurs transporteurs locaux pour assurer des délais express en Tunisie.
                    </p>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
                    <span class="text-3xl">🤝</span>
                    <h3 class="text-base font-bold text-slate-900">Support Client Humain</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Une équipe disponible à Tunis pour répondre à vos questions par téléphone et messagerie instantanée.
                    </p>
                </div>
            </div>
        </div>

    </div>

@endsection
