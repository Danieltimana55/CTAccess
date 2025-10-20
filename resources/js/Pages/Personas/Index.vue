<script setup>
import { Head } from '@inertiajs/vue3';
import PersonaLayout from '@/Layouts/PersonaLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    persona: {
        type: Object,
        required: true,
    },
    accesos_recientes: {
        type: Array,
        default: () => [],
    },
    estadisticas: {
        type: Object,
        default: () => ({}),
    },
});

// Formatear fecha
const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Formatear estado
const getEstadoBadge = (estado) => {
    const badges = {
        'activo': { class: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400', text: 'Activo' },
        'finalizado': { class: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', text: 'Finalizado' },
        'incidencia': { class: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400', text: 'Incidencia' },
    };
    return badges[estado] || badges.finalizado;
};
</script>

<template>
    <Head title="Inicio - Portal de Personas" />

    <PersonaLayout :persona="persona">
        <!-- Bienvenida -->
        <div class="mb-8">
            <div class="bg-sena-green-600 dark:bg-cyan-600 rounded-2xl p-8 text-white">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">
                            ¡Bienvenido, {{ persona.Nombre }}!
                        </h1>
                        <p class="text-white/90 text-lg">
                            Portal de Personas - CTAccess
                        </p>
                    </div>
                    <div class="flex items-center justify-center h-20 w-20 rounded-full bg-white/20">
                        <span class="text-4xl font-bold">
                            {{ persona.Nombre.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Personal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Datos Personales -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-4">
                    <Icon name="user" :size="24" class="text-sena-green-600 dark:text-cyan-400 mr-3" />
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Información Personal</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre Completo</label>
                        <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">{{ persona.Nombre }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Documento</label>
                        <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">{{ persona.documento || 'No registrado' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de Persona</label>
                        <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-sena-green-100 dark:bg-cyan-900/30 text-sena-green-700 dark:text-cyan-400">
                                {{ persona.TipoPersona }}
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Correo Electrónico</label>
                        <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">{{ persona.correo }}</p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Rápidas -->
            <div class="space-y-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-md p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm mb-1">Total de Accesos</p>
                            <p class="text-3xl font-bold">{{ estadisticas.total_accesos || 0 }}</p>
                        </div>
                        <Icon name="log-in" :size="32" class="opacity-80" />
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-md p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm mb-1">Accesos Este Mes</p>
                            <p class="text-3xl font-bold">{{ estadisticas.accesos_mes || 0 }}</p>
                        </div>
                        <Icon name="calendar" :size="32" class="opacity-80" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Portátiles y Vehículos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Portátiles -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-4">
                    <Icon name="laptop" :size="24" class="text-purple-600 dark:text-purple-400 mr-3" />
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Mis Portátiles</h2>
                </div>
                
                <div v-if="persona.portatiles && persona.portatiles.length > 0" class="space-y-3">
                    <div
                        v-for="portatil in persona.portatiles"
                        :key="portatil.id"
                        class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600"
                    >
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ portatil.marca }} {{ portatil.modelo }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Serial: {{ portatil.serial }}</p>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <Icon name="laptop" :size="48" class="mx-auto text-gray-300 dark:text-gray-600 mb-3" />
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No tienes portátiles registrados</p>
                </div>
            </div>

            <!-- Vehículos -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-4">
                    <Icon name="truck" :size="24" class="text-orange-600 dark:text-orange-400 mr-3" />
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Mis Vehículos</h2>
                </div>
                
                <div v-if="persona.vehiculos && persona.vehiculos.length > 0" class="space-y-3">
                    <div
                        v-for="vehiculo in persona.vehiculos"
                        :key="vehiculo.id"
                        class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600"
                    >
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ vehiculo.tipo }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Placa: {{ vehiculo.placa }}</p>
                        <p v-if="vehiculo.marca" class="text-xs text-gray-500 dark:text-gray-400">{{ vehiculo.marca }} {{ vehiculo.modelo }}</p>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <Icon name="truck" :size="48" class="mx-auto text-gray-300 dark:text-gray-600 mb-3" />
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No tienes vehículos registrados</p>
                </div>
            </div>
        </div>

        <!-- Accesos Recientes -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center mb-6">
                <Icon name="clock" :size="24" class="text-sena-green-600 dark:text-cyan-400 mr-3" />
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Historial de Accesos Recientes</h2>
            </div>
            
            <div v-if="accesos_recientes && accesos_recientes.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Fecha Entrada
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Fecha Salida
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="acceso in accesos_recientes" :key="acceso.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ formatDate(acceso.fecha_entrada) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ acceso.fecha_salida ? formatDate(acceso.fecha_salida) : 'En curso' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="getEstadoBadge(acceso.estado).class"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                >
                                    {{ getEstadoBadge(acceso.estado).text }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-center py-12">
                <Icon name="inbox" :size="48" class="mx-auto text-gray-300 dark:text-gray-600 mb-3" />
                <p class="text-gray-500 dark:text-gray-400">No tienes accesos registrados aún</p>
            </div>
        </div>
    </PersonaLayout>
</template>
