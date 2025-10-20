<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const isLoading = ref(false)
let axiosRequestCount = 0

// ==========================================
// 🔥 INTERCEPTORES DE INERTIA
// ==========================================
const handleInertiaStart = () => {
  isLoading.value = true
}

const handleInertiaFinish = () => {
  // Pequeño delay para que se vea el loader
  setTimeout(() => {
    if (axiosRequestCount === 0) {
      isLoading.value = false
    }
  }, 200)
}

// ==========================================
// 🔥 INTERCEPTORES DE AXIOS
// ==========================================
const setupAxiosInterceptors = () => {
  // REQUEST: Mostrar loader cuando inicia una petición
  window.axios.interceptors.request.use(
    (config) => {
      axiosRequestCount++
      isLoading.value = true
      return config
    },
    (error) => {
      axiosRequestCount--
      if (axiosRequestCount === 0) {
        isLoading.value = false
      }
      return Promise.reject(error)
    }
  )

  // RESPONSE: Ocultar loader cuando termina
  window.axios.interceptors.response.use(
    (response) => {
      axiosRequestCount--
      if (axiosRequestCount === 0) {
        setTimeout(() => {
          isLoading.value = false
        }, 200)
      }
      return response
    },
    (error) => {
      axiosRequestCount--
      if (axiosRequestCount === 0) {
        setTimeout(() => {
          isLoading.value = false
        }, 200)
      }
      return Promise.reject(error)
    }
  )
}

onMounted(() => {
  // Escuchar eventos de Inertia
  router.on('start', handleInertiaStart)
  router.on('finish', handleInertiaFinish)
  
  // Configurar interceptores de Axios
  setupAxiosInterceptors()
})
</script>

<template>
  <Transition
    enter-active-class="transition-opacity duration-200"
    leave-active-class="transition-opacity duration-300"
    enter-from-class="opacity-0"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isLoading"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
    >
      <!-- Loader Animado -->
      <div class="loader"></div>
    </div>
  </Transition>
</template>

<style scoped>
/* From Uiverse.io by kerolos23 - Adaptado para CTAccess */
.loader {
  transform: rotateZ(45deg);
  perspective: 1000px;
  border-radius: 50%;
  width: 48px;
  height: 48px;
  color: #00d9ff; /* Color primario CTAccess (azul cian) */
}

.loader:before,
.loader:after {
  content: '';
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  width: inherit;
  height: inherit;
  border-radius: 50%;
  transform: rotateX(70deg);
  animation: 1s spin linear infinite;
}

.loader:after {
  color: #00304D; /* Color secundario CTAccess (azul oscuro) */
  transform: rotateY(70deg);
  animation-delay: .4s;
}

@keyframes rotate {
  0% {
    transform: translate(-50%, -50%) rotateZ(0deg);
  }
  100% {
    transform: translate(-50%, -50%) rotateZ(360deg);
  }
}

@keyframes rotateccw {
  0% {
    transform: translate(-50%, -50%) rotate(0deg);
  }
  100% {
    transform: translate(-50%, -50%) rotate(-360deg);
  }
}

@keyframes spin {
  0%,
  100% {
    box-shadow: .2em 0px 0 0px currentcolor;
  }
  12% {
    box-shadow: .2em .2em 0 0 currentcolor;
  }
  25% {
    box-shadow: 0 .2em 0 0px currentcolor;
  }
  37% {
    box-shadow: -.2em .2em 0 0 currentcolor;
  }
  50% {
    box-shadow: -.2em 0 0 0 currentcolor;
  }
  62% {
    box-shadow: -.2em -.2em 0 0 currentcolor;
  }
  75% {
    box-shadow: 0px -.2em 0 0 currentcolor;
  }
  87% {
    box-shadow: .2em -.2em 0 0 currentcolor;
  }
}
</style>
