<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Icon from '@/Components/Icon.vue';
import { Link } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, onMounted, onUnmounted } from 'vue';

const { isDark, toggleTheme } = useTheme();

// Partículas animadas
const particles = ref([]);
const particleCount = 50;

const createParticles = () => {
  particles.value = Array.from({ length: particleCount }, (_, i) => ({
    id: i,
    x: Math.random() * 100,
    y: Math.random() * 100,
    size: Math.random() * 4 + 2,
    duration: Math.random() * 20 + 15,
    delay: Math.random() * 5,
    opacity: Math.random() * 0.5 + 0.3,
  }));
};

onMounted(() => {
  createParticles();
});
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0 overflow-hidden bg-gradient-to-br from-sena-green-50 via-white to-cyan-50 dark:from-gray-900 dark:via-sena-blue-950 dark:to-gray-900 transition-colors duration-500">
        <!-- Partículas animadas de fondo -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
          <div
            v-for="particle in particles"
            :key="particle.id"
            class="absolute rounded-full animate-float bg-gradient-to-br from-sena-green-400 to-cyan-400 dark:from-cyan-500 dark:to-blue-500 opacity-20 dark:opacity-10"
            :style="{
              left: particle.x + '%',
              top: particle.y + '%',
              width: particle.size + 'px',
              height: particle.size + 'px',
              animationDuration: particle.duration + 's',
              animationDelay: particle.delay + 's',
            }"
          ></div>
        </div>

        <!-- Efectos de luz de fondo -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-sena-green-400 dark:bg-cyan-500 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-3xl opacity-20 dark:opacity-10 animate-blob"></div>
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-cyan-400 dark:bg-blue-500 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-3xl opacity-20 dark:opacity-10 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-0 left-1/3 w-96 h-96 bg-yellow-300 dark:bg-cyan-600 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-3xl opacity-20 dark:opacity-10 animate-blob animation-delay-4000"></div>

        <!-- Botón de tema -->
        <button
          @click="toggleTheme"
          class="absolute top-6 right-6 z-20 group rounded-xl p-3 bg-white dark:bg-gray-800 border border-sena-green-200 dark:border-cyan-700 transition-all duration-300"
          :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
        >
          <Icon :name="isDark ? 'sun' : 'moon'" :size="24" class="text-sena-green-600 dark:text-cyan-400 group-hover:rotate-180 transition-transform duration-500" />
        </button>

        <!-- Logo -->
        <div class="relative z-10 transform transition-all duration-500">
            <Link href="/">
                <div class="relative">
                  <ApplicationLogo classes="relative h-24 w-auto object-contain" />
                </div>
            </Link>
        </div>

        <!-- Formulario -->
        <div class="relative z-10 mt-8 w-full overflow-hidden bg-white dark:bg-gray-800 px-8 py-8 sm:max-w-md sm:rounded-2xl border border-sena-green-100 dark:border-cyan-900 transition-all duration-500">
            <!-- Borde animado superior -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-sena-green-500 dark:bg-cyan-500"></div>
            
            <slot />
        </div>
        
        <!-- Texto de marca -->
        <div class="relative z-10 mt-6 text-center">
          <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">
            Sistema de Control de Acceso
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
            CTAccess v2.0 • SENA
          </p>
        </div>
        
        <!-- 🔥 Loader Global -->
        <GlobalLoader />
    </div>
</template>

<style scoped>
@keyframes float {
  0%, 100% {
    transform: translateY(0) translateX(0);
  }
  25% {
    transform: translateY(-20px) translateX(10px);
  }
  50% {
    transform: translateY(-10px) translateX(-10px);
  }
  75% {
    transform: translateY(-30px) translateX(5px);
  }
}

@keyframes blob {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  25% {
    transform: translate(20px, -50px) scale(1.1);
  }
  50% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  75% {
    transform: translate(50px, 50px) scale(1.05);
  }
}

@keyframes gradient-x {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

.animate-float {
  animation: float linear infinite;
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

.animate-gradient-x {
  background-size: 200% 200%;
  animation: gradient-x 3s ease infinite;
}
</style>
