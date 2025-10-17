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

        // Mantener solo los últimos 3 para visualización limpia
        if (recentActivity.value.length > 3) {
          recentActivity.value = recentActivity.value.slice(0, 3)
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

        // Mantener solo los últimos 3 para visualización limpia
        if (recentRegistrations.value.length > 3) {
          recentRegistrations.value = recentRegistrations.value.slice(0, 3)
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

// 🔥 Actividad en Tiempo Real con WebSockets - Solo mostrar últimos 3
const fetchRecentActivity = async () => {
  try {
    const response = await fetch('/api/accesos/recientes')
    const data = await response.json()
    // Limitar a solo 3 registros para una vista limpia
    recentActivity.value = data.slice(0, 3)
    loadingActivity.value = false
  } catch (error) {
    console.error('Error fetching recent activity:', error)
    loadingActivity.value = false
  }
}

// 👥 Registros Recientes de Personas - Solo mostrar últimos 3
const fetchRecentRegistrations = async () => {
  try {
    const response = await fetch('/api/personas/recientes')
    const data = await response.json()
    // Limitar a solo 3 registros para una vista limpia
    recentRegistrations.value = data.slice(0, 3)
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
    <header class="bg-theme-navbar border-b-2 border-theme-primary px-3 sm:px-4 lg:px-6 py-3 sm:py-4 flex-shrink-0 sticky top-0 z-50 shadow-lg">
      <div class="max-w-[1920px] 2xl:max-w-[2400px] mx-auto flex items-center justify-between gap-3">
        <!-- Logo -->
        <div class="flex items-center gap-2 sm:gap-3 lg:gap-4">
          <div class="relative flex-shrink-0">
            <ApplicationLogo alt="CTAccess Logo" classes="h-10 sm:h-12 md:h-14 lg:h-16 w-auto object-contain" />
          </div>

          <!-- Reloj Digital - Ahora visible en móviles con diseño compacto -->
          <div
            class="flex items-center gap-1.5 sm:gap-2 bg-theme-secondary border-2 border-theme-primary rounded-xl px-2.5 sm:px-3 lg:px-4 py-1.5 sm:py-2 shadow-theme-lg">
            <Icon name="clock" :size="16" class="text-sena-green-600 dark:text-cyan-400 hidden sm:block lg:w-5 lg:h-5" />
            <div class="flex flex-col">
              <div class="text-theme-primary font-bold text-xs sm:text-sm lg:text-base tabular-nums leading-tight digital-clock">
                {{ formatTime(currentTime) }}
              </div>
              <div class="text-theme-muted text-[9px] sm:text-[10px] lg:text-xs font-medium uppercase leading-tight hidden xs:block">
                {{ formatDate(currentTime) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Navegación -->
        <nav class="flex gap-2 sm:gap-2.5 lg:gap-3 items-center">
          <!-- Theme toggle button -->
          <button @click="toggleTheme"
            class="rounded-lg p-2 sm:p-2.5 text-theme-muted hover:bg-theme-tertiary hover:text-theme-secondary focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200"
            :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'">
            <Icon :name="isDark ? 'sun' : 'moon'" :size="18" class="sm:w-5 sm:h-5 lg:w-6 lg:h-6" />
          </button>

          <template v-if="$page.props.auth && $page.props.auth.user">
            <Link :href="route('dashboard')"
              class="flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 lg:px-5 py-2 sm:py-2.5 text-theme-primary border-2 border-theme-primary rounded-xl hover:bg-theme-tertiary transition-all duration-200 text-sm sm:text-base font-medium shadow-theme-md hover:shadow-theme-lg">
            <Icon name="home" :size="18" class="lg:w-5 lg:h-5" />
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
                'relative flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 lg:px-5 py-2 sm:py-2.5 border-2 rounded-xl transition-all duration-200 overflow-hidden select-none text-sm sm:text-base font-medium shadow-theme-md hover:shadow-theme-lg',
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
              <div class="relative z-10 flex items-center gap-1.5 sm:gap-2">
                <Icon
                  :name="isLongPressing ? 'shield' : 'log-in'"
                  :size="18"
                  class="lg:w-5 lg:h-5"
                  :class="{ 'animate-pulse': isLongPressing }"
                />
                <span class="hidden sm:inline">
                  {{ isLongPressing
                    ? `Sistema ${Math.floor(longPressProgress / 33)}...`
                    : 'Iniciar Sesión'
                  }}
                </span>
              </div>
            </Link>
            <Link :href="route('personas.create')"
              class="flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 lg:px-6 py-2 sm:py-2.5 bg-sena-green-600 hover:bg-sena-green-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded-xl transition-all duration-200 font-semibold shadow-theme-lg hover:shadow-theme-xl text-sm sm:text-base">
            <Icon name="user-plus" :size="18" class="lg:w-5 lg:h-5" />
            <span class="hidden sm:inline">Registrarse</span>
            <span class="sm:hidden">+</span>
            </Link>
          </template>
        </nav>
      </div>
    </header>

    <!-- Contenido principal -->
    <main class="flex-1 px-2 sm:px-4 lg:px-6 xl:px-8 py-3 sm:py-4 lg:py-6">
      <div class="max-w-[1920px] 2xl:max-w-[2400px] mx-auto flex flex-col">

        <!-- 🎨 Stats Cubitos Animados - Responsive para todas las pantallas -->
        <div class="w-full mb-6 sm:mb-8 lg:mb-10 mt-4 sm:mt-6 px-1 sm:px-4 lg:px-6 flex-shrink-0">
          <div class="container-items">
            <button v-for="(stat, index) in statsData" :key="index" class="item-color"
              :class="{ 'active-color': activeColorIndex === index }" :style="{ '--color': stat.color }"
              :aria-label="stat.label">
              <!-- Contenedor interno del cubito -->
              <div class="item-inner">
                <!-- Icono -->
                <div class="icon-container">
                  <Icon :name="stat.icon" :size="16" class="sm:hidden" />
                  <Icon :name="stat.icon" :size="20" class="hidden sm:block lg:hidden" />
                  <Icon :name="stat.icon" :size="24" class="hidden lg:block" />
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

        <!-- 📋 Sección de Tablas - Full Width, Solo 3 Registros Visibles -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">

          <!-- 🔥 Actividad en Tiempo Real -->
          <div class="relative flex-shrink-0">
            
            <!-- Card Compacta - Solo 3 registros, sin scroll -->
            <div class="bg-theme-card border-2 border-theme-primary rounded-xl shadow-theme-xl overflow-hidden">
              <!-- Encabezado -->
              <div class="bg-sena-green-600 dark:bg-blue-700 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 bg-sena-green-700 dark:bg-blue-800 rounded-lg flex items-center justify-center relative">
                    <Icon name="activity" :size="18" class="text-white" />
                    <div class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-cyan-400 rounded-full border-2 border-sena-green-700 dark:border-blue-900 live-indicator"></div>
                  </div>
                  <div>
                    <h3 class="text-base font-bold text-white">Actividad Reciente</h3>
                    <p class="text-xs text-white/80">Últimos 3 accesos</p>
                  </div>
                </div>
                <span class="text-xs font-semibold text-white/90 bg-white/20 px-3 py-1 rounded-full">
                  {{ recentActivity.length }}/3
                </span>
              </div>

              <!-- Lista de Registros - Solo 3 visibles, sin scroll -->
              <div class="p-3 sm:p-4 bg-theme-secondary">
                <div class="space-y-2">

                  <!-- Loading State - Solo 3 items -->
                  <template v-if="loadingActivity">
                    <div v-for="i in 3" :key="i"
                      class="flex items-center gap-3 p-3 bg-theme-card border border-theme-primary rounded-lg animate-pulse">
                      <div class="w-10 h-10 bg-theme-tertiary rounded-lg"></div>
                      <div class="flex-1 space-y-2">
                        <div class="h-3 bg-theme-tertiary rounded w-40"></div>
                        <div class="h-2.5 bg-theme-tertiary rounded w-32"></div>
                      </div>
                      <div class="w-12 h-7 bg-theme-tertiary rounded"></div>
                    </div>
                  </template>

                  <!-- Lista de Actividades - Solo 3 registros -->
                  <template v-else-if="recentActivity.length > 0">
                    <transition-group name="spotlight" tag="div" class="space-y-2">
                      <div v-for="activity in recentActivity" :key="activity.id" :class="[
                        'relative flex items-center gap-3 p-3 rounded-lg border-2 transition-all duration-500',
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

                        <!-- Avatar con Icono -->
                        <div class="relative flex-shrink-0">
                          <div :class="[
                            'w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold border-2 transition-all duration-300',
                            activity.tipo === 'entrada'
                              ? 'bg-sena-green-600 border-sena-green-700 dark:bg-cyan-600 dark:border-cyan-500'
                              : 'bg-red-600 border-red-700 dark:bg-red-500 dark:border-red-600',
                            activity.isNew ? 'scale-110 shake' : 'group-hover:scale-105'
                          ]">
                            <Icon :name="activity.tipo === 'entrada' ? 'log-in' : 'log-out'" :size="18" />
                          </div>
                          <!-- Badge Nuevo -->
                          <div v-if="activity.isNew"
                            class="absolute -top-1 -right-1 w-4 h-4 bg-sena-yellow-400 border-2 border-white dark:border-gray-900 rounded-full flex items-center justify-center notification-badge">
                            <span class="text-[8px] font-black text-sena-yellow-900">!</span>
                          </div>
                        </div>

                        <!-- Información -->
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-2 mb-1">
                            <p :class="[
                              'font-bold text-sm truncate',
                              activity.isNew ? 'text-sena-yellow-900 dark:text-sena-yellow-200' : 'text-theme-primary'
                            ]">
                              {{ activity.persona }}
                            </p>
                            <span v-if="activity.isNew"
                              class="px-2 py-0.5 bg-sena-yellow-400 text-sena-yellow-900 text-[9px] font-black rounded uppercase tracking-wide border border-sena-yellow-500 blink-badge">
                              ¡Nuevo!
                            </span>
                          </div>
                          <div class="flex items-center gap-2 text-xs">
                            <Icon name="credit-card" :size="11"
                              :class="activity.isNew ? 'text-sena-yellow-700 dark:text-sena-yellow-400' : 'text-theme-muted'" />
                            <span
                              :class="activity.isNew ? 'text-sena-yellow-800 dark:text-sena-yellow-300 font-semibold' : 'text-theme-muted'">
                              {{ activity.documento }}
                            </span>
                            <span class="text-theme-muted">•</span>
                            <Icon name="clock" :size="11"
                              :class="activity.isNew ? 'text-sena-yellow-700 dark:text-sena-yellow-400' : 'text-theme-muted'" />
                            <span :class="[
                              'font-semibold',
                              activity.isNew ? 'text-sena-yellow-800 dark:text-sena-yellow-300' : 'text-theme-muted'
                            ]">
                              {{ formatRelativeTime(activity.tiempo) }}
                            </span>
                          </div>
                        </div>

                        <!-- Badge de Estado -->
                        <div class="flex-shrink-0">
                          <div :class="[
                            'px-3 py-1.5 rounded-lg text-[10px] font-black uppercase border-2 transition-transform duration-300',
                            activity.tipo === 'entrada'
                              ? 'bg-sena-green-600 text-white border-sena-green-700 dark:bg-cyan-600 dark:border-cyan-500'
                              : 'bg-red-600 text-white border-red-700 dark:bg-red-500 dark:border-red-600',
                            'group-hover:scale-105'
                          ]">
                            <div class="flex items-center gap-1">
                              <div :class="[
                                'w-1.5 h-1.5 rounded-full bg-white',
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
                    <div class="text-center py-8 text-theme-muted">
                      <div
                        class="w-16 h-16 mx-auto mb-3 bg-theme-tertiary rounded-xl flex items-center justify-center border-2 border-theme-primary">
                        <Icon name="inbox" :size="32" class="opacity-40" />
                      </div>
                      <p class="text-sm font-bold">Sin actividad reciente</p>
                      <p class="text-xs mt-1 opacity-70">Los accesos aparecerán aquí automáticamente</p>
                    </div>
                  </template>
                </div>
              </div>
            </div>

          </div>

          <!-- 👥 Registros Recientes de Personas -->
          <div class="relative flex-shrink-0">
            <!-- Card Compacta - Solo 3 registros, sin scroll -->
            <div class="bg-theme-card border-2 border-theme-primary rounded-xl shadow-theme-xl overflow-hidden">
              <!-- Encabezado -->
              <div class="bg-purple-600 dark:bg-purple-700 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 bg-purple-700 dark:bg-purple-800 rounded-lg flex items-center justify-center relative">
                    <Icon name="user-plus" :size="18" class="text-white" />
                    <div class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-yellow-400 rounded-full border-2 border-purple-700 dark:border-purple-900 live-indicator"></div>
                  </div>
                  <div>
                    <h3 class="text-base font-bold text-white">Registros</h3>
                    <p class="text-xs text-white/80">Nuevas personas</p>
                  </div>
                </div>
                <span class="text-xs font-semibold text-white/90 bg-white/20 px-3 py-1 rounded-full">
                  {{ recentRegistrations.length }}/3
                </span>
              </div>

              <!-- Lista de Registros - Solo 3 visibles, sin scroll -->
              <div class="p-3 sm:p-4 bg-theme-secondary">
                <div class="space-y-2">
                  <!-- Loading State - Solo 3 items -->
                  <template v-if="loadingRegistrations">
                    <div v-for="i in 3" :key="i" class="flex items-center gap-3 p-3 bg-theme-card border border-theme-primary rounded-lg animate-pulse">
                      <div class="w-10 h-10 bg-theme-tertiary rounded-lg"></div>
                      <div class="flex-1 space-y-2">
                        <div class="h-3 bg-theme-tertiary rounded w-40"></div>
                        <div class="h-2.5 bg-theme-tertiary rounded w-32"></div>
                      </div>
                      <div class="w-12 h-7 bg-theme-tertiary rounded"></div>
                    </div>
                  </template>

                  <!-- Lista de Registros - Solo 3 registros -->
                  <template v-else-if="recentRegistrations.length > 0">
                    <transition-group name="spotlight" tag="div" class="space-y-2">
                      <div v-for="registro in recentRegistrations" :key="registro.id" :class="[
                        'relative flex items-center gap-3 p-3 rounded-lg border-2 transition-all duration-500',
                        registro.isNew
                          ? 'bg-purple-50 dark:bg-purple-900/20 border-purple-400 dark:border-purple-600 spotlight-card'
                          : 'bg-theme-card border-theme-primary hover:border-theme-hover hover:shadow-theme-lg',
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
                            'w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold border-2 transition-all duration-300',
                            'bg-purple-600 border-purple-700 dark:bg-purple-600 dark:border-purple-500',
                            registro.isNew ? 'scale-110 shake' : 'group-hover:scale-105'
                          ]">
                            <Icon name="user" :size="18" />
                          </div>
                          <div v-if="registro.isNew"
                            class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 border-2 border-white dark:border-gray-900 rounded-full flex items-center justify-center notification-badge">
                            <span class="text-[8px] font-black text-purple-900">!</span>
                          </div>
                        </div>

                        <!-- Información -->
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-2 mb-1">
                            <p :class="[
                              'font-bold text-sm truncate',
                              registro.isNew ? 'text-purple-900 dark:text-purple-200' : 'text-theme-primary'
                            ]">
                              {{ registro.nombre }}
                            </p>
                            <span v-if="registro.isNew"
                              class="px-2 py-0.5 bg-yellow-400 text-purple-900 text-[9px] font-black rounded uppercase tracking-wide border border-yellow-500 blink-badge">
                              ¡Nuevo!
                            </span>
                          </div>
                          <div class="flex items-center gap-2 text-xs">
                            <Icon name="briefcase" :size="11" 
                              :class="registro.isNew ? 'text-purple-700 dark:text-purple-400' : 'text-theme-muted'" />
                            <span
                              :class="registro.isNew ? 'text-purple-800 dark:text-purple-300 font-semibold' : 'text-theme-muted'">
                              {{ registro.tipo_persona }}
                            </span>
                            <span class="text-theme-muted">•</span>
                            <Icon name="clock" :size="11"
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
                            'px-3 py-1.5 rounded-lg text-[10px] font-black uppercase border-2 transition-transform duration-300',
                            'bg-purple-600 text-white border-purple-700 dark:bg-purple-600 dark:border-purple-500',
                            'group-hover:scale-105'
                          ]">
                            <div class="flex items-center gap-1">
                              <div :class="[
                                'w-1.5 h-1.5 rounded-full bg-white',
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
                    <div class="text-center py-8 text-theme-muted">
                      <div class="w-16 h-16 mx-auto mb-3 bg-theme-tertiary rounded-xl flex items-center justify-center border-2 border-theme-primary">
                        <Icon name="user-plus" :size="32" class="opacity-40" />
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

        <!-- 📊 SECCIÓN DE GRÁFICOS ANALÍTICOS - Debajo de las tablas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
          
          <!-- Gráfico 1: Accesos por Hora del Día -->
          <div class="bg-theme-card border border-theme-primary sm:border-2 rounded-lg sm:rounded-xl shadow-theme-lg sm:shadow-theme-xl overflow-hidden">
            <div class="bg-sena-green-600 dark:bg-blue-700 px-3 sm:px-4 py-2 sm:py-3 flex items-center justify-between">
              <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-sena-green-700 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                  <Icon name="trending-up" :size="14" class="text-white sm:hidden" />
                  <Icon name="trending-up" :size="16" class="text-white hidden sm:block" />
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-white">Accesos por Hora</h3>
              </div>
              <div class="flex items-center gap-1 sm:gap-1.5 text-[10px] sm:text-xs text-white/80 bg-white/10 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full">
                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-cyan-400 rounded-full animate-pulse"></div>
                <span class="hidden sm:inline">Live</span>
              </div>
            </div>
            <div class="p-2 sm:p-3 md:p-4 bg-theme-secondary">
              <div v-if="loadingCharts" class="h-48 sm:h-56 md:h-64 xl:h-72 2xl:h-80 flex items-center justify-center">
                <div class="text-center">
                  <div class="w-10 h-10 sm:w-12 sm:h-12 border-4 border-theme-primary border-t-sena-green-600 dark:border-t-cyan-400 rounded-full animate-spin mx-auto mb-2"></div>
                  <p class="text-xs sm:text-sm text-theme-muted">Cargando datos...</p>
                </div>
              </div>
              <div v-else-if="chartData.accesosPorHora" class="h-48 sm:h-56 md:h-64 xl:h-72 2xl:h-80">
                <LineChart :chartData="chartData.accesosPorHora" />
              </div>
            </div>
          </div>

          <!-- Gráfico 2: Comparativa Últimos 7 Días -->
          <div class="bg-theme-card border border-theme-primary sm:border-2 rounded-lg sm:rounded-xl shadow-theme-lg sm:shadow-theme-xl overflow-hidden">
            <div class="bg-sena-green-600 dark:bg-blue-700 px-3 sm:px-4 py-2 sm:py-3 flex items-center justify-between">
              <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-sena-green-700 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                  <Icon name="bar-chart-2" :size="14" class="text-white sm:hidden" />
                  <Icon name="bar-chart-2" :size="16" class="text-white hidden sm:block" />
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-white">Últimos 7 Días</h3>
              </div>
              <div class="flex items-center gap-1 sm:gap-1.5 text-[10px] sm:text-xs text-white/80 bg-white/10 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full">
                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-cyan-400 rounded-full animate-pulse"></div>
                <span class="hidden sm:inline">Live</span>
              </div>
            </div>
            <div class="p-2 sm:p-3 md:p-4 bg-theme-secondary">
              <div v-if="loadingCharts" class="h-48 sm:h-56 md:h-64 xl:h-72 2xl:h-80 flex items-center justify-center">
                <div class="text-center">
                  <div class="w-10 h-10 sm:w-12 sm:h-12 border-4 border-theme-primary border-t-sena-green-600 dark:border-t-cyan-400 rounded-full animate-spin mx-auto mb-2"></div>
                  <p class="text-xs sm:text-sm text-theme-muted">Cargando datos...</p>
                </div>
              </div>
              <div v-else-if="chartData.ultimosSieteDias" class="h-48 sm:h-56 md:h-64 xl:h-72 2xl:h-80">
                <BarChart :chartData="chartData.ultimosSieteDias" />
              </div>
            </div>
          </div>

          <!-- Gráfico 3: Distribución Hoy -->
          <div class="bg-theme-card border border-theme-primary sm:border-2 rounded-lg sm:rounded-xl shadow-theme-lg sm:shadow-theme-xl overflow-hidden">
            <div class="bg-sena-green-600 dark:bg-blue-700 px-3 sm:px-4 py-2 sm:py-3 flex items-center justify-between">
              <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-sena-green-700 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                  <Icon name="pie-chart" :size="14" class="text-white sm:hidden" />
                  <Icon name="pie-chart" :size="16" class="text-white hidden sm:block" />
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-white">Distribución de Hoy</h3>
              </div>
              <div class="flex items-center gap-1 sm:gap-1.5 text-[10px] sm:text-xs text-white/80 bg-white/10 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full">
                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-cyan-400 rounded-full animate-pulse"></div>
                <span class="hidden sm:inline">Live</span>
              </div>
            </div>
            <div class="p-2 sm:p-3 md:p-4 bg-theme-secondary">
              <div v-if="loadingCharts" class="h-48 sm:h-56 md:h-64 xl:h-72 2xl:h-80 flex items-center justify-center">
                <div class="text-center">
                  <div class="w-10 h-10 sm:w-12 sm:h-12 border-4 border-theme-primary border-t-sena-green-600 dark:border-t-cyan-400 rounded-full animate-spin mx-auto mb-2"></div>
                  <p class="text-xs sm:text-sm text-theme-muted">Cargando datos...</p>
                </div>
              </div>
              <div v-else-if="chartData.distribucionHoy" class="h-48 sm:h-56 md:h-64 xl:h-72 2xl:h-80">
                <DoughnutChart :chartData="chartData.distribucionHoy" />
              </div>
            </div>
          </div>

          <!-- Gráfico 4: Tendencia del Mes -->
          <div class="bg-theme-card border border-theme-primary sm:border-2 rounded-lg sm:rounded-xl shadow-theme-lg sm:shadow-theme-xl overflow-hidden">
            <div class="bg-sena-green-600 dark:bg-blue-700 px-3 sm:px-4 py-2 sm:py-3 flex items-center justify-between">
              <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-sena-green-700 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                  <Icon name="calendar" :size="14" class="text-white sm:hidden" />
                  <Icon name="calendar" :size="16" class="text-white hidden sm:block" />
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-white">Tendencia del Mes</h3>
              </div>
              <div class="flex items-center gap-1 sm:gap-1.5 text-[10px] sm:text-xs text-white/80 bg-white/10 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full">
                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-cyan-400 rounded-full animate-pulse"></div>
                <span class="hidden sm:inline">Live</span>
              </div>
            </div>
            <div class="p-2 sm:p-3 md:p-4 bg-theme-secondary">
              <div v-if="loadingCharts" class="h-48 sm:h-56 md:h-64 xl:h-72 2xl:h-80 flex items-center justify-center">
                <div class="text-center">
                  <div class="w-10 h-10 sm:w-12 sm:h-12 border-4 border-theme-primary border-t-sena-green-600 dark:border-t-cyan-400 rounded-full animate-spin mx-auto mb-2"></div>
                  <p class="text-xs sm:text-sm text-theme-muted">Cargando datos...</p>
                </div>
              </div>
              <div v-else-if="chartData.tendenciaMes" class="h-48 sm:h-56 md:h-64 xl:h-72 2xl:h-80">
                <LineChart :chartData="chartData.tendenciaMes" />
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>

    <!-- Footer Profesional Compacto -->
    <footer class="bg-theme-navbar border-t border-theme-primary sm:border-t-2 px-3 sm:px-4 lg:px-6 xl:px-8 py-4 sm:py-6 flex-shrink-0 mt-auto">
      <div class="max-w-[1920px] 2xl:max-w-[2400px] mx-auto">
        <!-- Contenido principal del footer -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-6 gap-4 sm:gap-6 xl:gap-8 mb-3 sm:mb-4">

          <!-- Columna 1: Información institucional -->
          <div class="space-y-1.5 sm:space-y-2 xl:col-span-2">
            <div class="flex items-center gap-2 mb-1.5 sm:mb-2">
              <ApplicationLogo alt="SENA Logo" classes="h-7 sm:h-8 w-auto" />
              <div>
                <h3 class="text-theme-primary font-bold text-xs sm:text-sm">CTAccess</h3>
                <p class="text-theme-muted text-[9px] sm:text-[10px]">Control de Acceso</p>
              </div>
            </div>
            <p class="text-theme-muted text-[11px] sm:text-xs leading-relaxed hidden sm:block">
              Sistema integral de control y gestión de accesos para el SENA.
              Seguridad, eficiencia y tecnología al servicio de la institución.
            </p>
            <p class="text-theme-muted text-[11px] leading-relaxed sm:hidden">
              Control de accesos SENA
            </p>
            <div class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs">
              <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-green-500 rounded-full animate-pulse"></div>
              <span class="text-theme-muted font-semibold">Sistema Operativo</span>
            </div>
          </div>

          <!-- Columna 2: Enlaces rápidos -->
          <div class="space-y-1.5 sm:space-y-2">
            <h4 class="text-theme-primary font-bold text-xs sm:text-sm mb-1.5 sm:mb-2 flex items-center gap-1.5 sm:gap-2">
              <Icon name="link" :size="12" class="sm:hidden" />
              <Icon name="link" :size="14" class="hidden sm:block" />
              Enlaces Rápidos
            </h4>
            <nav class="space-y-1.5 sm:space-y-2">
              <Link :href="route('home')"
                class="flex items-center gap-1.5 sm:gap-2 text-theme-muted hover:text-theme-primary text-[11px] sm:text-xs transition-colors duration-200 group">
              <Icon name="home" :size="11" class="group-hover:scale-110 transition-transform sm:hidden" />
              <Icon name="home" :size="12" class="group-hover:scale-110 transition-transform hidden sm:block" />
              <span>Inicio</span>
              </Link>
              <Link v-if="$page.props.auth && $page.props.auth.user" :href="route('dashboard')"
                class="flex items-center gap-1.5 sm:gap-2 text-theme-muted hover:text-theme-primary text-[11px] sm:text-xs transition-colors duration-200 group">
              <Icon name="layout-dashboard" :size="11" class="group-hover:scale-110 transition-transform sm:hidden" />
              <Icon name="layout-dashboard" :size="12" class="group-hover:scale-110 transition-transform hidden sm:block" />
              <span>Panel de Control</span>
              </Link>
              <Link :href="route('personas.create')"
                class="flex items-center gap-1.5 sm:gap-2 text-theme-muted hover:text-theme-primary text-[11px] sm:text-xs transition-colors duration-200 group">
              <Icon name="user-plus" :size="11" class="group-hover:scale-110 transition-transform sm:hidden" />
              <Icon name="user-plus" :size="12" class="group-hover:scale-110 transition-transform hidden sm:block" />
              <span>Registro</span>
              </Link>
              <a href="#"
                class="flex items-center gap-1.5 sm:gap-2 text-theme-muted hover:text-theme-primary text-[11px] sm:text-xs transition-colors duration-200 group hidden sm:flex">
                <Icon name="help-circle" :size="12" class="group-hover:scale-110 transition-transform" />
                <span>Ayuda y Soporte</span>
              </a>
              <a href="#"
                class="flex items-center gap-1.5 sm:gap-2 text-theme-muted hover:text-theme-primary text-[11px] sm:text-xs transition-colors duration-200 group hidden sm:flex">
                <Icon name="book-open" :size="12" class="group-hover:scale-110 transition-transform" />
                <span>Documentación</span>
              </a>
            </nav>
          </div>

          <!-- Columna 3: Contacto -->
          <div class="space-y-1.5 sm:space-y-2">
            <h4 class="text-theme-primary font-bold text-xs sm:text-sm mb-1.5 sm:mb-2 flex items-center gap-1.5 sm:gap-2">
              <Icon name="mail" :size="12" class="sm:hidden" />
              <Icon name="mail" :size="14" class="hidden sm:block" />
              Contacto
            </h4>
            <div class="space-y-1.5 sm:space-y-2">
              <div class="flex items-start gap-1.5 sm:gap-2 hidden sm:flex">
                <Icon name="building-2" :size="12" class="text-theme-muted mt-0.5 flex-shrink-0 sm:hidden" />
                <Icon name="building-2" :size="14" class="text-theme-muted mt-0.5 flex-shrink-0 hidden sm:block" />
                <div>
                  <p class="text-theme-primary font-semibold text-[11px] sm:text-xs">SENA</p>
                  <p class="text-theme-muted text-[10px] sm:text-[11px]">Servicio Nacional de Aprendizaje</p>
                </div>
              </div>
              <a href="mailto:ctaccesscqta@gmail.com"
                class="flex items-start gap-1.5 sm:gap-2 text-theme-muted hover:text-blue-500 text-[11px] sm:text-xs transition-colors duration-200 group">
                <Icon name="mail" :size="12" class="mt-0.5 flex-shrink-0 group-hover:scale-110 transition-transform sm:hidden" />
                <Icon name="mail" :size="14" class="mt-0.5 flex-shrink-0 group-hover:scale-110 transition-transform hidden sm:block" />
                <div>
                  <p class="font-medium">Soporte Técnico</p>
                  <p class="text-[10px] sm:text-[11px] break-all">ctaccesscqta@gmail.com</p>
                </div>
              </a>
              <div class="flex items-start gap-1.5 sm:gap-2 text-theme-muted text-[11px] sm:text-xs hidden sm:flex">
                <Icon name="clock" :size="14" class="mt-0.5 flex-shrink-0" />
                <div>
                  <p class="font-medium">Horario de Atención</p>
                  <p class="text-[11px]">Lun - Sab: 6:00 AM - 10:00 PM</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Columna 4: Legal y Versión -->
          <div class="space-y-1.5 sm:space-y-2">
            <h4 class="text-theme-primary font-bold text-xs sm:text-sm mb-1.5 sm:mb-2 flex items-center gap-1.5 sm:gap-2">
              <Icon name="shield-check" :size="12" class="sm:hidden" />
              <Icon name="shield-check" :size="14" class="hidden sm:block" />
              Información Legal
            </h4>
            <nav class="space-y-1.5 sm:space-y-2">
              <a href="#"
                class="flex items-center gap-1.5 sm:gap-2 text-theme-muted hover:text-theme-primary text-[11px] sm:text-xs transition-colors duration-200 group hidden sm:flex">
                <Icon name="file-text" :size="12" class="group-hover:scale-110 transition-transform" />
                <span>Términos y Condiciones</span>
              </a>
              <a href="#"
                class="flex items-center gap-1.5 sm:gap-2 text-theme-muted hover:text-theme-primary text-[11px] sm:text-xs transition-colors duration-200 group hidden sm:flex">
                <Icon name="lock" :size="12" class="group-hover:scale-110 transition-transform" />
                <span>Política de Privacidad</span>
              </a>
              <a href="#"
                class="flex items-center gap-1.5 sm:gap-2 text-theme-muted hover:text-theme-primary text-[11px] sm:text-xs transition-colors duration-200 group hidden sm:flex">
                <Icon name="shield" :size="12" class="group-hover:scale-110 transition-transform" />
                <span>Política de Datos</span>
              </a>
            </nav>

            <!-- Info de versión -->
            <div class="mt-1.5 sm:mt-2 p-1.5 sm:p-2 bg-theme-secondary border border-theme-primary rounded-lg">
              <div class="flex items-center gap-1.5 sm:gap-2 mb-1.5 sm:mb-2">
                <Icon name="code" :size="12" class="text-blue-500 sm:hidden" />
                <Icon name="code" :size="14" class="text-blue-500 hidden sm:block" />
                <span class="text-theme-primary font-semibold text-[11px] sm:text-xs">Versión del Sistema</span>
              </div>
              <div class="space-y-0.5 sm:space-y-1">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px]">
                  <span class="text-theme-muted">Versión:</span>
                  <span class="text-theme-primary font-bold">v{{ sistema_info?.version || '1.0' }}</span>
                </div>
                <div class="flex items-center justify-between text-[10px] sm:text-[11px]">
                  <span class="text-theme-muted">Última actualización:</span>
                  <span class="text-theme-primary font-mono">{{ sistema_info?.ultima_actualizacion || '2025-10-13'
                    }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Separador -->
        <div class="border-t border-theme-primary my-2.5 sm:my-4"></div>

        <!-- Footer bottom -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2.5 sm:gap-4">
          <!-- Copyright -->
          <div class="flex items-center gap-1.5 sm:gap-2 text-[10px] sm:text-xs text-theme-muted text-center sm:text-left">
            <Icon name="copyright" :size="12" class="sm:hidden" />
            <Icon name="copyright" :size="14" class="hidden sm:block" />
            <span>{{ new Date().getFullYear() }} <strong class="text-theme-primary">SENA</strong> - Todos los derechos
              reservados</span>
          </div>

          <!-- Desarrollado por -->
          <div class="flex items-center gap-1.5 sm:gap-2 text-[10px] sm:text-xs text-theme-muted text-center">
            <Icon name="heart" :size="12" class="text-red-500 sm:hidden" />
            <Icon name="heart" :size="14" class="text-red-500 hidden sm:block" />
            <span class="hidden sm:inline">Desarrollado con</span>
            <span class="text-theme-primary font-semibold">Laravel + Vue + Inertia</span>
          </div>

          <!-- Redes sociales -->
          <div class="flex items-center gap-2 sm:gap-3">
            <a href="https://www.sena.edu.co" target="_blank"
              class="w-7 h-7 sm:w-8 sm:h-8 bg-theme-secondary border border-theme-primary rounded-lg flex items-center justify-center text-theme-muted hover:text-blue-500 hover:border-blue-500 transition-all duration-200 hover:scale-110"
              title="Sitio web SENA">
              <Icon name="globe" :size="14" class="sm:hidden" />
              <Icon name="globe" :size="16" class="hidden sm:block" />
            </a>
            <a href="mailto:ctaccesscqta@gmail.com"
              class="w-7 h-7 sm:w-8 sm:h-8 bg-theme-secondary border border-theme-primary rounded-lg flex items-center justify-center text-theme-muted hover:text-red-500 hover:border-red-500 transition-all duration-200 hover:scale-110"
              title="Enviar email">
              <Icon name="mail" :size="14" class="sm:hidden" />
              <Icon name="mail" :size="16" class="hidden sm:block" />
            </a>
            <a href="#"
              class="w-7 h-7 sm:w-8 sm:h-8 bg-theme-secondary border border-theme-primary rounded-lg flex items-center justify-center text-theme-muted hover:text-green-500 hover:border-green-500 transition-all duration-200 hover:scale-110"
              title="Soporte técnico">
              <Icon name="life-buoy" :size="14" class="sm:hidden" />
              <Icon name="life-buoy" :size="16" class="hidden sm:block" />
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
/* ⚡ ANIMACIÓN SPOTLIGHT - Efecto optimizado para registros nuevos */
.spotlight-enter-active {
  animation: spotlightEntrance 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.spotlight-leave-active {
  animation: spotlightExit 0.4s ease-out;
  position: absolute;
  width: 100%;
}

.spotlight-move {
  transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* 🎬 Entrada optimizada con transición suave */
@keyframes spotlightEntrance {
  0% {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }

  60% {
    transform: translateY(5px) scale(1.02);
  }

  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes spotlightExit {
  0% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }

  100% {
    opacity: 0;
    transform: translateY(-15px) scale(0.9);
  }
}

/* 🎯 Spotlight Card - Efecto de brillo optimizado */
.spotlight-card {
  animation: cardPulse 2.5s ease-in-out infinite;
  position: relative;
}

@keyframes cardPulse {
  0%, 100% {
    box-shadow: 0 0 15px rgba(253, 195, 0, 0.4);
  }

  50% {
    box-shadow: 0 0 25px rgba(253, 195, 0, 0.6);
  }
}

/* 💡 Corner Spotlights - Optimizado para mejor rendimiento */
.corner-spotlight {
  position: absolute;
  width: 12px;
  height: 12px;
  background: #FDC300;
  opacity: 0;
  animation: cornerFlash 2s ease-in-out infinite;
  will-change: opacity, transform;
}

.corner-spotlight.top-left {
  top: -1px;
  left: -1px;
  border-radius: 0 0 100% 0;
  animation-delay: 0s;
}

.corner-spotlight.top-right {
  top: -1px;
  right: -1px;
  border-radius: 0 0 0 100%;
  animation-delay: 0.4s;
}

.corner-spotlight.bottom-left {
  bottom: -1px;
  left: -1px;
  border-radius: 0 100% 0 0;
  animation-delay: 0.8s;
}

.corner-spotlight.bottom-right {
  bottom: -1px;
  right: -1px;
  border-radius: 100% 0 0 0;
  animation-delay: 1.2s;
}

@keyframes cornerFlash {
  0%, 100% {
    opacity: 0;
    transform: scale(0);
  }

  50% {
    opacity: 0.7;
    transform: scale(1);
  }
}

/* 🔔 Notification Badge - Badge animado optimizado */
.notification-badge {
  animation: badgeBounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 2;
}

@keyframes badgeBounce {
  0%, 100% {
    transform: scale(1) rotate(0deg);
  }

  50% {
    transform: scale(1.2) rotate(-8deg);
  }
}

/* 🌀 Shake Animation - Optimizado */
.shake {
  animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) 2;
}

@keyframes shake {
  0%, 100% {
    transform: translateX(0) scale(1);
  }

  25% {
    transform: translateX(-3px) rotate(-3deg) scale(1.05);
  }

  75% {
    transform: translateX(3px) rotate(3deg) scale(1.05);
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

/* 📜 Custom Scrollbar - Oculto para diseño limpio */
.custom-scrollbar {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.custom-scrollbar::-webkit-scrollbar {
  display: none;
}

/* 🎨 Hover effects para cards - Sutil y elegante */
.group:hover {
  transform: translateY(-1px);
  transition: all 0.2s ease-out;
}

/* 🎨 Stats Cubitos Animados - Diseño Mejorado */
.container-items {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  max-width: 1600px;
  margin: 0 auto;
  gap: 4px;
  padding: 0 4px;
}

@media (min-width: 640px) {
  .container-items {
    gap: 8px;
    padding: 0 8px;
  }
}

@media (min-width: 1024px) {
  .container-items {
    gap: 12px;
    padding: 0 16px;
  }
}

@media (min-width: 1280px) {
  .container-items {
    gap: 14px;
    padding: 0 20px;
  }
}

@media (min-width: 1536px) {
  .container-items {
    gap: 16px;
    padding: 0 24px;
  }
}

.item-color {
  position: relative;
  flex: 1;
  height: 40px;
  min-width: 40px;
  max-width: 120px;
  border: none;
  outline: none;
  cursor: pointer;
  background: transparent;
  overflow: visible;
  transition: flex 400ms cubic-bezier(0.34, 1.56, 0.64, 1), transform 300ms ease;
}

@media (min-width: 640px) {
  .item-color {
    height: 50px;
    min-width: 50px;
    max-width: 140px;
  }
}

@media (min-width: 1024px) {
  .item-color {
    height: 60px;
    min-width: 60px;
    max-width: 160px;
  }
}

@media (min-width: 1280px) {
  .item-color {
    height: 70px;
    min-width: 70px;
    max-width: 180px;
  }
}

@media (min-width: 1536px) {
  .item-color {
    height: 75px;
    min-width: 75px;
    max-width: 200px;
  }
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
  gap: 6px;
  padding: 0 8px;
  opacity: 0.5;
  filter: grayscale(0.6) brightness(0.7) saturate(0.8);
  transition: all 400ms cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
  will-change: opacity, filter, box-shadow, transform;
}

@media (min-width: 640px) {
  .item-inner {
    border-radius: 10px;
    gap: 8px;
    padding: 0 10px;
  }
}

@media (min-width: 1024px) {
  .item-inner {
    border-radius: 12px;
    gap: 10px;
    padding: 0 14px;
  }
}

@media (min-width: 1280px) {
  .item-inner {
    border-radius: 14px;
    gap: 12px;
    padding: 0 16px;
  }
}

@media (min-width: 1536px) {
  .item-inner {
    border-radius: 16px;
    gap: 14px;
    padding: 0 18px;
  }
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
  transition: transform 400ms cubic-bezier(0.34, 1.56, 0.64, 1);
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
  transition: opacity 400ms ease-out, width 400ms cubic-bezier(0.34, 1.56, 0.64, 1);
  white-space: nowrap;
}

.stat-value {
  font-size: 14px;
  font-weight: 900;
  line-height: 1;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5),
               0 1px 2px rgba(0, 0, 0, 0.3);
}

@media (min-width: 640px) {
  .stat-value {
    font-size: 16px;
  }
}

@media (min-width: 1024px) {
  .stat-value {
    font-size: 19px;
  }
}

@media (min-width: 1280px) {
  .stat-value {
    font-size: 22px;
  }
}

@media (min-width: 1536px) {
  .stat-value {
    font-size: 24px;
  }
}

.stat-label {
  font-size: 8px;
  font-weight: 700;
  line-height: 1.2;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  opacity: 1;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
}

@media (min-width: 640px) {
  .stat-label {
    font-size: 9px;
    letter-spacing: 0.7px;
  }
}

@media (min-width: 1024px) {
  .stat-label {
    font-size: 10px;
    letter-spacing: 0.9px;
  }
}

@media (min-width: 1280px) {
  .stat-label {
    font-size: 11px;
    letter-spacing: 1px;
  }
}

@media (min-width: 1536px) {
  .stat-label {
    font-size: 12px;
    letter-spacing: 1.1px;
  }
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
  flex: 2.2;
  z-index: 50;
  transform: scale(1.05);
}

@media (min-width: 640px) {
  .item-color.active-color {
    flex: 2.5;
    transform: scale(1.05);
  }
}

@media (min-width: 1024px) {
  .item-color.active-color {
    flex: 2.8;
    transform: scale(1.08);
  }
}

@media (min-width: 1280px) {
  .item-color.active-color {
    flex: 3;
    transform: scale(1.08);
  }
}

@media (min-width: 1536px) {
  .item-color.active-color {
    flex: 3.2;
    transform: scale(1.1);
  }
}

.item-color.active-color .item-inner {
  opacity: 1;
  filter: grayscale(0) brightness(1) saturate(1);
  box-shadow: 0 10px 30px -4px var(--color),
    0 0 50px -10px var(--color),
    inset 0 2px 0 rgba(255, 255, 255, 0.25);
  border: 2px solid rgba(255, 255, 255, 0.35);
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
  padding-left: 6px;
}

@media (min-width: 640px) {
  .item-color.active-color .stat-data {
    padding-left: 8px;
  }
}

@media (min-width: 1024px) {
  .item-color.active-color .stat-data {
    padding-left: 10px;
  }
}

@media (min-width: 1280px) {
  .item-color.active-color .stat-data {
    padding-left: 12px;
  }
}

@media (min-width: 1536px) {
  .item-color.active-color .stat-data {
    padding-left: 14px;
  }
}

/* Hover suave en todos los cubitos */
.item-color:hover .item-inner {
  opacity: 0.7;
  filter: grayscale(0.4) brightness(0.85) saturate(0.9);
  transform: translateY(-2px) scale(1.02);
}

.item-color.active-color:hover .item-inner {
  opacity: 1;
  transform: translateY(-2px);
}

/* Modo claro - Hover más oscuro para mejor visibilidad */
:not(.dark) .item-color:hover .item-inner {
  opacity: 0.7;
  filter: grayscale(0.4) brightness(0.85) saturate(0.95);
  transform: translateY(-2px) scale(1.02);
}

:not(.dark) .item-color.active-color:hover .item-inner {
  opacity: 1;
  filter: grayscale(0) brightness(0.95) saturate(1.05);
  transform: translateY(-2px);
}

/* Modo oscuro - Hover más claro y brillante */
.dark .item-color:hover .item-inner {
  opacity: 0.8;
  filter: grayscale(0.3) brightness(1.05) saturate(1.05);
  transform: translateY(-2px) scale(1.02);
}

.dark .item-color.active-color:hover .item-inner {
  opacity: 1;
  filter: grayscale(0) brightness(1.25) saturate(1.2);
  transform: translateY(-2px);
}

/* Mejoras adicionales para móviles muy pequeños */
@media (max-width: 375px) {
  .container-items {
    gap: 2px;
    padding: 0 2px;
  }

  .item-color {
    min-width: 30px;
    height: 32px;
  }

  .item-inner {
    border-radius: 5px;
    padding: 0 4px;
  }

  .stat-value {
    font-size: 11px;
  }

  .stat-label {
    font-size: 6px;
    letter-spacing: 0.3px;
  }

  .item-color.active-color {
    flex: 1.8;
  }
}

/* 🖥️ Optimizaciones para pantallas ultra-anchas y 4K */
@media (min-width: 1920px) {
  .container-items {
    gap: 14px;
    padding: 0 24px;
  }

  .item-color {
    height: 70px;
    min-width: 70px;
  }

  .item-inner {
    border-radius: 14px;
    gap: 14px;
    padding: 0 18px;
  }

  .stat-value {
    font-size: 22px;
  }

  .stat-label {
    font-size: 12px;
    letter-spacing: 1.1px;
  }

  .item-color.active-color {
    flex: 3.2;
  }

  .item-color.active-color .stat-data {
    padding-left: 14px;
  }
}

@media (min-width: 2560px) {
  .container-items {
    gap: 16px;
    padding: 0 32px;
  }

  .item-color {
    height: 80px;
    min-width: 80px;
  }

  .item-inner {
    border-radius: 16px;
    gap: 16px;
    padding: 0 20px;
  }

  .stat-value {
    font-size: 24px;
  }

  .stat-label {
    font-size: 13px;
    letter-spacing: 1.2px;
  }

  .item-color.active-color {
    flex: 3.5;
  }

  .item-color.active-color .stat-data {
    padding-left: 16px;
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
