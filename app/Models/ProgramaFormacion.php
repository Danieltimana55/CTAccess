<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ProgramaFormacion extends Model
{
    use HasFactory, HasActivityLog, SoftDeletes;
    
    protected $table = 'programas_formacion';
    
    protected $fillable = [
        'nombre',
        'ficha',
        'fecha_inicio',
        'fecha_fin',
        'nivel_formacion',
        'activo',
        'descripcion'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    /**
     * Relación con personas
     */
    public function personas()
    {
        return $this->hasMany(Persona::class, 'programa_formacion_id');
    }

    /**
     * Verificar si el programa está vigente
     */
    public function estaVigente()
    {
        $hoy = Carbon::now()->startOfDay();
        return $this->activo && 
               $this->fecha_inicio <= $hoy && 
               $this->fecha_fin >= $hoy;
    }

    /**
     * Verificar si el programa ha finalizado
     */
    public function haFinalizado()
    {
        return Carbon::now()->startOfDay() > $this->fecha_fin;
    }

    /**
     * Obtener la duración en meses
     */
    public function getDuracionMeses()
    {
        return $this->fecha_inicio->diffInMonths($this->fecha_fin);
    }

    /**
     * Obtener tiempo restante en días
     */
    public function getDiasRestantes()
    {
        $hoy = Carbon::now()->startOfDay();
        if ($this->haFinalizado()) {
            return 0;
        }
        return $hoy->diffInDays($this->fecha_fin, false);
    }

    /**
     * Scope para programas activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para programas vigentes (activos y dentro de fechas)
     */
    public function scopeVigentes($query)
    {
        $hoy = Carbon::now()->startOfDay();
        return $query->where('activo', true)
                     ->where('fecha_inicio', '<=', $hoy)
                     ->where('fecha_fin', '>=', $hoy);
    }

    /**
     * Scope para programas por ficha
     */
    public function scopePorFicha($query, $ficha)
    {
        return $query->where('ficha', $ficha);
    }
}
