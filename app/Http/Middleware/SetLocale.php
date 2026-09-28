<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported application locales.
     */
    protected array $supportedLocales = ['fr', 'en'];

    /**
     * Handle an incoming request and set the active application locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale');

        // 1. Check long-lived cookie if session is not yet populated
        if (!$locale) {
            $cookieLocale = $request->cookie('realvoyage_locale') ?: $request->cookie('transportcm_locale');
            if ($cookieLocale && in_array($cookieLocale, $this->supportedLocales, true)) {
                $locale = $cookieLocale;
            }
        }

        // 2. Check authenticated user's saved preference
        if (!$locale && $request->user() && !empty($request->user()->locale)) {
            if (in_array($request->user()->locale, $this->supportedLocales, true)) {
                $locale = $request->user()->locale;
            }
        }

        // 3. Fallback to application default locale
        if (!$locale) {
            $locale = config('app.locale', 'fr');
        }

        // 4. Strict safety fallback
        if (!in_array($locale, $this->supportedLocales, true)) {
            $locale = 'fr';
        }

        // Persist in session for consistent subsequent requests
        Session::put('locale', $locale);
        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
