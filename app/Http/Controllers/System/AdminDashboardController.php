<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Acceso;
use App\Models\Incidencia;
use App\Models\Persona;
use App\Models\UsuarioSistema;
use App\Models\ProgramaFormacion;
use App\Models\Jornada;
use App\Models\Portatiles;
use App\Models\Vehiculo;
use App\Http\Resources\PersonaResource;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        // Asegurar guard del sistema
        $this->middleware('auth:system');
    }

    public function index(Request $request)
    {
        // Obtener filtros
        $filters = $this->getFilters($request);
        
        // KPI cards
        $stats = $this->getStats($filters);
        
        // Datos para gráficos
        $charts = [
            'accesosPorDia' => $this->getAccesosPorDia($filters),
            'accesosPorSemana' => $this->getAccesosPorSemana($filters),
            'accesosPorMes' => $this->getAccesosPorMes($filters),
            'topPersonas' => $this->getTopPersonasConMasAccesos($filters),
            'estadoAccesos' => $this->getEstadoAccesos($filters),
            'incidenciasPorTipo' => $this->getIncidenciasPorTipo($filters),
            'incidenciasPorPrioridad' => $this->getIncidenciasPorPrioridad($filters),
            'promedioDuracion' => $this->getPromedioDuracionAccesos($filters),
            'personasPorTipo' => $this->getPersonasPorTipo($filters),
            'portatilesPorMarca' => $this->getPortatilesPorMarca($filters),
            'vehiculosPorTipo' => $this->getVehiculosPorTipo($filters),
            'accesosPorJornada' => $this->getAccesosPorJornada($filters),
            'accesosPorPrograma' => $this->getAccesosPorPrograma($filters),
        ];

        // Opciones para filtros
        $filterOptions = [
            'jornadas' => Jornada::where('activa', true)->select('id', 'nombre')->get(),
            'programas' => ProgramaFormacion::where('activo', true)->select('id', 'nombre', 'ficha')->get(),
            'tiposPersona' => Persona::select('TipoPersona')
                ->distinct()
                ->whereNotNull('TipoPersona')
                ->pluck('TipoPersona'),
        ];

        return Inertia::render('System/Admin/Dashboard', [
            'stats' => $stats,
            'charts' => $charts,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'meta' => [
                'generated_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Obtener y validar filtros de la request
     */
    private function getFilters(Request $request)
    {
        return [
            'fecha_inicio' => $request->get('fecha_inicio', Carbon::now()->subDays(30)->format('Y-m-d')),
            'fecha_fin' => $request->get('fecha_fin', Carbon::now()->format('Y-m-d')),
            'jornada_id' => $request->get('jornada_id'),
            'programa_id' => $request->get('programa_id'),
            'tipo_persona' => $request->get('tipo_persona'),
        ];
    }

    /**
     * Obtener estadísticas KPI
     */
    private function getStats($filters)
    {
        $today = Carbon::today();
        $queryAccesos = $this->applyAccesosFilters(Acceso::query(), $filters);

        return [
            'personas' => Persona::count(),
            'usuarios' => UsuarioSistema::count(),
            'accesos_hoy' => Acceso::whereDate('fecha_entrada', $today)->count(),
            'accesos_activos' => Acceso::where('estado', Acceso::ESTADO_ACTIVO)->count(),
            'incidencias_7d' => Incidencia::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'incidencias_abiertas' => Incidencia::where('created_at', '>=', Carbon::now()->subDays(30))->count(),
            'programas_formacion' => ProgramaFormacion::count(),
            'programas_vigentes' => ProgramaFormacion::vigentes()->count(),
            'portatiles_registrados' => Portatiles::count(),
            'vehiculos_registrados' => Vehiculo::count(),
            'accesos_periodo' => (clone $queryAccesos)->count(),
        ];
    }

    /**
     * Aplicar filtros a query de accesos
     */
    private function applyAccesosFilters($query, $filters)
    {
        if ($filters['fecha_inicio'] && $filters['fecha_fin']) {
            $query->whereBetween('fecha_entrada', [
                Carbon::parse($filters['fecha_inicio'])->startOfDay(),
                Carbon::parse($filters['fecha_fin'])->endOfDay()
            ]);
        }

        if ($filters['jornada_id']) {
            $query->whereHas('persona', function($q) use ($filters) {
                $q->where('jornada_id', $filters['jornada_id']);
            });
        }

        if ($filters['programa_id']) {
            $query->whereHas('persona', function($q) use ($filters) {
                $q->where('programa_formacion_id', $filters['programa_id']);
            });
        }

        if ($filters['tipo_persona']) {
            $query->whereHas('persona', function($q) use ($filters) {
                $q->where('TipoPersona', $filters['tipo_persona']);
            });
        }

        return $query;
    }

    /**
     * Accesos por día (últimos 14 días o rango filtrado)
     */
    private function getAccesosPorDia($filters)
    {
        $fechaInicio = $filters['fecha_inicio'] 
            ? Carbon::parse($filters['fecha_inicio']) 
            : Carbon::now()->subDays(13);
        
        $fechaFin = $filters['fecha_fin'] 
            ? Carbon::parse($filters['fecha_fin']) 
            : Carbon::now();

        $query = $this->applyAccesosFilters(Acceso::query(), $filters);
        
        return $query->select(
                DB::raw('DATE(fecha_entrada) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('fecha_entrada', [$fechaInicio->startOfDay(), $fechaFin->endOfDay()])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => Carbon::parse($item->fecha)->format('d/m'),
                    'fecha_completa' => Carbon::parse($item->fecha)->format('Y-m-d'),
                    'dia' => Carbon::parse($item->fecha)->locale('es')->isoFormat('ddd'),
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Accesos por semana (últimas 12 semanas)
     */
    private function getAccesosPorSemana($filters)
    {
        $query = $this->applyAccesosFilters(Acceso::query(), $filters);
        
        return $query->select(
                DB::raw('YEAR(fecha_entrada) as año'),
                DB::raw('WEEK(fecha_entrada) as semana'),
                DB::raw('COUNT(*) as total')
            )
            ->where('fecha_entrada', '>=', Carbon::now()->subWeeks(11)->startOfWeek())
            ->groupBy('año', 'semana')
            ->orderBy('año')
            ->orderBy('semana')
            ->get()
            ->map(function ($item) {
                return [
                    'semana' => "S{$item->semana}",
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Accesos por mes (últimos 12 meses)
     */
    private function getAccesosPorMes($filters)
    {
        $query = $this->applyAccesosFilters(Acceso::query(), $filters);
        
        return $query->select(
                DB::raw('YEAR(fecha_entrada) as año'),
                DB::raw('MONTH(fecha_entrada) as mes'),
                DB::raw('COUNT(*) as total')
            )
            ->where('fecha_entrada', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('año', 'mes')
            ->orderBy('año')
            ->orderBy('mes')
            ->get()
            ->map(function ($item) {
                $fecha = Carbon::create($item->año, $item->mes, 1);
                return [
                    'mes' => $fecha->locale('es')->isoFormat('MMM YYYY'),
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Top 5 personas con más accesos
     */
    private function getTopPersonasConMasAccesos($filters)
    {
        $query = $this->applyAccesosFilters(Acceso::query(), $filters);
        
        return $query->select('persona_id', DB::raw('COUNT(*) as total'))
            ->with('persona:idPersona,Nombre,TipoPersona')
            ->groupBy('persona_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->persona->Nombre ?? 'Desconocido',
                    'tipo' => $item->persona->TipoPersona ?? 'N/A',
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Porcentaje de accesos activos vs finalizados
     */
    private function getEstadoAccesos($filters)
    {
        $query = $this->applyAccesosFilters(Acceso::query(), $filters);
        
        return $query->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->get()
            ->map(function ($item) {
                $nombres = [
                    'activo' => 'Activos',
                    'finalizado' => 'Finalizados',
                    'incidencia' => 'Con Incidencia',
                ];
                return [
                    'estado' => $nombres[$item->estado] ?? ucfirst($item->estado),
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Incidencias por tipo
     */
    private function getIncidenciasPorTipo($filters)
    {
        $query = Incidencia::query();
        
        if ($filters['fecha_inicio'] && $filters['fecha_fin']) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['fecha_inicio'])->startOfDay(),
                Carbon::parse($filters['fecha_fin'])->endOfDay()
            ]);
        } else {
            $query->where('created_at', '>=', Carbon::now()->subMonth());
        }

        return $query->select('tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo')
            ->get()
            ->map(function ($item) {
                $nombres = [
                    'seguridad' => 'Seguridad',
                    'acceso' => 'Acceso',
                    'equipamiento' => 'Equipamiento',
                    'comportamiento' => 'Comportamiento',
                    'otro' => 'Otro',
                ];
                return [
                    'tipo' => $nombres[$item->tipo] ?? ucfirst($item->tipo),
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Incidencias por prioridad
     */
    private function getIncidenciasPorPrioridad($filters)
    {
        $query = Incidencia::query();
        
        if ($filters['fecha_inicio'] && $filters['fecha_fin']) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['fecha_inicio'])->startOfDay(),
                Carbon::parse($filters['fecha_fin'])->endOfDay()
            ]);
        } else {
            $query->where('created_at', '>=', Carbon::now()->subMonth());
        }

        return $query->select('prioridad', DB::raw('COUNT(*) as total'))
            ->groupBy('prioridad')
            ->get()
            ->map(function ($item) {
                $nombres = [
                    'baja' => 'Baja',
                    'media' => 'Media',
                    'alta' => 'Alta',
                    'critica' => 'Crítica',
                ];
                return [
                    'prioridad' => $nombres[$item->prioridad] ?? ucfirst($item->prioridad),
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Promedio de duración de accesos (en minutos)
     */
    private function getPromedioDuracionAccesos($filters)
    {
        $query = $this->applyAccesosFilters(Acceso::query(), $filters);
        
        $resultado = $query->whereNotNull('fecha_salida')
            ->select(
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, fecha_entrada, fecha_salida)) as promedio_minutos'),
                DB::raw('MIN(TIMESTAMPDIFF(MINUTE, fecha_entrada, fecha_salida)) as minimo_minutos'),
                DB::raw('MAX(TIMESTAMPDIFF(MINUTE, fecha_entrada, fecha_salida)) as maximo_minutos')
            )
            ->first();

        return [
            'promedio' => round($resultado->promedio_minutos ?? 0, 2),
            'minimo' => round($resultado->minimo_minutos ?? 0, 2),
            'maximo' => round($resultado->maximo_minutos ?? 0, 2),
            'promedio_horas' => round(($resultado->promedio_minutos ?? 0) / 60, 2),
        ];
    }

    /**
     * Personas por tipo
     */
    private function getPersonasPorTipo($filters)
    {
        return Persona::select('TipoPersona', DB::raw('COUNT(*) as total'))
            ->whereNotNull('TipoPersona')
            ->groupBy('TipoPersona')
            ->get()
            ->map(function ($item) {
                $nombres = [
                    'Estudiante' => 'Estudiantes',
                    'Funcionario' => 'Funcionarios',
                    'Visitante' => 'Visitantes',
                    'Instructor' => 'Instructores',
                    'Contratista' => 'Contratistas',
                ];
                return [
                    'tipo' => $nombres[$item->TipoPersona] ?? $item->TipoPersona,
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Portátiles por marca
     */
    private function getPortatilesPorMarca($filters)
    {
        return Portatiles::select('marca', DB::raw('COUNT(*) as total'))
            ->whereNotNull('marca')
            ->groupBy('marca')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'marca' => $item->marca,
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Vehículos por tipo
     */
    private function getVehiculosPorTipo($filters)
    {
        return Vehiculo::select('tipo', DB::raw('COUNT(*) as total'))
            ->whereNotNull('tipo')
            ->groupBy('tipo')
            ->get()
            ->map(function ($item) {
                $nombres = [
                    'moto' => 'Motocicletas',
                    'carro' => 'Automóviles',
                    'bicicleta' => 'Bicicletas',
                    'otro' => 'Otros',
                ];
                return [
                    'tipo' => $nombres[$item->tipo] ?? ucfirst($item->tipo),
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Accesos por jornada
     */
    private function getAccesosPorJornada($filters)
    {
        $query = $this->applyAccesosFilters(Acceso::query(), $filters);
        
        return $query->join('personas', 'accesos.persona_id', '=', 'personas.idPersona')
            ->join('jornadas', 'personas.jornada_id', '=', 'jornadas.id')
            ->select('jornadas.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('jornadas.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'jornada' => $item->nombre,
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Accesos por programa de formación
     */
    private function getAccesosPorPrograma($filters)
    {
        $query = $this->applyAccesosFilters(Acceso::query(), $filters);
        
        return $query->join('personas', 'accesos.persona_id', '=', 'personas.idPersona')
            ->join('programas_formacion', 'personas.programa_formacion_id', '=', 'programas_formacion.id')
            ->select(
                'programas_formacion.nombre',
                'programas_formacion.ficha',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('programas_formacion.id', 'programas_formacion.nombre', 'programas_formacion.ficha')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'programa' => $item->nombre,
                    'ficha' => $item->ficha,
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Vista de gestión de personas
     */
    public function personasView()
    {
        return Inertia::render('System/Admin/Personas', [
            'title' => 'Gestión de Personas'
        ]);
    }

    /**
     * API endpoint para obtener personas con paginación
     */
    public function personas(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search', '');

        $query = Persona::query()
            ->orderByDesc('idPersona')
            ->with(['portatiles', 'vehiculos']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Nombre', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%")
                  ->orWhere('TipoPersona', 'like', "%{$search}%");
            });
        }

        $personas = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'personas' => [
                'data' => PersonaResource::collection($personas->items())->toArray($request),
                'links' => $personas->linkCollection()->toArray(),
                'total' => $personas->total(),
                'from' => $personas->firstItem(),
                'to' => $personas->lastItem(),
                'current_page' => $personas->currentPage(),
                'last_page' => $personas->lastPage(),
                'per_page' => $personas->perPage(),
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }
}
