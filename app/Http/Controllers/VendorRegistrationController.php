<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class VendorRegistrationController extends Controller
{
    public function create()
    {
        return Inertia::render('BecomeVendor');
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        // 1. If already logged in with an existing vendor account, redirect immediately
        if ($currentUser && $currentUser->vendor) {
            return redirect()->route('vendor.dashboard')->with('error', __('Vous avez déjà un compte vendeur actif ou en attente.'));
        }

        // 2. Build comprehensive validation rules upfront (Atomic validation)
        $rules = [
            'shop_name' => 'required|string|max:255|unique:vendors,shop_name',
            'phone' => 'required|string|max:30',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:2000',
            'terms' => 'accepted',
        ];

        // Add user credentials validation if guest
        if (!$currentUser) {
            $passwordRules = ['required', 'string', 'min:8'];
            if ($request->filled('password_confirmation')) {
                $passwordRules[] = 'confirmed';
            }

            $rules['name'] = 'required|string|max:255';
            $rules['email'] = 'required|string|email|max:255|unique:users,email';
            $rules['password'] = $passwordRules;
        }

        $validated = $request->validate($rules, [
            'shop_name.required' => 'Le nom de la boutique est obligatoire.',
            'shop_name.unique' => 'Ce nom de boutique est déjà utilisé par un autre vendeur.',
            'name.required' => 'Le nom du responsable est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
            'email.unique' => 'Cette adresse e-mail est déjà associée à un compte existant.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'city.required' => 'La ville ou gouvernorat est obligatoire.',
            'terms.accepted' => 'Vous devez accepter les conditions d\'utilisation pour continuer.',
        ]);

        // 3. Perform atomic creation within a Database Transaction
        DB::transaction(function () use ($request, $currentUser, $validated) {
            // Create user if guest
            if (!$currentUser) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($request->password),
                    'role' => 'vendor',
                ]);

                Auth::login($user);
            } else {
                $user = $currentUser;
                if ($user->role === 'customer') {
                    $user->role = 'vendor';
                    $user->save();
                }
            }

            // Generate a safe unique slug
            $baseSlug = Str::slug($validated['shop_name']);
            if (empty($baseSlug)) {
                $baseSlug = 'boutique-' . time();
            }
            $slug = $baseSlug;
            $counter = 1;
            while (Vendor::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            // Create vendor record
            Vendor::create([
                'user_id' => $user->id,
                'shop_name' => $validated['shop_name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'address' => $validated['address'] ?? $validated['city'],
                'city' => $validated['city'],
                'country' => $validated['country'] ?? 'Tunisie',
                'postal_code' => $validated['postal_code'] ?? null,
                'phone' => $validated['phone'],
                'status' => 'pending', // Pending admin approval
            ]);
        });

        return redirect()->back()->with('success', __('Votre demande d\'adhésion a été soumise avec succès ! Notre équipe examinera votre profil et vous contactera dans les plus brefs délais.'));
    }
}
