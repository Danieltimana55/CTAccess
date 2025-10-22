<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  items: Array,
  estadisticas: Object,
  filtro_tipo: String,
})

const filtroActual = ref(props.filtro_tipo || 'todos')

// Aplicar filtro
const aplicarFiltro = (tipo) => {
  filtroActual.value = tipo
  router.get(route('system.admin.papelera.index'), { tipo }, {
    preserveState: true,
    replace: true,
  })
}

// Modales
const showRestoreModal = ref(false)
const showDeleteModal = ref(false)
const showEmptyModal = ref(false)
const selectedItem = ref(null)
const confirmacion = ref('')
const confirmacionVaciar = ref('')

// Restaurar
const openRestoreModal = (item) => {
  selectedItem.value = item
  showRestoreModal.value = true
}

const restaurar = () => {
  router.post(route('system.admin.papelera.restore'), {
    tipo: selectedItem.value.tipo,
    id: selectedItem.value.id,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showRestoreModal.value = false
      selectedItem.value = null
    },
  })
}

// Eliminar permanentemente
const openDeleteModal = (item) => {
  selectedItem.value = item
  confirmacion.value = ''
  showDeleteModal.value = true
}

const eliminarPermanente = () => {
  if (confirmacion.value !== 'ELIMINAR PERMANENTEMENTE') {
    return
  }

  router.post(route('system.admin.papelera.force-delete'), {
    tipo: selectedItem.value.tipo,
    id: selectedItem.value.id,
    confirmacion: confirmacion.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false
      selectedItem.value = null
      confirmacion.value = ''
    },
  })
}

// Vaciar papelera
const openEmptyModal = () => {
  confirmacionVaciar.value = ''
  showEmptyModal.value = true
}

const vaciarPapelera = () => {
  if (confirmacionVaciar.value !== 'VACIAR PAPELERA') {
    return
  }

  router.post(route('system.admin.papelera.empty'), {
    confirmacion: confirmacionVaciar.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showEmptyModal.value = false
      confirmacionVaciar.value = ''
    },
  })
}

// Colores por tipo
const getTipoColor = (tipo) => {
  const colors = {
    persona: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    usuario: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    portatil: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    vehiculo: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    programa: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400',
  }
  return colors[tipo] || colors.persona
}

// Iconos por tipo
const getTipoIcon = (tipo) => {
  const icons = {
    persona: 'user',
    usuario: 'user-cog',
    portatil: 'laptop',
    vehiculo: 'truck',
    programa: 'book',
  }
  return icons[tipo] || 'file'
}
</script>

<template>
  <SystemLayout>
    <Head title="Papelera de Reciclaje" />

    <template #header>
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-600 dark:bg-gray-700">
            <Icon name="trash-2" :size="20" class="text-white" />
          </div>
          <div>
            <h2 class="text-xl font-bold text-theme-primary">Papelera de Reciclaje</h2>
            <p class="text-xs text-theme-secondary">Recupera o elimina permanentemente registros</p>
          </div>
        </div>
        <button
          v-if="items.length > 0"
          @click="openEmptyModal"
          class="inline-flex items-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors"
        >
          <Icon name="trash" :size="16" />
          Vaciar Papelera
        </button>
      </div>
    </template>

    <div class="space-y-4">
      <!-- Estadísticas -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-theme-primary">{{ estadisticas.total }}</div>
          <div class="text-xs text-theme-secondary">Total</div>
        </div>
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-blue-600">{{ estadisticas.personas }}</div>
          <div class="text-xs text-theme-secondary">Personas</div>
        </div>
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-purple-600">{{ estadisticas.usuarios }}</div>
          <div class="text-xs text-theme-secondary">Usuarios</div>
        </div>
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-green-600">{{ estadisticas.portatiles }}</div>
          <div class="text-xs text-theme-secondary">Portátiles</div>
        </div>
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-yellow-600">{{ estadisticas.vehiculos }}</div>
          <div class="text-xs text-theme-secondary">Vehículos</div>
        </div>
        <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
          <div class="text-2xl font-bold text-cyan-600">{{ estadisticas.programas }}</div>
          <div class="text-xs text-theme-secondary">Programas</div>
        </div>
      </div>

      <!-- Filtros por tipo -->
      <div class="bg-theme-card rounded-lg border border-theme-primary p-4 shadow-theme-sm">
        <div class="flex items-center gap-2 mb-3">
          <Icon name="filter" :size="16" class="text-theme-secondary" />
          <h3 class="text-sm font-semibold text-theme-primary">Filtrar por tipo</h3>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            @click="aplicarFiltro('todos')"
            :class="[
              'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
              filtroActual === 'todos'
                ? 'bg-sena-green-600 dark:bg-cyan-600 text-white'
                : 'bg-theme-secondary text-theme-primary hover:bg-theme-primary/10'
            ]"
          >
            Todos
          </button>
          <button
            @click="aplicarFiltro('personas')"
            :class="[
              'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
              filtroActual === 'personas'
                ? 'bg-blue-600 text-white'
                : 'bg-theme-secondary text-theme-primary hover:bg-theme-primary/10'
            ]"
          >
            Personas
          </button>
          <button
            @click="aplicarFiltro('usuarios')"
            :class="[
              'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
              filtroActual === 'usuarios'
                ? 'bg-purple-600 text-white'
                : 'bg-theme-secondary text-theme-primary hover:bg-theme-primary/10'
            ]"
          >
            Usuarios
          </button>
          <button
            @click="aplicarFiltro('portatiles')"
            :class="[
              'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
              filtroActual === 'portatiles'
                ? 'bg-green-600 text-white'
                : 'bg-theme-secondary text-theme-primary hover:bg-theme-primary/10'
            ]"
          >
            Portátiles
          </button>
          <button
            @click="aplicarFiltro('vehiculos')"
            :class="[
              'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
              filtroActual === 'vehiculos'
                ? 'bg-yellow-600 text-white'
                : 'bg-theme-secondary text-theme-primary hover:bg-theme-primary/10'
            ]"
          >
            Vehículos
          </button>
          <button
            @click="aplicarFiltro('programas')"
            :class="[
              'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
              filtroActual === 'programas'
                ? 'bg-cyan-600 text-white'
                : 'bg-theme-secondary text-theme-primary hover:bg-theme-primary/10'
            ]"
          >
            Programas
          </button>
        </div>
      </div>

      <!-- Lista de items -->
      <div v-if="items.length > 0" class="bg-theme-card rounded-lg border border-theme-primary shadow-theme-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-theme-primary">
            <thead class="bg-theme-secondary">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Tipo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Nombre</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Identificación</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Info Adicional</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-theme-secondary uppercase">Eliminado</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-theme-secondary uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-theme-primary">
              <tr v-for="item in items" :key="`${item.tipo}-${item.id}`" class="hover:bg-theme-secondary transition-colors">
                <td class="px-4 py-3">
                  <span :class="getTipoColor(item.tipo)" class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium">
                    <Icon :name="getTipoIcon(item.tipo)" :size="12" />
                    {{ item.tipo_display }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm font-medium text-theme-primary">
                  {{ item.nombre }}
                </td>
                <td class="px-4 py-3 text-sm text-theme-secondary font-mono">
                  {{ item.identificacion }}
                </td>
                <td class="px-4 py-3 text-sm text-theme-secondary">
                  {{ item.info_adicional }}
                </td>
                <td class="px-4 py-3 text-xs text-theme-secondary">
                  {{ item.deleted_at_formatted }}
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="openRestoreModal(item)"
                      class="inline-flex items-center gap-1 px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-medium transition-colors"
                    >
                      <Icon name="rotate-ccw" :size="12" />
                      Restaurar
                    </button>
                    <button
                      @click="openDeleteModal(item)"
                      class="inline-flex items-center gap-1 px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium transition-colors"
                    >
                      <Icon name="x" :size="12" />
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Estado vacío -->
      <div v-else class="bg-theme-card rounded-lg border border-theme-primary p-12 text-center shadow-theme-sm">
        <Icon name="inbox" :size="64" class="mx-auto mb-4 text-theme-muted opacity-50" />
        <h3 class="text-lg font-semibold text-theme-primary mb-2">Papelera vacía</h3>
        <p class="text-sm text-theme-secondary">No hay elementos eliminados para mostrar</p>
      </div>
    </div>

    <!-- Modal Restaurar -->
    <div v-if="showRestoreModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showRestoreModal = false">
      <div class="bg-theme-card rounded-lg shadow-xl max-w-md w-full mx-4 p-6 border border-theme-primary">
        <div class="flex items-start gap-4 mb-4">
          <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
            <Icon name="rotate-ccw" :size="24" class="text-green-600 dark:text-green-400" />
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-theme-primary mb-1">¿Restaurar registro?</h3>
            <p class="text-sm text-theme-secondary mb-2">
              Estás a punto de restaurar:
            </p>
            <div class="bg-theme-secondary rounded-lg p-3 mb-3">
              <div class="text-sm font-semibold text-theme-primary">{{ selectedItem?.nombre }}</div>
              <div class="text-xs text-theme-secondary">{{ selectedItem?.tipo_display }} • {{ selectedItem?.identificacion }}</div>
            </div>
            <p class="text-xs text-theme-secondary">
              El registro volverá a estar disponible en el sistema.
            </p>
          </div>
        </div>
        <div class="flex gap-2 justify-end">
          <button
            @click="showRestoreModal = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-theme-primary rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="restaurar"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors"
          >
            Restaurar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Eliminar Permanentemente -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showDeleteModal = false">
      <div class="bg-theme-card rounded-lg shadow-xl max-w-md w-full mx-4 p-6 border border-theme-primary">
        <div class="flex items-start gap-4 mb-4">
          <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
            <Icon name="alert-triangle" :size="24" class="text-red-600 dark:text-red-400" />
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-red-600 dark:text-red-400 mb-1">⚠️ Eliminar Permanentemente</h3>
            <p class="text-sm text-theme-secondary mb-3">
              Esta acción <strong class="text-red-600 dark:text-red-400">NO SE PUEDE DESHACER</strong>. El registro será eliminado definitivamente de la base de datos.
            </p>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 mb-3 border border-red-200 dark:border-red-800">
              <div class="text-sm font-semibold text-theme-primary">{{ selectedItem?.nombre }}</div>
              <div class="text-xs text-theme-secondary">{{ selectedItem?.tipo_display }} • {{ selectedItem?.identificacion }}</div>
            </div>
            <p class="text-xs text-theme-secondary mb-3">
              Para confirmar, escribe: <strong class="text-red-600 dark:text-red-400">ELIMINAR PERMANENTEMENTE</strong>
            </p>
            <input
              v-model="confirmacion"
              type="text"
              placeholder="ELIMINAR PERMANENTEMENTE"
              class="w-full px-3 py-2 border border-theme-primary rounded-lg bg-theme-card text-theme-primary text-sm focus:ring-2 focus:ring-red-500"
            />
          </div>
        </div>
        <div class="flex gap-2 justify-end">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-theme-primary rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="eliminarPermanente"
            :disabled="confirmacion !== 'ELIMINAR PERMANENTEMENTE'"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
              confirmacion === 'ELIMINAR PERMANENTEMENTE'
                ? 'bg-red-600 hover:bg-red-700 text-white'
                : 'bg-gray-300 dark:bg-gray-700 text-gray-500 cursor-not-allowed'
            ]"
          >
            Eliminar Permanentemente
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Vaciar Papelera -->
    <div v-if="showEmptyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showEmptyModal = false">
      <div class="bg-theme-card rounded-lg shadow-xl max-w-md w-full mx-4 p-6 border border-theme-primary">
        <div class="flex items-start gap-4 mb-4">
          <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
            <Icon name="trash" :size="24" class="text-red-600 dark:text-red-400" />
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-red-600 dark:text-red-400 mb-1">⚠️ Vaciar Papelera</h3>
            <p class="text-sm text-theme-secondary mb-3">
              Esta acción eliminará <strong class="text-red-600 dark:text-red-400">PERMANENTEMENTE</strong> todos los {{ estadisticas.total }} elementos de la papelera.
            </p>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 mb-3 border border-red-200 dark:border-red-800">
              <ul class="text-xs text-theme-secondary space-y-1">
                <li v-if="estadisticas.personas > 0">• {{ estadisticas.personas }} Personas</li>
                <li v-if="estadisticas.usuarios > 0">• {{ estadisticas.usuarios }} Usuarios</li>
                <li v-if="estadisticas.portatiles > 0">• {{ estadisticas.portatiles }} Portátiles</li>
                <li v-if="estadisticas.vehiculos > 0">• {{ estadisticas.vehiculos }} Vehículos</li>
                <li v-if="estadisticas.programas > 0">• {{ estadisticas.programas }} Programas</li>
              </ul>
            </div>
            <p class="text-xs text-theme-secondary mb-3">
              Para confirmar, escribe: <strong class="text-red-600 dark:text-red-400">VACIAR PAPELERA</strong>
            </p>
            <input
              v-model="confirmacionVaciar"
              type="text"
              placeholder="VACIAR PAPELERA"
              class="w-full px-3 py-2 border border-theme-primary rounded-lg bg-theme-card text-theme-primary text-sm focus:ring-2 focus:ring-red-500"
            />
          </div>
        </div>
        <div class="flex gap-2 justify-end">
          <button
            @click="showEmptyModal = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-theme-primary rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="vaciarPapelera"
            :disabled="confirmacionVaciar !== 'VACIAR PAPELERA'"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
              confirmacionVaciar === 'VACIAR PAPELERA'
                ? 'bg-red-600 hover:bg-red-700 text-white'
                : 'bg-gray-300 dark:bg-gray-700 text-gray-500 cursor-not-allowed'
            ]"
          >
            Vaciar Papelera
          </button>
        </div>
      </div>
    </div>
  </SystemLayout>
</template>
