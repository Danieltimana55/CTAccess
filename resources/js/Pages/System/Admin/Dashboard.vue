<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import Icon from '@/Components/Icon.vue'
import KpiCard from '@/Components/Dashboard/KpiCard.vue'
import DashboardFilters from '@/Components/Dashboard/DashboardFilters.vue'
import { computed, ref } from 'vue'
import { Line, Doughnut, Bar, Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const page = usePage()
const stats = page.props.stats || {}
const charts = page.props.charts || {}
const filters = page.props.filters || {}
const filterOptions = page.props.filterOptions || {}
const meta = page.props.meta || {}

const activeTab = ref('diario') // diario, semanal, mensual

// ============================================
// CONFIGURACIONES DE GRÁFICOS
// ============================================

// Opciones comunes para todos los gráficos
const commonOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(57, 169, 0, 0.3)',
      borderWidth: 1
    }
  }
}

// Gráfico: Accesos por día/semana/mes
const accesosPorPeriodoData = computed(() => {
  let data = []
  if (activeTab.value === 'diario') {
    data = charts.accesosPorDia || []
  } else if (activeTab.value === 'semanal') {
    data = charts.accesosPorSemana || []
  } else {
    data = charts.accesosPorMes || []
  }

  return {
    labels: data.map(d => activeTab.value === 'diario' ? d.fecha : (activeTab.value === 'semanal' ? d.semana : d.mes)),
    datasets: [{
      label: 'Accesos',
      data: data.map(d => d.total),
      borderColor: 'rgb(57, 169, 0)',
      backgroundColor: 'rgba(57, 169, 0, 0.1)',
      tension: 0.3,
      fill: true,
      borderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6,
    }]
  }
})

const accesosPorPeriodoOptions = {
  ...commonOptions,
  plugins: {
    ...commonOptions.plugins,
    legend: { display: false },
    title: { display: false }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: { stepSize: 1, color: '#64748b' },
      grid: { color: 'rgba(100, 116, 139, 0.1)' }
    },
    x: {
      ticks: { color: '#64748b' },
      grid: { display: false }
    }
  }
}

// Gráfico: Top 5 personas
const topPersonasData = computed(() => ({
  labels: (charts.topPersonas || []).map(p => p.nombre),
  datasets: [{
    label: 'Accesos',
    data: (charts.topPersonas || []).map(p => p.total),
    backgroundColor: [
      'rgba(57, 169, 0, 0.8)',
      'rgba(6, 182, 212, 0.8)',
      'rgba(59, 130, 246, 0.8)',
      'rgba(168, 85, 247, 0.8)',
      'rgba(251, 146, 60, 0.8)',
    ],
    borderColor: [
      'rgb(57, 169, 0)',
      'rgb(6, 182, 212)',
      'rgb(59, 130, 246)',
      'rgb(168, 85, 247)',
      'rgb(251, 146, 60)',
    ],
    borderWidth: 2,
    borderRadius: 8
  }]
}))

const topPersonasOptions = {
  ...commonOptions,
  indexAxis: 'y',
  plugins: {
    ...commonOptions.plugins,
    legend: { display: false }
  },
  scales: {
    x: {
      beginAtZero: true,
      ticks: { stepSize: 1, color: '#64748b' },
      grid: { color: 'rgba(100, 116, 139, 0.1)' }
    },
    y: {
      ticks: { color: '#64748b' },
      grid: { display: false }
    }
  }
}

// Gráfico: Estado de accesos
const estadoAccesosData = computed(() => ({
  labels: (charts.estadoAccesos || []).map(e => e.estado),
  datasets: [{
    data: (charts.estadoAccesos || []).map(e => e.total),
    backgroundColor: [
      'rgba(34, 197, 94, 0.8)',
      'rgba(239, 68, 68, 0.8)',
      'rgba(251, 146, 60, 0.8)',
    ],
    borderColor: [
      'rgb(34, 197, 94)',
      'rgb(239, 68, 68)',
      'rgb(251, 146, 60)',
    ],
    borderWidth: 2
  }]
}))

const estadoAccesosOptions = {
  ...commonOptions,
  plugins: {
    ...commonOptions.plugins,
    legend: {
      position: 'bottom',
      labels: { padding: 15, usePointStyle: true, color: '#64748b' }
    }
  }
}

// Gráfico: Incidencias por tipo
const incidenciasPorTipoData = computed(() => ({
  labels: (charts.incidenciasPorTipo || []).map(i => i.tipo),
  datasets: [{
    data: (charts.incidenciasPorTipo || []).map(i => i.total),
    backgroundColor: [
      'rgba(239, 68, 68, 0.8)',
      'rgba(251, 146, 60, 0.8)',
      'rgba(250, 204, 21, 0.8)',
      'rgba(59, 130, 246, 0.8)',
      'rgba(168, 85, 247, 0.8)',
    ],
    borderColor: [
      'rgb(239, 68, 68)',
      'rgb(251, 146, 60)',
      'rgb(250, 204, 21)',
      'rgb(59, 130, 246)',
      'rgb(168, 85, 247)',
    ],
    borderWidth: 2
  }]
}))

const incidenciasPorTipoOptions = estadoAccesosOptions

// Gráfico: Incidencias por prioridad
const incidenciasPorPrioridadData = computed(() => ({
  labels: (charts.incidenciasPorPrioridad || []).map(i => i.prioridad),
  datasets: [{
    data: (charts.incidenciasPorPrioridad || []).map(i => i.total),
    backgroundColor: [
      'rgba(34, 197, 94, 0.8)',
      'rgba(250, 204, 21, 0.8)',
      'rgba(251, 146, 60, 0.8)',
      'rgba(239, 68, 68, 0.8)',
    ],
    borderColor: [
      'rgb(34, 197, 94)',
      'rgb(250, 204, 21)',
      'rgb(251, 146, 60)',
      'rgb(239, 68, 68)',
    ],
    borderWidth: 2
  }]
}))

// Gráfico: Personas por tipo
const personasPorTipoData = computed(() => ({
  labels: (charts.personasPorTipo || []).map(p => p.tipo),
  datasets: [{
    label: 'Personas',
    data: (charts.personasPorTipo || []).map(p => p.total),
    backgroundColor: 'rgba(6, 182, 212, 0.8)',
    borderColor: 'rgb(6, 182, 212)',
    borderWidth: 2,
    borderRadius: 8,
  }]
}))

const personasPorTipoOptions = {
  ...commonOptions,
  indexAxis: 'y',
  plugins: {
    ...commonOptions.plugins,
    legend: { display: false }
  },
  scales: {
    x: {
      beginAtZero: true,
      ticks: { stepSize: 1, color: '#64748b' },
      grid: { color: 'rgba(100, 116, 139, 0.1)' }
    },
    y: {
      ticks: { color: '#64748b' },
      grid: { display: false }
    }
  }
}

// Gráfico: Portátiles por marca
const portatilesPorMarcaData = computed(() => ({
  labels: (charts.portatilesPorMarca || []).map(p => p.marca),
  datasets: [{
    label: 'Portátiles',
    data: (charts.portatilesPorMarca || []).map(p => p.total),
    backgroundColor: 'rgba(168, 85, 247, 0.8)',
    borderColor: 'rgb(168, 85, 247)',
    borderWidth: 2,
    borderRadius: 8,
  }]
}))

// Gráfico: Vehículos por tipo
const vehiculosPorTipoData = computed(() => ({
  labels: (charts.vehiculosPorTipo || []).map(v => v.tipo),
  datasets: [{
    data: (charts.vehiculosPorTipo || []).map(v => v.total),
    backgroundColor: [
      'rgba(251, 146, 60, 0.8)',
      'rgba(59, 130, 246, 0.8)',
      'rgba(34, 197, 94, 0.8)',
      'rgba(168, 85, 247, 0.8)',
    ],
    borderColor: [
      'rgb(251, 146, 60)',
      'rgb(59, 130, 246)',
      'rgb(34, 197, 94)',
      'rgb(168, 85, 247)',
    ],
    borderWidth: 2
  }]
}))

// Gráfico: Accesos por jornada
const accesosPorJornadaData = computed(() => ({
  labels: (charts.accesosPorJornada || []).map(j => j.jornada),
  datasets: [{
    label: 'Accesos',
    data: (charts.accesosPorJornada || []).map(j => j.total),
    backgroundColor: 'rgba(250, 204, 21, 0.8)',
    borderColor: 'rgb(250, 204, 21)',
    borderWidth: 2,
    borderRadius: 8,
  }]
}))

// Gráfico: Accesos por programa
const accesosPorProgramaData = computed(() => ({
  labels: (charts.accesosPorPrograma || []).map(p => `${p.ficha}`),
  datasets: [{
    label: 'Accesos',
    data: (charts.accesosPorPrograma || []).map(p => p.total),
    backgroundColor: 'rgba(57, 169, 0, 0.8)',
    borderColor: 'rgb(57, 169, 0)',
    borderWidth: 2,
    borderRadius: 8,
  }]
}))

const formatDuration = (minutes) => {
  if (minutes < 60) return `${minutes} min`
  const hours = Math.floor(minutes / 60)
  const mins = Math.round(minutes % 60)
  return `${hours}h ${mins}m`
}
</script>

<template>
  <SystemLayout>
    <Head title="Panel de Administración" />

    <template #header>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-lg sm:text-xl font-bold text-theme-primary">Panel de Administración</h2>
        <div class="text-xs text-theme-muted">{{ meta.generated_at }}</div>
      </div>
    </template>

    <div class="space-y-4">
      <!-- Filtros -->
      <DashboardFilters 
        :filters="filters" 
        :filter-options="filterOptions"
      />

      <!-- KPIs Principales -->
      <section>
        <h3 class="text-sm font-bold text-theme-secondary mb-3 flex items-center gap-2">
          <Icon name="activity" :size="16" />
          Indicadores Clave
        </h3>
        <div class="grid gap-2 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
          <KpiCard 
            title="Personas" 
            :value="stats.personas" 
            icon="users" 
            color="green"
          />
          <KpiCard 
            title="Usuarios" 
            :value="stats.usuarios" 
            icon="user-cog" 
            color="cyan"
          />
          <KpiCard 
            title="Accesos Hoy" 
            :value="stats.accesos_hoy" 
            icon="log-in" 
            color="yellow"
          />
          <KpiCard 
            title="Activos" 
            :value="stats.accesos_activos" 
            icon="zap" 
            color="green"
          />
          <KpiCard 
            title="Incidencias 7d" 
            :value="stats.incidencias_7d" 
            icon="alert-triangle" 
            color="red"
          />
          <KpiCard 
            title="Abiertas" 
            :value="stats.incidencias_abiertas" 
            icon="alert-circle" 
            color="red"
            subtitle="incidencias"
          />
          <KpiCard 
            title="Programas" 
            :value="stats.programas_formacion" 
            icon="book-open" 
            color="purple"
          />
          <KpiCard 
            title="Vigentes" 
            :value="stats.programas_vigentes" 
            icon="graduation-cap" 
            color="blue"
            subtitle="programas"
          />
          <KpiCard 
            title="Portátiles" 
            :value="stats.portatiles_registrados" 
            icon="laptop" 
            color="purple"
          />
          <KpiCard 
            title="Vehículos" 
            :value="stats.vehiculos_registrados" 
            icon="truck" 
            color="cyan"
          />
          <KpiCard 
            title="Período" 
            :value="stats.accesos_periodo" 
            icon="calendar" 
            color="blue"
            subtitle="accesos filtrados"
          />
          <KpiCard 
            title="Duración Prom." 
            :value="formatDuration(charts.promedioDuracion?.promedio || 0)" 
            icon="clock" 
            color="yellow"
            subtitle="tiempo de acceso"
          />
        </div>
      </section>

      <!-- Gráfico Principal: Accesos por Período -->
      <section>
        <h3 class="text-sm font-bold text-theme-secondary mb-3 flex items-center gap-2">
          <Icon name="trending-up" :size="16" />
          Tendencia de Accesos
        </h3>
        <div class="overflow-hidden rounded-lg border border-theme-primary bg-theme-card shadow-theme-sm">
          <!-- Tabs de período -->
          <div class="flex border-b border-theme-primary bg-gradient-to-r from-sena-green-50 to-sena-green-100 dark:from-sena-green-900/20 dark:to-sena-green-900/30">
            <button
              @click="activeTab = 'diario'"
              class="flex-1 px-4 py-3 text-sm font-medium transition-colors"
              :class="activeTab === 'diario' 
                ? 'bg-sena-green-600 text-white shadow-sm' 
                : 'text-sena-green-700 dark:text-sena-green-300 hover:bg-sena-green-100 dark:hover:bg-sena-green-900/30'"
            >
              Diario
            </button>
            <button
              @click="activeTab = 'semanal'"
              class="flex-1 px-4 py-3 text-sm font-medium transition-colors border-l border-theme-primary"
              :class="activeTab === 'semanal' 
                ? 'bg-sena-green-600 text-white shadow-sm' 
                : 'text-sena-green-700 dark:text-sena-green-300 hover:bg-sena-green-100 dark:hover:bg-sena-green-900/30'"
            >
              Semanal
            </button>
            <button
              @click="activeTab = 'mensual'"
              class="flex-1 px-4 py-3 text-sm font-medium transition-colors border-l border-theme-primary"
              :class="activeTab === 'mensual' 
                ? 'bg-sena-green-600 text-white shadow-sm' 
                : 'text-sena-green-700 dark:text-sena-green-300 hover:bg-sena-green-100 dark:hover:bg-sena-green-900/30'"
            >
              Mensual
            </button>
          </div>
          
          <div class="p-4" style="height: 300px;">
            <Line 
              :data="accesosPorPeriodoData" 
              :options="accesosPorPeriodoOptions" 
            />
          </div>
        </div>
      </section>

      <!-- Sección: Análisis de Accesos -->
      <section>
        <h3 class="text-sm font-bold text-theme-secondary mb-3 flex items-center gap-2">
          <Icon name="bar-chart-2" :size="16" />
          Análisis de Accesos
        </h3>
        <div class="grid gap-3 sm:gap-4 lg:grid-cols-2">
          <!-- Top 5 Personas -->
          <div class="overflow-hidden rounded-lg border border-sena-green-300 dark:border-sena-green-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-sena-green-300 dark:border-sena-green-800 bg-gradient-to-r from-sena-green-50 to-sena-green-100 dark:from-sena-green-900/20 dark:to-sena-green-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-sena-green-600 to-sena-green-700 shadow-sm flex-shrink-0">
                <Icon name="award" :size="20" class="text-white" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-sena-green-800 dark:text-sena-green-300">Top 5 Personas</h3>
                <p class="text-xs text-sena-green-600 dark:text-sena-green-400">Más accesos registrados</p>
              </div>
            </div>
            <div class="p-4" style="height: 280px;">
              <Bar v-if="charts.topPersonas?.length" :data="topPersonasData" :options="topPersonasOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="users" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>

          <!-- Estado de Accesos -->
          <div class="overflow-hidden rounded-lg border border-blue-300 dark:border-blue-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-blue-300 dark:border-blue-800 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 shadow-sm flex-shrink-0">
                <Icon name="activity" :size="20" class="text-white" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-blue-800 dark:text-blue-200">Estado de Accesos</h3>
                <p class="text-xs text-blue-600 dark:text-blue-400">Distribución por estado</p>
              </div>
            </div>
            <div class="p-4" style="height: 280px;">
              <Doughnut v-if="charts.estadoAccesos?.length" :data="estadoAccesosData" :options="estadoAccesosOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="pie-chart" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Sección: Incidencias -->
      <section>
        <h3 class="text-sm font-bold text-theme-secondary mb-3 flex items-center gap-2">
          <Icon name="alert-triangle" :size="16" />
          Análisis de Incidencias
        </h3>
        <div class="grid gap-3 sm:gap-4 lg:grid-cols-2">
          <!-- Por Tipo -->
          <div class="overflow-hidden rounded-lg border border-red-300 dark:border-red-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-red-300 dark:border-red-800 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-red-700 shadow-sm flex-shrink-0">
                <Icon name="pie-chart" :size="20" class="text-white" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-red-800 dark:text-red-200">Por Tipo</h3>
                <p class="text-xs text-red-600 dark:text-red-400">Clasificación de incidencias</p>
              </div>
            </div>
            <div class="p-4" style="height: 280px;">
              <Doughnut v-if="charts.incidenciasPorTipo?.length" :data="incidenciasPorTipoData" :options="incidenciasPorTipoOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="pie-chart" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>

          <!-- Por Prioridad -->
          <div class="overflow-hidden rounded-lg border border-orange-300 dark:border-orange-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-orange-300 dark:border-orange-800 bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 shadow-sm flex-shrink-0">
                <Icon name="flag" :size="20" class="text-white" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-orange-800 dark:text-orange-200">Por Prioridad</h3>
                <p class="text-xs text-orange-600 dark:text-orange-400">Nivel de urgencia</p>
              </div>
            </div>
            <div class="p-4" style="height: 280px;">
              <Pie v-if="charts.incidenciasPorPrioridad?.length" :data="incidenciasPorPrioridadData" :options="incidenciasPorTipoOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="pie-chart" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Sección: Recursos -->
      <section>
        <h3 class="text-sm font-bold text-theme-secondary mb-3 flex items-center gap-2">
          <Icon name="package" :size="16" />
          Análisis de Recursos
        </h3>
        <div class="grid gap-3 sm:gap-4 lg:grid-cols-3">
          <!-- Personas por Tipo -->
          <div class="overflow-hidden rounded-lg border border-cyan-300 dark:border-cyan-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-cyan-300 dark:border-cyan-800 bg-gradient-to-r from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-cyan-600 shadow-sm flex-shrink-0">
                <Icon name="users" :size="20" class="text-white" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-cyan-800 dark:text-cyan-200">Personas</h3>
                <p class="text-xs text-cyan-600 dark:text-cyan-400">Por tipo</p>
              </div>
            </div>
            <div class="p-4" style="height: 250px;">
              <Bar v-if="charts.personasPorTipo?.length" :data="personasPorTipoData" :options="personasPorTipoOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="bar-chart" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>

          <!-- Portátiles por Marca -->
          <div class="overflow-hidden rounded-lg border border-purple-300 dark:border-purple-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-purple-300 dark:border-purple-800 bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 shadow-sm flex-shrink-0">
                <Icon name="laptop" :size="20" class="text-white" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-purple-800 dark:text-purple-200">Portátiles</h3>
                <p class="text-xs text-purple-600 dark:text-purple-400">Por marca</p>
              </div>
            </div>
            <div class="p-4" style="height: 250px;">
              <Bar v-if="charts.portatilesPorMarca?.length" :data="portatilesPorMarcaData" :options="topPersonasOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="laptop" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>

          <!-- Vehículos por Tipo -->
          <div class="overflow-hidden rounded-lg border border-orange-300 dark:border-orange-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-orange-300 dark:border-orange-800 bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 shadow-sm flex-shrink-0">
                <Icon name="truck" :size="20" class="text-white" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-orange-800 dark:text-orange-200">Vehículos</h3>
                <p class="text-xs text-orange-600 dark:text-orange-400">Por tipo</p>
              </div>
            </div>
            <div class="p-4" style="height: 250px;">
              <Pie v-if="charts.vehiculosPorTipo?.length" :data="vehiculosPorTipoData" :options="incidenciasPorTipoOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="truck" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Sección: Organización Académica -->
      <section>
        <h3 class="text-sm font-bold text-theme-secondary mb-3 flex items-center gap-2">
          <Icon name="book-open" :size="16" />
          Organización Académica
        </h3>
        <div class="grid gap-3 sm:gap-4 lg:grid-cols-2">
          <!-- Accesos por Jornada -->
          <div class="overflow-hidden rounded-lg border border-yellow-300 dark:border-yellow-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-yellow-300 dark:border-yellow-800 bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-yellow-500 to-yellow-600 shadow-sm flex-shrink-0">
                <Icon name="clock" :size="20" class="text-gray-900" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-yellow-800 dark:text-yellow-200">Por Jornada</h3>
                <p class="text-xs text-yellow-600 dark:text-yellow-400">Accesos por horario</p>
              </div>
            </div>
            <div class="p-4" style="height: 280px;">
              <Bar v-if="charts.accesosPorJornada?.length" :data="accesosPorJornadaData" :options="topPersonasOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="clock" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>

          <!-- Accesos por Programa -->
          <div class="overflow-hidden rounded-lg border border-sena-green-300 dark:border-sena-green-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-sena-green-300 dark:border-sena-green-800 bg-gradient-to-r from-sena-green-50 to-sena-green-100 dark:from-sena-green-900/20 dark:to-sena-green-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-sena-green-600 to-sena-green-700 shadow-sm flex-shrink-0">
                <Icon name="graduation-cap" :size="20" class="text-white" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-sena-green-800 dark:text-sena-green-300">Por Programa</h3>
                <p class="text-xs text-sena-green-600 dark:text-sena-green-400">Top 10 programas</p>
              </div>
            </div>
            <div class="p-4" style="height: 280px;">
              <Bar v-if="charts.accesosPorPrograma?.length" :data="accesosPorProgramaData" :options="topPersonasOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="book-open" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Resumen de Duración -->
      <section v-if="charts.promedioDuracion">
        <h3 class="text-sm font-bold text-theme-secondary mb-3 flex items-center gap-2">
          <Icon name="clock" :size="16" />
          Duración de Accesos
        </h3>
        <div class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-3">
          <div class="rounded-lg border border-theme-primary bg-gradient-to-br from-theme-card to-theme-secondary p-4">
            <div class="flex items-center gap-2 mb-2">
              <Icon name="trending-down" :size="18" class="text-blue-500" />
              <span class="text-xs font-medium text-theme-secondary">Mínimo</span>
            </div>
            <p class="text-2xl font-bold text-theme-primary">{{ formatDuration(charts.promedioDuracion.minimo) }}</p>
          </div>
          <div class="rounded-lg border border-theme-primary bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/30 p-4">
            <div class="flex items-center gap-2 mb-2">
              <Icon name="minus" :size="18" class="text-blue-600" />
              <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Promedio</span>
            </div>
            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ formatDuration(charts.promedioDuracion.promedio) }}</p>
          </div>
          <div class="rounded-lg border border-theme-primary bg-gradient-to-br from-theme-card to-theme-secondary p-4">
            <div class="flex items-center gap-2 mb-2">
              <Icon name="trending-up" :size="18" class="text-red-500" />
              <span class="text-xs font-medium text-theme-secondary">Máximo</span>
            </div>
            <p class="text-2xl font-bold text-theme-primary">{{ formatDuration(charts.promedioDuracion.maximo) }}</p>
          </div>
        </div>
      </section>
    </div>
  </SystemLayout>
</template>
