<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->guest(route('register.passenger'))
                ->with('warning', 'Veuillez vous inscrire ou vous connecter pour réserver des places et effectuer des expéditions.');
        }

        $user = Auth::user();

        if ($user->status === 'suspended') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Votre compte a été suspendu par les administrateurs.');
        }

        if (!in_array($user->role, $roles)) {
            // If user is accessing wrong dashboard, route them to their respective area
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('warning', 'Accès réservé.');
            } elseif ($user->isManager()) {
                return redirect()->route('manager.dashboard')->with('warning', 'Accès réservé.');
            } else {
                return redirect()->route('passenger.dashboard')->with('warning', 'Accès non autorisé.');
            }
        }

        return $next($request);
    }
}
