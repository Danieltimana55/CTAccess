<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documento' => ['nullable', 'string', 'max:255', 'unique:personas,documento'],
            'nombre' => ['required', 'string', 'max:255'],
            'tipoPersona' => ['required', 'string', 'max:255'],
            'jornada_id' => ['nullable', 'exists:jornadas,id'],
            'programa_formacion_id' => ['nullable', 'exists:programas_formacion,id'],
            'correo' => ['nullable', 'string', 'email', 'max:255'],

            'portatiles' => ['sometimes', 'array'],
            'portatiles.*.serial' => ['required_with:portatiles', 'string', 'max:255', 'unique:portatiles,serial'],
            'portatiles.*.marca' => ['required_with:portatiles', 'string', 'max:255'],
            'portatiles.*.modelo' => ['required_with:portatiles', 'string', 'max:255'],

            'vehiculos' => ['sometimes', 'array'],
            'vehiculos.*.tipo' => ['required_with:vehiculos', 'string', 'max:255'],
            'vehiculos.*.placa' => ['required_with:vehiculos', 'string', 'max:255', 'unique:vehiculos,placa'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'Nombre',
            'tipoPersona' => 'Tipo de Persona',
            'jornada_id' => 'Jornada',
            'programa_formacion_id' => 'Programa de Formación',
            'portatiles.*.serial' => 'Serial del portátil',
            'vehiculos.*.placa' => 'Placa del vehículo',
        ];
    }
}

