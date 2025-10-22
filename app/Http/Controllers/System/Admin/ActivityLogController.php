<?php

namespace App\Http\Controllers\System\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\UsuarioSistema;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:system', 'check.system.role:administrador']);
    }

    /**
     * Muestra el índice de logs
     */
    public function index(Request $request)
    {
        $query = ActivityLog::query()
            ->with('usuario:idUsuario,nombre,UserName');

        // Filtro por búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('usuario_nombre', 'like', "%{$search}%")
                    ->orWhere('model_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Filtro por usuario
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        // Filtro por módulo
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filtro por acción
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filtro por severidad
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filtro por fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $query->orderBy('created_at', 'desc');

        $logs = $query->paginate($request->per_page ?? 20);

        // Estadísticas
        $estadisticas = $this->getEstadisticas($request);

        // Opciones para filtros
        $usuarios = UsuarioSistema::select('idUsuario as id', 'nombre', 'UserName')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $modulos = ActivityLog::select('module')
            ->distinct()
            ->whereNotNull('module')
            ->orderBy('module')
            ->pluck('module');

        $acciones = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return Inertia::render('System/Admin/ActivityLogs/Index', [
            'logs' => $logs,
            'estadisticas' => $estadisticas,
            'usuarios' => $usuarios,
            'modulos' => $modulos,
            'acciones' => $acciones,
            'filters' => [
                'search' => $request->search,
                'usuario_id' => $request->usuario_id,
                'module' => $request->module,
                'action' => $request->action,
                'severity' => $request->severity,
                'fecha_desde' => $request->fecha_desde,
                'fecha_hasta' => $request->fecha_hasta,
            ],
        ]);
    }

    /**
     * Muestra el detalle de un log
     */
    public function show(ActivityLog $log)
    {
        $log->load('usuario');

        return Inertia::render('System/Admin/ActivityLogs/Show', [
            'log' => [
                'id' => $log->id,
                'usuario' => $log->usuario ? [
                    'id' => $log->usuario->idUsuario,
                    'nombre' => $log->usuario->nombre,
                    'UserName' => $log->usuario->UserName,
                ] : null,
                'usuario_nombre' => $log->usuario_nombre,
                'usuario_rol' => $log->usuario_rol,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'model_name' => $log->model_name,
                'action' => $log->action,
                'module' => $log->module,
                'description' => $log->description,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'changes' => $log->changes,
                'properties' => $log->properties,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'url' => $log->url,
                'method' => $log->method,
                'status_code' => $log->status_code,
                'severity' => $log->severity,
                'created_at' => $log->created_at,
            ]
        ]);
    }

    /**
     * Obtiene estadísticas
     */
    protected function getEstadisticas(Request $request)
    {
        $query = ActivityLog::query();

        // Aplicar mismos filtros que en el index
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        } else {
            // Por defecto, últimos 30 días
            $query->where('created_at', '>=', now()->subDays(30));
        }

        return [
            'total' => $query->count(),
            'hoy' => ActivityLog::whereDate('created_at', today())->count(),
            'por_severidad' => [
                'info' => (clone $query)->where('severity', 'info')->count(),
                'warning' => (clone $query)->where('severity', 'warning')->count(),
                'error' => (clone $query)->where('severity', 'error')->count(),
                'critical' => (clone $query)->where('severity', 'critical')->count(),
            ],
            'por_accion' => ActivityLog::select('action', DB::raw('count(*) as total'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('action')
                ->orderByDesc('total')
                ->limit(5)
                ->pluck('total', 'action'),
            'usuarios_activos' => ActivityLog::whereDate('created_at', today())
                ->distinct('usuario_id')
                ->count('usuario_id'),
        ];
    }

    /**
     * Limpia logs antiguos
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'dias' => 'required|integer|min:30|max:365',
        ]);

        $fecha = now()->subDays($request->dias);
        $deleted = ActivityLog::where('created_at', '<', $fecha)->delete();

        return back()->with('success', "Se eliminaron {$deleted} registros de auditoría anteriores a " . $fecha->format('Y-m-d'));
    }

    /**
     * Exporta logs a CSV
     */
    public function export(Request $request)
    {
        $query = ActivityLog::query()->with('usuario');

        // Aplicar filtros
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $csv = "ID,Fecha,Usuario,Rol,Acción,Módulo,Descripción,IP,Severidad\n";

        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                '"' . ($log->usuario_nombre ?? 'N/A') . '"',
                '"' . ($log->usuario_rol ?? 'N/A') . '"',
                $log->action,
                $log->module ?? 'N/A',
                '"' . str_replace('"', '""', $log->description) . '"',
                $log->ip_address,
                $log->severity,
            ]) . "\n";
        }

        $filename = 'activity_logs_' . now()->format('Y-m-d_His') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
