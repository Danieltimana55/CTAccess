<?php

namespace App\Http\Controllers\System\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramaFormacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class ProgramasFormacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('System/Admin/ProgramasFormacion/Index');
    }

    /**
     * Get programas data for DataTable
     */
    public function data(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search', '');
        $estado = $request->get('estado', 'todos'); // todos, activos, inactivos, vigentes, finalizados

        $query = ProgramaFormacion::query()
            ->withCount('personas')
            ->orderByDesc('created_at');

        // Filtro de búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('ficha', 'like', "%{$search}%")
                  ->orWhere('nivel_formacion', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        switch ($estado) {
            case 'activos':
                $query->where('activo', true);
                break;
            case 'inactivos':
                $query->where('activo', false);
                break;
            case 'vigentes':
                $query->vigentes();
                break;
            case 'finalizados':
                $query->where('fecha_fin', '<', now()->startOfDay());
                break;
        }

        $programas = $query->paginate($perPage)->withQueryString();

        // Agregar información adicional a cada programa
        $programas->getCollection()->transform(function ($programa) {
            return [
                'id' => $programa->id,
                'nombre' => $programa->nombre,
                'ficha' => $programa->ficha,
                'fecha_inicio' => $programa->fecha_inicio->format('Y-m-d'),
                'fecha_fin' => $programa->fecha_fin->format('Y-m-d'),
                'nivel_formacion' => $programa->nivel_formacion,
                'activo' => $programa->activo,
                'descripcion' => $programa->descripcion,
                'personas_count' => $programa->personas_count,
                'esta_vigente' => $programa->estaVigente(),
                'ha_finalizado' => $programa->haFinalizado(),
                'dias_restantes' => $programa->getDiasRestantes(),
                'duracion_meses' => $programa->getDuracionMeses(),
                'created_at' => $programa->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json($programas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'ficha' => 'required|string|max:50|unique:programas_formacion,ficha',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'nivel_formacion' => 'required|in:Técnico,Tecnólogo,Especialización,Curso Corto,Diplomado',
            'activo' => 'boolean',
            'descripcion' => 'nullable|string|max:1000',
        ], [
            'nombre.required' => 'El nombre del programa es obligatorio',
            'ficha.required' => 'La ficha es obligatoria',
            'ficha.unique' => 'Ya existe un programa con esta ficha',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'nivel_formacion.required' => 'El nivel de formación es obligatorio',
            'nivel_formacion.in' => 'El nivel de formación no es válido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $programa = ProgramaFormacion::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Programa de formación creado exitosamente',
            'programa' => $programa
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProgramaFormacion $programa): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'ficha' => 'required|string|max:50|unique:programas_formacion,ficha,' . $programa->id,
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'nivel_formacion' => 'required|in:Técnico,Tecnólogo,Especialización,Curso Corto,Diplomado',
            'activo' => 'boolean',
            'descripcion' => 'nullable|string|max:1000',
        ], [
            'nombre.required' => 'El nombre del programa es obligatorio',
            'ficha.required' => 'La ficha es obligatoria',
            'ficha.unique' => 'Ya existe un programa con esta ficha',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'nivel_formacion.required' => 'El nivel de formación es obligatorio',
            'nivel_formacion.in' => 'El nivel de formación no es válido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $programa->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Programa de formación actualizado exitosamente',
            'programa' => $programa
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProgramaFormacion $programa): JsonResponse
    {
        // Verificar si tiene personas asociadas
        if ($programa->personas()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el programa porque tiene personas asociadas. Desactívelo en su lugar.'
            ], 422);
        }

        $programa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Programa de formación eliminado exitosamente'
        ], 200);
    }

    /**
     * Toggle active status
     */
    public function toggleActivo(ProgramaFormacion $programa): JsonResponse
    {
        $programa->activo = !$programa->activo;
        $programa->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado exitosamente',
            'activo' => $programa->activo
        ], 200);
    }
}
