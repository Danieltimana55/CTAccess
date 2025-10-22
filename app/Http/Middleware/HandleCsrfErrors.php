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
            if ($request->hasSession()) {
                $request->session()->regenerateToken();
            }

            // Log para debugging (solo en desarrollo)
            if (config('app.debug')) {
                Log::info('CSRF token regenerado', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);
            }

            // Si es una petición AJAX/Inertia, devolver respuesta que recargue automáticamente
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Sesión actualizada',
                    'reload' => true
                ], 200); // Cambiar a 200 para que se maneje mejor en el frontend
            }

            // Para peticiones normales, redirigir y reintentar automáticamente
            return redirect($request->fullUrl())
                ->withInput($request->except(['password', 'contraseña', 'password_confirmation', '_token']));
        }
    }
}
