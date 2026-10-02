<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;

class LocaleController extends Controller
{
    /**
     * Switch application language between French and English.
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request, string $locale)
    {
        $supportedLocales = ['fr', 'en'];

        if (in_array($locale, $supportedLocales, true)) {
            Session::put('locale', $locale);
            App::setLocale($locale);
            Carbon::setLocale($locale);

            // Persist preference via cookies (1 year = 525600 minutes)
            Cookie::queue('realvoyage_locale', $locale, 525600, '/', null, false, false);
            Cookie::queue('transportcm_locale', $locale, 525600, '/', null, false, false);

            // Synchronize Google Translate automatic engine cookie
            $googtransVal = '/fr/' . $locale;
            Cookie::queue('googtrans', $googtransVal, 525600, '/', null, false, false);

            // Update user preference if logged in and column exists
            $user = $request->user();
            if ($user && in_array('locale', $user->getFillable(), true)) {
                $user->update(['locale' => $locale]);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'locale' => $locale,
                    'googtrans' => $googtransVal,
                ]);
            }
        }

        return redirect()->back(fallback: route('home'));
    }
}
