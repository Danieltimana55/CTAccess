<?php

namespace App\Http\Controllers\System\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use App\Models\UsuarioSistema;

class LoginController extends Controller
{
    public function create()
    {
        return Inertia::render('System/Auth/Login', [
            'canResetPassword' => false,
            'status' => session('status'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'UserName' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Validar que el usuario esté activo antes de intentar login
        $user = UsuarioSistema::where('UserName', $data['UserName'])->first();
        if (! $user || ! $user->activo) {
            throw ValidationException::withMessages([
                'UserName' => __('Este usuario está inactivo o no existe.'),
            ]);
        }

        if (Auth::guard('system')->attempt($data, false)) {
            $request->session()->regenerate();

            // Redirección por rol
            $user = Auth::guard('system')->user();
            if ($user->isAdmin()) {
                return redirect()->intended(route('system.admin.dashboard'));
            }
            if ($user->isCelador()) {
                return redirect()->intended(route('system.celador.dashboard'));
            }

            // Fallback al panel genérico si hubiera otro rol
            return redirect()->intended(route('system.panel'));
        }

        throw ValidationException::withMessages([
            'UserName' => __('Credenciales inválidas'),
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('system')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
