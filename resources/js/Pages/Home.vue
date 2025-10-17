<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, nextTick } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Icon from '@/Components/Icon.vue'
import PWAInstallPrompt from '@/Components/System/PWAInstallPrompt.vue'
import LineChart from '@/Components/Charts/LineChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue'
import { useTheme } from '@/composables/useTheme'

const props = defineProps({
  estadisticas: Object,
  sistema_info: Object
})

// Estado para actividad reciente
const recentActivity = ref([])
const loadingActivity = ref(true)

// � Estado para registros recientes de personas
const recentRegistrations = ref([])
const loadingRegistrations = ref(true)

// �📊 Estado para gráficos
const loadingCharts = ref(true)
const chartData = ref({
  accesosPorHora: null,
  ultimosSieteDias: null,
  distribucionHoy: null,
  tendenciaMes: null
})

// Tema
const { isDark, toggleTheme } = useTheme()

// 🔐 Estado para el botón de login con toque largo
const longPressTimer = ref(null)
const longPressProgress = ref(0)
const isLongPressing = ref(false)
const LONG_PRESS_DURATION = 3000 // 3 segundos

// 🎨 Datos para los cubitos animados - Color corporativo verde SENA
const statsData = [
  { color: '#39A900', icon: 'users', label: 'Usuarios', value: props.estadisticas?.total_personas || 0 },
  { color: '#39A900', icon: 'user-check', label: 'Activos Hoy', value: props.estadisticas?.accesos_hoy || 0 },
  { color: '#39A900', icon: 'log-in', label: 'Entradas', value: props.estadisticas?.entradas_hoy || 0 },
  { color: '#39A900', icon: 'log-out', label: 'Salidas', value: props.estadisticas?.salidas_hoy || 0 },
  { color: '#39A900', icon: 'clock', label: 'Promedio', value: props.estadisticas?.tiempo_promedio || '0h' },
  { color: '#39A900', icon: 'trending-up', label: 'Esta Semana', value: props.estadisticas?.accesos_semana || 0 },
  { color: '#39A900', icon: 'calendar', label: 'Este Mes', value: props.estadisticas?.accesos_mes || 0 },
  { color: '#39A900', icon: 'activity', label: 'En Vivo', value: props.estadisticas?.usuarios_dentro || 0 },
  { color: '#39A900', icon: 'alert-circle', label: 'Incidencias', value: props.estadisticas?.incidencias_mes || 0 },
  { color: '#39A900', icon: 'shield', label: 'Seguridad', value: '99%' }
]
const activeColorIndex = ref(0)

// Reloj Digital
const currentTime = ref(new Date())
const updateClock = () => {
  currentTime.value = new Date()
}

// Formatear hora
const formatTime = (date) => {
  return date.toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  })
}

// Formatear fecha
const formatDate = (date) => {
  return date.toLocaleDateString('es-ES', {
    weekday: 'short',
    day: '2-digit',
    month: 'short'
  })
}

// Configurar WebSocket y cargar datos
onMounted(() => {
  // Cargar actividad reciente inicial
  fetchRecentActivity()
  
  // � Cargar registros recientes de personas
  fetchRecentRegistrations()
  
  // �📊 Cargar datos de gráficos
  fetchChartData()

  // Iniciar reloj
  setInterval(updateClock, 1000)

  // 🎨 Animación automática de colores (cada 3 segundos)
  setInterval(() => {
    activeColorIndex.value = (activeColorIndex.value + 1) % statsData.length
  }, 3000)

  // 🔥 WEBSOCKET: Escuchar nuevos accesos en tiempo real
  if (typeof window.Echo !== 'undefined') {
    window.Echo.channel('accesos')
      .listen('.acceso.registrado', (data) => {
        console.log('Nuevo acceso registrado:', data)

        const newAccess = {
          id: data.id,
          persona: data.persona.nombre,
          documento: data.persona.documento,
          tipo: data.tipo_acceso,
          tiempo: new Date(data.timestamp),
          isNew: true // Marcador para animación
        }

        // Agregar al inicio de la actividad reciente
        recentActivity.value.unshift(newAccess)

        // Mantener solo los últimos 15
        if (recentActivity.value.length > 15) {
          recentActivity.value = recentActivity.value.slice(0, 15)
        }

        // Quitar el marcador "isNew" después de 5 segundos
        setTimeout(() => {
          const index = recentActivity.value.findIndex(a => a.id === newAccess.id)
          if (index !== -1) {
            recentActivity.value[index].isNew = false
          }
        }, 5000)
        
        // 📊 Actualizar gráficos cuando hay nuevo acceso
        fetchChartData()
      })
  } else {
    console.warn('⚠️ Laravel Echo no está disponible. WebSockets deshabilitados.')
  }
  
  // 👥 WEBSOCKET: Escuchar nuevos registros de personas
  if (typeof window.Echo !== 'undefined') {
    window.Echo.channel('personas')
      .listen('.persona.registrada', (data) => {
        console.log('Nueva persona registrada:', data)

        const newRegistration = {
          id: data.id,
          nombre: data.nombre,
          documento: data.documento,
          tipo_persona: data.tipo_persona,
          correo: data.correo,
          tiempo: new Date(data.timestamp),
          isNew: true
        }

        // Agregar al inicio de registros recientes
        recentRegistrations.value.unshift(newRegistration)

        // Mantener solo los últimos 15
        if (recentRegistrations.value.length > 15) {
          recentRegistrations.value = recentRegistrations.value.slice(0, 15)
        }

        // Quitar el marcador "isNew" después de 5 segundos
        setTimeout(() => {
          const index = recentRegistrations.value.findIndex(r => r.id === newRegistration.id)
          if (index !== -1) {
            recentRegistrations.value[index].isNew = false
          }
        }, 5000)
      })
  }
})

// 🔥 Actividad en Tiempo Real con WebSockets
const fetchRecentActivity = async () => {
  try {
    const response = await fetch('/api/accesos/recientes')
    const data = await response.json()
    recentActivity.value = data
    loadingActivity.value = false
  } catch (error) {
    console.error('Error fetching recent activity:', error)
    loadingActivity.value = false
  }
}

// 👥 Registros Recientes de Personas
const fetchRecentRegistrations = async () => {
  try {
    const response = await fetch('/api/personas/recientes')
    const data = await response.json()
    recentRegistrations.value = data
    loadingRegistrations.value = false
  } catch (error) {
    console.error('Error fetching recent registrations:', error)
    loadingRegistrations.value = false
  }
}

// 📊 Obtener datos para los gráficos
const fetchChartData = async () => {
  try {
    const response = await fetch('/api/analytics/charts')
    const data = await response.json()
    
    // Gráfico de línea: Accesos por hora
    chartData.value.accesosPorHora = {
      labels: data.accesos_por_hora.labels,
      datasets: [{
        label: 'Accesos por Hora',
        data: data.accesos_por_hora.data,
        borderColor: isDark.value ? 'rgb(34, 211, 238)' : 'rgb(57, 169, 0)',
        backgroundColor: isDark.value ? 'rgba(34, 211, 238, 0.1)' : 'rgba(57, 169, 0, 0.1)',
        tension: 0.4,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointBackgroundColor: isDark.value ? 'rgb(34, 211, 238)' : 'rgb(57, 169, 0)',
        borderWidth: 2
      }]
    }
    
    // Gráfico de barras: Últimos 7 días
    chartData.value.ultimosSieteDias = {
      labels: data.ultimos_siete_dias.labels,
      datasets: [
        {
          label: 'Entradas',
          data: data.ultimos_siete_dias.entradas,
          backgroundColor: isDark.value ? 'rgba(34, 211, 238, 0.8)' : 'rgba(57, 169, 0, 0.8)',
          borderColor: isDark.value ? 'rgb(34, 211, 238)' : 'rgb(57, 169, 0)',
          borderWidth: 2
        },
        {
          label: 'Salidas',
          data: data.ultimos_siete_dias.salidas,
          backgroundColor: isDark.value ? 'rgba(239, 68, 68, 0.8)' : 'rgba(220, 38, 38, 0.8)',
          borderColor: isDark.value ? 'rgb(239, 68, 68)' : 'rgb(220, 38, 38)',
          borderWidth: 2
        }
      ]
    }
    
    // Gráfico de dona: Distribución hoy
    chartData.value.distribucionHoy = {
      labels: data.distribucion_hoy.labels,
      datasets: [{
        data: data.distribucion_hoy.data,
        backgroundColor: [
          isDark.value ? 'rgba(34, 211, 238, 0.8)' : 'rgba(57, 169, 0, 0.8)',
          isDark.value ? 'rgba(239, 68, 68, 0.8)' : 'rgba(220, 38, 38, 0.8)',
          isDark.value ? 'rgba(253, 224, 71, 0.8)' : 'rgba(253, 195, 0, 0.8)'
        ],
        borderColor: [
          isDark.value ? 'rgb(34, 211, 238)' : 'rgb(57, 169, 0)',
          isDark.value ? 'rgb(239, 68, 68)' : 'rgb(220, 38, 38)',
          isDark.value ? 'rgb(253, 224, 71)' : 'rgb(253, 195, 0)'
        ],
        borderWidth: 3
      }]
    }
    
    // Gráfico de línea: Tendencia del mes
    chartData.value.tendenciaMes = {
      labels: data.tendencia_mes.labels,
      datasets: [{
        label: 'Accesos Diarios',
        data: data.tendencia_mes.data,
        borderColor: isDark.value ? 'rgb(34, 211, 238)' : 'rgb(57, 169, 0)',
        backgroundColor: isDark.value ? 'rgba(34, 211, 238, 0.2)' : 'rgba(57, 169, 0, 0.2)',
        tension: 0.4,
        fill: true,
        pointRadius: 4,
        pointHoverRadius: 7,
        pointBackgroundColor: isDark.value ? 'rgb(34, 211, 238)' : 'rgb(57, 169, 0)',
        borderWidth: 3
      }]
    }
    
    loadingCharts.value = false
  } catch (error) {
    console.error('Error fetching chart data:', error)
    loadingCharts.value = false
  }
}


// Formatear tiempo relativo
const formatRelativeTime = (date) => {
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)

  if (minutes < 1) return 'Ahora'
  if (minutes < 60) return `${minutes}m`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h`
  return `${Math.floor(hours / 24)}d`
}

// 🔐 Funciones para el toque largo en el botón de login
const handleLoginPressStart = (event) => {
  event.preventDefault() // Prevenir comportamiento por defecto
  isLongPressing.value = true
  longPressProgress.value = 0
  
  // Vibración inicial suave (si está disponible)
  if (navigator.vibrate) {
    navigator.vibrate(10)
  }
  
  // Iniciar animación de progreso
  const startTime = Date.now()
  
  longPressTimer.value = setInterval(() => {
    const elapsed = Date.now() - startTime
    longPressProgress.value = (elapsed / LONG_PRESS_DURATION) * 100
    
    // Vibración a mitad de camino
    if (longPressProgress.value >= 50 && longPressProgress.value < 55) {
      if (navigator.vibrate) {
        navigator.vibrate(15)
      }
    }
    
    // Si se completa el tiempo, redirigir al login del sistema
    if (elapsed >= LONG_PRESS_DURATION) {
      handleLoginPressComplete()
    }
  }, 16) // ~60fps
}

const handleLoginPressEnd = (event) => {
  if (!isLongPressing.value) return
  
  // Si no se completó el tiempo, hacer click normal
  if (longPressProgress.value < 100) {
    // Limpiar timer
    if (longPressTimer.value) {
      clearInterval(longPressTimer.value)
      longPressTimer.value = null
    }
    
    // Reset estado
    isLongPressing.value = false
    longPressProgress.value = 0
    
    // Redirigir al login normal (este evento se propagará naturalmente al Link)
    return
  }
}

const handleLoginPressComplete = () => {
  // Vibración de éxito (patrón: corto-largo-corto)
  if (navigator.vibrate) {
    navigator.vibrate([30, 50, 30, 50, 50])
  }
  
  // Limpiar timer
  if (longPressTimer.value) {
    clearInterval(longPressTimer.value)
    longPressTimer.value = null
  }
  
  // Reset estado
  isLongPressing.value = false
  longPressProgress.value = 0
  
  // Redirigir al login del sistema
  window.location.href = '/system/login'
}

const handleLoginPressCancel = () => {
  if (longPressTimer.value) {
    clearInterval(longPressTimer.value)
    longPressTimer.value = null
  }
  isLongPressing.value = false
  longPressProgress.value = 0
}
</script>

<template>

  <Head title="CTAccess - Sistema de Control de Acceso" />

  <div class="min-h-screen bg-theme-primary text-theme-primary flex flex-col">
    <!-- Header fijo -->
    <header class="bg-theme-navbar border-b border-theme-primary px-4 py-3 flex-shrink-0 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center gap-4">
          <div class="relative">
            <ApplicationLogo alt="CTAccess Logo" classes="h-12 w-auto object-contain" />
          </div>

          <!-- Reloj Digital -->
          <div
            class="hidden md:flex items-center gap-2 bg-theme-secondary border-2 border-theme-primary rounded-lg px-3 py-1.5 shadow-theme-md">
            <Icon name="clock" :size="16" class="text-sena-green-600 dark:text-cyan-400" />
            <div class="flex flex-col">
              <div class="text-theme-primary font-bold text-sm tabular-nums leading-tight digital-clock">
                {{ formatTime(currentTime) }}
              </div>
              <div class="text-theme-muted text-[10px] font-medium uppercase leading-tight">
                {{ formatDate(currentTime) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Navegación -->
        <nav class="flex gap-2 items-center">
          <!-- Theme toggle button -->
          <button @click="toggleTheme"
            class="rounded-md p-2 text-theme-muted hover:bg-theme-tertiary hover:text-theme-secondary focus:outline-none focus:ring-2 focus:ring-green-500"
            :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'">
            <Icon :name="isDark ? 'sun' : 'moon'" :size="20" />
          </button>

          <template v-if="$page.props.auth && $page.props.auth.user">
            <Link :href="route('dashboard')"
              class="flex items-center gap-2 px-3 py-2 text-theme-primary border border-theme-primary rounded-lg hover:bg-theme-tertiary transition-all duration-200">
            <Icon name="home" :size="16" />
            <span class="hidden sm:inline">Panel</span>
            </Link>
          </template>
          <template v-else>
            <!-- 🔐 Botón con Toque Largo: Click normal = login usuarios | Mantener 3s = login sistema -->
            <Link 
              :href="route('login')"
              @mousedown="handleLoginPressStart"
              @mouseup="handleLoginPressEnd"
              @mouseleave="handleLoginPressCancel"
              @touchstart="handleLoginPressStart"
              @touchend="handleLoginPressEnd"
              @touchcancel="handleLoginPressCancel"
              :class="[
                'relative flex items-center gap-2 px-3 py-2 border rounded-lg transition-all duration-200 overflow-hidden select-none',
                isLongPressing 
                  ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300' 
                  : 'text-theme-primary border-theme-primary hover:bg-theme-tertiary'
              ]"
              :title="'Click normal: Iniciar sesión | Mantén presionado 3s: Acceso al Sistema (Admin/Celador)'">
              
              <!-- Barra de progreso circular -->
              <div 
                v-if="isLongPressing" 
                class="absolute inset-0 bg-gradient-to-r from-amber-400/20 via-amber-500/20 to-amber-600/20 dark:from-amber-500/30 dark:via-amber-400/30 dark:to-amber-300/30"
                :style="{ width: `${longPressProgress}%` }"
              ></div>
              
              <!-- Contenido del botón -->
              <div class="relative z-10 flex items-center gap-2">
                <Icon 
                  :name="isLongPressing ? 'shield' : 'log-in'" 
                  :size="16" 
                  :class="{ 'animate-pulse': isLongPressing }"
                />
                <span class="hidden sm:inline font-medium">
                  {{ isLongPressing 
                    ? `Sistema ${Math.floor(longPressProgress / 33)}...` 
                    : 'Iniciar Sesión' 
                  }}
                </span>
              </div>
            </Link>
            <Link :href="route('personas.create')"
              class="flex items-center gap-2 px-4 py-2 bg-sena-green-600 hover:bg-sena-green-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded-lg transition-all duration-200 font-medium shadow-theme-sm hover:shadow-theme-md">
            <Icon name="user-plus" :size="16" />
            Registrarse
            </Link>
          </template>
        </nav>
      </div>
    </header>

    <!-- Contenido principal -->
    <main class="flex-1 px-4 py-2">
      <div class="max-w-7xl mx-auto flex flex-col">

        <!-- 🎨 Stats Cubitos Animados - Solo Desktop -->
        <div class="hidden lg:block w-full mb-6 mt-4 px-4 flex-shrink-0">
          <div class="container-items">
            <button v-for="(stat, index) in statsData" :key="index" class="item-color"
              :class="{ 'active-color': activeColorIndex === index }" :style="{ '--color': stat.color }"
              :aria-label="stat.label">
              <!-- Contenedor interno del cubito -->
              <div class="item-inner">
                <!-- Icono -->
                <div class="icon-container">
                  <Icon :name="stat.icon" :size="18" />
                </div>

                <!-- Datos que aparecen cuando está activo -->
                <div class="stat-data" :class="{ 'stat-visible': activeColorIndex === index }">
                  <div class="stat-value">{{ stat.value }}</div>
                  <div class="stat-label">{{ stat.label }}</div>
                </div>
              </div>
            </button>
          </div>
        </div>

        <!-- Grid de Contenido -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

          <!-- Columna 1: Actividad en Tiempo Real (2 columnas en large) -->
          <div class="lg:col-span-2 relative flex-shrink-0">
            
            <!-- �️ VERSIÓN DESKTOP - Título Diagonal -->
            <div class="hidden lg:block relative w-full h-full">
              <!-- Título Diagonal en Esquina -->
              <div class="absolute -top-12 -left-6 z-10">
                <div
                  class="bg-sena-green-700 dark:bg-blue-900 px-6 py-3 rounded-xl shadow-theme-xl border-2 border-sena-green-600 dark:border-blue-800 transform -rotate-2 hover:rotate-0 transition-transform duration-300">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-9 h-9 bg-sena-green-600 dark:bg-blue-800 rounded-lg flex items-center justify-center relative">
                      <Icon name="activity" :size="18" class="text-white" />
                      <div
                        class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-cyan-400 rounded-full border-2 border-sena-green-700 dark:border-blue-900 live-indicator">
                      </div>
                    </div>
                    <div>
                      <h3 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                        Actividad reciente
                        <span class="text-xs font-normal text-gray-200 dark:text-cyan-200">{{ recentActivity.length }} registros</span>
                      </h3>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Contenedor de la Tabla Desktop -->
              <div class="bg-theme-card border-2 border-theme-primary rounded-xl shadow-theme-xl overflow-hidden mt-12 w-fit">
                <!-- Feed de Registros Compacto con altura fija -->
                <div class="p-2 bg-theme-secondary">
                  <div class="space-y-1.5 h-[516px] overflow-y-auto custom-scrollbar pr-1">

                    <!-- Loading State -->
                    <template v-if="loadingActivity">
                      <div v-for="i in 4" :key="i"
                        class="flex items-center gap-2 p-1.5 bg-theme-card border border-theme-primary rounded-lg animate-pulse w-fit min-w-[400px]">
                        <div class="w-8 h-8 bg-theme-tertiary rounded-lg"></div>
                        <div class="flex-1 space-y-1">
                          <div class="h-2.5 bg-theme-tertiary rounded w-32"></div>
                          <div class="h-2 bg-theme-tertiary rounded w-24"></div>
                        </div>
                        <div class="w-9 h-6 bg-theme-tertiary rounded"></div>
                      </div>
                    </template>

                    <!-- Lista de Actividades -->
                    <template v-else-if="recentActivity.length > 0">
                      <transition-group name="spotlight">
                        <div v-for="activity in recentActivity" :key="activity.id" :class="[
                          'relative flex items-center gap-2 p-1.5 rounded-lg border-2 transition-all duration-500 w-fit min-w-[400px]',
                          activity.isNew
                            ? 'bg-sena-yellow-50 dark:bg-sena-yellow-900/20 border-sena-yellow-400 dark:border-sena-yellow-600 spotlight-card'
                            : 'bg-theme-card border-theme-primary hover:border-theme-hover hover:shadow-theme-lg',
                          'cursor-pointer group'
                        ]">
                          <!-- Spotlight Effect para nuevos (4 esquinas brillantes) -->
                          <div v-if="activity.isNew" class="corner-spotlight top-left"></div>
                          <div v-if="activity.isNew" class="corner-spotlight top-right"></div>
                          <div v-if="activity.isNew" class="corner-spotlight bottom-left"></div>
                          <div v-if="activity.isNew" class="corner-spotlight bottom-right"></div>

                          <!-- Avatar Mate con Icono -->
                          <div class="relative flex-shrink-0">
                            <div :class="[
                              'w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold border-2 transition-all duration-300',
                              activity.tipo === 'entrada'
                                ? 'bg-sena-green-600 border-sena-green-700 dark:bg-cyan-600 dark:border-cyan-500'
                                : 'bg-red-600 border-red-700 dark:bg-red-500 dark:border-red-600',
                              activity.isNew ? 'scale-110 shake' : 'group-hover:scale-105'
                            ]">
                              <Icon :name="activity.tipo === 'entrada' ? 'log-in' : 'log-out'" :size="14" />
                            </div>
                            <!-- Notificación Badge Animado -->
                            <div v-if="activity.isNew"
                              class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-sena-yellow-400 border-2 border-white dark:border-gray-900 rounded-full flex items-center justify-center notification-badge">
                              <span class="text-[7px] font-black text-sena-yellow-900">!</span>
                            </div>
                          </div>

                          <!-- Información Compacta -->
                          <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-0.5">
                              <p :class="[
                                'font-bold text-[13px] truncate',
                                activity.isNew ? 'text-sena-yellow-900 dark:text-sena-yellow-200' : 'text-theme-primary'
                              ]">
                                {{ activity.persona }}
                              </p>
                              <!-- Badge "NUEVO" llamativo -->
                              <span v-if="activity.isNew"
                                class="px-1.5 py-0.5 bg-sena-yellow-400 text-sena-yellow-900 text-[8px] font-black rounded uppercase tracking-wider border border-sena-yellow-500 blink-badge">
                                ¡Nuevo!
                              </span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[10px]">
                              <Icon name="credit-card" :size="9"
                                :class="activity.isNew ? 'text-sena-yellow-700 dark:text-sena-yellow-400' : 'text-theme-muted'" />
                              <span
                                :class="activity.isNew ? 'text-sena-yellow-800 dark:text-sena-yellow-300 font-semibold' : 'text-theme-muted'">
                                {{ activity.documento }}
                              </span>
                              <span class="text-theme-muted text-[8px]">•</span>
                              <Icon name="clock" :size="9"
                                :class="activity.isNew ? 'text-sena-yellow-700 dark:text-sena-yellow-400' : 'text-theme-muted'" />
                              <span :class="[
                                'font-semibold',
                                activity.isNew ? 'text-sena-yellow-800 dark:text-sena-yellow-300' : 'text-theme-muted'
                              ]">
                                {{ formatRelativeTime(activity.tiempo) }}
                              </span>
                            </div>
                          </div>

                          <!-- Badge de Estado Ultra Compacto -->
                          <div class="flex-shrink-0">
                            <div :class="[
                              'px-1.5 py-0.5 rounded text-[9px] font-black uppercase border-2 transition-transform duration-300',
                              activity.tipo === 'entrada'
                                ? 'bg-sena-green-600 text-white border-sena-green-700 dark:bg-cyan-600 dark:border-cyan-500'
                                : 'bg-red-600 text-white border-red-700 dark:bg-red-500 dark:border-red-600',
                              'group-hover:scale-105'
                            ]">
                              <div class="flex items-center gap-0.5">
                                <div :class="[
                                  'w-1 h-1 rounded-full bg-white',
                                  activity.isNew ? 'pulse-dot' : ''
                                ]"></div>
                                {{ activity.tipo === 'entrada' ? 'IN' : 'OUT' }}
                              </div>
                            </div>
                          </div>
                        </div>
                      </transition-group>
                    </template>

                    <!-- Empty State -->
                    <template v-else>
                      <div class="text-center py-12 text-theme-muted">
                        <div
                          class="w-14 h-14 mx-auto mb-3 bg-theme-tertiary rounded-xl flex items-center justify-center border-2 border-theme-primary">
                          <Icon name="inbox" :size="28" class="opacity-40" />
                        </div>
                        <p class="text-sm font-bold">Sin actividad reciente</p>
                        <p class="text-xs mt-1 opacity-70">Los accesos aparecerán aquí automáticamente</p>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </div>

            <!-- 📱 VERSIÓN MÓVIL - Título Dentro y Compacta -->
            <div class="lg:hidden">
              <div class="bg-theme-card border-2 border-theme-primary rounded-xl shadow-theme-lg overflow-hidden">
                <!-- Encabezado Compacto -->
                <div class="bg-sena-green-600 dark:bg-blue-700 px-3 py-2.5 flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-sena-green-700 dark:bg-blue-800 rounded-lg flex items-center justify-center relative">
                      <Icon name="activity" :size="14" class="text-white" />
                      <div class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-cyan-400 rounded-full border border-sena-green-700 dark:border-blue-900 live-indicator"></div>
                    </div>
                    <h3 class="text-sm font-bold text-white">Actividad reciente</h3>
                  </div>
                  <span class="text-xs font-medium text-white/80 bg-white/10 px-2 py-0.5 rounded-full">
                    {{ recentActivity.length }}
                  </span>
                </div>

                <!-- Lista Compacta -->
                <div class="p-2 bg-theme-secondary">
                  <div class="space-y-1.5 max-h-[400px] overflow-y-auto custom-scrollbar">
                    
                    <!-- Loading State Móvil -->
                    <template v-if="loadingActivity">
                      <div v-for="i in 3" :key="i" class="flex items-center gap-2 p-2 bg-theme-card border border-theme-primary rounded-lg animate-pulse">
                        <div class="w-7 h-7 bg-theme-tertiary rounded-lg"></div>
                        <div class="flex-1 space-y-1">
                          <div class="h-2 bg-theme-tertiary rounded w-24"></div>
                          <div class="h-2 bg-theme-tertiary rounded w-16"></div>
                        </div>
                        <div class="w-8 h-5 bg-theme-tertiary rounded"></div>
                      </div>
                    </template>

                    <!-- Lista de Actividades Móvil -->
                    <template v-else-if="recentActivity.length > 0">
                      <div v-for="activity in recentActivity.slice(0, 8)" :key="activity.id" :class="[
                        'flex items-center gap-2 p-2 rounded-lg border transition-all duration-300',
                        activity.isNew
                          ? 'bg-sena-yellow-50 dark:bg-sena-yellow-900/20 border-sena-yellow-400 dark:border-sena-yellow-600'
                          : 'bg-theme-card border-theme-primary hover:border-theme-hover'
                      ]">
                        <!-- Avatar Compacto -->
                        <div :class="[
                          'w-7 h-7 rounded-lg flex items-center justify-center text-white font-bold border transition-all',
                          activity.tipo === 'entrada'
                            ? 'bg-sena-green-600 border-sena-green-700 dark:bg-cyan-600 dark:border-cyan-500'
                            : 'bg-red-600 border-red-700 dark:bg-red-500 dark:border-red-600'
                        ]">
                          <Icon :name="activity.tipo === 'entrada' ? 'log-in' : 'log-out'" :size="12" />
                        </div>

                        <!-- Información -->
                        <div class="flex-1 min-w-0">
                          <p :class="[
                            'font-bold text-xs truncate',
                            activity.isNew ? 'text-sena-yellow-900 dark:text-sena-yellow-200' : 'text-theme-primary'
                          ]">
                            {{ activity.persona }}
                          </p>
                          <div class="flex items-center gap-1 text-[10px] text-theme-muted">
                            <Icon name="clock" :size="8" />
                            <span>{{ formatRelativeTime(activity.tiempo) }}</span>
                          </div>
                        </div>

                        <!-- Badge Estado -->
                        <div :class="[
                          'px-1.5 py-0.5 rounded text-[8px] font-black uppercase border',
                          activity.tipo === 'entrada'
                            ? 'bg-sena-green-600 text-white border-sena-green-700 dark:bg-cyan-600 dark:border-cyan-500'
                            : 'bg-red-600 text-white border-red-700'
                        ]">
                          {{ activity.tipo === 'entrada' ? 'IN' : 'OUT' }}
                        </div>
                      </div>
                    </template>

                    <!-- Empty State Móvil -->
                    <template v-else>
                      <div class="text-center py-8 text-theme-muted">
                        <div class="w-12 h-12 mx-auto mb-2 bg-theme-tertiary rounded-lg flex items-center justify-center border border-theme-primary">
                          <Icon name="inbox" :size="24" class="opacity-40" />
                        </div>
                        <p class="text-xs font-semibold">Sin actividad reciente</p>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Columna 3: Registros Recientes de Personas (1 columna en large) -->
          <div class="lg:col-span-1 relative flex-shrink-0">
            
            <!-- VERSIÓN DESKTOP Y MÓVIL UNIFICADA -->
            <div class="bg-theme-card border-2 border-theme-primary rounded-xl shadow-theme-lg overflow-hidden">
              <!-- Encabezado -->
              <div class="bg-purple-600 dark:bg-purple-700 px-3 py-2.5 lg:px-4 lg:py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 lg:w-8 lg:h-8 bg-purple-700 dark:bg-purple-800 rounded-lg flex items-center justify-center relative">
                    <Icon name="user-plus" :size="14" class="text-white lg:hidden" />
                    <Icon name="user-plus" :size="16" class="text-white hidden lg:block" />
                    <div class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-yellow-400 rounded-full border border-purple-700 dark:border-purple-900 live-indicator"></div>
                  </div>
                  <div>
                    <h3 class="text-sm lg:text-base font-bold text-white">Registros</h3>
                    <p class="text-[10px] text-white/70">Nuevas personas</p>
                  </div>
                </div>
                <span class="text-xs font-medium text-white/80 bg-white/10 px-2 py-0.5 rounded-full">
                  {{ recentRegistrations.length }}
                </span>
              </div>

              <!-- Lista de Registros -->
              <div class="p-2 bg-theme-secondary">
                <div class="space-y-1.5 h-[516px] lg:h-[516px] overflow-y-auto custom-scrollbar pr-1">
                  
                  <!-- Loading State -->
                  <template v-if="loadingRegistrations">
                    <div v-for="i in 4" :key="i" class="flex items-center gap-2 p-2 bg-theme-card border border-theme-primary rounded-lg animate-pulse">
                      <div class="w-8 h-8 bg-theme-tertiary rounded-lg"></div>
                      <div class="flex-1 space-y-1">
                        <div class="h-2.5 bg-theme-tertiary rounded w-32"></div>
                        <div class="h-2 bg-theme-tertiary rounded w-24"></div>
                      </div>
                      <div class="w-16 h-5 bg-theme-tertiary rounded"></div>
                    </div>
                  </template>

                  <!-- Lista de Registros -->
                  <template v-else-if="recentRegistrations.length > 0">
                    <transition-group name="spotlight">
                      <div v-for="registro in recentRegistrations" :key="registro.id" :class="[
                        'relative flex items-center gap-2 p-2 rounded-lg border-2 transition-all duration-500',
                        registro.isNew
                          ? 'bg-purple-50 dark:bg-purple-900/20 border-purple-400 dark:border-purple-600 spotlight-card'
                          : 'bg-theme-card border-theme-primary hover:border-theme-hover hover:shadow-theme-md',
                        'cursor-pointer group'
                      ]">
                        <!-- Spotlight corners para nuevos -->
                        <div v-if="registro.isNew" class="corner-spotlight top-left"></div>
                        <div v-if="registro.isNew" class="corner-spotlight top-right"></div>
                        <div v-if="registro.isNew" class="corner-spotlight bottom-left"></div>
                        <div v-if="registro.isNew" class="corner-spotlight bottom-right"></div>

                        <!-- Avatar con Badge -->
                        <div class="relative flex-shrink-0">
                          <div :class="[
                            'w-8 h-8 lg:w-9 lg:h-9 rounded-lg flex items-center justify-center text-white font-bold border-2 transition-all duration-300',
                            'bg-purple-600 border-purple-700 dark:bg-purple-600 dark:border-purple-500',
                            registro.isNew ? 'scale-110 shake' : 'group-hover:scale-105'
                          ]">
                            <Icon name="user" :size="14" class="lg:hidden" />
                            <Icon name="user" :size="16" class="hidden lg:block" />
                          </div>
                          <div v-if="registro.isNew"
                            class="absolute -top-0.5 -right-0.5 w-3 h-3 lg:w-3.5 lg:h-3.5 bg-yellow-400 border-2 border-white dark:border-gray-900 rounded-full flex items-center justify-center notification-badge">
                            <span class="text-[7px] font-black text-purple-900">!</span>
                          </div>
                        </div>

                        <!-- Información -->
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-1.5 mb-0.5">
                            <p :class="[
                              'font-bold text-xs lg:text-[13px] truncate',
                              registro.isNew ? 'text-purple-900 dark:text-purple-200' : 'text-theme-primary'
                            ]">
                              {{ registro.nombre }}
                            </p>
                            <span v-if="registro.isNew"
                              class="px-1.5 py-0.5 bg-yellow-400 text-purple-900 text-[8px] font-black rounded uppercase tracking-wider border border-yellow-500 blink-badge">
                              ¡Nuevo!
                            </span>
                          </div>
                          <div class="flex items-center gap-1.5 text-[10px]">
                            <Icon name="briefcase" :size="9" 
                              :class="registro.isNew ? 'text-purple-700 dark:text-purple-400' : 'text-theme-muted'" />
                            <span
                              :class="registro.isNew ? 'text-purple-800 dark:text-purple-300 font-semibold' : 'text-theme-muted'">
                              {{ registro.tipo_persona }}
                            </span>
                            <span class="text-theme-muted text-[8px]">•</span>
                            <Icon name="clock" :size="9"
                              :class="registro.isNew ? 'text-purple-700 dark:text-purple-400' : 'text-theme-muted'" />
                            <span :class="[
                              'font-semibold',
                              registro.isNew ? 'text-purple-800 dark:text-purple-300' : 'text-theme-muted'
                            ]">
                              {{ formatRelativeTime(registro.tiempo) }}
                            </span>
                          </div>
                        </div>

                        <!-- Badge de tipo -->
                        <div class="flex-shrink-0">
                          <div :class="[
                            'px-1.5 py-0.5 rounded text-[9px] font-black uppercase border-2 transition-transform duration-300',
                            'bg-purple-600 text-white border-purple-700 dark:bg-purple-600 dark:border-purple-500',
                            'group-hover:scale-105'
                          ]">
                            <div class="flex items-center gap-0.5">
                              <div :class="[
                                'w-1 h-1 rounded-full bg-white',
                                registro.isNew ? 'pulse-dot' : ''
                              ]"></div>
                              NEW
                            </div>
                          </div>
                        </div>
                      </div>
                    </transition-group>
                  </template>

                  <!-- Empty State -->
                  <template v-else>
                    <div class="text-center py-12 text-theme-muted">
                      <div class="w-14 h-14 mx-auto mb-3 bg-theme-tertiary rounded-xl flex items-center justify-center border-2 border-theme-primary">
                        <Icon name="user-plus" :size="28" class="opacity-40" />
                      </div>
                      <p class="text-sm font-bold">Sin registros recientes</p>
                      <p class="text-xs mt-1 opacity-70">Los nuevos registros aparecerán aquí</p>
                    </div>
                  </template>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- 📊 SECCIÓN DE GRÁFICOS ANALÍTICOS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          
          <!-- Gráfico 1: Accesos por Hora del Día -->
          <div class="bg-theme-card border-2 border-theme-primary rounded-xl shadow-theme-xl overflow-hidden">
            <div class="bg-sena-green-600 dark:bg-blue-700 px-4 py-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-sena-green-700 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                  <Icon name="trending-up" :size="16" class="text-white" />
                </div>
                <h3 class="text-sm font-bold text-white">Accesos por Hora (Hoy)</h3>
              </div>
              <div class="flex items-center gap-1.5 text-xs text-white/80 bg-white/10 px-2 py-1 rounded-full">
                <div class="w-2 h-2 bg-cyan-400 rounded-full animate-pulse"></div>
                <span>Live</span>
              </div>
            </div>
            <div class="p-4 bg-theme-secondary">
              <div v-if="loadingCharts" class="h-64 flex items-center justify-center">
                <div class="text-center">
                  <div class="w-12 h-12 border-4 border-theme-primary border-t-sena-green-600 dark:border-t-cyan-400 rounded-full animate-spin mx-auto mb-2"></div>
                  <p class="text-sm text-theme-muted">Cargando datos...</p>
                </div>
              </div>
              <div v-else-if="chartData.accesosPorHora" class="h-64">
                <LineChart :chartData="chartData.accesosPorHora" />
              </div>
            </div>
          </div>

          <!-- Gráfico 2: Comparativa Últimos 7 Días -->
          <div class="bg-theme-card border-2 border-theme-primary rounded-xl shadow-theme-xl overflow-hidden">
            <div class="bg-sena-green-600 dark:bg-blue-700 px-4 py-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-sena-green-700 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                  <Icon name="bar-chart-2" :size="16" class="text-white" />
                </div>
                <h3 class="text-sm font-bold text-white">Últimos 7 Días</h3>
              </div>
              <div class="flex items-center gap-1.5 text-xs text-white/80 bg-white/10 px-2 py-1 rounded-full">
                <div class="w-2 h-2 bg-cyan-400 rounded-full animate-pulse"></div>
                <span>Live</span>
              </div>
            </div>
            <div class="p-4 bg-theme-secondary">
              <div v-if="loadingCharts" class="h-64 flex items-center justify-center">
                <div class="text-center">
                  <div class="w-12 h-12 border-4 border-theme-primary border-t-sena-green-600 dark:border-t-cyan-400 rounded-full animate-spin mx-auto mb-2"></div>
                  <p class="text-sm text-theme-muted">Cargando datos...</p>
                </div>
              </div>
              <div v-else-if="chartData.ultimosSieteDias" class="h-64">
                <BarChart :chartData="chartData.ultimosSieteDias" />
              </div>
            </div>
          </div>

          <!-- Gráfico 3: Distribución Hoy -->
          <div class="bg-theme-card border-2 border-theme-primary rounded-xl shadow-theme-xl overflow-hidden">
            <div class="bg-sena-green-600 dark:bg-blue-700 px-4 py-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-sena-green-700 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                  <Icon name="pie-chart" :size="16" class="text-white" />
                </div>
                <h3 class="text-sm font-bold text-white">Distribución de Hoy</h3>
              </div>
              <div class="flex items-center gap-1.5 text-xs text-white/80 bg-white/10 px-2 py-1 rounded-full">
                <div class="w-2 h-2 bg-cyan-400 rounded-full animate-pulse"></div>
                <span>Live</span>
              </div>
            </div>
            <div class="p-4 bg-theme-secondary">
              <div v-if="loadingCharts" class="h-64 flex items-center justify-center">
                <div class="text-center">
                  <div class="w-12 h-12 border-4 border-theme-primary border-t-sena-green-600 dark:border-t-cyan-400 rounded-full animate-spin mx-auto mb-2"></div>
                  <p class="text-sm text-theme-muted">Cargando datos...</p>
                </div>
              </div>
              <div v-else-if="chartData.distribucionHoy" class="h-64">
                <DoughnutChart :chartData="chartData.distribucionHoy" />
              </div>
            </div>
          </div>

          <!-- Gráfico 4: Tendencia del Mes -->
          <div class="bg-theme-card border-2 border-theme-primary rounded-xl shadow-theme-xl overflow-hidden">
            <div class="bg-sena-green-600 dark:bg-blue-700 px-4 py-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-sena-green-700 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                  <Icon name="calendar" :size="16" class="text-white" />
                </div>
                <h3 class="text-sm font-bold text-white">Tendencia del Mes</h3>
              </div>
              <div class="flex items-center gap-1.5 text-xs text-white/80 bg-white/10 px-2 py-1 rounded-full">
                <div class="w-2 h-2 bg-cyan-400 rounded-full animate-pulse"></div>
                <span>Live</span>
              </div>
            </div>
            <div class="p-4 bg-theme-secondary">
              <div v-if="loadingCharts" class="h-64 flex items-center justify-center">
                <div class="text-center">
                  <div class="w-12 h-12 border-4 border-theme-primary border-t-sena-green-600 dark:border-t-cyan-400 rounded-full animate-spin mx-auto mb-2"></div>
                  <p class="text-sm text-theme-muted">Cargando datos...</p>
                </div>
              </div>
              <div v-else-if="chartData.tendenciaMes" class="h-64">
                <LineChart :chartData="chartData.tendenciaMes" />
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>

    <!-- Footer Profesional Compacto -->
    <footer class="bg-theme-navbar border-t-2 border-theme-primary px-4 py-6 flex-shrink-0 mt-auto">
      <div class="max-w-7xl mx-auto">
        <!-- Contenido principal del footer -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-4">

          <!-- Columna 1: Información institucional -->
          <div class="space-y-2">
            <div class="flex items-center gap-2 mb-2">
              <ApplicationLogo alt="SENA Logo" classes="h-8 w-auto" />
              <div>
                <h3 class="text-theme-primary font-bold text-sm">CTAccess</h3>
                <p class="text-theme-muted text-[10px]">Control de Acceso</p>
              </div>
            </div>
            <p class="text-theme-muted text-xs leading-relaxed">
              Sistema integral de control y gestión de accesos para el SENA.
              Seguridad, eficiencia y tecnología al servicio de la institución.
            </p>
            <div class="flex items-center gap-2 text-xs">
              <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
              <span class="text-theme-muted font-semibold">Sistema Operativo</span>
            </div>
          </div>

          <!-- Columna 2: Enlaces rápidos -->
          <div class="space-y-2">
            <h4 class="text-theme-primary font-bold text-sm mb-2 flex items-center gap-2">
              <Icon name="link" :size="14" />
              Enlaces Rápidos
            </h4>
            <nav class="space-y-2">
              <Link :href="route('home')"
                class="flex items-center gap-2 text-theme-muted hover:text-theme-primary text-xs transition-colors duration-200 group">
              <Icon name="home" :size="12" class="group-hover:scale-110 transition-transform" />
              <span>Inicio</span>
              </Link>
              <Link v-if="$page.props.auth && $page.props.auth.user" :href="route('dashboard')"
                class="flex items-center gap-2 text-theme-muted hover:text-theme-primary text-xs transition-colors duration-200 group">
              <Icon name="layout-dashboard" :size="12" class="group-hover:scale-110 transition-transform" />
              <span>Panel de Control</span>
              </Link>
              <Link :href="route('personas.create')"
                class="flex items-center gap-2 text-theme-muted hover:text-theme-primary text-xs transition-colors duration-200 group">
              <Icon name="user-plus" :size="12" class="group-hover:scale-110 transition-transform" />
              <span>Registro</span>
              </Link>
              <a href="#"
                class="flex items-center gap-2 text-theme-muted hover:text-theme-primary text-xs transition-colors duration-200 group">
                <Icon name="help-circle" :size="12" class="group-hover:scale-110 transition-transform" />
                <span>Ayuda y Soporte</span>
              </a>
              <a href="#"
                class="flex items-center gap-2 text-theme-muted hover:text-theme-primary text-xs transition-colors duration-200 group">
                <Icon name="book-open" :size="12" class="group-hover:scale-110 transition-transform" />
                <span>Documentación</span>
              </a>
            </nav>
          </div>

          <!-- Columna 3: Contacto -->
          <div class="space-y-2">
            <h4 class="text-theme-primary font-bold text-sm mb-2 flex items-center gap-2">
              <Icon name="mail" :size="14" />
              Contacto
            </h4>
            <div class="space-y-2">
              <div class="flex items-start gap-2">
                <Icon name="building-2" :size="14" class="text-theme-muted mt-0.5 flex-shrink-0" />
                <div>
                  <p class="text-theme-primary font-semibold text-xs">SENA</p>
                  <p class="text-theme-muted text-[11px]">Servicio Nacional de Aprendizaje</p>
                </div>
              </div>
              <a href="mailto:ctaccesscqta@gmail.com"
                class="flex items-start gap-2 text-theme-muted hover:text-blue-500 text-xs transition-colors duration-200 group">
                <Icon name="mail" :size="14" class="mt-0.5 flex-shrink-0 group-hover:scale-110 transition-transform" />
                <div>
                  <p class="font-medium">Soporte Técnico</p>
                  <p class="text-[11px] break-all">ctaccesscqta@gmail.com</p>
                </div>
              </a>
              <div class="flex items-start gap-2 text-theme-muted text-xs">
                <Icon name="clock" :size="14" class="mt-0.5 flex-shrink-0" />
                <div>
                  <p class="font-medium">Horario de Atención</p>
                  <p class="text-[11px]">Lun - Sab: 6:00 AM - 10:00 PM</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Columna 4: Legal y Versión -->
          <div class="space-y-2">
            <h4 class="text-theme-primary font-bold text-sm mb-2 flex items-center gap-2">
              <Icon name="shield-check" :size="14" />
              Información Legal
            </h4>
            <nav class="space-y-2">
              <a href="#"
                class="flex items-center gap-2 text-theme-muted hover:text-theme-primary text-xs transition-colors duration-200 group">
                <Icon name="file-text" :size="12" class="group-hover:scale-110 transition-transform" />
                <span>Términos y Condiciones</span>
              </a>
              <a href="#"
                class="flex items-center gap-2 text-theme-muted hover:text-theme-primary text-xs transition-colors duration-200 group">
                <Icon name="lock" :size="12" class="group-hover:scale-110 transition-transform" />
                <span>Política de Privacidad</span>
              </a>
              <a href="#"
                class="flex items-center gap-2 text-theme-muted hover:text-theme-primary text-xs transition-colors duration-200 group">
                <Icon name="shield" :size="12" class="group-hover:scale-110 transition-transform" />
                <span>Política de Datos</span>
              </a>
            </nav>

            <!-- Info de versión -->
            <div class="mt-2 p-2 bg-theme-secondary border border-theme-primary rounded-lg">
              <div class="flex items-center gap-2 mb-2">
                <Icon name="code" :size="14" class="text-blue-500" />
                <span class="text-theme-primary font-semibold text-xs">Versión del Sistema</span>
              </div>
              <div class="space-y-1">
                <div class="flex items-center justify-between text-[11px]">
                  <span class="text-theme-muted">Versión:</span>
                  <span class="text-theme-primary font-bold">v{{ sistema_info?.version || '1.0' }}</span>
                </div>
                <div class="flex items-center justify-between text-[11px]">
                  <span class="text-theme-muted">Última actualización:</span>
                  <span class="text-theme-primary font-mono">{{ sistema_info?.ultima_actualizacion || '2025-10-13'
                    }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Separador -->
        <div class="border-t border-theme-primary my-4"></div>

        <!-- Footer bottom -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
          <!-- Copyright -->
          <div class="flex items-center gap-2 text-xs text-theme-muted">
            <Icon name="copyright" :size="14" />
            <span>{{ new Date().getFullYear() }} <strong class="text-theme-primary">SENA</strong> - Todos los derechos
              reservados</span>
          </div>

          <!-- Desarrollado por -->
          <div class="flex items-center gap-2 text-xs text-theme-muted">
            <Icon name="heart" :size="14" class="text-red-500" />
            <span>Desarrollado con</span>
            <span class="text-theme-primary font-semibold">Laravel + Vue.js + Inertia</span>
          </div>

          <!-- Redes sociales -->
          <div class="flex items-center gap-3">
            <a href="https://www.sena.edu.co" target="_blank"
              class="w-8 h-8 bg-theme-secondary border border-theme-primary rounded-lg flex items-center justify-center text-theme-muted hover:text-blue-500 hover:border-blue-500 transition-all duration-200 hover:scale-110"
              title="Sitio web SENA">
              <Icon name="globe" :size="16" />
            </a>
            <a href="mailto:ctaccesscqta@gmail.com"
              class="w-8 h-8 bg-theme-secondary border border-theme-primary rounded-lg flex items-center justify-center text-theme-muted hover:text-red-500 hover:border-red-500 transition-all duration-200 hover:scale-110"
              title="Enviar email">
              <Icon name="mail" :size="16" />
            </a>
            <a href="#"
              class="w-8 h-8 bg-theme-secondary border border-theme-primary rounded-lg flex items-center justify-center text-theme-muted hover:text-green-500 hover:border-green-500 transition-all duration-200 hover:scale-110"
              title="Soporte técnico">
              <Icon name="life-buoy" :size="16" />
            </a>
          </div>
        </div>
      </div>
    </footer>

    <!-- PWA Install Prompt -->
    <PWAInstallPrompt />
  </div>
</template>

<style scoped>
/* ⚡ ANIMACIÓN SPOTLIGHT - Efecto super llamativo para registros nuevos */
.spotlight-enter-active {
  animation: spotlightEntrance 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.spotlight-leave-active {
  animation: spotlightExit 0.5s ease-in-out;
  position: absolute;
  width: 100%;
}

.spotlight-move {
  transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* 🎬 Entrada dramática con zoom y rotación */
@keyframes spotlightEntrance {
  0% {
    opacity: 0;
    transform: translateX(150px) scale(0.3) rotate(15deg);
    filter: brightness(3);
  }

  50% {
    transform: translateX(-20px) scale(1.15) rotate(-2deg);
    filter: brightness(1.5);
  }

  70% {
    transform: translateX(5px) scale(0.95) rotate(1deg);
  }

  100% {
    opacity: 1;
    transform: translateX(0) scale(1) rotate(0);
    filter: brightness(1);
  }
}

@keyframes spotlightExit {
  from {
    opacity: 1;
    transform: scale(1);
  }

  to {
    opacity: 0;
    transform: scale(0.8) translateX(-50px);
  }
}

/* 🎯 Spotlight Card - Efecto de reflector en las 4 esquinas */
.spotlight-card {
  animation: cardPulse 2s ease-in-out infinite;
  position: relative;
}

@keyframes cardPulse {

  0%,
  100% {
    box-shadow: 0 0 0 rgba(253, 195, 0, 0.5),
      0 8px 24px -4px rgba(253, 195, 0, 0.3);
  }

  50% {
    box-shadow: 0 0 30px rgba(253, 195, 0, 0.6),
      0 12px 32px -4px rgba(253, 195, 0, 0.4);
  }
}

/* 💡 Corner Spotlights - Luces en las esquinas */
.corner-spotlight {
  position: absolute;
  width: 16px;
  height: 16px;
  background: #FDC300;
  opacity: 0;
  animation: cornerFlash 1.5s ease-in-out infinite;
}

.corner-spotlight.top-left {
  top: -2px;
  left: -2px;
  border-radius: 0 0 100% 0;
  animation-delay: 0s;
}

.corner-spotlight.top-right {
  top: -2px;
  right: -2px;
  border-radius: 0 0 0 100%;
  animation-delay: 0.3s;
}

.corner-spotlight.bottom-left {
  bottom: -2px;
  left: -2px;
  border-radius: 0 100% 0 0;
  animation-delay: 0.6s;
}

.corner-spotlight.bottom-right {
  bottom: -2px;
  right: -2px;
  border-radius: 100% 0 0 0;
  animation-delay: 0.9s;
}

@keyframes cornerFlash {

  0%,
  100% {
    opacity: 0;
    transform: scale(0);
  }

  50% {
    opacity: 0.8;
    transform: scale(1);
  }
}

/* � Notification Badge - Badge amarillo animado */
.notification-badge {
  animation: badgeBounce 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 3;
}

@keyframes badgeBounce {

  0%,
  100% {
    transform: scale(1) rotate(0deg);
  }

  25% {
    transform: scale(1.3) rotate(-10deg);
  }

  75% {
    transform: scale(1.3) rotate(10deg);
  }
}

/* 🌀 Shake Animation - Avatar temblando */
.shake {
  animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) 3;
}

@keyframes shake {

  0%,
  100% {
    transform: translateX(0) scale(1.1);
  }

  25% {
    transform: translateX(-5px) rotate(-5deg) scale(1.15);
  }

  75% {
    transform: translateX(5px) rotate(5deg) scale(1.15);
  }
}

/* ⚡ Blink Badge - Parpadeo llamativo del badge "NUEVO" */
.blink-badge {
  animation: blinkScale 1s ease-in-out infinite;
}

@keyframes blinkScale {

  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }

  50% {
    opacity: 0.7;
    transform: scale(1.1);
  }
}

/* 🔴 Pulse Dot - Punto pulsante en badge de estado */
.pulse-dot {
  animation: pulseDot 1.5s ease-in-out infinite;
}

@keyframes pulseDot {

  0%,
  100% {
    transform: scale(1);
    opacity: 1;
  }

  50% {
    transform: scale(1.5);
    opacity: 0.5;
  }
}

/* 🟢 Live Indicator - Indicador verde pulsante */
.live-indicator {
  animation: liveIndicator 2s ease-in-out infinite;
}

@keyframes liveIndicator {

  0%,
  100% {
    transform: scale(1);
    opacity: 1;
  }

  50% {
    transform: scale(1.3);
    opacity: 0.7;
  }
}

/* 📐 Título Diagonal - Hover suave */
.transform.-rotate-2 {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
}

.transform.-rotate-2:hover {
  box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.4);
}

/* 🕐 Reloj Digital - Estilo LED moderno */
.digital-clock {
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
  letter-spacing: 0.05em;
  text-shadow: 0 0 8px rgba(45, 135, 0, 0.5);
  animation: digitGlow 2s ease-in-out infinite;
}

@keyframes digitGlow {
  0%,
  100% {
    text-shadow: 0 0 8px rgba(45, 135, 0, 0.5);
  }

  50% {
    text-shadow: 0 0 12px rgba(45, 135, 0, 0.7);
  }
}

/* Modo oscuro - Reloj con efecto cyan corporativo */
.dark .digital-clock {
  text-shadow: 0 0 10px rgba(80, 229, 249, 0.5);
  animation: digitGlowDark 2s ease-in-out infinite;
}

@keyframes digitGlowDark {
  0%,
  100% {
    text-shadow: 0 0 10px rgba(80, 229, 249, 0.5);
  }

  50% {
    text-shadow: 0 0 14px rgba(80, 229, 249, 0.7);
  }
}

/* Números con espaciado uniforme */
.tabular-nums {
  font-variant-numeric: tabular-nums;
}

/* 📜 Custom Scrollbar - Mate y moderno */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #3b82f6 transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #3b82f6;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #2563eb;
}

/* 🎨 Hover effects para cards */
.group:hover {
  transform: translateY(-2px);
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* ✨ Transiciones suaves globales */
* {
  transition-property: transform, background-color, border-color, box-shadow, opacity;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* 🎨 Stats Cubitos Animados - Diseño Mejorado */
.container-items {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  gap: 8px;
  padding: 0 12px;
}

.item-color {
  position: relative;
  flex: 1;
  height: 50px;
  min-width: 50px;
  border: none;
  outline: none;
  cursor: pointer;
  background: transparent;
  overflow: visible;
  transition: all 600ms cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Contenedor interno - Fondo del cubito */
.item-inner {
  position: relative;
  width: 100%;
  height: 100%;
  background-color: var(--color);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 12px;
  opacity: 0.5;
  filter: grayscale(0.6) brightness(0.7) saturate(0.8);
  transition: all 600ms cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

/* Modo claro - Cubitos más oscuros y mates */
:not(.dark) .item-inner {
  opacity: 0.5;
  filter: grayscale(0.6) brightness(0.7) saturate(0.85);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Modo oscuro - Cubitos más claros y visibles */
.dark .item-inner {
  opacity: 0.6;
  filter: grayscale(0.5) brightness(0.9) saturate(1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

/* Contenedor del icono */
.icon-container {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  flex-shrink: 0;
  transition: all 600ms cubic-bezier(0.34, 1.56, 0.64, 1);
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3));
}

/* Ícono en cubito activo - efecto levitación */
.item-color.active-color .icon-container {
  animation: levitate 2s ease-in-out infinite;
}

@keyframes levitate {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-3px);
  }
}

/* Datos estadísticos */
.stat-data {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  gap: 2px;
  color: #ffffff;
  opacity: 0;
  width: 0;
  overflow: hidden;
  transition: all 600ms cubic-bezier(0.34, 1.56, 0.64, 1);
  white-space: nowrap;
}

.stat-value {
  font-size: 16px;
  font-weight: 900;
  line-height: 1;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4),
               0 1px 2px rgba(0, 0, 0, 0.3);
}

.stat-label {
  font-size: 9px;
  font-weight: 700;
  line-height: 1.2;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  opacity: 1;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

/* Animación de levitación para los datos */
.item-color.active-color .stat-data {
  animation: levitateText 2.5s ease-in-out infinite;
}

@keyframes levitateText {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-2px);
  }
}

/* Estado activo - Se expande horizontalmente */
.item-color.active-color {
  flex: 2.5;
  z-index: 50;
}

.item-color.active-color .item-inner {
  opacity: 1;
  filter: grayscale(0) brightness(1) saturate(1);
  box-shadow: 0 8px 24px -4px var(--color),
    0 0 40px -8px var(--color),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
}

/* Modo claro - Estado activo más oscuro y mate, sin brillo excesivo */
:not(.dark) .item-color.active-color .item-inner {
  opacity: 1;
  filter: grayscale(0) brightness(0.85) saturate(1);
  box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.25),
    0 2px 8px -2px rgba(0, 0, 0, 0.15);
  border: 2px solid rgba(255, 255, 255, 0.6);
}

/* Modo oscuro - Estado activo más claro y brillante */
.dark .item-color.active-color .item-inner {
  opacity: 1;
  filter: grayscale(0) brightness(1.15) saturate(1.1);
  box-shadow: 0 8px 24px -4px var(--color),
    0 0 40px -8px var(--color),
    0 0 20px -10px var(--color),
    inset 0 1px 0 rgba(255, 255, 255, 0.3);
  border: 2px solid rgba(255, 255, 255, 0.4);
}

.item-color.active-color .stat-data {
  opacity: 1;
  width: auto;
  padding-left: 8px;
}

/* Hover suave en todos los cubitos */
.item-color:hover .item-inner {
  opacity: 0.6;
  filter: grayscale(0.5) brightness(0.8) saturate(0.85);
  transform: translateY(-2px);
}

.item-color.active-color:hover .item-inner {
  opacity: 1;
  transform: translateY(-2px);
}

/* Modo claro - Hover más oscuro para mejor visibilidad */
:not(.dark) .item-color:hover .item-inner {
  opacity: 0.65;
  filter: grayscale(0.5) brightness(0.8) saturate(0.9);
  transform: translateY(-1px);
}

:not(.dark) .item-color.active-color:hover .item-inner {
  opacity: 1;
  filter: grayscale(0) brightness(0.9) saturate(1.05);
  transform: translateY(-1px);
}

/* Modo oscuro - Hover más claro y brillante */
.dark .item-color:hover .item-inner {
  opacity: 0.75;
  filter: grayscale(0.4) brightness(1) saturate(1);
  transform: translateY(-2px);
}

.dark .item-color.active-color:hover .item-inner {
  opacity: 1;
  filter: grayscale(0) brightness(1.2) saturate(1.15);
  transform: translateY(-2px);
}

/* Responsivo */
@media (max-width: 1024px) {
  .container-items {
    gap: 6px;
  }

  .item-color {
    min-width: 45px;
    height: 45px;
  }

  .stat-value {
    font-size: 14px;
  }

  .stat-label {
    font-size: 8px;
  }
}

@media (max-width: 768px) {
  .container-items {
    gap: 4px;
  }

  .item-color {
    min-width: 40px;
    height: 40px;
  }

  .item-inner {
    padding: 0 8px;
  }

  .stat-value {
    font-size: 12px;
  }

  .stat-label {
    font-size: 7px;
  }
}

/* 🔐 Estilos para el botón de login con toque largo */
.select-none {
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  -webkit-touch-callout: none; /* iOS Safari */
}

/* Animación de pulso suave para el ícono */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.6;
  }
}

.animate-pulse {
  animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Efecto de onda en la barra de progreso */
@keyframes progressWave {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
</style>
