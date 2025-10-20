<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Icon from '@/Components/Icon.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    correo: '',
});

const { isDark, toggleTheme } = useTheme();

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <div class="min-h-screen bg-theme-primary text-theme-primary flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <Head title="Recuperar Contraseña - CTAccess" />
        
        <div class="max-w-md w-full space-y-8">

            <!-- Logo y título -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 rounded-xl flex items-center justify-center mb-4 shadow-theme-lg" 
                     style="background: linear-gradient(135deg, #FDC300, #ff8c00);">
                    <Icon name="key" :size="32" class="text-white" />
                </div>
                <h2 class="text-3xl font-bold text-theme-primary mb-2">Recuperar Contraseña</h2>
                <p class="text-theme-secondary text-sm">CTAccess - Sistema de Control de Accesos</p>
            </div>

            <!-- Formulario -->
            <div class="bg-theme-card rounded-2xl p-8 border border-theme-primary">
                <div class="mb-6 p-4 rounded-lg text-sm border border-theme-secondary" 
                     style="background-color: rgba(80, 229, 249, 0.1); color: #50E5F9;">
                    <div class="flex items-start">
                        <Icon name="info" :size="20" class="mt-0.5 mr-3 flex-shrink-0" style="color: #50E5F9;" />
                        <div>
                            <p class="font-medium mb-1">¿Olvidaste tu contraseña?</p>
                            <p class="text-xs text-theme-muted">
                                No te preocupes. Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="status"
                    class="mb-6 p-4 rounded-lg text-sm"
                    style="background-color: rgba(57, 169, 0, 0.1); border: 1px solid rgba(57, 169, 0, 0.3); color: #39A900;"
                >
                    <div class="flex items-center">
                        <Icon name="check-circle" :size="20" class="mr-3" style="color: #39A900;" />
                        {{ status }}
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="correo" class="block text-sm font-medium text-theme-primary mb-2">
                            Correo Electrónico
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Icon name="mail" :size="20" class="text-theme-muted" />
                            </div>
                            <input
                                id="correo"
                                type="email"
                                v-model="form.correo"
                                required
                                autofocus
                                autocomplete="username"
                                class="block w-full pl-10 pr-3 py-3 border border-theme-primary rounded-lg bg-theme-secondary text-theme-primary placeholder-theme-muted focus:outline-none focus:ring-2 focus:border-transparent transition-all duration-200 focus:ring-yellow-500"
                                placeholder="tu@email.com"
                            />
                        </div>
                        <InputError class="mt-2 text-red-500" :message="form.errors.correo" />
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-theme-md hover:shadow-theme-lg focus:ring-yellow-500"
                            style="background: linear-gradient(135deg, #FDC300, #ff8c00);"
                        >
                            <span v-if="form.processing" class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <Icon name="loader" :size="20" class="text-white animate-spin" />
                            </span>
                            {{ form.processing ? 'Enviando enlace...' : 'Enviar Enlace de Recuperación' }}
                        </button>
                    </div>
                </form>

                <!-- Enlaces adicionales -->
                <div class="mt-6 text-center space-y-3">
                    <Link
                        :href="route('login')"
                        class="inline-flex items-center text-sm text-theme-secondary hover:text-theme-primary transition-colors duration-200"
                        style="color: #50E5F9;"
                    >
                        <Icon name="arrow-left" :size="16" class="mr-2" />
                        Volver al inicio de sesión
                    </Link>
                    <div>
                        <Link
                            :href="route('home')"
                            class="text-sm text-theme-secondary hover:text-theme-primary transition-colors duration-200"
                            style="color: #50E5F9;"
                        >
                            ← Volver al inicio
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-xs text-theme-muted">
                    © 2024 CTAccess. Sistema de Control de Accesos
                </p>
            </div>
        </div>
    </div>
</template>
