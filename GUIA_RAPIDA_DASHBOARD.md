# 🚀 Guía Rápida de Implementación - Dashboard Analítico

## ⚡ Inicio Rápido (5 minutos)

### Paso 1: Compilar Assets
```bash
npm run dev
# o para producción
npm run build
```

### Paso 2: Probar el Dashboard

Visita: `http://tu-dominio.com/admin/dashboard`

---

## 🎯 Ejemplos de Uso

### 1️⃣ Consultar Datos con Filtros

**Desde el navegador:**
```
/admin/dashboard?fecha_inicio=2025-10-01&fecha_fin=2025-10-21&jornada_id=1
```

**Desde Postman/API:**
```http
GET /admin/dashboard
Query Params:
  - fecha_inicio: 2025-10-01
  - fecha_fin: 2025-10-21
  - jornada_id: 1
  - programa_id: 5
  - tipo_persona: Estudiante
```

### 2️⃣ Usar Componente KpiCard

```vue
<script setup>
import KpiCard from '@/Components/Dashboard/KpiCard.vue'
</script>

<template>
  <!-- KPI Simple -->
  <KpiCard 
    title="Total Usuarios" 
    :value="1234" 
    icon="users" 
    color="green"
  />

  <!-- KPI con subtítulo -->
  <KpiCard 
    title="Accesos Hoy" 
    :value="89" 
    icon="log-in" 
    color="blue"
    subtitle="últimas 24 horas"
  />

  <!-- KPI con tendencia -->
  <KpiCard 
    title="Incidencias" 
    :value="12" 
    icon="alert-triangle" 
    color="red"
    :trend="{ value: 15, isPositive: false }"
  />
</template>
```

### 3️⃣ Usar Componente DashboardFilters

```vue
<script setup>
import DashboardFilters from '@/Components/Dashboard/DashboardFilters.vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const filters = page.props.filters
const filterOptions = page.props.filterOptions

const handleFilterChange = (newFilters) => {
  console.log('Nuevos filtros:', newFilters)
}
</script>

<template>
  <DashboardFilters 
    :filters="filters"
    :filter-options="filterOptions"
    @filter-change="handleFilterChange"
  />
</template>
```

### 4️⃣ Crear un Gráfico Personalizado

```vue
<script setup>
import { Line } from 'vue-chartjs'
import { computed } from 'vue'

const props = defineProps({
  data: Array
})

const chartData = computed(() => ({
  labels: props.data.map(d => d.label),
  datasets: [{
    label: 'Mi Métrica',
    data: props.data.map(d => d.value),
    borderColor: 'rgb(57, 169, 0)',
    backgroundColor: 'rgba(57, 169, 0, 0.1)',
    tension: 0.3,
    fill: true
  }]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  }
}
</script>

<template>
  <div class="bg-theme-card rounded-lg p-4" style="height: 300px;">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>
```

---

## 🔧 Personalización Avanzada

### Agregar un Nuevo KPI

**1. En el Controlador:**
```php
// AdminDashboardController.php - Método getStats()
private function getStats($filters)
{
    return [
        // ... KPIs existentes
        'mi_nuevo_kpi' => MiModelo::where('condicion', true)->count(),
    ];
}
```

**2. En el Dashboard:**
```vue
<KpiCard 
  title="Mi Nuevo KPI" 
  :value="stats.mi_nuevo_kpi" 
  icon="star" 
  color="purple"
/>
```

### Agregar un Nuevo Gráfico

**1. En el Controlador:**
```php
private function getMiNuevoGrafico($filters)
{
    return MiModelo::select('categoria', DB::raw('COUNT(*) as total'))
        ->groupBy('categoria')
        ->get()
        ->map(function ($item) {
            return [
                'categoria' => $item->categoria,
                'total' => $item->total,
            ];
        });
}

// En el método index():
$charts = [
    // ... gráficos existentes
    'miNuevoGrafico' => $this->getMiNuevoGrafico($filters),
];
```

**2. En el Dashboard:**
```vue
<script setup>
const miNuevoGraficoData = computed(() => ({
  labels: (charts.miNuevoGrafico || []).map(d => d.categoria),
  datasets: [{
    data: (charts.miNuevoGrafico || []).map(d => d.total),
    backgroundColor: 'rgba(57, 169, 0, 0.8)',
  }]
}))
</script>

<template>
  <div class="overflow-hidden rounded-lg border border-theme-primary bg-theme-card shadow-theme-sm">
    <div class="p-4" style="height: 300px;">
      <Bar :data="miNuevoGraficoData" :options="chartOptions" />
    </div>
  </div>
</template>
```

### Agregar un Nuevo Filtro

**1. En el Controlador:**
```php
private function getFilters(Request $request)
{
    return [
        // ... filtros existentes
        'mi_filtro' => $request->get('mi_filtro'),
    ];
}

private function applyAccesosFilters($query, $filters)
{
    // ... filtros existentes
    
    if ($filters['mi_filtro']) {
        $query->where('campo', $filters['mi_filtro']);
    }
    
    return $query;
}

// Agregar opciones para el filtro:
$filterOptions = [
    // ... opciones existentes
    'misFiltroOpciones' => MiModelo::pluck('nombre', 'id'),
];
```

**2. En DashboardFilters.vue:**
```vue
<!-- Agregar en la sección de filtros -->
<div>
  <label class="block text-xs font-medium text-theme-secondary mb-1.5">
    Mi Filtro
  </label>
  <select
    v-model="localFilters.mi_filtro"
    class="w-full rounded-lg border border-theme-primary bg-theme-card px-3 py-2 text-sm"
  >
    <option value="">Todos</option>
    <option 
      v-for="opcion in filterOptions.misFiltroOpciones" 
      :key="opcion.id" 
      :value="opcion.id"
    >
      {{ opcion.nombre }}
    </option>
  </select>
</div>
```

---

## 🎨 Paleta de Colores Disponibles

### Colores SENA
```vue
<KpiCard color="green" />    <!-- Verde SENA -->
<KpiCard color="yellow" />   <!-- Amarillo SENA -->
```

### Colores Estándar
```vue
<KpiCard color="blue" />     <!-- Azul -->
<KpiCard color="red" />      <!-- Rojo -->
<KpiCard color="purple" />   <!-- Morado -->
<KpiCard color="cyan" />     <!-- Cian -->
```

### Íconos Disponibles

Los íconos provienen de tu componente `Icon.vue`. Ejemplos comunes:

```
users, user-cog, log-in, zap, alert-triangle, alert-circle,
book-open, graduation-cap, laptop, truck, calendar, clock,
trending-up, trending-down, bar-chart, bar-chart-2, pie-chart,
activity, package, award, flag, search, filter, x, chevron-up,
chevron-down, minus
```

---

## 📊 Tipos de Gráficos Disponibles

### 1. Line Chart (Líneas)
```vue
<Line :data="chartData" :options="chartOptions" />
```
**Uso:** Tendencias temporales, evolución

### 2. Bar Chart (Barras)
```vue
<Bar :data="chartData" :options="chartOptions" />
```
**Uso:** Comparaciones, rankings

### 3. Doughnut Chart (Dona)
```vue
<Doughnut :data="chartData" :options="chartOptions" />
```
**Uso:** Porcentajes, distribuciones

### 4. Pie Chart (Pastel)
```vue
<Pie :data="chartData" :options="chartOptions" />
```
**Uso:** Proporciones, categorías

---

## 🔍 Debugging

### Ver datos en el navegador

```vue
<script setup>
import { usePage } from '@inertiajs/vue3'

const page = usePage()

// En desarrollo, mostrar datos
console.log('Stats:', page.props.stats)
console.log('Charts:', page.props.charts)
console.log('Filters:', page.props.filters)
</script>
```

### Verificar consultas SQL

En el controlador:
```php
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();

$result = $this->getAccesosPorDia($filters);

dd(DB::getQueryLog());
```

### Verificar rendimiento

```php
$start = microtime(true);

$stats = $this->getStats($filters);

$duration = microtime(true) - $start;
\Log::info("Stats computed in {$duration} seconds");
```

---

## 📱 Responsive Design

El dashboard funciona en:
- ✅ Móviles (320px - 640px)
- ✅ Tablets (641px - 1024px)
- ✅ Laptops (1025px - 1440px)
- ✅ Pantallas grandes (1441px+)

### Breakpoints de Tailwind:
```
sm:  640px   (Small)
md:  768px   (Medium)
lg:  1024px  (Large)
xl:  1280px  (Extra Large)
2xl: 1536px  (2X Extra Large)
```

---

## 🚀 Performance Tips

### 1. Caché de Consultas Pesadas
```php
use Illuminate\Support\Facades\Cache;

$charts = Cache::remember('dashboard_charts_' . md5(json_encode($filters)), 300, function () use ($filters) {
    return [
        'accesosPorDia' => $this->getAccesosPorDia($filters),
        // ... otros gráficos
    ];
});
```

### 2. Limitar Resultados
```php
// Limitar a top 10 en lugar de todos
->limit(10)
->get()
```

### 3. Seleccionar Solo Campos Necesarios
```php
->select('id', 'nombre', 'total')  // Solo lo que necesitas
```

### 4. Usar Índices
```php
// En migrations:
$table->index('fecha_entrada');
$table->index(['persona_id', 'estado']);
```

---

## 🎯 Casos de Uso Reales

### Caso 1: Dashboard para Director
```
Filtros:
- Fecha: Último mes
- Sin filtros de jornada/programa

Objetivo: Visión general de toda la institución
```

### Caso 2: Dashboard para Coordinador
```
Filtros:
- Fecha: Semana actual
- Programa: Su programa específico
- Jornada: Su jornada

Objetivo: Seguimiento de su programa
```

### Caso 3: Dashboard para Seguridad
```
Filtros:
- Fecha: Día actual
- Sin filtros adicionales

Objetivo: Monitoreo en tiempo real de accesos e incidencias
```

---

## ✅ Testing

### Test Manual
1. ✅ Verificar que cada KPI muestre un número
2. ✅ Verificar que cada gráfico renderice
3. ✅ Aplicar cada filtro individualmente
4. ✅ Aplicar múltiples filtros combinados
5. ✅ Limpiar filtros
6. ✅ Cambiar entre tabs (diario/semanal/mensual)
7. ✅ Probar en mobile
8. ✅ Probar en modo oscuro

### Test Automatizado (Opcional)
```php
// tests/Feature/DashboardTest.php
public function test_dashboard_loads_successfully()
{
    $user = UsuarioSistema::factory()->create();
    
    $response = $this->actingAs($user, 'system')
        ->get(route('system.admin.dashboard'));
        
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('System/Admin/Dashboard')
             ->has('stats')
             ->has('charts')
    );
}
```

---

## 📞 Soporte

Si encuentras algún problema:

1. **Revisa la consola del navegador** para errores de JavaScript
2. **Revisa logs de Laravel** en `storage/logs/laravel.log`
3. **Verifica la estructura de datos** con `dd()` o `console.log()`
4. **Consulta la documentación** en `DASHBOARD_ANALITICO_COMPLETO.md`

---

**¡Listo para usar! 🎉**

Cualquier duda, revisa la documentación completa o los comentarios en el código.
