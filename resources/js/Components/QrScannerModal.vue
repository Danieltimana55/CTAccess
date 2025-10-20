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
            <div class="bg-blue-600 px-6 py-5">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-bold text-white">Escanear QR</h3>
                    <p class="text-xs text-blue-50">Apunta la cámara al código QR</p>
                  </div>
                </div>
                <button
                  @click="handleClose"
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-white transition-all hover:bg-white/30 active:scale-95"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Body -->
            <div class="p-6">
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
                  <div class="relative h-64 w-64">
                    <!-- Esquinas del marco -->
                    <div class="absolute left-0 top-0 h-16 w-16 border-l-4 border-t-4 border-blue-500"></div>
                    <div class="absolute right-0 top-0 h-16 w-16 border-r-4 border-t-4 border-blue-500"></div>
                    <div class="absolute bottom-0 left-0 h-16 w-16 border-b-4 border-l-4 border-blue-500"></div>
                    <div class="absolute bottom-0 right-0 h-16 w-16 border-b-4 border-r-4 border-blue-500"></div>
                    
                    <!-- Línea de escaneo animada -->
                    <div class="scan-line absolute left-0 right-0 top-0 h-1 bg-blue-500"></div>
                  </div>
                </div>

                <!-- Estado de carga -->
                <div
                  v-if="loading"
                  class="absolute inset-0 flex items-center justify-center bg-gray-900/80"
                >
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-white">Iniciando cámara...</p>
                  </div>
                </div>

                <!-- Mensaje de éxito -->
                <Transition name="success">
                  <div
                    v-if="successMessage"
                    class="absolute inset-0 flex items-center justify-center bg-emerald-600/95"
                  >
                    <div class="text-center px-4">
                      <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-white">
                        <svg class="h-10 w-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </div>
                      <p class="text-lg font-bold text-white">{{ successMessage }}</p>
                    </div>
                  </div>
                </Transition>
              </div>

              <!-- Canvas oculto para procesar frames -->
              <canvas ref="canvasElement" class="hidden"></canvas>

              <!-- Info y controles -->
              <div class="mt-4 space-y-3">
                <!-- Información de la Persona (cuando se detecta) -->
                <div v-if="personaInfo && !error" class="space-y-3">
                  <!-- Datos de la persona -->
                  <div class="rounded-lg bg-emerald-50 border-2 border-emerald-200 p-3">
                    <div class="flex items-start space-x-2">
                      <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-white flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                      </div>
                      <div class="flex-1">
                        <h4 class="text-sm font-bold text-emerald-900">{{ personaInfo.persona?.Nombre }}</h4>
                        <p class="text-xs text-emerald-700">
                          <span class="font-medium">Cédula:</span> {{ personaInfo.persona?.documento }}
                        </p>
                        <p class="text-xs text-emerald-700">
                          <span class="font-medium">Tipo:</span> {{ personaInfo.persona?.TipoPersona }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Equipos -->
                  <div class="space-y-2">
                    <div v-if="personaInfo.tiene_portatil" class="flex items-center space-x-2 rounded-lg bg-blue-50 border border-blue-200 p-2">
                      <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white flex-shrink-0">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-blue-900 truncate">
                          {{ personaInfo.portatil_asociado?.marca }} {{ personaInfo.portatil_asociado?.modelo }}
                        </p>
                        <p class="text-xs text-blue-700">Serial: {{ personaInfo.portatil_asociado?.serial }}</p>
                      </div>
                      <svg class="h-4 w-4 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                      </svg>
                    </div>

                  </div>

                  <!-- Tipo de acceso -->
                  <div class="rounded-lg border-2 p-2" :class="{
                    'bg-green-50 border-green-300': personaInfo.es_entrada,
                    'bg-yellow-50 border-yellow-300': personaInfo.es_salida
                  }">
                    <div class="flex items-center space-x-2">
                      <svg v-if="personaInfo.es_entrada" class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                      </svg>
                      <svg v-else class="h-4 w-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                      </svg>
                      <span class="text-xs font-bold" :class="{
                        'text-green-800': personaInfo.es_entrada,
                        'text-yellow-800': personaInfo.es_salida
                      }">
                        {{ personaInfo.mensaje_accion }}
                      </span>
                    </div>
                  </div>

                  <!-- Botones de acción -->
                  <div class="flex space-x-2 pt-1">
                    <button
                      type="button"
                      @click="resetScan"
                      :disabled="confirming"
                      class="flex-1 rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-all hover:bg-gray-50 active:scale-95 disabled:opacity-50"
                    >
                      <span class="flex items-center justify-center space-x-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Nuevo</span>
                      </span>
                    </button>
                    <button
                      type="button"
                      @click="confirmAcceso"
                      :disabled="confirming"
                      class="flex-1 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-bold text-white transition-all hover:bg-emerald-700 active:scale-95 disabled:opacity-50"
                    >
                      <span v-if="confirming" class="flex items-center justify-center space-x-1">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Registrando...</span>
                      </span>
                      <span v-else class="flex items-center justify-center space-x-1">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Confirmar</span>
                      </span>
                    </button>
                  </div>
                </div>

                <!-- Estado: Buscando -->
                <div v-else-if="searching" class="rounded-lg bg-blue-50 border-2 border-blue-200 p-4">
                  <div class="flex items-center justify-center space-x-2">
                    <svg class="h-5 w-5 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-medium text-blue-900">Buscando persona...</p>
                  </div>
                </div>

                <!-- Error -->
                <div v-else-if="error" class="rounded-lg bg-red-50 border-2 border-red-200 p-3">
                  <div class="flex items-start space-x-2">
                    <svg class="h-5 w-5 mt-0.5 flex-shrink-0 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-red-900">Error</p>
                      <p class="text-xs text-red-700">{{ error }}</p>
                    </div>
                  </div>
                </div>

                <!-- Instrucciones (cuando no hay escaneo) -->
                <div v-else class="rounded-lg bg-gray-50 p-3">
                  <div class="flex items-start space-x-2">
                    <svg class="h-5 w-5 mt-0.5 flex-shrink-0 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                      <p class="text-xs font-medium text-gray-900">Instrucciones</p>
                      <p class="text-xs text-gray-600 mt-1">
                        Centra el código QR o código de barras con la cédula dentro del marco. El escaneo es automático.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Botón cerrar/activar cámara (cuando no hay persona detectada) -->
                <div v-if="!personaInfo" class="flex space-x-3">
                  <button
                    type="button"
                    @click="handleClose"
                    class="flex-1 rounded-lg border-2 border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 transition-all hover:bg-gray-50 active:scale-95"
                  >
                    Cerrar
                  </button>
                  <button
                    v-if="!cameraActive"
                    @click="startCamera"
                    :disabled="loading"
                    class="flex-1 rounded-lg bg-gradient-to-r from-blue-600 to-blue-500 px-4 py-3 font-bold text-white shadow-lg transition-all hover:shadow-xl active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <span class="flex items-center justify-center space-x-2">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                      <span>Activar Cámara</span>
                    </span>
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
