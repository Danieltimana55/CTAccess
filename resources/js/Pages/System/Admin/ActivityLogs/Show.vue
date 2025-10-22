<script setup>
import SystemLayout from '@/Layouts/System/SystemLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  log: Object,
})

const formatDate = (date) => {
  return new Date(date).toLocaleString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  })
}

const getSeverityColor = (severity) => {
  const colors = {
    info: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    error: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    critical: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
  }
  return colors[severity] || colors.info
}
</script>

<template>
  <SystemLayout>
    <Head title="Detalle de Auditoría" />

    <template #header>
      <div class="flex items-center gap-3">
        <Link
          :href="route('system.admin.activity-logs.index')"
          class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-theme-secondary transition-colors"
        >
          <Icon name="arrow-left" :size="18" class="text-theme-primary" />
        </Link>
        <div>
          <h2 class="text-xl font-bold text-theme-primary">Detalle de Auditoría</h2>
          <p class="text-xs text-theme-secondary">Log #{{ log.id }}</p>
        </div>
      </div>
    </template>

    <div class="space-y-4">
      <!-- Información General -->
      <div class="bg-theme-card rounded-lg border border-theme-primary p-6 shadow-theme-sm">
        <h3 class="text-lg font-bold text-theme-primary mb-4">Información General</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <dt class="text-sm font-medium text-theme-secondary">Fecha y Hora</dt>
            <dd class="mt-1 text-sm text-theme-primary font-semibold">{{ formatDate(log.created_at) }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-theme-secondary">Usuario</dt>
            <dd class="mt-1 text-sm text-theme-primary font-semibold">
              {{ log.usuario_nombre || 'Sistema' }}
              <span v-if="log.usuario_rol" class="ml-2 text-xs text-theme-secondary">({{ log.usuario_rol }})</span>
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-theme-secondary">Acción</dt>
            <dd class="mt-1">
              <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-theme-secondary text-sm font-medium text-theme-primary">
                {{ log.action }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-theme-secondary">Módulo</dt>
            <dd class="mt-1 text-sm text-theme-primary font-semibold">{{ log.module || 'N/A' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-theme-secondary">Severidad</dt>
            <dd class="mt-1">
              <span :class="getSeverityColor(log.severity)" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold">
                {{ log.severity }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-theme-secondary">Código de Estado</dt>
            <dd class="mt-1 text-sm text-theme-primary font-mono">{{ log.status_code || 'N/A' }}</dd>
          </div>
        </dl>
      </div>

      <!-- Descripción -->
      <div class="bg-theme-card rounded-lg border border-theme-primary p-6 shadow-theme-sm">
        <h3 class="text-lg font-bold text-theme-primary mb-3">Descripción</h3>
        <p class="text-sm text-theme-secondary">{{ log.description }}</p>
      </div>

      <!-- Modelo Afectado -->
      <div v-if="log.model_type" class="bg-theme-card rounded-lg border border-theme-primary p-6 shadow-theme-sm">
        <h3 class="text-lg font-bold text-theme-primary mb-4">Modelo Afectado</h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <dt class="text-sm font-medium text-theme-secondary">Tipo</dt>
            <dd class="mt-1 text-sm text-theme-primary font-mono">{{ log.model_type }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-theme-secondary">ID</dt>
            <dd class="mt-1 text-sm text-theme-primary font-semibold">{{ log.model_id }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-theme-secondary">Nombre</dt>
            <dd class="mt-1 text-sm text-theme-primary font-semibold">{{ log.model_name || 'N/A' }}</dd>
          </div>
        </dl>
      </div>

      <!-- Cambios Realizados -->
      <div v-if="log.changes && Object.keys(log.changes).length" class="bg-theme-card rounded-lg border border-theme-primary p-6 shadow-theme-sm">
        <h3 class="text-lg font-bold text-theme-primary mb-4">Cambios Realizados</h3>
        <div class="space-y-3">
          <div v-for="(change, field) in log.changes" :key="field" class="border border-theme-primary rounded-lg p-3">
            <div class="text-sm font-semibold text-theme-primary mb-2">{{ field }}</div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <div class="text-xs font-medium text-theme-secondary mb-1">Anterior</div>
                <div class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-3 py-2 rounded font-mono">
                  {{ change.old || '(vacío)' }}
                </div>
              </div>
              <div>
                <div class="text-xs font-medium text-theme-secondary mb-1">Nuevo</div>
                <div class="text-sm text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-3 py-2 rounded font-mono">
                  {{ change.new || '(vacío)' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Información Técnica -->
      <div class="bg-theme-card rounded-lg border border-theme-primary p-6 shadow-theme-sm">
        <h3 class="text-lg font-bold text-theme-primary mb-4">Información Técnica</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <dt class="text-sm font-medium text-theme-secondary">Dirección IP</dt>
            <dd class="mt-1 text-sm text-theme-primary font-mono">{{ log.ip_address || 'N/A' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-theme-secondary">Método HTTP</dt>
            <dd class="mt-1 text-sm text-theme-primary font-mono">{{ log.method || 'N/A' }}</dd>
          </div>
          <div class="md:col-span-2">
            <dt class="text-sm font-medium text-theme-secondary">URL</dt>
            <dd class="mt-1 text-sm text-theme-primary font-mono break-all">{{ log.url || 'N/A' }}</dd>
          </div>
          <div class="md:col-span-2">
            <dt class="text-sm font-medium text-theme-secondary">User Agent</dt>
            <dd class="mt-1 text-xs text-theme-secondary font-mono break-all">{{ log.user_agent || 'N/A' }}</dd>
          </div>
        </dl>
      </div>

      <!-- Propiedades Adicionales -->
      <div v-if="log.properties && Object.keys(log.properties).length" class="bg-theme-card rounded-lg border border-theme-primary p-6 shadow-theme-sm">
        <h3 class="text-lg font-bold text-theme-primary mb-4">Propiedades Adicionales</h3>
        <pre class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg text-xs text-theme-primary overflow-x-auto">{{ JSON.stringify(log.properties, null, 2) }}</pre>
      </div>

      <!-- Botón Volver -->
      <div class="flex justify-start">
        <Link
          :href="route('system.admin.activity-logs.index')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-theme-primary rounded-lg text-sm font-medium transition-colors"
        >
          <Icon name="arrow-left" :size="16" />
          Volver al listado
        </Link>
      </div>
    </div>
  </SystemLayout>
</template>
