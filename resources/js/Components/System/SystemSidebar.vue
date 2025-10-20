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
    collapsed ? 'lg:w-16' : 'lg:w-64'
  ]">
    <!-- Collapse Button (Moved to top) -->
    <div class="border-b border-theme-primary bg-theme-secondary p-2">
      <button
        @click="emit('toggle-collapse')"
        :class="[
          'group relative w-full overflow-hidden rounded-lg p-3 transition-all duration-300',
          'bg-sena-green-500/10 dark:bg-blue-500/10',
          'hover:bg-sena-green-500/20 dark:hover:bg-blue-500/20',
          'border border-sena-green-500/30 dark:border-cyan-500/30',
          'hover:border-sena-green-500 dark:hover:border-cyan-500',
          'flex justify-center'
        ]"
        :title="collapsed ? 'Expandir menú' : 'Contraer menú'"
      >
        <div class="flex items-center justify-center rounded-lg bg-sena-green-500 dark:bg-cyan-500 p-2 text-white transition-all duration-300">
          <Icon name="menu" :size="18" />
        </div>
      </button>
    </div>

    <!-- Rol Badge -->
    <div v-if="!collapsed" class="flex items-center gap-3 border-b border-theme-primary bg-theme-secondary p-4">
      <div class="flex h-8 w-8 items-center justify-center rounded-full bg-sena-green-600 dark:bg-cyan-600">
        <Icon name="user" :size="16" class="text-white" />
      </div>
      <div>
        <div class="text-xs font-medium text-theme-secondary uppercase tracking-wide">Rol Actual</div>
        <div class="text-sm font-semibold text-theme-primary">{{ role || '—' }}</div>
      </div>
    </div>

    <!-- Collapsed role indicator -->
    <div v-if="collapsed" class="flex justify-center border-b border-theme-primary bg-theme-secondary p-4">
      <div class="flex h-8 w-8 items-center justify-center rounded-full bg-sena-green-600 dark:bg-cyan-600" :title="`Rol: ${role || '—'}`">
        <Icon name="user" :size="16" class="text-white" />
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 space-y-2 p-3">
      <button
        v-for="item in menus"
        :key="item.route"
        :class="[
          'group flex w-full items-center rounded-lg text-left font-semibold transition-all duration-200 ease-in-out',
          collapsed ? 'px-2 py-3 justify-center' : 'px-4 py-3',
          isActiveRoute(item.route)
            ? 'bg-sena-green-600 dark:bg-blue-600 text-white' + (collapsed ? '' : ' border-l-4 border-sena-green-300 dark:border-cyan-400')
            : 'text-theme-secondary hover:bg-sena-green-50 dark:hover:bg-sena-blue-900/20 hover:text-sena-green-700 dark:hover:text-cyan-400 hover:border-l-4 hover:border-sena-green-500 dark:hover:border-cyan-500'
        ]"
        @click="go(item.route)"
        :title="collapsed ? item.label : undefined"
      >
        <div :class="['flex items-center', collapsed ? '' : 'gap-3.5']">
          <div :class="[
            'flex h-9 w-9 items-center justify-center rounded-lg transition-all duration-200',
            isActiveRoute(item.route)
              ? 'bg-white/20 text-white'
              : 'bg-theme-tertiary text-theme-muted group-hover:bg-sena-green-500 dark:group-hover:bg-cyan-500 group-hover:text-white'
          ]">
            <Icon :name="getIconName(item.label)" :size="18" />
          </div>
          <span v-if="!collapsed" :class="[
            'text-[15px] font-semibold transition-all duration-200',
            isActiveRoute(item.route)
              ? 'text-white'
              : 'group-hover:translate-x-1'
          ]">{{ item.label }}</span>
        </div>
      </button>
    </nav>

    <!-- Footer with version -->
    <div class="border-t border-theme-primary bg-theme-secondary">
      <!-- Version -->
      <div class="px-3 py-3">
        <div v-if="!collapsed" class="text-xs text-theme-muted text-center">
          CTAccess v2.0
        </div>
        <div v-else class="flex justify-center">
          <div class="text-xs text-theme-muted" title="CTAccess v2.0">
            CT
          </div>
        </div>
      </div>
    </div>
  </aside>

  <!-- Mobile Sidebar Placeholder -->
  <div class="lg:hidden">
    <!-- Este espacio se puede usar para un sidebar móvil desplegable en el futuro -->
  </div>
</template>
