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

                {{-- Success Banner --}}
                @if (session('success'))
                    <div class="mb-6 p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-xs">
                        <svg class="w-6 h-6 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-xs space-y-1">
                            <p class="font-bold text-sm text-emerald-900">Candidature envoyée avec succès !</p>
                            <p class="text-emerald-700 leading-relaxed">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Error Banner (Session) --}}
                @if (session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="text-xs">
                            <p class="font-bold">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Validation Errors Banner --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-xs space-y-1">
                            <p class="font-bold text-rose-900">Certaines informations sont incomplètes ou incorrectes :</p>
                            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('become-vendor.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nom de votre Boutique / Enseigne *</label>
                        <input type="text" name="shop_name" value="{{ old('shop_name') }}" required placeholder="Ex: Electro Store Tunis" 
                            class="w-full p-3 rounded-xl border @error('shop_name') border-rose-400 bg-rose-50/30 focus:ring-rose-200 @else border-slate-300 focus:ring-sky-200 @enderror text-xs focus:ring-2 outline-hidden">
                        @error('shop_name')
                            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nom du Responsable *</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required placeholder="Nom & Prénom" 
                                class="w-full p-3 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 focus:ring-rose-200 @else border-slate-300 focus:ring-sky-200 @enderror text-xs focus:ring-2 outline-hidden"
                                @auth readonly @endauth>
                            @error('name')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Téléphone de contact *</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="+216 ..." 
                                class="w-full p-3 rounded-xl border @error('phone') border-rose-400 bg-rose-50/30 focus:ring-rose-200 @else border-slate-300 focus:ring-sky-200 @enderror text-xs focus:ring-2 outline-hidden">
                            @error('phone')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Adresse e-mail professionnelle *</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required placeholder="vendeur@domaine.tn" 
                            class="w-full p-3 rounded-xl border @error('email') border-rose-400 bg-rose-50/30 focus:ring-rose-200 @else border-slate-300 focus:ring-sky-200 @enderror text-xs focus:ring-2 outline-hidden"
                            @auth readonly @endauth>
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Gouvernorat / Ville *</label>
                            <input type="text" name="city" value="{{ old('city') }}" required placeholder="Ex: Tunis, Sfax, Sousse..." 
                                class="w-full p-3 rounded-xl border @error('city') border-rose-400 bg-rose-50/30 focus:ring-rose-200 @else border-slate-300 focus:ring-sky-200 @enderror text-xs focus:ring-2 outline-hidden">
                            @error('city')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Adresse de la boutique / Dépôt</label>
                            <input type="text" name="address" value="{{ old('address') }}" placeholder="Rue, quartier (optionnel)" 
                                class="w-full p-3 rounded-xl border @error('address') border-rose-400 bg-rose-50/30 focus:ring-rose-200 @else border-slate-300 focus:ring-sky-200 @enderror text-xs focus:ring-2 outline-hidden">
                            @error('address')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @guest
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mot de passe pour votre compte *</label>
                            <input type="password" name="password" required placeholder="Minimum 8 caractères" 
                                class="w-full p-3 rounded-xl border @error('password') border-rose-400 bg-rose-50/30 focus:ring-rose-200 @else border-slate-300 focus:ring-sky-200 @enderror text-xs focus:ring-2 outline-hidden">
                            @error('password')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    @endguest

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Description de votre activité ou produits vendus</label>
                        <textarea name="description" rows="3" placeholder="Quels types de produits vendez-vous ? Avez-vous déjà du stock prêt ?" 
                            class="w-full p-3 rounded-xl border @error('description') border-rose-400 bg-rose-50/30 focus:ring-rose-200 @else border-slate-300 focus:ring-sky-200 @enderror text-xs focus:ring-2 outline-hidden">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-1">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required
                                class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 mt-0.5">
                            <span class="text-xs text-slate-600 leading-relaxed">
                                J'accepte les <a href="{{ route('terms-of-service') }}" target="_blank" class="text-sky-600 font-bold hover:underline">Conditions d'Utilisation</a> et la charte de vente de la marketplace AquawaSouk *
                            </span>
                        </label>
                        @error('terms')
                            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
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
