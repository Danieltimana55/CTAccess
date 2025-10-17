import '../css/app.css';
import './bootstrap';
import './echo'; // 🔥 Importar configuración de Laravel Echo para WebSockets

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import GlobalLoader from './Components/GlobalLoader.vue'; // 🔥 Loader Global
import axios from 'axios';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// 🔥 Interceptor global para manejar errores 419 (CSRF token expired)
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 419) {
            console.warn('Token CSRF expirado. Recargando página...');
            // Recargar la página para obtener un nuevo token CSRF
            window.location.reload();
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
