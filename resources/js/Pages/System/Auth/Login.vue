<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    UserName: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('system.login.store'), {
        onFinish: () => form.reset('password'),
        onError: (errors) => {
            // Si hay error de token CSRF, recargar la página
            if (errors.message && (errors.message.includes('CSRF') || errors.message.includes('expired'))) {
                window.location.reload();
            }
        },
        preserveScroll: true,
        preserveState: false,
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Login Sistema" />

        <!-- Encabezado del formulario -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                Iniciar Sesión
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Ingresa tus credenciales para acceder
            </p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-sena-green-600 dark:text-cyan-400 bg-sena-green-50 dark:bg-cyan-900/20 border border-sena-green-200 dark:border-cyan-700 rounded-lg p-3">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="UserName" value="Usuario" class="text-gray-700 dark:text-gray-300 font-semibold" />

                <TextInput
                    id="UserName"
                    type="text"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-sena-green-500 dark:focus:border-cyan-500 focus:ring-sena-green-500 dark:focus:ring-cyan-500 rounded-lg shadow-sm dark:bg-gray-700 dark:text-white transition-all duration-200"
                    v-model="form.UserName"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Ingresa tu usuario"
                />

                <InputError class="mt-2" :message="form.errors.UserName" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" class="text-gray-700 dark:text-gray-300 font-semibold" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-sena-green-500 dark:focus:border-cyan-500 focus:ring-sena-green-500 dark:focus:ring-cyan-500 rounded-lg shadow-sm dark:bg-gray-700 dark:text-white transition-all duration-200"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Ingresa tu contraseña"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox 
                        name="remember" 
                        v-model:checked="form.remember"
                        class="text-sena-green-600 dark:text-cyan-500 focus:ring-sena-green-500 dark:focus:ring-cyan-500"
                    />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200 transition-colors">
                        Recordarme
                    </span>
                </label>
            </div>

            <div class="flex items-center justify-end pt-2">
                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center px-6 py-3 bg-sena-green-600 dark:bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-sena-green-700 dark:hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:ring-offset-2 disabled:opacity-50 transition-all duration-300 active:scale-95"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-if="!form.processing">Entrar</span>
                    <span v-else>Ingresando...</span>
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
