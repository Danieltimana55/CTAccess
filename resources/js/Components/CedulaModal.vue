<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="handleClose"
      >
        <div class="fixed inset-0 bg-black/50 transition-opacity"></div>
        <div class="flex min-h-screen items-center justify-center p-4">
          <div
            class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white transition-all"
            @click.stop
          >
            <div class="bg-emerald-600 px-4 py-3">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Entrada Manual por Cédula</h3>
                <button
                  @click="handleClose"
                  class="flex h-6 w-6 items-center justify-center rounded text-white/80 hover:text-white hover:bg-white/20 transition-colors"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
            <div class="p-4">
              <form @submit.prevent="handleSearch" v-if="!personaInfo">
                <div class="space-y-2">
                  <label class="block text-xs font-medium text-gray-700">Número de Cédula</label>
                  <div class="relative">
                    <input
                      ref="cedulaInput"
                      v-model="cedula"
                      type="text"
                      inputmode="numeric"
                      pattern="[0-9]*"
                      placeholder="Ej: 123456789"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                      :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/20': error }"
                      @input="clearError"
                    />
                    <div v-if="cedula" class="absolute right-2 top-1/2 -translate-y-1/2">
                      <button
                        type="button"
                        @click="clearInput"
                        class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300"
                      >
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                  <Transition name="error">
                    <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
                  </Transition>
                  <p class="text-[10px] text-gray-500">Solo números, sin espacios</p>
                </div>
                <div class="mt-3 flex space-x-2">
                  <button
                    type="button"
                    @click="handleClose"
                    :disabled="searching"
                    class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    :disabled="!cedula.trim() || searching"
                    class="flex-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700 disabled:opacity-50"
                  >
                    <span v-if="searching">Buscando...</span>
                    <span v-else>Buscar</span>
                  </button>
                </div>
              </form>
              <div v-else class="space-y-2">
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-2">
                  <div class="flex items-start space-x-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-white flex-shrink-0">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-xs font-bold text-emerald-900 truncate">{{ personaInfo?.persona?.Nombre || 'Sin nombre' }}</h4>
                      <p class="text-[10px] text-emerald-700">Cédula: {{ personaInfo?.persona?.documento || cedula }}</p>
                      <p class="text-[10px] text-emerald-700">Tipo: {{ personaInfo?.persona?.TipoPersona || 'N/A' }}</p>
                    </div>
                  </div>
                </div>
                <div v-if="personaInfo?.tiene_portatil" class="rounded-lg bg-blue-50 border border-blue-200 p-2">
                  <div class="flex items-center space-x-2">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white flex-shrink-0">
                      <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-[10px] font-semibold text-blue-900 truncate">
                        {{ personaInfo?.portatil_asociado?.marca || '' }} {{ personaInfo?.portatil_asociado?.modelo || '' }}
                      </p>
                      <p class="text-[10px] text-blue-700">
                        Serial: {{ personaInfo?.portatil_asociado?.serial || 'N/A' }}
                      </p>
                    </div>
                  </div>
                  <div v-if="personaInfo?.es_salida && !verificandoEquipo" class="mt-1.5">
                    <button
                      type="button"
                      @click="iniciarVerificacion('portatil')"
                      class="w-full rounded bg-blue-600 px-2 py-1 text-[10px] font-semibold text-white hover:bg-blue-700"
                    >
                      Verificar
                    </button>
                  </div>
                </div>
                <div v-if="!personaInfo?.tiene_portatil" class="rounded-lg bg-gray-50 border border-gray-200 p-2 text-center">
                  <p class="text-[10px] text-gray-600">Sin portátil</p>
                </div>
                <div class="rounded-lg border p-1.5" :class="{
                  'bg-green-50 border-green-300': personaInfo?.es_entrada,
                  'bg-yellow-50 border-yellow-300': personaInfo?.es_salida
                }">
                  <p class="text-[10px] font-bold text-center" :class="{
                    'text-green-800': personaInfo?.es_entrada,
                    'text-yellow-800': personaInfo?.es_salida
                  }">
                    {{ personaInfo?.mensaje_accion || 'Acceso' }}
                  </p>
                </div>
                <!-- Botones de acción -->
                <div class="flex space-x-2 pt-1">
                  <button
                    type="button"
                    @click="resetSearch"
                    :disabled="confirming || verificandoEquipo"
                    class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                  >
                    Nuevo
                  </button>
                  <button
                    v-if="personaInfo?.es_entrada"
                    type="button"
                    @click="confirmAcceso(false)"
                    :disabled="confirming || verificandoEquipo"
                    class="flex-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700 disabled:opacity-50"
                  >
                    {{ confirming ? 'Registrando...' : 'Confirmar' }}
                  </button>
                  <button
                    v-else-if="personaInfo?.es_salida"
                    type="button"
                    @click="confiarSinVerificar"
                    :disabled="confirming || verificandoEquipo"
                    class="flex-1 rounded-lg bg-yellow-600 px-3 py-2 text-xs font-bold text-white hover:bg-yellow-700 disabled:opacity-50"
                  >
                    {{ confirming ? 'Registrando...' : 'Confiar y Registrar' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal de Verificación de Equipo (overlay sobre el modal principal) -->
        <Transition name="scanner">
          <div
            v-if="verificandoEquipo"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4"
            @click.self="cancelarVerificacion"
          >
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white">
              <!-- Header -->
              <div class="px-6 py-4" :class="{
                'bg-blue-600': tipoEquipoVerificar === 'portatil',
                'bg-orange-600': tipoEquipoVerificar === 'vehiculo'
              }">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-lg font-bold text-white">
                      {{ tipoEquipoVerificar === 'portatil' ? '💻 Verificar Portátil' : '🚗 Verificar Vehículo' }}
                    </h3>
                    <p class="text-xs text-white/80">Escanea el QR del equipo</p>
                  </div>
                  <button
                    @click="cancelarVerificacion"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-white transition-all hover:bg-white/30"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Visor de Cámara -->
              <div class="relative aspect-square bg-gray-900">
                <video
                  ref="videoElement"
                  autoplay
                  playsinline
                  class="h-full w-full object-cover"
                ></video>
                <canvas ref="canvasElement" class="hidden"></canvas>

                <!-- Overlay de escaneo -->
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="relative h-48 w-48">
                    <!-- Esquinas del marco -->
                    <div class="absolute left-0 top-0 h-8 w-8 border-l-4 border-t-4 border-white"></div>
                    <div class="absolute right-0 top-0 h-8 w-8 border-r-4 border-t-4 border-white"></div>
                    <div class="absolute bottom-0 left-0 h-8 w-8 border-b-4 border-l-4 border-white"></div>
                    <div class="absolute bottom-0 right-0 h-8 w-8 border-b-4 border-r-4 border-white"></div>
                    
                    <!-- Línea de escaneo animada -->
                    <div class="scan-line absolute left-0 right-0 h-1 bg-white"></div>
                  </div>
                </div>
              </div>

              <!-- Información -->
              <div class="p-6 space-y-3">
                <div class="rounded-lg bg-gray-50 p-3">
                  <p class="text-sm font-semibold text-gray-700 mb-1">
                    Se espera escanear:
                  </p>
                  <p v-if="tipoEquipoVerificar === 'portatil'" class="text-xs text-blue-600 font-mono">
                    Serial: {{ personaInfo?.portatil_asociado?.serial }}
                  </p>
                </div>

                <Transition name="error">
                  <div v-if="error" class="rounded-lg bg-red-50 border border-red-200 p-3">
                    <p class="text-sm font-semibold text-red-800 whitespace-pre-line">{{ error }}</p>
                  </div>
                </Transition>

                <button
                  type="button"
                  @click="cancelarVerificacion"
                  class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 transition-all hover:bg-gray-50 active:scale-95"
                >
                  Cancelar Verificación
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick, onUnmounted } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false }
})

const emit = defineEmits(['close', 'acceso-registrado', 'incidencia-detectada'])
const cedula = ref('')
const error = ref('')
const searching = ref(false)
const confirming = ref(false)
const personaInfo = ref(null)
const cedulaInput = ref(null)

// Estados para verificación de equipos en SALIDA
const verificandoEquipo = ref(false)
const tipoEquipoVerificar = ref(null) // solo 'portatil' (vehiculo eliminado)
const videoElement = ref(null)
const canvasElement = ref(null)
let mediaStream = null
let scanningInterval = null

watch(() => props.show, (newValue) => {
  if (newValue) {
    nextTick(() => {
      if (cedulaInput.value) cedulaInput.value.focus()
    })
  } else {
    stopCamera()
    cedula.value = ''
    error.value = ''
    searching.value = false
    confirming.value = false
    personaInfo.value = null
    verificandoEquipo.value = false
    tipoEquipoVerificar.value = null
  }
})

const handleClose = () => {
  if (!searching.value && !confirming.value) emit('close')
}

const clearInput = () => {
  cedula.value = ''
  error.value = ''
  if (cedulaInput.value) cedulaInput.value.focus()
}

const clearError = () => {
  error.value = ''
}

const resetSearch = () => {
  cedula.value = ''
  error.value = ''
  personaInfo.value = null
  nextTick(() => {
    if (cedulaInput.value) cedulaInput.value.focus()
  })
}

const handleSearch = async () => {
  const trimmedCedula = cedula.value.trim()
  if (!trimmedCedula) {
    error.value = 'Por favor ingresa un número de cédula'
    return
  }
  if (trimmedCedula.length < 5) {
    error.value = 'La cédula debe tener al menos 5 caracteres'
    return
  }
  if (trimmedCedula.length > 20) {
    error.value = 'La cédula no puede tener más de 20 caracteres'
    return
  }
  if (!/^\d+$/.test(trimmedCedula)) {
    error.value = 'La cédula solo debe contener números'
    return
  }
  searching.value = true
  error.value = ''
  
  try {
    // Usar window.axios que automáticamente incluye el token CSRF de Laravel
    const response = await window.axios.post(route('system.celador.qr.buscar-persona'), {
      qr_persona: `PERSONA_${trimmedCedula}`
    })
    
    // Axios ya parsea el JSON automáticamente
    if (response.data) {
      personaInfo.value = response.data
    } else {
      error.value = 'No se recibió información de la persona'
    }
  } catch (err) {
    console.error('Error en búsqueda:', err)
    
    // Manejar diferentes tipos de errores
    if (err.response) {
      // El servidor respondió con un código de error
      if (err.response.status === 404) {
        error.value = 'Persona no encontrada con esa cédula'
      } else if (err.response.status === 419) {
        error.value = 'Sesión expirada. Por favor recarga la página.'
      } else if (err.response.data?.message) {
        error.value = err.response.data.message
      } else {
        error.value = `Error del servidor (${err.response.status})`
      }
    } else if (err.request) {
      // La petición se hizo pero no hubo respuesta
      error.value = 'Sin respuesta del servidor. Verifica tu conexión.'
    } else {
      // Algo pasó al configurar la petición
      error.value = err.message || 'Error al buscar persona'
    }
  } finally {
    searching.value = false
  }
}

// Función para iniciar verificación de equipo (SALIDA)
const iniciarVerificacion = async (tipo) => {
  tipoEquipoVerificar.value = tipo
  verificandoEquipo.value = true
  error.value = ''
  
  await nextTick()
  await startCamera()
}

// Función para confiar (skip verificación)
const confiarSinVerificar = () => {
  // Registrar sin verificar equipos
  confirmAcceso(true)
}

// Iniciar cámara para escaneo
const startCamera = async () => {
  try {
    const constraints = {
      video: {
        facingMode: 'environment',
        width: { ideal: 1280 },
        height: { ideal: 720 }
      }
    }

    mediaStream = await navigator.mediaDevices.getUserMedia(constraints)
    
    if (videoElement.value) {
      videoElement.value.srcObject = mediaStream
      await videoElement.value.play()
      startScanning()
    }
  } catch (err) {
    console.error('Error al iniciar cámara:', err)
    error.value = 'No se pudo acceder a la cámara'
    verificandoEquipo.value = false
  }
}

// Detener cámara
const stopCamera = () => {
  if (scanningInterval) {
    clearInterval(scanningInterval)
    scanningInterval = null
  }

  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop())
    mediaStream = null
  }

  if (videoElement.value) {
    videoElement.value.srcObject = null
  }
}

// Iniciar escaneo continuo
const startScanning = () => {
  if (scanningInterval) return
  
  // Importar jsQR dinámicamente
  import('jsqr').then(({ default: jsQR }) => {
    scanningInterval = setInterval(() => {
      processFrame(jsQR)
    }, 250)
  })
}

// Procesar frame del video
const processFrame = (jsQR) => {
  if (!videoElement.value || !canvasElement.value || !verificandoEquipo.value) return
  
  const video = videoElement.value
  const canvas = canvasElement.value
  
  if (video.readyState !== video.HAVE_ENOUGH_DATA) return
  
  canvas.width = video.videoWidth
  canvas.height = video.videoHeight
  
  const context = canvas.getContext('2d')
  context.drawImage(video, 0, 0, canvas.width, canvas.height)
  
  const imageData = context.getImageData(0, 0, canvas.width, canvas.height)
  const code = jsQR(imageData.data, imageData.width, imageData.height, {
    inversionAttempts: 'dontInvert'
  })
  
  if (code && code.data) {
    handleQrVerificacion(code.data)
  }
}

// Manejar QR escaneado durante verificación
const handleQrVerificacion = (qrData) => {
  // Detener escaneo
  stopCamera()
  
  if (tipoEquipoVerificar.value === 'portatil') {
    // Extraer serial del QR: "PORTATIL_3HABSA57B79" -> "3HABSA57B79"
    let serialEscaneado = qrData
    if (qrData.startsWith('PORTATIL_')) {
      serialEscaneado = qrData.replace('PORTATIL_', '')
    }
    
    const serialEsperado = personaInfo.value.portatil_asociado.serial
    
    if (serialEscaneado === serialEsperado) {
      // ✅ COINCIDE - Registrar salida normal
      verificandoEquipo.value = false
      confirmAcceso(false, serialEscaneado)
    } else {
      // ❌ NO COINCIDE - Emitir evento para abrir modal de incidencia
      verificandoEquipo.value = false
      stopCamera()
      
      emit('incidencia-detectada', {
        errorMessage: `El portátil escaneado NO coincide con el registrado en la entrada`,
        accesoInfo: {
          persona: personaInfo.value.persona.Nombre,
          documento: personaInfo.value.persona.documento,
          equipoEsperado: `Serial: ${serialEsperado}`,
          equipoVerificado: `Serial: ${serialEscaneado}`
        },
        datosRegistro: {
          qr_persona: `PERSONA_${cedula.value.trim()}`,
          qr_portatil: personaInfo.value.tiene_portatil ? `PORTATIL_${personaInfo.value.portatil_asociado.serial}` : null,
          serial_verificado: serialEscaneado
        }
      })
    }
  }
}

// Cancelar verificación
const cancelarVerificacion = () => {
  stopCamera()
  verificandoEquipo.value = false
  tipoEquipoVerificar.value = null
  error.value = ''
}

const confirmAcceso = async (confiar = false, serialVerificado = null) => {
  if (!personaInfo.value) return
  confirming.value = true
  error.value = ''
  
  try {
    // Preparar datos para registrar (solo portátil, vehiculo eliminado)
    const data = {
      qr_persona: `PERSONA_${cedula.value.trim()}`,
      qr_portatil: personaInfo.value.tiene_portatil ? `PORTATIL_${personaInfo.value.portatil_asociado.serial}` : null
    }
    
    // Si es SALIDA y se verificó portátil, enviar datos de verificación
    if (personaInfo.value.es_salida && serialVerificado) {
      data.serial_verificado = serialVerificado
    }
    
    // Registrar el acceso directamente desde el modal
    const response = await window.axios.post(route('system.celador.qr.registrar'), data)
    
    if (response.data) {
      // Emitir evento de éxito para que el padre actualice la lista
      emit('acceso-registrado', response.data)
      
      // Mostrar mensaje de éxito temporal
      const tipoAcceso = personaInfo.value.es_entrada ? 'ENTRADA' : 'SALIDA'
      const mensaje = `✅ ${tipoAcceso} registrada exitosamente para ${personaInfo.value.persona.Nombre}`
      
      // Aquí podrías usar una notificación toast si tienes
      console.log(mensaje)
      
      // Limpiar y preparar para siguiente registro
      setTimeout(() => {
        resetSearch()
        confirming.value = false
      }, 1000)
    }
  } catch (err) {
    console.error('Error al registrar acceso:', err)
    
    // Manejar diferentes tipos de errores
    if (err.response) {
      if (err.response.status === 422) {
        // Error de validación
        const errors = err.response.data.errors
        if (errors) {
          const firstError = Object.values(errors)[0][0]
          error.value = firstError
        } else {
          error.value = err.response.data.message || 'Error de validación'
        }
      } else if (err.response.status === 419) {
        error.value = 'Sesión expirada. Por favor recarga la página.'
      } else if (err.response.data?.message) {
        error.value = err.response.data.message
      } else {
        error.value = `Error al registrar acceso (${err.response.status})`
      }
    } else if (err.request) {
      error.value = 'Sin respuesta del servidor. Verifica tu conexión.'
    } else {
      error.value = err.message || 'Error al registrar acceso'
    }
    
    confirming.value = false
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.show && !searching.value && !confirming.value) {
    handleClose()
  }
}

if (typeof window !== 'undefined') {
  window.addEventListener('keydown', handleKeydown)
}

// Limpiar al desmontar
onUnmounted(() => {
  stopCamera()
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleKeydown)
  }
})

defineExpose({
  setProcessing: (value) => { confirming.value = value },
  setError: (message) => {
    error.value = message
    confirming.value = false
    searching.value = false
  },
  close: () => { emit('close') }
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-active > div > div,
.modal-leave-active > div > div {
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from > div > div,
.modal-leave-to > div > div {
  transform: scale(0.9) translateY(-20px);
}
.error-enter-active,
.error-leave-active {
  transition: all 0.2s ease;
}
.error-enter-from,
.error-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
input:focus {
  animation: input-focus 0.3s ease;
}
@keyframes input-focus {
  0% { transform: scale(1); }
  50% { transform: scale(1.02); }
  100% { transform: scale(1); }
}
/* Scanner modal transitions */
.scanner-enter-active,
.scanner-leave-active {
  transition: opacity 0.3s ease;
}
.scanner-enter-from,
.scanner-leave-to {
  opacity: 0;
}
/* Scanning line animation */
.scan-line {
  animation: scan 2s linear infinite;
}
@keyframes scan {
  0%, 100% {
    top: 0;
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    top: 100%;
    opacity: 0;
  }
}
@media (max-width: 640px) {
  button { min-height: 44px; }
  input { font-size: 16px; }
}
@media (hover: none) and (pointer: coarse) {
  * {
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
  }
}
</style>
