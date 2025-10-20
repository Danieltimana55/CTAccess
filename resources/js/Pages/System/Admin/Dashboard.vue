<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import Icon from '@/Components/Icon.vue'
import { computed } from 'vue'
import { Line, Doughnut, Bar } from 'vue-chartjs'
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
const stats = page.props.stats || { personas: 0, usuarios: 0, accesos_hoy: 0, incidencias_7d: 0, programas_formacion: 0, programas_vigentes: 0 }
const charts = page.props.charts || { accesosPorDia: [], incidenciasPorTipo: [], personasPorTipo: [] }
const meta = page.props.meta || {}

// Configuración del gráfico de accesos por día
const accesosPorDiaData = computed(() => ({
  labels: charts.accesosPorDia.map(d => d.fecha),
  datasets: [{
    label: 'Accesos',
    data: charts.accesosPorDia.map(d => d.total),
    borderColor: 'rgb(57, 169, 0)',
    backgroundColor: 'rgba(57, 169, 0, 0.1)',
    tension: 0.3,
    fill: true,
    borderWidth: 2,
    pointRadius: 4,
    pointHoverRadius: 6,
  }]
}))

const accesosPorDiaOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    title: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(57, 169, 0, 0.3)',
      borderWidth: 1
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        stepSize: 1,
        color: '#64748b'
      },
      grid: {
        color: 'rgba(100, 116, 139, 0.1)'
      }
    },
    x: {
      ticks: {
        color: '#64748b'
      },
      grid: {
        display: false
      }
    }
  }
}

// Configuración del gráfico de incidencias por tipo
const incidenciasPorTipoData = computed(() => ({
  labels: charts.incidenciasPorTipo.map(i => i.tipo),
  datasets: [{
    data: charts.incidenciasPorTipo.map(i => i.total),
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

const incidenciasPorTipoOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        padding: 15,
        usePointStyle: true,
        color: '#64748b'
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12
    }
  }
}

// Configuración del gráfico de personas por tipo
const personasPorTipoData = computed(() => ({
  labels: charts.personasPorTipo.map(p => p.tipo),
  datasets: [{
    label: 'Personas',
    data: charts.personasPorTipo.map(p => p.total),
    backgroundColor: 'rgba(6, 182, 212, 0.8)',
    borderColor: 'rgb(6, 182, 212)',
    borderWidth: 2,
    borderRadius: 8,
  }]
}))

const personasPorTipoOptions = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12
    }
  },
  scales: {
    x: {
      beginAtZero: true,
      ticks: {
        stepSize: 1,
        color: '#64748b'
      },
      grid: {
        color: 'rgba(100, 116, 139, 0.1)'
      }
    },
    y: {
      ticks: {
        color: '#64748b'
      },
      grid: {
        display: false
      }
    }
  }
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
      <!-- KPIs Compactos -->
      <section>
        <div class="grid gap-2 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
          <div class="group relative overflow-hidden rounded-lg border border-theme-primary bg-gradient-to-br from-theme-card to-theme-secondary p-2.5 shadow-theme-sm transition-all hover:shadow-theme-md">
            <div class="absolute top-0 right-0 h-16 w-16 -translate-y-8 translate-x-8 rounded-full bg-gradient-to-br from-sena-green-400 to-sena-green-600 opacity-20"></div>
            <div class="relative">
              <div class="flex items-center gap-1.5 mb-1">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br from-sena-green-600 to-sena-green-700 shadow-sm">
                  <Icon name="users" :size="14" class="text-white" />
                </div>
                <div class="text-xs font-medium text-theme-secondary leading-tight">Personas</div>
              </div>
              <div class="text-xl sm:text-2xl font-bold text-theme-primary">{{ stats.personas }}</div>
            </div>
          </div>
          
          <div class="group relative overflow-hidden rounded-lg border border-theme-primary bg-gradient-to-br from-theme-card to-theme-secondary p-2.5 shadow-theme-sm transition-all hover:shadow-theme-md">
            <div class="absolute top-0 right-0 h-16 w-16 -translate-y-8 translate-x-8 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-600 opacity-20"></div>
            <div class="relative">
              <div class="flex items-center gap-1.5 mb-1">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br from-cyan-500 to-cyan-600 shadow-sm">
                  <Icon name="user-cog" :size="14" class="text-white" />
                </div>
                <div class="text-xs font-medium text-theme-secondary leading-tight">Usuarios</div>
              </div>
              <div class="text-xl sm:text-2xl font-bold text-theme-primary">{{ stats.usuarios }}</div>
            </div>
          </div>
          
          <div class="group relative overflow-hidden rounded-lg border border-theme-primary bg-gradient-to-br from-theme-card to-theme-secondary p-2.5 shadow-theme-sm transition-all hover:shadow-theme-md">
            <div class="absolute top-0 right-0 h-16 w-16 -translate-y-8 translate-x-8 rounded-full bg-gradient-to-br from-sena-yellow-400 to-sena-yellow-600 opacity-20"></div>
            <div class="relative">
              <div class="flex items-center gap-1.5 mb-1">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br from-sena-yellow-500 to-sena-yellow-600 shadow-sm">
                  <Icon name="log-in" :size="14" class="text-gray-900" />
                </div>
                <div class="text-xs font-medium text-theme-secondary leading-tight">Hoy</div>
              </div>
              <div class="text-xl sm:text-2xl font-bold text-theme-primary">{{ stats.accesos_hoy }}</div>
            </div>
          </div>
          
          <div class="group relative overflow-hidden rounded-lg border border-theme-primary bg-gradient-to-br from-theme-card to-theme-secondary p-2.5 shadow-theme-sm transition-all hover:shadow-theme-md">
            <div class="absolute top-0 right-0 h-16 w-16 -translate-y-8 translate-x-8 rounded-full bg-gradient-to-br from-red-400 to-red-600 opacity-20"></div>
            <div class="relative">
              <div class="flex items-center gap-1.5 mb-1">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br from-red-500 to-red-700 shadow-sm">
                  <Icon name="alert-triangle" :size="14" class="text-white" />
                </div>
                <div class="text-xs font-medium text-theme-secondary leading-tight">Incidencias</div>
              </div>
              <div class="text-xl sm:text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.incidencias_7d }}</div>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-lg border border-theme-primary bg-gradient-to-br from-theme-card to-theme-secondary p-2.5 shadow-theme-sm transition-all hover:shadow-theme-md">
            <div class="absolute top-0 right-0 h-16 w-16 -translate-y-8 translate-x-8 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 opacity-20"></div>
            <div class="relative">
              <div class="flex items-center gap-1.5 mb-1">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br from-purple-500 to-purple-600 shadow-sm">
                  <Icon name="book-open" :size="14" class="text-white" />
                </div>
                <div class="text-xs font-medium text-theme-secondary leading-tight">Programas</div>
              </div>
              <div class="text-xl sm:text-2xl font-bold text-theme-primary">{{ stats.programas_formacion }}</div>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-lg border border-theme-primary bg-gradient-to-br from-theme-card to-theme-secondary p-2.5 shadow-theme-sm transition-all hover:shadow-theme-md">
            <div class="absolute top-0 right-0 h-16 w-16 -translate-y-8 translate-x-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 opacity-20"></div>
            <div class="relative">
              <div class="flex items-center gap-1.5 mb-1">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br from-blue-500 to-blue-600 shadow-sm">
                  <Icon name="graduation-cap" :size="14" class="text-white" />
                </div>
                <div class="text-xs font-medium text-theme-secondary leading-tight">Vigentes</div>
              </div>
              <div class="text-xl sm:text-2xl font-bold text-theme-primary">{{ stats.programas_vigentes }}</div>
            </div>
          </div>
        </div>
      </section>

      <!-- Gráficos analíticos -->
      <section>
        <div class="grid gap-3 sm:gap-4 lg:grid-cols-3">
          <!-- Gráfico 1: Accesos por día -->
          <div class="overflow-hidden rounded-lg border border-theme-primary bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-theme-primary bg-gradient-to-r from-sena-green-50 to-sena-green-100 dark:from-sena-green-900/20 dark:to-sena-green-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-sena-green-600 to-sena-green-700 dark:from-sena-green-500 dark:to-sena-green-600 shadow-sm flex-shrink-0">
                <Icon name="trending-up" :size="18" class="text-white sm:hidden" />
                <Icon name="trending-up" :size="20" class="text-white hidden sm:block" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-sena-green-800 dark:text-sena-green-300">Accesos diarios</h3>
                <p class="text-xs text-sena-green-600 dark:text-sena-green-400 hidden sm:block">Últimos 14 días</p>
              </div>
            </div>
            <div class="p-4" style="height: 250px;">
              <Line v-if="charts.accesosPorDia.length" :data="accesosPorDiaData" :options="accesosPorDiaOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="bar-chart-2" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>

          <!-- Gráfico 2: Incidencias por tipo -->
          <div class="overflow-hidden rounded-lg border border-red-300 dark:border-red-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-red-300 dark:border-red-800 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-red-700 shadow-sm flex-shrink-0">
                <Icon name="pie-chart" :size="18" class="text-white sm:hidden" />
                <Icon name="pie-chart" :size="20" class="text-white hidden sm:block" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-red-800 dark:text-red-200">Incidencias</h3>
                <p class="text-xs text-red-600 dark:text-red-400 hidden sm:block">Por tipo (último mes)</p>
              </div>
            </div>
            <div class="p-4" style="height: 250px;">
              <Doughnut v-if="charts.incidenciasPorTipo.length" :data="incidenciasPorTipoData" :options="incidenciasPorTipoOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="pie-chart" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>

          <!-- Gráfico 3: Personas por tipo -->
          <div class="overflow-hidden rounded-lg border border-cyan-300 dark:border-cyan-800 bg-theme-card shadow-theme-sm">
            <div class="flex items-center gap-2 sm:gap-3 border-b border-cyan-300 dark:border-cyan-800 bg-gradient-to-r from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-900/30 p-3 sm:p-4">
              <div class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-cyan-600 shadow-sm flex-shrink-0">
                <Icon name="users" :size="18" class="text-white sm:hidden" />
                <Icon name="users" :size="20" class="text-white hidden sm:block" />
              </div>
              <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-cyan-800 dark:text-cyan-200">Distribución</h3>
                <p class="text-xs text-cyan-600 dark:text-cyan-400 hidden sm:block">Por tipo de persona</p>
              </div>
            </div>
            <div class="p-4" style="height: 250px;">
              <Bar v-if="charts.personasPorTipo.length" :data="personasPorTipoData" :options="personasPorTipoOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center">
                <Icon name="bar-chart" :size="40" class="text-theme-muted opacity-30 mb-2" />
                <p class="text-theme-muted text-sm">Sin datos disponibles</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </SystemLayout>
</template>