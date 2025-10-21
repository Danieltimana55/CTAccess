<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import Modal from '@/Components/Modal.vue'
import Icon from '@/Components/Icon.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, onMounted, reactive, computed } from 'vue'
import axios from 'axios'

// People management state
const personas = ref({ data: [], links: [], total: 0, from: 0, to: 0 })
const showModal = ref(false)
const isEditing = ref(false)
const editingPersonaId = ref(null)
const loading = ref(false)

const searchForm = reactive({
  search: '',
  per_page: 15,
  tipo_persona: '',
  tiene_portatiles: '',
  tiene_vehiculos: '',
  orden: 'nombre_asc',
})

// Form
const form = useForm({
  nombre: '',
  documento: '',
  tipoPersona: '',
  correo: '',
})

const tiposPersona = [
  { value: 'Aprendiz', label: 'Aprendiz' },
  { value: 'Instructor', label: 'Instructor' },
  { value: 'Empleado', label: 'Empleado' },
  { value: 'Contratista', label: 'Contratista' },
  { value: 'Visitante', label: 'Visitante' },
]

const opcionesOrden = [
  { value: 'nombre_asc', label: 'Nombre (A-Z)' },
  { value: 'nombre_desc', label: 'Nombre (Z-A)' },
  { value: 'documento_asc', label: 'Documento (0-9)' },
  { value: 'documento_desc', label: 'Documento (9-0)' },
  { value: 'reciente', label: 'Más recientes' },
  { value: 'antiguo', label: 'Más antiguos' },
]

// Computed para verificar si hay filtros activos
const hasActiveFilters = computed(() => {
  return searchForm.search || 
         searchForm.tipo_persona || 
         searchForm.tiene_portatiles || 
         searchForm.tiene_vehiculos ||
         searchForm.orden !== 'nombre_asc'
})

// Contador de filtros activos
const activeFiltersCount = computed(() => {
  let count = 0
  if (searchForm.search) count++
  if (searchForm.tipo_persona) count++
  if (searchForm.tiene_portatiles) count++
  if (searchForm.tiene_vehiculos) count++
  if (searchForm.orden !== 'nombre_asc') count++
  return count
})

// Debounce function
const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Load personas data
const loadPersonas = async () => {
  loading.value = true
  try {
    const response = await axios.get('/system/admin/personas/data', {
      params: searchForm
    })
    personas.value = response.data.personas
  } catch (error) {
    console.error('Error loading personas:', error)
  } finally {
    loading.value = false
  }
}

// Search with debounce
const search = debounce(() => {
  loadPersonas()
}, 300)

// Clear all filters
const clearFilters = () => {
  searchForm.search = ''
  searchForm.tipo_persona = ''
  searchForm.tiene_portatiles = ''
  searchForm.tiene_vehiculos = ''
  searchForm.orden = 'nombre_asc'
  loadPersonas()
}

// Open create modal
const openCreateModal = () => {
  isEditing.value = false
  editingPersonaId.value = null
  form.reset()
  form.clearErrors()
  showModal.value = true
}

// Open edit modal
const openEditModal = (persona) => {
  isEditing.value = true
  editingPersonaId.value = persona.id
  form.nombre = persona.nombre
  form.documento = persona.documento
  form.tipoPersona = persona.tipoPersona || persona.TipoPersona
  form.correo = persona.correo || ''
  form.clearErrors()
  showModal.value = true
}

// Close modal
const closeModal = () => {
  showModal.value = false
  form.reset()
  form.clearErrors()
}

// Can save
const canSave = computed(() => {
  return form.nombre && form.documento && form.tipoPersona
})

// Submit form
const submit = () => {
  if (isEditing.value) {
    form.put(route('system.admin.personas.update', editingPersonaId.value), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal()
        loadPersonas()
      },
    })
  } else {
    form.post(route('system.admin.personas.store'), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal()
        loadPersonas()
      },
    })
  }
}

// Delete persona
const confirmDelete = (persona) => {
  if (confirm(`¿Estás seguro de eliminar a ${persona.nombre}?`)) {
    router.delete(route('system.admin.personas.destroy', persona.id), {
      preserveScroll: true,
      onSuccess: () => loadPersonas(),
    })
  }
}

// Load personas from pagination link
const loadPersonasPage = async (url) => {
  if (!url) return
  loading.value = true
  try {
    const response = await axios.get(url)
    personas.value = response.data.personas
  } catch (error) {
    console.error('Error loading personas page:', error)
  } finally {
    loading.value = false
  }
}

// Load data on component mount
onMounted(() => {
  loadPersonas()
})
</script>

<template>
  <SystemLayout>
    <Head title="Gestión de Personas" />

    <template #header>
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sena-green-600 dark:bg-cyan-600">
            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h2 class="text-lg sm:text-xl font-bold text-theme-primary">Gestión de Personas</h2>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-sena-green-600 hover:bg-sena-green-700 active:bg-sena-green-800 dark:bg-blue-600 dark:hover:bg-blue-500 dark:active:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium touch-manipulation"
        >
          <Icon name="user-plus" class="w-4 h-4" />
          <span class="hidden sm:inline">Nueva Persona</span>
          <span class="sm:hidden">Nueva</span>
        </button>
      </div>
    </template>

    <div class="space-y-3 sm:space-y-4">
      <!-- Filtros Avanzados -->
      <div class="bg-theme-card rounded-lg border border-theme-primary p-3 sm:p-4 shadow-theme-sm">
        <!-- Header de filtros -->
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <Icon name="filter" class="w-4 h-4 text-theme-secondary" />
            <h3 class="text-sm font-semibold text-theme-primary">Filtros</h3>
            <span v-if="activeFiltersCount > 0" class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-sena-green-600 dark:bg-cyan-600 text-white text-xs font-bold">
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

        <!-- Fila 1: Búsqueda y Tipo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mb-2">
          <!-- Búsqueda general -->
          <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-theme-secondary mb-1">
              <Icon name="search" class="w-3 h-3 inline mr-1" />
              Buscar
            </label>
            <input
              v-model="searchForm.search"
              @input="search"
              type="search"
              inputmode="search"
              placeholder="Nombre, documento..."
              class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary placeholder-theme-muted focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent touch-manipulation"
            >
          </div>

          <!-- Tipo de persona -->
          <div>
            <label class="block text-xs font-medium text-theme-secondary mb-1">
              <Icon name="users" class="w-3 h-3 inline mr-1" />
              Tipo
            </label>
            <select
              v-model="searchForm.tipo_persona"
              @change="search"
              class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 touch-manipulation"
            >
              <option value="">Todos los tipos</option>
              <option v-for="tipo in tiposPersona" :key="tipo.value" :value="tipo.value">
                {{ tipo.label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Fila 2: Filtros de recursos y ordenamiento -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
          <!-- Tiene portátiles -->
          <div>
            <label class="block text-xs font-medium text-theme-secondary mb-1">
              <Icon name="laptop" class="w-3 h-3 inline mr-1" />
              Portátiles
            </label>
            <select
              v-model="searchForm.tiene_portatiles"
              @change="search"
              class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 touch-manipulation"
            >
              <option value="">Todos</option>
              <option value="si">Con portátiles</option>
              <option value="no">Sin portátiles</option>
            </select>
          </div>

          <!-- Tiene vehículos -->
          <div>
            <label class="block text-xs font-medium text-theme-secondary mb-1">
              <Icon name="car" class="w-3 h-3 inline mr-1" />
              Vehículos
            </label>
            <select
              v-model="searchForm.tiene_vehiculos"
              @change="search"
              class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 touch-manipulation"
            >
              <option value="">Todos</option>
              <option value="si">Con vehículos</option>
              <option value="no">Sin vehículos</option>
            </select>
          </div>

          <!-- Ordenamiento -->
          <div>
            <label class="block text-xs font-medium text-theme-secondary mb-1">
              <Icon name="arrow-up-down" class="w-3 h-3 inline mr-1" />
              Ordenar
            </label>
            <select
              v-model="searchForm.orden"
              @change="search"
              class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 touch-manipulation"
            >
              <option v-for="orden in opcionesOrden" :key="orden.value" :value="orden.value">
                {{ orden.label }}
              </option>
            </select>
          </div>

          <!-- Por página -->
          <div>
            <label class="block text-xs font-medium text-theme-secondary mb-1">
              <Icon name="list" class="w-3 h-3 inline mr-1" />
              Mostrar
            </label>
            <select
              v-model="searchForm.per_page"
              @change="search"
              class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 touch-manipulation"
            >
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>

        <!-- Indicadores de filtros activos -->
        <div v-if="hasActiveFilters" class="mt-3 pt-3 border-t border-theme-primary">
          <div class="flex flex-wrap gap-1.5">
            <span v-if="searchForm.search" class="inline-flex items-center gap-1 px-2 py-1 bg-sena-green-100 dark:bg-sena-green-900/30 text-sena-green-700 dark:text-sena-green-300 rounded-md text-xs">
              <Icon name="search" class="w-3 h-3" />
              {{ searchForm.search }}
            </span>
            <span v-if="searchForm.tipo_persona" class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-md text-xs">
              <Icon name="users" class="w-3 h-3" />
              {{ searchForm.tipo_persona }}
            </span>
            <span v-if="searchForm.tiene_portatiles" class="inline-flex items-center gap-1 px-2 py-1 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 rounded-md text-xs">
              <Icon name="laptop" class="w-3 h-3" />
              {{ searchForm.tiene_portatiles === 'si' ? 'Con portátiles' : 'Sin portátiles' }}
            </span>
            <span v-if="searchForm.tiene_vehiculos" class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-md text-xs">
              <Icon name="car" class="w-3 h-3" />
              {{ searchForm.tiene_vehiculos === 'si' ? 'Con vehículos' : 'Sin vehículos' }}
            </span>
            <span v-if="searchForm.orden !== 'nombre_asc'" class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-md text-xs">
              <Icon name="arrow-up-down" class="w-3 h-3" />
              {{ opcionesOrden.find(o => o.value === searchForm.orden)?.label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Tabla de personas -->
      <div class="bg-theme-card rounded-lg border border-theme-primary shadow-theme-sm overflow-hidden">
        <div class="border-b border-theme-primary bg-theme-secondary p-3 sm:p-4">
          <h3 class="text-sm sm:text-base font-semibold text-theme-primary">
            Personas
            <span v-if="personas.total" class="text-xs sm:text-sm font-normal text-theme-secondary ml-1">
              ({{ personas.total }})
            </span>
          </h3>
        </div>

        <!-- Loading indicator -->
        <div v-if="loading" class="text-center py-12">
          <div class="inline-flex items-center gap-2 text-theme-secondary text-sm">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Cargando...
          </div>
        </div>

        <!-- Vista Móvil -->
        <div v-if="!loading" class="lg:hidden divide-y divide-theme-primary">
          <div v-for="persona in personas.data" :key="persona.id" class="p-3 hover:bg-theme-secondary transition-colors">
            <div class="flex items-start justify-between gap-2 mb-2">
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-theme-primary text-sm truncate">{{ persona.nombre }}</p>
                <p class="text-xs text-theme-secondary">{{ persona.documento || '—' }}</p>
              </div>
              <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-sena-green-100 dark:bg-sena-green-800 text-sena-green-700 dark:text-sena-green-300 whitespace-nowrap">
                {{ persona.tipoPersona }}
              </span>
            </div>
            
            <div class="flex items-center justify-between pt-2 border-t border-theme-primary">
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs text-cyan-600 dark:text-cyan-400">
                  <Icon name="laptop" class="w-3 h-3" />
                  {{ persona.portatiles?.length || 0 }}
                </span>
                <span class="inline-flex items-center gap-1 text-xs text-sena-yellow-600 dark:text-sena-yellow-400">
                  <Icon name="car" class="w-3 h-3" />
                  {{ persona.vehiculos?.length || 0 }}
                </span>
              </div>
              <div class="flex items-center gap-1.5">
                <button
                  @click="openEditModal(persona)"
                  class="p-2 text-xs bg-sena-green-600 hover:bg-sena-green-700 active:bg-sena-green-800 dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded transition-colors touch-manipulation"
                  title="Editar"
                >
                  <Icon name="pencil" class="w-3.5 h-3.5" />
                </button>
                <button
                  @click="confirmDelete(persona)"
                  class="p-2 text-xs bg-red-600 hover:bg-red-700 active:bg-red-800 dark:bg-red-700 dark:hover:bg-red-600 text-white rounded transition-colors touch-manipulation"
                  title="Eliminar"
                >
                  <Icon name="trash" class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
          <div v-if="!personas.data?.length" class="p-8 text-center text-theme-muted">
            <div class="flex flex-col items-center gap-2">
              <svg class="h-8 w-8 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span class="text-sm">No hay personas</span>
            </div>
          </div>
        </div>

        <!-- Tabla Desktop -->
        <div v-if="!loading" class="hidden lg:block overflow-x-auto">
          <table class="min-w-full divide-y divide-theme-primary text-sm">
            <thead class="bg-theme-secondary">
              <tr>
                <th class="px-3 py-2 text-left text-xs font-semibold text-theme-secondary">ID</th>
                <th class="px-3 py-2 text-left text-xs font-semibold text-theme-secondary">Documento</th>
                <th class="px-3 py-2 text-left text-xs font-semibold text-theme-secondary">Nombre</th>
                <th class="px-3 py-2 text-left text-xs font-semibold text-theme-secondary">Tipo</th>
                <th class="px-3 py-2 text-left text-xs font-semibold text-theme-secondary">Portátiles</th>
                <th class="px-3 py-2 text-left text-xs font-semibold text-theme-secondary">Vehículos</th>
                <th class="px-3 py-2 text-left text-xs font-semibold text-theme-secondary">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-theme-primary bg-theme-card">
              <tr v-for="persona in personas.data" :key="persona.id" class="transition-colors hover:bg-theme-secondary">
                <td class="px-3 py-3 text-theme-primary">{{ persona.id }}</td>
                <td class="px-3 py-3 text-theme-secondary">{{ persona.documento || '—' }}</td>
                <td class="px-3 py-3 font-medium text-theme-primary">{{ persona.nombre }}</td>
                <td class="px-3 py-3">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-sena-green-100 dark:bg-sena-green-800 text-sena-green-700 dark:text-sena-green-300">
                    {{ persona.tipoPersona }}
                  </span>
                </td>
                <td class="px-3 py-3">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-cyan-100 dark:bg-cyan-800 text-cyan-700 dark:text-cyan-300">
                    {{ persona.portatiles?.length || 0 }}
                  </span>
                </td>
                <td class="px-3 py-3">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-sena-yellow-100 dark:bg-sena-yellow-800 text-sena-yellow-700 dark:text-sena-yellow-300">
                    {{ persona.vehiculos?.length || 0 }}
                  </span>
                </td>
                <td class="px-3 py-3">
                  <div class="flex items-center gap-1.5">
                    <button
                      @click="openEditModal(persona)"
                      class="p-1.5 text-xs bg-sena-green-600 hover:bg-sena-green-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded transition-colors"
                      title="Editar"
                    >
                      <Icon name="pencil" class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="confirmDelete(persona)"
                      class="p-1.5 text-xs bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white rounded transition-colors"
                      title="Eliminar"
                    >
                      <Icon name="trash" class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!personas.data?.length">
                <td colspan="7" class="px-3 py-10 text-center text-theme-muted">
                  <div class="flex flex-col items-center gap-2">
                    <svg class="h-8 w-8 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="text-sm">No hay personas</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="personas.links && personas.data?.length > 0" class="border-t border-theme-primary bg-theme-secondary px-3 py-3">
          <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
            <div class="text-xs sm:text-sm text-theme-secondary">
              {{ personas.from }} - {{ personas.to }} de {{ personas.total }}
            </div>
            <div class="flex gap-1 flex-wrap justify-center">
              <button
                v-for="(link, index) in personas.links"
                :key="index"
                @click="loadPersonasPage(link.url)"
                :disabled="!link.url"
                :class="[
                  'min-w-[2rem] h-8 px-2 text-xs border rounded transition-colors touch-manipulation',
                  link.active
                    ? 'bg-sena-green-600 dark:bg-blue-600 text-white border-sena-green-600 dark:border-blue-600'
                    : link.url
                      ? 'bg-theme-card text-theme-secondary border-theme-primary hover:bg-theme-secondary active:bg-theme-secondary'
                      : 'bg-theme-tertiary text-theme-muted border-theme-primary cursor-not-allowed opacity-50'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para crear/editar persona -->
        <Modal :show="showModal" @close="closeModal" max-width="md">
      <div class="p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-base font-semibold text-sage-800 dark:text-sage-100 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-sena-green-600 dark:bg-cyan-600">
              <Icon :name="isEditing ? 'pencil' : 'user-plus'" class="w-4 h-4 text-white" />
            </div>
            {{ isEditing ? 'Editar Persona' : 'Nueva Persona' }}
          </h3>
          <button @click="closeModal" class="text-sage-400 hover:text-sage-600 dark:hover:text-sage-300 touch-manipulation">
            <Icon name="x" class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submit" class="space-y-3">
          <!-- Nombre Completo -->
          <div>
            <label class="block text-xs font-medium text-sage-700 dark:text-sage-300 mb-1.5">
              <Icon name="user" class="w-3 h-3 inline mr-1" />
              Nombre Completo *
            </label>
            <input
              v-model="form.nombre"
              type="text"
              required
              placeholder="Ej: Juan Pérez García"
              class="w-full px-3 py-2 text-sm border border-sage-300 dark:border-sage-600 rounded-lg focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 bg-white dark:bg-sage-700 text-sage-900 dark:text-sage-100 placeholder-sage-400 dark:placeholder-sage-500 touch-manipulation"
              :class="{ 'border-red-500': form.errors.nombre }"
            />
            <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
          </div>

          <!-- Documento de Identidad -->
          <div>
            <label class="block text-xs font-medium text-sage-700 dark:text-sage-300 mb-1.5">
              <Icon name="credit-card" class="w-3 h-3 inline mr-1" />
              Documento de Identidad *
            </label>
            <input
              v-model="form.documento"
              type="text"
              required
              placeholder="Ej: 12345678"
              inputmode="numeric"
              class="w-full px-3 py-2 text-sm border border-sage-300 dark:border-sage-600 rounded-lg focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 bg-white dark:bg-sage-700 text-sage-900 dark:text-sage-100 placeholder-sage-400 dark:placeholder-sage-500 touch-manipulation"
              :class="{ 'border-red-500': form.errors.documento }"
            />
            <p v-if="form.errors.documento" class="mt-1 text-xs text-red-500">{{ form.errors.documento }}</p>
          </div>

          <!-- Tipo de Persona -->
          <div>
            <label class="block text-xs font-medium text-sage-700 dark:text-sage-300 mb-1.5">
              <Icon name="users" class="w-3 h-3 inline mr-1" />
              Tipo de Persona *
            </label>
            <select
              v-model="form.tipoPersona"
              required
              class="w-full px-3 py-2 text-sm border border-sage-300 dark:border-sage-600 rounded-lg focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 bg-white dark:bg-sage-700 text-sage-900 dark:text-sage-100 appearance-none cursor-pointer touch-manipulation"
              :class="{ 'border-red-500': form.errors.tipoPersona }"
            >
              <option value="">Seleccionar tipo</option>
              <option v-for="tipo in tiposPersona" :key="tipo.value" :value="tipo.value">
                {{ tipo.label }}
              </option>
            </select>
            <p v-if="form.errors.tipoPersona" class="mt-1 text-xs text-red-500">{{ form.errors.tipoPersona }}</p>
          </div>

          <!-- Correo Electrónico -->
          <div>
            <label class="block text-xs font-medium text-sage-700 dark:text-sage-300 mb-1.5">
              <Icon name="mail" class="w-3 h-3 inline mr-1" />
              Correo Electrónico
            </label>
            <input
              v-model="form.correo"
              type="email"
              placeholder="correo@ejemplo.com"
              inputmode="email"
              class="w-full px-3 py-2 text-sm border border-sage-300 dark:border-sage-600 rounded-lg focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 bg-white dark:bg-sage-700 text-sage-900 dark:text-sage-100 placeholder-sage-400 dark:placeholder-sage-500 touch-manipulation"
              :class="{ 'border-red-500': form.errors.correo }"
            />
            <p v-if="form.errors.correo" class="mt-1 text-xs text-red-500">{{ form.errors.correo }}</p>
            <p class="mt-1 text-xs text-sage-500 dark:text-sage-400 flex items-center gap-1">
              <Icon name="info" class="w-3 h-3" />
              <span>Se enviará un QR por correo si se proporciona</span>
            </p>
          </div>

          <!-- Buttons -->
          <div class="flex justify-end gap-2 pt-4 border-t border-sage-200 dark:border-sage-700">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-sm border border-sage-300 dark:border-sage-600 text-sage-700 dark:text-sage-300 rounded-lg hover:bg-sage-50 dark:hover:bg-sage-700 transition-colors touch-manipulation"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="!canSave || form.processing"
              class="px-4 py-2 text-sm bg-sena-green-600 hover:bg-sena-green-700 active:bg-sena-green-800 dark:bg-cyan-600 dark:hover:bg-cyan-500 dark:active:bg-cyan-700 text-white font-semibold rounded-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 transition-colors touch-manipulation"
            >
              <Icon v-if="form.processing" name="loader" class="w-4 h-4 animate-spin" />
              <Icon v-else name="save" class="w-4 h-4" />
              {{ form.processing ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Guardar') }}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </SystemLayout>
</template>