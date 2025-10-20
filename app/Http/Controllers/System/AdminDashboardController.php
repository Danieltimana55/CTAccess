<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Acceso;
use App\Models\Incidencia;
use App\Models\Persona;
use App\Models\UsuarioSistema;
use App\Models\ProgramaFormacion;
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

    public function index()
    {
        $today = Carbon::today();

        // KPI cards
        $stats = [
            'personas' => Persona::count(),
            'usuarios' => UsuarioSistema::count(),
            'accesos_hoy' => Acceso::whereDate('fecha_entrada', $today)->count(),
            'incidencias_7d' => Incidencia::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'programas_formacion' => ProgramaFormacion::count(),
            'programas_vigentes' => ProgramaFormacion::vigentes()->count(),
        ];

        // Datos para gráficos
        // Gráfico 1: Accesos por día (últimos 14 días)
        $accesosPorDia = Acceso::select(
                DB::raw('DATE(fecha_entrada) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->where('fecha_entrada', '>=', Carbon::now()->subDays(13)->startOfDay())
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => Carbon::parse($item->fecha)->format('d/m'),
                    'dia' => Carbon::parse($item->fecha)->locale('es')->isoFormat('ddd'),
                    'total' => $item->total,
                ];
            });

        // Gráfico 2: Incidencias por tipo (último mes)
        $incidenciasPorTipo = Incidencia::select('tipo', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', Carbon::now()->subMonth())
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

        // Gráfico 3: Personas por tipo
        $personasPorTipo = Persona::select('TipoPersona', DB::raw('COUNT(*) as total'))
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

        return Inertia::render('System/Admin/Dashboard', [
            'stats' => $stats,
            'charts' => [
                'accesosPorDia' => $accesosPorDia,
                'incidenciasPorTipo' => $incidenciasPorTipo,
                'personasPorTipo' => $personasPorTipo,
            ],
            'meta' => [
                'generated_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    public function personasView()
    {
        return Inertia::render('System/Admin/Personas', [
            'title' => 'Gestión de Personas'
        ]);
    }

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
