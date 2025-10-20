<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="$emit('close')"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/70 transition-opacity"></div>

        <!-- Modal Container -->
        <div class="flex min-h-screen items-center justify-center p-4">
          <div
            class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white transition-all"
            @click.stop
          >
            <!-- Header -->
            <div class="bg-orange-600 px-6 py-5">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-bold text-white">Incidencia Detectada</h3>
                    <p class="text-xs text-yellow-50">El equipo no coincide</p>
                  </div>
                </div>
                <button
                  @click="$emit('close')"
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
              <!-- Alerta de inconsistencia -->
              <div class="mb-4 rounded-lg bg-yellow-50 border-2 border-yellow-200 p-4">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <h4 class="text-sm font-bold text-yellow-900 mb-1">Equipo No Coincide</h4>
                    <p class="text-xs text-yellow-800">{{ errorMessage }}</p>
                  </div>
                </div>
              </div>

              <!-- Información del Acceso -->
              <div v-if="accesoInfo" class="mb-4 space-y-3">
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                  <div class="flex items-start space-x-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-600 text-white flex-shrink-0">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                    <div class="flex-1">
                      <p class="text-sm font-bold text-gray-900">{{ accesoInfo.persona }}</p>
                      <p class="text-xs text-gray-600">Cédula: {{ accesoInfo.documento }}</p>
                    </div>
                  </div>
                </div>

                <!-- Comparación de Equipos -->
                <div class="rounded-lg bg-red-50 border border-red-200 p-3">
                  <h5 class="text-xs font-bold text-red-900 mb-2">Comparación de Equipos:</h5>
                  <div class="space-y-2 text-xs">
                    <div class="flex items-center space-x-2">
                      <svg class="h-4 w-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <span class="text-green-900"><strong>Entrada:</strong> {{ accesoInfo.equipoEsperado }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <svg class="h-4 w-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <span class="text-red-900"><strong>Verificado:</strong> {{ accesoInfo.equipoVerificado }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Campo de descripción adicional -->
              <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Observaciones adicionales (opcional):
                </label>
                <textarea
                  v-model="descripcionAdicional"
                  rows="3"
                  class="w-full rounded-lg border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 text-sm"
                  placeholder="Escriba cualquier observación adicional..."
                ></textarea>
              </div>

              <!-- Aviso -->
              <div class="rounded-lg bg-blue-50 border border-blue-200 p-3">
                <div class="flex items-start space-x-2">
                  <svg class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <div class="flex-1">
                    <p class="text-xs text-blue-900">
                      <strong>Nota:</strong> La salida se registrará normalmente, pero quedará marcada esta incidencia para revisión posterior.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer con botones -->
            <div class="bg-gray-50 px-6 py-4 flex space-x-3">
              <button
                @click="$emit('close')"
                :disabled="isSubmitting"
                type="button"
                class="flex-1 rounded-lg border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50 transition-all active:scale-95"
              >
                Cancelar
              </button>
              <button
                @click="confirmarYRegistrar"
                :disabled="isSubmitting"
                type="button"
                class="flex-1 rounded-lg bg-yellow-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-yellow-700 disabled:opacity-50 transition-all active:scale-95 flex items-center justify-center space-x-2"
              >
                <svg v-if="isSubmitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ isSubmitting ? 'Registrando...' : 'Registrar Salida' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  errorMessage: {
    type: String,
    required: true
  },
  accesoInfo: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'confirmar'])

const descripcionAdicional = ref('')
const isSubmitting = ref(false)

const confirmarYRegistrar = () => {
  isSubmitting.value = true
  
  // Construir descripción completa de la incidencia
  const descripcionCompleta = `${props.errorMessage}${descripcionAdicional.value ? '. Observaciones: ' + descripcionAdicional.value : ''}`
  
  emit('confirmar', {
    descripcion: descripcionCompleta,
    observaciones: descripcionAdicional.value
  })
  
  // Reset después de un breve delay
  setTimeout(() => {
    isSubmitting.value = false
    descripcionAdicional.value = ''
  }, 500)
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
  opacity: 0;
}
</style>
