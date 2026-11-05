@extends('blade.layout')

@section('title', 'Politique de Confidentialité - AquawaSouk Tunisie')
@section('meta_description', 'Politique de confidentialité et protection des données personnelles sur AquawaSouk Tunisie.')

@section('content')

    <div class="bg-white border-b border-slate-200 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Politique de Confidentialité</h1>
            <p class="text-xs text-slate-500 mt-1">Dernière mise à jour : {{ date('d/m/Y') }}</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-12 shadow-xs space-y-6 text-xs sm:text-sm text-slate-700 leading-relaxed">
            <section class="space-y-2">
                <h2 class="text-base font-extrabold text-slate-900">1. Collecte des données</h2>
                <p>AquawaSouk accorde une importance primordiale au respect et à la protection de la vie privée de ses utilisateurs en Tunisie. Les informations recueillies (nom, adresse de livraison, numéro de téléphone, adresse e-mail) sont strictement destinées au traitement des commandes, à l'acheminement des colis et à la communication du statut de livraison.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-base font-extrabold text-slate-900">2. Utilisation des informations</h2>
                <p>Vos données ne sont ni vendues, ni louées à des tiers. Elles sont uniquement partagées avec le vendeur partenaire préparant votre article et le transporteur effectuant la livraison à votre domicile.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-base font-extrabold text-slate-900">3. Sécurité</h2>
                <p>Nous mettons en œuvre toutes les mesures techniques et organisationnelles nécessaires pour préserver l'intégrité et la confidentialité de vos données personnelles.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-base font-extrabold text-slate-900">4. Vos droits</h2>
                <p>Conformément à la législation en vigueur, vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant. Vous pouvez nous contacter à tout moment à <a href="mailto:contact@aquawasouk.com" class="text-sky-600 underline">contact@aquawasouk.com</a>.</p>
            </section>
        </div>
    </div>

@endsection
