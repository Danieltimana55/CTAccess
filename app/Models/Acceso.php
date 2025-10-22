<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Acceso extends Model
{
    use HasFactory, HasActivityLog;
    
    // Constantes de estado
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_FINALIZADO = 'finalizado';
    const ESTADO_INCIDENCIA = 'incidencia';
    
    protected $fillable = [
        'persona_id',
        'portatil_id',
        'vehiculo_id',
        'fecha_entrada',
        'fecha_salida',
        'estado',
        'usuario_entrada_id',
        'usuario_salida_id'
    ];

    protected $casts = [
        'fecha_entrada' => 'datetime',
        'fecha_salida' => 'datetime',
    ];

    protected $attributes = [
        'estado' => self::ESTADO_ACTIVO,
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'idPersona');
    }

    public function portatil()
    {
        return $this->belongsTo(Portatiles::class, 'portatil_id', 'portatil_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function usuarioEntrada()
    {
        return $this->belongsTo(UsuarioSistema::class, 'usuario_entrada_id', 'idUsuario');
    }

    public function usuarioSalida()
    {
        return $this->belongsTo(UsuarioSistema::class, 'usuario_salida_id', 'idUsuario');
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'accesoId_id_fk');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO)->whereNull('fecha_salida');
    }

    public function scopeFinalizados($query)
    {
        return $query->where('estado', self::ESTADO_FINALIZADO)->whereNotNull('fecha_salida');
    }

    public function scopeDePersona($query, $personaId)
    {
        return $query->where('persona_id', $personaId);
    }

    public function scopeConPortatil($query)
    {
        return $query->whereNotNull('portatil_id');
    }

    public function scopeConVehiculo($query)
    {
        return $query->whereNotNull('vehiculo_id');
    }

    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->format('Y-m-d');
        return $query->whereDate('fecha_entrada', $fecha);
    }

    // Métodos de instancia
    public function estaActivo()
    {
        return $this->estado === self::ESTADO_ACTIVO && is_null($this->fecha_salida);
    }

    public function getDuracionAttribute()
    {
        if (!$this->fecha_entrada) {
            return null;
        }

        $fin = $this->fecha_salida ?? now();
        return $this->fecha_entrada->diffForHumans($fin, true);
    }

    public function marcarSalida($usuarioId = null)
    {
        $this->update([
            'fecha_salida' => now(),
            'estado' => self::ESTADO_FINALIZADO,
            'usuario_salida_id' => $usuarioId
        ]);

        Log::info('Acceso finalizado', [
            'acceso_id' => $this->id,
            'persona_id' => $this->persona_id,
            'usuario_salida_id' => $usuarioId
        ]);

        return $this;
    }

    public function marcarIncidencia($descripcion, $usuarioId = null)
    {
        $this->update(['estado' => self::ESTADO_INCIDENCIA]);

        $incidencia = $this->incidencias()->create([
            'usuario_id_fk' => $usuarioId,
            'tipo' => 'acceso_irregular',
            'descripcion' => $descripcion
        ]);

        Log::warning('Incidencia registrada en acceso', [
            'acceso_id' => $this->id,
            'incidencia_id' => $incidencia->incidenciaId,
            'descripcion' => $descripcion
        ]);

        return $incidencia;
    }

    // Métodos estáticos
    public static function buscarAccesoActivo($personaId)
    {
        return self::activos()->dePersona($personaId)->first();
    }

    public static function registrarEntrada($personaId, $portatilId = null, $vehiculoId = null, $usuarioId = null)
    {
        $acceso = self::create([
            'persona_id' => $personaId,
            'portatil_id' => $portatilId,
            'vehiculo_id' => $vehiculoId,
            'fecha_entrada' => now(),
            'estado' => self::ESTADO_ACTIVO,
            'usuario_entrada_id' => $usuarioId
        ]);

        Log::info('Acceso de entrada registrado', [
            'acceso_id' => $acceso->id,
            'persona_id' => $personaId,
            'portatil_id' => $portatilId,
            'vehiculo_id' => $vehiculoId,
            'usuario_entrada_id' => $usuarioId
        ]);

        return $acceso;
    }

    public static function estadisticasDelDia($fecha = null)
    {
        $fecha = $fecha ?? now()->format('Y-m-d');
        
        return [
            'total_entradas' => self::whereDate('fecha_entrada', $fecha)->count(),
            'total_salidas' => self::whereDate('fecha_salida', $fecha)->count(),
            'activos_actuales' => self::activos()->count(),
            'con_portatil' => self::whereDate('fecha_entrada', $fecha)->conPortatil()->count(),
            'con_vehiculo' => self::whereDate('fecha_entrada', $fecha)->conVehiculo()->count(),
        ];
    }
}
