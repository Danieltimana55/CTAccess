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
            <div class="bg-emerald-600 px-6 py-5">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-bold text-white">Entrada Manual</h3>
                    <p class="text-xs text-emerald-50">Búsqueda rápida por cédula</p>
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
            <div class="p-6">
              <form @submit.prevent="handleSearch" v-if="!personaInfo">
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700">
                    <div class="flex items-center space-x-2">
                      <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                      </svg>
                      <span>Número de Cédula</span>
                    </div>
                  </label>
                  <div class="relative">
                    <input
                      ref="cedulaInput"
                      v-model="cedula"
                      type="text"
                      inputmode="numeric"
                      pattern="[0-9]*"
                      placeholder="Ej: 123456789"
                      class="w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg font-medium text-gray-900 placeholder-gray-400 transition-all focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/20"
                      :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/20': error }"
                      @input="clearError"
                    />
                    <div v-if="cedula" class="absolute right-3 top-1/2 -translate-y-1/2">
                      <button
                        type="button"
                        @click="clearInput"
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-200 text-gray-600 transition-all hover:bg-gray-300 active:scale-95"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                  <Transition name="error">
                    <p v-if="error" class="text-sm text-red-600 flex items-center space-x-1">
                      <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                      </svg>
                      <span>{{ error }}</span>
                    </p>
                  </Transition>
                  <p class="text-xs text-gray-500 flex items-start space-x-1">
                    <svg class="h-4 w-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Ingresa solo números, sin espacios ni caracteres especiales</span>
                  </p>
                </div>
                <div class="mt-4 hidden sm:flex items-center justify-center space-x-4 text-xs text-gray-500">
                  <div class="flex items-center space-x-1">
                    <kbd class="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-mono">Enter</kbd>
                    <span>Buscar</span>
                  </div>
                  <div class="flex items-center space-x-1">
                    <kbd class="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-mono">Esc</kbd>
                    <span>Cerrar</span>
                  </div>
                </div>
                <div class="mt-6 flex space-x-3">
                  <button
                    type="button"
                    @click="handleClose"
                    :disabled="searching"
                    class="flex-1 rounded-lg border-2 border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 transition-all hover:bg-gray-50 active:scale-95 disabled:opacity-50"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    :disabled="!cedula.trim() || searching"
                    class="flex-1 rounded-lg bg-emerald-600 px-4 py-3 font-bold text-white transition-all hover:bg-emerald-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <span v-if="searching" class="flex items-center justify-center space-x-2">
                      <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span>Buscando...</span>
                    </span>
                    <span v-else class="flex items-center justify-center space-x-2">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                      </svg>
                      <span>Buscar Persona</span>
                    </span>
                  </button>
                </div>
              </form>
              <div v-else class="space-y-4">
                <div class="rounded-lg bg-emerald-50 border-2 border-emerald-200 p-4">
                  <div class="flex items-start space-x-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-600 text-white flex-shrink-0">
                      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                    <div class="flex-1">
                      <h4 class="text-lg font-bold text-emerald-900">{{ personaInfo?.persona?.Nombre || 'Sin nombre' }}</h4>
                      <p class="text-sm text-emerald-700">
                        <span class="font-medium">Cédula:</span> {{ personaInfo?.persona?.documento || cedula }}
                      </p>
                      <p class="text-sm text-emerald-700">
                        <span class="font-medium">Tipo:</span> {{ personaInfo?.persona?.TipoPersona || 'N/A' }}
                      </p>
                    </div>
                  </div>
                </div>
                <div class="space-y-2">
                  <h5 class="text-sm font-semibold text-gray-700">Equipos Asociados:</h5>
                  <div v-if="personaInfo?.tiene_portatil" class="rounded-lg bg-blue-50 border border-blue-200 p-3">
                    <div class="flex items-center space-x-3">
                      <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-white flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                      <div class="flex-1">
                        <p class="text-sm font-semibold text-blue-900">
                          {{ personaInfo?.portatil_asociado?.marca || '' }} {{ personaInfo?.portatil_asociado?.modelo || '' }}
                        </p>
                        <p class="text-xs text-blue-700">
                          Serial: <span class="font-mono">{{ personaInfo?.portatil_asociado?.serial || 'N/A' }}</span>
                        </p>
                      </div>
                      <svg v-if="personaInfo?.es_entrada" class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                      </svg>
                    </div>
                    <!-- Botones de verificación (solo en SALIDA) -->
                    <div v-if="personaInfo?.es_salida && !verificandoEquipo" class="mt-3 flex space-x-2">
                      <button
                        type="button"
                        @click="iniciarVerificacion('portatil')"
                        class="flex-1 flex items-center justify-center space-x-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition-all hover:bg-blue-700 active:scale-95"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Verificar</span>
                      </button>
                    </div>
                  </div>
                  <div v-if="!personaInfo?.tiene_portatil" class="rounded-lg bg-gray-50 border border-gray-200 p-3 text-center">
                    <p class="text-sm text-gray-600">Sin portátil asociado</p>
                  </div>
                </div>
                <div class="rounded-lg border-2 p-3" :class="{
                  'bg-green-50 border-green-300': personaInfo?.es_entrada,
                  'bg-yellow-50 border-yellow-300': personaInfo?.es_salida
                }">
                  <div class="flex items-center space-x-2">
                    <svg v-if="personaInfo?.es_entrada" class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <svg v-else class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-bold" :class="{
                      'text-green-800': personaInfo?.es_entrada,
                      'text-yellow-800': personaInfo?.es_salida
                    }">
                      {{ personaInfo?.mensaje_accion || 'Acceso' }}
                    </span>
                  </div>
                </div>
                <!-- Botones de acción -->
                <div class="flex space-x-3 pt-2">
                  <button
                    type="button"
                    @click="resetSearch"
                    :disabled="confirming || verificandoEquipo"
                    class="flex-1 rounded-lg border-2 border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 transition-all hover:bg-gray-50 active:scale-95 disabled:opacity-50"
                  >
                    <span class="flex items-center justify-center space-x-2">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                      </svg>
                      <span>Nueva Búsqueda</span>
                    </span>
                  </button>
                  <!-- Botón dinámico según tipo de acceso -->
                  <button
                    v-if="personaInfo?.es_entrada"
                    type="button"
                    @click="confirmAcceso(false)"
                    :disabled="confirming || verificandoEquipo"
                    class="flex-1 rounded-lg bg-emerald-600 px-4 py-3 font-bold text-white transition-all hover:bg-emerald-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <span v-if="confirming" class="flex items-center justify-center space-x-2">
                      <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span>Registrando...</span>
                    </span>
                    <span v-else class="flex items-center justify-center space-x-2">
                      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <span>Confirmar Acceso</span>
                    </span>
                  </button>
                  <!-- Botón "Confiar" para SALIDA (skip verificación) -->
                  <button
                    v-else-if="personaInfo?.es_salida"
                    type="button"
                    @click="confiarSinVerificar"
                    :disabled="confirming || verificandoEquipo"
                    class="flex-1 rounded-lg bg-yellow-600 px-4 py-3 font-bold text-white transition-all hover:bg-yellow-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <span v-if="confirming" class="flex items-center justify-center space-x-2">
                      <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span>Registrando...</span>
                    </span>
                    <span v-else class="flex items-center justify-center space-x-2">
                      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <span>Confiar y Registrar</span>
                    </span>
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
