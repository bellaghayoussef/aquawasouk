<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Display the privacy policy page.
     */
    public function privacyPolicy()
    {
        return inertia('PrivacyPolicy');
    }

    /**
     * Display the terms of service page.
     */
    public function termsOfService()
    {
        return inertia('TermsOfService');
    }
}
