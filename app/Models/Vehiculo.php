<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use HasFactory, HasActivityLog, SoftDeletes;
    
    protected $fillable = ['persona_id', 'tipo', 'placa', 'qrCode'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'idPersona');
    }

    public function accesos()
    {
        return $this->hasMany(Acceso::class, 'vehiculo_id');
    }

    // Métodos para QR
    public static function buscarPorQr($qrCode)
    {
        return self::where('qrCode', $qrCode)->first();
    }

    public static function buscarPorPlaca($placa)
    {
        return self::where('placa', $placa)->first();
    }

    public function perteneceAPersona($personaId)
    {
        return $this->persona_id == $personaId;
    }

    public function getInfoCompleta()
    {
        return [
            'vehiculo' => $this,
            'persona' => $this->persona,
            'accesos_recientes' => $this->accesos()->latest()->take(5)->get()
        ];
    }
}
