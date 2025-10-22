# 🚀 Comandos de Implementación - Dashboard CTAccess

## ⚡ Setup Inicial

### 1. Verificar Dependencias
```powershell
# Verificar que vue-chartjs y chart.js estén instalados
npm list vue-chartjs chart.js

# Si no están instalados, ejecutar:
npm install vue-chartjs chart.js
```

### 2. Compilar Assets
```powershell
# Desarrollo (con hot reload)
npm run dev

# Producción (optimizado)
npm run build
```

---

## 🔄 Activación del Dashboard

### Opción A: Reemplazo Directo
```powershell
# 1. Crear backup del dashboard actual
Move-Item "resources\js\Pages\System\Admin\Dashboard.vue" "resources\js\Pages\System\Admin\DashboardOld.vue"

# 2. Activar el nuevo dashboard
Move-Item "resources\js\Pages\System\Admin\DashboardNew.vue" "resources\js\Pages\System\Admin\Dashboard.vue"

# 3. Compilar
npm run build

# 4. Limpiar caché de Laravel
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### Opción B: Testing en Paralelo
```powershell
# No mover archivos, solo agregar ruta nueva en web.php
# Ver instrucciones en GUIA_RAPIDA_DASHBOARD.md

# Compilar
npm run build
```

---

## 🧪 Testing

### Verificar Compilación
```powershell
# Ver errores de compilación
npm run dev

# Si hay errores, revisar:
# - Sintaxis de Vue
# - Imports correctos
# - Componentes registrados
```

### Probar en Navegador
```powershell
# Iniciar servidor Laravel
php artisan serve

# Visitar:
# http://localhost:8000/admin/dashboard
```

### Verificar Datos
```powershell
# Ejecutar tinker para verificar datos
php artisan tinker

# Probar queries:
App\Models\Acceso::count()
App\Models\Persona::count()
App\Models\ProgramaFormacion::vigentes()->count()
```

---

## 🔍 Debugging

### Ver Logs en Tiempo Real
```powershell
# Terminal 1: Logs de Laravel
Get-Content -Path "storage\logs\laravel.log" -Wait -Tail 50

# Terminal 2: Servidor
php artisan serve

# Terminal 3: Vite (assets)
npm run dev
```

### Limpiar Todo
```powershell
# Limpiar caché completo
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Limpiar compiled assets
Remove-Item -Recurse -Force "public\build" -ErrorAction SilentlyContinue

# Recompilar
npm run build
```

### Verificar Rutas
```powershell
# Listar todas las rutas
php artisan route:list --path=admin

# Buscar ruta específica
php artisan route:list | Select-String "dashboard"
```

---

## 📊 Verificación de Base de Datos

### Verificar Tablas
```powershell
# MySQL/MariaDB
php artisan tinker

# Ejecutar:
DB::table('accesos')->count()
DB::table('personas')->count()
DB::table('incidencias')->count()
DB::table('programas_formacion')->count()
DB::table('jornadas')->count()
DB::table('portatiles')->count()
DB::table('vehiculos')->count()
```

### Verificar Relaciones
```powershell
php artisan tinker

# Ejecutar:
$acceso = App\Models\Acceso::with('persona')->first()
$acceso->persona

$persona = App\Models\Persona::with('portatiles', 'vehiculos')->first()
$persona->portatiles
$persona->vehiculos
```

### Crear Datos de Prueba (Opcional)
```powershell
# Si necesitas datos de prueba
php artisan tinker

# Ejecutar:
App\Models\Acceso::factory(100)->create()
App\Models\Incidencia::factory(50)->create()
```

---

## 🎨 Compilación de Assets

### Desarrollo
```powershell
# Modo watch (recompila automáticamente)
npm run dev

# El servidor quedará activo, presiona Ctrl+C para detener
```

### Producción
```powershell
# Compilación optimizada
npm run build

# Verificar archivos generados
Get-ChildItem "public\build" -Recurse
```

### Solución de Problemas
```powershell
# Si Vite no compila:

# 1. Limpiar node_modules
Remove-Item -Recurse -Force "node_modules"
Remove-Item "package-lock.json"

# 2. Reinstalar
npm install

# 3. Recompilar
npm run build
```

---

## 🔧 Optimización

### Optimizar Autoload
```powershell
composer dump-autoload
```

### Optimizar Config
```powershell
php artisan config:cache
```

### Optimizar Rutas
```powershell
php artisan route:cache
```

### Optimizar Views
```powershell
php artisan view:cache
```

### Todo en Uno (Producción)
```powershell
# Ejecutar antes de desplegar
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
npm run build
```

---

## 🚀 Despliegue a Producción

### Checklist Pre-Despliegue
```powershell
# 1. Tests
php artisan test

# 2. Limpiar
php artisan cache:clear
php artisan view:clear

# 3. Compilar assets
npm run build

# 4. Optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Verificar permisos
# storage/
# bootstrap/cache/
```

### Git Commands
```powershell
# Agregar archivos nuevos
git add app/Http/Controllers/System/AdminDashboardController.php
git add resources/js/Components/Dashboard/
git add resources/js/Pages/System/Admin/DashboardNew.vue
git add *.md

# Commit
git commit -m "feat: Implementar dashboard analítico completo

- 12 KPIs en tiempo real
- 13 gráficos interactivos
- Sistema de filtros dinámicos
- Componentes reutilizables
- Responsive y tema oscuro"

# Push
git push origin main
```

---

## 🔄 Rollback (Si algo sale mal)

### Volver al Dashboard Anterior
```powershell
# 1. Restaurar dashboard original
Move-Item "resources\js\Pages\System\Admin\DashboardOld.vue" "resources\js\Pages\System\Admin\Dashboard.vue" -Force

# 2. Recompilar
npm run build

# 3. Limpiar caché
php artisan cache:clear
php artisan view:clear
```

### Usando Git
```powershell
# Ver commits recientes
git log --oneline -5

# Revertir último commit
git revert HEAD

# O volver a commit específico
git reset --hard <commit-hash>
```

---

## 📈 Monitoreo Post-Despliegue

### Ver Logs en Producción
```powershell
# Últimas 100 líneas
Get-Content "storage\logs\laravel.log" -Tail 100

# Filtrar errores
Get-Content "storage\logs\laravel.log" | Select-String "ERROR"

# Monitorear en tiempo real
Get-Content "storage\logs\laravel.log" -Wait -Tail 50
```

### Performance
```powershell
# Verificar tiempos de consulta en log
# Buscar queries lentas (>1000ms)
Get-Content "storage\logs\laravel.log" | Select-String "Slow query"
```

---

## 🛠️ Comandos Útiles

### Laravel Útil
```powershell
# Ver configuración actual
php artisan config:show database
php artisan config:show app

# Listar comandos disponibles
php artisan list

# Ayuda sobre comando específico
php artisan help cache:clear
```

### NPM Útil
```powershell
# Ver paquetes instalados
npm list --depth=0

# Actualizar paquetes
npm update

# Auditar seguridad
npm audit

# Corregir vulnerabilidades
npm audit fix
```

### Composer Útil
```powershell
# Ver paquetes instalados
composer show

# Actualizar paquetes
composer update

# Verificar platform requirements
composer check-platform-reqs
```

---

## 🎯 Scripts Personalizados (Opcional)

Puedes crear un archivo `deploy.ps1`:

```powershell
# deploy.ps1
Write-Host "🚀 Desplegando Dashboard CTAccess..." -ForegroundColor Green

Write-Host "📦 Instalando dependencias..." -ForegroundColor Yellow
npm install

Write-Host "🔨 Compilando assets..." -ForegroundColor Yellow
npm run build

Write-Host "🧹 Limpiando caché..." -ForegroundColor Yellow
php artisan cache:clear
php artisan config:clear
php artisan view:clear

Write-Host "⚡ Optimizando..." -ForegroundColor Yellow
php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host "✅ Despliegue completado!" -ForegroundColor Green
```

Ejecutar con:
```powershell
.\deploy.ps1
```

---

## 📞 Comandos de Ayuda

### Ver Versiones
```powershell
php --version
composer --version
npm --version
node --version
```

### Ver Configuración Laravel
```powershell
php artisan about
```

### Ver Info de la Aplicación
```powershell
php artisan env
php artisan optimize:clear
```

---

## ✅ Comandos de Verificación Final

```powershell
# 1. Verificar que los componentes existen
Get-ChildItem "resources\js\Components\Dashboard"
Get-ChildItem "resources\js\Pages\System\Admin\Dashboard*.vue"

# 2. Verificar que el controlador está actualizado
Get-Content "app\Http\Controllers\System\AdminDashboardController.php" | Select-String "getAccesosPorDia"

# 3. Verificar compilación
Get-ChildItem "public\build"

# 4. Verificar que no hay errores de sintaxis PHP
php -l app\Http\Controllers\System\AdminDashboardController.php

# 5. Ver tamaño de archivos compilados
Get-ChildItem "public\build\assets" | Measure-Object -Property Length -Sum
```

---

**¡Todo listo para producción! 🎉**

Si encuentras algún problema, consulta los logs o la documentación completa.
