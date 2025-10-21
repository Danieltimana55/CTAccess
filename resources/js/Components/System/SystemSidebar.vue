<script setup>
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Icon from '@/Components/Icon.vue'

const props = defineProps({
  menus: { type: Array, default: () => [] },
  role: { type: String, default: null },
  collapsed: { type: Boolean, default: false },
})

const emit = defineEmits(['toggle-collapse'])

const currentRoute = computed(() => route().current())

const go = (routeName) => {
  if (routeName) router.visit(route(routeName))
}

const isActiveRoute = (routeName) => {
  if (!routeName) return false
  return currentRoute.value === routeName || currentRoute.value?.startsWith(routeName)
}

const getIconName = (label) => {
  const iconMap = {
    'Dashboard': 'layout-dashboard',
    'Personas': 'users',
    'Accesos': 'key-round',
    'Verificación QR': 'qr-code',
    'Incidencias': 'alert-triangle',
    'Historial': 'clock',
    'Gestión de Usuarios': 'user-cog',
    'Permisos': 'shield',
    'Portátiles': 'laptop',
    'Vehículos': 'car'
  }
  return iconMap[label] || 'circle'
}
</script>

<template>
  <aside :class="[
    'hidden lg:flex lg:shrink-0 lg:flex-col lg:border-r border-theme-primary bg-theme-sidebar shadow-theme-sm transition-all duration-300',
    collapsed ? 'lg:w-14' : 'lg:w-60'
  ]">
    <!-- Collapse Button -->
    <div class="border-b border-theme-primary bg-theme-secondary px-2 py-1.5">
      <button
        @click="emit('toggle-collapse')"
        :class="[
          'group relative w-full overflow-hidden rounded-md p-2 transition-all duration-200',
          'bg-sena-green-500/10 dark:bg-blue-500/10',
          'hover:bg-sena-green-500/20 dark:hover:bg-blue-500/20',
          'border border-sena-green-500/20 dark:border-cyan-500/20',
          'hover:border-sena-green-500/40 dark:hover:border-cyan-500/40',
          'flex items-center justify-center'
        ]"
        :title="collapsed ? 'Expandir menú' : 'Contraer menú'"
      >
        <Icon :name="collapsed ? 'chevron-right' : 'chevron-left'" :size="16" class="text-sena-green-600 dark:text-cyan-500" />
      </button>
    </div>

    <!-- Rol Badge -->
    <div v-if="!collapsed" class="flex items-center gap-2 border-b border-theme-primary bg-theme-secondary px-3 py-2.5">
      <div class="flex h-7 w-7 items-center justify-center rounded-full bg-sena-green-600 dark:bg-cyan-600">
        <Icon name="user" :size="14" class="text-white" />
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-[10px] font-medium text-theme-secondary uppercase tracking-wider">Rol</div>
        <div class="text-xs font-semibold text-theme-primary truncate">{{ role || '—' }}</div>
      </div>
    </div>

    <!-- Collapsed role indicator -->
    <div v-if="collapsed" class="flex justify-center border-b border-theme-primary bg-theme-secondary py-2">
      <div class="flex h-7 w-7 items-center justify-center rounded-full bg-sena-green-600 dark:bg-cyan-600" :title="`Rol: ${role || '—'}`">
        <Icon name="user" :size="14" class="text-white" />
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 space-y-1 px-2 py-2 overflow-y-auto">
      <button
        v-for="item in menus"
        :key="item.route"
        :class="[
          'group flex w-full items-center rounded-md text-left font-medium transition-all duration-200',
          collapsed ? 'px-1.5 py-2.5 justify-center' : 'px-3 py-2.5',
          isActiveRoute(item.route)
            ? 'bg-sena-green-600 dark:bg-blue-600 text-white shadow-sm' + (collapsed ? '' : ' border-l-3 border-sena-green-300 dark:border-cyan-400')
            : 'text-theme-secondary hover:bg-sena-green-50 dark:hover:bg-sena-blue-900/20 hover:text-sena-green-700 dark:hover:text-cyan-400'
        ]"
        @click="go(item.route)"
        :title="collapsed ? item.label : undefined"
      >
        <div :class="['flex items-center', collapsed ? '' : 'gap-2.5']">
          <div :class="[
            'flex h-7 w-7 items-center justify-center rounded-md transition-all duration-200',
            isActiveRoute(item.route)
              ? 'bg-white/20 text-white'
              : 'text-theme-muted group-hover:text-sena-green-600 dark:group-hover:text-cyan-400'
          ]">
            <Icon :name="getIconName(item.label)" :size="16" />
          </div>
          <span v-if="!collapsed" :class="[
            'text-[13px] font-medium transition-all duration-200 truncate',
            isActiveRoute(item.route)
              ? 'text-white font-semibold'
              : 'group-hover:translate-x-0.5'
          ]">{{ item.label }}</span>
        </div>
      </button>
    </nav>

    <!-- Footer -->
    <div class="border-t border-theme-primary bg-theme-secondary px-2 py-1.5">
      <div v-if="!collapsed" class="text-[10px] text-theme-muted text-center font-medium">
        CTAccess v2.0
      </div>
      <div v-else class="flex justify-center">
        <div class="text-[10px] text-theme-muted font-medium" title="CTAccess v2.0">
          CT
        </div>
      </div>
    </div>
  </aside>

  <!-- Mobile Sidebar Placeholder -->
  <div class="lg:hidden">
    <!-- Este espacio se puede usar para un sidebar móvil desplegable en el futuro -->
  </div>
</template>
