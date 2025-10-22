<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, router, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  logs: Object,
  estadisticas: Object,
  usuarios: Array,
  modulos: Array,
  acciones: Array,
  filters: Object,
})

// Filtros
const filters = ref({
  search: props.filters.search || '',
  usuario_id: props.filters.usuario_id || '',
  module: props.filters.module || '',
  action: props.filters.action || '',
  severity: props.filters.severity || '',
  fecha_desde: props.filters.fecha_desde || '',
  fecha_hasta: props.filters.fecha_hasta || '',
  per_page: 20,
})

// Aplicar filtros
const applyFilters = () => {
  router.get(route('system.admin.activity-logs.index'), filters.value, {
    preserveState: true,
    replace: true,
  })
}

// Limpiar filtros
const clearFilters = () => {
  filters.value = {
    search: '',
    usuario_id: '',
    module: '',
    action: '',
    severity: '',
    fecha_desde: '',
    fecha_hasta: '',
    per_page: 20,
  }
  applyFilters()
}

// Contador de filtros activos
const activeFiltersCount = computed(() => {
  let count = 0
  if (filters.value.search) count++
  if (filters.value.usuario_id) count++
  if (filters.value.module) count++
  if (filters.value.action) count++
  if (filters.value.severity) count++
  if (filters.value.fecha_desde) count++
  if (filters.value.fecha_hasta) count++
  return count
})

// Formato de fecha
const formatDate = (date) => {
  return new Date(date).toLocaleString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Colores por severidad
const getSeverityColor = (severity) => {
  const colors = {
    info: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    error: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    critical: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
  }
  return colors[severity] || colors.info
}

// Iconos por severidad
const getSeverityIcon = (severity) => {
  const icons = {
    info: 'info',
    warning: 'alert-triangle',
    error: 'x-circle',
    critical: 'alert-octagon',
  }
  return icons[severity] || 'info'
}

// Iconos por acción
const getActionIcon = (action) => {
  const icons = {
    created: 'plus-circle',
    updated: 'edit',
    deleted: 'trash-2',
    viewed: 'eye',
    login: 'log-in',
    logout: 'log-out',
    failed_login: 'x-circle',
    exported: 'download',
  }
  return icons[action] || 'activity'
}

// Exportar CSV
const exportLogs = () => {
  window.location.href = route('system.admin.activity-logs.export', filters.value)
}

// Limpiar logs antiguos
const showCleanupModal = ref(false)
const cleanupDays = ref(90)

const cleanupLogs = () => {
  if (confirm(`¿Estás seguro de eliminar logs anteriores a ${cleanupDays.value} días?`)) {
    router.post(route('system.admin.activity-logs.cleanup'), {
      dias: cleanupDays.value,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        showCleanupModal.value = false
      },
    })
  }
}
</script>

<template>
  <SystemLayout>
    <Head title="Registro de Auditoría" />

    <template #header>
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-600 dark:bg-purple-700">
            <Icon name="file-text" :size="20" class="text-white" />
          </div>
          <div>
            <h2 class="text-xl font-bold text-theme-primary">Registro de Auditoría</h2>
            <p class="text-xs text-theme-secondary">Historial completo de actividades del sistema</p>
          </div>
        </div>
        <div class="flex gap-2">
          <button
            @click="exportLogs"
            class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors"
          >
            <Icon name="download" :size="16" />
            Exportar CSV
          </button>
          <button
            @click="showCleanupModal = true"
            class="inline-flex items-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors"
          >
            <Icon name="trash-2" :size="16" />
            Limpiar Antiguos
          </button>
        </div>
      </div>
    </template>

    <div class="space-y-4">
      <!-- Estadísticas -->
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-theme-primary">{{ estadisticas.total }}</div>
          <div class="text-xs text-theme-secondary">Total Registros</div>
        </div>
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-blue-600">{{ estadisticas.hoy }}</div>
          <div class="text-xs text-theme-secondary">Hoy</div>
        </div>
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-blue-600">{{ estadisticas.por_severidad.info }}</div>
          <div class="text-xs text-theme-secondary">Info</div>
        </div>
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-yellow-600">{{ estadisticas.por_severidad.warning }}</div>
          <div class="text-xs text-theme-secondary">Warnings</div>
        </div>
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-red-600">{{ estadisticas.por_severidad.error }}</div>
          <div class="text-xs text-theme-secondary">Errors</div>
        </div>
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-purple-600">{{ estadisticas.por_severidad.critical }}</div>
          <div class="text-xs text-theme-secondary">Critical</div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <Icon name="filter" :size="16" class="text-theme-secondary" />
            <h3 class="text-sm font-semibold text-theme-primary">Filtros</h3>
            <span v-if="activeFiltersCount > 0" class="px-2 py-0.5 bg-sena-green-600 dark:bg-cyan-600 text-white text-xs font-bold rounded-full">
              {{ activeFiltersCount }}
            </span>
          </div>
          <button
            v-if="activeFiltersCount > 0"
            @click="clearFilters"
            class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 font-medium"
          >
            <Icon name="x-circle" :size="14" class="inline mr-1" />
            Limpiar
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <!-- Búsqueda -->
          <input
            v-model="filters.search"
            @input="applyFilters"
            type="text"
            placeholder="Buscar..."
            class="px-3 py-2 border border-theme-primary rounded-lg bg-theme-card text-theme-primary text-sm focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500"
          />

          <!-- Usuario -->
          <select
            v-model="filters.usuario_id"
            @change="applyFilters"
            class="px-3 py-2 border border-theme-primary rounded-lg bg-theme-card text-theme-primary text-sm focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500"
          >
            <option value="">Todos los usuarios</option>
            <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
              {{ usuario.nombre }}
            </option>
          </select>

          <!-- Módulo -->
          <select
            v-model="filters.module"
            @change="applyFilters"
            class="px-3 py-2 border border-theme-primary rounded-lg bg-theme-card text-theme-primary text-sm focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500"
          >
            <option value="">Todos los módulos</option>
            <option v-for="modulo in modulos" :key="modulo" :value="modulo">
              {{ modulo }}
            </option>
          </select>

          <!-- Acción -->
          <select
            v-model="filters.action"
            @change="applyFilters"
            class="px-3 py-2 border border-theme-primary rounded-lg bg-theme-card text-theme-primary text-sm focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500"
          >
            <option value="">Todas las acciones</option>
            <option v-for="accion in acciones" :key="accion" :value="accion">
              {{ accion }}
            </option>
          </select>

          <!-- Severidad -->
          <select
            v-model="filters.severity"
            @change="applyFilters"
            class="px-3 py-2 border border-theme-primary rounded-lg bg-theme-card text-theme-primary text-sm focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500"
          >
            <option value="">Todas las severidades</option>
            <option value="info">Info</option>
            <option value="warning">Warning</option>
            <option value="error">Error</option>
            <option value="critical">Critical</option>
          </select>

          <!-- Fecha desde -->
          <input
            v-model="filters.fecha_desde"
            @change="applyFilters"
            type="date"
            class="px-3 py-2 border border-theme-primary rounded-lg bg-theme-card text-theme-primary text-sm focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500"
          />

          <!-- Fecha hasta -->
          <input
            v-model="filters.fecha_hasta"
            @change="applyFilters"
            type="date"
            class="px-3 py-2 border border-theme-primary rounded-lg bg-theme-card text-theme-primary text-sm focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500"
          />
        </div>
      </div>

      <!-- Tabla de logs -->
      <div class="bg-theme-card rounded-lg border border-theme-primary shadow-theme-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-theme-primary">
            <thead class="bg-theme-secondary">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Usuario</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Acción</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Módulo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Descripción</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">IP</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Severidad</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-theme-secondary uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-theme-primary">
              <tr v-for="log in logs.data" :key="log.id" class="hover:bg-theme-secondary transition-colors">
                <td class="px-4 py-3 text-xs text-theme-secondary whitespace-nowrap">
                  {{ formatDate(log.created_at) }}
                </td>
                <td class="px-4 py-3">
                  <div class="text-sm font-medium text-theme-primary">{{ log.usuario_nombre || 'Sistema' }}</div>
                  <div class="text-xs text-theme-secondary">{{ log.usuario_rol }}</div>
                </td>
                <td class="px-4 py-3">
                  <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-theme-secondary">
                    <Icon :name="getActionIcon(log.action)" :size="14" class="text-theme-primary" />
                    <span class="text-xs font-medium text-theme-primary">{{ log.action }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-theme-secondary">
                  {{ log.module || 'N/A' }}
                </td>
                <td class="px-4 py-3 text-sm text-theme-primary max-w-md truncate">
                  {{ log.description }}
                </td>
                <td class="px-4 py-3 text-xs text-theme-secondary font-mono">
                  {{ log.ip_address }}
                </td>
                <td class="px-4 py-3">
                  <span :class="getSeverityColor(log.severity)" class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium">
                    <Icon :name="getSeverityIcon(log.severity)" :size="12" />
                    {{ log.severity }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <Link
                    :href="route('system.admin.activity-logs.show', log.id)"
                    class="text-sena-green-600 dark:text-cyan-500 hover:text-sena-green-700 dark:hover:text-cyan-400 text-xs font-medium"
                  >
                    <Icon name="eye" :size="14" class="inline" />
                    Ver detalle
                  </Link>
                </td>
              </tr>
              <tr v-if="!logs.data?.length">
                <td colspan="8" class="px-4 py-12 text-center text-theme-muted">
                  <Icon name="inbox" :size="48" class="mx-auto mb-2 opacity-50" />
                  <p class="font-medium">No hay registros de auditoría</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="logs.data?.length" class="flex items-center justify-between px-4 py-3 bg-theme-secondary border-t border-theme-primary">
          <div class="text-sm text-theme-secondary">
            Mostrando <span class="font-medium text-theme-primary">{{ logs.from }}</span> a 
            <span class="font-medium text-theme-primary">{{ logs.to }}</span> de 
            <span class="font-medium text-theme-primary">{{ logs.total }}</span> registros
          </div>
          <div class="flex gap-1">
            <Link
              v-for="link in logs.links"
              :key="link.label"
              :href="link.url || '#'"
              :class="[
                'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                link.active
                  ? 'bg-sena-green-600 dark:bg-cyan-600 text-white'
                  : 'bg-theme-card text-theme-secondary hover:bg-theme-secondary border border-theme-primary'
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de limpieza -->
    <div v-if="showCleanupModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showCleanupModal = false">
      <div class="bg-theme-card rounded-lg shadow-xl max-w-md w-full mx-4 p-6 border border-theme-primary">
        <h3 class="text-lg font-bold text-theme-primary mb-4">Limpiar Logs Antiguos</h3>
        <p class="text-sm text-theme-secondary mb-4">
          Esta acción eliminará permanentemente todos los logs anteriores al número de días especificado.
        </p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-theme-primary mb-2">
            Días a conservar (mínimo 30)
          </label>
          <input
            v-model.number="cleanupDays"
            type="number"
            min="30"
            max="365"
            class="w-full px-3 py-2 border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500"
          />
        </div>
        <div class="flex gap-2 justify-end">
          <button
            @click="showCleanupModal = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-theme-primary rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600"
          >
            Cancelar
          </button>
          <button
            @click="cleanupLogs"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium"
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </SystemLayout>
</template>
