<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import Icon from '@/Components/Icon.vue'
import Modal from '@/Components/Modal.vue'
import axios from 'axios'

const props = defineProps({
  incidencias: Object,
  filters: Object,
  estadisticas: Object,
})

const q = ref(props.filters?.q ?? '')
const tipo = ref(props.filters?.tipo ?? '')
const prioridad = ref(props.filters?.prioridad ?? '')

watch([q, tipo, prioridad], ([newQ, newTipo, newPrioridad]) => {
  router.get(route('system.celador.incidencias.index'), 
    { q: newQ, tipo: newTipo, prioridad: newPrioridad }, 
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

const getTipoColor = (tipo) => {
  const colores = {
    'seguridad': 'text-red-600 bg-red-600/10 border-red-600/20',
    'acceso': 'text-[#00304D] bg-[#00304D]/10 border-[#00304D]/20',
    'equipamiento': 'text-purple-600 bg-purple-600/10 border-purple-600/20',
    'comportamiento': 'text-[#FDC300] bg-[#FDC300]/10 border-[#FDC300]/20',
    'otro': 'text-gray-600 bg-gray-600/10 border-gray-600/20'
  }
  return colores[tipo?.toLowerCase()] || colores['otro']
}

const getTipoIcon = (tipo) => {
  const iconos = {
    'seguridad': 'shield-alert',
    'acceso': 'key',
    'equipamiento': 'tool',
    'comportamiento': 'user-x',
    'otro': 'alert-circle'
  }
  return iconos[tipo?.toLowerCase()] || 'alert-circle'
}

const getPrioridadColor = (prioridad) => {
  const colores = {
    'alta': 'text-red-600 bg-red-600/10 border-red-600/20',
    'media': 'text-[#FDC300] bg-[#FDC300]/10 border-[#FDC300]/20',
    'baja': 'text-[#39A900] bg-[#39A900]/10 border-[#39A900]/20'
  }
  return colores[prioridad?.toLowerCase()] || colores['baja']
}

const getPrioridadIcon = (prioridad) => {
  const iconos = {
    'alta': 'alert-triangle',
    'media': 'alert-circle',
    'baja': 'info'
  }
  return iconos[prioridad?.toLowerCase()] || 'info'
}

// Modal de nueva incidencia
const showModal = ref(false)
const accesos = ref([])
const loadingAccesos = ref(false)

const form = useForm({
  acceso_id: '',
  tipo: '',
  prioridad: 'media',
  descripcion: '',
})

const openCreateModal = async () => {
  await loadAccesos()
  form.reset()
  form.prioridad = 'media'
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  form.reset()
}

const loadAccesos = async () => {
  loadingAccesos.value = true
  try {
    const response = await axios.get('/system/celador/incidencias/accesos-activos')
    accesos.value = response.data.accesos || []
  } catch (error) {
    console.error('Error loading accesos:', error)
    accesos.value = []
  } finally {
    loadingAccesos.value = false
  }
}

const submit = () => {
  form.post(route('system.celador.incidencias.store'), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal()
      router.reload({ only: ['incidencias', 'estadisticas'] })
    },
  })
}
</script>

<template>
  <SystemLayout>
    <Head title="Incidencias" />

    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Icon name="alert-triangle" :size="20" class="text-red-600" />
          <h2 class="text-lg sm:text-xl font-semibold text-theme-primary">Incidencias</h2>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white rounded-lg transition-colors text-sm font-medium touch-manipulation shadow-sm"
        >
          <Icon name="plus" :size="16" />
          <span class="hidden sm:inline">Nueva Incidencia</span>
          <span class="sm:hidden">Nueva</span>
        </button>
      </div>
    </template>

    <div class="py-3 px-3 sm:px-4 lg:px-6">
      <div class="mx-auto max-w-7xl space-y-3 sm:space-y-4">
        
        <!-- Estadísticas Compactas -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
          <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-theme-secondary">Total</p>
                <p class="text-xl sm:text-2xl font-bold text-theme-primary mt-0.5">{{ estadisticas?.total ?? 0 }}</p>
              </div>
              <div class="p-2 bg-red-600/10 rounded-lg">
                <Icon name="alert-triangle" :size="18" class="text-red-600" />
              </div>
            </div>
          </div>

          <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-theme-secondary">Alta</p>
                <p class="text-xl sm:text-2xl font-bold text-red-600 mt-0.5">{{ estadisticas?.alta ?? 0 }}</p>
              </div>
              <div class="p-2 bg-red-600/10 rounded-lg">
                <Icon name="alert-circle" :size="18" class="text-red-600" />
              </div>
            </div>
          </div>

          <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-theme-secondary">Mes</p>
                <p class="text-xl sm:text-2xl font-bold text-[#FDC300] mt-0.5">{{ estadisticas?.mes ?? 0 }}</p>
              </div>
              <div class="p-2 bg-[#FDC300]/10 rounded-lg">
                <Icon name="calendar" :size="18" class="text-[#FDC300]" />
              </div>
            </div>
          </div>

          <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-theme-secondary">Hoy</p>
                <p class="text-xl sm:text-2xl font-bold text-[#FDC300] mt-0.5">{{ estadisticas?.hoy ?? 0 }}</p>
              </div>
              <div class="p-2 bg-[#FDC300]/10 rounded-lg">
                <Icon name="clock" :size="18" class="text-[#FDC300]" />
              </div>
            </div>
          </div>
        </div>

        <!-- Filtros Compactos PWA -->
        <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
          <!-- Búsqueda móvil -->
          <div class="mb-2 sm:hidden">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Icon name="search" :size="16" class="text-theme-muted" />
              </div>
              <input 
                v-model="q" 
                type="search"
                inputmode="search"
                placeholder="Buscar incidencia..." 
                class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary pl-10 pr-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-red-600/50 transition-all touch-manipulation"
              />
            </div>
          </div>

          <!-- Filtros en línea -->
          <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-end">
            <!-- Búsqueda escritorio -->
            <div class="hidden sm:block flex-1 min-w-0">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Icon name="search" :size="14" class="text-theme-muted" />
                </div>
                <input 
                  v-model="q" 
                  type="search"
                  inputmode="search"
                  placeholder="Buscar por descripción o persona..." 
                  class="w-full rounded-md border-theme-primary bg-theme-primary text-theme-primary pl-9 pr-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-600/50 transition-all"
                />
              </div>
            </div>

            <!-- Filtros -->
            <div class="flex gap-2 sm:contents">
              <!-- Tipo -->
              <div class="flex-1 sm:w-40">
                <select 
                  v-model="tipo"
                  class="w-full rounded-lg sm:rounded-md border-theme-primary bg-theme-primary text-theme-primary px-3 py-2.5 sm:px-2 sm:py-1.5 text-base sm:text-sm focus:outline-none focus:ring-2 sm:focus:ring-1 focus:ring-red-600/50 transition-all touch-manipulation"
                >
                  <option value="">Todos</option>
                  <option value="seguridad">Seguridad</option>
                  <option value="acceso">Acceso</option>
                  <option value="equipamiento">Equipamiento</option>
                  <option value="comportamiento">Comportamiento</option>
                  <option value="otro">Otro</option>
                </select>
              </div>

              <!-- Prioridad -->
              <div class="flex-1 sm:w-32">
                <select 
                  v-model="prioridad"
                  class="w-full rounded-lg sm:rounded-md border-theme-primary bg-theme-primary text-theme-primary px-3 py-2.5 sm:px-2 sm:py-1.5 text-base sm:text-sm focus:outline-none focus:ring-2 sm:focus:ring-1 focus:ring-red-600/50 transition-all touch-manipulation"
                >
                  <option value="">Todas</option>
                  <option value="alta">Alta</option>
                  <option value="media">Media</option>
                  <option value="baja">Baja</option>
                </select>
              </div>
            </div>

            <!-- Botón limpiar -->
            <button 
              @click="q = ''; tipo = ''; prioridad = ''"
              class="inline-flex items-center justify-center px-4 py-2.5 sm:py-1.5 bg-theme-secondary text-theme-inverse rounded-lg sm:rounded-md hover:bg-theme-secondary/90 active:bg-theme-secondary/80 transition-all gap-2 text-sm font-medium touch-manipulation"
            >
              <Icon name="x" :size="14" />
              <span class="sm:hidden">Limpiar</span>
            </button>
          </div>
        </div>

        <!-- Lista de Incidencias - Vista Móvil -->
        <div class="lg:hidden space-y-2">
          <div v-for="i in incidencias.data" :key="i.incidenciaId" 
            class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-start gap-3">
              <!-- Avatar -->
              <div class="w-12 h-12 flex-shrink-0 rounded-lg bg-gradient-to-br from-red-600 to-orange-500 flex items-center justify-center text-white font-bold shadow-sm">
                {{ (i.acceso?.persona?.Nombre ?? 'U').charAt(0).toUpperCase() }}
              </div>
              
              <!-- Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-2">
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-theme-primary text-sm truncate">{{ i.acceso?.persona?.Nombre ?? 'Sin asignar' }}</p>
                    <p class="text-xs text-theme-secondary">{{ i.acceso?.persona?.tipo_persona || 'N/A' }}</p>
                  </div>
                  <span :class="['inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold border whitespace-nowrap', getPrioridadColor(i.prioridad)]">
                    <Icon :name="getPrioridadIcon(i.prioridad)" :size="12" />
                    <span class="capitalize">{{ i.prioridad || 'Baja' }}</span>
                  </span>
                </div>
                
                <!-- Tipo -->
                <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium border mb-2', getTipoColor(i.tipo)]">
                  <Icon :name="getTipoIcon(i.tipo)" :size="12" />
                  <span class="capitalize">{{ i.tipo || 'Otro' }}</span>
                </span>
                
                <!-- Descripción -->
                <p class="text-xs text-theme-primary line-clamp-2 mb-2">{{ i.descripcion || 'Sin descripción' }}</p>
                
                <!-- Footer -->
                <div class="flex items-center justify-between pt-2 border-t border-theme-primary">
                  <div class="flex items-center gap-1 text-xs text-theme-muted">
                    <Icon name="clock" :size="12" />
                    {{ formatDate(i.created_at).split(',')[1] }}
                  </div>
                  <div class="flex items-center gap-1 text-xs text-theme-secondary">
                    <Icon name="user-check" :size="12" />
                    {{ i.reportadoPor?.nombre ?? 'Sistema' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="!incidencias.data?.length" class="bg-theme-card rounded-lg border border-theme-primary p-8 text-center">
            <div class="p-3 bg-[#39A900]/10 rounded-full inline-flex mb-2">
              <Icon name="check-circle" :size="40" class="text-[#39A900]" />
            </div>
            <p class="text-sm text-theme-primary font-semibold">¡Sin incidencias!</p>
            <p class="text-xs text-theme-muted mt-1">Todo funciona correctamente</p>
          </div>
        </div>

        <!-- Tabla de Incidencias - Vista Desktop -->
        <div class="hidden lg:block bg-theme-card rounded-lg border border-theme-primary overflow-hidden shadow-theme-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-theme-primary">
              <thead class="bg-theme-secondary/5">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Prioridad
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Persona
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Tipo
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Descripción
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Reportado por
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Fecha
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-theme-primary">
                <tr v-for="i in incidencias.data" :key="i.incidenciaId" class="hover:bg-theme-secondary/5 transition-colors">
                  <td class="px-3 py-3">
                    <span :class="['inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold border', getPrioridadColor(i.prioridad)]">
                      <Icon :name="getPrioridadIcon(i.prioridad)" :size="12" />
                      <span class="capitalize">{{ i.prioridad || 'Baja' }}</span>
                    </span>
                  </td>
                  <td class="px-3 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-600 to-orange-500 flex items-center justify-center text-white font-semibold text-xs">
                        {{ (i.acceso?.persona?.Nombre ?? 'U').charAt(0).toUpperCase() }}
                      </div>
                      <div class="min-w-0">
                        <p class="font-medium text-theme-primary text-sm truncate">{{ i.acceso?.persona?.Nombre ?? 'Sin asignar' }}</p>
                        <p class="text-xs text-theme-secondary truncate">{{ i.acceso?.persona?.tipo_persona || 'N/A' }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 py-3">
                    <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium border', getTipoColor(i.tipo)]">
                      <Icon :name="getTipoIcon(i.tipo)" :size="12" />
                      <span class="capitalize">{{ i.tipo || 'Otro' }}</span>
                    </span>
                  </td>
                  <td class="px-3 py-3">
                    <p class="text-sm text-theme-primary line-clamp-2 max-w-md">
                      {{ i.descripcion || 'Sin descripción' }}
                    </p>
                  </td>
                  <td class="px-3 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-7 h-7 rounded-lg bg-[#00304D]/10 flex items-center justify-center">
                        <Icon name="user-check" :size="12" class="text-[#00304D]" />
                      </div>
                      <div class="min-w-0">
                        <p class="font-medium text-theme-primary text-sm truncate">{{ i.reportadoPor?.nombre ?? 'Sistema' }}</p>
                        <p class="text-xs text-theme-secondary truncate capitalize">{{ i.reportadoPor?.principalRole?.nombre ?? i.reportadoPor?.principal_role?.nombre ?? 'Auto' }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 py-3 text-xs">
                    <p class="font-medium text-theme-primary">{{ formatDate(i.created_at).split(',')[0] }}</p>
                    <p class="text-theme-secondary">{{ formatDate(i.created_at).split(',')[1] }}</p>
                  </td>
                </tr>
                <tr v-if="!incidencias.data?.length">
                  <td colspan="6" class="px-3 py-12 text-center">
                    <div class="p-3 bg-[#39A900]/10 rounded-full inline-flex mb-2">
                      <Icon name="check-circle" :size="48" class="text-[#39A900]" />
                    </div>
                    <p class="text-sm text-theme-primary font-semibold">¡No hay incidencias!</p>
                    <p class="text-xs text-theme-muted mt-1">Todo funciona correctamente</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paginación Compacta -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
          <div class="text-xs sm:text-sm text-theme-secondary">
            <span class="font-semibold text-theme-primary">{{ incidencias.from ?? 0 }}</span> - 
            <span class="font-semibold text-theme-primary">{{ incidencias.to ?? 0 }}</span> de 
            <span class="font-semibold text-theme-primary">{{ incidencias.total ?? 0 }}</span>
          </div>
          <div class="flex flex-wrap gap-1 justify-center">
            <Link 
              v-for="link in incidencias.links" 
              :key="link.url + link.label" 
              :href="link.url || '#'" 
              class="min-w-[2rem] h-8 flex items-center justify-center rounded-md px-2 text-xs font-medium transition-all touch-manipulation"
              :class="[
                link.active 
                  ? 'bg-red-600 text-white shadow-sm' 
                  : 'bg-theme-card border border-theme-primary text-theme-primary hover:bg-theme-secondary/10 active:bg-theme-secondary/20 hover:border-red-600/30',
                !link.url && 'opacity-50 cursor-not-allowed'
              ]" 
              v-html="link.label" 
              preserve-scroll 
            />
          </div>
        </div>

      </div>
    </div>

    <!-- Modal para crear nueva incidencia -->
    <Modal :show="showModal" @close="closeModal" max-width="2xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-theme-primary flex items-center gap-2">
            <div class="w-10 h-10 rounded-lg bg-red-600/10 flex items-center justify-center">
              <Icon name="alert-triangle" :size="20" class="text-red-600" />
            </div>
            Nueva Incidencia
          </h3>
          <button @click="closeModal" class="text-theme-muted hover:text-theme-primary transition-colors">
            <Icon name="x" :size="24" />
          </button>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Acceso -->
          <div>
            <label class="block text-sm font-medium text-theme-primary mb-2">
              <span class="flex items-center gap-2">
                <Icon name="user" :size="16" />
                Acceso Relacionado *
              </span>
            </label>
            <select 
              v-model="form.acceso_id"
              required
              :disabled="loadingAccesos"
              class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 transition-all disabled:opacity-50"
            >
              <option value="">{{ loadingAccesos ? 'Cargando accesos activos...' : 'Seleccionar acceso...' }}</option>
              <option v-for="acceso in accesos" :key="acceso.id" :value="acceso.id">
                {{ acceso.persona_nombre }} - Entrada: {{ formatDate(acceso.fecha_entrada) }}
                <template v-if="acceso.portatil_serial"> | 💻 {{ acceso.portatil_serial }}</template>
                <template v-if="acceso.vehiculo_placa"> | 🚗 {{ acceso.vehiculo_placa }}</template>
              </option>
            </select>
            <p v-if="!loadingAccesos && accesos.length === 0" class="mt-1 text-xs text-theme-muted">
              No hay accesos activos en este momento
            </p>
            <p v-if="form.errors.acceso_id" class="mt-1 text-sm text-red-600">{{ form.errors.acceso_id }}</p>
          </div>

          <!-- Tipo -->
          <div>
            <label class="block text-sm font-medium text-theme-primary mb-2">
              <span class="flex items-center gap-2">
                <Icon name="tag" :size="16" />
                Tipo *
              </span>
            </label>
            <select 
              v-model="form.tipo"
              required
              class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 transition-all"
            >
              <option value="">Seleccionar tipo...</option>
              <option value="seguridad">🛡️ Seguridad</option>
              <option value="acceso">🔑 Acceso</option>
              <option value="equipamiento">🔧 Equipamiento</option>
              <option value="comportamiento">👤 Comportamiento</option>
              <option value="otro">📋 Otro</option>
            </select>
            <p v-if="form.errors.tipo" class="mt-1 text-sm text-red-600">{{ form.errors.tipo }}</p>
          </div>

          <!-- Prioridad -->
          <div>
            <label class="block text-sm font-medium text-theme-primary mb-2">
              <span class="flex items-center gap-2">
                <Icon name="flag" :size="16" />
                Prioridad *
              </span>
            </label>
            <div class="grid grid-cols-3 gap-3">
              <label 
                class="relative flex items-center justify-center p-3 rounded-lg border-2 cursor-pointer transition-all"
                :class="form.prioridad === 'baja' 
                  ? 'border-[#39A900] bg-[#39A900]/10' 
                  : 'border-theme-primary bg-theme-primary hover:border-[#39A900]/50'"
              >
                <input type="radio" v-model="form.prioridad" value="baja" class="sr-only" />
                <div class="text-center">
                  <Icon name="info" :size="20" class="mx-auto mb-1 text-[#39A900]" />
                  <span class="text-sm font-medium text-theme-primary">Baja</span>
                </div>
              </label>
              <label 
                class="relative flex items-center justify-center p-3 rounded-lg border-2 cursor-pointer transition-all"
                :class="form.prioridad === 'media' 
                  ? 'border-[#FDC300] bg-[#FDC300]/10' 
                  : 'border-theme-primary bg-theme-primary hover:border-[#FDC300]/50'"
              >
                <input type="radio" v-model="form.prioridad" value="media" class="sr-only" />
                <div class="text-center">
                  <Icon name="alert-circle" :size="20" class="mx-auto mb-1 text-[#FDC300]" />
                  <span class="text-sm font-medium text-theme-primary">Media</span>
                </div>
              </label>
              <label 
                class="relative flex items-center justify-center p-3 rounded-lg border-2 cursor-pointer transition-all"
                :class="form.prioridad === 'alta' 
                  ? 'border-red-600 bg-red-600/10' 
                  : 'border-theme-primary bg-theme-primary hover:border-red-600/50'"
              >
                <input type="radio" v-model="form.prioridad" value="alta" class="sr-only" />
                <div class="text-center">
                  <Icon name="alert-triangle" :size="20" class="mx-auto mb-1 text-red-600" />
                  <span class="text-sm font-medium text-theme-primary">Alta</span>
                </div>
              </label>
            </div>
            <p v-if="form.errors.prioridad" class="mt-1 text-sm text-red-600">{{ form.errors.prioridad }}</p>
          </div>

          <!-- Descripción -->
          <div>
            <label class="block text-sm font-medium text-theme-primary mb-2">
              <span class="flex items-center gap-2">
                <Icon name="file-text" :size="16" />
                Descripción *
              </span>
            </label>
            <textarea 
              v-model="form.descripcion"
              required
              rows="4"
              placeholder="Describe detalladamente la incidencia..."
              class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 transition-all resize-none"
            ></textarea>
            <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
          </div>

          <!-- Mensaje informativo -->
          <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/30 rounded-lg p-4">
            <div class="flex gap-3">
              <Icon name="info" :size="20" class="text-red-600 flex-shrink-0 mt-0.5" />
              <div class="text-sm text-theme-primary">
                <p class="font-medium mb-1">Registro de incidencia</p>
                <p class="text-theme-secondary">
                  Esta incidencia se asociará al acceso seleccionado y será visible para todo el personal autorizado.
                  La prioridad determinará el orden de atención.
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
              class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white rounded-lg transition-all font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <Icon v-if="form.processing" name="loader" :size="16" class="animate-spin" />
              <Icon v-else name="alert-triangle" :size="16" />
              {{ form.processing ? 'Reportando...' : 'Reportar Incidencia' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </SystemLayout>
</template>
