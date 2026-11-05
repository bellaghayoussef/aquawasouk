@extends('blade.layout')

@section('title', 'Carnet d\'Adresses - AquawaSouk Tunisie')
@section('meta_description', 'Gérez vos adresses de livraison enregistrées sur AquawaSouk pour commander plus rapidement.')

@section('content')
@php
    $isAr = app()->getLocale() === 'ar';
@endphp

<div class="bg-slate-50/70 py-8 min-h-[70vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ modalOpen: false, editMode: false, currentAddress: {} }">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200/80 mb-8">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl shadow-xs">
                        📍
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        {{ $isAr ? 'دفتر العناوين' : 'Mes Adresses de Livraison' }}
                    </h1>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    {{ $isAr ? 'احفظ عناوينك لتسريع عملية تأكيد الطلبيات' : 'Enregistrez plusieurs adresses pour faciliter et accélérer vos livraisons' }}
                </p>
            </div>

            <button 
                type="button" 
                @click="editMode = false; currentAddress = {}; modalOpen = true"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-sky-600 text-white font-bold text-xs shadow-xs transition cursor-pointer"
            >
                <span>➕</span>
                <span>{{ $isAr ? 'إضافة عنوان جديد' : 'Ajouter une adresse' }}</span>
            </button>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($addresses->isEmpty())
            <!-- Empty state -->
            <div class="bg-white rounded-3xl p-8 sm:p-14 text-center max-w-md mx-auto border border-slate-200/80 shadow-xs">
                <span class="text-4xl block mb-3">🏠</span>
                <h3 class="text-base font-black text-slate-900 mb-1">
                    {{ $isAr ? 'لم تقم بإضافة أي عنوان بعد' : 'Aucune adresse enregistrée' }}
                </h3>
                <p class="text-xs text-slate-500 mb-5">
                    {{ $isAr ? 'أضف عنوانك الرئيسي لملء استمارة الطلب بنقرة واحدة.' : 'Ajoutez votre domicile ou bureau pour gagner du temps lors de vos commandes.' }}
                </p>
                <button 
                    type="button" 
                    @click="editMode = false; currentAddress = {}; modalOpen = true"
                    class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-xs transition"
                >
                    {{ $isAr ? 'إضافة عنوان الآن' : 'Ajouter une adresse' }}
                </button>
            </div>
        @else
            <!-- Addresses Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                @foreach($addresses as $addr)
                    <div class="bg-white rounded-3xl p-5 sm:p-6 border {{ $addr->is_default ? 'border-sky-500 ring-4 ring-sky-500/10' : 'border-slate-200/80' }} shadow-xs relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black {{ $addr->is_default ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-700' }}">
                                    <span>{{ $addr->title === 'Maison' ? '🏠' : ($addr->title === 'Travail' ? '🏢' : '📍') }}</span>
                                    <span>{{ $addr->title }}</span>
                                </span>

                                @if($addr->is_default)
                                    <span class="text-[11px] font-extrabold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md">
                                        {{ $isAr ? 'العنوان الافتراضي' : 'Par défaut' }}
                                    </span>
                                @else
                                    <form action="{{ route('addresses.default', $addr->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-bold text-slate-400 hover:text-sky-600 transition cursor-pointer">
                                            {{ $isAr ? 'تعيين كافتراضي' : 'Définir par défaut' }}
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <h4 class="text-sm font-black text-slate-900">{{ $addr->recipient_name }}</h4>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $addr->address }}</p>
                            <p class="text-xs text-slate-600">{{ $addr->city }}{{ $addr->governorate ? ', ' . $addr->governorate : '' }} {{ $addr->postal_code }}</p>
                            <p class="text-xs font-bold text-slate-800 mt-2">📞 {{ $addr->phone }}</p>
                        </div>

                        <!-- Actions footer -->
                        <div class="pt-4 border-t border-slate-100 mt-4 flex items-center justify-end gap-2">
                            <form action="{{ route('addresses.destroy', $addr->id) }}" method="POST" onsubmit="return confirm('Supprimer cette adresse ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition text-xs font-bold cursor-pointer">
                                    🗑️ {{ $isAr ? 'حذف' : 'Supprimer' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add / Edit Modal -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative" @click.away="modalOpen = false">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <h3 class="text-base font-black text-slate-900">
                        {{ $isAr ? 'إضافة عنوان توصيل' : 'Ajouter une adresse de livraison' }}
                    </h3>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form action="{{ route('addresses.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ $isAr ? 'نوع العنوان' : 'Titre de l\'adresse' }}</label>
                        <select name="title" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-hidden">
                            <option value="Maison">🏠 Maison / Domicile</option>
                            <option value="Travail">🏢 Bureau / Travail</option>
                            <option value="Autre">📍 Autre</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ $isAr ? 'اسم المستلم' : 'Nom complet' }}</label>
                            <input type="text" name="recipient_name" required value="{{ auth()->user()->name }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-hidden">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ $isAr ? 'رقم الهاتف' : 'Téléphone' }}</label>
                            <input type="text" name="phone" required placeholder="ex: 98 123 456" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-hidden">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ $isAr ? 'العنوان التفصيلي' : 'Adresse exacte (Rue, N°)' }}</label>
                        <input type="text" name="address" required placeholder="Rue, Résidence, Appartement..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-hidden">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ $isAr ? 'المدينة / المعتمدية' : 'Ville' }}</label>
                            <input type="text" name="city" required placeholder="ex: Tunis, Sousse..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-hidden">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ $isAr ? 'الترقيم البريدي' : 'Code Postal' }}</label>
                            <input type="text" name="postal_code" placeholder="ex: 1000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-hidden">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="is_default" name="is_default" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <label for="is_default" class="text-xs font-semibold text-slate-700 cursor-pointer">
                            {{ $isAr ? 'تعيين كعنوان رئيسي مفضل للطلبات' : 'Définir comme adresse principale par défaut' }}
                        </label>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition">
                            {{ __('messages.cancel') ?? 'Annuler' }}
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-xs transition">
                            {{ $isAr ? 'حفظ العنوان' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
