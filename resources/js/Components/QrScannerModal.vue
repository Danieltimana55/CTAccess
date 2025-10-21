<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="handleClose"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/70 transition-opacity"></div>

        <!-- Modal Container -->
        <div class="flex min-h-screen items-center justify-center p-4">
          <div
            class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white transition-all"
            @click.stop
          >
            <!-- Header -->
            <div class="bg-blue-600 px-4 py-3">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Escanear Código QR</h3>
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

            <!-- Body -->
            <div class="p-4">
              <!-- Video Preview -->
              <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-gray-900">
                <video
                  ref="videoElement"
                  autoplay
                  playsinline
                  class="h-full w-full object-cover"
                ></video>
                
                <!-- Overlay de escaneo -->
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="relative h-48 w-48">
                    <!-- Esquinas del marco -->
                    <div class="absolute left-0 top-0 h-12 w-12 border-l-4 border-t-4 border-white"></div>
                    <div class="absolute right-0 top-0 h-12 w-12 border-r-4 border-t-4 border-white"></div>
                    <div class="absolute bottom-0 left-0 h-12 w-12 border-b-4 border-l-4 border-white"></div>
                    <div class="absolute bottom-0 right-0 h-12 w-12 border-b-4 border-r-4 border-white"></div>
                  </div>
                </div>

                <!-- Estado de carga -->
                <div
                  v-if="loading"
                  class="absolute inset-0 flex items-center justify-center bg-gray-900/80"
                >
                  <p class="text-xs font-medium text-white">Iniciando cámara...</p>
                </div>

                <!-- Mensaje de éxito -->
                <Transition name="success">
                  <div
                    v-if="successMessage"
                    class="absolute inset-0 flex items-center justify-center bg-emerald-600/95"
                  >
                    <p class="text-sm font-bold text-white px-4 text-center">{{ successMessage }}</p>
                  </div>
                </Transition>
              </div>

              <!-- Canvas oculto para procesar frames -->
              <canvas ref="canvasElement" class="hidden"></canvas>

              <!-- Info y controles -->
              <div class="mt-2 space-y-2">
                <!-- Información de la Persona (cuando se detecta) -->
                <div v-if="personaInfo && !error" class="space-y-2">
                  <!-- Datos de la persona -->
                  <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-2">
                    <div class="flex items-start space-x-2">
                      <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-white flex-shrink-0">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                      </div>
                      <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold text-emerald-900 truncate">{{ personaInfo.persona?.Nombre }}</h4>
                        <p class="text-[10px] text-emerald-700">Cédula: {{ personaInfo.persona?.documento }}</p>
                        <p class="text-[10px] text-emerald-700">Tipo: {{ personaInfo.persona?.TipoPersona }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Equipos -->
                  <div v-if="personaInfo.tiene_portatil" class="flex items-center space-x-2 rounded-lg bg-blue-50 border border-blue-200 p-2">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white flex-shrink-0">
                      <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-[10px] font-semibold text-blue-900 truncate">
                        {{ personaInfo.portatil_asociado?.marca }} {{ personaInfo.portatil_asociado?.modelo }}
                      </p>
                      <p class="text-[10px] text-blue-700">Serial: {{ personaInfo.portatil_asociado?.serial }}</p>
                    </div>
                  </div>

                  <!-- Tipo de acceso -->
                  <div class="rounded-lg border p-1.5" :class="{
                    'bg-green-50 border-green-300': personaInfo.es_entrada,
                    'bg-yellow-50 border-yellow-300': personaInfo.es_salida
                  }">
                    <p class="text-[10px] font-bold text-center" :class="{
                      'text-green-800': personaInfo.es_entrada,
                      'text-yellow-800': personaInfo.es_salida
                    }">
                      {{ personaInfo.mensaje_accion }}
                    </p>
                  </div>

                  <!-- Botones de acción -->
                  <div class="flex space-x-2">
                    <button
                      type="button"
                      @click="resetScan"
                      :disabled="confirming"
                      class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                    >
                      Nuevo
                    </button>
                    <button
                      type="button"
                      @click="confirmAcceso"
                      :disabled="confirming"
                      class="flex-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700 disabled:opacity-50"
                    >
                      {{ confirming ? 'Registrando...' : 'Confirmar' }}
                    </button>
                  </div>
                </div>

                <!-- Estado: Buscando -->
                <div v-else-if="searching" class="rounded-lg bg-blue-50 border border-blue-200 p-2">
                  <p class="text-xs text-center font-medium text-blue-900">Buscando persona...</p>
                </div>

                <!-- Error -->
                <div v-else-if="error" class="rounded-lg bg-red-50 border border-red-200 p-2">
                  <p class="text-xs text-red-900 font-medium">{{ error }}</p>
                </div>

                <!-- Instrucciones (cuando no hay escaneo) -->
                <div v-else class="rounded-lg bg-gray-50 p-2">
                  <p class="text-[10px] text-center text-gray-600">
                    Centra el código QR dentro del marco. El escaneo es automático.
                  </p>
                </div>

                <!-- Botón cerrar/activar cámara (cuando no hay persona detectada) -->
                <div v-if="!personaInfo" class="flex space-x-2">
                  <button
                    type="button"
                    @click="handleClose"
                    class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50"
                  >
                    Cerrar
                  </button>
                  <button
                    v-if="!cameraActive"
                    @click="startCamera"
                    :disabled="loading"
                    class="flex-1 rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700 disabled:opacity-50"
                  >
                    Activar Cámara
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick, onUnmounted } from 'vue'
import jsQR from 'jsqr'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  autoStart: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['close', 'acceso-registrado', 'incidencia-detectada'])

const videoElement = ref(null)
const canvasElement = ref(null)
const loading = ref(false)
const cameraActive = ref(false)
const lastScanResult = ref('')
const lastScanTime = ref('')
const successMessage = ref('')
const searching = ref(false)
const confirming = ref(false)
const personaInfo = ref(null)
const error = ref('')

let scanningInterval = null
let mediaStream = null

// Watch para iniciar cámara cuando se abre el modal
watch(() => props.show, async (newValue) => {
  if (newValue && props.autoStart) {
    await nextTick()
    await startCamera()
  } else if (!newValue) {
    stopCamera()
    // Limpiar estado
    lastScanResult.value = ''
    lastScanTime.value = ''
    successMessage.value = ''
  }
})

const startCamera = async () => {
  if (cameraActive.value) return

  try {
    loading.value = true

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
      cameraActive.value = true
      loading.value = false

      // Iniciar escaneo continuo
      startScanning()
    }
  } catch (error) {
    loading.value = false
    console.error('Error al iniciar cámara:', error)
    
    let errorMessage = 'No se pudo acceder a la cámara'
    if (error.name === 'NotAllowedError') {
      errorMessage = 'Permiso de cámara denegado'
    } else if (error.name === 'NotFoundError') {
      errorMessage = 'No se encontró ninguna cámara'
    }
    
    alert(errorMessage)
  }
}

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

  cameraActive.value = false
  loading.value = false
}

const startScanning = () => {
  if (scanningInterval) return

  scanningInterval = setInterval(async () => {
    await processFrame()
  }, 250) // Escanear cada 250ms
}

const processFrame = async () => {
  if (!videoElement.value || !canvasElement.value || !cameraActive.value) {
    return
  }

  const video = videoElement.value
  const canvas = canvasElement.value

  if (video.readyState !== video.HAVE_ENOUGH_DATA) {
    return
  }

  try {
    // Configurar canvas
    canvas.width = video.videoWidth
    canvas.height = video.videoHeight

    const context = canvas.getContext('2d')
    context.drawImage(video, 0, 0, canvas.width, canvas.height)

    // Obtener datos de la imagen
    const imageData = context.getImageData(0, 0, canvas.width, canvas.height)

    // Escanear QR usando jsQR
    const code = jsQR(imageData.data, imageData.width, imageData.height, {
      inversionAttempts: 'dontInvert'
    })
    
    if (code && code.data) {
      handleQrDetected(code.data)
    }
  } catch (err) {
    // Error al escanear (esto puede ser normal si no hay QR visible)
    console.error('Error al escanear:', err)
  }
}

const handleQrDetected = async (qrData) => {
  // Detener escaneo para evitar múltiples lecturas
  if (scanningInterval) {
    clearInterval(scanningInterval)
    scanningInterval = null
  }

  lastScanResult.value = qrData
  lastScanTime.value = new Date().toLocaleTimeString()

  // Extraer solo el número de cédula del QR
  // El QR puede venir como "1125180688" o "PERSONA_1125180688"
  let cedula = qrData
  if (qrData.startsWith('PERSONA_')) {
    cedula = qrData.replace('PERSONA_', '')
  }

  // Buscar la persona automáticamente
  await buscarPersona(cedula)
}

const buscarPersona = async (cedula) => {
  searching.value = true
  error.value = ''
  successMessage.value = ''

  try {
    const response = await window.axios.post(route('system.celador.qr.buscar-persona'), {
      qr_persona: `PERSONA_${cedula}`
    })

    if (response.data) {
      personaInfo.value = response.data
      successMessage.value = `✓ ${response.data.persona.Nombre} detectado/a`
    } else {
      error.value = 'No se recibió información de la persona'
      // Reiniciar escaneo si no se encontró
      setTimeout(() => {
        startScanning()
      }, 2000)
    }
  } catch (err) {
    console.error('Error en búsqueda:', err)

    if (err.response) {
      if (err.response.status === 404) {
        error.value = 'Persona no encontrada'
      } else if (err.response.status === 419) {
        error.value = 'Sesión expirada. Por favor recarga la página.'
      } else if (err.response.data?.message) {
        error.value = err.response.data.message
      } else {
        error.value = `Error del servidor (${err.response.status})`
      }
    } else if (err.request) {
      error.value = 'Sin respuesta del servidor'
    } else {
      error.value = err.message || 'Error al buscar persona'
    }

    // Reiniciar escaneo después del error
    setTimeout(() => {
      startScanning()
    }, 2000)
  } finally {
    searching.value = false
  }
}

const confirmAcceso = async () => {
  if (!personaInfo.value) return
  confirming.value = true
  error.value = ''

  try {
    // Registrar el acceso directamente desde el modal (solo portátil, sin vehículo)
    const response = await window.axios.post(route('system.celador.qr.registrar'), {
      qr_persona: `PERSONA_${lastScanResult.value.replace('PERSONA_', '')}`,
      qr_portatil: personaInfo.value.tiene_portatil ? `PORTATIL_${personaInfo.value.portatil_asociado.serial}` : null
    })

    if (response.data) {
      // Emitir evento de éxito
      emit('acceso-registrado', response.data)

      const tipoAcceso = personaInfo.value.es_entrada ? 'ENTRADA' : 'SALIDA'
      successMessage.value = `✅ ${tipoAcceso} registrada para ${personaInfo.value.persona.Nombre}`

      // Limpiar y preparar para siguiente escaneo
      setTimeout(() => {
        resetScan()
        confirming.value = false
      }, 1500)
    }
  } catch (err) {
    console.error('Error al registrar acceso:', err)

    if (err.response) {
      if (err.response.status === 422) {
        const errors = err.response.data.errors
        error.value = errors ? Object.values(errors)[0][0] : 'Error de validación'
      } else if (err.response.status === 419) {
        error.value = 'Sesión expirada. Por favor recarga la página.'
      } else if (err.response.data?.message) {
        error.value = err.response.data.message
      } else {
        error.value = `Error al registrar acceso (${err.response.status})`
      }
    } else if (err.request) {
      error.value = 'Sin respuesta del servidor'
    } else {
      error.value = err.message || 'Error al registrar acceso'
    }

    confirming.value = false
  }
}

const resetScan = () => {
  personaInfo.value = null
  error.value = ''
  successMessage.value = ''
  lastScanResult.value = ''
  lastScanTime.value = ''
  
  // Reiniciar escaneo
  if (cameraActive.value) {
    startScanning()
  }
}

const handleClose = () => {
  stopCamera()
  personaInfo.value = null
  error.value = ''
  successMessage.value = ''
  emit('close')
}

// Limpiar al desmontar
onUnmounted(() => {
  stopCamera()
})

// Exponer métodos
defineExpose({
  startCamera,
  stopCamera
})
</script>

<style scoped>
/* Modal transitions */
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

/* Success message transition */
.success-enter-active,
.success-leave-active {
  transition: all 0.3s ease;
}

.success-enter-from,
.success-leave-to {
  opacity: 0;
  transform: scale(0.95);
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

/* PWA optimizations */
@media (max-width: 640px) {
  button {
    min-height: 44px;
  }
}

@media (hover: none) and (pointer: coarse) {
  * {
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
  }
}
</style>
