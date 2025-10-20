<template>
  <Head title="Registrate aquí"/>

  <div class="min-h-screen bg-theme-primary text-theme-primary overflow-y-auto">
    <!-- Toggle de tema fijo -->
    <div class="fixed top-4 right-4 z-50">
      <button @click="toggleTheme" class="p-2 rounded-lg bg-theme-card border border-theme-primary hover:bg-theme-secondary transition-all duration-200 shadow-theme-sm">
        <Icon :name="isDark ? 'sun' : 'moon'" :size="20" class="text-theme-secondary" />
      </button>
    </div>

    <!-- 🎉 Mensaje de Éxito Flotante -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 translate-y-4"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-4"
    >
      <div v-if="showSuccessModal" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-[9999] max-w-md w-full mx-4">
        <div class="bg-sena-green-600 dark:bg-cyan-600 text-white rounded-xl border-2 border-white/20 overflow-hidden p-4">
          <div class="flex items-start gap-3">
            <!-- Icono animado -->
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center animate-bounce">
                <Icon name="check-circle" :size="24" class="text-white" />
              </div>
            </div>

            <!-- Contenido -->
            <div class="flex-1">
              <h4 class="font-bold text-base mb-1">¡Registro Exitoso!</h4>
              <p class="text-sm text-white/90">{{ $page.props.flash.success }}</p>
            </div>

            <!-- Botón cerrar -->
            <button @click="closeSuccessModal" class="flex-shrink-0 text-white/80 hover:text-white transition-colors">
              <Icon name="x" :size="20" />
            </button>
          </div>

          <!-- Barra de progreso -->
          <div class="h-1 bg-white/20 overflow-hidden mt-3">
            <div class="h-full bg-white/60 animate-progress-bar"></div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Container principal centrado -->
    <div class="min-h-screen flex flex-col items-center justify-start py-4 sm:py-5 px-3 sm:px-4 lg:px-6">

      <!-- Header con Logo -->
      <div class="w-full max-w-md sm:max-w-2xl lg:max-w-4xl mb-3 sm:mb-4">
        <div class="text-center">
          <div class="mx-auto h-12 w-12 sm:h-14 sm:w-14 mb-2 flex items-center justify-center">
            <ApplicationLogo alt="CTAccess Logo" classes="h-12 w-auto object-contain sm:h-14" />
          </div>
          <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-theme-primary mb-1">Nueva Persona</h1>
          <p class="text-theme-secondary text-xs sm:text-sm">{{ getStepDescription() }}</p>
        </div>
      </div>

      <!-- Indicador de progreso horizontal superior -->
      <div class="w-full max-w-md sm:max-w-2xl lg:max-w-4xl mb-3 sm:mb-4">
        <div class="bg-theme-card rounded-xl p-3 sm:p-4 border border-theme-primary">
          <div class="flex items-center justify-between mb-2 sm:mb-3">
            <div v-for="(step, index) in totalSteps" :key="step" class="flex items-center flex-1">
              <!-- Círculo del paso -->
              <div class="flex flex-col items-center flex-1">
                <div class="relative">
                  <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-all duration-300" :class="step <= currentStep ? 'bg-sena-green-600 dark:bg-cyan-600 text-white shadow-md' : 'bg-theme-secondary text-theme-muted border-2 border-theme-primary'">
                    <Icon v-if="step < currentStep" name="check" :size="16" class="sm:hidden" />
                    <Icon v-if="step < currentStep" name="check" :size="18" class="hidden sm:block" />
                    <span v-else>{{ step }}</span>
                  </div>
                  <!-- Anillo de progreso para el paso actual -->
                  <div v-if="step === currentStep" class="absolute -inset-0.5 bg-sena-green-600 dark:bg-cyan-600 rounded-full opacity-20 animate-pulse"></div>
                </div>
                <!-- Título del paso -->
                <div class="mt-1.5 text-center hidden sm:block">
                  <div class="text-xs font-medium text-theme-primary truncate max-w-[80px] lg:max-w-[120px]" :class="step === currentStep ? 'text-sena-green-700 dark:text-cyan-400 font-bold' : ''">
                    {{ getStepTitle(step) }}
                  </div>
                </div>
              </div>

              <!-- Línea conectora -->
              <div v-if="index < totalSteps - 1" class="flex-1 h-0.5 mx-1.5 sm:mx-2 rounded-full transition-all duration-300 relative -top-3 sm:-top-4" :class="step < currentStep ? 'bg-sena-green-500 dark:bg-cyan-500' : 'bg-theme-secondary'">
                <div v-if="step === currentStep" class="h-full bg-sena-green-500 dark:bg-cyan-500 rounded-full animate-pulse"></div>
              </div>
            </div>
          </div>
          <!-- Contador de pasos para móviles -->
          <div class="text-center text-xs text-theme-muted sm:hidden">Paso {{ currentStep }} de {{ totalSteps }}</div>
        </div>
      </div>

      <!-- Formulario -->
      <div class="w-full max-w-md sm:max-w-2xl lg:max-w-4xl">

      <!-- Formulario paso a paso -->
      <div class="bg-theme-card rounded-xl p-4 sm:p-5 lg:p-6 border border-theme-primary">

        <!-- Paso 1: Información Personal -->
        <div v-if="currentStep === 1" class="space-y-3 sm:space-y-4">

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
            <div class="lg:col-span-2">
              <label for="nombre" class="block text-sm font-medium text-theme-primary mb-1.5">Nombre Completo *</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                  <Icon name="user" :size="18" class="text-theme-muted group-focus-within:text-sena-green-500 dark:group-focus-within:text-cyan-400" />
                </div>
                <input id="nombre" v-model="form.nombre" type="text" required class="block w-full pl-10 pr-3 py-2.5 text-sm border border-theme-primary rounded-lg bg-theme-secondary text-theme-primary placeholder-theme-muted focus:outline-none focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all duration-200 hover:border-sena-green-300 dark:hover:border-cyan-600" placeholder="Ej: Juan Pérez García" />
              </div>
              <div v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</div>
            </div>

            <div>
              <label for="documento" class="block text-sm font-medium text-theme-primary mb-1.5">Documento de Identidad</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                  <Icon name="credit-card" :size="18" class="text-theme-muted group-focus-within:text-sena-green-500 dark:group-focus-within:text-cyan-400" />
                </div>
                <input id="documento" v-model="form.documento" type="text" class="block w-full pl-10 pr-3 py-2.5 text-sm border border-theme-primary rounded-lg bg-theme-secondary text-theme-primary placeholder-theme-muted focus:outline-none focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all duration-200 hover:border-sena-green-300 dark:hover:border-cyan-600" placeholder="Ej: 12345678" />
              </div>
            </div>

            <div>
              <label for="tipoPersona" class="block text-sm font-medium text-theme-primary mb-1.5">Tipo de Persona *</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                  <Icon name="users" :size="18" class="text-theme-muted group-focus-within:text-sena-green-500 dark:group-focus-within:text-cyan-400" />
                </div>
                <select id="tipoPersona" v-model="form.tipoPersona" required class="block w-full pl-10 pr-8 py-2.5 text-sm border border-theme-primary rounded-lg bg-theme-secondary text-theme-primary focus:outline-none focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all duration-200 hover:border-sena-green-300 dark:hover:border-cyan-600 appearance-none cursor-pointer">
                  <option value="">Seleccionar tipo</option>
                  <option value="Aprendiz">Aprendiz</option>
                  <option value="Instructor">Instructor</option>
                  <option value="Empleado">Empleado</option>
                  <option value="Contratista">Contratista</option>
                  <option value="Visitante">Visitante</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <Icon name="chevron-down" :size="14" class="text-theme-muted" />
                </div>
              </div>
            </div>

            <div>
              <label for="jornada" class="block text-sm font-medium text-theme-primary mb-1.5">Jornada</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                  <Icon name="clock" :size="18" class="text-theme-muted group-focus-within:text-sena-green-500 dark:group-focus-within:text-cyan-400" />
                </div>
                <select id="jornada" v-model="form.jornada_id" class="block w-full pl-10 pr-8 py-2.5 text-sm border border-theme-primary rounded-lg bg-theme-secondary text-theme-primary focus:outline-none focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all duration-200 hover:border-sena-green-300 dark:hover:border-cyan-600 appearance-none cursor-pointer">
                  <option :value="null">Sin jornada asignada</option>
                  <option v-for="jornada in jornadas" :key="jornada.id" :value="jornada.id">
                    {{ jornada.nombre }} ({{ formatTime(jornada.hora_inicio) }} - {{ formatTime(jornada.hora_fin) }})
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <Icon name="chevron-down" :size="14" class="text-theme-muted" />
                </div>
              </div>
              <p class="mt-1.5 text-xs text-theme-muted flex items-center">
                <Icon name="info" :size="12" class="mr-1" />Opcional: Asigna el horario de trabajo/estudio
              </p>
            </div>

            <div class="lg:col-span-2">
              <label for="programa" class="block text-sm font-medium text-theme-primary mb-1.5">Programa de Formación</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                  <Icon name="graduation-cap" :size="18" class="text-theme-muted group-focus-within:text-sena-green-500 dark:group-focus-within:text-cyan-400" />
                </div>
                <select id="programa" v-model="form.programa_formacion_id" class="block w-full pl-10 pr-8 py-2.5 text-sm border border-theme-primary rounded-lg bg-theme-secondary text-theme-primary focus:outline-none focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all duration-200 hover:border-sena-green-300 dark:hover:border-cyan-600 appearance-none cursor-pointer">
                  <option :value="null">Sin programa asignado</option>
                  <option v-for="programa in programas" :key="programa.id" :value="programa.id">
                    {{ programa.nombre }} - Ficha {{ programa.ficha }} ({{ programa.nivel_formacion }})
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <Icon name="chevron-down" :size="14" class="text-theme-muted" />
                </div>
              </div>
              <p class="mt-1.5 text-xs text-theme-muted flex items-center">
                <Icon name="info" :size="12" class="mr-1" />Solo para Aprendices e Instructores
              </p>
            </div>

            <div class="lg:col-span-2">
              <label for="correo" class="block text-sm font-medium text-theme-primary mb-1.5">Correo Electrónico</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                  <Icon name="mail" :size="18" class="text-theme-muted group-focus-within:text-sena-green-500 dark:group-focus-within:text-cyan-400" />
                </div>
                <input id="correo" v-model="form.correo" type="email" class="block w-full pl-10 pr-3 py-2.5 text-sm border border-theme-primary rounded-lg bg-theme-secondary text-theme-primary placeholder-theme-muted focus:outline-none focus:ring-2 focus:ring-sena-green-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all duration-200 hover:border-sena-green-300 dark:hover:border-cyan-600" placeholder="correo@ejemplo.com" />
              </div>
              <p class="mt-1.5 text-xs text-theme-muted flex items-center">
                <Icon name="info" :size="12" class="mr-1" />Se enviará un QR por correo si se proporciona
              </p>
            </div>
          </div>
        </div>

        <!-- Paso 2: Portátiles -->
        <div v-if="currentStep === 2" class="space-y-3 sm:space-y-4">

          <div v-if="form.portatiles.length === 0" class="text-center py-8 sm:py-10">
            <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-full mb-4 bg-cyan-500/10 dark:bg-cyan-900/20">
              <Icon name="laptop" :size="32" class="text-cyan-600 dark:text-cyan-400 sm:hidden" />
              <Icon name="laptop" :size="40" class="text-cyan-600 dark:text-cyan-400 hidden sm:block" />
            </div>
            <h3 class="text-base sm:text-lg font-semibold text-theme-primary mb-1.5">Sin portátiles registrados</h3>
            <p class="text-theme-muted mb-4 max-w-md mx-auto text-xs sm:text-sm">Agrega los portátiles que esta persona utilizará. Puedes omitir este paso si no aplica.</p>
            <button type="button" @click="addPortatil" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-cyan-600 hover:bg-cyan-700 dark:bg-cyan-600 dark:hover:bg-cyan-500 text-white font-medium transition-all duration-200 text-sm">
              <Icon name="plus" :size="16" class="mr-2" />Agregar Primer Portátil
            </button>
          </div>

          <div v-else class="space-y-3">
            <div v-for="(portatil, index) in form.portatiles" :key="`portatil-${index}`" class="border-2 border-theme-primary rounded-lg p-3 sm:p-4 bg-theme-secondary hover:border-cyan-400 dark:hover:border-cyan-600 transition-all duration-200">
              <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm sm:text-base font-semibold text-theme-primary flex items-center">
                  <span class="inline-flex items-center justify-center w-7 h-7 text-white text-xs font-bold rounded-full mr-2 shadow-sm bg-cyan-600 dark:bg-cyan-600">{{ index + 1 }}</span>
                  Portátil {{ index + 1 }}
                </h4>
                <button type="button" @click="removePortatil(index)" class="text-red-500 hover:text-red-600 dark:hover:text-red-400 transition-all duration-200 p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                  <Icon name="trash" :size="16" />
                </button>
              </div>
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                <div class="lg:col-span-3">
                  <label class="block text-xs sm:text-sm font-medium text-theme-primary mb-1.5">Serial *</label>
                  <input v-model="portatil.serial" type="text" required class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-primary text-theme-primary placeholder-theme-muted focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all duration-200 hover:border-cyan-300 dark:hover:border-cyan-600" placeholder="Ej: ABC123456DEF" />
                </div>
                <div class="lg:col-span-1">
                  <label class="block text-xs sm:text-sm font-medium text-theme-primary mb-1.5">Marca *</label>
                  <input v-model="portatil.marca" type="text" required class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-primary text-theme-primary placeholder-theme-muted focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all duration-200 hover:border-cyan-300 dark:hover:border-cyan-600" placeholder="Dell, HP..." />
                </div>
                <div class="lg:col-span-2">
                  <label class="block text-xs sm:text-sm font-medium text-theme-primary mb-1.5">Modelo *</label>
                  <input v-model="portatil.modelo" type="text" required class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-primary text-theme-primary placeholder-theme-muted focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-500 focus:border-transparent transition-all duration-200 hover:border-cyan-300 dark:hover:border-cyan-600" placeholder="Inspiron 15..." />
                </div>
              </div>
            </div>
            <button type="button" @click="addPortatil" class="w-full py-3 border-2 border-dashed border-theme-secondary rounded-lg text-theme-secondary hover:border-cyan-400 dark:hover:border-cyan-500 hover:text-cyan-500 dark:hover:text-cyan-400 hover:bg-cyan-50/5 dark:hover:bg-cyan-900/10 transition-all duration-200 font-medium text-sm">
              <Icon name="plus" :size="16" class="mr-2" />Agregar Otro Portátil
            </button>
          </div>
        </div>

        <!-- Paso 3: Vehículos -->
        <div v-if="currentStep === 3" class="space-y-3 sm:space-y-4">

          <div v-if="form.vehiculos.length === 0" class="text-center py-8 sm:py-10">
            <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-full mb-4 bg-yellow-500/10 dark:bg-yellow-900/20">
              <Icon name="car" :size="32" class="text-yellow-600 dark:text-yellow-400 sm:hidden" />
              <Icon name="car" :size="40" class="text-yellow-600 dark:text-yellow-400 hidden sm:block" />
            </div>
            <h3 class="text-base sm:text-lg font-semibold text-theme-primary mb-1.5">Sin vehículos registrados</h3>
            <p class="text-theme-muted mb-4 max-w-md mx-auto text-xs sm:text-sm">Registra los vehículos que esta persona utiliza para acceder. Puedes omitir este paso si no aplica.</p>
            <button type="button" @click="addVehiculo" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-500 text-black font-medium transition-all duration-200 text-sm">
              <Icon name="plus" :size="16" class="mr-2" />Agregar Primer Vehículo
            </button>
          </div>

          <div v-else class="space-y-3">
            <div v-for="(vehiculo, index) in form.vehiculos" :key="`vehiculo-${index}`" class="border-2 border-theme-primary rounded-lg p-3 sm:p-4 bg-theme-secondary hover:border-yellow-400 dark:hover:border-yellow-600 transition-all duration-200">
              <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm sm:text-base font-semibold text-theme-primary flex items-center">
                  <span class="inline-flex items-center justify-center w-7 h-7 text-black text-xs font-bold rounded-full mr-2 shadow-sm bg-yellow-500 dark:bg-yellow-600">{{ index + 1 }}</span>
                  Vehículo {{ index + 1 }}
                </h4>
                <button type="button" @click="removeVehiculo(index)" class="text-red-500 hover:text-red-600 dark:hover:text-red-400 transition-all duration-200 p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                  <Icon name="trash" :size="16" />
                </button>
              </div>
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                <div class="lg:col-span-2">
                  <label class="block text-xs sm:text-sm font-medium text-theme-primary mb-1.5">Tipo de Vehículo *</label>
                  <div class="relative">
                    <select v-model="vehiculo.tipo" required class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-primary text-theme-primary focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:focus:ring-yellow-500 focus:border-transparent transition-all duration-200 hover:border-yellow-300 dark:hover:border-yellow-600 appearance-none cursor-pointer">
                      <option value="">Seleccionar tipo</option>
                      <option value="Automóvil">🚗 Automóvil</option>
                      <option value="Motocicleta">🏍️ Motocicleta</option>
                      <option value="Bicicleta">🚲 Bicicleta</option>
                      <option value="Camioneta">🚙 Camioneta</option>
                      <option value="Camión">🚛 Camión</option>
                      <option value="Otro">🚐 Otro</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <Icon name="chevron-down" :size="14" class="text-theme-muted" />
                    </div>
                  </div>
                </div>
                <div class="lg:col-span-2">
                  <label class="block text-xs sm:text-sm font-medium text-theme-primary mb-1.5">Placa *</label>
                  <input v-model="vehiculo.placa" type="text" required class="w-full px-3 py-2 text-sm border border-theme-primary rounded-lg bg-theme-primary text-theme-primary placeholder-theme-muted focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:focus:ring-yellow-500 focus:border-transparent transition-all duration-200 uppercase hover:border-yellow-300 dark:hover:border-yellow-600 font-mono tracking-wider" placeholder="ABC-123" @input="vehiculo.placa = vehiculo.placa.toUpperCase()" />
                </div>
              </div>
            </div>
            <button type="button" @click="addVehiculo" class="w-full py-3 border-2 border-dashed border-theme-secondary rounded-lg text-theme-secondary hover:border-yellow-400 dark:hover:border-yellow-500 hover:text-yellow-500 dark:hover:text-yellow-400 hover:bg-yellow-50/5 dark:hover:bg-yellow-900/10 transition-all duration-200 font-medium text-sm">
              <Icon name="plus" :size="16" class="mr-2" />Agregar Otro Vehículo
            </button>
          </div>
        </div>

        <!-- Paso 4: Resumen -->
        <div v-if="currentStep === 4" class="space-y-3 sm:space-y-4">

          <!-- Header del resumen -->
          <div class="text-center mb-4 sm:mb-5">
            <h3 class="text-lg sm:text-xl font-bold text-theme-primary mb-1">Revisa tu información</h3>
            <p class="text-theme-muted text-xs sm:text-sm">Verifica que todos los datos sean correctos antes de crear el registro</p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
            <!-- Información Personal -->
            <div class="border-2 border-theme-primary rounded-lg p-3 sm:p-4 bg-gradient-to-br from-sena-green-50/5 dark:from-cyan-900/5 to-theme-secondary hover:border-sena-green-400 dark:hover:border-cyan-600 transition-all duration-200 lg:col-span-2">
              <h4 class="text-sm sm:text-base font-semibold text-theme-primary mb-3 flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2 bg-sena-green-600 dark:bg-cyan-600">
                  <Icon name="user" :size="16" class="text-white" />
                </div>
                Información Personal
              </h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                <div class="bg-theme-primary rounded-lg p-2.5 sm:p-3">
                  <div class="text-[10px] sm:text-xs text-theme-muted mb-0.5">Nombre Completo</div>
                  <div class="text-xs sm:text-sm font-semibold text-theme-primary">{{ form.nombre || 'No especificado' }}</div>
                </div>
                <div class="bg-theme-primary rounded-lg p-2.5 sm:p-3">
                  <div class="text-[10px] sm:text-xs text-theme-muted mb-0.5">Documento</div>
                  <div class="text-xs sm:text-sm font-semibold text-theme-primary">{{ form.documento || 'No especificado' }}</div>
                </div>
                <div class="bg-theme-primary rounded-lg p-2.5 sm:p-3">
                  <div class="text-[10px] sm:text-xs text-theme-muted mb-0.5">Tipo de Persona</div>
                  <div class="text-xs sm:text-sm font-semibold text-theme-primary">{{ form.tipoPersona || 'No especificado' }}</div>
                </div>
                <div class="bg-theme-primary rounded-lg p-2.5 sm:p-3">
                  <div class="text-[10px] sm:text-xs text-theme-muted mb-0.5">Jornada</div>
                  <div class="text-xs sm:text-sm font-semibold text-theme-primary">{{ getJornadaName() }}</div>
                </div>
                <div class="bg-theme-primary rounded-lg p-2.5 sm:p-3 sm:col-span-2">
                  <div class="text-[10px] sm:text-xs text-theme-muted mb-0.5">Programa de Formación</div>
                  <div class="text-xs sm:text-sm font-semibold text-theme-primary">{{ getProgramaName() }}</div>
                </div>
                <div class="bg-theme-primary rounded-lg p-2.5 sm:p-3 sm:col-span-2">
                  <div class="text-[10px] sm:text-xs text-theme-muted mb-0.5">Correo Electrónico</div>
                  <div class="text-xs sm:text-sm font-semibold text-theme-primary truncate">{{ form.correo || 'No especificado' }}</div>
                </div>
              </div>
            </div>

            <!-- Portátiles -->
            <div v-if="form.portatiles.length > 0" class="border-2 border-theme-primary rounded-lg p-3 sm:p-4 bg-gradient-to-br from-cyan-50/5 dark:from-cyan-900/5 to-theme-secondary hover:border-cyan-400 dark:hover:border-cyan-600 transition-all duration-200" :class="form.vehiculos.length === 0 ? 'lg:col-span-2' : ''">
              <h4 class="text-sm sm:text-base font-semibold text-theme-primary mb-3 flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2 bg-cyan-600 dark:bg-cyan-600">
                  <Icon name="laptop" :size="16" class="text-white" />
                </div>
                Portátiles <span class="ml-1.5 text-xs font-normal text-theme-muted">({{ form.portatiles.length }})</span>
              </h4>
              <div class="space-y-2">
                <div v-for="(portatil, index) in form.portatiles" :key="index" class="bg-theme-primary rounded-lg p-2.5 sm:p-3">
                  <div class="flex items-start justify-between mb-1.5">
                    <div class="text-[10px] sm:text-xs text-theme-muted">Portátil {{ index + 1 }}</div>
                    <div class="inline-flex items-center justify-center w-5 h-5 text-white text-[10px] font-bold rounded-full bg-cyan-600">{{ index + 1 }}</div>
                  </div>
                  <div class="text-xs sm:text-sm font-semibold text-theme-primary mb-0.5">{{ portatil.marca }} {{ portatil.modelo }}</div>
                  <div class="text-[10px] sm:text-xs text-theme-muted">Serial: <span class="font-mono text-theme-primary">{{ portatil.serial }}</span></div>
                </div>
              </div>
            </div>

            <!-- Vehículos -->
            <div v-if="form.vehiculos.length > 0" class="border-2 border-theme-primary rounded-lg p-3 sm:p-4 bg-gradient-to-br from-yellow-50/5 dark:from-yellow-900/5 to-theme-secondary hover:border-yellow-400 dark:hover:border-yellow-600 transition-all duration-200" :class="form.portatiles.length === 0 ? 'lg:col-span-2' : ''">
              <h4 class="text-sm sm:text-base font-semibold text-theme-primary mb-3 flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2 bg-yellow-500 dark:bg-yellow-600">
                  <Icon name="car" :size="16" class="text-black" />
                </div>
                Vehículos <span class="ml-1.5 text-xs font-normal text-theme-muted">({{ form.vehiculos.length }})</span>
              </h4>
              <div class="space-y-2">
                <div v-for="(vehiculo, index) in form.vehiculos" :key="index" class="bg-theme-primary rounded-lg p-2.5 sm:p-3">
                  <div class="flex items-start justify-between mb-1.5">
                    <div class="text-[10px] sm:text-xs text-theme-muted">Vehículo {{ index + 1 }}</div>
                    <div class="inline-flex items-center justify-center w-5 h-5 text-black text-[10px] font-bold rounded-full bg-yellow-500">{{ index + 1 }}</div>
                  </div>
                  <div class="text-xs sm:text-sm font-semibold text-theme-primary mb-0.5">{{ vehiculo.tipo }}</div>
                  <div class="text-[10px] sm:text-xs text-theme-muted">Placa: <span class="font-mono text-theme-primary font-bold text-sm">{{ vehiculo.placa }}</span></div>
                </div>
              </div>
            </div>

            <!-- Mensaje si no hay portátiles ni vehículos -->
            <div v-if="form.portatiles.length === 0 && form.vehiculos.length === 0" class="lg:col-span-2 text-center py-4 sm:py-5 border-2 border-dashed border-theme-secondary rounded-lg">
              <Icon name="info" :size="24" class="mx-auto text-theme-muted mb-1.5 sm:hidden" />
              <Icon name="info" :size="28" class="mx-auto text-theme-muted mb-2 hidden sm:block" />
              <p class="text-theme-muted text-xs sm:text-sm">No se registraron portátiles ni vehículos para esta persona</p>
            </div>
          </div>
        </div>

        <!-- Botones de navegación -->
        <div class="flex flex-col sm:flex-row justify-between gap-2 sm:gap-3 pt-5 mt-4 border-t border-theme-primary">
          <button v-if="currentStep > 1" type="button" @click="previousStep" class="inline-flex items-center justify-center px-4 py-2.5 border-2 border-theme-primary rounded-lg text-theme-secondary hover:bg-theme-secondary hover:border-sena-green-400 dark:hover:border-cyan-400 transition-all duration-200 order-2 sm:order-1 font-medium text-sm">
            <Icon name="arrow-left" :size="16" class="mr-1.5" />Anterior
          </button>
          <div v-else class="hidden sm:block"></div>

          <div class="flex flex-col sm:flex-row gap-2 sm:gap-2.5 order-1 sm:order-2">
            <Link :href="route('home')" class="inline-flex items-center justify-center px-4 py-2.5 border-2 border-red-400 dark:border-red-500 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50/10 dark:hover:bg-red-900/20 hover:border-red-500 dark:hover:border-red-400 transition-all duration-200 font-medium text-sm">
              <Icon name="x" :size="16" class="mr-1.5" />Cancelar
            </Link>

            <button v-if="currentStep < totalSteps" type="button" @click="nextStep" :disabled="!canProceedToNextStep()" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-sena-green-600 hover:bg-sena-green-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-semibold text-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
              Siguiente<Icon name="arrow-right" :size="16" class="ml-1.5" />
            </button>

            <button v-else type="submit" @click="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-sena-green-600 hover:bg-sena-green-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-semibold text-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
              <Icon v-if="form.processing" name="loader" :size="16" class="animate-spin mr-1.5" />
              <span v-if="form.processing">Creando...</span><span v-else><Icon name="check" :size="16" class="mr-1.5" />Crear Persona</span>
            </button>
          </div>
        </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import Icon from '@/Components/Icon.vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { useTheme } from '@/composables/useTheme'
import { ref, computed, watch, onMounted } from 'vue'

defineOptions({ layout: null })

// Recibir las jornadas como prop
const props = defineProps({
  jornadas: {
    type: Array,
    default: () => []
  },
  programas: {
    type: Array,
    default: () => []
  }
})

const { isDark, toggleTheme } = useTheme()
const currentStep = ref(1)
const totalSteps = 4

// Estado para controlar la visibilidad del modal
const showSuccessModal = ref(false)

// Obtener las props de la página
const page = usePage()

// Función para cerrar el modal manualmente
const closeSuccessModal = () => {
  showSuccessModal.value = false
}

// Observar cambios en los mensajes flash
watch(() => page.props.flash?.success, (successMessage) => {
  if (successMessage) {
    showSuccessModal.value = true

    // Resetear el formulario después de mostrar el mensaje
    setTimeout(() => {
      resetForm()
    }, 500)

    // Ocultar automáticamente después de 5 segundos
    setTimeout(() => {
      showSuccessModal.value = false
    }, 5000)
  }
}, { immediate: true })

const form = useForm({
  documento: '', nombre: '', tipoPersona: '', jornada_id: null, programa_formacion_id: null, correo: '', portatiles: [], vehiculos: []
})

const resetForm = () => {
  form.reset()
  form.clearErrors()
  currentStep.value = 1
}

const getStepDescription = () => {
  const descriptions = {
    1: 'Completa la información personal básica',
    2: 'Agrega portátiles asociados (opcional)',
    3: 'Registra vehículos (opcional)',
    4: 'Revisa y confirma la información'
  }
  return descriptions[currentStep.value]
}

const getStepTitle = (step) => {
  const titles = {
    1: 'Información Personal',
    2: 'Portátiles',
    3: 'Vehículos', 
    4: 'Resumen'
  }
  return titles[step]
}

const getStepSubtitle = (step) => {
  const subtitles = {
    1: 'Datos básicos',
    2: 'Equipos (opcional)',
    3: 'Transporte (opcional)',
    4: 'Confirmar datos'
  }
  return subtitles[step]
}

const nextStep = () => {
  if (currentStep.value < totalSteps && canProceedToNextStep()) {
    currentStep.value++
  }
}

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const canProceedToNextStep = () => {
  switch (currentStep.value) {
    case 1: return form.nombre.trim() && form.tipoPersona
    case 2:
    case 3: return true
    default: return true
  }
}

const addPortatil = () => {
  form.portatiles.push({ serial: '', marca: '', modelo: '' })
}

const removePortatil = (index) => {
  form.portatiles.splice(index, 1)
}

const addVehiculo = () => {
  form.vehiculos.push({ tipo: '', placa: '' })
}

const removeVehiculo = (index) => {
  form.vehiculos.splice(index, 1)
}

const submit = () => {
  if (!form.nombre.trim()) {
    alert('El nombre es obligatorio');
    return;
  }

  if (!form.tipoPersona) {
    alert('Debe seleccionar un tipo de persona');
    return;
  }

  console.log('📤 Enviando formulario...')

  form.post(route('personas.store'), {
    onSuccess: async (page) => {
      console.log('✅ Persona creada exitosamente')
      console.log('📄 Página recibida:', page)

      // Mostrar mensaje de éxito manualmente usando reactive
      modalState.message = '¡Registro exitoso! Persona creada correctamente.'

      // Usar nextTick para asegurar que Vue actualice el DOM
      await nextTick()
      modalState.show = true

      console.log('🎉 Modal activado - modalState.show:', modalState.show)
      console.log('�� Mensaje:', modalState.message)

      // Forzar actualización del DOM
      await nextTick()

      // Verificar si el elemento existe en el DOM - buscar por clase específica del modal
      const modalElement = document.querySelector('.fixed.top-4.left-1\\/2')
      console.log('🔍 Elemento del modal encontrado:', modalElement)
      if (modalElement) {
        console.log('✅ Modal está en el DOM con display:', window.getComputedStyle(modalElement).display)
        console.log('✅ Clases del modal:', modalElement.className)
      } else {
        console.error('❌ Modal NO se encontró en el DOM')
        // Buscar TODOS los elementos fixed
        const allFixed = document.querySelectorAll('.fixed')
        console.log('📋 Todos los elementos fixed encontrados:', allFixed.length)
        allFixed.forEach((el, i) => {
          console.log(`  ${i + 1}. ${el.className}`)
        })
      }

      // Resetear el formulario después de un pequeño delay para que el usuario vea el mensaje
      setTimeout(() => {
        resetForm()
      }, 500)

      // Ocultar el mensaje después de 5 segundos
      setTimeout(() => {
        modalState.show = false
      }, 5000)
    },
    onError: (errors) => {
      console.error('❌ Errores de validación:', errors);
      if (errors.message && (errors.message.includes('CSRF') || errors.message.includes('expired'))) {
        window.location.reload();
        return;
      }
      const firstError = Object.values(errors)[0];
      if (firstError && typeof firstError === 'string') {
        alert(`Error: ${firstError}`);
      }
    },
    onFinish: () => {
      console.log('🏁 Petición finalizada')
    },
    preserveScroll: true,
    preserveState: false, // Cambiado a false para que reciba el flash message
  })
}

// Función para formatear hora de 24h a 12h AM/PM
const formatTime = (time) => {
  if (!time) return '';
  const [hours, minutes] = time.split(':');
  const hour = parseInt(hours);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const hour12 = hour % 12 || 12;
  return `${hour12}:${minutes} ${ampm}`;
}

// Función para obtener el nombre de la jornada seleccionada
const getJornadaName = () => {
  if (!form.jornada_id) return 'Sin asignar';
  const jornada = props.jornadas.find(j => j.id === form.jornada_id);
  return jornada ? `${jornada.nombre} (${formatTime(jornada.hora_inicio)} - ${formatTime(jornada.hora_fin)})` : 'Sin asignar';
}

// Función para obtener el nombre del programa de formación
const getProgramaName = () => {
  if (!form.programa_formacion_id) return 'Sin asignar';
  const programa = props.programas.find(p => p.id === form.programa_formacion_id);
  return programa ? `${programa.nombre} - Ficha ${programa.ficha}` : 'Sin asignar';
}
</script>

<style scoped>
@keyframes progress-bar {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

.animate-progress-bar {
  animation: progress-bar 5s linear forwards;
}
</style>
