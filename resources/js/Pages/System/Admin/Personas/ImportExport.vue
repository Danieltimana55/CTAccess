<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import SystemLayout from '@/Layouts/System/SystemLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    stats: Object,
    programas: Array,
});

// Estado
const activeTab = ref('export');
const exportForm = useForm({
    rol_persona: '',
    programa_formacion_id: '',
});
const importForm = useForm({
    file: null,
    update_existing: false,
});
const fileInput = ref(null);
const fileName = ref('');
const validationResult = ref(null);
const importResult = ref(null);
const history = ref([]);
const showHistoryModal = ref(false);
const isProcessing = ref(false);

// Computadas
const canValidate = computed(() => importForm.file !== null);
const canImport = computed(() => validationResult.value?.valid === true);

// Métodos
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        importForm.file = file;
        fileName.value = file.name;
        validationResult.value = null;
        importResult.value = null;
    }
};

const validateFile = async () => {
    if (!importForm.file) return;

    isProcessing.value = true;
    const formData = new FormData();
    formData.append('file', importForm.file);

    try {
        const response = await fetch(route('system.admin.personas.import-export.validate'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        validationResult.value = await response.json();
    } catch (error) {
        console.error('Error validando archivo:', error);
        validationResult.value = {
            valid: false,
            errors: ['Error al validar el archivo'],
        };
    } finally {
        isProcessing.value = false;
    }
};

const downloadTemplate = () => {
    window.location.href = route('system.admin.personas.import-export.template');
};

const exportData = () => {
    const params = new URLSearchParams();
    if (exportForm.rol_persona) params.append('rol_persona', exportForm.rol_persona);
    if (exportForm.programa_formacion_id) params.append('programa_formacion_id', exportForm.programa_formacion_id);
    
    window.location.href = route('system.admin.personas.import-export.export') + '?' + params.toString();
};

const importData = async () => {
    if (!canImport.value) return;

    isProcessing.value = true;
    const formData = new FormData();
    formData.append('file', importForm.file);
    formData.append('update_existing', importForm.update_existing ? '1' : '0');

    try {
        const response = await fetch(route('system.admin.personas.import-export.import'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        importResult.value = await response.json();

        if (importResult.value.success) {
            // Resetear formulario
            resetImport();
            // Recargar página para actualizar estadísticas
            setTimeout(() => router.reload(), 2000);
        }
    } catch (error) {
        console.error('Error importando datos:', error);
        importResult.value = {
            success: false,
            message: 'Error al importar el archivo',
            errors: [],
            stats: { success: 0, errors: 0, total: 0 },
        };
    } finally {
        isProcessing.value = false;
    }
};

const resetImport = () => {
    importForm.file = null;
    fileName.value = '';
    validationResult.value = null;
    importResult.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const loadHistory = async () => {
    try {
        const response = await fetch(route('system.admin.personas.import-export.history'));
        history.value = await response.json();
        showHistoryModal.value = true;
    } catch (error) {
        console.error('Error cargando historial:', error);
    }
};
</script>

<template>
    <Head title="Importar/Exportar Personas" />

    <SystemLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Importar/Exportar Personas
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Gestiona la importación y exportación masiva de personas en formato CSV
                    </p>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <Icon name="users" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Personas</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_personas }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <Icon name="academic-cap" class="w-6 h-6 text-green-600 dark:text-green-400" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Aprendices</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_aprendices }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                    <Icon name="briefcase" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Instructores</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_instructores }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                    <Icon name="building-office" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Funcionarios</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_funcionarios }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex -mb-px">
                            <button
                                @click="activeTab = 'export'"
                                :class="[
                                    activeTab === 'export'
                                        ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                    'flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors'
                                ]"
                            >
                                <Icon name="arrow-down-tray" class="w-5 h-5 inline-block mr-2" />
                                Exportar
                            </button>
                            <button
                                @click="activeTab = 'import'"
                                :class="[
                                    activeTab === 'import'
                                        ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                    'flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors'
                                ]"
                            >
                                <Icon name="arrow-up-tray" class="w-5 h-5 inline-block mr-2" />
                                Importar
                            </button>
                            <button
                                @click="loadHistory"
                                class="py-4 px-6 text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 font-medium text-sm transition-colors"
                            >
                                <Icon name="clock" class="w-5 h-5 inline-block mr-2" />
                                Historial
                            </button>
                        </nav>
                    </div>

                    <!-- Export Tab -->
                    <div v-if="activeTab === 'export'" class="p-6">
                        <div class="max-w-3xl">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Exportar Personas
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                Descarga un archivo CSV con la información de las personas registradas. 
                                Puedes aplicar filtros para exportar solo registros específicos.
                            </p>

                            <!-- Filtros -->
                            <div class="space-y-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Rol de Persona
                                    </label>
                                    <select
                                        v-model="exportForm.rol_persona"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Todos los roles</option>
                                        <option value="aprendiz">Aprendiz</option>
                                        <option value="instructor">Instructor</option>
                                        <option value="funcionario">Funcionario</option>
                                        <option value="visitante">Visitante</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Programa de Formación
                                    </label>
                                    <select
                                        v-model="exportForm.programa_formacion_id"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Todos los programas</option>
                                        <option v-for="programa in programas" :key="programa.id" :value="programa.id">
                                            {{ programa.codigo }} - {{ programa.nombre }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Botón Exportar -->
                            <button
                                @click="exportData"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center"
                            >
                                <Icon name="arrow-down-tray" class="w-5 h-5 mr-2" />
                                Exportar a CSV
                            </button>
                        </div>
                    </div>

                    <!-- Import Tab -->
                    <div v-if="activeTab === 'import'" class="p-6">
                        <div class="max-w-3xl">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Importar Personas
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                Carga un archivo CSV con la información de las personas. 
                                El archivo debe seguir el formato del template proporcionado.
                            </p>

                            <!-- Template Download -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                                <div class="flex">
                                    <Icon name="information-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300">
                                            ¿Primera vez importando?
                                        </h4>
                                        <p class="mt-1 text-sm text-blue-700 dark:text-blue-400">
                                            Descarga el template con el formato correcto y un ejemplo de datos.
                                        </p>
                                        <button
                                            @click="downloadTemplate"
                                            class="mt-3 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 flex items-center"
                                        >
                                            <Icon name="document-arrow-down" class="w-5 h-5 mr-1" />
                                            Descargar Template
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- File Upload -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Seleccionar Archivo CSV
                                </label>
                                <div class="flex items-center space-x-4">
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept=".csv,.txt"
                                        @change="handleFileChange"
                                        class="hidden"
                                    />
                                    <button
                                        @click="fileInput.click()"
                                        class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        Seleccionar archivo
                                    </button>
                                    <span v-if="fileName" class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ fileName }}
                                    </span>
                                </div>
                            </div>

                            <!-- Update Existing Option -->
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input
                                        v-model="importForm.update_existing"
                                        type="checkbox"
                                        class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                        Actualizar registros existentes (por número de identificación)
                                    </span>
                                </label>
                            </div>

                            <!-- Validation Button -->
                            <button
                                @click="validateFile"
                                :disabled="!canValidate || isProcessing"
                                :class="[
                                    canValidate && !isProcessing
                                        ? 'bg-yellow-600 hover:bg-yellow-700'
                                        : 'bg-gray-400 cursor-not-allowed',
                                    'w-full text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center mb-4'
                                ]"
                            >
                                <Icon name="exclamation-triangle" class="w-5 h-5 mr-2" />
                                {{ isProcessing ? 'Validando...' : 'Validar Archivo' }}
                            </button>

                            <!-- Validation Result -->
                            <div v-if="validationResult" class="mb-6">
                                <div
                                    v-if="validationResult.valid"
                                    class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4"
                                >
                                    <div class="flex">
                                        <Icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" />
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-green-900 dark:text-green-300">
                                                Archivo válido
                                            </h4>
                                            <p class="mt-1 text-sm text-green-700 dark:text-green-400">
                                                El archivo contiene {{ validationResult.rows }} filas y puede ser importado.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4"
                                >
                                    <div class="flex">
                                        <Icon name="x-circle" class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" />
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-red-900 dark:text-red-300">
                                                Errores de validación
                                            </h4>
                                            <ul class="mt-2 text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-1">
                                                <li v-for="(error, index) in validationResult.errors" :key="index">
                                                    {{ error }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Import Button -->
                            <button
                                @click="importData"
                                :disabled="!canImport || isProcessing"
                                :class="[
                                    canImport && !isProcessing
                                        ? 'bg-green-600 hover:bg-green-700'
                                        : 'bg-gray-400 cursor-not-allowed',
                                    'w-full text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center'
                                ]"
                            >
                                <Icon name="arrow-up-tray" class="w-5 h-5 mr-2" />
                                {{ isProcessing ? 'Importando...' : 'Importar Datos' }}
                            </button>

                            <!-- Import Result -->
                            <div v-if="importResult" class="mt-6">
                                <div
                                    :class="[
                                        importResult.success
                                            ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
                                            : 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                                        'border rounded-lg p-4'
                                    ]"
                                >
                                    <div class="flex">
                                        <Icon
                                            :name="importResult.success ? 'check-circle' : 'exclamation-triangle'"
                                            :class="[
                                                importResult.success
                                                    ? 'text-green-600 dark:text-green-400'
                                                    : 'text-yellow-600 dark:text-yellow-400',
                                                'w-5 h-5 flex-shrink-0'
                                            ]"
                                        />
                                        <div class="ml-3 flex-1">
                                            <h4
                                                :class="[
                                                    importResult.success
                                                        ? 'text-green-900 dark:text-green-300'
                                                        : 'text-yellow-900 dark:text-yellow-300',
                                                    'text-sm font-medium'
                                                ]"
                                            >
                                                {{ importResult.message }}
                                            </h4>
                                            <div class="mt-2 text-sm">
                                                <p
                                                    :class="[
                                                        importResult.success
                                                            ? 'text-green-700 dark:text-green-400'
                                                            : 'text-yellow-700 dark:text-yellow-400'
                                                    ]"
                                                >
                                                    Exitosos: {{ importResult.stats.success }} | 
                                                    Errores: {{ importResult.stats.errors }} | 
                                                    Total: {{ importResult.stats.total }}
                                                </p>
                                            </div>

                                            <!-- Errores -->
                                            <div v-if="importResult.errors.length > 0" class="mt-3">
                                                <details class="cursor-pointer">
                                                    <summary class="text-sm font-medium text-red-900 dark:text-red-300">
                                                        Ver errores ({{ importResult.errors.length }})
                                                    </summary>
                                                    <ul class="mt-2 space-y-1 text-sm text-red-700 dark:text-red-400 max-h-60 overflow-y-auto">
                                                        <li v-for="(error, index) in importResult.errors" :key="index" class="border-l-2 border-red-300 pl-2">
                                                            <span class="font-medium">Fila {{ error.row }}:</span> {{ error.message }}
                                                        </li>
                                                    </ul>
                                                </details>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Modal -->
        <div
            v-if="showHistoryModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity"
                    @click="showHistoryModal = false"
                ></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Historial de Importaciones/Exportaciones
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acción</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Usuario</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in history" :key="item.id">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    item.action === 'export' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                    'px-2 py-1 text-xs font-medium rounded-full'
                                                ]"
                                            >
                                                {{ item.action === 'export' ? 'Exportación' : 'Importación' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                            {{ item.description }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            {{ item.usuario }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                            {{ item.created_at }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            @click="showHistoryModal = false"
                            class="w-full sm:w-auto bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </SystemLayout>
</template>
