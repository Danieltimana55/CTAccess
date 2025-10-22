<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Icon from '@/Components/Icon.vue'

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({})
  },
  filterOptions: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['filter-change'])

const localFilters = ref({
  fecha_inicio: props.filters.fecha_inicio || '',
  fecha_fin: props.filters.fecha_fin || '',
  jornada_id: props.filters.jornada_id || '',
  programa_id: props.filters.programa_id || '',
  tipo_persona: props.filters.tipo_persona || ''
})

const showFilters = ref(false)

// Aplicar filtros
const applyFilters = () => {
  router.get(route(route().current()), localFilters.value, {
    preserveState: true,
    preserveScroll: true,
    only: ['stats', 'charts']
  })
  emit('filter-change', localFilters.value)
}

// Limpiar filtros
const clearFilters = () => {
  localFilters.value = {
    fecha_inicio: '',
    fecha_fin: '',
    jornada_id: '',
    programa_id: '',
    tipo_persona: ''
  }
  applyFilters()
}

// Verificar si hay filtros activos
const hasActiveFilters = () => {
  return Object.values(localFilters.value).some(val => val !== '' && val !== null)
}
</script>

<template>
  <div class="bg-theme-card rounded-lg border border-theme-primary shadow-theme-sm">
    <!-- Header del filtro -->
    <div 
      class="flex items-center justify-between p-3 sm:p-4 cursor-pointer"
      @click="showFilters = !showFilters"
    >
      <div class="flex items-center gap-2">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 shadow-sm">
          <Icon name="filter" :size="18" class="text-white" />
        </div>
        <div>
          <h3 class="text-sm sm:text-base font-bold text-theme-primary">Filtros</h3>
          <p class="text-xs text-theme-muted">{{ hasActiveFilters() ? 'Filtros aplicados' : 'Sin filtros' }}</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span v-if="hasActiveFilters()" class="hidden sm:flex h-5 w-5 items-center justify-center rounded-full bg-blue-500 text-xs font-bold text-white">
          {{ Object.values(localFilters).filter(v => v).length }}
        </span>
        <Icon 
          :name="showFilters ? 'chevron-up' : 'chevron-down'" 
          :size="20" 
          class="text-theme-muted transition-transform"
        />
      </div>
    </div>

    <!-- Contenido de filtros -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-show="showFilters" class="border-t border-theme-primary p-3 sm:p-4">
        <div class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
          <!-- Fecha inicio -->
          <div>
            <label class="block text-xs font-medium text-theme-secondary mb-1.5">
              Fecha Inicio
            </label>
            <input
              v-model="localFilters.fecha_inicio"
              type="date"
              class="w-full rounded-lg border border-theme-primary bg-theme-card px-3 py-2 text-sm text-theme-primary focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
            />
          </div>

          <!-- Fecha fin -->
          <div>
            <label class="block text-xs font-medium text-theme-secondary mb-1.5">
              Fecha Fin
            </label>
            <input
              v-model="localFilters.fecha_fin"
              type="date"
              class="w-full rounded-lg border border-theme-primary bg-theme-card px-3 py-2 text-sm text-theme-primary focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
            />
          </div>

          <!-- Jornada -->
          <div>
            <label class="block text-xs font-medium text-theme-secondary mb-1.5">
              Jornada
            </label>
            <select
              v-model="localFilters.jornada_id"
              class="w-full rounded-lg border border-theme-primary bg-theme-card px-3 py-2 text-sm text-theme-primary focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
            >
              <option value="">Todas las jornadas</option>
              <option 
                v-for="jornada in filterOptions.jornadas" 
                :key="jornada.id" 
                :value="jornada.id"
              >
                {{ jornada.nombre }}
              </option>
            </select>
          </div>

          <!-- Programa -->
          <div>
            <label class="block text-xs font-medium text-theme-secondary mb-1.5">
              Programa
            </label>
            <select
              v-model="localFilters.programa_id"
              class="w-full rounded-lg border border-theme-primary bg-theme-card px-3 py-2 text-sm text-theme-primary focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
            >
              <option value="">Todos los programas</option>
              <option 
                v-for="programa in filterOptions.programas" 
                :key="programa.id" 
                :value="programa.id"
              >
                {{ programa.nombre }} {{ programa.ficha ? `(${programa.ficha})` : '' }}
              </option>
            </select>
          </div>

          <!-- Tipo de persona -->
          <div>
            <label class="block text-xs font-medium text-theme-secondary mb-1.5">
              Tipo de Persona
            </label>
            <select
              v-model="localFilters.tipo_persona"
              class="w-full rounded-lg border border-theme-primary bg-theme-card px-3 py-2 text-sm text-theme-primary focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
            >
              <option value="">Todos los tipos</option>
              <option 
                v-for="tipo in filterOptions.tiposPersona" 
                :key="tipo" 
                :value="tipo"
              >
                {{ tipo }}
              </option>
            </select>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="mt-4 flex flex-col sm:flex-row gap-2 sm:justify-end">
          <button
            @click="clearFilters"
            :disabled="!hasActiveFilters()"
            class="flex items-center justify-center gap-2 rounded-lg border border-theme-primary bg-theme-card px-4 py-2 text-sm font-medium text-theme-secondary transition-all hover:bg-theme-secondary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Icon name="x" :size="16" />
            <span>Limpiar</span>
          </button>
          <button
            @click="applyFilters"
            class="flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:from-blue-600 hover:to-blue-700 hover:shadow-md"
          >
            <Icon name="search" :size="16" />
            <span>Aplicar Filtros</span>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>
