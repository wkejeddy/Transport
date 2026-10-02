<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
        
        // Exclude logout and webhook callback routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'logout',
            'api/payments/momo/callback',
            'api/payments/om/callback',
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('logout') || $request->routeIs('logout')) {
                \Illuminate\Support\Facades\Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('home')->with('info', __('messages.flash.logged_out'));
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'CSRF token mismatch or session expired.',
                ], 419);
            }

            return redirect()->route('login')->with('warning', __('Votre session a expiré en raison d\'inactivité. Veuillez vous reconnecter.'));
        });
    })->create();
