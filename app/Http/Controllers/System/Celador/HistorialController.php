<?php

namespace App\Http\Controllers\System\Celador;

use App\Http\Controllers\Controller;
use App\Models\Acceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class HistorialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:system');
    }

    protected function ensureRole(): void
    {
        $user = Auth::guard('system')->user();
        
        if (! $user) {
            abort(403, 'No autorizado');
        }

        // Admin tiene acceso a todo, incluyendo funcionalidades de celador
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return;
        }

        // Si no es admin, debe ser celador
        if (! method_exists($user, 'isCelador') || ! $user->isCelador()) {
            abort(403, 'No autorizado');
        }
    }

    public function index(Request $request)
    {
        $this->ensureRole();

        $query = Acceso::with(['persona', 'portatil', 'vehiculo'])
            ->latest('fecha_entrada');

        // Filtro de búsqueda
        if ($search = $request->get('q')) {
            $query->whereHas('persona', function ($q) use ($search) {
                $q->where('Nombre', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        // Filtro por rango de fechas
        if ($fechaDesde = $request->get('fecha_desde')) {
            $query->whereDate('fecha_entrada', '>=', $fechaDesde);
        }

        if ($fechaHasta = $request->get('fecha_hasta')) {
            $query->whereDate('fecha_entrada', '<=', $fechaHasta);
        }

        $historial = $query->paginate(15)->withQueryString();

        // Estadísticas del período seleccionado
        $statsQuery = Acceso::query();
        
        if ($fechaDesde) {
            $statsQuery->whereDate('fecha_entrada', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $statsQuery->whereDate('fecha_entrada', '<=', $fechaHasta);
        }
        if ($search) {
            $statsQuery->whereHas('persona', function ($q) use ($search) {
                $q->where('Nombre', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        $estadisticas = [
            'total' => $statsQuery->count(),
            'con_portatil' => (clone $statsQuery)->whereNotNull('portatil_id')->count(),
            'con_vehiculo' => (clone $statsQuery)->whereNotNull('vehiculo_id')->count(),
            'personas_unicas' => (clone $statsQuery)->distinct('persona_id')->count('persona_id'),
        ];

        return Inertia::render('System/Celador/Historial/Index', [
            'filters' => [
                'q' => $request->get('q'),
                'fecha_desde' => $request->get('fecha_desde'),
                'fecha_hasta' => $request->get('fecha_hasta'),
                'periodo' => $request->get('periodo'),
            ],
            'registros' => $historial,
            'estadisticas' => $estadisticas,
        ]);
    }

    public function exportPDF(Request $request)
    {
        $this->ensureRole();

        $query = Acceso::with(['persona', 'portatil', 'vehiculo'])
            ->latest('fecha_entrada');

        // Aplicar mismos filtros que en index
        if ($search = $request->get('q')) {
            $query->whereHas('persona', function ($q) use ($search) {
                $q->where('Nombre', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        if ($fechaDesde = $request->get('fecha_desde')) {
            $query->whereDate('fecha_entrada', '>=', $fechaDesde);
        }

        if ($fechaHasta = $request->get('fecha_hasta')) {
            $query->whereDate('fecha_entrada', '<=', $fechaHasta);
        }

        // Obtener todos los registros (sin paginación para PDF)
        $accesos = $query->get();

        // Estadísticas para el PDF
        $estadisticas = [
            'total' => $accesos->count(),
            'con_portatil' => $accesos->whereNotNull('portatil_id')->count(),
            'con_vehiculo' => $accesos->whereNotNull('vehiculo_id')->count(),
            'personas_unicas' => $accesos->pluck('persona_id')->unique()->count(),
            'duracion_total' => $this->calcularDuracionTotal($accesos),
            'duracion_promedio' => $this->calcularDuracionPromedio($accesos),
        ];

        // Información del período
        $periodo = [
            'desde' => $fechaDesde ?? 'Inicio',
            'hasta' => $fechaHasta ?? 'Actualidad',
            'descripcion' => $this->obtenerDescripcionPeriodo($fechaDesde, $fechaHasta),
        ];

        // Información del usuario que genera el reporte
        $usuario = Auth::guard('system')->user();

        $data = [
            'accesos' => $accesos,
            'estadisticas' => $estadisticas,
            'periodo' => $periodo,
            'usuario' => $usuario,
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
        ];

        // Generar PDF usando DomPDF
        $pdf = Pdf::loadView('pdfs.reporte-accesos', $data);
        
        // Configurar opciones del PDF
        $pdf->setPaper('letter', 'portrait');
        
        // Nombre del archivo
        $nombreArchivo = 'Reporte_Accesos_' . now()->format('Y-m-d_His') . '.pdf';
        
        // Descargar PDF
        return $pdf->download($nombreArchivo);
    }

    private function calcularDuracionTotal($accesos)
    {
        $totalMinutos = 0;
        
        foreach ($accesos as $acceso) {
            if ($acceso->fecha_entrada && $acceso->fecha_salida) {
                $entrada = \Carbon\Carbon::parse($acceso->fecha_entrada);
                $salida = \Carbon\Carbon::parse($acceso->fecha_salida);
                $totalMinutos += $entrada->diffInMinutes($salida);
            }
        }
        
        $horas = floor($totalMinutos / 60);
        $minutos = $totalMinutos % 60;
        
        return "{$horas}h {$minutos}m";
    }

    private function calcularDuracionPromedio($accesos)
    {
        $accesosConSalida = $accesos->filter(function ($acceso) {
            return $acceso->fecha_entrada && $acceso->fecha_salida;
        });
        
        if ($accesosConSalida->count() === 0) {
            return '0h 0m';
        }
        
        $totalMinutos = 0;
        
        foreach ($accesosConSalida as $acceso) {
            $entrada = \Carbon\Carbon::parse($acceso->fecha_entrada);
            $salida = \Carbon\Carbon::parse($acceso->fecha_salida);
            $totalMinutos += $entrada->diffInMinutes($salida);
        }
        
        $promedioMinutos = floor($totalMinutos / $accesosConSalida->count());
        $horas = floor($promedioMinutos / 60);
        $minutos = $promedioMinutos % 60;
        
        return "{$horas}h {$minutos}m";
    }

    private function obtenerDescripcionPeriodo($desde, $hasta)
    {
        if (!$desde && !$hasta) {
            return 'Todos los registros';
        }
        
        if ($desde && $hasta) {
            if ($desde === $hasta) {
                return 'Día: ' . \Carbon\Carbon::parse($desde)->format('d/m/Y');
            }
            return 'Del ' . \Carbon\Carbon::parse($desde)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($hasta)->format('d/m/Y');
        }
        
        if ($desde) {
            return 'Desde el ' . \Carbon\Carbon::parse($desde)->format('d/m/Y');
        }
        
        return 'Hasta el ' . \Carbon\Carbon::parse($hasta)->format('d/m/Y');
    }
}
