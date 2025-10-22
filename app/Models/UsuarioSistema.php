<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class UsuarioSistema extends Authenticatable
{
    use HasFactory, Notifiable, HasActivityLog, SoftDeletes;

    protected $table = 'usuarios_sistema';
    protected $primaryKey = 'idUsuario';
    protected $fillable = ['UserName', 'password', 'nombre', 'rol', 'activo', 'tipo_documento', 'documento', 'correo'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    // Laravel guard name convenience (optional)
    protected $guard = 'system';

    // Roles
    public const ROL_ADMIN = 'admin';
    public const ROL_CELADOR = 'celador';

    // Username field for authentication (we'll use in controller)
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    // Ensure passwords are always hashed when set
    public function setPasswordAttribute($value): void
    {
        if (! empty($value)) {
            $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
        }
    }

    public function accesosEntrada()
    {
        return $this->hasMany(Acceso::class, 'usuario_entrada_id');
    }

    public function accesosSalida()
    {
        return $this->hasMany(Acceso::class, 'usuario_salida_id');
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'usuario_id_fk');
    }

    // RBAC relationships
    public function roles()
    {
        // Many-to-many with roles through usuario_rol pivot
        return $this->belongsToMany(Role::class, 'usuario_rol', 'usuario_id', 'rol_id');
    }

    // Optional main role linked by FK (non-breaking)
    public function principalRole()
    {
        return $this->belongsTo(Role::class, 'rol_principal_id');
    }

    // Convenience: check if user has a given role name
    public function hasSystemRole(string $roleName): bool
    {
        return $this->roles()->where('nombre', $roleName)->exists();
    }

    // Convenience: check permission via roles
    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($q) use ($permissionName) {
                $q->where('nombre', $permissionName);
            })
            ->exists();
    }

    // Role helpers
    /**
     * Admin check prefers RBAC roles (nombre = 'administrador'),
     * falls back to legacy string column ('admin').
     */
    public function isAdmin(): bool
    {
        if ($this->relationLoaded('roles')) {
            if ($this->roles->contains(fn ($r) => $r->nombre === 'administrador')) {
                return true;
            }
        } else {
            if ($this->hasSystemRole('administrador')) {
                return true;
            }
        }
        return $this->rol === self::ROL_ADMIN;
    }

    /**
     * Celador check prefers RBAC roles (nombre = 'celador'),
     * falls back to legacy string column ('celador').
     */
    public function isCelador(): bool
    {
        if ($this->relationLoaded('roles')) {
            if ($this->roles->contains(fn ($r) => $r->nombre === 'celador')) {
                return true;
            }
        } else {
            if ($this->hasSystemRole('celador')) {
                return true;
            }
        }
        return $this->rol === self::ROL_CELADOR;
    }

    /**
     * Generic role check: prefer RBAC role name match, fallback to legacy string.
     */
    public function hasRole(string $role): bool
    {
        if ($this->relationLoaded('roles')) {
            if ($this->roles->contains(fn ($r) => $r->nombre === $role)) {
                return true;
            }
        } else {
            if ($this->hasSystemRole($role)) {
                return true;
            }
        }
        return $this->rol === $role;
    }

    // Scope for active users
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
