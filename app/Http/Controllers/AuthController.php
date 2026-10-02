<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Anti-Bruteforce Throttle Key (per email + client IP)
        $throttleKey = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => __('Trop de tentatives de connexion échouées. Par mesure de sécurité, veuillez patienter :seconds secondes avant de réessayer.', ['seconds' => $seconds]),
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->status === 'suspended') {
                Auth::logout();
                return back()->withErrors(['email' => 'Votre compte a été suspendu par l\'administrateur.']);
            }

            return $this->redirectBasedOnRole($user)->with('success', __('messages.flash.welcome', ['name' => $user->name]));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => __('Identifiants invalides. Veuillez vérifier votre adresse e-mail et mot de passe.'),
        ])->onlyInput('email');
    }

    public function showRegisterPassenger()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.register-passenger');
    }

    public function registerPassenger(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
        ], [
            'password.min' => __('Le mot de passe doit comporter au moins 8 caractères.'),
            'password.letters' => __('Le mot de passe doit contenir au moins une lettre.'),
            'password.numbers' => __('Le mot de passe doit contenir au moins un chiffre.'),
            'password.confirmed' => __('La confirmation du mot de passe ne correspond pas.'),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => 'passager',
            'status' => 'active',
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->intended(route('passenger.dashboard'))->with('success', __('messages.flash.passenger_account_created'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.flash.logged_out'),
                'redirect' => route('home')
            ]);
        }

        return redirect()->route('home')->with('info', __('messages.flash.logged_out'));
    }

    private function redirectBasedOnRole(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isManager()) {
            return redirect()->route('manager.dashboard');
        } else {
            return redirect()->intended(route('passenger.dashboard'));
        }
    }
}
