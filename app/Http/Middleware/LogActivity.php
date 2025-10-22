<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo registrar en rutas del sistema (admin/celador)
        if (!$request->is('system/*')) {
            return $response;
        }

        // No registrar peticiones GET simples (solo acciones importantes)
        if ($request->isMethod('GET') && !$this->shouldLogGetRequest($request)) {
            return $response;
        }

        try {
            $this->logRequest($request, $response);
        } catch (\Exception $e) {
            // No fallar la request si el log falla
            Log::error('Error al registrar actividad', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
            ]);
        }

        return $response;
    }

    /**
     * Registra la petición
     */
    protected function logRequest(Request $request, Response $response): void
    {
        $usuario = auth('system')->user();

        // No registrar si no hay usuario autenticado (excepto login)
        if (!$usuario && !$request->is('system/login')) {
            return;
        }

        $action = $this->determineAction($request, $response);
        
        // No registrar ciertas acciones
        if ($this->shouldSkipLogging($action)) {
            return;
        }

        ActivityLog::create([
            'usuario_id' => $usuario?->idUsuario,
            'usuario_nombre' => $usuario?->nombre,
            'usuario_rol' => $usuario?->principalRole?->nombre,
            'action' => $action,
            'module' => $this->determineModule($request),
            'description' => $this->generateDescription($request, $action),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'severity' => $this->determineSeverity($response->getStatusCode(), $action),
            'properties' => $this->getRequestProperties($request),
        ]);
    }

    /**
     * Determina si debe registrar una petición GET
     */
    protected function shouldLogGetRequest(Request $request): bool
    {
        // Registrar exports, vistas de detalles importantes, etc.
        $importantPatterns = [
            'export',
            'download',
            'pdf',
            'excel',
        ];

        foreach ($importantPatterns as $pattern) {
            if (str_contains($request->url(), $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determina la acción basada en la ruta y método
     */
    protected function determineAction(Request $request, Response $response): string
    {
        $method = $request->method();
        $path = $request->path();

        // Login/Logout
        if (str_contains($path, 'login')) {
            return $response->getStatusCode() === 200 ? 'login' : 'failed_login';
        }
        if (str_contains($path, 'logout')) {
            return 'logout';
        }

        // Export
        if (str_contains($path, 'export') || str_contains($path, 'download')) {
            return 'exported';
        }

        // CRUD based on HTTP method
        switch ($method) {
            case 'POST':
                return 'created';
            case 'PUT':
            case 'PATCH':
                return 'updated';
            case 'DELETE':
                return 'deleted';
            case 'GET':
                return 'viewed';
            default:
                return strtolower($method);
        }
    }

    /**
     * Determina el módulo basado en la ruta
     */
    protected function determineModule(Request $request): ?string
    {
        $path = $request->path();

        $modules = [
            'personas' => 'personas',
            'usuarios' => 'usuarios',
            'users' => 'usuarios',
            'accesos' => 'accesos',
            'incidencias' => 'incidencias',
            'portatiles' => 'portatiles',
            'vehiculos' => 'vehiculos',
            'programas' => 'programas_formacion',
            'permisos' => 'permisos',
            'permissions' => 'permisos',
            'roles' => 'roles',
            'qr' => 'qr',
            'historial' => 'historial',
            'dashboard' => 'dashboard',
        ];

        foreach ($modules as $key => $module) {
            if (str_contains($path, $key)) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Genera descripción legible
     */
    protected function generateDescription(Request $request, string $action): string
    {
        $usuario = auth('system')->user()?->nombre ?? 'Usuario';
        $module = $this->determineModule($request) ?? 'sistema';
        
        $actionMap = [
            'created' => 'creó un registro en',
            'updated' => 'actualizó un registro en',
            'deleted' => 'eliminó un registro en',
            'viewed' => 'visualizó',
            'exported' => 'exportó datos de',
            'login' => 'inició sesión',
            'logout' => 'cerró sesión',
            'failed_login' => 'intentó iniciar sesión',
        ];

        $actionText = $actionMap[$action] ?? $action;
        
        return "$usuario $actionText $module";
    }

    /**
     * Determina la severidad
     */
    protected function determineSeverity(int $statusCode, string $action): string
    {
        if ($statusCode >= 500) {
            return 'critical';
        }
        
        if ($statusCode >= 400) {
            return 'error';
        }

        if (in_array($action, ['deleted', 'failed_login'])) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Obtiene propiedades adicionales del request
     */
    protected function getRequestProperties(Request $request): array
    {
        return [
            'referer' => $request->header('referer'),
            'accept_language' => $request->header('accept-language'),
        ];
    }

    /**
     * Determina si debe omitir el logging
     */
    protected function shouldSkipLogging(string $action): bool
    {
        // No registrar vistas simples en GET
        return $action === 'viewed';
    }
}
