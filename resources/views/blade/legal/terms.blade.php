@extends('blade.layout')

@section('title', 'Conditions Générales d\'Utilisation - AquawaSouk Tunisie')
@section('meta_description', 'Conditions générales d\'utilisation et de vente de la marketplace AquawaSouk en Tunisie.')

@section('content')

    <div class="bg-white border-b border-slate-200 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Conditions Générales d'Utilisation (CGU)</h1>
            <p class="text-xs text-slate-500 mt-1">Dernière mise à jour : {{ date('d/m/Y') }}</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-12 shadow-xs space-y-6 text-xs sm:text-sm text-slate-700 leading-relaxed">
            <section class="space-y-2">
                <h2 class="text-base font-extrabold text-slate-900">1. Présentation de la plateforme</h2>
                <p>AquawaSouk est une place de marché numérique mettant en relation des vendeurs professionnels ou indépendants et des acheteurs résidant en Tunisie.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-base font-extrabold text-slate-900">2. Commandes & Prix</h2>
                <p>Les prix affichés sur le site sont exprimés en Dinars Tunisiens (TND / DT) toutes taxes comprises. La validation de la commande engage le client à réceptionner son colis et à régler le montant exact auprès du livreur en espèces (COD).</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-base font-extrabold text-slate-900">3. Modalités de Livraison</h2>
                <p>Les commandes sont expédiées à l'adresse fournie par l'acheteur dans un délai moyen de 24 à 48 heures ouvrables. En cas d'indisponibilité temporaire d'un produit, l'acheteur en est notifié dans les plus brefs délais.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-base font-extrabold text-slate-900">4. Retours & Réclamations</h2>
                <p>Tout article non conforme ou endommagé lors de la livraison peut faire l'objet d'une réclamation auprès de notre service d'assistance client sous 48 heures après réception.</p>
            </section>
        </div>
    </div>

@endsection
