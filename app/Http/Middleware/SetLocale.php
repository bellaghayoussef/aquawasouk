<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from session or default to French (Tunisia)
        $locale = session('locale', config('app.locale', 'fr'));
        
        // Validate locale
        if (!in_array($locale, ['en', 'fr', 'ar'])) {
            $locale = 'fr';
        }
        
        // Set application locale
        app()->setLocale($locale);
        
        return $next($request);
    }
}
