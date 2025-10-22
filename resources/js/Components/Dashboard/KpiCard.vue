<script setup>
import Icon from '@/Components/Icon.vue'

defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  color: {
    type: String,
    default: 'blue' // blue, green, yellow, red, purple, cyan
  },
  subtitle: {
    type: String,
    default: ''
  },
  trend: {
    type: Object,
    default: null // { value: 12, isPositive: true }
  }
})

const colorClasses = {
  blue: {
    gradient: 'from-blue-400 to-blue-600',
    bg: 'from-blue-500 to-blue-600',
    text: 'text-blue-600 dark:text-blue-400'
  },
  green: {
    gradient: 'from-sena-green-400 to-sena-green-600',
    bg: 'from-sena-green-600 to-sena-green-700',
    text: 'text-sena-green-600 dark:text-sena-green-400'
  },
  yellow: {
    gradient: 'from-sena-yellow-400 to-sena-yellow-600',
    bg: 'from-sena-yellow-500 to-sena-yellow-600',
    text: 'text-sena-yellow-600 dark:text-sena-yellow-400'
  },
  red: {
    gradient: 'from-red-400 to-red-600',
    bg: 'from-red-500 to-red-700',
    text: 'text-red-600 dark:text-red-400'
  },
  purple: {
    gradient: 'from-purple-400 to-purple-600',
    bg: 'from-purple-500 to-purple-600',
    text: 'text-purple-600 dark:text-purple-400'
  },
  cyan: {
    gradient: 'from-cyan-400 to-cyan-600',
    bg: 'from-cyan-500 to-cyan-600',
    text: 'text-cyan-600 dark:text-cyan-400'
  }
}
</script>

<template>
  <div class="group relative overflow-hidden rounded-lg border border-theme-primary bg-gradient-to-br from-theme-card to-theme-secondary p-2.5 shadow-theme-sm transition-all hover:shadow-theme-md">
    <!-- Fondo decorativo -->
    <div 
      class="absolute top-0 right-0 h-16 w-16 -translate-y-8 translate-x-8 rounded-full bg-gradient-to-br opacity-20"
      :class="colorClasses[color].gradient"
    ></div>
    
    <div class="relative">
      <!-- Header con icono y título -->
      <div class="flex items-center gap-1.5 mb-1">
        <div 
          class="flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br shadow-sm"
          :class="colorClasses[color].bg"
        >
          <Icon :name="icon" :size="14" class="text-white" />
        </div>
        <div class="text-xs font-medium text-theme-secondary leading-tight">{{ title }}</div>
      </div>
      
      <!-- Valor principal -->
      <div class="flex items-end gap-2">
        <div class="text-xl sm:text-2xl font-bold text-theme-primary">{{ value }}</div>
        
        <!-- Tendencia (opcional) -->
        <div v-if="trend" class="flex items-center gap-1 mb-1">
          <Icon 
            :name="trend.isPositive ? 'trending-up' : 'trending-down'" 
            :size="14" 
            :class="trend.isPositive ? 'text-green-500' : 'text-red-500'"
          />
          <span 
            class="text-xs font-medium"
            :class="trend.isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
          >
            {{ trend.value }}%
          </span>
        </div>
      </div>
      
      <!-- Subtítulo (opcional) -->
      <p v-if="subtitle" class="text-xs text-theme-muted mt-0.5">{{ subtitle }}</p>
    </div>
  </div>
</template>
