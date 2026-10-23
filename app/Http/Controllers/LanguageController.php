<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        // Validate locale
        if (!in_array($locale, ['en', 'fr', 'ar'])) {
            $locale = 'ar';
        }

        // Store in session
        Session::put('locale', $locale);
        
        // Set app locale
        app()->setLocale($locale);

        return redirect()->back();
    }
}
