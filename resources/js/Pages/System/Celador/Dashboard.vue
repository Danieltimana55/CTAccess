<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import Icon from '@/Components/Icon.vue'

const props = defineProps({
  stats: Object,
  accesosPorDia: Array,
  incidenciasPorTipo: Array,
  incidenciasPorPrioridad: Array,
})

const quickLinks = computed(() => ([
  {
    title: 'Personas',
    description: 'Consulta información de personas registradas.',
    href: route('system.celador.personas.index'),
    color: 'from-blue-500 to-indigo-500',
    icon: 'users',
  },
  {
    title: 'Accesos',
    description: 'Gestiona entradas y salidas de personas y elementos.',
    href: route('system.celador.accesos.index'),
    color: 'from-emerald-500 to-teal-500',
    icon: 'key',
  },
  {
    title: 'Verificación QR',
    description: 'Escanea y valida códigos QR al instante.',
    href: route('system.celador.qr.index'),
    color: 'from-indigo-500 to-sky-500',
    icon: 'qr-code',
  },
  {
    title: 'Incidencias',
    description: 'Registra y consulta incidencias reportadas.',
    href: route('system.celador.incidencias.index'),
    color: 'from-amber-500 to-orange-500',
    icon: 'alert-triangle',
  },
  {
    title: 'Historial',
    description: 'Revisa el historial completo de accesos.',
    href: route('system.celador.historial.index'),
    color: 'from-slate-500 to-gray-600',
    icon: 'file-text',
  },
]))

const maxAccesos = computed(() => {
  if (!props.accesosPorDia || props.accesosPorDia.length === 0) return 1
  return Math.max(...props.accesosPorDia.map(d => d.total))
})

const getBarHeight = (value) => {
  const percentage = (value / maxAccesos.value) * 100
  return Math.max(percentage, 5) // Mínimo 5% para que se vea algo
}

const getPrioridadColor = (prioridad) => {
  const colors = {
    'Alta': 'bg-red-500',
    'Media': 'bg-yellow-500',
    'Baja': 'bg-green-500',
  }
  return colors[prioridad] || 'bg-gray-500'
}

const getTipoColor = (tipo) => {
  const colors = {
    'Seguridad': 'bg-red-500',
    'Acceso': 'bg-blue-500',
    'Equipamiento': 'bg-purple-500',
    'Comportamiento': 'bg-orange-500',
    'Otro': 'bg-gray-500',
  }
  return colors[tipo] || 'bg-gray-500'
}
</script>

<template>
  <SystemLayout>
    <Head title="Dashboard Celador" />

    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-theme-primary">Dashboard • Celador</h2>
        <div class="text-sm text-theme-secondary">Vista general del sistema</div>
      </div>
    </template>

    <div class="space-y-5">
      <!-- Estadísticas principales - Compactas -->
      <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <!-- Accesos Hoy -->
        <div class="bg-blue-600 rounded-lg p-4 text-white">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-md">
              <Icon name="log-in" :size="20" />
            </div>
            <div class="flex-1">
              <div class="text-2xl font-bold leading-none mb-1">{{ stats.accesos_hoy }}</div>
              <div class="text-blue-100 text-xs font-medium">Accesos Hoy</div>
            </div>
          </div>
        </div>

        <!-- Accesos Activos -->
        <div class="bg-emerald-600 rounded-lg p-4 text-white">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-md">
              <Icon name="users" :size="20" />
            </div>
            <div class="flex-1">
              <div class="text-2xl font-bold leading-none mb-1">{{ stats.accesos_activos }}</div>
              <div class="text-emerald-100 text-xs font-medium">Dentro</div>
            </div>
          </div>
        </div>

        <!-- Incidencias Hoy -->
        <div class="bg-orange-600 rounded-lg p-4 text-white">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-md">
              <Icon name="alert-triangle" :size="20" />
            </div>
            <div class="flex-1">
              <div class="text-2xl font-bold leading-none mb-1">{{ stats.incidencias_hoy }}</div>
              <div class="text-orange-100 text-xs font-medium">Incidencias</div>
            </div>
          </div>
        </div>

        <!-- Total Personas -->
        <div class="bg-purple-600 rounded-lg p-4 text-white">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-md">
              <Icon name="user-check" :size="20" />
            </div>
            <div class="flex-1">
              <div class="text-2xl font-bold leading-none mb-1">{{ stats.total_personas }}</div>
              <div class="text-purple-100 text-xs font-medium">Personas</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráficos - Optimizados -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Gráfico de Accesos por Día -->
        <div class="bg-theme-card rounded-lg p-4 shadow-md border border-theme-primary/30">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-base font-bold text-theme-primary">Accesos - Últimos 7 días</h3>
              <p class="text-xs text-theme-secondary mt-0.5">Total: {{ stats.accesos_semana }}</p>
            </div>
            <div v-if="stats.cambio_semanal !== 0" class="flex items-center gap-1 text-xs font-semibold" :class="stats.cambio_semanal > 0 ? 'text-emerald-600' : 'text-red-600'">
              <Icon :name="stats.cambio_semanal > 0 ? 'trending-up' : 'trending-down'" :size="16" />
              <span>{{ Math.abs(stats.cambio_semanal) }}%</span>
            </div>
          </div>
          
          <div v-if="accesosPorDia && accesosPorDia.length > 0" class="flex items-end justify-between h-40 gap-2">
            <div v-for="(dia, index) in accesosPorDia" :key="index" class="flex-1 flex flex-col items-center gap-1.5">
              <div class="text-xs font-bold text-theme-primary">{{ dia.total }}</div>
              <div class="w-full bg-gradient-to-t from-emerald-500 to-emerald-400 rounded-t transition-all duration-500 hover:from-emerald-600 hover:to-emerald-500 cursor-pointer" :style="{ height: getBarHeight(dia.total) + '%' }"></div>
              <div class="text-[10px] text-theme-secondary font-medium text-center">
                <div>{{ dia.dia }}</div>
                <div class="text-[9px] text-theme-tertiary">{{ dia.fecha }}</div>
              </div>
            </div>
          </div>
          <div v-else class="flex items-center justify-center h-40 text-theme-secondary">
            <div class="text-center">
              <Icon name="bar-chart" :size="32" class="mx-auto mb-1 opacity-30" />
              <p class="text-xs">No hay datos</p>
            </div>
          </div>
        </div>

        <!-- Incidencias por Tipo -->
        <div class="bg-theme-card rounded-lg p-4 shadow-md border border-theme-primary/30">
          <h3 class="text-base font-bold text-theme-primary mb-1">Incidencias por Tipo</h3>
          <p class="text-xs text-theme-secondary mb-4">Registradas este mes</p>
          
          <div v-if="incidenciasPorTipo && incidenciasPorTipo.length > 0" class="space-y-3">
            <div v-for="(item, index) in incidenciasPorTipo" :key="index" class="flex items-center gap-3">
              <div class="w-20 text-xs font-medium text-theme-primary truncate">{{ item.tipo }}</div>
              <div class="flex-1 relative">
                <div class="h-6 bg-theme-secondary/30 rounded overflow-hidden">
                  <div :class="['h-full rounded transition-all duration-500', getTipoColor(item.tipo)]" :style="{ width: (item.total / Math.max(...incidenciasPorTipo.map(i => i.total)) * 100) + '%' }"></div>
                </div>
              </div>
              <div class="w-8 text-right text-sm font-bold text-theme-primary">{{ item.total }}</div>
            </div>
          </div>
          <div v-else class="flex items-center justify-center h-40 text-theme-secondary">
            <div class="text-center">
              <Icon name="alert-circle" :size="32" class="mx-auto mb-1 opacity-30" />
              <p class="text-xs">No hay datos</p>
            </div>
          </div>
        </div>

        <!-- Incidencias por Prioridad -->
        <div class="bg-theme-card rounded-lg p-4 shadow-md border border-theme-primary/30">
          <h3 class="text-base font-bold text-theme-primary mb-1">Incidencias por Prioridad</h3>
          <p class="text-xs text-theme-secondary mb-4">Registradas este mes</p>
          
          <div v-if="incidenciasPorPrioridad && incidenciasPorPrioridad.length > 0" class="space-y-3">
            <div v-for="(item, index) in incidenciasPorPrioridad" :key="index" class="flex items-center gap-3">
              <div class="w-16 text-xs font-medium text-theme-primary">{{ item.prioridad }}</div>
              <div class="flex-1 relative">
                <div class="h-6 bg-theme-secondary/30 rounded overflow-hidden">
                  <div :class="['h-full rounded transition-all duration-500', getPrioridadColor(item.prioridad)]" :style="{ width: (item.total / Math.max(...incidenciasPorPrioridad.map(i => i.total)) * 100) + '%' }"></div>
                </div>
              </div>
              <div class="w-8 text-right text-sm font-bold text-theme-primary">{{ item.total }}</div>
            </div>
          </div>
          <div v-else class="flex items-center justify-center h-40 text-theme-secondary">
            <div class="text-center">
              <Icon name="alert-circle" :size="32" class="mx-auto mb-1 opacity-30" />
              <p class="text-xs">No hay datos</p>
            </div>
          </div>
        </div>

        <!-- Accesos Rápidos Compactos -->
        <div class="bg-theme-card rounded-lg p-4 shadow-md border border-theme-primary/30 lg:col-span-2">
          <h3 class="text-base font-bold text-theme-primary mb-1">Accesos Rápidos</h3>
          <p class="text-xs text-theme-secondary mb-4">Funciones principales</p>
          
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
            <Link v-for="item in quickLinks" :key="item.title" :href="item.href" class="group relative overflow-hidden rounded-lg bg-gradient-to-br from-theme-secondary to-theme-tertiary p-3 shadow-sm ring-1 ring-theme-primary/20 transition-all duration-300 hover:shadow-md hover:ring-theme-primary hover:-translate-y-0.5">
              <div :class="['absolute inset-0 opacity-0 bg-gradient-to-br transition-opacity duration-300 group-hover:opacity-10', item.color]" />
              <div class="relative text-center">
                <div class="mb-2 mx-auto flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br shadow-sm" :class="item.color">
                  <Icon :name="item.icon" :size="18" class="text-white" />
                </div>
                <div class="text-xs font-bold text-theme-primary leading-tight">{{ item.title }}</div>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </SystemLayout>
</template>

