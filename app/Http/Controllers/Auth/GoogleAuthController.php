<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google OAuth 2.0 authentication screen.
     */
    public function redirectToGoogle(Request $request)
    {
        $clientId = config('services.google.client_id');

        // Store intended destination URL if specified or from previous location
        if ($request->has('redirect')) {
            session(['url.intended' => $request->get('redirect')]);
        } elseif (!session()->has('url.intended')) {
            $prev = url()->previous();
            if ($prev && $prev !== route('login') && $prev !== route('register.passenger') && $prev !== route('auth.google') && $prev !== route('auth.google.setup')) {
                session(['url.intended' => $prev]);
            }
        }

        // If Google API credentials are not yet configured in .env,
        // display the setup guide page with the exact Authorized Redirect URI.
        if (empty($clientId) && !$request->has('demo') && !$request->has('force_real')) {
            return redirect()->route('auth.google.setup');
        }

        try {
            $driver = Socialite::driver('google');
            $this->configureDriver($driver);

            return $driver
                ->with(['prompt' => 'select_account'])
                ->redirect();
        } catch (\Exception $e) {
            Log::error('Google OAuth Redirect Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', __('Erreur lors de la redirection vers Google : :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Show the Google OAuth 2.0 API Configuration & Setup Guide.
     */
    public function showSetupGuide(Request $request)
    {
        return view('auth.google-setup', [
            'redirectUri' => $this->getRedirectUri(),
            'hasCredentials' => !empty(config('services.google.client_id')),
        ]);
    }

    /**
     * Save Google OAuth credentials to the .env file.
     */
    public function saveCredentials(Request $request)
    {
        $request->validate([
            'google_client_id' => 'required|string|min:10',
            'google_client_secret' => 'required|string|min:5',
        ]);

        $clientId = trim($request->input('google_client_id'));
        $clientSecret = trim($request->input('google_client_secret'));

        $this->updateEnvFile([
            'GOOGLE_CLIENT_ID' => $clientId,
            'GOOGLE_CLIENT_SECRET' => $clientSecret,
        ]);

        config([
            'services.google.client_id' => $clientId,
            'services.google.client_secret' => $clientSecret,
        ]);

        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Ignore if artisan fails in restricted environments
        }

        return redirect()->route('auth.google.setup')
            ->with('success', __('Identifiants Google enregistrés avec succès ! Vous pouvez maintenant lancer la connexion Google.'));
    }

    /**
     * Obtain the user information from Google callback.
     */
    public function handleGoogleCallback(Request $request)
    {
        // 1. Check for cancellation or error from Google consent screen
        if ($request->has('error')) {
            $error = $request->get('error');
            if ($error === 'access_denied') {
                return redirect()->route('login')
                    ->with('info', __('Connexion avec Google annulée.'));
            }
            return redirect()->route('login')
                ->with('error', __('Erreur d\'autorisation Google : :msg', ['msg' => $error]));
        }

        try {
            $clientId = config('services.google.client_id');

            if (empty($clientId)) {
                return redirect()->route('auth.google.setup')
                    ->with('error', __('Veuillez configurer vos identifiants Google OAuth dans votre fichier .env.'));
            }

            $driver = Socialite::driver('google');
            $this->configureDriver($driver);

            // Handle browser tracking prevention or cross-domain cookie stripping:
            // Attempt standard stateful verification first, fallback to stateless() if state was lost
            try {
                $googleUser = $driver->user();
            } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
                Log::info('Google OAuth state mismatch (often caused by browser tracking prevention). Falling back to stateless.');
                if (method_exists($driver, 'stateless')) {
                    $googleUser = $driver->stateless()->user();
                } else {
                    throw $e;
                }
            }

            if (!$googleUser || !$googleUser->getEmail()) {
                return redirect()->route('login')
                    ->with('error', __('Impossible de récupérer les informations de votre compte Google.'));
            }

            $user = $this->findOrCreateUser($googleUser);

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('passenger.dashboard'))
                ->with('success', __('Connexion réussie avec votre compte Google ! Bienvenue :name.', ['name' => $user->name]));
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('Google OAuth Connection Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', __('Impossible de joindre les serveurs de Google (délai d\'attente ou instabilité réseau / IPv6). Veuillez vérifier votre connexion ou tester en mode simulation.'))
                ->with('show_demo_link', true);
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::warning('Google OAuth Invalid State: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', __('Session expirée lors de la connexion Google. Veuillez réessayer.'));
        } catch (\Exception $e) {
            Log::error('Google OAuth Callback Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', __('Impossible de vous connecter via Google : :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Get the dynamic or configured Authorized Redirect URI.
     */
    public function getRedirectUri(): string
    {
        $custom = env('GOOGLE_REDIRECT_URI');
        if (!empty($custom) && !str_contains($custom, '${APP_URL}')) {
            return $custom;
        }

        return route('auth.google.callback');
    }

    /**
     * Configure the Socialite driver with IPv4 resolution, timeouts, and dynamic redirect URI.
     */
    protected function configureDriver($driver): void
    {
        $redirectUri = $this->getRedirectUri();
        if (is_object($driver)) {
            if (method_exists($driver, 'redirectUrl')) {
                $driver->redirectUrl($redirectUri);
            }
            if (method_exists($driver, 'setHttpClient')) {
                // Force IPv4 to prevent IPv6 connection drops (ERR_NETWORK_CHANGED / connection closed)
                $curlOptions = [];
                if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                    $curlOptions[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
                }
                $httpClient = new \GuzzleHttp\Client([
                    'curl' => $curlOptions,
                    'timeout' => 15,
                    'connect_timeout' => 8,
                ]);
                $driver->setHttpClient($httpClient);
            }
        }
    }

    /**
     * Safely update or append key-value pairs in the .env file.
     */
    protected function updateEnvFile(array $values): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $escapedValue = (str_contains($value, ' ') || str_contains($value, '#')) 
                ? '"' . addcslashes($value, '"\\$') . '"' 
                : $value;

            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$escapedValue}", $envContent);
            } else {
                $envContent .= "\n{$key}={$escapedValue}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    /**
     * 1-Click Demo Google OAuth Simulator for instant testing without API keys.
     */
    public function demoGoogleLogin(Request $request)
    {
        $demoEmail = 'google.passenger@transport.cm';
        
        $user = User::firstOrCreate(
            ['email' => $demoEmail],
            [
                'name' => 'Jean-Paul Kamga (Google)',
                'google_id' => 'google_demo_1092837465',
                'phone' => '699112233',
                'role' => 'passager',
                'status' => 'active',
                'avatar' => 'https://ui-avatars.com/api/?name=Jean-Paul+Kamga&background=008744&color=fff',
                'email_verified_at' => now(),
                'password' => bcrypt(Str::random(24)),
            ]
        );

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('passenger.dashboard'))
            ->with('success', __('Connexion réussie avec Google (Mode Démo) ! Bienvenue :name.', ['name' => $user->name]));
    }

    /**
     * Find existing user by google_id or email, or create new passenger.
     */
    protected function findOrCreateUser($googleUser): User
    {
        // 1. Check if user already exists by google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            // Update avatar or name if changed in Google
            $updates = [];
            if ($googleUser->getAvatar() && $user->avatar !== $googleUser->getAvatar()) {
                $updates['avatar'] = $googleUser->getAvatar();
            }
            if (!empty($updates)) {
                $user->update($updates);
            }
            return $user;
        }

        // 2. Check if user exists by email (link accounts)
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $user->avatar ?: $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?: now(),
            ]);
            return $user;
        }

        // 3. Create new Passenger Account
        $name = $googleUser->getName() ?? $googleUser->getNickname() ?? 'Voyageur Google';
        return User::create([
            'name' => $name,
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar() ?: 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=008744&color=fff',
            'role' => 'passager',
            'status' => 'active',
            'email_verified_at' => now(),
            'password' => bcrypt(Str::random(24)),
        ]);
    }
}
