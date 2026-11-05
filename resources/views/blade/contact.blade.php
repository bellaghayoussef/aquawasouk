@extends('blade.layout')

@section('title', 'Contact & Assistance - AquawaSouk Tunisie')
@section('meta_description', 'Contactez le support AquawaSouk en Tunisie. Assistance commandes, livraison et service après-vente.')

@section('content')

    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white py-14 text-center">
        <div class="max-w-4xl mx-auto px-4">
            <span class="text-xs uppercase font-extrabold tracking-widest text-sky-400">À votre écoute</span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight mt-2">Contactez Notre Équipe</h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-3 max-w-xl mx-auto">
                Une question sur une commande, une livraison ou un produit ? Notre équipe basée en Tunisie est disponible 6j/7 pour vous assister.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Left: Contact Form -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                <h2 class="text-xl font-extrabold text-slate-900 mb-1">Envoyez-nous un message</h2>
                <p class="text-xs text-slate-500 mb-6">Nous répondons généralement sous 24 heures ouvrées.</p>

                <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nom complet *</label>
                            <input type="text" name="name" required class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden" placeholder="Votre nom">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Numéro de téléphone *</label>
                            <input type="tel" name="phone" required class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden" placeholder="+216 ...">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Adresse e-mail *</label>
                        <input type="email" name="email" required class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden" placeholder="nom@exemple.com">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sujet de votre demande</label>
                        <select name="subject" class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden bg-slate-50">
                            <option value="Suivi de commande">Suivi d'une commande</option>
                            <option value="Question produit">Renseignement sur un produit</option>
                            <option value="Devenir vendeur">Informations pour devenir vendeur</option>
                            <option value="Autre">Autre demande</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Votre message *</label>
                        <textarea name="message" rows="5" required class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden" placeholder="Détaillez votre demande ici..."></textarea>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-md transition transform active:scale-98">
                        Envoyer le message &rarr;
                    </button>
                </form>
            </div>

            <!-- Right: Info cards & FAQ -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Direct Details Card -->
                <div class="bg-gradient-to-br from-sky-600 to-indigo-700 rounded-3xl p-6 text-white shadow-lg space-y-4">
                    <h3 class="text-base font-extrabold">Coordonnées Directes</h3>
                    
                    <div class="space-y-3 text-xs text-sky-100">
                        <div class="flex items-start gap-3">
                            <span class="text-lg">📍</span>
                            <div>
                                <p class="font-bold text-white">Siège & Logistique</p>
                                <p>Tunis, Tunisie</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="text-lg">📞</span>
                            <div>
                                <p class="font-bold text-white">Téléphone & WhatsApp</p>
                                <p>+216 26 260 262</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="text-lg">✉️</span>
                            <div>
                                <p class="font-bold text-white">Courrier Électronique</p>
                                <p>contact@aquawasouk.com</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="text-lg">⏰</span>
                            <div>
                                <p class="font-bold text-white">Horaires d'Ouverture</p>
                                <p>Du Lundi au Samedi : 08h30 - 18h30</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fast FAQ -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4 text-xs">
                    <h3 class="text-sm font-extrabold text-slate-900">Questions Fréquentes</h3>
                    
                    <div class="space-y-3 divide-y divide-slate-100">
                        <div class="pt-2">
                            <p class="font-bold text-slate-800">Comment payer ma commande ?</p>
                            <p class="text-slate-500 mt-0.5">Le paiement s'effectue exclusivement en espèces à la livraison auprès du transporteur.</p>
                        </div>
                        <div class="pt-3">
                            <p class="font-bold text-slate-800">Quels sont les délais de livraison ?</p>
                            <p class="text-slate-500 mt-0.5">La livraison est assurée sous 24 à 48 heures dans tout le territoire tunisien.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
