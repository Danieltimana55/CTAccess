<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jornada extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'hora_inicio',
        'hora_fin',
        'dias_semana',
        'activa'
    ];

    protected $casts = [
        'dias_semana' => 'array',
        'activa' => 'boolean',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
    ];

    /**
     * Relación con personas
     */
    public function personas()
    {
        return $this->hasMany(Persona::class, 'jornada_id');
    }

    /**
     * Verificar si la jornada aplica para un día específico
     */
    public function aplicaParaDia($numeroDia)
    {
        if (empty($this->dias_semana)) {
            return true;
        }
        return in_array($numeroDia, $this->dias_semana);
    }

    /**
     * Verificar si una hora está dentro de la jornada
     */
    public function estaEnHorario($hora)
    {
        $horaActual = is_string($hora) ? strtotime($hora) : $hora;
        $inicio = strtotime($this->hora_inicio);
        $fin = strtotime($this->hora_fin);

        return $horaActual >= $inicio && $horaActual <= $fin;
    }

    /**
     * Scope para jornadas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
