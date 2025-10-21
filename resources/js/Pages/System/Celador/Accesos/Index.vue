<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import Icon from '@/Components/Icon.vue'
import Modal from '@/Components/Modal.vue'
import axios from 'axios'

const props = defineProps({
  accesos: Object,
  filters: Object,
  estadisticas: Object,
  personas: Array,
})

const q = ref(props.filters?.q ?? '')
const estado = ref(props.filters?.estado ?? '')
const tipoPersona = ref(props.filters?.tipo_persona ?? '')
const tienePortatil = ref(props.filters?.tiene_portatil ?? '')
const tieneVehiculo = ref(props.filters?.tiene_vehiculo ?? '')
const fechaDesde = ref(props.filters?.fecha_desde ?? '')
const fechaHasta = ref(props.filters?.fecha_hasta ?? '')
const orden = ref(props.filters?.orden ?? 'reciente')

// Computed para verificar filtros activos
const hasActiveFilters = computed(() => {
  return q.value || estado.value || tipoPersona.value || tienePortatil.value || 
         tieneVehiculo.value || fechaDesde.value || fechaHasta.value || orden.value !== 'reciente'
})

const activeFiltersCount = computed(() => {
  let count = 0
  if (q.value) count++
  if (estado.value) count++
  if (tipoPersona.value) count++
  if (tienePortatil.value) count++
  if (tieneVehiculo.value) count++
  if (fechaDesde.value) count++
  if (fechaHasta.value) count++
  if (orden.value !== 'reciente') count++
  return count
})

const tiposPersona = [
  { value: 'Aprendiz', label: 'Aprendiz' },
  { value: 'Instructor', label: 'Instructor' },
  { value: 'Empleado', label: 'Empleado' },
  { value: 'Contratista', label: 'Contratista' },
  { value: 'Visitante', label: 'Visitante' },
]

const opcionesOrden = [
  { value: 'reciente', label: 'Más recientes' },
  { value: 'antiguo', label: 'Más antiguos' },
  { value: 'nombre_asc', label: 'Nombre (A-Z)' },
  { value: 'nombre_desc', label: 'Nombre (Z-A)' },
  { value: 'duracion_asc', label: 'Duración (menor)' },
  { value: 'duracion_desc', label: 'Duración (mayor)' },
]

const clearFilters = () => {
  q.value = ''
  estado.value = ''
  tipoPersona.value = ''
  tienePortatil.value = ''
  tieneVehiculo.value = ''
  fechaDesde.value = ''
  fechaHasta.value = ''
  orden.value = 'reciente'
}

watch([q, estado, tipoPersona, tienePortatil, tieneVehiculo, fechaDesde, fechaHasta, orden], 
  ([newQ, newEstado, newTipo, newPortatil, newVehiculo, newFechaD, newFechaH, newOrden]) => {
    router.get(route('system.celador.accesos.index'), 
      { 
        q: newQ, 
        estado: newEstado, 
        tipo_persona: newTipo,
        tiene_portatil: newPortatil,
        tiene_vehiculo: newVehiculo,
        fecha_desde: newFechaD,
        fecha_hasta: newFechaH,
        orden: newOrden
      }, 
      { preserveState: true, replace: true }
    )
  }, { debounce: 300 })

const formatDate = (dateString) => {
  if (!dateString) return '—'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('es-CO', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

const calcularDuracion = (entrada, salida) => {
  if (!entrada) return '—'
  const start = new Date(entrada)
  const end = salida ? new Date(salida) : new Date()
  const diff = Math.floor((end - start) / 1000 / 60) // minutos
  
  if (diff < 60) return `${diff}m`
  const hours = Math.floor(diff / 60)
  const mins = diff % 60
  return `${hours}h ${mins}m`
}

// Modal de nuevo acceso
const showModal = ref(false)
const personas = ref([])
const portatiles = ref([])
const vehiculos = ref([])
const loadingPortatiles = ref(false)
const loadingVehiculos = ref(false)

const form = useForm({
  persona_id: '',
  portatil_id: '',
  vehiculo_id: '',
  fecha_entrada: new Date().toISOString().slice(0, 16),
})

const openCreateModal = async () => {
  await loadPersonas()
  form.reset()
  form.persona_id = ''
  form.portatil_id = ''
  form.vehiculo_id = ''
  form.fecha_entrada = new Date().toISOString().slice(0, 16)
  portatiles.value = []
  vehiculos.value = []
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  form.reset()
  portatiles.value = []
  vehiculos.value = []
}

const loadPersonas = async () => {
  try {
    const response = await axios.get('/system/admin/portatiles/personas')
    personas.value = response.data.personas
  } catch (error) {
    console.error('Error loading personas:', error)
  }
}

const loadPortatilesPersona = async (personaId) => {
  if (!personaId) {
    portatiles.value = []
    return
  }
  loadingPortatiles.value = true
  try {
    const response = await axios.get(`/system/celador/accesos/portatiles/${personaId}`)
    portatiles.value = response.data.portatiles || []
  } catch (error) {
    console.error('Error loading portátiles:', error)
    portatiles.value = []
  } finally {
    loadingPortatiles.value = false
  }
}

const loadVehiculosPersona = async (personaId) => {
  if (!personaId) {
    vehiculos.value = []
    return
  }
  loadingVehiculos.value = true
  try {
    const response = await axios.get(`/system/celador/accesos/vehiculos/${personaId}`)
    vehiculos.value = response.data.vehiculos || []
  } catch (error) {
    console.error('Error loading vehículos:', error)
    vehiculos.value = []
  } finally {
    loadingVehiculos.value = false
  }
}

watch(() => form.persona_id, (newPersonaId) => {
  if (newPersonaId) {
    loadPortatilesPersona(newPersonaId)
    loadVehiculosPersona(newPersonaId)
  } else {
    portatiles.value = []
    vehiculos.value = []
  }
  form.portatil_id = ''
  form.vehiculo_id = ''
})

const submit = () => {
  form.post(route('system.celador.accesos.store'), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal()
      router.reload({ only: ['accesos', 'estadisticas'] })
    },
  })
}
</script>

<template>
  <SystemLayout>
    <Head title="Accesos" />

    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Icon name="log-in" :size="20" class="text-[#39A900]" />
          <h2 class="text-lg sm:text-xl font-semibold text-theme-primary">Accesos</h2>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-3 py-2 bg-[#39A900] hover:bg-[#2d7f00] active:bg-[#236600] text-white rounded-lg transition-colors text-sm font-medium touch-manipulation shadow-sm"
        >
          <Icon name="plus" :size="16" />
          <span class="hidden sm:inline">Nuevo Acceso</span>
          <span class="sm:hidden">Nuevo</span>
        </button>
      </div>
    </template>

    <div class="py-3 px-3 sm:px-4 lg:px-6">
      <div class="mx-auto max-w-7xl space-y-3 sm:space-y-4">
        
        <!-- Estadísticas con Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-4">
          
          <!-- Gráfico de Dona: Activos vs Total -->
          <div class="lg:col-span-3 bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
            <div class="flex flex-col items-center justify-center h-full">
              <p class="text-xs font-medium text-theme-secondary mb-3">Estado de Accesos</p>
              <div class="relative w-32 h-32 mb-3">
                <!-- Gráfico de dona usando conic-gradient -->
                <div 
                  class="w-full h-full rounded-full transition-all duration-1000 ease-out"
                  :style="{
                    background: `conic-gradient(
                      #39A900 0deg ${(estadisticas?.activos / (estadisticas?.total || 1)) * 360}deg,
                      #e5e7eb ${(estadisticas?.activos / (estadisticas?.total || 1)) * 360}deg 360deg
                    )`,
                    maskImage: 'radial-gradient(circle, transparent 55%, black 56%)',
                    WebkitMaskImage: 'radial-gradient(circle, transparent 55%, black 56%)'
                  }"
                >
                </div>
                <!-- Número central -->
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                  <span class="text-2xl font-bold text-theme-primary">{{ estadisticas?.activos ?? 0 }}</span>
                  <span class="text-xs text-theme-muted">Activos</span>
                </div>
              </div>
              <div class="text-center">
                <p class="text-sm text-theme-primary font-semibold">{{ estadisticas?.total ?? 0 }} Total</p>
                <p class="text-xs text-theme-muted">{{ ((estadisticas?.activos / (estadisticas?.total || 1)) * 100).toFixed(0) }}% activos</p>
              </div>
            </div>
          </div>

          <!-- Barra de Progreso: Hoy -->
          <div class="lg:col-span-3 bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
            <div class="flex flex-col justify-between h-full">
              <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-theme-secondary">Accesos Hoy</p>
                <Icon name="calendar" :size="16" class="text-[#FDC300]" />
              </div>
              
              <div class="flex-1 flex items-center justify-center">
                <div class="text-center">
                  <div class="text-4xl font-bold text-[#FDC300] mb-2">{{ estadisticas?.hoy ?? 0 }}</div>
                  <div class="text-xs text-theme-muted">registros</div>
                </div>
              </div>

              <!-- Barra de progreso animada -->
              <div class="space-y-1.5">
                <div class="h-2 bg-theme-secondary/10 rounded-full overflow-hidden">
                  <div 
                    class="h-full bg-gradient-to-r from-[#FDC300] to-[#39A900] rounded-full transition-all duration-1000 ease-out"
                    :style="{ width: `${Math.min((estadisticas?.hoy / (estadisticas?.total || 1)) * 100, 100)}%` }"
                  ></div>
                </div>
                <p class="text-xs text-theme-muted text-right">{{ ((estadisticas?.hoy / (estadisticas?.total || 1)) * 100).toFixed(0) }}% del total</p>
              </div>
            </div>
          </div>

          <!-- Barras Comparativas -->
          <div class="lg:col-span-6 bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
            <p class="text-xs font-medium text-theme-secondary mb-4">Comparativa de Registros</p>
            
            <div class="space-y-4">
              <!-- Total -->
              <div>
                <div class="flex items-center justify-between mb-1.5">
                  <div class="flex items-center gap-2">
                    <Icon name="users" :size="14" class="text-theme-primary" />
                    <span class="text-xs font-medium text-theme-primary">Total</span>
                  </div>
                  <span class="text-sm font-bold text-theme-primary">{{ estadisticas?.total ?? 0 }}</span>
                </div>
                <div class="h-2.5 bg-theme-secondary/10 rounded-full overflow-hidden">
                  <div 
                    class="h-full bg-gradient-to-r from-gray-400 to-gray-600 rounded-full transition-all duration-1000 ease-out"
                    :style="{ width: '100%' }"
                  ></div>
                </div>
              </div>

              <!-- Activos -->
              <div>
                <div class="flex items-center justify-between mb-1.5">
                  <div class="flex items-center gap-2">
                    <Icon name="check-circle" :size="14" class="text-[#39A900]" />
                    <span class="text-xs font-medium text-theme-primary">Activos</span>
                  </div>
                  <span class="text-sm font-bold text-[#39A900]">{{ estadisticas?.activos ?? 0 }}</span>
                </div>
                <div class="h-2.5 bg-theme-secondary/10 rounded-full overflow-hidden">
                  <div 
                    class="h-full bg-gradient-to-r from-[#39A900] to-[#2d7f00] rounded-full transition-all duration-1000 ease-out"
                    :style="{ width: `${(estadisticas?.activos / (estadisticas?.total || 1)) * 100}%` }"
                  ></div>
                </div>
              </div>

              <!-- Finalizados -->
              <div>
                <div class="flex items-center justify-between mb-1.5">
                  <div class="flex items-center gap-2">
                    <Icon name="check" :size="14" class="text-theme-secondary" />
                    <span class="text-xs font-medium text-theme-primary">Finalizados</span>
                  </div>
                  <span class="text-sm font-bold text-theme-secondary">{{ estadisticas?.finalizados ?? 0 }}</span>
                </div>
                <div class="h-2.5 bg-theme-secondary/10 rounded-full overflow-hidden">
                  <div 
                    class="h-full bg-gradient-to-r from-theme-secondary to-theme-muted rounded-full transition-all duration-1000 ease-out"
                    :style="{ width: `${(estadisticas?.finalizados / (estadisticas?.total || 1)) * 100}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Filtros Avanzados -->
        <div class="bg-theme-card rounded-lg border border-theme-primary p-3 sm:p-4 shadow-theme-sm">
          <!-- Header de filtros -->
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <Icon name="filter" class="w-4 h-4 text-theme-secondary" />
              <h3 class="text-sm font-semibold text-theme-primary">Filtros</h3>
              <span v-if="activeFiltersCount > 0" class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-[#39A900] text-white text-xs font-bold">
                {{ activeFiltersCount }}
              </span>
            </div>
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
            >
              <Icon name="x-circle" class="w-3.5 h-3.5" />
              Limpiar
            </button>
          </div>

          <!-- Fila 1: Búsqueda y Estado -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mb-2">
            <!-- Búsqueda -->
            <div class="sm:col-span-2">
              <label class="block text-xs font-medium text-theme-secondary mb-1">
                <Icon name="search" class="w-3 h-3 inline mr-1" />
                Buscar
              </label>
              <input 
                v-model="q" 
                type="search"
                inputmode="search"
                placeholder="Persona, documento, correo..." 
                class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary placeholder-theme-muted focus:ring-2 focus:ring-[#39A900] focus:border-transparent touch-manipulation"
              />
            </div>

            <!-- Estado -->
            <div>
              <label class="block text-xs font-medium text-theme-secondary mb-1">
                <Icon name="activity" class="w-3 h-3 inline mr-1" />
                Estado
              </label>
              <select 
                v-model="estado"
                class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-[#39A900] touch-manipulation"
              >
                <option value="">Todos</option>
                <option value="activo">Activos</option>
                <option value="finalizado">Finalizados</option>
              </select>
            </div>
          </div>

          <!-- Fila 2: Tipo, Recursos, Fechas y Ordenamiento -->
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
            <!-- Tipo de persona -->
            <div>
              <label class="block text-xs font-medium text-theme-secondary mb-1">
                <Icon name="users" class="w-3 h-3 inline mr-1" />
                Tipo
              </label>
              <select 
                v-model="tipoPersona"
                class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-[#39A900] touch-manipulation"
              >
                <option value="">Todos</option>
                <option v-for="tipo in tiposPersona" :key="tipo.value" :value="tipo.value">{{ tipo.label }}</option>
              </select>
            </div>

            <!-- Portátil -->
            <div>
              <label class="block text-xs font-medium text-theme-secondary mb-1">
                <Icon name="laptop" class="w-3 h-3 inline mr-1" />
                Portátil
              </label>
              <select 
                v-model="tienePortatil"
                class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-[#39A900] touch-manipulation"
              >
                <option value="">Todos</option>
                <option value="si">Con portátil</option>
                <option value="no">Sin portátil</option>
              </select>
            </div>

            <!-- Vehículo -->
            <div>
              <label class="block text-xs font-medium text-theme-secondary mb-1">
                <Icon name="car" class="w-3 h-3 inline mr-1" />
                Vehículo
              </label>
              <select 
                v-model="tieneVehiculo"
                class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-[#39A900] touch-manipulation"
              >
                <option value="">Todos</option>
                <option value="si">Con vehículo</option>
                <option value="no">Sin vehículo</option>
              </select>
            </div>

            <!-- Fecha desde -->
            <div>
              <label class="block text-xs font-medium text-theme-secondary mb-1">
                <Icon name="calendar" class="w-3 h-3 inline mr-1" />
                Desde
              </label>
              <input 
                v-model="fechaDesde"
                type="date"
                class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-[#39A900] touch-manipulation"
              />
            </div>

            <!-- Fecha hasta -->
            <div>
              <label class="block text-xs font-medium text-theme-secondary mb-1">
                <Icon name="calendar" class="w-3 h-3 inline mr-1" />
                Hasta
              </label>
              <input 
                v-model="fechaHasta"
                type="date"
                class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-[#39A900] touch-manipulation"
              />
            </div>

            <!-- Ordenamiento -->
            <div>
              <label class="block text-xs font-medium text-theme-secondary mb-1">
                <Icon name="arrow-up-down" class="w-3 h-3 inline mr-1" />
                Ordenar
              </label>
              <select 
                v-model="orden"
                class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-[#39A900] touch-manipulation"
              >
                <option v-for="ord in opcionesOrden" :key="ord.value" :value="ord.value">{{ ord.label }}</option>
              </select>
            </div>
          </div>

          <!-- Indicadores de filtros activos -->
          <div v-if="hasActiveFilters" class="mt-3 pt-3 border-t border-theme-primary">
            <div class="flex flex-wrap gap-1.5">
              <span v-if="q" class="inline-flex items-center gap-1 px-2 py-1 bg-[#39A900]/10 text-[#39A900] rounded-md text-xs">
                <Icon name="search" class="w-3 h-3" />
                {{ q }}
              </span>
              <span v-if="estado" class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-md text-xs">
                <Icon name="activity" class="w-3 h-3" />
                {{ estado === 'activo' ? 'Activos' : 'Finalizados' }}
              </span>
              <span v-if="tipoPersona" class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-md text-xs">
                <Icon name="users" class="w-3 h-3" />
                {{ tipoPersona }}
              </span>
              <span v-if="tienePortatil" class="inline-flex items-center gap-1 px-2 py-1 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 rounded-md text-xs">
                <Icon name="laptop" class="w-3 h-3" />
                {{ tienePortatil === 'si' ? 'Con portátil' : 'Sin portátil' }}
              </span>
              <span v-if="tieneVehiculo" class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-md text-xs">
                <Icon name="car" class="w-3 h-3" />
                {{ tieneVehiculo === 'si' ? 'Con vehículo' : 'Sin vehículo' }}
              </span>
              <span v-if="fechaDesde" class="inline-flex items-center gap-1 px-2 py-1 bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 rounded-md text-xs">
                <Icon name="calendar" class="w-3 h-3" />
                Desde: {{ fechaDesde }}
              </span>
              <span v-if="fechaHasta" class="inline-flex items-center gap-1 px-2 py-1 bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 rounded-md text-xs">
                <Icon name="calendar" class="w-3 h-3" />
                Hasta: {{ fechaHasta }}
              </span>
              <span v-if="orden !== 'reciente'" class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-md text-xs">
                <Icon name="arrow-up-down" class="w-3 h-3" />
                {{ opcionesOrden.find(o => o.value === orden)?.label }}
              </span>
            </div>
          </div>
        </div>

        <!-- Lista de Accesos - Vista Móvil -->
        <div class="lg:hidden space-y-2">
          <div v-for="a in accesos.data" :key="a.id" 
            class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-start gap-3">
              <!-- Avatar -->
              <div class="w-12 h-12 flex-shrink-0 rounded-full bg-[#39A900] flex items-center justify-center text-white font-semibold">
                {{ (a.persona?.Nombre ?? 'U').charAt(0).toUpperCase() }}
              </div>
              
              <!-- Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-2">
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-theme-primary text-sm truncate">{{ a.persona?.Nombre ?? '—' }}</p>
                    <p class="text-xs text-theme-secondary truncate">{{ a.persona?.numero_documento ?? '—' }}</p>
                  </div>
                  <span v-if="a.estado === 'activo'" 
                    class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-[#39A900]/10 text-[#39A900] whitespace-nowrap">
                    <Icon name="check-circle" :size="12" />
                    Activo
                  </span>
                  <span v-else 
                    class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-theme-secondary/10 text-theme-secondary whitespace-nowrap">
                    <Icon name="x-circle" :size="12" />
                    Fin
                  </span>
                </div>
                
                <!-- Detalles -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                  <div>
                    <p class="text-theme-muted">Entrada</p>
                    <p class="font-medium text-theme-primary">{{ formatDate(a.fecha_entrada).split(',')[1] }}</p>
                  </div>
                  <div>
                    <p class="text-theme-muted">Salida</p>
                    <p class="font-medium text-theme-primary">{{ a.fecha_salida ? formatDate(a.fecha_salida).split(',')[1] : '—' }}</p>
                  </div>
                </div>
                
                <!-- Recursos y duración -->
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-theme-primary">
                  <div class="flex items-center gap-2">
                    <span v-if="a.portatil" class="inline-flex items-center gap-1 text-xs text-gray-500">
                      <Icon name="laptop" :size="12" />
                    </span>
                    <span v-if="a.vehiculo" class="inline-flex items-center gap-1 text-xs text-[#FDC300]">
                      <Icon name="car" :size="12" />
                    </span>
                  </div>
                  <span class="text-xs text-theme-muted font-mono">{{ calcularDuracion(a.fecha_entrada, a.fecha_salida) }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="!accesos.data?.length" class="bg-theme-card rounded-lg border border-theme-primary p-8 text-center">
            <Icon name="inbox" :size="40" class="mx-auto text-theme-muted mb-2" />
            <p class="text-sm text-theme-secondary font-medium">No hay accesos</p>
          </div>
        </div>

        <!-- Tabla de Accesos - Vista Desktop -->
        <div class="hidden lg:block bg-theme-card rounded-lg border border-theme-primary overflow-hidden shadow-theme-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-theme-primary">
              <thead class="bg-theme-secondary/5">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Persona
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Documento
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Tipo
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Entrada
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Salida
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Duración
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Estado
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Recursos
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-theme-primary">
                <tr v-for="a in accesos.data" :key="a.id" class="hover:bg-theme-secondary/5 transition-colors">
                  <td class="px-3 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 rounded-full bg-[#39A900] flex items-center justify-center text-white font-semibold text-xs">
                        {{ (a.persona?.Nombre ?? 'U').charAt(0).toUpperCase() }}
                      </div>
                      <div class="min-w-0">
                        <p class="font-medium text-theme-primary text-sm truncate">{{ a.persona?.Nombre ?? '—' }}</p>
                        <p class="text-xs text-theme-secondary truncate">{{ a.persona?.correo ?? '' }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 py-3 text-sm text-theme-primary">
                    {{ a.persona?.numero_documento ?? '—' }}
                  </td>
                  <td class="px-3 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-[#39A900]/10 text-[#39A900]">
                      {{ a.persona?.tipo_persona ?? '—' }}
                    </span>
                  </td>
                  <td class="px-3 py-3 text-xs">
                    <p class="font-medium text-theme-primary">{{ formatDate(a.fecha_entrada).split(',')[0] }}</p>
                    <p class="text-theme-secondary">{{ formatDate(a.fecha_entrada).split(',')[1] }}</p>
                  </td>
                  <td class="px-3 py-3 text-xs">
                    <div v-if="a.fecha_salida">
                      <p class="font-medium text-theme-primary">{{ formatDate(a.fecha_salida).split(',')[0] }}</p>
                      <p class="text-theme-secondary">{{ formatDate(a.fecha_salida).split(',')[1] }}</p>
                    </div>
                    <span v-else class="text-theme-muted">—</span>
                  </td>
                  <td class="px-3 py-3 text-sm text-theme-secondary">
                    <span class="font-mono">{{ calcularDuracion(a.fecha_entrada, a.fecha_salida) }}</span>
                  </td>
                  <td class="px-3 py-3">
                    <span v-if="a.estado === 'activo'" class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-[#39A900]/10 text-[#39A900] border border-[#39A900]/20">
                      <Icon name="check-circle" :size="12" />
                      Activo
                    </span>
                    <span v-else class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-theme-secondary/10 text-theme-secondary border border-theme-secondary/20">
                      <Icon name="x-circle" :size="12" />
                      Fin
                    </span>
                  </td>
                  <td class="px-3 py-3">
                    <div class="flex items-center gap-2">
                      <span v-if="a.portatil" class="inline-flex items-center gap-1 text-xs text-gray-500">
                        <Icon name="laptop" :size="14" />
                      </span>
                      <span v-if="a.vehiculo" class="inline-flex items-center gap-1 text-xs text-[#FDC300]">
                        <Icon name="car" :size="14" />
                      </span>
                      <span v-if="!a.portatil && !a.vehiculo" class="text-theme-muted">—</span>
                    </div>
                  </td>
                </tr>
                <tr v-if="!accesos.data?.length">
                  <td colspan="8" class="px-3 py-8 text-center">
                    <Icon name="inbox" :size="40" class="mx-auto text-theme-muted mb-2" />
                    <p class="text-sm text-theme-secondary font-medium">No hay registros de accesos</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paginación Compacta -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
          <div class="text-xs sm:text-sm text-theme-secondary">
            <span class="font-semibold text-theme-primary">{{ accesos.from ?? 0 }}</span> - 
            <span class="font-semibold text-theme-primary">{{ accesos.to ?? 0 }}</span> de 
            <span class="font-semibold text-theme-primary">{{ accesos.total ?? 0 }}</span>
          </div>
          <div class="flex flex-wrap gap-1 justify-center">
            <Link 
              v-for="link in accesos.links" 
              :key="link.url + link.label" 
              :href="link.url || '#'" 
              class="min-w-[2rem] h-8 flex items-center justify-center rounded-md px-2 text-xs font-medium transition-all touch-manipulation"
              :class="[
                link.active 
                  ? 'bg-[#39A900] text-white shadow-sm' 
                  : 'bg-theme-card border border-theme-primary text-theme-primary hover:bg-theme-secondary/10 active:bg-theme-secondary/20',
                !link.url && 'opacity-50 cursor-not-allowed'
              ]" 
              v-html="link.label" 
              preserve-scroll 
            />
          </div>
        </div>

      </div>
    </div>

    <!-- Modal para crear nuevo acceso -->
    <Modal :show="showModal" @close="closeModal" max-width="2xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-theme-primary flex items-center gap-2">
            <div class="w-10 h-10 rounded-lg bg-[#39A900]/10 flex items-center justify-center">
              <Icon name="plus" :size="20" class="text-[#39A900]" />
            </div>
            Nuevo Acceso
          </h3>
          <button @click="closeModal" class="text-theme-muted hover:text-theme-primary transition-colors">
            <Icon name="x" :size="24" />
          </button>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Persona -->
          <div>
            <label class="block text-sm font-medium text-theme-primary mb-2">
              <span class="flex items-center gap-2">
                <Icon name="user" :size="16" />
                Persona *
              </span>
            </label>
            <select 
              v-model="form.persona_id"
              required
              class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#39A900] transition-all"
            >
              <option value="">Seleccionar persona...</option>
              <option v-for="persona in personas" :key="persona.id" :value="persona.id">
                {{ persona.nombre }} - {{ persona.documento }} ({{ persona.tipo_persona }})
              </option>
            </select>
            <p v-if="form.errors.persona_id" class="mt-1 text-sm text-red-600">{{ form.errors.persona_id }}</p>
          </div>

          <!-- Portátil (Opcional) -->
          <div>
            <label class="block text-sm font-medium text-theme-primary mb-2">
              <span class="flex items-center gap-2">
                <Icon name="laptop" :size="16" />
                Portátil (opcional)
              </span>
            </label>
            <select 
              v-model="form.portatil_id"
              :disabled="!form.persona_id || loadingPortatiles"
              class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#39A900] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <option value="">{{ loadingPortatiles ? 'Cargando...' : 'Sin portátil' }}</option>
              <option v-for="portatil in portatiles" :key="portatil.portatil_id" :value="portatil.portatil_id">
                {{ portatil.marca }} {{ portatil.modelo }} - {{ portatil.serial }}
              </option>
            </select>
            <p v-if="form.persona_id && !loadingPortatiles && portatiles.length === 0" class="mt-1 text-xs text-theme-muted">
              Esta persona no tiene portátiles registrados
            </p>
            <p v-if="form.errors.portatil_id" class="mt-1 text-sm text-red-600">{{ form.errors.portatil_id }}</p>
          </div>

          <!-- Vehículo (Opcional) -->
          <div>
            <label class="block text-sm font-medium text-theme-primary mb-2">
              <span class="flex items-center gap-2">
                <Icon name="car" :size="16" />
                Vehículo (opcional)
              </span>
            </label>
            <select 
              v-model="form.vehiculo_id"
              :disabled="!form.persona_id || loadingVehiculos"
              class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#39A900] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <option value="">{{ loadingVehiculos ? 'Cargando...' : 'Sin vehículo' }}</option>
              <option v-for="vehiculo in vehiculos" :key="vehiculo.vehiculo_id" :value="vehiculo.vehiculo_id">
                {{ vehiculo.tipo }} - {{ vehiculo.placa }}
              </option>
            </select>
            <p v-if="form.persona_id && !loadingVehiculos && vehiculos.length === 0" class="mt-1 text-xs text-theme-muted">
              Esta persona no tiene vehículos registrados
            </p>
            <p v-if="form.errors.vehiculo_id" class="mt-1 text-sm text-red-600">{{ form.errors.vehiculo_id }}</p>
          </div>

          <!-- Fecha y hora de entrada -->
          <div>
            <label class="block text-sm font-medium text-theme-primary mb-2">
              <span class="flex items-center gap-2">
                <Icon name="calendar" :size="16" />
                Fecha y hora de entrada *
              </span>
            </label>
            <input 
              v-model="form.fecha_entrada"
              type="datetime-local"
              required
              class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#39A900] transition-all"
            />
            <p v-if="form.errors.fecha_entrada" class="mt-1 text-sm text-red-600">{{ form.errors.fecha_entrada }}</p>
          </div>

          <!-- Mensaje informativo -->
          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex gap-3">
              <Icon name="info" :size="20" class="text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
              <div class="text-sm text-theme-primary">
                <p class="font-medium mb-1">Registro de acceso</p>
                <p class="text-theme-secondary">
                  El acceso se creará en estado <strong>activo</strong>. El portátil y vehículo son opcionales, 
                  solo se mostrarán los que pertenecen a la persona seleccionada.
                </p>
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div class="flex flex-col sm:flex-row gap-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2.5 bg-theme-secondary text-theme-inverse rounded-lg hover:bg-theme-secondary/80 transition-all font-medium text-sm"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="flex-1 px-4 py-2.5 bg-[#39A900] hover:bg-[#2d7f00] active:bg-[#236600] text-white rounded-lg transition-all font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <Icon v-if="form.processing" name="loader" :size="16" class="animate-spin" />
              <Icon v-else name="save" :size="16" />
              {{ form.processing ? 'Guardando...' : 'Registrar Acceso' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </SystemLayout>
</template>
