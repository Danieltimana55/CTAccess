<script setup>
import { ref, computed } from 'vue'
import Icon from '@/Components/Icon.vue'

const props = defineProps({
  persona: {
    type: Object,
    default: null
  },
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

// Estado para vista previa del QR
const showQrPreview = ref(false)

// Computed para acceso seguro
const personaData = computed(() => props.persona || {})
const portatilesList = computed(() => {
  const p = props.persona?.portatiles
  return Array.isArray(p) ? p : []
})
const vehiculosList = computed(() => {
  const v = props.persona?.vehiculos
  return Array.isArray(v) ? v : []
})

// URL de la imagen QR - usa directamente qrCode ya que viene con /storage/...
const qrImageUrl = computed(() => {
  // qrCode ya viene como /storage/qrcodes/xxx.png desde la BD
  const url = personaData.value.qrCode
  console.log('📍 QR Debug:', {
    qrCode: personaData.value.qrCode,
    nombre: personaData.value.Nombre,
    finalUrl: url
  })
  return url
})

// Funciones de utilidad
const getInitials = (nombre) => {
  if (!nombre) return '?'
  const palabras = nombre.trim().split(' ')
  if (palabras.length >= 2) {
    return (palabras[0].charAt(0) + palabras[1].charAt(0)).toUpperCase()
  }
  return nombre.charAt(0).toUpperCase()
}

const getTipoBadgeColor = (tipo) => {
  const colors = {
    'Empleado': 'bg-blue-500 text-white',
    'Visitante': 'bg-green-600 text-white',
    'Contratista': 'bg-yellow-600 text-white',
    'Proveedor': 'bg-purple-600 text-white'
  }
  return colors[tipo] || 'bg-gray-500 text-white'
}

const close = () => {
  emit('close')
}

// Función para descargar QR
const downloadQr = () => {
  if (!qrImageUrl.value) return
  
  const link = document.createElement('a')
  link.href = qrImageUrl.value
  link.download = `QR_${personaData.value.Nombre || 'persona'}_${personaData.value.documento || 'sin-doc'}.png`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

// Función para abrir vista previa
const openQrPreview = () => {
  showQrPreview.value = true
}

// Función para cerrar vista previa
const closeQrPreview = () => {
  showQrPreview.value = false
}

// Cerrar con Escape
const handleKeydown = (e) => {
  if (e.key === 'Escape') close()
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show && persona"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        @click.self="close"
        @keydown="handleKeydown"
      >
        <!-- Modal Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
          <!-- Header Compacto -->
          <div class="bg-[#00304D] px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
              <!-- Avatar -->
              <div class="h-14 w-14 rounded-xl bg-[#39A900] flex items-center justify-center">
                <span class="text-xl font-bold text-white">
                  {{ getInitials(personaData.Nombre) }}
                </span>
              </div>
              <div>
                <h2 class="text-xl font-bold text-white">
                  {{ personaData.Nombre || 'Sin nombre' }}
                </h2>
                <span :class="getTipoBadgeColor(personaData.TipoPersona)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold mt-1">
                  {{ personaData.TipoPersona || 'Sin tipo' }}
                </span>
              </div>
            </div>
            <button
              @click="close"
              class="p-2 hover:bg-white/10 rounded-lg transition-colors"
            >
              <Icon name="x" :size="20" class="text-white" />
            </button>
          </div>

          <!-- Contenido con Scroll -->
          <div class="flex-1 overflow-y-auto p-6">
            <div class="space-y-6">
              <!-- Información Básica -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-sm mb-1">
                    <Icon name="credit-card" :size="16" />
                    <span class="font-medium">Documento</span>
                  </div>
                  <p class="text-gray-900 dark:text-white font-semibold">
                    {{ personaData.documento || 'N/A' }}
                  </p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-sm mb-1">
                    <Icon name="mail" :size="16" />
                    <span class="font-medium">Correo</span>
                  </div>
                  <p class="text-gray-900 dark:text-white font-semibold text-sm truncate">
                    {{ personaData.correo || 'Sin correo' }}
                  </p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-sm mb-2">
                    <Icon name="qr-code" :size="16" />
                    <span class="font-medium">Código QR</span>
                  </div>
                  <div v-if="qrImageUrl" class="space-y-2">
                    <!-- Imagen del QR compacta -->
                    <div class="flex justify-center">
                      <img 
                        :src="qrImageUrl" 
                        :alt="`QR de ${personaData.Nombre}`"
                        class="w-20 h-20 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:scale-105 transition-transform"
                        @click="openQrPreview"
                      />
                    </div>
                    <!-- Botones -->
                    <div class="flex gap-2">
                      <button
                        @click="openQrPreview"
                        class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium"
                      >
                        <Icon name="eye" :size="14" />
                        Ver
                      </button>
                      <button
                        @click="downloadQr"
                        class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium"
                      >
                        <Icon name="download" :size="14" />
                        Descargar
                      </button>
                    </div>
                  </div>
                  <p v-else class="text-gray-500 dark:text-gray-400 text-xs text-center">
                    Sin QR generado
                  </p>
                </div>
              </div>

              <!-- Portátiles y Vehículos -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Portátiles -->
                <div>
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                      <Icon name="laptop" :size="18" class="text-blue-600" />
                      Portátiles
                      <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">
                        {{ portatilesList.length }}
                      </span>
                    </h3>
                  </div>
                  <div v-if="portatilesList.length === 0" class="text-center py-6 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <Icon name="laptop" :size="32" class="mx-auto text-gray-300 mb-1" />
                    <p class="text-sm text-gray-500">Sin portátiles</p>
                  </div>
                  <div v-else class="space-y-2">
                    <div
                      v-for="portatil in portatilesList"
                      :key="portatil.id || portatil.portatil_id"
                      class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800"
                    >
                      <p class="font-semibold text-gray-900 dark:text-white text-sm">
                        {{ portatil.marca || 'N/A' }} {{ portatil.modelo || '' }}
                      </p>
                      <p class="text-xs text-gray-600 dark:text-gray-400 font-mono">
                        S/N: {{ portatil.serial || 'N/A' }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Vehículos -->
                <div>
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                      <Icon name="car" :size="18" class="text-green-600" />
                      Vehículos
                      <span class="text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-full">
                        {{ vehiculosList.length }}
                      </span>
                    </h3>
                  </div>
                  <div v-if="vehiculosList.length === 0" class="text-center py-6 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <Icon name="car" :size="32" class="mx-auto text-gray-300 mb-1" />
                    <p class="text-sm text-gray-500">Sin vehículos</p>
                  </div>
                  <div v-else class="space-y-2">
                    <div
                      v-for="vehiculo in vehiculosList"
                      :key="vehiculo.id || vehiculo.vehiculo_id"
                      class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-200 dark:border-green-800"
                    >
                      <p class="font-semibold text-gray-900 dark:text-white text-sm">
                        {{ vehiculo.tipo || 'Vehículo' }}
                      </p>
                      <p class="text-xs text-gray-600 dark:text-gray-400 font-bold">
                        🚗 {{ vehiculo.placa || 'N/A' }}
                      </p>
                      <p v-if="vehiculo.marca || vehiculo.modelo" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ vehiculo.marca }} {{ vehiculo.modelo }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-900">
            <div class="flex justify-end">
              <button
                @click="close"
                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors font-medium"
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal de Vista Previa del QR -->
    <Transition name="modal">
      <div
        v-if="showQrPreview && qrImageUrl"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70"
        @click.self="closeQrPreview"
      >
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6">
          <!-- Header -->
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
              Código QR - {{ personaData.Nombre }}
            </h3>
            <button
              @click="closeQrPreview"
              class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            >
              <Icon name="x" :size="20" class="text-gray-500" />
            </button>
          </div>
          
          <!-- Imagen QR Grande -->
          <div class="flex justify-center mb-4">
            <img 
              :src="qrImageUrl" 
              :alt="`QR de ${personaData.Nombre}`"
              class="w-64 h-64 border-4 border-gray-200 dark:border-gray-700 rounded-xl shadow-lg"
            />
          </div>
          
          <!-- Info -->
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
              <span class="font-semibold text-gray-900 dark:text-white">{{ personaData.Nombre }}</span><br>
              Documento: {{ personaData.documento || 'N/A' }}
            </p>
          </div>
          
          <!-- Botón descargar grande -->
          <button
            @click="downloadQr"
            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-[#39A900] hover:bg-[#2d7f00] text-white rounded-lg transition-colors font-semibold"
          >
            <Icon name="download" :size="20" />
            Descargar Código QR
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .bg-white,
.modal-leave-active .bg-white {
  transition: transform 0.3s ease;
}

.modal-enter-from .bg-white,
.modal-leave-to .bg-white {
  transform: scale(0.95);
}
</style>
