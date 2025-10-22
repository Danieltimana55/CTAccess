<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActivityLog
{
    /**
     * Boot del trait
     */
    protected static function bootHasActivityLog(): void
    {
        // Evento: Modelo creado
        static::created(function (Model $model) {
            $model->logActivity('created');
        });

        // Evento: Modelo actualizado
        static::updated(function (Model $model) {
            $model->logActivity('updated', [
                'old_values' => $model->getOriginal(),
                'new_values' => $model->getAttributes(),
            ]);
        });

        // Evento: Modelo eliminado
        static::deleted(function (Model $model) {
            $model->logActivity('deleted');
        });
    }

    /**
     * Relación polimórfica con los logs
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'model');
    }

    /**
     * Registra una actividad
     */
    public function logActivity(
        string $action,
        array $additionalData = [],
        string $severity = 'info'
    ): ActivityLog {
        $usuario = auth('system')->user();

        $data = array_merge([
            'usuario_id' => $usuario?->idUsuario,
            'usuario_nombre' => $usuario?->nombre,
            'usuario_rol' => $usuario?->principalRole?->nombre,
            'model_type' => get_class($this),
            'model_id' => $this->getKey(),
            'model_name' => $this->getActivityLogName(),
            'action' => $action,
            'module' => $this->getActivityModule(),
            'description' => $this->generateActivityDescription($action),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'severity' => $severity,
        ], $additionalData);

        return ActivityLog::create($data);
    }

    /**
     * Nombre del modelo para el log (override en cada modelo si es necesario)
     */
    public function getActivityLogName(): string
    {
        // Intenta obtener nombre, título, o representación del modelo
        if (property_exists($this, 'nombre') && $this->nombre) {
            return $this->nombre;
        }

        if (property_exists($this, 'Nombre') && $this->Nombre) {
            return $this->Nombre;
        }

        if (property_exists($this, 'UserName') && $this->UserName) {
            return $this->UserName;
        }

        return class_basename($this) . " #{$this->getKey()}";
    }

    /**
     * Módulo del modelo (override si es necesario)
     */
    public function getActivityModule(): string
    {
        $className = class_basename($this);
        
        $moduleMap = [
            'Persona' => 'personas',
            'UsuarioSistema' => 'usuarios',
            'Acceso' => 'accesos',
            'Incidencia' => 'incidencias',
            'Portatil' => 'portatiles',
            'Vehiculo' => 'vehiculos',
            'ProgramaFormacion' => 'programas_formacion',
            'Permission' => 'permisos',
            'Role' => 'roles',
        ];

        return $moduleMap[$className] ?? strtolower($className);
    }

    /**
     * Genera descripción de la actividad
     */
    protected function generateActivityDescription(string $action): string
    {
        $usuario = auth('system')->user()?->nombre ?? 'Sistema';
        $modelName = $this->getActivityLogName();
        
        $actionMap = [
            'created' => "creó",
            'updated' => "actualizó",
            'deleted' => "eliminó",
            'restored' => "restauró",
            'viewed' => "visualizó",
        ];

        $actionText = $actionMap[$action] ?? $action;
        
        return "$usuario $actionText: $modelName";
    }

    /**
     * Obtiene el historial de actividades de este modelo
     */
    public function getActivityHistory()
    {
        return $this->activityLogs()
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtiene el último cambio
     */
    public function getLastActivity()
    {
        return $this->activityLogs()
            ->latest()
            ->first();
    }
}
