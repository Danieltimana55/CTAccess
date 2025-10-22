# 📋 Sistema de Auditoría - Activity Logs

## ✅ Implementación Completada

El sistema de auditoría ha sido implementado exitosamente en CTAccess. Este módulo proporciona **trazabilidad completa** de todas las acciones realizadas en el sistema.

---

## 🎯 Características Implementadas

### 1. **Registro Automático de Actividades**
- ✅ Tracking de **creación, edición y eliminación** en modelos principales
- ✅ Registro automático mediante Trait `HasActivityLog`
- ✅ Captura de cambios (antes/después) en actualizaciones
- ✅ Registro de login/logout y accesos al sistema

### 2. **Información Capturada**
```
- Usuario que realizó la acción
- Fecha y hora exacta
- Tipo de acción (created, updated, deleted, etc.)
- Modelo afectado (Persona, Usuario, Acceso, etc.)
- Valores anteriores y nuevos
- IP del usuario
- User Agent (navegador)
- URL de la petición
- Severidad (info, warning, error, critical)
```

### 3. **Interfaz de Administración**
- ✅ Dashboard con estadísticas
- ✅ Filtros avanzados (usuario, módulo, acción, fecha, severidad)
- ✅ Vista detallada de cada log
- ✅ Exportación a CSV
- ✅ Limpieza automática de logs antiguos

---

## 📊 Modelos con Auditoría Habilitada

Los siguientes modelos ya tienen auditoría automática:

```php
✅ Persona
✅ UsuarioSistema
✅ Portatil
✅ Vehiculo
✅ Acceso
```

### ¿Cómo agregar auditoría a otros modelos?

Solo agrega el trait en tu modelo:

```php
<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;

class TuModelo extends Model
{
    use HasActivityLog;
    
    // ... resto del código
}
```

**¡Eso es todo!** El modelo ahora tiene auditoría automática.

---

## 🔍 Acciones Registradas Automáticamente

### Por el Trait (en modelos)
- `created` - Cuando se crea un registro
- `updated` - Cuando se actualiza un registro
- `deleted` - Cuando se elimina un registro

### Por el Middleware (en requests HTTP)
- `login` - Inicio de sesión exitoso
- `failed_login` - Intento fallido de login
- `logout` - Cierre de sesión
- `exported` - Exportación de datos
- `viewed` - Visualización de recursos (GET importantes)

---

## 💻 Uso Manual del Sistema de Logs

### Registrar una actividad personalizada

```php
use App\Models\ActivityLog;

// Opción 1: Usando el helper estático
ActivityLog::log('custom_action', $model, [
    'additional_data' => 'value'
]);

// Opción 2: Desde un modelo con el trait
$persona->logActivity('exported', [
    'format' => 'PDF',
    'records' => 100
], 'info');

// Opción 3: Creación manual completa
ActivityLog::create([
    'usuario_id' => auth('system')->id(),
    'action' => 'bulk_import',
    'module' => 'personas',
    'description' => 'Importación masiva de 500 personas',
    'properties' => ['file' => 'personas.xlsx'],
    'ip_address' => request()->ip(),
]);
```

---

## 📖 Ejemplos de Uso

### 1. Ver historial de un modelo específico

```php
// En tu controlador
$persona = Persona::find(1);
$historial = $persona->activityLogs()
    ->with('usuario')
    ->orderBy('created_at', 'desc')
    ->get();
```

### 2. Ver último cambio

```php
$ultimoCambio = $persona->getLastActivity();
```

### 3. Consultas avanzadas

```php
// Logs de hoy
$logsHoy = ActivityLog::today()->get();

// Logs de un usuario específico
$logsUsuario = ActivityLog::byUser(5)->get();

// Logs de un módulo
$logsPersonas = ActivityLog::byModule('personas')->get();

// Logs con severidad crítica
$logsCriticos = ActivityLog::bySeverity('critical')->get();

// Últimos 7 días
$logsRecientes = ActivityLog::recent(7)->get();
```

---

## 🎨 Vista de Administración

### Acceso
`Sistema → Auditoría` (solo para administradores)

### Características de la interfaz
- ✅ **Estadísticas en tiempo real**: Total, hoy, por severidad
- ✅ **Filtros múltiples**: Búsqueda, usuario, módulo, acción, severidad, fechas
- ✅ **Paginación**: 20 registros por página
- ✅ **Vista detallada**: Click en "Ver detalle" para información completa
- ✅ **Exportar CSV**: Botón para descargar logs filtrados
- ✅ **Limpieza**: Eliminar logs anteriores a X días (mínimo 30)

---

## 🔐 Seguridad y Privacidad

### Datos sensibles
El sistema **NO registra**:
- ❌ Contraseñas
- ❌ Tokens de autenticación
- ❌ Información de pago

### Protección
- ✅ Solo administradores pueden ver los logs
- ✅ Los logs son **append-only** (solo se puede agregar, no editar)
- ✅ Captura de IP para rastreo de acciones sospechosas

---

## 🧹 Mantenimiento

### Limpieza Automática (Recomendado)

Agrega a tu `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Limpiar logs mayores a 90 días cada domingo a las 2 AM
    $schedule->command('logs:cleanup --days=90')->weekly()->sundays()->at('02:00');
}
```

### Limpieza Manual

Desde la interfaz web:
1. Ir a **Sistema → Auditoría**
2. Click en botón **"Limpiar Antiguos"**
3. Especificar días a conservar (mínimo 30)
4. Confirmar

O por línea de comandos:

```bash
# Crear el comando primero
php artisan make:command CleanupActivityLogs

# Luego ejecutar
php artisan logs:cleanup --days=90
```

---

## 📈 Performance y Optimización

### Índices de Base de Datos
La tabla `activity_logs` tiene índices optimizados:
```sql
- usuario_id + created_at
- model_type + model_id
- action + created_at
- module + created_at
- ip_address
- created_at
```

### Recomendaciones
1. ✅ Limpiar logs cada 90 días mínimo
2. ✅ Exportar logs antes de eliminar (compliance)
3. ✅ Monitorear el tamaño de la tabla
4. ✅ Considerar archivado en almacenamiento externo para logs muy antiguos

### Ver tamaño de la tabla

```sql
SELECT 
    table_name AS 'Tabla',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Tamaño (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'ctaccess'
AND table_name = 'activity_logs';
```

---

## 🔔 Alertas y Notificaciones (Futuro)

### Próximas implementaciones sugeridas:

```php
// Alertar en acciones críticas
if ($log->severity === 'critical') {
    // Enviar email al administrador
    Mail::to('admin@ctaccess.com')->send(new CriticalActivityAlert($log));
}

// Detectar comportamiento anormal
if ($usuario->activityLogs()->today()->count() > 1000) {
    // Alerta de actividad sospechosa
    Log::warning('Usuario con actividad anormal', ['usuario_id' => $usuario->id]);
}
```

---

## 📊 Reportes Disponibles

### Exportar a CSV
- Incluye todos los logs filtrados
- Formato: ID, Fecha, Usuario, Rol, Acción, Módulo, Descripción, IP, Severidad
- Nombre del archivo: `activity_logs_YYYY-MM-DD_HHmmss.csv`

### Campos en el export
```csv
ID,Fecha,Usuario,Rol,Acción,Módulo,Descripción,IP,Severidad
1,2025-10-21 10:30:45,Admin User,administrador,created,personas,Admin User creó: Juan Pérez,192.168.1.1,info
```

---

## 🚀 Próximos Pasos Recomendados

### FASE 2 - Mejoras de Auditoría

1. **Dashboard de Auditoría Mejorado**
   - Gráficos de actividad por hora/día
   - Top usuarios más activos
   - Acciones más frecuentes
   - Mapa de calor de actividad

2. **Alertas Automáticas**
   - Email en acciones críticas
   - Webhook para integraciones
   - Slack/Discord notifications

3. **Compliance y Reportes**
   - Reportes PDF formateados
   - Export a formatos estándar (JSON, XML)
   - Firma digital de logs (inmutabilidad)

4. **Geolocalización**
   - Usar servicio de IP geolocation
   - Mostrar mapa de accesos
   - Alertar por ubicaciones inusuales

---

## 🐛 Troubleshooting

### Los logs no se registran

**Verificar:**
1. ¿La migración se ejecutó? `php artisan migrate:status`
2. ¿El modelo tiene el trait? Revisar `use HasActivityLog;`
3. ¿El middleware está activo? Revisar `bootstrap/app.php`

### Performance lento

**Soluciones:**
1. Limpiar logs antiguos
2. Agregar más índices si es necesario
3. Considerar tabla de archivado separada

### Logs muy grandes

**Limitar qué se registra:**
```php
// En HasActivityLog trait, personaliza:
protected static function bootHasActivityLog(): void
{
    // Solo registrar en producción
    if (!app()->environment('production')) {
        return;
    }
    
    // ... resto del código
}
```

---

## 📞 Soporte

Para dudas o mejoras sobre el sistema de auditoría:
- Revisar logs: `storage/logs/laravel.log`
- Verificar tabla: `SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10;`
- Debug: Agregar `Log::info()` en el middleware/trait

---

## ✨ Resumen

El sistema de auditoría está **completamente funcional** y proporciona:

✅ **Trazabilidad total** de acciones
✅ **Cumplimiento legal** (compliance)
✅ **Seguridad mejorada** (detección de anomalías)
✅ **Debugging facilitado** (saber qué cambió y cuándo)
✅ **Interfaz amigable** para administradores

**Estado:** 🟢 PRODUCCIÓN LISTO

---

*Última actualización: Octubre 21, 2025*
*Versión: 1.0.0*
