<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import Modal from '@/Components/Modal.vue'
import Icon from '@/Components/Icon.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, watch, computed, onMounted } from 'vue'
import axios from 'axios'

// Estado de datos
const programas = ref({ data: [], meta: {} })
const loading = ref(false)

// Filtros
const search = ref('')
const estado = ref('todos')
const perPage = ref(15)

// Modal state
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

// Form data
const form = ref({
  nombre: '',
  ficha: '',
  fecha_inicio: '',
  fecha_fin: '',
  nivel_formacion: '',
  activo: true,
  descripcion: ''
})

const formErrors = ref({})

// Niveles de formación
const nivelesFormacion = [
  'Técnico',
  'Tecnólogo',
  'Especialización',
  'Curso Corto',
  'Diplomado'
]

// Estados disponibles para filtro
const estadosDisponibles = [
  { value: 'todos', label: 'Todos' },
  { value: 'activos', label: 'Activos' },
  { value: 'inactivos', label: 'Inactivos' },
  { value: 'vigentes', label: 'Vigentes' },
  { value: 'finalizados', label: 'Finalizados' }
]

// Watchers para filtros
watch([search, estado, perPage], () => {
  fetchProgramas()
}, { debounce: 300 })

// Cargar programas
const fetchProgramas = async (page = 1) => {
  loading.value = true
  try {
    const response = await axios.get(route('system.admin.programas-formacion.data'), {
      params: {
        page,
        per_page: perPage.value,
        search: search.value,
        estado: estado.value
      }
    })
    programas.value = {
      data: response.data.data,
      meta: {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
        from: response.data.from,
        to: response.data.to
      }
    }
  } catch (error) {
    console.error('Error al cargar programas:', error)
  } finally {
    loading.value = false
  }
}

// Abrir modal para crear
const openCreateModal = () => {
  isEditing.value = false
  editingId.value = null
  form.value = {
    nombre: '',
    ficha: '',
    fecha_inicio: '',
    fecha_fin: '',
    nivel_formacion: '',
    activo: true,
    descripcion: ''
  }
  formErrors.value = {}
  showModal.value = true
}

// Abrir modal para editar
const openEditModal = (programa) => {
  isEditing.value = true
  editingId.value = programa.id
  form.value = {
    nombre: programa.nombre,
    ficha: programa.ficha,
    fecha_inicio: programa.fecha_inicio,
    fecha_fin: programa.fecha_fin,
    nivel_formacion: programa.nivel_formacion,
    activo: programa.activo,
    descripcion: programa.descripcion || ''
  }
  formErrors.value = {}
  showModal.value = true
}

// Cerrar modal
const closeModal = () => {
  showModal.value = false
  form.value = {
    nombre: '',
    ficha: '',
    fecha_inicio: '',
    fecha_fin: '',
    nivel_formacion: '',
    activo: true,
    descripcion: ''
  }
  formErrors.value = {}
}

// Validar formulario
const canSave = computed(() => {
  return form.value.nombre && 
         form.value.ficha && 
         form.value.fecha_inicio && 
         form.value.fecha_fin && 
         form.value.nivel_formacion
})

// Guardar (crear o actualizar)
const submit = async () => {
  if (!canSave.value) return

  formErrors.value = {}
  
  try {
    if (isEditing.value) {
      const response = await axios.put(
        route('system.admin.programas-formacion.update', editingId.value),
        form.value
      )
      if (response.data.success) {
        closeModal()
        fetchProgramas()
        showNotification('Programa actualizado exitosamente', 'success')
      }
    } else {
      const response = await axios.post(
        route('system.admin.programas-formacion.store'),
        form.value
      )
      if (response.data.success) {
        closeModal()
        fetchProgramas()
        showNotification('Programa creado exitosamente', 'success')
      }
    }
  } catch (error) {
    if (error.response?.status === 422) {
      formErrors.value = error.response.data.errors
    } else {
      showNotification('Error al guardar el programa', 'error')
    }
  }
}

// Eliminar programa
const remove = async (id, nombre) => {
  if (!confirm(`¿Está seguro de eliminar el programa "${nombre}"?`)) return

  try {
    const response = await axios.delete(route('system.admin.programas-formacion.destroy', id))
    if (response.data.success) {
      fetchProgramas()
      showNotification('Programa eliminado exitosamente', 'success')
    }
  } catch (error) {
    if (error.response?.data?.message) {
      showNotification(error.response.data.message, 'error')
    } else {
      showNotification('Error al eliminar el programa', 'error')
    }
  }
}

// Toggle activo/inactivo
const toggleActivo = async (id) => {
  try {
    const response = await axios.post(route('system.admin.programas-formacion.toggle', id))
    if (response.data.success) {
      fetchProgramas()
      showNotification('Estado actualizado exitosamente', 'success')
    }
  } catch (error) {
    showNotification('Error al actualizar el estado', 'error')
  }
}

// Notificaciones
const showNotification = (message, type = 'success') => {
  // Implementar sistema de notificaciones (puedes usar toast, etc.)
  alert(message)
}

// Paginación
const changePage = (page) => {
  if (page >= 1 && page <= programas.value.meta.last_page) {
    fetchProgramas(page)
  }
}

// Formatear fechas
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

// Obtener badge de estado
const getEstadoBadge = (programa) => {
  if (!programa.activo) {
    return { text: 'Inactivo', class: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400' }
  }
  if (programa.ha_finalizado) {
    return { text: 'Finalizado', class: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }
  }
  if (programa.esta_vigente) {
    return { text: 'Vigente', class: 'bg-sena-green-100 text-sena-green-800 dark:bg-sena-green-900/30 dark:text-sena-green-400' }
  }
  return { text: 'Próximamente', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }
}

// Cargar datos al montar
onMounted(() => {
  fetchProgramas()
})
</script>

<template>
  <SystemLayout>
    <Head title="Programas de Formación" />

    <template #header>
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-xl font-semibold leading-tight text-theme-primary">Programas de Formación</h2>
        <button 
          class="rounded-md bg-sena-green-600 hover:bg-sena-green-700 dark:bg-blue-600 dark:hover:bg-blue-500 px-3 py-2 text-sm font-medium text-white transition-colors flex items-center gap-2" 
          @click="openCreateModal"
        >
          <Icon name="Plus" :size="16" />
          Nuevo programa
        </button>
      </div>
    </template>

    <div class="space-y-4">
      <!-- Filtros -->
      <div class="rounded-lg border border-theme-primary bg-theme-card p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Búsqueda -->
          <div class="md:col-span-2">
            <input 
              v-model="search" 
              type="text" 
              class="w-full rounded-md border border-theme-primary px-3 py-2 text-sm bg-theme-card text-theme-primary placeholder-theme-muted focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all" 
              placeholder="Buscar por nombre, ficha o nivel..." 
            />
          </div>

          <!-- Estado -->
          <div>
            <select 
              v-model="estado"
              class="w-full rounded-md border border-theme-primary px-3 py-2 text-sm bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all"
            >
              <option v-for="est in estadosDisponibles" :key="est.value" :value="est.value">
                {{ est.label }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto rounded-lg border border-theme-primary bg-theme-card shadow-sm">
        <table class="min-w-full divide-y divide-theme-primary text-sm">
          <thead class="bg-theme-secondary">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-theme-secondary">Programa</th>
              <th class="px-4 py-3 text-left font-semibold text-theme-secondary">Ficha</th>
              <th class="px-4 py-3 text-left font-semibold text-theme-secondary">Nivel</th>
              <th class="px-4 py-3 text-left font-semibold text-theme-secondary">Fechas</th>
              <th class="px-4 py-3 text-left font-semibold text-theme-secondary">Estado</th>
              <th class="px-4 py-3 text-center font-semibold text-theme-secondary">Personas</th>
              <th class="px-4 py-3 text-right font-semibold text-theme-secondary">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-theme-primary bg-theme-card">
            <tr v-if="loading">
              <td colspan="7" class="px-4 py-12 text-center text-theme-muted">
                <div class="flex flex-col items-center gap-2">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sena-green-600 dark:border-cyan-500"></div>
                  <span>Cargando programas...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="!programas.data?.length">
              <td colspan="7" class="px-4 py-12 text-center text-theme-muted">
                <div class="flex flex-col items-center gap-2">
                  <Icon name="BookOpen" :size="48" class="opacity-50" />
                  <span class="font-medium">No se encontraron programas de formación</span>
                </div>
              </td>
            </tr>
            <tr v-else v-for="programa in programas.data" :key="programa.id" class="hover:bg-theme-secondary transition-colors">
              <td class="px-4 py-3">
                <div class="flex flex-col">
                  <span class="font-medium text-theme-secondary">{{ programa.nombre }}</span>
                  <span v-if="programa.descripcion" class="text-xs text-theme-muted mt-1">{{ programa.descripcion }}</span>
                </div>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-mono font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                  {{ programa.ficha }}
                </span>
              </td>
              <td class="px-4 py-3 text-theme-secondary">{{ programa.nivel_formacion }}</td>
              <td class="px-4 py-3">
                <div class="flex flex-col text-xs">
                  <span class="text-theme-secondary">{{ formatDate(programa.fecha_inicio) }}</span>
                  <span class="text-theme-muted">{{ formatDate(programa.fecha_fin) }}</span>
                  <span v-if="programa.esta_vigente && !programa.ha_finalizado" class="text-sena-green-600 dark:text-sena-green-400 font-medium mt-1">
                    {{ programa.dias_restantes }} días restantes
                  </span>
                </div>
              </td>
              <td class="px-4 py-3">
                <span 
                  :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-medium', getEstadoBadge(programa).class]"
                >
                  {{ getEstadoBadge(programa).text }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-theme-secondary text-theme-primary font-medium text-xs">
                  {{ programa.personas_count }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    class="rounded border border-theme-primary px-2 py-1 text-xs font-medium text-theme-primary hover:bg-theme-secondary transition-colors flex items-center gap-1"
                    :class="programa.activo ? 'border-yellow-400 text-yellow-600 dark:text-yellow-400' : 'border-sena-green-400 text-sena-green-600 dark:text-sena-green-400'"
                    @click="toggleActivo(programa.id)"
                    :title="programa.activo ? 'Desactivar' : 'Activar'"
                  >
                    <Icon :name="programa.activo ? 'EyeOff' : 'Eye'" :size="12" />
                  </button>
                  <button 
                    class="rounded border border-sena-green-300 dark:border-cyan-700 px-2 py-1 text-xs font-medium text-sena-green-700 dark:text-cyan-400 hover:bg-sena-green-50 dark:hover:bg-cyan-900/20 transition-colors flex items-center gap-1" 
                    @click="openEditModal(programa)"
                  >
                    <Icon name="Edit" :size="12" />
                    Editar
                  </button>
                  <button 
                    class="rounded border border-red-300 dark:border-red-700 px-2 py-1 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors flex items-center gap-1" 
                    @click="remove(programa.id, programa.nombre)"
                    :disabled="programa.personas_count > 0"
                    :class="programa.personas_count > 0 ? 'opacity-50 cursor-not-allowed' : ''"
                    :title="programa.personas_count > 0 ? 'No se puede eliminar (tiene personas asociadas)' : 'Eliminar programa'"
                  >
                    <Icon name="Trash2" :size="12" />
                    Eliminar
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div v-if="programas.meta?.last_page > 1" class="border-t border-theme-primary px-4 py-3 bg-theme-secondary">
          <div class="flex items-center justify-between">
            <div class="text-sm text-theme-muted">
              Mostrando {{ programas.meta.from }} - {{ programas.meta.to }} de {{ programas.meta.total }} programas
            </div>
            <div class="flex gap-2">
              <button 
                @click="changePage(programas.meta.current_page - 1)" 
                :disabled="programas.meta.current_page === 1"
                class="px-3 py-1 text-sm rounded border border-theme-primary text-theme-primary hover:bg-theme-secondary transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Anterior
              </button>
              <span class="px-3 py-1 text-sm text-theme-primary">
                Página {{ programas.meta.current_page }} de {{ programas.meta.last_page }}
              </span>
              <button 
                @click="changePage(programas.meta.current_page + 1)" 
                :disabled="programas.meta.current_page === programas.meta.last_page"
                class="px-3 py-1 text-sm rounded border border-theme-primary text-theme-primary hover:bg-theme-secondary transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Siguiente
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para crear/editar -->
    <Modal :show="showModal" @close="closeModal" max-width="2xl">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-theme-primary mb-4">
          {{ isEditing ? 'Editar Programa de Formación' : 'Nuevo Programa de Formación' }}
        </h3>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Nombre -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-theme-secondary mb-1">
                Nombre del programa <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.nombre"
                type="text"
                class="w-full rounded-md border border-theme-primary px-3 py-2 text-sm bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent"
                placeholder="Ej: Tecnología en Desarrollo de Software"
              />
              <p v-if="formErrors.nombre" class="mt-1 text-xs text-red-600 dark:text-red-400">
                {{ formErrors.nombre[0] }}
              </p>
            </div>

            <!-- Ficha -->
            <div>
              <label class="block text-sm font-medium text-theme-secondary mb-1">
                Ficha <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.ficha"
                type="text"
                class="w-full rounded-md border border-theme-primary px-3 py-2 text-sm bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent"
                placeholder="Ej: 2889453"
              />
              <p v-if="formErrors.ficha" class="mt-1 text-xs text-red-600 dark:text-red-400">
                {{ formErrors.ficha[0] }}
              </p>
            </div>

            <!-- Nivel de formación -->
            <div>
              <label class="block text-sm font-medium text-theme-secondary mb-1">
                Nivel de formación <span class="text-red-500">*</span>
              </label>
              <select 
                v-model="form.nivel_formacion"
                class="w-full rounded-md border border-theme-primary px-3 py-2 text-sm bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent"
              >
                <option value="">Seleccione...</option>
                <option v-for="nivel in nivelesFormacion" :key="nivel" :value="nivel">
                  {{ nivel }}
                </option>
              </select>
              <p v-if="formErrors.nivel_formacion" class="mt-1 text-xs text-red-600 dark:text-red-400">
                {{ formErrors.nivel_formacion[0] }}
              </p>
            </div>

            <!-- Fecha inicio -->
            <div>
              <label class="block text-sm font-medium text-theme-secondary mb-1">
                Fecha de inicio <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.fecha_inicio"
                type="date"
                class="w-full rounded-md border border-theme-primary px-3 py-2 text-sm bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent"
              />
              <p v-if="formErrors.fecha_inicio" class="mt-1 text-xs text-red-600 dark:text-red-400">
                {{ formErrors.fecha_inicio[0] }}
              </p>
            </div>

            <!-- Fecha fin -->
            <div>
              <label class="block text-sm font-medium text-theme-secondary mb-1">
                Fecha de finalización <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.fecha_fin"
                type="date"
                class="w-full rounded-md border border-theme-primary px-3 py-2 text-sm bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent"
              />
              <p v-if="formErrors.fecha_fin" class="mt-1 text-xs text-red-600 dark:text-red-400">
                {{ formErrors.fecha_fin[0] }}
              </p>
            </div>

            <!-- Descripción -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-theme-secondary mb-1">
                Descripción
              </label>
              <textarea 
                v-model="form.descripcion"
                rows="3"
                class="w-full rounded-md border border-theme-primary px-3 py-2 text-sm bg-theme-card text-theme-primary focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent"
                placeholder="Descripción opcional del programa..."
              ></textarea>
              <p v-if="formErrors.descripcion" class="mt-1 text-xs text-red-600 dark:text-red-400">
                {{ formErrors.descripcion[0] }}
              </p>
            </div>

            <!-- Activo -->
            <div class="md:col-span-2">
              <label class="flex items-center gap-2 cursor-pointer">
                <input 
                  v-model="form.activo"
                  type="checkbox"
                  class="rounded border-theme-primary text-sena-green-600 focus:ring-sena-green-500 dark:text-cyan-500 dark:focus:ring-cyan-500"
                />
                <span class="text-sm font-medium text-theme-secondary">Programa activo</span>
              </label>
            </div>
          </div>

          <!-- Botones -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-theme-primary">
            <button 
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-theme-secondary hover:bg-theme-secondary rounded-md transition-colors"
            >
              Cancelar
            </button>
            <button 
              type="submit"
              :disabled="!canSave"
              class="px-4 py-2 text-sm font-medium text-white bg-sena-green-600 hover:bg-sena-green-700 dark:bg-blue-600 dark:hover:bg-blue-500 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isEditing ? 'Actualizar' : 'Crear' }} programa
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </SystemLayout>
</template>
