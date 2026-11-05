@extends('blade.layout')

@section('title', 'Devenir Vendeur sur AquawaSouk - Ouvrez votre boutique en ligne')
@section('meta_description', 'Vendez vos produits à des milliers de clients en Tunisie. Inscription rapide, commission avantageuse et gestion facile.')

@section('content')

    <!-- Hero Header -->
    <div class="relative bg-gradient-to-r from-indigo-950 via-slate-900 to-sky-950 text-white py-16 sm:py-20 overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 space-y-4">
            <span class="inline-block px-3 py-1 rounded-full bg-amber-400 text-slate-950 text-xs font-extrabold uppercase tracking-widest">
                Rejoignez le réseau des commerçants tunisiens
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">
                Vendez Vos Produits sur <span class="bg-gradient-to-r from-sky-400 to-indigo-300 bg-clip-text text-transparent">AquawaSouk</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 max-w-2xl mx-auto leading-relaxed">
                Profitez de la puissance de notre marketplace pour toucher de nouveaux clients dans toute la Tunisie. Créez votre boutique en quelques clics sans compétences techniques.
            </p>
        </div>
    </div>

    <!-- Main Registration Section -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            
            <!-- Left: Advantages -->
            <div class="lg:col-span-5 space-y-8">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pourquoi vendre chez nous ?</h2>
                    <p class="text-xs text-slate-500 mt-1">Les avantages concrets réservés à nos marchands partenaires</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                        <span class="text-2xl">📈</span>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Visibilité Immédiate</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Accédez à des milliers d'acheteurs actifs recherchant quotidiennement des produits en Tunisie.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                        <span class="text-2xl">💰</span>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Paiements Rapides & Sûrs</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Vos fonds vous sont reversés directement sur votre compte bancaire ou postal en toute transparence.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                        <span class="text-2xl">📱</span>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Partage Facebook Automatisé</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Partagez vos produits sur Facebook en 1 clic avec description, prix et lien de commande générés.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                        <span class="text-2xl">🚚</span>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Livraison Couverte</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Expédition assurée dans les 24 gouvernorats avec enlèvement rapide de vos colis.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Vendor Form -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-10 shadow-sm">
                <div class="mb-6">
                    <h3 class="text-xl font-extrabold text-slate-900">Demande d'Adhésion Vendeur</h3>
                    <p class="text-xs text-slate-500 mt-1">Remplissez le formulaire ci-dessous pour créer votre espace boutique :</p>
                </div>

                <form action="{{ route('become-vendor.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nom de votre Boutique / Enseigne *</label>
                        <input type="text" name="shop_name" required placeholder="Ex: Electro Store Tunis" class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nom du Responsable *</label>
                            <input type="text" name="name" required placeholder="Nom & Prénom" class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Téléphone de contact *</label>
                            <input type="tel" name="phone" required placeholder="+216 ..." class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Adresse e-mail professionnelle *</label>
                        <input type="email" name="email" required placeholder="vendeur@domaine.tn" class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Gouvernorat / Ville *</label>
                            <input type="text" name="city" required placeholder="Ex: Tunis, Sfax, Sousse..." class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mot de passe pour votre compte *</label>
                            <input type="password" name="password" required placeholder="Minimum 8 caractères" class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Description de votre activité ou produits vendus</label>
                        <textarea name="description" rows="3" placeholder="Quels types de produits vendez-vous ? Avez-vous déjà du stock prêt ?" class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-md transition transform active:scale-98">
                            Soumettre ma candidature &rarr;
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

@endsection
