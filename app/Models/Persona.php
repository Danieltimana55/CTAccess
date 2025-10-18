<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Persona extends Authenticatable
{
    use HasFactory, Notifiable;
    
    protected $primaryKey = 'idPersona';
    protected $fillable = ['documento', 'Nombre', 'TipoPersona', 'jornada_id', 'programa_formacion_id', 'qrCode', 'correo', 'contraseña'];
    
    protected $hidden = [
        'contraseña',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'contraseña' => 'hashed',
        ];
    }

    // Override para usar 'correo' en lugar de 'email'
    public function getAuthIdentifierName()
    {
        return 'correo';
    }

    // Override para usar 'contraseña' en lugar de 'password'
    public function getAuthPassword()
    {
        return $this->contraseña;
    }
    
    // Override para que Laravel sepa qué campo usar para la contraseña
    public function getAuthPasswordName()
    {
        return 'contraseña';
    }

    // Método para verificar contraseña
    public function checkPassword($password)
    {
        return Hash::check($password, $this->contraseña);
    }

    public function getRouteKeyName(): string
    {
        return 'idPersona';
    }

    public function portatiles()
    {
        return $this->hasMany(Portatiles::class, 'persona_id');
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'persona_id');
    }

    public function accesos()
    {
        return $this->hasMany(Acceso::class, 'persona_id');
    }

    public function jornada()
    {
        return $this->belongsTo(Jornada::class, 'jornada_id');
    }

    public function programaFormacion()
    {
        return $this->belongsTo(ProgramaFormacion::class, 'programa_formacion_id');
    }

    // Métodos para QR
    public function tieneAccesoActivo()
    {
        return $this->accesos()->where('estado', Acceso::ESTADO_ACTIVO)->whereNull('fecha_salida')->exists();
    }

    public function getAccesoActivo()
    {
        return $this->accesos()->where('estado', Acceso::ESTADO_ACTIVO)->whereNull('fecha_salida')->first();
    }

    public function getPortatilPrincipal()
    {
        return $this->portatiles()->first();
    }

    public function getVehiculoPrincipal()
    {
        return $this->vehiculos()->first();
    }

    public static function buscarPorQr($qrCode)
    {
        return self::where('qrCode', $qrCode)->first();
    }

    public static function buscarPorDocumento($documento)
    {
        return self::where('documento', $documento)->first();
    }

    public function getInfoCompleta()
    {
        return [
            'persona' => $this,
            'portatiles' => $this->portatiles,
            'vehiculos' => $this->vehiculos,
            'acceso_activo' => $this->getAccesoActivo(),
            'tiene_acceso_activo' => $this->tieneAccesoActivo()
        ];
    }
}
