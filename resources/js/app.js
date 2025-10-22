import '../css/app.css';
import './bootstrap';
import './echo'; // 🔥 Importar configuración de Laravel Echo para WebSockets

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import GlobalLoader from './Components/GlobalLoader.vue'; // 🔥 Loader Global
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// 🔥 Interceptor de Inertia para errores 419
router.on('error', (event) => {
    if (event.detail.response && event.detail.response.status === 419) {
        event.preventDefault();
        window.location.reload();
    }
});

// 🔥 Interceptor global para manejar errores 419 (CSRF token expired)
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 419) {
            // Recargar silenciosamente sin mostrar mensaje de error
            window.location.reload();
            // Retornar una promesa que nunca se resuelve para evitar que el error se propague
            return new Promise(() => {});
        }
        return Promise.reject(error);
    }
);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // 🔥 Registrar GlobalLoader como componente global
        app.component('GlobalLoader', GlobalLoader);
        
        // 🔥 Manejar errores de Inertia globalmente
        app.config.errorHandler = (err, instance, info) => {
            if (err.response && err.response.status === 419) {
                window.location.reload();
                return;
            }
            console.error('Error en la aplicación:', err, info);
        };

        return app.mount(el);
    },
    progress: {
        // Deshabilitamos la barra de progreso nativa de Inertia
        // porque ya tenemos nuestro loader personalizado
        delay: 0,
        color: '#00304D',
        includeCSS: true,
        showSpinner: false,
    },
});
