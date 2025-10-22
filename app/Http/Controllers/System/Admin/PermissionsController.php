<?php

namespace App\Http\Controllers\System\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PermissionsController extends Controller
{
    public function index(Request $request)
    {
        $search = (string) $request->query('q', '');
        $moduloFilter = (string) $request->query('modulo', '');

        $query = Permission::query()
            ->with('roles')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nombre', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when($moduloFilter !== '', function ($q) use ($moduloFilter) {
                $q->where('modulo', $moduloFilter);
            })
            ->orderBy('modulo')
            ->orderBy('nombre');

        $permisos = $query->paginate(15)->withQueryString();

        $permisosData = $permisos->through(function (Permission $p) {
            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'modulo' => $p->modulo,
                'descripcion' => $p->descripcion,
                'roles' => $p->roles->pluck('nombre')->values(),
                'roles_ids' => $p->roles->pluck('id')->values(),
                'roles_count' => $p->roles->count(),
            ];
        });

        // Obtener módulos únicos para el filtro
        $modulos = Permission::distinct()
            ->pluck('modulo')
            ->filter()
            ->sort()
            ->values();

        return Inertia::render('System/Admin/Permissions/Index', [
            'filters' => [
                'q' => $search,
                'modulo' => $moduloFilter,
            ],
            'permisos' => $permisosData,
            'modulos' => $modulos,
            'roles' => Role::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:permisos,nombre'],
            'modulo' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'roles' => ['array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        $permiso = new Permission();
        $permiso->nombre = $validated['nombre'];
        $permiso->modulo = $validated['modulo'] ?? null;
        $permiso->descripcion = $validated['descripcion'] ?? null;
        $permiso->save();

        if (!empty($validated['roles'])) {
            $permiso->roles()->sync($validated['roles']);
        }

        return redirect()->route('system.admin.permissions.index')
            ->with('success', 'Permiso creado correctamente');
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('permisos', 'nombre')->ignore($permission->id)],
            'modulo' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'roles' => ['array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        $permission->nombre = $validated['nombre'];
        $permission->modulo = $validated['modulo'] ?? null;
        $permission->descripcion = $validated['descripcion'] ?? null;
        $permission->save();

        if (isset($validated['roles'])) {
            $permission->roles()->sync($validated['roles']);
        }

        return redirect()->route('system.admin.permissions.index')
            ->with('success', 'Permiso actualizado correctamente');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return back()->with('success', 'Permiso eliminado');
    }
}
