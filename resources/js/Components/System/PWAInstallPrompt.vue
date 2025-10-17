<script setup>
import { ref, onMounted } from 'vue'
import Icon from '@/Components/Icon.vue'
import { usePWA } from '@/composables/usePWA'

const { isInstallable, showInstallPrompt, installApp, dismissInstallPrompt } = usePWA()
</script>

<template>
  <div
    v-if="showInstallPrompt"
    class="fixed bottom-4 left-4 right-4 z-50 mx-auto max-w-sm transform transition-all duration-300 sm:left-auto sm:right-6 sm:max-w-sm"
  >
    <div class="overflow-hidden rounded-2xl border-2 border-theme-primary bg-theme-card p-4 shadow-theme-xl">
      <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-sena-green-600 to-sena-green-700 dark:from-cyan-600 dark:to-cyan-700">
          <Icon name="smartphone" :size="20" class="text-white" />
        </div>
        <div class="flex-1 min-w-0">
          <h3 class="text-sm font-semibold text-theme-primary">Instalar CTAccess</h3>
          <p class="text-xs text-theme-muted mt-1">
            Instala la aplicación en tu dispositivo para acceso rápido y offline.
          </p>
        </div>
        <button
          @click="dismissInstallPrompt"
          class="flex-shrink-0 rounded-lg p-1.5 text-theme-muted hover:bg-theme-secondary hover:text-theme-primary transition-all duration-200"
        >
          <Icon name="x" :size="16" />
        </button>
      </div>

      <div class="mt-4 flex gap-2">
        <button
          @click="installApp"
          class="flex-1 rounded-lg bg-gradient-to-r from-sena-green-600 to-sena-green-700 dark:from-cyan-600 dark:to-cyan-700 px-3 py-2 text-xs font-medium text-white transition-all hover:from-sena-green-700 hover:to-sena-green-800 dark:hover:from-cyan-700 dark:hover:to-cyan-800 focus:outline-none focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 shadow-theme-md hover:shadow-theme-lg"
        >
          Instalar
        </button>
        <button
          @click="dismissInstallPrompt"
          class="flex-1 rounded-lg border-2 border-theme-primary px-3 py-2 text-xs font-medium text-theme-primary bg-theme-secondary transition-colors hover:bg-theme-tertiary hover:border-sena-green-500 dark:hover:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-theme-secondary"
        >
          Ahora no
        </button>
      </div>
    </div>
  </div>
</template>