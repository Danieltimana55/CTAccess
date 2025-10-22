<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    // Solo created_at, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'usuario_nombre',
        'usuario_rol',
        'model_type',
        'model_id',
        'model_name',
        'action',
        'module',
        'description',
        'old_values',
        'new_values',
        'properties',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'status_code',
        'country',
        'city',
        'severity',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que realizó la acción
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioSistema::class, 'usuario_id', 'idUsuario');
    }

    /**
     * Relación polimórfica con el modelo afectado
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Scopes
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('usuario_id', $userId);
    }

    public function scopeByModel($query, $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);
        
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Métodos estáticos para logging rápido
     */
    public static function log(string $action, ?Model $model = null, array $properties = []): self
    {
        $usuario = auth('system')->user();
        
        return static::create([
            'usuario_id' => $usuario?->idUsuario,
            'usuario_nombre' => $usuario?->nombre,
            'usuario_rol' => $usuario?->principalRole?->nombre,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'model_name' => method_exists($model, 'getActivityLogName') ? $model->getActivityLogName() : null,
            'action' => $action,
            'module' => static::inferModule($model),
            'description' => static::generateDescription($action, $model),
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'severity' => static::inferSeverity($action),
        ]);
    }

    /**
     * Infiere el módulo basado en el modelo
     */
    protected static function inferModule(?Model $model): ?string
    {
        if (!$model) return null;

        $className = class_basename($model);
        
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
     * Genera descripción legible
     */
    protected static function generateDescription(string $action, ?Model $model): string
    {
        $usuario = auth('system')->user()?->nombre ?? 'Sistema';
        $modelName = $model ? class_basename($model) : 'registro';
        
        $actionMap = [
            'created' => "creó un $modelName",
            'updated' => "actualizó un $modelName",
            'deleted' => "eliminó un $modelName",
            'restored' => "restauró un $modelName",
            'viewed' => "visualizó un $modelName",
            'exported' => "exportó datos de $modelName",
            'imported' => "importó datos de $modelName",
            'login' => "inició sesión",
            'logout' => "cerró sesión",
            'failed_login' => "intentó iniciar sesión sin éxito",
        ];

        return "$usuario " . ($actionMap[$action] ?? $action);
    }

    /**
     * Infiere severidad basada en la acción
     */
    protected static function inferSeverity(string $action): string
    {
        $severityMap = [
            'deleted' => 'warning',
            'failed_login' => 'warning',
            'error' => 'error',
            'exception' => 'critical',
        ];

        return $severityMap[$action] ?? 'info';
    }

    /**
     * Obtiene cambios formateados para mostrar
     */
    public function getChangesAttribute(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Getter para display del modelo
     */
    public function getModelDisplayAttribute(): string
    {
        if ($this->model_name) {
            return $this->model_name;
        }

        if ($this->model_type && $this->model_id) {
            return class_basename($this->model_type) . " #{$this->model_id}";
        }

        return 'N/A';
    }
}
