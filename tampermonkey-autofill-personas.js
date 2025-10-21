// ==UserScript==
// @name         CTAccess - Autofill Formulario Personas (Vue/Inertia)
// @namespace    http://tampermonkey.net/
// @version      2.0
// @description  Auto-rellena el formulario de registro de personas con ejemplos (Compatible con Vue 3 + Inertia)
// @author       CTAccess
// @match        http://127.0.0.1:8000/personas/registrarse
// @match        http://127.0.0.1:8000/personas/registrarse*
// @match        http://localhost:8000/personas/registrarse
// @match        http://localhost:8000/personas/registrarse*
// @icon         data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==
// @grant        none
// @run-at       document-idle
// ==/UserScript==

(function() {
    'use strict';

    console.log('🤖 [CTAccess Autofill] Script cargado');

    // ========================================
    // DATOS DE EJEMPLO
    // ========================================
    const ejemplos = [
        {
            nombre: "Juan Carlos Rodríguez García",
            documento: "1045678901",
            tipoPersona: "Aprendiz",
            correo: "juan.rodriguez@soy.sena.edu.co",
            portatiles: [
                { serial: "DLXYZ123456", marca: "Dell", modelo: "Inspiron 15 3000" }
            ],
            vehiculos: [
                { tipo: "Motocicleta", placa: "ABC-123" }
            ]
        },
        {
            nombre: "María Elena Gómez Pérez",
            documento: "1034567890",
            tipoPersona: "Instructor",
            correo: "maria.gomez@sena.edu.co",
            portatiles: [
                { serial: "HPXYZ789012", marca: "HP", modelo: "Pavilion 14" },
                { serial: "LNVO456789", marca: "Lenovo", modelo: "ThinkPad X1 Carbon" }
            ],
            vehiculos: []
        },
        {
            nombre: "Carlos Andrés Martínez López",
            documento: "1098765432",
            tipoPersona: "Contratista",
            correo: "carlos.martinez@example.com",
            portatiles: [],
            vehiculos: [
                { tipo: "Automóvil", placa: "XYZ-789" }
            ]
        }
    ];

    // ========================================
    // FUNCIONES PARA INTERACTUAR CON VUE
    // ========================================

    /**
     * Encuentra la instancia de Vue de un elemento DOM
     */
    function getVueInstance(element) {
        if (!element) return null;

        // Vue 3 usa __vueParentComponent
        if (element.__vueParentComponent) {
            return element.__vueParentComponent.ctx;
        }

        // Buscar en el padre
        if (element.parentElement) {
            return getVueInstance(element.parentElement);
        }

        return null;
    }

    /**
     * Dispara eventos nativos y de Vue para que el componente detecte el cambio
     */
    function triggerVueInput(element, value) {
        if (!element) return false;

        // Asignar el valor
        element.value = value;

        // Disparar eventos nativos
        const events = ['input', 'change', 'blur'];
        events.forEach(eventType => {
            const event = new Event(eventType, { bubbles: true, cancelable: true });
            element.dispatchEvent(event);
        });

        // Disparar evento personalizado para Vue
        const customEvent = new CustomEvent('update:modelValue', {
            detail: value,
            bubbles: true
        });
        element.dispatchEvent(customEvent);

        console.log(`✅ Campo actualizado: ${element.id || element.name} = "${value}"`);
        return true;
    }

    /**
     * Espera hasta que un elemento aparezca en el DOM
     */
    function waitForElement(selector, timeout = 5000) {
        return new Promise((resolve, reject) => {
            const element = document.querySelector(selector);
            if (element) {
                resolve(element);
                return;
            }

            const observer = new MutationObserver(() => {
                const element = document.querySelector(selector);
                if (element) {
                    observer.disconnect();
                    resolve(element);
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            setTimeout(() => {
                observer.disconnect();
                reject(new Error(`Timeout esperando elemento: ${selector}`));
            }, timeout);
        });
    }

    /**
     * Hace clic en un botón y espera a que el DOM se actualice
     */
    async function clickButton(buttonText) {
        const buttons = Array.from(document.querySelectorAll('button'));
        const button = buttons.find(btn =>
            btn.textContent.trim().includes(buttonText)
        );

        if (button) {
            console.log(`🖱️ Haciendo clic en: "${buttonText}"`);
            button.click();
            await sleep(600); // Esperar animación
            return true;
        }

        console.warn(`⚠️ No se encontró el botón: "${buttonText}"`);
        return false;
    }

    /**
     * Función auxiliar para esperar
     */
    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // ========================================
    // LLENADO DEL FORMULARIO
    // ========================================

    /**
     * Llena el Paso 1: Información Personal
     */
    async function llenarPaso1(datos) {
        console.log('📝 Paso 1: Información Personal');

        await sleep(500);

        // Nombre
        const nombreInput = document.querySelector('#nombre');
        if (nombreInput) {
            triggerVueInput(nombreInput, datos.nombre);
            await sleep(300);
        }

        // Documento
        const documentoInput = document.querySelector('#documento');
        if (documentoInput) {
            triggerVueInput(documentoInput, datos.documento);
            await sleep(300);
        }

        // Tipo de Persona
        const tipoSelect = document.querySelector('#tipoPersona');
        if (tipoSelect) {
            triggerVueInput(tipoSelect, datos.tipoPersona);
            await sleep(300);
        }

        // Correo
        const correoInput = document.querySelector('#correo');
        if (correoInput) {
            triggerVueInput(correoInput, datos.correo);
            await sleep(300);
        }

        console.log('✅ Paso 1 completado');
    }

    /**
     * Llena el Paso 2: Portátiles
     */
    async function llenarPaso2(datos) {
        console.log('💻 Paso 2: Portátiles');

        await sleep(500);

        if (datos.portatiles.length === 0) {
            console.log('⏭️ Sin portátiles, continuando...');
            return;
        }

        // Agregar cada portátil
        for (let i = 0; i < datos.portatiles.length; i++) {
            const portatil = datos.portatiles[i];

            // Clic en "Agregar Primer/Otro Portátil"
            const textoBoton = i === 0 ? 'Agregar Primer Portátil' : 'Agregar Otro Portátil';
            await clickButton(textoBoton);
            await sleep(500);

            // Llenar campos del portátil
            const inputs = document.querySelectorAll('input[type="text"]');
            let serialInput = null;
            let marcaInput = null;
            let modeloInput = null;

            // Buscar inputs por placeholder
            for (const input of inputs) {
                const placeholder = input.getAttribute('placeholder') || '';
                if (placeholder.includes('ABC123456DEF') || placeholder.includes('Serial')) {
                    serialInput = input;
                } else if (placeholder.includes('Dell, HP') || placeholder.includes('Marca')) {
                    marcaInput = input;
                } else if (placeholder.includes('Inspiron') || placeholder.includes('Modelo')) {
                    modeloInput = input;
                }
            }

            if (serialInput) {
                triggerVueInput(serialInput, portatil.serial);
                await sleep(300);
            }

            if (marcaInput) {
                triggerVueInput(marcaInput, portatil.marca);
                await sleep(300);
            }

            if (modeloInput) {
                triggerVueInput(modeloInput, portatil.modelo);
                await sleep(300);
            }

            console.log(`✅ Portátil ${i + 1} agregado: ${portatil.marca} ${portatil.modelo}`);
        }

        console.log('✅ Paso 2 completado');
    }

    /**
     * Llena el Paso 3: Vehículos
     */
    async function llenarPaso3(datos) {
        console.log('🚗 Paso 3: Vehículos');

        await sleep(500);

        if (datos.vehiculos.length === 0) {
            console.log('⏭️ Sin vehículos, continuando...');
            return;
        }

        // Agregar cada vehículo
        for (let i = 0; i < datos.vehiculos.length; i++) {
            const vehiculo = datos.vehiculos[i];

            // Clic en "Agregar Primer/Otro Vehículo"
            const textoBoton = i === 0 ? 'Agregar Primer Vehículo' : 'Agregar Otro Vehículo';
            await clickButton(textoBoton);
            await sleep(500);

            // Select de tipo de vehículo
            const selectTipo = document.querySelector('select[required]');
            if (selectTipo) {
                triggerVueInput(selectTipo, vehiculo.tipo);
                await sleep(400);
            }

            // Input de placa
            const placaInput = document.querySelector('input[placeholder="ABC-123"]');
            if (placaInput) {
                triggerVueInput(placaInput, vehiculo.placa);
                await sleep(300);
            }

            console.log(`✅ Vehículo ${i + 1} agregado: ${vehiculo.tipo} - ${vehiculo.placa}`);
        }

        console.log('✅ Paso 3 completado');
    }

    /**
     * Función principal que llena todo el formulario
     */
    async function llenarFormulario(datos) {
        console.log('🚀 ========================================');
        console.log('🚀 Iniciando llenado automático del formulario');
        console.log('🚀 ========================================');
        console.log('📋 Datos:', datos);

        try {
            // Verificar que estamos en el paso 1
            const nombreInput = document.querySelector('#nombre');
            if (!nombreInput) {
                console.error('❌ No se encontró el formulario. ¿Estás en la página correcta?');
                return;
            }

            // PASO 1: Información Personal
            await llenarPaso1(datos);
            await sleep(500);

            // Avanzar al Paso 2
            console.log('➡️ Avanzando al Paso 2...');
            await clickButton('Siguiente');
            await sleep(800);

            // PASO 2: Portátiles
            await llenarPaso2(datos);
            await sleep(500);

            // Avanzar al Paso 3
            console.log('➡️ Avanzando al Paso 3...');
            await clickButton('Siguiente');
            await sleep(800);

            // PASO 3: Vehículos
            await llenarPaso3(datos);
            await sleep(500);

            // Avanzar al Paso 4 (Resumen)
            console.log('➡️ Avanzando al Paso 4 (Resumen)...');
            await clickButton('Siguiente');
            await sleep(800);

            console.log('✅ ========================================');
            console.log('✅ Formulario completado - Revisando resumen...');
            console.log('✅ ========================================');

            // PASO 4: Hacer clic en "Crear Persona" automáticamente
            console.log('🎯 Haciendo clic en "Crear Persona"...');
            await sleep(1000); // Dar tiempo para revisar el resumen

            const creado = await clickButton('Crear Persona');

            if (creado) {
                console.log('⏳ Esperando confirmación...');

                // Esperar a que aparezca el mensaje de éxito (máximo 10 segundos)
                let intentos = 0;
                const maxIntentos = 50; // 50 intentos x 200ms = 10 segundos

                while (intentos < maxIntentos) {
                    // Buscar el mensaje de éxito en el DOM
                    const mensajeExito = document.querySelector('[class*="bg-gradient-to-r"][class*="from-sena-green"]') ||
                                        document.querySelector('[class*="Registro Exitoso"]') ||
                                        Array.from(document.querySelectorAll('h4, h3, div')).find(el =>
                                            el.textContent.includes('Registro Exitoso') ||
                                            el.textContent.includes('¡Registro exitoso!')
                                        );

                    if (mensajeExito) {
                        console.log('🎉 ========================================');
                        console.log('🎉 ¡PERSONA REGISTRADA CORRECTAMENTE!');
                        console.log('🎉 ========================================');
                        console.log('✅ El formulario se limpiará automáticamente');
                        console.log('✅ Puedes registrar otra persona');

                        // Mensaje visual en pantalla
                        mostrarNotificacion('¡Persona registrada exitosamente!', 'success');
                        break;
                    }

                    // Verificar si hay error
                    const mensajeError = Array.from(document.querySelectorAll('div, span')).find(el =>
                        el.textContent.includes('Error') ||
                        el.textContent.includes('error') ||
                        el.textContent.includes('No se pudo')
                    );

                    if (mensajeError && mensajeError.textContent.length < 200) {
                        console.error('❌ Error al crear persona:', mensajeError.textContent);
                        mostrarNotificacion('Error al registrar persona', 'error');
                        break;
                    }

                    await sleep(200);
                    intentos++;
                }

                if (intentos >= maxIntentos) {
                    console.warn('⚠️ No se detectó mensaje de confirmación');
                    console.log('ℹ️ Verifica manualmente si la persona fue creada');
                }
            } else {
                console.error('❌ No se pudo hacer clic en "Crear Persona"');
            }

        } catch (error) {
            console.error('❌ Error al llenar el formulario:', error);
            mostrarNotificacion('Error en el proceso de llenado', 'error');
        }
    }

    /**
     * Muestra una notificación visual en pantalla
     */
    function mostrarNotificacion(mensaje, tipo = 'success') {
        // Eliminar notificación anterior si existe
        const notifAnterior = document.getElementById('tampermonkey-notification');
        if (notifAnterior) {
            notifAnterior.remove();
        }

        const notif = document.createElement('div');
        notif.id = 'tampermonkey-notification';

        const estiloBase = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999;
            padding: 16px 24px;
            border-radius: 12px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideDown 0.3s ease-out;
        `;

        const colores = {
            success: 'background: linear-gradient(135deg, #39A900, #2d7a00); border: 2px solid #4CAF50;',
            error: 'background: linear-gradient(135deg, #EF4444, #DC2626); border: 2px solid #F87171;',
            info: 'background: linear-gradient(135deg, #3B82F6, #2563EB); border: 2px solid #60A5FA;'
        };

        notif.style.cssText = estiloBase + (colores[tipo] || colores.info);
        notif.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 24px;">${tipo === 'success' ? '✅' : tipo === 'error' ? '❌' : 'ℹ️'}</span>
                <span>${mensaje}</span>
            </div>
        `;

        document.body.appendChild(notif);

        // Auto-eliminar después de 5 segundos
        setTimeout(() => {
            notif.style.animation = 'slideUp 0.3s ease-in';
            setTimeout(() => notif.remove(), 300);
        }, 5000);
    }

    // ========================================
    // CREAR INTERFAZ DE BOTONES
    // ========================================

    function crearBotones() {
        // Verificar si ya existen los botones
        if (document.getElementById('tampermonkey-controls')) {
            console.log('⚠️ Botones ya creados');
            return;
        }

        console.log('🎨 Creando panel de control...');

        const contenedor = document.createElement('div');
        contenedor.id = 'tampermonkey-controls';
        contenedor.style.cssText = `
            position: fixed !important;
            bottom: 20px !important;
            right: 20px !important;
            z-index: 999999 !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            padding: 16px !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.1) !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif !important;
            width: 260px !important;
            backdrop-filter: blur(10px) !important;
        `;

        contenedor.innerHTML = `
            <div style="color: white; font-weight: 700; margin-bottom: 12px; text-align: center; font-size: 15px; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                🤖 Auto-Fill CTAccess
            </div>
            <div style="color: rgba(255,255,255,0.8); font-size: 11px; text-align: center; margin-bottom: 12px; line-height: 1.4;">
                Haz clic en un ejemplo para llenar el formulario automáticamente
            </div>

            <button id="ejemplo1-btn" style="
                width: 100%;
                padding: 12px;
                margin-bottom: 8px;
                background: linear-gradient(135deg, #39A900, #2d7a00);
                color: white;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                font-size: 13px;
                box-shadow: 0 4px 12px rgba(57, 169, 0, 0.3);
                position: relative;
                overflow: hidden;
            ">
                <div style="position: relative; z-index: 1;">
                    🏍️ Ejemplo 1
                    <div style="font-size: 10px; font-weight: 400; margin-top: 2px; opacity: 0.9;">
                        Portátil + Motocicleta
                    </div>
                </div>
            </button>

            <button id="ejemplo2-btn" style="
                width: 100%;
                padding: 12px;
                margin-bottom: 8px;
                background: linear-gradient(135deg, #39A900, #FDC300);
                color: white;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                font-size: 13px;
                box-shadow: 0 4px 12px rgba(80, 229, 249, 0.3);
            ">
                <div style="position: relative; z-index: 1;">
                    💻 Ejemplo 2
                    <div style="font-size: 10px; font-weight: 400; margin-top: 2px; opacity: 0.9;">
                        2 Portátiles + Instructor
                    </div>
                </div>
            </button>

            <button id="ejemplo3-btn" style="
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #FF6B6B, #EE5A6F);
                color: white;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                font-size: 13px;
                box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
            ">
                <div style="position: relative; z-index: 1;">
                    🚗 Ejemplo 3
                    <div style="font-size: 10px; font-weight: 400; margin-top: 2px; opacity: 0.9;">
                        Solo Automóvil
                    </div>
                </div>
            </button>
        `;

        document.body.appendChild(contenedor);

        // Agregar efectos hover y eventos
        const botones = contenedor.querySelectorAll('button');
        botones.forEach(btn => {
            // Hover
            btn.addEventListener('mouseenter', () => {
                btn.style.transform = 'translateY(-2px) scale(1.02)';
                btn.style.filter = 'brightness(1.1)';
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = 'translateY(0) scale(1)';
                btn.style.filter = 'brightness(1)';
            });
        });

        // Eventos de clic
        document.getElementById('ejemplo1-btn').addEventListener('click', () => {
            console.log('🏍️ Cargando Ejemplo 1...');
            llenarFormulario(ejemplos[0]);
        });

        document.getElementById('ejemplo2-btn').addEventListener('click', () => {
            console.log('💻 Cargando Ejemplo 2...');
            llenarFormulario(ejemplos[1]);
        });

        document.getElementById('ejemplo3-btn').addEventListener('click', () => {
            console.log('🚗 Cargando Ejemplo 3...');
            llenarFormulario(ejemplos[2]);
        });

        console.log('✅ Panel de control creado exitosamente');
    }

    // ========================================
    // INICIALIZACIÓN
    // ========================================

    async function inicializar() {
        console.log('🔄 Inicializando CTAccess Autofill...');

        try {
            // Esperar a que el formulario esté listo
            await waitForElement('#nombre', 10000);
            console.log('✅ Formulario detectado');

            // Esperar un poco más para asegurar que Vue está montado
            await sleep(1000);

            // Crear botones
            crearBotones();

            console.log('🎉 CTAccess Autofill inicializado correctamente');
        } catch (error) {
            console.error('❌ Error al inicializar:', error);
            console.log('⚠️ Asegúrate de estar en la página: /personas/registrarse');
        }
    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar);
    } else {
        // DOM ya está listo
        setTimeout(inicializar, 1000);
    }

    // ========================================
    // AGREGAR ANIMACIONES CSS
    // ========================================
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideDown {
            from {
                transform: translate(-50%, -100px);
                opacity: 0;
            }
            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translate(-50%, 0);
                opacity: 1;
            }
            to {
                transform: translate(-50%, -100px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    console.log('🤖 [CTAccess Autofill] Script completamente cargado');

})();
