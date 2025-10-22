# 🚀 Sistema de Generación de QR en Segundo Plano

## 📝 Descripción

Este sistema permite registrar personas **instantáneamente** sin esperar a que se generen los códigos QR. Los QR se generan en segundo plano mediante un sistema de colas (Jobs).

## ⚡ Ventajas

- ✅ **Registro instantáneo**: La persona se crea inmediatamente sin esperar
- ✅ **Sin bloqueos**: El usuario puede seguir registrando más personas
- ✅ **Resiliente**: Si falla la generación, se reintenta automáticamente
- ✅ **Escalable**: Puede procesar múltiples QR en paralelo

## 🔧 Cómo Funciona

1. **Usuario registra persona** → Se guarda en DB con ruta del QR predefinida
2. **Job se encola** → Laravel encola el trabajo de generar el QR
3. **Worker procesa** → Un worker toma el job y genera el QR físico
4. **QR generado** → El archivo se guarda en la ruta predefinida

## 🚦 Iniciar el Sistema de Colas

### Opción 1: Worker en Terminal (Desarrollo)

```bash
# Iniciar el worker de colas
php artisan queue:work

# Con logs detallados
php artisan queue:work --verbose

# Procesar solo la cola 'default'
php artisan queue:work --queue=default

# Con timeout de 60 segundos
php artisan queue:work --timeout=60
```

### Opción 2: Worker en Segundo Plano (Producción)

**Windows (PowerShell):**
```powershell
# Iniciar en segundo plano
Start-Process powershell -ArgumentList "php artisan queue:work --verbose" -WindowStyle Hidden
```

**Linux/Mac:**
```bash
# Usando nohup
nohup php artisan queue:work --verbose > storage/logs/queue.log 2>&1 &

# Usando screen
screen -dmS queue php artisan queue:work --verbose
```

### Opción 3: Supervisor (Recomendado para Producción)

Crear archivo `/etc/supervisor/conf.d/ctaccess-worker.conf`:

```ini
[program:ctaccess-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /ruta/a/CTAccess/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/ruta/a/CTAccess/storage/logs/worker.log
```

Luego:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ctaccess-worker:*
```

## 📊 Monitoreo de Colas

### Ver jobs pendientes:
```bash
php artisan queue:monitor
```

### Ver jobs fallidos:
```bash
php artisan queue:failed
```

### Reintentar jobs fallidos:
```bash
# Reintentar todos
php artisan queue:retry all

# Reintentar uno específico
php artisan queue:retry 1
```

### Limpiar jobs fallidos:
```bash
php artisan queue:flush
```

## 🔍 Logs

Los logs del sistema se encuentran en:
- **Laravel Logs**: `storage/logs/laravel.log`
- **Queue Worker**: Buscar por `"Generando QR en segundo plano"`

### Ejemplo de log exitoso:
```
[2024-01-20 10:30:15] local.INFO: 🔄 Generando QR en segundo plano {"type":"persona","id":123,"content":"PERSONA_12345678"}
[2024-01-20 10:30:16] local.INFO: ✅ QR generado exitosamente {"type":"persona","id":123,"path":"qrcodes/persona_xxx.png"}
```

## ⚙️ Configuración

### Variables de entorno (.env):

```env
# Cola por defecto: database (usa la DB para almacenar jobs)
QUEUE_CONNECTION=database

# Nombre de la tabla de jobs
DB_QUEUE_TABLE=jobs

# Cola por defecto
DB_QUEUE=default

# Tiempo antes de reintentar (segundos)
DB_QUEUE_RETRY_AFTER=90
```

### Alternativa: Usar Redis (Más rápido)

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 🧪 Probar el Sistema

1. **Iniciar el worker:**
   ```bash
   php artisan queue:work --verbose
   ```

2. **Registrar una persona** desde el formulario

3. **Observar los logs** en el terminal del worker

4. **Verificar el QR generado:**
   ```bash
   ls storage/app/public/qrcodes/
   ```

## 🔄 Flujo Completo

```
Usuario → Formulario → Controller → PersonaService
                                           ↓
                                    DB: persona con qrCode = /storage/qrcodes/xxx.png
                                           ↓
                                    Job encolado → [tabla jobs]
                                           ↓
                                    Worker toma el job
                                           ↓
                                    Genera QR físico
                                           ↓
                                    Guarda en storage/app/public/qrcodes/xxx.png
                                           ↓
                                    ✅ QR disponible en la ruta predefinida
```

## 🛠️ Troubleshooting

### El QR no se genera:
1. ✅ Verificar que el worker esté corriendo: `ps aux | grep queue:work`
2. ✅ Revisar logs: `tail -f storage/logs/laravel.log`
3. ✅ Ver jobs fallidos: `php artisan queue:failed`

### Worker se detiene:
- Usar **Supervisor** en producción para auto-reinicio
- O reiniciar manualmente: `php artisan queue:work`

### Jobs se acumulan:
- Aumentar workers: `php artisan queue:work --processes=4`
- O cambiar a Redis para mejor performance

## 📚 Recursos

- [Laravel Queue Documentation](https://laravel.com/docs/10.x/queues)
- [Supervisor Documentation](http://supervisord.org/)
