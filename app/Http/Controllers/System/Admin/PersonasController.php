<?php

namespace App\Http\Controllers\System\Admin;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Mail\PersonaQrMailable;

class PersonasController extends Controller
{
    public function index()
    {
        return Inertia::render('System/Admin/Personas');
    }

    public function data(Request $request)
    {
        $query = Persona::with(['portatiles', 'vehiculos']);

        // Filtro de búsqueda general
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Nombre', 'like', "%{$search}%")
                    ->orWhere('documento', 'like', "%{$search}%")
                    ->orWhere('correo', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo de persona
        if ($request->filled('tipo_persona')) {
            $query->where('TipoPersona', $request->tipo_persona);
        }

        // Filtro por portátiles
        if ($request->filled('tiene_portatiles')) {
            if ($request->tiene_portatiles === 'si') {
                $query->has('portatiles');
            } elseif ($request->tiene_portatiles === 'no') {
                $query->doesntHave('portatiles');
            }
        }

        // Filtro por vehículos
        if ($request->filled('tiene_vehiculos')) {
            if ($request->tiene_vehiculos === 'si') {
                $query->has('vehiculos');
            } elseif ($request->tiene_vehiculos === 'no') {
                $query->doesntHave('vehiculos');
            }
        }

        // Ordenamiento
        $orden = $request->orden ?? 'nombre_asc';
        switch ($orden) {
            case 'nombre_desc':
                $query->orderBy('Nombre', 'desc');
                break;
            case 'documento_asc':
                $query->orderBy('documento', 'asc');
                break;
            case 'documento_desc':
                $query->orderBy('documento', 'desc');
                break;
            case 'reciente':
                $query->orderBy('idPersona', 'desc');
                break;
            case 'antiguo':
                $query->orderBy('idPersona', 'asc');
                break;
            default: // nombre_asc
                $query->orderBy('Nombre', 'asc');
                break;
        }

        $perPage = $request->per_page ?? 15;
        $personas = $query->paginate($perPage)->through(function ($persona) {
            return [
                'id' => $persona->idPersona,
                'nombre' => $persona->Nombre,
                'documento' => $persona->documento,
                'tipoPersona' => $persona->TipoPersona,
                'correo' => $persona->correo,
                'qrCode' => $persona->qrCode,
                'portatiles' => $persona->portatiles->map(fn($p) => [
                    'id' => $p->idPortatil,
                    'marca' => $p->Marca,
                    'modelo' => $p->Modelo,
                    'serial' => $p->Serial,
                ]),
                'vehiculos' => $persona->vehiculos->map(fn($v) => [
                    'id' => $v->idVehiculo,
                    'placa' => $v->Placa,
                    'marca' => $v->Marca,
                    'modelo' => $v->Modelo,
                ]),
            ];
        });

        return response()->json([
            'personas' => $personas
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'documento' => ['required', 'string', 'max:50', 'unique:personas,documento'],
            'tipoPersona' => ['required', 'string', 'max:100'],
            'correo' => ['nullable', 'email', 'max:255'],
        ]);

        try {
            $persona = DB::transaction(function () use ($validated) {
                // Generar QR para la persona
                $qrCode = null;
                if (!empty($validated['documento'])) {
                    $qrContent = 'PERSONA_' . $validated['documento'];
                    $qrCode = $this->generateAndStoreQr($qrContent, 'persona');
                }

                // Crear persona
                $persona = Persona::create([
                    'Nombre' => $validated['nombre'],
                    'documento' => $validated['documento'],
                    'TipoPersona' => $validated['tipoPersona'],
                    'correo' => $validated['correo'] ?? null,
                    'qrCode' => $qrCode,
                ]);

                return $persona;
            });

            // Enviar email con el QR si hay correo
            if (!empty($persona->correo)) {
                try {
                    Mail::to($persona->correo)->send(new PersonaQrMailable($persona));
                } catch (\Throwable $mailEx) {
                    Log::error('Error enviando correo de QR a persona', [
                        'persona_id' => $persona->idPersona,
                        'correo' => $persona->correo,
                        'error' => $mailEx->getMessage(),
                    ]);
                }
            }

            return back()->with('success', 'Persona creada correctamente' . ($persona->correo ? ' y correo enviado con QR' : ''));

        } catch (\Throwable $e) {
            Log::error('Error creando persona', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Error al crear persona: ' . $e->getMessage()]);
        }
    }

    /**
     * Genera un código QR y lo almacena en storage
     */
    protected function generateAndStoreQr(string $content, string $prefix = 'qr'): string
    {
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($content);
        $response = Http::get($qrUrl);
        
        if (!$response->ok()) {
            throw new \RuntimeException('No se pudo generar la imagen QR');
        }
        
        $filename = sprintf('%s_%s_%s.png', $prefix, Str::slug($content), Str::random(8));
        $path = 'qrcodes/' . $filename;
        Storage::disk('public')->put($path, $response->body());
        
        return Storage::url($path);
    }

    public function update(Request $request, Persona $persona)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'documento' => ['required', 'string', 'max:50', Rule::unique('personas', 'documento')->ignore($persona->idPersona, 'idPersona')],
            'tipoPersona' => ['required', 'string', 'max:100'],
            'correo' => ['nullable', 'email', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($persona, $validated) {
                // Si el documento cambió, regenerar QR
                if ($validated['documento'] !== $persona->documento) {
                    $qrContent = 'PERSONA_' . $validated['documento'];
                    $qrCode = $this->generateAndStoreQr($qrContent, 'persona');
                    $validated['qrCode'] = $qrCode;
                }

                $persona->update([
                    'Nombre' => $validated['nombre'],
                    'documento' => $validated['documento'],
                    'TipoPersona' => $validated['tipoPersona'],
                    'correo' => $validated['correo'] ?? null,
                    'qrCode' => $validated['qrCode'] ?? $persona->qrCode,
                ]);
            });

            return back()->with('success', 'Persona actualizada correctamente');

        } catch (\Throwable $e) {
            Log::error('Error actualizando persona', [
                'error' => $e->getMessage(),
                'persona_id' => $persona->idPersona
            ]);
            return back()->withErrors(['error' => 'Error al actualizar persona: ' . $e->getMessage()]);
        }
    }

    public function destroy(Persona $persona)
    {
        $persona->delete();
        return back()->with('success', 'Persona eliminada correctamente');
    }
}
