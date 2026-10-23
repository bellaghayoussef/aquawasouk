<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $user = Auth::user();

        // If no user is logged in, create a new user account
        if (!$user) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'vendor',
            ]);

            Auth::login($user);
        } else {
            // Check if user already has a vendor profile
            if ($user->vendor) {
                return redirect()->route('vendor.dashboard')->with('error', __('You already have a vendor account.'));
            }
            
            // Update user role to vendor if they're a customer
            if ($user->role === 'customer') {
                $user->role = 'vendor';
                $user->save();
            }
        }

        // Validate vendor information
        $request->validate([
            'shop_name' => 'required|string|max:255|unique:vendors',
            'description' => 'required|string',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'terms' => 'accepted',
        ]);

        // Create vendor record with pending status
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $request->shop_name,
            'description' => $request->description,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'phone' => $request->phone,
            'status' => 'pending', // Will require admin approval
        ]);

        return redirect()->route('dashboard')->with('success', __('Your vendor application has been submitted successfully. Please wait for admin approval.'));
    }
}
