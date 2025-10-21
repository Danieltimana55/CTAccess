<?php

namespace App\Http\Controllers\System\Celador;

use App\Http\Controllers\Controller;
use App\Models\Acceso;
use App\Models\Persona;
use App\Models\Portatil;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccesoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:system');
    }

    public function index(Request $request)
    {
        $query = Acceso::with(['persona', 'portatil', 'vehiculo']);

        // Filtro de búsqueda por persona
        if ($search = $request->get('q')) {
            $query->whereHas('persona', function ($q) use ($search) {
                $q->where('Nombre', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        // Filtro por tipo de persona
        if ($tipoPersona = $request->get('tipo_persona')) {
            $query->whereHas('persona', function ($q) use ($tipoPersona) {
                $q->where('TipoPersona', $tipoPersona);
            });
        }

        // Filtro por portátil
        if ($tienePortatil = $request->get('tiene_portatil')) {
            if ($tienePortatil === 'si') {
                $query->whereNotNull('portatil_id');
            } elseif ($tienePortatil === 'no') {
                $query->whereNull('portatil_id');
            }
        }

        // Filtro por vehículo
        if ($tieneVehiculo = $request->get('tiene_vehiculo')) {
            if ($tieneVehiculo === 'si') {
                $query->whereNotNull('vehiculo_id');
            } elseif ($tieneVehiculo === 'no') {
                $query->whereNull('vehiculo_id');
            }
        }

        // Filtro por rango de fechas
        if ($fechaDesde = $request->get('fecha_desde')) {
            $query->whereDate('fecha_entrada', '>=', $fechaDesde);
        }
        if ($fechaHasta = $request->get('fecha_hasta')) {
            $query->whereDate('fecha_entrada', '<=', $fechaHasta);
        }

        // Ordenamiento
        $orden = $request->get('orden', 'reciente');
        switch ($orden) {
            case 'antiguo':
                $query->oldest('fecha_entrada');
                break;
            case 'nombre_asc':
                $query->join('personas', 'accesos.persona_id', '=', 'personas.idPersona')
                      ->orderBy('personas.Nombre', 'asc')
                      ->select('accesos.*');
                break;
            case 'nombre_desc':
                $query->join('personas', 'accesos.persona_id', '=', 'personas.idPersona')
                      ->orderBy('personas.Nombre', 'desc')
                      ->select('accesos.*');
                break;
            case 'duracion_asc':
                $query->orderByRaw('TIMESTAMPDIFF(MINUTE, fecha_entrada, COALESCE(fecha_salida, NOW())) ASC');
                break;
            case 'duracion_desc':
                $query->orderByRaw('TIMESTAMPDIFF(MINUTE, fecha_entrada, COALESCE(fecha_salida, NOW())) DESC');
                break;
            default: // reciente
                $query->latest('fecha_entrada');
                break;
        }

        $accesos = $query->paginate(15)->withQueryString();

        // Transformar los datos para el frontend
        $accesos->getCollection()->transform(function ($acceso) {
            return [
                'id' => $acceso->id,
                'estado' => $acceso->estado,
                'fecha_entrada' => $acceso->fecha_entrada,
                'fecha_salida' => $acceso->fecha_salida,
                'persona' => $acceso->persona ? [
                    'Nombre' => $acceso->persona->Nombre,
                    'numero_documento' => $acceso->persona->documento,
                    'documento' => $acceso->persona->documento,
                    'correo' => $acceso->persona->correo,
                    'tipo_persona' => $acceso->persona->TipoPersona,
                    'TipoPersona' => $acceso->persona->TipoPersona,
                ] : null,
                'portatil' => $acceso->portatil ? [
                    'serial' => $acceso->portatil->serial,
                    'marca' => $acceso->portatil->marca,
                    'modelo' => $acceso->portatil->modelo,
                ] : null,
                'vehiculo' => $acceso->vehiculo ? [
                    'placa' => $acceso->vehiculo->placa,
                    'tipo' => $acceso->vehiculo->tipo,
                ] : null,
            ];
        });

        // Estadísticas
        $estadisticas = [
            'total' => Acceso::count(),
            'activos' => Acceso::where('estado', 'activo')->count(),
            'finalizados' => Acceso::where('estado', 'finalizado')->count(),
            'hoy' => Acceso::whereDate('fecha_entrada', today())->count(),
        ];

        return Inertia::render('System/Celador/Accesos/Index', [
            'filters' => [
                'q' => $request->get('q'),
                'estado' => $request->get('estado'),
                'tipo_persona' => $request->get('tipo_persona'),
                'tiene_portatil' => $request->get('tiene_portatil'),
                'tiene_vehiculo' => $request->get('tiene_vehiculo'),
                'fecha_desde' => $request->get('fecha_desde'),
                'fecha_hasta' => $request->get('fecha_hasta'),
                'orden' => $request->get('orden', 'reciente'),
            ],
            'accesos' => $accesos,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Store a newly created access
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'persona_id' => ['required', 'exists:personas,idPersona'],
            'portatil_id' => ['nullable', 'exists:portatiles,portatil_id'],
            'vehiculo_id' => ['nullable', 'exists:vehiculos,vehiculo_id'],
            'fecha_entrada' => ['required', 'date'],
        ]);

        // Verificar que el portátil pertenece a la persona (si se especifica)
        if (!empty($validated['portatil_id'])) {
            $portatil = Portatil::find($validated['portatil_id']);
            if ($portatil && $portatil->persona_id != $validated['persona_id']) {
                return back()->withErrors(['portatil_id' => 'El portátil no pertenece a esta persona.']);
            }
        }

        // Verificar que el vehículo pertenece a la persona (si se especifica)
        if (!empty($validated['vehiculo_id'])) {
            $vehiculo = Vehiculo::find($validated['vehiculo_id']);
            if ($vehiculo && $vehiculo->persona_id != $validated['persona_id']) {
                return back()->withErrors(['vehiculo_id' => 'El vehículo no pertenece a esta persona.']);
            }
        }

        Acceso::create([
            'persona_id' => $validated['persona_id'],
            'portatil_id' => $validated['portatil_id'] ?? null,
            'vehiculo_id' => $validated['vehiculo_id'] ?? null,
            'fecha_entrada' => $validated['fecha_entrada'],
            'estado' => 'activo',
        ]);

        return back()->with('success', 'Acceso registrado exitosamente.');
    }

    /**
     * Get portátiles of a specific persona
     */
    public function getPortatiles($personaId)
    {
        $portatiles = Portatil::where('persona_id', $personaId)
            ->select('portatil_id', 'serial', 'marca', 'modelo')
            ->get();

        return response()->json([
            'portatiles' => $portatiles
        ]);
    }

    /**
     * Get vehiculos of a specific persona
     */
    public function getVehiculos($personaId)
    {
        $vehiculos = Vehiculo::where('persona_id', $personaId)
            ->select('vehiculo_id', 'tipo', 'placa')
            ->get();

        return response()->json([
            'vehiculos' => $vehiculos
        ]);
    }
}


