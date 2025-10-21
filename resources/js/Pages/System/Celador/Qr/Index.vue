<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import QrScannerModal from '@/Components/QrScannerModal.vue'
import CedulaModal from '@/Components/CedulaModal.vue'
import IncidenciaModal from '@/Components/IncidenciaModal.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import Icon from '@/Components/Icon.vue'

const props = defineProps({
  estadisticas: Object,
  accesosActivos: Array,
  historialReciente: Array
})

const page = usePage()

// Estado del componente
const scannedCodes = ref({
  persona: null,
  portatil: null,
  vehiculo: null
})

const personaInfo = ref(null)
const isProcessing = ref(false)
const showPersonaInfo = ref(false)
const showConfirmModal = ref(false)
const registroInstantaneo = ref(false)
const refreshInterval = ref(null)
const notification = ref(null)

// Modales
const showQrScannerModal = ref(false)
const showCedulaModal = ref(false)
const showIncidenciaModal = ref(false)
const qrScannerModalRef = ref(null)
const cedulaModalRef = ref(null)

// Estado para incidencia
const incidenciaData = ref(null)

// Computadas
const canProcess = computed(() => {
  return scannedCodes.value.persona && !isProcessing.value
})

const estadisticasActuales = ref(props.estadisticas)
const accesosActivosActuales = ref(props.accesosActivos)
const historialRecienteActual = ref(props.historialReciente)

// Métodos de modales
const openQrScanner = () => {
  showQrScannerModal.value = true
}

const closeQrScanner = () => {
  showQrScannerModal.value = false
}

const openCedulaModal = () => {
  showCedulaModal.value = true
}

const closeCedulaModal = () => {
  showCedulaModal.value = false
}

const handleAccesoRegistrado = (data) => {
  // El modal ya registró el acceso, solo necesitamos actualizar la UI
  console.log('Acceso registrado desde modal:', data)
  
  // Recargar la página para actualizar las estadísticas y el historial
  router.reload({
    only: ['accesosActivos', 'historial', 'estadisticas']
  })
  
  // El modal se encarga de limpiarse y quedar abierto para el siguiente registro
}

// Métodos de escaneo
const handleQrScanned = async (qrEvent) => {
  const { type, data } = qrEvent

  if (type === 'persona') {
    // 🔥 MISMO FLUJO para QR escaneado Y entrada manual
    scannedCodes.value.persona = data
    await buscarPersona(data)
  } else if (type === 'portatil') {
    scannedCodes.value.portatil = data
    // Si es salida y hay portátil esperado, verificar coincidencia
    if (personaInfo.value?.acceso_activo?.requiere_verificacion_portatil) {
      await verificarPortatil(data)
    }
  }
  // vehiculo eliminado - ya no se verifica
}

const buscarPersona = async (qrPersona) => {
  try {
    const response = await fetch(route('system.celador.qr.buscar-persona'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token
      },
      body: JSON.stringify({ qr_persona: qrPersona })
    })
    
    const result = await response.json()
    
    if (response.ok) {
      personaInfo.value = result
      showPersonaInfo.value = true
      
      // Establecer el QR de persona para procesamiento
      scannedCodes.value.persona = qrPersona
      
      // 🔥 INFORMACIÓN IMPORTANTE PARA EL CELADOR
      const esEntrada = result.es_entrada
      const esSalida = result.es_salida
      
      // Mostrar información al celador
      let mensaje = `${result.persona.Nombre} - ${result.mensaje_accion}`
      
      // Agregar info de portátil detectado automáticamente (vehiculo eliminado)
      if (esEntrada) {
        const elementos = []
        if (result.tiene_portatil) {
          elementos.push(`✓ Portátil: ${result.portatil_asociado.marca} ${result.portatil_asociado.modelo}`)
        }
        
        if (elementos.length > 0) {
          showNotification('info', `${mensaje}\n${elementos.join('\n')}`)
        }
      }
      
      // Si es SALIDA, verificar si necesita escanear portátil
      if (esSalida && result.acceso_activo) {
        if (result.acceso_activo.requiere_verificacion_portatil) {
          showNotification('warning', `SALIDA - Verificación requerida:\n� Debe escanear QR del portátil: ${result.acceso_activo.portatil_entrada.serial}`)
          // NO procesar automáticamente - debe escanear portátil
          showConfirmModal.value = true
          return
        }
      }
      
      // Si está activado el registro instantáneo Y no requiere verificaciones, procesar directamente
      if (registroInstantaneo.value && (!esSalida || !result.acceso_activo?.requiere_verificacion_portatil)) {
        await procesarAcceso()
      } else {
        showConfirmModal.value = true
      }
    } else {
      throw new Error(result.message || 'Persona no encontrada')
    }
  } catch (error) {
    console.error('Error al buscar persona:', error)
    showNotification('error', error.message || 'Persona no encontrada')
  }
}

// Nueva función para verificar portátil y detectar inconsistencias
const verificarPortatil = async (qrPortatil) => {
  if (!personaInfo.value?.acceso_activo?.portatil_entrada) {
    return
  }

  const serialEsperado = personaInfo.value.acceso_activo.portatil_entrada.serial
  const serialVerificado = qrPortatil.replace('PORTATIL_', '')

  if (serialEsperado !== serialVerificado) {
    // 🚨 NO COINCIDE - Abrir modal de incidencia
    incidenciaData.value = {
      errorMessage: `El portátil escaneado NO coincide con el registrado en la entrada`,
      accesoInfo: {
        persona: personaInfo.value.persona.Nombre,
        documento: personaInfo.value.persona.documento,
        equipoEsperado: `Serial: ${serialEsperado}`,
        equipoVerificado: `Serial: ${serialVerificado}`
      }
    }
    showIncidenciaModal.value = true
  }
}

// buscarPersonaPorCedula eliminada - ahora todo usa buscarPersona con QR virtual

const procesarAcceso = async (descripcionIncidencia = null) => {
  if (!canProcess.value) return

  isProcessing.value = true

  try {
    const payload = {
      qr_persona: scannedCodes.value.persona,
      qr_portatil: scannedCodes.value.portatil,
    }

    // Si hay descripción de incidencia, incluirla
    if (descripcionIncidencia) {
      payload.descripcion_incidencia = descripcionIncidencia
    }

    router.post(route('system.celador.qr.registrar'), payload, {
      onSuccess: (page) => {
        limpiarCodigos()
        showNotification('success', descripcionIncidencia ? 'Salida registrada con incidencia' : 'Acceso registrado correctamente')
        refreshData()
      },
      onError: (errors) => {
        console.error('Error al registrar acceso:', errors)
        const errorMessage = Object.values(errors)[0] || 'Error al procesar el acceso'
        showNotification('error', errorMessage)
      },
      onFinish: () => {
        isProcessing.value = false
      }
    })
  } catch (error) {
    console.error('Error al procesar acceso:', error)
    showNotification('error', error.message || 'Error al procesar el acceso')
    isProcessing.value = false
  }
}

// Handler para incidencias detectadas desde los modales hijos
const handleIncidenciaDetectada = (incidenciaInfo) => {
  // Cerrar los modales de escaneo
  showQrScannerModal.value = false
  showCedulaModal.value = false
  
  // Preparar datos de la incidencia
  incidenciaData.value = {
    errorMessage: incidenciaInfo.errorMessage,
    accesoInfo: incidenciaInfo.accesoInfo,
    datosRegistro: incidenciaInfo.datosRegistro // Datos para el registro
  }
  
  // Abrir modal de incidencia
  showIncidenciaModal.value = true
}

// Handler para confirmar registro con incidencia
const handleIncidenciaConfirmada = (data) => {
  showIncidenciaModal.value = false
  
  // Usar los datos de registro guardados
  if (incidenciaData.value?.datosRegistro) {
    const payload = {
      ...incidenciaData.value.datosRegistro,
      descripcion_incidencia: data.descripcion
    }
    
    isProcessing.value = true
    
    router.post(route('system.celador.qr.registrar'), payload, {
      onSuccess: (page) => {
        limpiarCodigos()
        showNotification('warning', 'Salida registrada con incidencia')
        refreshData()
      },
      onError: (errors) => {
        console.error('Error al registrar acceso:', errors)
        const errorMessage = Object.values(errors)[0] || 'Error al procesar el acceso'
        showNotification('error', errorMessage)
      },
      onFinish: () => {
        isProcessing.value = false
      }
    })
  }
}

// Handler para cerrar modal de incidencia
const closeIncidenciaModal = () => {
  showIncidenciaModal.value = false
  incidenciaData.value = null
}

const limpiarCodigos = () => {
  scannedCodes.value = {
    persona: null,
    portatil: null,
    vehiculo: null
  }
  personaInfo.value = null
  showPersonaInfo.value = false
  showConfirmModal.value = false
  showIncidenciaModal.value = false
  incidenciaData.value = null
}

const cerrarModal = () => {
  showConfirmModal.value = false
}

const confirmarAcceso = async () => {
  showConfirmModal.value = false
  await procesarAcceso()
}

// Función para registro directo sin modal (opcional)
const registrarDirecto = async () => {
  if (!canProcess.value) return
  await procesarAcceso()
}

const refreshData = async () => {
  try {
    const statsResponse = await fetch(route('system.celador.qr.estadisticas'))
    if (statsResponse.ok) {
      estadisticasActuales.value = await statsResponse.json()
    }

    const activosResponse = await fetch(route('system.celador.qr.accesos-activos'))
    if (activosResponse.ok) {
      accesosActivosActuales.value = await activosResponse.json()
    }

    const historialResponse = await fetch(route('system.celador.qr.historial'))
    if (historialResponse.ok) {
      historialRecienteActual.value = await historialResponse.json()
    }
  } catch (error) {
    console.error('Error al actualizar datos:', error)
  }
}

const showNotification = (type, message, data = null) => {
  notification.value = {
    type,
    message,
    data,
    timestamp: Date.now()
  }

  setTimeout(() => {
    if (notification.value && notification.value.timestamp === notification.value.timestamp) {
      notification.value = null
    }
  }, 5000)
}

const closeNotification = () => {
  notification.value = null
}

// Funciones de utilidad

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatDuration = (entrada, salida = null) => {
  const start = new Date(entrada)
  const end = salida ? new Date(salida) : new Date()
  const diff = Math.floor((end - start) / 1000 / 60)
  
  if (diff < 60) return `${diff}m`
  const hours = Math.floor(diff / 60)
  const minutes = diff % 60
  return `${hours}h ${minutes}m`
}

const calcularMinutos = (entrada) => {
  const start = new Date(entrada)
  const end = new Date()
  return Math.floor((end - start) / 1000 / 60)
}

onMounted(() => {
  refreshInterval.value = setInterval(refreshData, 30000)
  
  if (page.props.flash?.success) {
    showNotification('success', page.props.flash.success.mensaje, page.props.flash.success)
  }
  if (page.props.flash?.warning) {
    showNotification('warning', page.props.flash.warning.mensaje, page.props.flash.warning)
  }
  if (page.props.flash?.error) {
    showNotification('error', page.props.flash.error)
  }
})

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})
</script>

<template>
  <SystemLayout>
    <Head title="Verificación QR" />

    <template #header>
      <div class="flex items-center justify-between gap-2">
        <h2 class="text-base sm:text-lg font-semibold text-theme-primary">Verificación QR</h2>
        
        <!-- Botones de control compactos en header -->
        <div class="flex items-center gap-1.5">
          <!-- Toggle instantáneo -->
          <label class="flex items-center gap-1 text-[10px] text-theme-secondary">
            <input 
              type="checkbox" 
              v-model="registroInstantaneo"
              class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-3 h-3"
            >
            <span class="hidden sm:inline">Inst</span>
          </label>
          
          <!-- Botón Escanear QR -->
          <button
            @click="openQrScanner"
            class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white rounded-md px-2 py-1 text-xs font-medium touch-manipulation transition-colors"
          >
            <Icon name="qr-code" :size="14" />
            <span class="hidden sm:inline">Escanear</span>
          </button>

          <!-- Botón Manual -->
          <button
            @click="openCedulaModal"
            class="flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md px-2 py-1 text-xs font-medium touch-manipulation transition-colors"
          >
            <Icon name="edit" :size="14" />
            <span class="hidden sm:inline">Manual</span>
          </button>
          
          <!-- Botón limpiar -->
          <button 
            @click="limpiarCodigos"
            class="p-1 text-theme-muted hover:text-theme-primary hover:bg-theme-secondary rounded transition-colors"
            title="Limpiar"
          >
            <Icon name="x" :size="14" />
          </button>
        </div>
        
        <div class="hidden lg:flex items-center space-x-4">
          <!-- Estado de conexión -->
          <div class="flex items-center space-x-2 text-sm">
            <svg 
              :class="{
                'text-green-500': isOnline,
                'text-red-500': !isOnline
              }" 
              class="w-4 h-4" 
              fill="currentColor" 
              viewBox="0 0 20 20"
            >
              <circle cx="10" cy="10" r="3"/>
            </svg>
            <span :class="{ 'text-theme-secondary': isOnline, 'text-red-600': !isOnline }">
              {{ isOnline ? 'En línea' : 'Sin conexión' }}
            </span>
          </div>

          <!-- Indicador de datos pendientes -->
          <div v-if="hasPendingSync" class="flex items-center space-x-2 text-sm text-orange-600">
            <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
            </svg>
            <span>{{ syncStatus.pendingItems }} pendiente(s)</span>
          </div>

          <!-- Botón de sincronización manual -->
          <button
            v-if="isOnline && hasPendingSync"
            @click="handleSyncData"
            :disabled="syncStatus.inProgress"
            class="flex items-center space-x-1 px-3 py-1 text-xs bg-theme-tertiary text-blue-700 rounded-full hover:bg-theme-secondary disabled:opacity-50"
          >
            <svg 
              :class="{ 'animate-spin': syncStatus.inProgress }"
              class="w-3 h-3" 
              fill="currentColor" 
              viewBox="0 0 20 20"
            >
              <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
            </svg>
            <span>{{ syncStatus.inProgress ? 'Sincronizando...' : 'Sincronizar' }}</span>
          </button>
        </div>
      </div>
    </template>

    <div class="py-2">
      <div class="mx-auto max-w-7xl space-y-2 px-2 sm:px-4">
        
        <!-- Notificaciones -->
        <div v-if="notification" class="fixed top-2 right-2 z-50 max-w-sm">
          <div 
            :class="{
              'bg-green-50 border-green-200 text-green-800': notification.type === 'success',
              'bg-yellow-50 border-yellow-200 text-yellow-800': notification.type === 'warning',
              'bg-red-50 border-red-200 text-red-800': notification.type === 'error'
            }"
            class="border rounded-lg p-2 shadow-lg text-xs"
          >
            <div class="flex items-start gap-2">
              <Icon v-if="notification.type === 'success'" name="check-circle" :size="16" class="text-green-400 flex-shrink-0" />
              <Icon v-else-if="notification.type === 'warning'" name="alert-triangle" :size="16" class="text-yellow-400 flex-shrink-0" />
              <Icon v-else name="x-circle" :size="16" class="text-red-400 flex-shrink-0" />
              <div class="flex-1 min-w-0">
                <p class="text-xs font-medium">{{ notification.message }}</p>
                <div v-if="notification.data" class="mt-1 text-xs opacity-75">
                  <div v-if="notification.data.persona">{{ notification.data.persona }}</div>
                </div>
              </div>
              <button @click="closeNotification" class="flex-shrink-0">
                <Icon name="x" :size="14" />
              </button>
            </div>
          </div>
        </div>

        <!-- Estadísticas ultra-compactas -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-1.5">
          <div class="bg-theme-card border border-theme-primary rounded-lg shadow-sm p-1.5">
            <div class="flex items-center gap-1.5">
              <div class="w-7 h-7 rounded-md bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                <Icon name="log-in" :size="14" class="text-green-600 dark:text-green-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[10px] text-theme-secondary leading-tight">Entradas</p>
                <p class="text-base font-bold text-theme-primary leading-tight">{{ estadisticasActuales?.total_entradas || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="bg-theme-card border border-theme-primary rounded-lg shadow-sm p-1.5">
            <div class="flex items-center gap-1.5">
              <div class="w-7 h-7 rounded-md bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                <Icon name="log-out" :size="14" class="text-red-600 dark:text-red-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[10px] text-theme-secondary leading-tight">Salidas</p>
                <p class="text-base font-bold text-theme-primary leading-tight">{{ estadisticasActuales?.total_salidas || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="bg-theme-card border border-theme-primary rounded-lg shadow-sm p-1.5">
            <div class="flex items-center gap-1.5">
              <div class="w-7 h-7 rounded-md bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                <Icon name="users" :size="14" class="text-blue-600 dark:text-blue-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[10px] text-theme-secondary leading-tight">Activos</p>
                <p class="text-base font-bold text-theme-primary leading-tight">{{ estadisticasActuales?.activos_actuales || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="bg-theme-card border border-theme-primary rounded-lg shadow-sm p-1.5">
            <div class="flex items-center gap-1.5">
              <div class="w-7 h-7 rounded-md bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center flex-shrink-0">
                <Icon name="laptop" :size="14" class="text-cyan-600 dark:text-cyan-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[10px] text-theme-secondary leading-tight">Portátiles</p>
                <p class="text-base font-bold text-theme-primary leading-tight">{{ estadisticasActuales?.con_portatil || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="bg-theme-card border border-theme-primary rounded-lg shadow-sm p-1.5">
            <div class="flex items-center gap-1.5">
              <div class="w-7 h-7 rounded-md bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0">
                <Icon name="car" :size="14" class="text-yellow-600 dark:text-yellow-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[10px] text-theme-secondary leading-tight">Vehículos</p>
                <p class="text-base font-bold text-theme-primary leading-tight">{{ estadisticasActuales?.con_vehiculo || 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Códigos escaneados (si hay) -->
        <div v-if="scannedCodes.persona || scannedCodes.portatil" class="bg-theme-card border border-theme-primary rounded-lg shadow-sm p-2">
          <div class="flex flex-wrap items-center gap-1.5">
            <div v-if="scannedCodes.persona" class="flex items-center gap-1 px-2 py-1 bg-green-50 dark:bg-green-900/20 rounded text-[10px]">
              <Icon name="user" :size="10" class="text-green-600 dark:text-green-400" />
              <span class="font-medium text-green-800 dark:text-green-300">Persona</span>
            </div>

            <div v-if="scannedCodes.portatil" class="flex items-center gap-1 px-2 py-1 bg-blue-50 dark:bg-blue-900/20 rounded text-[10px]">
              <Icon name="laptop" :size="10" class="text-blue-600 dark:text-blue-400" />
              <span class="font-medium text-blue-800 dark:text-blue-300">Portátil</span>
            </div>

            <button
              @click="procesarAcceso"
              :disabled="!canProcess"
              class="ml-auto flex items-center gap-1 px-3 py-1 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-xs font-medium touch-manipulation transition-colors"
            >
              <Icon v-if="isProcessing" name="loader" :size="12" class="animate-spin" />
              <Icon v-else name="check-circle" :size="12" />
              <span>{{ isProcessing ? 'Procesando...' : 'Registrar' }}</span>
            </button>
          </div>
        </div>

        <!-- Grid principal optimizado -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
          <!-- Info de persona + Accesos activos -->
          <div class="space-y-2">
            <!-- Info de persona escaneada -->
            <div v-if="showPersonaInfo && personaInfo" class="bg-theme-card border border-theme-primary rounded-lg shadow-sm p-2">
              <h3 class="text-xs font-semibold text-theme-primary mb-1">Info Persona</h3>
              
              <div class="space-y-0.5 text-[10px]">
                <div class="flex justify-between">
                  <span class="text-theme-secondary">Nombre:</span>
                  <span class="font-medium text-theme-primary truncate ml-1">{{ personaInfo.persona.Nombre }}</span>
                </div>
                
                <div class="flex justify-between">
                  <span class="text-theme-secondary">Doc:</span>
                  <span class="font-medium text-theme-primary">{{ personaInfo.persona.documento }}</span>
                </div>
                
                <div class="flex justify-between">
                  <span class="text-theme-secondary">Tipo:</span>
                  <span class="font-medium text-theme-primary">{{ personaInfo.persona.TipoPersona }}</span>
                </div>

                <div v-if="personaInfo.tiene_acceso_activo" class="p-1 bg-yellow-50 dark:bg-yellow-900/20 rounded text-[10px] mt-1">
                  <p class="font-medium text-yellow-800 dark:text-yellow-300">⚠️ Acceso activo</p>
                </div>
              </div>
            </div>

            <!-- Accesos activos con gráficos -->
            <div class="bg-theme-card border border-theme-primary rounded-lg shadow-sm p-2">
              <h3 class="text-xs font-semibold text-theme-primary mb-1.5">Activos ({{ accesosActivosActuales?.length || 0 }})</h3>
              
              <div v-if="accesosActivosActuales?.length" class="space-y-1 max-h-48 overflow-y-auto">
                <div v-for="acceso in accesosActivosActuales.slice(0, 8)" :key="acceso.id" class="border border-theme-primary rounded p-1.5 hover:bg-theme-secondary transition-colors">
                  <div class="flex items-center justify-between mb-0.5">
                    <p class="font-medium text-theme-primary text-[10px] truncate flex-1">{{ acceso.persona?.Nombre }}</p>
                    <div class="flex gap-0.5 flex-shrink-0 ml-1 text-[10px]">
                      <span v-if="acceso.portatil" title="Portátil">💻</span>
                      <span v-if="acceso.vehiculo" title="Vehículo">🚗</span>
                    </div>
                  </div>
                  <div class="flex items-center gap-1 text-[10px] text-theme-secondary mb-0.5">
                    <Icon name="clock" :size="9" />
                    <span>{{ formatTime(acceso.fecha_entrada) }}</span>
                    <span class="ml-auto font-medium">{{ formatDuration(acceso.fecha_entrada) }}</span>
                  </div>
                  <!-- Barra de progreso visual de duración -->
                  <div class="w-full bg-theme-secondary rounded-full h-0.5">
                    <div 
                      class="h-0.5 rounded-full transition-all"
                      :class="{
                        'bg-green-500': calcularMinutos(acceso.fecha_entrada) < 60,
                        'bg-yellow-500': calcularMinutos(acceso.fecha_entrada) >= 60 && calcularMinutos(acceso.fecha_entrada) < 180,
                        'bg-orange-500': calcularMinutos(acceso.fecha_entrada) >= 180 && calcularMinutos(acceso.fecha_entrada) < 360,
                        'bg-red-500': calcularMinutos(acceso.fecha_entrada) >= 360
                      }"
                      :style="{ width: Math.min(100, (calcularMinutos(acceso.fecha_entrada) / 360) * 100) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
              
              <div v-else class="text-center text-theme-secondary py-2 text-[10px]">
                <Icon name="users" :size="16" class="mx-auto mb-0.5 opacity-50" />
                <p>Sin accesos activos</p>
              </div>
            </div>
          </div>

          <!-- Historial con filtros -->
          <div class="bg-theme-card border border-theme-primary rounded-lg shadow-sm">
            <div class="p-2 border-b border-theme-primary">
              <div class="flex items-center justify-between mb-1">
                <h3 class="text-xs font-semibold text-theme-primary">Historial del Día</h3>
                <span class="text-[10px] text-theme-secondary">{{ historialRecienteActual?.length || 0 }}</span>
              </div>
              
              <!-- Filtros rápidos -->
              <div class="flex gap-1">
                <button class="px-2 py-0.5 text-[10px] rounded bg-theme-secondary hover:bg-theme-primary transition-colors border border-theme-primary">
                  <Icon name="users" :size="10" class="inline mr-0.5" />
                  Todos
                </button>
                <button class="px-2 py-0.5 text-[10px] rounded hover:bg-theme-secondary transition-colors border border-theme-primary">
                  <Icon name="log-in" :size="10" class="inline mr-0.5 text-green-600" />
                  Activos
                </button>
                <button class="px-2 py-0.5 text-[10px] rounded hover:bg-theme-secondary transition-colors border border-theme-primary">
                  <Icon name="check" :size="10" class="inline mr-0.5 text-gray-600" />
                  Finalizados
                </button>
              </div>
            </div>
          
          <div class="overflow-x-auto max-h-64">
            <table class="min-w-full divide-y divide-theme-primary text-[10px]">
              <thead class="bg-theme-secondary sticky top-0">
                <tr>
                  <th class="px-2 py-1 text-left font-medium text-theme-secondary uppercase">Persona</th>
                  <th class="px-2 py-1 text-left font-medium text-theme-secondary uppercase">Entrada</th>
                  <th class="px-2 py-1 text-left font-medium text-theme-secondary uppercase">Salida</th>
                  <th class="px-2 py-1 text-left font-medium text-theme-secondary uppercase">Dur.</th>
                  <th class="px-2 py-1 text-center font-medium text-theme-secondary uppercase">Rec.</th>
                  <th class="px-2 py-1 text-left font-medium text-theme-secondary uppercase">Estado</th>
                </tr>
              </thead>
              <tbody class="bg-theme-card divide-y divide-theme-primary">
                <tr v-for="acceso in historialRecienteActual?.slice(0, 10)" :key="acceso.id" class="hover:bg-theme-secondary transition-colors">
                  <td class="px-3 py-2">
                    <div class="font-medium text-theme-primary truncate max-w-xs">{{ acceso.persona?.Nombre }}</div>
                    <div class="text-theme-secondary">{{ acceso.persona?.documento }}</div>
                  </td>
                  <td class="px-3 py-2 text-theme-primary whitespace-nowrap">
                    {{ formatTime(acceso.fecha_entrada) }}
                  </td>
                  <td class="px-3 py-2 text-theme-primary whitespace-nowrap">
                    {{ acceso.fecha_salida ? formatTime(acceso.fecha_salida) : '-' }}
                  </td>
                  <td class="px-3 py-2 text-theme-primary whitespace-nowrap">
                    {{ formatDuration(acceso.fecha_entrada, acceso.fecha_salida) }}
                  </td>
                  <td class="px-3 py-2 text-center">
                    <div class="flex gap-1 justify-center">
                      <span v-if="acceso.portatil" title="Portátil">💻</span>
                      <span v-if="acceso.vehiculo" title="Vehículo">🚗</span>
                      <span v-if="!acceso.portatil && !acceso.vehiculo" class="text-theme-secondary">-</span>
                    </div>
                  </td>
                  <td class="px-3 py-2">
                    <span 
                      :class="{
                        'bg-green-100 text-green-800': acceso.estado === 'activo',
                        'bg-gray-100 text-gray-800': acceso.estado === 'finalizado',
                        'bg-red-100 text-red-800': acceso.estado === 'incidencia'
                      }"
                      class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full"
                    >
                      {{ acceso.estado === 'activo' ? 'Activo' : acceso.estado === 'finalizado' ? 'Fin' : 'Inc' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div v-if="!historialRecienteActual?.length" class="text-center py-4 text-theme-secondary text-xs">
            <Icon name="file-text" :size="24" class="mx-auto mb-1 opacity-50" />
            <p>Sin registros del día</p>
          </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmación Compacto -->
    <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="cerrarModal"></div>

      <!-- Contenido del modal -->
      <div class="relative bg-theme-card border-2 border-theme-primary rounded-xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="p-4">
          <!-- Header con icono -->
          <div class="flex items-center gap-3 mb-3">
            <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-blue-100">
              <Icon name="user" :size="20" class="text-blue-600" />
            </div>
            <h3 class="text-base font-bold text-theme-primary">Confirmar Acceso</h3>
          </div>

          <div v-if="personaInfo" class="space-y-3">
            <!-- Información de la persona -->
            <div class="bg-theme-secondary border border-theme-primary rounded-lg p-3">
              <div class="text-center">
                <h4 class="text-base font-bold text-theme-primary">{{ personaInfo.persona?.Nombre }}</h4>
                <p class="text-xs text-theme-secondary mt-1">{{ personaInfo.persona?.TipoPersona }} • {{ personaInfo.persona?.documento }}</p>
              </div>
            </div>

            <!-- Estado del acceso -->
            <div class="text-center p-3 rounded-lg" :class="personaInfo.tiene_acceso_activo ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800'">
              <div class="text-3xl mb-1">
                {{ personaInfo.tiene_acceso_activo ? '🚪➡️' : '🚪⬅️' }}
              </div>
              <p class="font-bold text-sm">
                {{ personaInfo.tiene_acceso_activo ? 'REGISTRAR SALIDA' : 'REGISTRAR ENTRADA' }}
              </p>
            </div>

            <!-- Recursos adicionales -->
            <div v-if="scannedCodes.portatil" class="bg-blue-50 rounded-lg p-2">
              <h5 class="text-xs font-semibold text-blue-900 mb-1">Equipo a verificar:</h5>
              <div class="space-y-1 text-xs">
                <div class="flex items-center text-blue-700">
                  <Icon name="laptop" :size="14" class="mr-1" />
                  Portátil verificado
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="bg-theme-secondary px-4 py-3 flex gap-2 border-t border-theme-primary">
          <button
            @click="cerrarModal"
            :disabled="isProcessing"
            type="button"
            class="flex-1 px-4 py-2 bg-theme-card border border-theme-primary text-sm font-medium text-theme-primary hover:bg-theme-tertiary disabled:opacity-50 transition-colors rounded-lg touch-manipulation"
          >
            Cancelar
          </button>
          <button
            @click="confirmarAcceso"
            :disabled="isProcessing"
            type="button"
            class="flex-1 px-4 py-2 text-sm font-bold text-white disabled:opacity-50 disabled:cursor-not-allowed rounded-lg touch-manipulation active:scale-95 transition-all"
            :class="personaInfo?.tiene_acceso_activo ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
          >
            <Icon v-if="isProcessing" name="loader" :size="14" class="inline animate-spin mr-1" />
            {{ isProcessing ? 'Procesando...' : (personaInfo?.tiene_acceso_activo ? 'SALIDA' : 'ENTRADA') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modales -->
    <QrScannerModal
      :show="showQrScannerModal"
      @close="closeQrScanner"
      @acceso-registrado="handleAccesoRegistrado"
      @incidencia-detectada="handleIncidenciaDetectada"
      ref="qrScannerModalRef"
    />

    <CedulaModal
      :show="showCedulaModal"
      @close="closeCedulaModal"
      @acceso-registrado="handleAccesoRegistrado"
      @incidencia-detectada="handleIncidenciaDetectada"
      ref="cedulaModalRef"
    />

    <!-- Modal de Incidencia -->
    <IncidenciaModal
      v-if="incidenciaData"
      :show="showIncidenciaModal"
      :error-message="incidenciaData.errorMessage"
      :acceso-info="incidenciaData.accesoInfo"
      @close="closeIncidenciaModal"
      @confirmar="handleIncidenciaConfirmada"
    />
  </SystemLayout>
</template>
