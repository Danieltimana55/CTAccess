<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class HandleCsrfErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (TokenMismatchException $e) {
            // Regenerar el token CSRF
            $request->session()->regenerateToken();

            // Log para debugging
            Log::warning('CSRF token mismatch detectado', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);

            // Si es una petición AJAX/Inertia, devolver error JSON con instrucciones de recarga
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'La sesión ha expirado. Por favor, recarga la página.',
                    'errors' => [
                        'csrf' => ['La página ha expirado por inactividad. Por favor recarga e intenta de nuevo.']
                    ],
                    'reload' => true  // Señal para que el frontend recargue automáticamente
                ], 419);
            }

            // Para peticiones normales, redirigir con mensaje
            return redirect()->back()
                ->withInput($request->except('password', 'contraseña', 'password_confirmation'))
                ->withErrors(['csrf' => 'La página ha expirado por inactividad. Por favor intenta de nuevo.']);
        }
    }
}
