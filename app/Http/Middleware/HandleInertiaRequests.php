<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Si es una ruta del sistema, compartir el usuario autenticado del guard 'system'
        $isSystemRoute = $request->routeIs('system.*');
        $user = $isSystemRoute ? Auth::guard('system')->user() : $request->user();

        // Rol del usuario del sistema (cuando aplica)
        $role = null;
        if ($isSystemRoute && $user) {
            // Prefer principal role if set, otherwise detect via RBAC
            if (method_exists($user, 'principalRole') && $user->principalRole) {
                $role = $user->principalRole->nombre; // e.g. 'administrador', 'celador'
            } elseif (method_exists($user, 'hasRole')) {
                foreach (['administrador', 'celador'] as $r) {
                    if ($user->hasRole($r)) { $role = $r; break; }
                }
            }
        }

        // Menús dinámicos del sistema por rol
        $systemMenus = [];
        if ($isSystemRoute) {
            $allMenus = config('menus.system', []);
            $rawMenus = $role ? ($allMenus[$role] ?? []) : [];
            // Filtrar por permisos si el item define 'can'
            $systemMenus = array_values(array_filter($rawMenus, function ($item) use ($user) {
                if (!is_array($item)) return false;
                if (!isset($item['can'])) return true;
                $ability = $item['can'];
                return $user && Gate::forUser($user)->allows($ability);
            }));
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'guard' => $isSystemRoute ? 'system' : 'web',
                'role' => $role,
            ],
            'menus' => [
                'system' => $systemMenus,
            ],
            'csrf_token' => csrf_token(), // 🔥 Compartir token CSRF actualizado en cada request
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}

