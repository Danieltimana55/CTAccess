<?php

namespace App\Http\Controllers\System\Admin;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\UsuarioSistema;
use App\Models\Portatil;
use App\Models\Vehiculo;
use App\Models\ProgramaFormacion;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PapeleraController extends Controller
{
    /**
     * Muestra la papelera con todos los registros eliminados
     */
    public function index(Request $request)
    {
        $tipo = $request->get('tipo', 'todos');

        // Obtener registros eliminados por tipo
        $personas = $tipo === 'todos' || $tipo === 'personas' 
            ? Persona::onlyTrashed()->with('portatiles', 'vehiculos')->get()
            : collect();

        $usuarios = $tipo === 'todos' || $tipo === 'usuarios'
            ? UsuarioSistema::onlyTrashed()->with('roles')->get()
            : collect();

        $portatiles = $tipo === 'todos' || $tipo === 'portatiles'
            ? Portatil::onlyTrashed()->with('persona')->get()
            : collect();

        $vehiculos = $tipo === 'todos' || $tipo === 'vehiculos'
            ? Vehiculo::onlyTrashed()->with('persona')->get()
            : collect();

        $programas = $tipo === 'todos' || $tipo === 'programas'
            ? ProgramaFormacion::onlyTrashed()->withCount('personas')->get()
            : collect();

        // Unificar en un solo array con tipo
        $items = collect();

        foreach ($personas as $persona) {
            $items->push([
                'id' => $persona->idPersona,
                'tipo' => 'persona',
                'tipo_display' => 'Persona',
                'nombre' => $persona->Nombre,
                'identificacion' => $persona->documento,
                'info_adicional' => $persona->TipoPersona,
                'deleted_at' => $persona->deleted_at,
                'deleted_at_formatted' => $persona->deleted_at->diffForHumans(),
                'can_restore' => true,
            ]);
        }

        foreach ($usuarios as $usuario) {
            $items->push([
                'id' => $usuario->idUsuario,
                'tipo' => 'usuario',
                'tipo_display' => 'Usuario',
                'nombre' => $usuario->nombre,
                'identificacion' => $usuario->UserName,
                'info_adicional' => $usuario->correo,
                'deleted_at' => $usuario->deleted_at,
                'deleted_at_formatted' => $usuario->deleted_at->diffForHumans(),
                'can_restore' => true,
            ]);
        }

        foreach ($portatiles as $portatil) {
            $items->push([
                'id' => $portatil->portatil_id,
                'tipo' => 'portatil',
                'tipo_display' => 'Portátil',
                'nombre' => $portatil->marca . ' ' . $portatil->modelo,
                'identificacion' => $portatil->serial,
                'info_adicional' => $portatil->persona?->Nombre ?? 'Sin asignar',
                'deleted_at' => $portatil->deleted_at,
                'deleted_at_formatted' => $portatil->deleted_at->diffForHumans(),
                'can_restore' => true,
            ]);
        }

        foreach ($vehiculos as $vehiculo) {
            $items->push([
                'id' => $vehiculo->id,
                'tipo' => 'vehiculo',
                'tipo_display' => 'Vehículo',
                'nombre' => $vehiculo->tipo,
                'identificacion' => $vehiculo->placa,
                'info_adicional' => $vehiculo->persona?->Nombre ?? 'Sin asignar',
                'deleted_at' => $vehiculo->deleted_at,
                'deleted_at_formatted' => $vehiculo->deleted_at->diffForHumans(),
                'can_restore' => true,
            ]);
        }

        foreach ($programas as $programa) {
            $items->push([
                'id' => $programa->id,
                'tipo' => 'programa',
                'tipo_display' => 'Programa',
                'nombre' => $programa->nombre,
                'identificacion' => $programa->ficha,
                'info_adicional' => $programa->nivel_formacion,
                'deleted_at' => $programa->deleted_at,
                'deleted_at_formatted' => $programa->deleted_at->diffForHumans(),
                'can_restore' => true,
            ]);
        }

        // Ordenar por fecha de eliminación (más reciente primero)
        $items = $items->sortByDesc('deleted_at')->values();

        // Estadísticas
        $estadisticas = [
            'total' => $items->count(),
            'personas' => Persona::onlyTrashed()->count(),
            'usuarios' => UsuarioSistema::onlyTrashed()->count(),
            'portatiles' => Portatil::onlyTrashed()->count(),
            'vehiculos' => Vehiculo::onlyTrashed()->count(),
            'programas' => ProgramaFormacion::onlyTrashed()->count(),
        ];

        return Inertia::render('System/Admin/Papelera/Index', [
            'items' => $items,
            'estadisticas' => $estadisticas,
            'filtro_tipo' => $tipo,
        ]);
    }

    /**
     * Restaura un registro eliminado
     */
    public function restore(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:persona,usuario,portatil,vehiculo,programa',
            'id' => 'required|integer',
        ]);

        try {
            $restored = false;

            switch ($request->tipo) {
                case 'persona':
                    $item = Persona::onlyTrashed()->findOrFail($request->id);
                    $item->restore();
                    $restored = true;
                    $nombre = $item->Nombre;
                    break;

                case 'usuario':
                    $item = UsuarioSistema::onlyTrashed()->findOrFail($request->id);
                    $item->restore();
                    $restored = true;
                    $nombre = $item->nombre;
                    break;

                case 'portatil':
                    $item = Portatil::onlyTrashed()->findOrFail($request->id);
                    $item->restore();
                    $restored = true;
                    $nombre = $item->serial;
                    break;

                case 'vehiculo':
                    $item = Vehiculo::onlyTrashed()->findOrFail($request->id);
                    $item->restore();
                    $restored = true;
                    $nombre = $item->placa;
                    break;

                case 'programa':
                    $item = ProgramaFormacion::onlyTrashed()->findOrFail($request->id);
                    $item->restore();
                    $restored = true;
                    $nombre = $item->nombre;
                    break;
            }

            if ($restored) {
                // Registrar en logs
                ActivityLog::log('restored', $item, [
                    'restored_by' => auth('system')->user()->nombre,
                ]);

                return back()->with('success', "'{$nombre}' ha sido restaurado correctamente");
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al restaurar: ' . $e->getMessage()]);
        }

        return back()->withErrors(['error' => 'No se pudo restaurar el registro']);
    }

    /**
     * Elimina permanentemente un registro
     */
    public function forceDelete(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:persona,usuario,portatil,vehiculo,programa',
            'id' => 'required|integer',
            'confirmacion' => 'required|string|in:ELIMINAR PERMANENTEMENTE',
        ]);

        try {
            $deleted = false;
            $nombre = '';

            switch ($request->tipo) {
                case 'persona':
                    $item = Persona::onlyTrashed()->findOrFail($request->id);
                    $nombre = $item->Nombre;
                    
                    // Eliminar relaciones primero
                    $item->portatiles()->forceDelete();
                    $item->vehiculos()->forceDelete();
                    $item->accesos()->delete();
                    
                    $item->forceDelete();
                    $deleted = true;
                    break;

                case 'usuario':
                    $item = UsuarioSistema::onlyTrashed()->findOrFail($request->id);
                    $nombre = $item->nombre;
                    
                    // Eliminar relaciones
                    $item->roles()->detach();
                    
                    $item->forceDelete();
                    $deleted = true;
                    break;

                case 'portatil':
                    $item = Portatil::onlyTrashed()->findOrFail($request->id);
                    $nombre = $item->serial;
                    $item->forceDelete();
                    $deleted = true;
                    break;

                case 'vehiculo':
                    $item = Vehiculo::onlyTrashed()->findOrFail($request->id);
                    $nombre = $item->placa;
                    $item->forceDelete();
                    $deleted = true;
                    break;

                case 'programa':
                    $item = ProgramaFormacion::onlyTrashed()->findOrFail($request->id);
                    $nombre = $item->nombre;
                    $item->forceDelete();
                    $deleted = true;
                    break;
            }

            if ($deleted) {
                // Registrar en logs
                ActivityLog::create([
                    'usuario_id' => auth('system')->user()->idUsuario,
                    'usuario_nombre' => auth('system')->user()->nombre,
                    'action' => 'permanently_deleted',
                    'module' => $request->tipo . 's',
                    'description' => auth('system')->user()->nombre . " eliminó permanentemente: {$nombre}",
                    'severity' => 'warning',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return back()->with('success', "'{$nombre}' ha sido eliminado permanentemente");
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }

        return back()->withErrors(['error' => 'No se pudo eliminar el registro']);
    }

    /**
     * Vacía toda la papelera (con confirmación)
     */
    public function empty(Request $request)
    {
        $request->validate([
            'confirmacion' => 'required|string|in:VACIAR PAPELERA',
        ]);

        try {
            DB::transaction(function () {
                $count = 0;

                // Personas
                $personas = Persona::onlyTrashed()->get();
                foreach ($personas as $persona) {
                    $persona->portatiles()->forceDelete();
                    $persona->vehiculos()->forceDelete();
                    $persona->forceDelete();
                    $count++;
                }

                // Usuarios
                $usuarios = UsuarioSistema::onlyTrashed()->get();
                foreach ($usuarios as $usuario) {
                    $usuario->roles()->detach();
                    $usuario->forceDelete();
                    $count++;
                }

                // Portátiles
                $count += Portatil::onlyTrashed()->forceDelete();

                // Vehículos
                $count += Vehiculo::onlyTrashed()->forceDelete();

                // Programas
                $count += ProgramaFormacion::onlyTrashed()->forceDelete();

                // Log
                ActivityLog::create([
                    'usuario_id' => auth('system')->user()->idUsuario,
                    'usuario_nombre' => auth('system')->user()->nombre,
                    'action' => 'emptied_trash',
                    'module' => 'papelera',
                    'description' => auth('system')->user()->nombre . " vació la papelera ({$count} elementos)",
                    'severity' => 'warning',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            });

            return back()->with('success', 'La papelera ha sido vaciada correctamente');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al vaciar la papelera: ' . $e->getMessage()]);
        }
    }
}
