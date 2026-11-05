@extends('blade.layout')

@section('title', 'Catalogue des Produits - AquawaSouk Tunisie')
@section('meta_description', 'Parcourez notre large catalogue de produits en Tunisie : High-tech, Mode, Maison, Beauté. Meilleurs prix et livraison rapide à domicile.')

@section('content')

    <!-- Breadcrumb & Title Header -->
    <div class="bg-white border-b border-slate-200 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                <a href="{{ route('blade.home') }}" class="hover:text-sky-600">Accueil</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">Catalogue des Produits</span>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Catalogue des Produits</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }} disponible{{ $products->total() > 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Catalog Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Filters Sidebar -->
            <div class="lg:col-span-1">
                <form action="{{ route('blade.products.index') }}" method="GET" class="bg-white rounded-2xl border border-slate-200 p-5 space-y-6 shadow-xs sticky top-28">
                    
                    <!-- Search inside filters -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Recherche</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Mot-clé..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Rayon / Catégorie</label>
                        <div class="space-y-1.5 max-h-56 overflow-y-auto no-scrollbar pr-1">
                            <label class="flex items-center gap-2 text-xs cursor-pointer py-1 text-slate-700 hover:text-sky-600">
                                <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()" class="text-sky-600 focus:ring-sky-500">
                                <span>Toutes les catégories</span>
                            </label>
                            @foreach($categories as $category)
                                <label class="flex items-center gap-2 text-xs cursor-pointer py-1 text-slate-700 hover:text-sky-600">
                                    <input type="radio" name="category" value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'checked' : '' }} onchange="this.form.submit()" class="text-sky-600 focus:ring-sky-500">
                                    <span class="truncate">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Prix (TND / DT)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full p-2 rounded-xl border border-slate-300 text-xs">
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full p-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>

                    <!-- Sorting -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Trier par</label>
                        <select name="sort" onchange="this.form.submit()" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-200 outline-hidden bg-slate-50">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Plus récents</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Popularité (Vues)</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Prix : Moins cher</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Prix : Plus cher</option>
                        </select>
                    </div>

                    <!-- Submit & Reset Buttons -->
                    <div class="space-y-2 pt-2">
                        <button type="submit" class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            Appliquer les filtres
                        </button>
                        @if(request()->hasAny(['search', 'category', 'min_price', 'max_price', 'sort']))
                            <a href="{{ route('blade.products.index') }}" class="block w-full py-2 text-center text-slate-500 hover:text-slate-800 text-xs font-medium">
                                Réinitialiser les filtres
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="lg:col-span-3">
                @if($products->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($products as $product)
                            @include('blade.components.product-card', ['product' => $product])
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Aucun produit ne correspond à votre recherche</h3>
                        <p class="text-xs text-slate-500 mt-1">Essayez d'ajuster vos critères ou de réinitialiser les filtres.</p>
                        <a href="{{ route('blade.products.index') }}" class="inline-block mt-4 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition">
                            Voir tous les produits
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>

@endsection
