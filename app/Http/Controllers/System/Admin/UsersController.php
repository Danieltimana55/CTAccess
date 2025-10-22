<?php

namespace App\Http\Controllers\System\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsuarioSistema;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $search = (string) $request->query('q', '');

        $query = UsuarioSistema::query()
            ->with(['roles', 'principalRole'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('UserName', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('idUsuario');

        $users = $query->paginate(10)->withQueryString();

        // Minimal transformation for the table
        $usersData = $users->through(function (UsuarioSistema $u) {
            return [
                'id' => $u->idUsuario,
                'UserName' => $u->UserName,
                'nombre' => $u->nombre,
                'tipo_documento' => $u->tipo_documento,
                'documento' => $u->documento,
                'correo' => $u->correo,
                'activo' => (bool) $u->activo,
                'rol_principal' => $u->principalRole?->nombre,
                'rol_principal_id' => $u->rol_principal_id,
                'roles' => $u->roles->pluck('id')->values(),
            ];
        });

        return Inertia::render('System/Admin/Users/Index', [
            'filters' => ['q' => $search],
            'users' => $usersData,
            'roles' => Role::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function create()
    {
        return Inertia::render('System/Admin/Users/Create', [
            'roles' => Role::orderBy('nombre')->get(['id', 'nombre'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'UserName' => ['required', 'string', 'max:255', 'unique:usuarios_sistema,UserName'],
            'password' => ['required', 'string', 'min:8'],
            'nombre'   => ['required', 'string', 'max:255'],
            'tipo_documento' => ['nullable', 'string', 'max:20'],
            'documento' => ['nullable', 'string', 'max:50', 'unique:usuarios_sistema,documento'],
            'correo' => ['nullable', 'email', 'max:100', 'unique:usuarios_sistema,correo'],
            'activo'   => ['boolean'],
            'roles'    => ['array'],
            'roles.*'  => ['integer', Rule::exists('roles', 'id')],
            'rol_principal_id' => ['nullable', 'integer', Rule::exists('roles', 'id')],
        ]);

        $user = new UsuarioSistema();
        $user->UserName = $validated['UserName'];
        $user->password = $validated['password']; // mutator hashes
        $user->nombre = $validated['nombre'];
        $user->tipo_documento = $validated['tipo_documento'] ?? null;
        $user->documento = $validated['documento'] ?? null;
        $user->correo = $validated['correo'] ?? null;
        $user->activo = $validated['activo'] ?? true;
        $user->rol_principal_id = $validated['rol_principal_id'] ?? null;
        $user->save();

        if (!empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }
        // Ensure principal role is included in pivot
        if ($user->rol_principal_id) {
            $user->roles()->syncWithoutDetaching([$user->rol_principal_id]);
        }

        return redirect()->route('system.admin.users.index')
            ->with('success', 'Usuario creado correctamente');
    }

    public function edit(UsuarioSistema $user)
    {
        $user->load(['roles', 'principalRole']);
        return Inertia::render('System/Admin/Users/Edit', [
            'user' => [
                'id' => $user->idUsuario,
                'UserName' => $user->UserName,
                'nombre' => $user->nombre,
                'tipo_documento' => $user->tipo_documento,
                'documento' => $user->documento,
                'correo' => $user->correo,
                'activo' => (bool) $user->activo,
                'rol_principal_id' => $user->rol_principal_id,
                'roles' => $user->roles->pluck('id')->values(),
            ],
            'roles' => Role::orderBy('nombre')->get(['id', 'nombre'])
        ]);
    }

    public function update(Request $request, UsuarioSistema $user)
    {
        $validated = $request->validate([
            'UserName' => ['required', 'string', 'max:255', Rule::unique('usuarios_sistema', 'UserName')->ignore($user->idUsuario, 'idUsuario')],
            'password' => ['nullable', 'string', 'min:8'],
            'nombre'   => ['required', 'string', 'max:255'],
            'tipo_documento' => ['nullable', 'string', 'max:20'],
            'documento' => ['nullable', 'string', 'max:50', Rule::unique('usuarios_sistema', 'documento')->ignore($user->idUsuario, 'idUsuario')],
            'correo' => ['nullable', 'email', 'max:100', Rule::unique('usuarios_sistema', 'correo')->ignore($user->idUsuario, 'idUsuario')],
            'activo'   => ['boolean'],
            'roles'    => ['array'],
            'roles.*'  => ['integer', Rule::exists('roles', 'id')],
            'rol_principal_id' => ['nullable', 'integer', Rule::exists('roles', 'id')],
        ]);

        $user->UserName = $validated['UserName'];
        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        $user->nombre = $validated['nombre'];
        $user->tipo_documento = $validated['tipo_documento'] ?? null;
        $user->documento = $validated['documento'] ?? null;
        $user->correo = $validated['correo'] ?? null;
        $user->activo = $validated['activo'] ?? $user->activo;
        $user->rol_principal_id = $validated['rol_principal_id'] ?? null;
        $user->save();

        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }
        if ($user->rol_principal_id) {
            $user->roles()->syncWithoutDetaching([$user->rol_principal_id]);
        }

        return redirect()->route('system.admin.users.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(UsuarioSistema $user)
    {
        $user->delete();
        return back()->with('success', 'Usuario eliminado');
    }
}
