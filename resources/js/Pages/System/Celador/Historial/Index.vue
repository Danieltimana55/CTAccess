<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import Icon from '@/Components/Icon.vue'

const props = defineProps({
  registros: Object,
  filters: Object,
  estadisticas: Object,
})

// Filtros de fecha
const fechaDesde = ref(props.filters?.fecha_desde ?? '')
const fechaHasta = ref(props.filters?.fecha_hasta ?? '')
const periodo = ref(props.filters?.periodo ?? '')
const q = ref(props.filters?.q ?? '')
const generandoPDF = ref(false)

// Aplicar filtros cuando cambien
watch([fechaDesde, fechaHasta, periodo, q], ([desde, hasta, per, search]) => {
  router.get(route('system.celador.historial.index'), 
    { 
      fecha_desde: desde, 
      fecha_hasta: hasta, 
      periodo: per,
      q: search
    }, 
    { preserveState: true, replace: true }
  )
}, { debounce: 300 })

// Configurar período predefinido
const setPeriodo = (periodoSeleccionado) => {
  const hoy = new Date()
  let desde, hasta

  switch(periodoSeleccionado) {
    case 'hoy':
      desde = hoy.toISOString().split('T')[0]
      hasta = hoy.toISOString().split('T')[0]
      break
    case 'ayer':
      const ayer = new Date(hoy)
      ayer.setDate(ayer.getDate() - 1)
      desde = ayer.toISOString().split('T')[0]
      hasta = ayer.toISOString().split('T')[0]
      break
    case 'semana':
      const inicioSemana = new Date(hoy)
      inicioSemana.setDate(hoy.getDate() - hoy.getDay())
      desde = inicioSemana.toISOString().split('T')[0]
      hasta = hoy.toISOString().split('T')[0]
      break
    case 'mes':
      desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0]
      hasta = hoy.toISOString().split('T')[0]
      break
    case 'mes_anterior':
      const mesAnterior = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1)
      const finMesAnterior = new Date(hoy.getFullYear(), hoy.getMonth(), 0)
      desde = mesAnterior.toISOString().split('T')[0]
      hasta = finMesAnterior.toISOString().split('T')[0]
      break
  }

  fechaDesde.value = desde
  fechaHasta.value = hasta
  periodo.value = periodoSeleccionado
}

// Generar PDF
const generarPDF = async () => {
  generandoPDF.value = true
  
  try {
    const params = new URLSearchParams({
      fecha_desde: fechaDesde.value || '',
      fecha_hasta: fechaHasta.value || '',
      q: q.value || ''
    })
    
    // Abrir en nueva pestaña
    window.open(
      route('system.celador.historial.export-pdf') + '?' + params.toString(),
      '_blank'
    )
  } catch (error) {
    console.error('Error al generar PDF:', error)
  } finally {
    generandoPDF.value = false
  }
}

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
  const diff = Math.floor((end - start) / 1000 / 60)
  
  if (diff < 60) return `${diff}m`
  const hours = Math.floor(diff / 60)
  const mins = diff % 60
  return `${hours}h ${mins}m`
}

const periodoActivo = computed(() => {
  if (!fechaDesde.value && !fechaHasta.value) return 'Todos los registros'
  if (periodo.value === 'hoy') return 'Hoy'
  if (periodo.value === 'ayer') return 'Ayer'
  if (periodo.value === 'semana') return 'Esta semana'
  if (periodo.value === 'mes') return 'Este mes'
  if (periodo.value === 'mes_anterior') return 'Mes anterior'
  if (fechaDesde.value && fechaHasta.value) {
    return `${fechaDesde.value} al ${fechaHasta.value}`
  }
  return 'Período personalizado'
})
</script>

<template>
  <SystemLayout>
    <Head title="Reportes de Accesos" />

    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Icon name="file-text" :size="20" class="text-[#39A900]" />
          <h2 class="text-lg sm:text-xl font-semibold text-theme-primary">Reportes</h2>
        </div>
      </div>
    </template>

    <div class="py-3 px-3 sm:px-4 lg:px-6">
      <div class="mx-auto max-w-7xl space-y-3 sm:space-y-4">
        
        <!-- Banner de Información -->
        <div class="bg-gradient-to-r from-[#39A900]/10 to-[#39A900]/10 border border-[#39A900]/30 rounded-lg p-3 sm:p-4">
          <div class="flex items-start gap-3">
            <div class="p-2 bg-[#39A900]/20 rounded-lg flex-shrink-0">
              <Icon name="info" :size="18" class="text-[#39A900]" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-sm sm:text-base font-semibold text-theme-primary mb-1">
                Sistema de Reportes
              </h3>
              <p class="text-xs sm:text-sm text-theme-secondary">
                Genera reportes en PDF con estadísticas y movimientos completos.
              </p>
            </div>
          </div>
        </div>

        <!-- Estadísticas del Período -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
          <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-theme-secondary">Total</p>
                <p class="text-xl sm:text-2xl font-bold text-theme-primary mt-0.5">{{ estadisticas?.total ?? 0 }}</p>
              </div>
              <div class="p-2 bg-[#39A900]/10 rounded-lg">
                <Icon name="users" :size="18" class="text-[#39A900]" />
              </div>
            </div>
          </div>

          <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-theme-secondary">Portátil</p>
                <p class="text-xl sm:text-2xl font-bold text-[#39A900] mt-0.5">{{ estadisticas?.con_portatil ?? 0 }}</p>
              </div>
              <div class="p-2 bg-[#39A900]/10 rounded-lg">
                <Icon name="laptop" :size="18" class="text-[#39A900]" />
              </div>
            </div>
          </div>

          <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-theme-secondary">Vehículo</p>
                <p class="text-xl sm:text-2xl font-bold text-[#FDC300] mt-0.5">{{ estadisticas?.con_vehiculo ?? 0 }}</p>
              </div>
              <div class="p-2 bg-[#FDC300]/10 rounded-lg">
                <Icon name="car" :size="18" class="text-[#FDC300]" />
              </div>
            </div>
          </div>

          <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-theme-secondary">Personas</p>
                <p class="text-xl sm:text-2xl font-bold text-purple-600 mt-0.5">{{ estadisticas?.personas_unicas ?? 0 }}</p>
              </div>
              <div class="p-2 bg-purple-600/10 rounded-lg">
                <Icon name="user-check" :size="18" class="text-purple-600" />
              </div>
            </div>
          </div>
        </div>

        <!-- Períodos Predefinidos -->
        <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
          <h3 class="text-xs sm:text-sm font-semibold text-theme-primary mb-2 flex items-center gap-1.5">
            <Icon name="calendar" :size="14" />
            Períodos Rápidos
          </h3>
          <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
            <button 
              @click="setPeriodo('hoy')"
              :class="[
                'px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition-all touch-manipulation',
                periodo === 'hoy' 
                  ? 'bg-[#39A900] text-white shadow-sm' 
                  : 'bg-theme-secondary/10 text-theme-primary hover:bg-theme-secondary/20 active:bg-theme-secondary/30 border border-theme-primary'
              ]"
            >
              Hoy
            </button>
            <button 
              @click="setPeriodo('ayer')"
              :class="[
                'px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition-all touch-manipulation',
                periodo === 'ayer' 
                  ? 'bg-[#39A900] text-white shadow-sm' 
                  : 'bg-theme-secondary/10 text-theme-primary hover:bg-theme-secondary/20 active:bg-theme-secondary/30 border border-theme-primary'
              ]"
            >
              Ayer
            </button>
            <button 
              @click="setPeriodo('semana')"
              :class="[
                'px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition-all touch-manipulation',
                periodo === 'semana' 
                  ? 'bg-[#39A900] text-white shadow-sm' 
                  : 'bg-theme-secondary/10 text-theme-primary hover:bg-theme-secondary/20 active:bg-theme-secondary/30 border border-theme-primary'
              ]"
            >
              Semana
            </button>
            <button 
              @click="setPeriodo('mes')"
              :class="[
                'px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition-all touch-manipulation',
                periodo === 'mes' 
                  ? 'bg-[#39A900] text-white shadow-sm' 
                  : 'bg-theme-secondary/10 text-theme-primary hover:bg-theme-secondary/20 active:bg-theme-secondary/30 border border-theme-primary'
              ]"
            >
              Este Mes
            </button>
            <button 
              @click="setPeriodo('mes_anterior')"
              :class="[
                'px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition-all touch-manipulation col-span-2 sm:col-span-1',
                periodo === 'mes_anterior' 
                  ? 'bg-[#39A900] text-white shadow-sm' 
                  : 'bg-theme-secondary/10 text-theme-primary hover:bg-theme-secondary/20 active:bg-theme-secondary/30 border border-theme-primary'
              ]"
            >
              Mes Anterior
            </button>
          </div>
        </div>

        <!-- Filtros Personalizados -->
        <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
          <h3 class="text-xs sm:text-sm font-semibold text-theme-primary mb-2 flex items-center gap-1.5">
            <Icon name="filter" :size="14" />
            Filtros Personalizados
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
            <div>
              <label class="block text-xs sm:text-sm font-medium text-theme-secondary mb-1">
                Desde
              </label>
              <input 
                v-model="fechaDesde" 
                type="date"
                class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#39A900] transition-all touch-manipulation"
              />
            </div>

            <div>
              <label class="block text-xs sm:text-sm font-medium text-theme-secondary mb-1">
                Hasta
              </label>
              <input 
                v-model="fechaHasta" 
                type="date"
                class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#39A900] transition-all touch-manipulation"
              />
            </div>

            <div>
              <label class="block text-xs sm:text-sm font-medium text-theme-secondary mb-1">
                Buscar
              </label>
              <input 
                v-model="q" 
                type="search"
                inputmode="search"
                placeholder="Nombre o documento"
                class="w-full rounded-lg border-theme-primary bg-theme-primary text-theme-primary px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#39A900] transition-all touch-manipulation"
              />
            </div>

            <div class="flex items-end">
              <button 
                @click="fechaDesde = ''; fechaHasta = ''; periodo = ''; q = ''"
                class="w-full px-3 py-2 bg-theme-secondary text-theme-inverse rounded-lg hover:bg-theme-secondary/80 active:bg-theme-secondary/90 transition-all flex items-center justify-center gap-1.5 text-xs sm:text-sm font-medium touch-manipulation"
              >
                <Icon name="x" :size="14" />
                Limpiar
              </button>
            </div>
          </div>
        </div>

        <!-- Botón de Generar PDF -->
        <div class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2">
            <div class="min-w-0">
              <p class="text-xs sm:text-sm text-theme-secondary truncate">
                <span class="font-semibold text-theme-primary">{{ periodoActivo }}</span>
                <span v-if="estadisticas?.total" class="text-theme-muted ml-1">
                  • {{ estadisticas.total }}
                </span>
              </p>
            </div>
            <button 
              @click="generarPDF"
              :disabled="generandoPDF || !estadisticas?.total"
              class="px-4 py-2 bg-[#39A900] text-white rounded-lg font-medium text-xs sm:text-sm hover:bg-[#39A900]/90 active:bg-[#39A900]/80 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 whitespace-nowrap touch-manipulation"
            >
              <Icon :name="generandoPDF ? 'loader' : 'download'" :size="14" :class="generandoPDF && 'animate-spin'" />
              {{ generandoPDF ? 'Generando...' : 'Descargar PDF' }}
            </button>
          </div>
        </div>

        <!-- Preview de Datos - Vista Móvil -->
        <div class="lg:hidden space-y-2">
          <div class="bg-theme-secondary/5 px-3 py-2 rounded-t-lg">
            <h3 class="text-sm font-semibold text-theme-primary flex items-center gap-1.5">
              <Icon name="eye" :size="16" />
              Vista Previa
            </h3>
          </div>
          
          <div v-for="r in registros.data" :key="r.id" 
            class="bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
            <div class="flex items-start gap-3">
              <div class="w-12 h-12 flex-shrink-0 rounded-full bg-[#39A900] flex items-center justify-center text-white font-semibold">
                {{ (r.persona?.Nombre ?? 'U').charAt(0).toUpperCase() }}
              </div>
              
              <div class="flex-1 min-w-0">
                <div class="mb-2">
                  <p class="font-semibold text-theme-primary text-sm truncate">{{ r.persona?.Nombre ?? '—' }}</p>
                  <p class="text-xs text-theme-secondary">{{ r.persona?.numero_documento ?? '—' }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                  <div>
                    <p class="text-theme-muted">Entrada</p>
                    <p class="font-medium text-theme-primary">{{ formatDate(r.fecha_entrada).split(',')[1] }}</p>
                  </div>
                  <div>
                    <p class="text-theme-muted">Salida</p>
                    <p class="font-medium text-theme-primary">{{ r.fecha_salida ? formatDate(r.fecha_salida).split(',')[1] : '—' }}</p>
                  </div>
                </div>
                
                <div class="flex items-center justify-between pt-2 border-t border-theme-primary">
                  <div class="flex items-center gap-2">
                    <span v-if="r.portatil" class="inline-flex items-center gap-1 text-xs text-[#39A900]">
                      <Icon name="laptop" :size="12" />
                    </span>
                    <span v-if="r.vehiculo" class="inline-flex items-center gap-1 text-xs text-[#FDC300]">
                      <Icon name="car" :size="12" />
                    </span>
                  </div>
                  <span class="text-xs text-theme-muted font-mono">{{ calcularDuracion(r.fecha_entrada, r.fecha_salida) }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="!registros.data?.length" class="bg-theme-card rounded-lg border border-theme-primary p-8 text-center">
            <Icon name="inbox" :size="40" class="mx-auto text-theme-muted mb-2" />
            <p class="text-sm text-theme-secondary font-medium">Sin registros</p>
            <p class="text-xs text-theme-muted mt-1">Ajusta los filtros</p>
          </div>
        </div>

        <!-- Preview de Datos - Vista Desktop -->
        <div class="hidden lg:block bg-theme-card rounded-lg border border-theme-primary overflow-hidden shadow-theme-sm">
          <div class="bg-theme-secondary/5 px-4 py-2 border-b border-theme-primary">
            <h3 class="text-sm font-semibold text-theme-primary flex items-center gap-2">
              <Icon name="eye" :size="16" />
              Vista Previa de Datos
            </h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-theme-primary">
              <thead class="bg-theme-secondary/5">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-theme-secondary">
                    Persona
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
                    Recursos
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-theme-primary">
                <tr v-for="r in registros.data" :key="r.id" class="hover:bg-theme-secondary/5 transition-colors">
                  <td class="px-3 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 rounded-full bg-[#39A900] flex items-center justify-center text-white font-semibold text-xs">
                        {{ (r.persona?.Nombre ?? 'U').charAt(0).toUpperCase() }}
                      </div>
                      <div class="min-w-0">
                        <p class="font-medium text-theme-primary text-sm truncate">{{ r.persona?.Nombre ?? '—' }}</p>
                        <p class="text-xs text-theme-secondary truncate">{{ r.persona?.numero_documento ?? '' }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-[#39A900]/10 text-[#39A900]">
                      {{ r.persona?.tipo_persona ?? '—' }}
                    </span>
                  </td>
                  <td class="px-3 py-3 text-xs">
                    <p class="font-medium text-theme-primary">{{ formatDate(r.fecha_entrada).split(',')[0] }}</p>
                    <p class="text-theme-secondary">{{ formatDate(r.fecha_entrada).split(',')[1] }}</p>
                  </td>
                  <td class="px-3 py-3 text-xs">
                    <div v-if="r.fecha_salida">
                      <p class="font-medium text-theme-primary">{{ formatDate(r.fecha_salida).split(',')[0] }}</p>
                      <p class="text-theme-secondary">{{ formatDate(r.fecha_salida).split(',')[1] }}</p>
                    </div>
                    <span v-else class="text-theme-muted">—</span>
                  </td>
                  <td class="px-3 py-3 text-sm text-theme-secondary">
                    <span class="font-mono">{{ calcularDuracion(r.fecha_entrada, r.fecha_salida) }}</span>
                  </td>
                  <td class="px-3 py-3">
                    <div class="flex items-center gap-2">
                      <span v-if="r.portatil" class="inline-flex items-center gap-1 text-xs text-[#39A900]">
                        <Icon name="laptop" :size="14" />
                      </span>
                      <span v-if="r.vehiculo" class="inline-flex items-center gap-1 text-xs text-[#FDC300]">
                        <Icon name="car" :size="14" />
                      </span>
                      <span v-if="!r.portatil && !r.vehiculo" class="text-theme-muted">—</span>
                    </div>
                  </td>
                </tr>
                <tr v-if="!registros.data?.length">
                  <td colspan="6" class="px-3 py-10 text-center">
                    <Icon name="inbox" :size="40" class="mx-auto text-theme-muted mb-2" />
                    <p class="text-sm text-theme-secondary font-medium">Sin registros</p>
                    <p class="text-xs text-theme-muted mt-1">Ajusta los filtros</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paginación -->
        <div v-if="registros.data?.length" class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-theme-card rounded-lg border border-theme-primary p-3 shadow-theme-sm">
          <div class="text-xs sm:text-sm text-theme-secondary">
            <span class="font-semibold text-theme-primary">{{ registros.from ?? 0 }}</span> - 
            <span class="font-semibold text-theme-primary">{{ registros.to ?? 0 }}</span> de 
            <span class="font-semibold text-theme-primary">{{ registros.total ?? 0 }}</span>
          </div>
          <div class="flex flex-wrap gap-1 justify-center">
            <Link 
              v-for="link in registros.links" 
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
  </SystemLayout>
</template>
