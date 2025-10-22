<?php

namespace App\Http\Controllers\System\Admin;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\ProgramaFormacion;
use App\Models\ActivityLog;
use App\Services\ExportService;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PersonaImportExportController extends Controller
{
    public function __construct(
        private ExportService $exportService,
        private ImportService $importService
    ) {}

    /**
     * Vista de importación/exportación
     */
    public function index()
    {
        return Inertia::render('System/Admin/Personas/ImportExport', [
            'stats' => [
                'total_personas' => Persona::count(),
                'total_aprendices' => Persona::where('rol_persona', 'aprendiz')->count(),
                'total_instructores' => Persona::where('rol_persona', 'instructor')->count(),
                'total_funcionarios' => Persona::where('rol_persona', 'funcionario')->count(),
            ],
            'programas' => ProgramaFormacion::select('id', 'codigo', 'nombre')->get(),
        ]);
    }

    /**
     * Exportar personas a CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'rol_persona' => 'nullable|in:aprendiz,instructor,funcionario,visitante',
            'programa_formacion_id' => 'nullable|exists:programas_formacion,id',
        ]);

        // Construir query
        $query = Persona::with('programaFormacion');

        if ($request->filled('rol_persona')) {
            $query->where('rol_persona', $request->rol_persona);
        }

        if ($request->filled('programa_formacion_id')) {
            $query->where('programa_formacion_id', $request->programa_formacion_id);
        }

        $personas = $query->get();

        // Definir columnas para exportación
        $columns = [
            'Tipo de Identificación' => 'tipo_identificacion',
            'Número de Identificación' => 'numero_identificacion',
            'Nombres' => 'nombres',
            'Apellidos' => 'apellidos',
            'Rol' => 'rol_persona',
            'Email' => 'email',
            'Teléfono' => 'telefono',
            'Dirección' => 'direccion',
            'Fecha de Nacimiento' => 'fecha_nacimiento',
            'Género' => 'genero',
            'Código de Programa' => 'programaFormacion.codigo',
            'Nombre de Programa' => 'programaFormacion.nombre',
            'Estado' => 'estado',
            'Fecha de Registro' => 'created_at',
        ];

        // Registrar en auditoría
        ActivityLog::create([
            'usuario_id' => auth('system')->id(),
            'module' => 'Persona',
            'action' => 'export',
            'description' => "Exportación de {$personas->count()} personas",
            'ip_address' => $request->ip(),
        ]);

        return $this->exportService->exportToCsv(
            $personas,
            $columns,
            'personas_export'
        );
    }

    /**
     * Descargar template de importación
     */
    public function downloadTemplate()
    {
        $columns = [
            'Tipo de Identificación' => 'tipo_identificacion',
            'Número de Identificación' => 'numero_identificacion',
            'Nombres' => 'nombres',
            'Apellidos' => 'apellidos',
            'Rol' => 'rol_persona',
            'Email' => 'email',
            'Teléfono' => 'telefono',
            'Dirección' => 'direccion',
            'Fecha de Nacimiento' => 'fecha_nacimiento',
            'Género' => 'genero',
            'Código de Programa' => 'codigo_programa',
        ];

        $exampleRow = [
            'CC',
            '1234567890',
            'Juan Carlos',
            'Pérez Gómez',
            'aprendiz',
            'juan.perez@example.com',
            '3001234567',
            'Calle 123 #45-67',
            '2000-01-15',
            'M',
            'ADSI-2567',
        ];

        return $this->exportService->exportTemplate(
            $columns,
            'personas_template',
            $exampleRow
        );
    }

    /**
     * Validar archivo antes de importar
     */
    public function validateFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $requiredColumns = [
            'Tipo de Identificación',
            'Número de Identificación',
            'Nombres',
            'Apellidos',
            'Rol',
        ];

        $validation = $this->importService->validateFile(
            $request->file('file'),
            $requiredColumns,
            5000
        );

        return response()->json($validation);
    }

    /**
     * Importar personas desde CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'update_existing' => 'boolean',
        ]);

        $updateExisting = $request->boolean('update_existing', false);

        // Mapeo de columnas
        $columns = [
            'Tipo de Identificación' => 'tipo_identificacion',
            'Número de Identificación' => 'numero_identificacion',
            'Nombres' => 'nombres',
            'Apellidos' => 'apellidos',
            'Rol' => 'rol_persona',
            'Email' => 'email',
            'Teléfono' => 'telefono',
            'Dirección' => 'direccion',
            'Fecha de Nacimiento' => 'fecha_nacimiento',
            'Género' => 'genero',
            'Código de Programa' => 'codigo_programa',
        ];

        // Reglas de validación
        $rules = [
            'tipo_identificacion' => 'required|in:CC,TI,CE,PP',
            'numero_identificacion' => 'required|string|max:20',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'rol_persona' => 'required|in:aprendiz,instructor,funcionario,visitante',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:15',
            'direccion' => 'nullable|string|max:200',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'genero' => 'nullable|in:M,F,O',
            'codigo_programa' => 'nullable|string',
        ];

        // Callback para procesar cada fila
        $processRow = function ($data) use ($updateExisting) {
            DB::transaction(function () use ($data, $updateExisting) {
                // Buscar programa si se proporciona código
                $programaId = null;
                if (!empty($data['codigo_programa'])) {
                    $programa = ProgramaFormacion::where('codigo', $data['codigo_programa'])->first();
                    if ($programa) {
                        $programaId = $programa->id;
                    }
                }

                // Preparar datos de persona
                $personaData = [
                    'tipo_identificacion' => $data['tipo_identificacion'],
                    'numero_identificacion' => $data['numero_identificacion'],
                    'nombres' => $data['nombres'],
                    'apellidos' => $data['apellidos'],
                    'rol_persona' => $data['rol_persona'],
                    'email' => $data['email'],
                    'telefono' => $data['telefono'],
                    'direccion' => $data['direccion'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                    'genero' => $data['genero'],
                    'programa_formacion_id' => $programaId,
                    'estado' => 'activo',
                ];

                // Buscar persona existente
                $persona = Persona::where('numero_identificacion', $data['numero_identificacion'])->first();

                if ($persona) {
                    if ($updateExisting) {
                        // Actualizar persona existente
                        $persona->update($personaData);
                    } else {
                        throw new \Exception("La persona con identificación {$data['numero_identificacion']} ya existe");
                    }
                } else {
                    // Crear nueva persona
                    Persona::create($personaData);
                }
            });
        };

        // Ejecutar importación
        $result = $this->importService->importFromCsv(
            $request->file('file'),
            $columns,
            $rules,
            $processRow
        );

        // Registrar en auditoría
        ActivityLog::create([
            'usuario_id' => auth('system')->id(),
            'module' => 'Persona',
            'action' => 'import',
            'description' => "Importación de personas: {$result['stats']['success']} exitosos, {$result['stats']['errors']} errores",
            'ip_address' => $request->ip(),
            'severity' => $result['success'] ? 'info' : 'warning',
        ]);

        return response()->json($result);
    }

    /**
     * Obtener historial de importaciones
     */
    public function history()
    {
        $history = ActivityLog::where('module', 'Persona')
            ->whereIn('action', ['import', 'export'])
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'usuario' => $log->usuario?->usuario ?? 'Sistema',
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'severity' => $log->severity,
                ];
            });

        return response()->json($history);
    }
}
