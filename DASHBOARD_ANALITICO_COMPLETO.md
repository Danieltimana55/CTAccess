# 📊 Dashboard Analítico Completo - CTAccess

## 🎯 Descripción General

Sistema de dashboard analítico profesional para el control de accesos institucional con **12 indicadores KPI** y **13 gráficos interactivos** diferentes, con filtros dinámicos y diseño responsive.

---

## 📁 Estructura de Archivos Creados

```
app/Http/Controllers/System/
└── AdminDashboardController.php (MEJORADO ✅)

resources/js/
├── Components/Dashboard/
│   ├── KpiCard.vue (NUEVO ✨)
│   └── DashboardFilters.vue (NUEVO ✨)
└── Pages/System/Admin/
    ├── Dashboard.vue (EXISTENTE - para referencia)
    └── DashboardNew.vue (NUEVO - versión completa ✨)
```

---

## 🚀 Características Implementadas

### 1️⃣ **Controlador Backend (Laravel)**

#### Métodos Principales:

1. **`index(Request $request)`** - Punto de entrada principal
2. **`getFilters($request)`** - Obtener y validar filtros
3. **`getStats($filters)`** - KPIs generales
4. **`applyAccesosFilters($query, $filters)`** - Aplicar filtros a consultas
5. **`getAccesosPorDia($filters)`** - Accesos diarios
6. **`getAccesosPorSemana($filters)`** - Accesos semanales
7. **`getAccesosPorMes($filters)`** - Accesos mensuales
8. **`getTopPersonasConMasAccesos($filters)`** - Top 5 personas
9. **`getEstadoAccesos($filters)`** - Distribución por estado
10. **`getIncidenciasPorTipo($filters)`** - Incidencias por tipo
11. **`getIncidenciasPorPrioridad($filters)`** - Incidencias por prioridad
12. **`getPromedioDuracionAccesos($filters)`** - Duración promedio
13. **`getPersonasPorTipo($filters)`** - Distribución de personas
14. **`getPortatilesPorMarca($filters)`** - Portátiles por marca
15. **`getVehiculosPorTipo($filters)`** - Vehículos por tipo
16. **`getAccesosPorJornada($filters)`** - Accesos por jornada
17. **`getAccesosPorPrograma($filters)`** - Accesos por programa

#### Filtros Implementados:

- ✅ Rango de fechas (inicio - fin)
- ✅ Jornada específica
- ✅ Programa de formación
- ✅ Tipo de persona

---

### 2️⃣ **Componentes Vue**

#### **KpiCard.vue** - Tarjeta KPI Reutilizable

**Props:**
- `title` - Título del indicador
- `value` - Valor a mostrar
- `icon` - Nombre del ícono
- `color` - Color del tema (blue, green, yellow, red, purple, cyan)
- `subtitle` - Texto descriptivo opcional
- `trend` - Objeto con tendencia: `{ value: 12, isPositive: true }`

**Uso:**
```vue
<KpiCard 
  title="Personas" 
  :value="stats.personas" 
  icon="users" 
  color="green"
/>
```

#### **DashboardFilters.vue** - Sistema de Filtros Dinámicos

**Props:**
- `filters` - Objeto con filtros actuales
- `filterOptions` - Opciones para los selectores

**Eventos:**
- `@filter-change` - Se emite cuando se aplican filtros

**Características:**
- Colapsable/expandible
- Contador de filtros activos
- Botón de limpiar filtros
- Integración con Inertia.js para actualización sin recarga

---

### 3️⃣ **Dashboard Principal**

#### **12 Indicadores KPI:**

1. **Personas** - Total de personas registradas
2. **Usuarios** - Total de usuarios del sistema
3. **Accesos Hoy** - Accesos del día actual
4. **Activos** - Accesos actualmente abiertos
5. **Incidencias 7d** - Incidencias últimos 7 días
6. **Abiertas** - Incidencias sin resolver
7. **Programas** - Total de programas de formación
8. **Vigentes** - Programas actualmente vigentes
9. **Portátiles** - Total de portátiles registrados
10. **Vehículos** - Total de vehículos registrados
11. **Período** - Accesos en el rango filtrado
12. **Duración Prom.** - Tiempo promedio de permanencia

#### **13 Gráficos Analíticos:**

**Sección 1: Tendencia de Accesos**
1. **Accesos por Período** (Line Chart con tabs)
   - Vista diaria (últimos 14 días)
   - Vista semanal (últimas 12 semanas)
   - Vista mensual (últimos 12 meses)

**Sección 2: Análisis de Accesos**
2. **Top 5 Personas** (Horizontal Bar Chart)
3. **Estado de Accesos** (Doughnut Chart)
   - Activos
   - Finalizados
   - Con incidencia

**Sección 3: Análisis de Incidencias**
4. **Incidencias por Tipo** (Doughnut Chart)
   - Seguridad, Acceso, Equipamiento, Comportamiento, Otro
5. **Incidencias por Prioridad** (Pie Chart)
   - Baja, Media, Alta, Crítica

**Sección 4: Análisis de Recursos**
6. **Personas por Tipo** (Horizontal Bar Chart)
7. **Portátiles por Marca** (Horizontal Bar Chart)
8. **Vehículos por Tipo** (Pie Chart)

**Sección 5: Organización Académica**
9. **Accesos por Jornada** (Horizontal Bar Chart)
10. **Accesos por Programa** (Horizontal Bar Chart)

**Sección 6: Duración de Accesos**
11. **Duración Mínima** (Card)
12. **Duración Promedio** (Card destacada)
13. **Duración Máxima** (Card)

---

## ⚙️ Instalación y Configuración

### Paso 1: Actualizar el Controlador

El archivo `AdminDashboardController.php` ya está actualizado con todas las funcionalidades.

### Paso 2: Crear los Componentes Vue

Los componentes ya están creados:
- `KpiCard.vue`
- `DashboardFilters.vue`
- `DashboardNew.vue`

### Paso 3: Actualizar la Ruta (si es necesario)

En `routes/web.php` o donde tengas tus rutas del sistema:

```php
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->name('system.admin.dashboard');
```

### Paso 4: Reemplazar el Dashboard Actual

Tienes dos opciones:

**Opción A: Reemplazar completamente**
```bash
# Renombrar el actual como backup
mv resources/js/Pages/System/Admin/Dashboard.vue resources/js/Pages/System/Admin/DashboardOld.vue

# Renombrar el nuevo
mv resources/js/Pages/System/Admin/DashboardNew.vue resources/js/Pages/System/Admin/Dashboard.vue
```

**Opción B: Crear una ruta nueva para probar**
```php
Route::get('/admin/dashboard-new', [AdminDashboardController::class, 'index'])
    ->name('system.admin.dashboard.new');
```

Y en el controlador, cambiar:
```php
return Inertia::render('System/Admin/DashboardNew', [
    // ... datos
]);
```

---

## 🎨 Personalización de Colores

Los colores están basados en tu tema SENA existente:

```javascript
const colorClasses = {
  blue: 'from-blue-500 to-blue-600',
  green: 'from-sena-green-600 to-sena-green-700',
  yellow: 'from-sena-yellow-500 to-sena-yellow-600',
  red: 'from-red-500 to-red-700',
  purple: 'from-purple-500 to-purple-600',
  cyan: 'from-cyan-500 to-cyan-600'
}
```

---

## 📊 Uso de Filtros

### Desde el Frontend:

Los filtros se aplican automáticamente usando Inertia.js:

```javascript
router.get(route('system.admin.dashboard'), localFilters.value, {
  preserveState: true,
  preserveScroll: true,
  only: ['stats', 'charts']
})
```

### Desde el Backend:

Los filtros se procesan en el método `applyAccesosFilters`:

```php
private function applyAccesosFilters($query, $filters)
{
    // Filtro por fechas
    if ($filters['fecha_inicio'] && $filters['fecha_fin']) {
        $query->whereBetween('fecha_entrada', [...]);
    }
    
    // Filtro por jornada
    if ($filters['jornada_id']) {
        $query->whereHas('persona', function($q) use ($filters) {
            $q->where('jornada_id', $filters['jornada_id']);
        });
    }
    
    // ... otros filtros
    
    return $query;
}
```

---

## 📈 Consultas Optimizadas

### Ejemplos de Consultas Eloquent:

**1. Top 5 Personas con Más Accesos:**
```php
Acceso::select('persona_id', DB::raw('COUNT(*) as total'))
    ->with('persona:idPersona,Nombre,TipoPersona')
    ->groupBy('persona_id')
    ->orderByDesc('total')
    ->limit(5)
    ->get()
```

**2. Promedio de Duración:**
```php
Acceso::whereNotNull('fecha_salida')
    ->select(
        DB::raw('AVG(TIMESTAMPDIFF(MINUTE, fecha_entrada, fecha_salida)) as promedio_minutos'),
        DB::raw('MIN(TIMESTAMPDIFF(MINUTE, fecha_entrada, fecha_salida)) as minimo_minutos'),
        DB::raw('MAX(TIMESTAMPDIFF(MINUTE, fecha_entrada, fecha_salida)) as maximo_minutos')
    )
    ->first()
```

**3. Accesos por Programa:**
```php
Acceso::join('personas', 'accesos.persona_id', '=', 'personas.idPersona')
    ->join('programas_formacion', 'personas.programa_formacion_id', '=', 'programas_formacion.id')
    ->select(
        'programas_formacion.nombre',
        'programas_formacion.ficha',
        DB::raw('COUNT(*) as total')
    )
    ->groupBy('programas_formacion.id', 'programas_formacion.nombre', 'programas_formacion.ficha')
    ->orderByDesc('total')
    ->limit(10)
    ->get()
```

---

## 🚀 Optimizaciones de Rendimiento

### 1. **Caché de Datos**

Puedes agregar caché para datos que no cambian frecuentemente:

```php
use Illuminate\Support\Facades\Cache;

$stats = Cache::remember('dashboard_stats_' . auth()->id(), 300, function () use ($filters) {
    return $this->getStats($filters);
});
```

### 2. **Carga Selectiva con Inertia**

El componente de filtros usa `only` para actualizar solo lo necesario:

```javascript
router.get(route('system.admin.dashboard'), localFilters.value, {
  preserveState: true,
  preserveScroll: true,
  only: ['stats', 'charts'] // Solo actualiza estos datos
})
```

### 3. **Índices de Base de Datos**

Asegúrate de tener índices en:
- `accesos.fecha_entrada`
- `accesos.estado`
- `accesos.persona_id`
- `personas.jornada_id`
- `personas.programa_formacion_id`
- `incidencias.tipo`
- `incidencias.prioridad`

---

## 🎯 Próximas Mejoras Sugeridas

1. **Exportación de Reportes**
   - PDF con gráficos
   - Excel con datos tabulares
   - CSV para análisis externo

2. **Comparativas Temporales**
   - Comparar períodos
   - Tendencias año a año

3. **Alertas y Notificaciones**
   - Alertas de incidencias críticas
   - Notificaciones de anomalías

4. **Dashboard en Tiempo Real**
   - WebSockets con Laravel Echo
   - Actualización automática de datos

5. **Más Gráficos**
   - Mapa de calor de accesos por hora
   - Gráfico de Gantt para programas
   - Timeline de incidencias

---

## 🐛 Troubleshooting

### Problema: "No se muestran datos"

**Solución:**
```php
// Verifica que tienes el scope vigentes en ProgramaFormacion
public function scopeVigentes($query)
{
    $hoy = Carbon::now()->startOfDay();
    return $query->where('activo', true)
        ->where('fecha_inicio', '<=', $hoy)
        ->where('fecha_fin', '>=', $hoy);
}
```

### Problema: "Filtros no funcionan"

**Solución:** Asegúrate de que la ruta esté nombrada correctamente:
```javascript
router.get(route('system.admin.dashboard'), ...) // Debe coincidir con el nombre de la ruta
```

### Problema: "Gráficos no se renderizan"

**Solución:** Verifica que Chart.js esté instalado:
```bash
npm list vue-chartjs chart.js
```

Si no está instalado:
```bash
npm install vue-chartjs chart.js
```

---

## 📝 Notas Importantes

1. **Rendimiento:** Las consultas están optimizadas pero en bases de datos muy grandes (>100k registros) considera:
   - Paginación de datos
   - Agregación en segundo plano
   - Vistas materializadas

2. **Seguridad:** El middleware `auth:system` ya está aplicado, pero asegúrate de validar permisos según roles.

3. **Responsive:** Todos los componentes son totalmente responsive y funcionan desde 320px de ancho.

4. **Tema Oscuro:** Los componentes soportan tema oscuro usando las clases `dark:` de Tailwind.

---

## 📚 Referencias

- [Vue 3 Documentation](https://vuejs.org/)
- [Chart.js Documentation](https://www.chartjs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Laravel Eloquent](https://laravel.com/docs/10.x/eloquent)

---

## ✅ Checklist de Implementación

- [ ] Actualizar `AdminDashboardController.php`
- [ ] Crear `KpiCard.vue`
- [ ] Crear `DashboardFilters.vue`
- [ ] Crear `DashboardNew.vue`
- [ ] Verificar scope `vigentes()` en `ProgramaFormacion`
- [ ] Probar filtros
- [ ] Verificar que todos los gráficos muestran datos
- [ ] Probar en dispositivos móviles
- [ ] Verificar tema oscuro
- [ ] Optimizar consultas si es necesario

---

**¡Dashboard listo para usar! 🎉**
