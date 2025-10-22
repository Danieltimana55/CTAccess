<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Persona;
use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdatePersonaRequest;
use App\Http\Resources\PersonaResource;
use App\Services\PersonaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PersonaQrMailable;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Jornada;
use App\Models\ProgramaFormacion;

class PersonaController extends Controller
{
    public function __construct(private PersonaService $service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $perPage = (int) request()->get('per_page', 15);
        $search = request()->get('search', '');
        
        $query = Persona::query()
            ->orderByDesc('idPersona')
            ->with(['portatiles', 'vehiculos']);

        // Filtro de búsqueda opcional
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Nombre', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%")
                  ->orWhere('TipoPersona', 'like', "%{$search}%");
            });
        }

        $personas = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Personas/Index', [
            'personas' => PersonaResource::collection($personas),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $jornadas = Jornada::activas()
            ->orderBy('hora_inicio')
            ->get(['id', 'nombre', 'descripcion', 'hora_inicio', 'hora_fin']);

        $programas = ProgramaFormacion::activos()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'ficha', 'nivel_formacion', 'fecha_inicio', 'fecha_fin']);

        return Inertia::render('Personas/Create', [
            'jornadas' => $jornadas,
            'programas' => $programas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonaRequest $request): RedirectResponse
    {
        try {
            $persona = $this->service->createWithRelations($request->validated());

            // 🔥 Disparar evento WebSocket para notificar registro en tiempo real (no bloquear si falla)
            try {
                event(new \App\Events\PersonaRegistrada($persona));
            } catch (\Throwable $broadcastEx) {
                // Registrar error pero no interrumpir el flujo
                Log::warning('Error disparando evento de broadcasting (Reverb no disponible)', [
                    'persona_id' => $persona->idPersona,
                    'error' => $broadcastEx->getMessage(),
                ]);
            }

            // 🔥 Enviar email con el QR en segundo plano (espera a que se genere el QR)
            if (!empty($persona->correo)) {
                try {
                    // Despachar job con delay de 10 segundos para dar tiempo a que se genere el QR
                    \App\Jobs\SendPersonaQrEmailJob::dispatch($persona->idPersona, $persona->correo)
                        ->delay(now()->addSeconds(10));
                    
                    Log::info('Email con QR encolado para envío', [
                        'persona_id' => $persona->idPersona,
                        'correo' => $persona->correo,
                    ]);
                } catch (\Throwable $mailEx) {
                    // Registrar pero no interrumpir el flujo de creación
                    Log::error('Error encolando correo de QR a persona', [
                        'persona_id' => $persona->idPersona,
                        'correo' => $persona->correo,
                        'error' => $mailEx->getMessage(),
                    ]);
                }
            }

            // Redirigir de vuelta al formulario con mensaje de éxito
            return redirect()
                ->route('personas.create')
                ->with('success', '¡Registro exitoso! Tu código QR será enviado a tu correo en unos segundos.');
                
        } catch (\Throwable $e) {
            Log::error('Error creando persona', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'No se pudo crear la persona: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Persona $persona): Response
    {
        $persona->load(['portatiles', 'vehiculos']);
        
        return Inertia::render('Personas/Show', [
            'persona' => new PersonaResource($persona),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Persona $persona): Response
    {
        $persona->load(['portatiles', 'vehiculos']);
        
        return Inertia::render('Personas/Edit', [
            'persona' => new PersonaResource($persona),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePersonaRequest $request, Persona $persona): RedirectResponse
    {
        try {
            $this->service->updateWithRelations($persona, $request->validated());
            
            return redirect()
                ->route('personas.show', $persona)
                ->with('success', 'Persona actualizada correctamente');
                
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'No se pudo actualizar la persona: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Persona $persona): RedirectResponse
    {
        try {
            DB::transaction(function () use ($persona) {
                // Delete related models first to respect FK constraints
                $persona->portatiles()->delete();
                $persona->vehiculos()->delete();
                $persona->delete();
            });
            
            return redirect()
                ->route('personas.index')
                ->with('success', 'Persona eliminada correctamente');
                
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar la persona: ' . $e->getMessage()]);
        }
    }
}
