<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * List user addresses
     */
    public function index()
    {
        $addresses = auth()->user()->addresses()->latest()->get();
        return view('blade.customer.addresses', compact('addresses'));
    }

    /**
     * Store new address
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'governorate' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        $data['user_id'] = auth()->id();
        $isFirst = auth()->user()->addresses()->count() === 0;

        if (!empty($data['is_default']) || $isFirst) {
            auth()->user()->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $address = Address::create($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'address' => $address]);
        }

        return back()->with('success', __('Adresse enregistrée avec succès.'));
    }

    /**
     * Update address
     */
    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'governorate' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($data['is_default'])) {
            auth()->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $address->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'address' => $address]);
        }

        return back()->with('success', __('Adresse mise à jour.'));
    }

    /**
     * Set default address
     */
    public function setDefault(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        auth()->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', __('Adresse principale mise à jour.'));
    }

    /**
     * Delete address
     */
    public function destroy(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', __('Adresse supprimée.'));
    }
}
