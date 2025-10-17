# 🤖 Script Tampermonkey para CTAccess - Autofill Formulario de Personas

Este script automatiza el llenado del formulario de registro de personas en CTAccess.

## 📋 Características

- ✅ Compatible con **Vue 3 + Inertia.js**
- ✅ Detecta y llena automáticamente los campos del formulario
- ✅ Incluye **3 ejemplos predefinidos** con diferentes combinaciones
- ✅ Navegación automática entre pasos del formulario
- ✅ Interfaz visual con botones flotantes
- ✅ Logs detallados en la consola para debugging

## 🚀 Instalación

### 1. Instalar Tampermonkey

Instala la extensión Tampermonkey en tu navegador:

- **Chrome**: [Tampermonkey en Chrome Web Store](https://chrome.google.com/webstore/detail/tampermonkey/dhdgffkkebhmkfjojejmpbldmpobfkfo)
- **Firefox**: [Tampermonkey en Firefox Add-ons](https://addons.mozilla.org/es/firefox/addon/tampermonkey/)
- **Edge**: [Tampermonkey en Edge Add-ons](https://microsoftedge.microsoft.com/addons/detail/tampermonkey/iikmkjmpaadaobahmlepeloendndfphd)

### 2. Instalar el Script

1. Haz clic en el icono de Tampermonkey en tu navegador
2. Selecciona **"Crear un nuevo script..."**
3. Borra todo el contenido del editor
4. Copia y pega el contenido del archivo `tampermonkey-autofill-personas.js`
5. Presiona **Ctrl + S** (o Cmd + S en Mac) para guardar
6. Cierra la pestaña del editor

## 📖 Cómo Usar

### Paso 1: Acceder al Formulario

Navega a la página de registro de personas:

```
http://127.0.0.1:8000/personas/registrarse
```

### Paso 2: Esperar el Panel de Control

Después de unos segundos, verás aparecer un **panel flotante morado** en la esquina inferior derecha con 3 botones:

![Panel de Control](https://via.placeholder.com/260x280/667eea/ffffff?text=Panel+CTAccess+Autofill)

### Paso 3: Seleccionar un Ejemplo

Haz clic en uno de los 3 botones:

- **🏍️ Ejemplo 1**: Aprendiz con 1 portátil y 1 motocicleta
- **💻 Ejemplo 2**: Instructor con 2 portátiles (sin vehículos)
- **🚗 Ejemplo 3**: Contratista con 1 automóvil (sin portátiles)

### Paso 4: Confirmación Automática

El script:
1. ✅ Llenará automáticamente todos los campos
2. ✅ Navegará hasta el **Paso 4 (Resumen)**
3. ✅ **Hará clic automáticamente en "Crear Persona"**
4. ✅ Esperará el mensaje de éxito
5. ✅ Mostrará una notificación visual en pantalla
6. ✅ El formulario se limpiará automáticamente y volverá al Paso 1

Puedes registrar otra persona inmediatamente después.

## 📊 Ejemplos Incluidos

### Ejemplo 1: Aprendiz con Portátil y Motocicleta
```javascript
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
}
```

### Ejemplo 2: Instructor con 2 Portátiles
```javascript
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
}
```

### Ejemplo 3: Contratista con Automóvil
```javascript
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
```

## 🔧 Personalización

### Agregar tus Propios Datos

Puedes editar el script y agregar más ejemplos en el array `ejemplos`:

```javascript
const ejemplos = [
    // ... ejemplos existentes ...
    {
        nombre: "Tu Nombre",
        documento: "123456789",
        tipoPersona: "Visitante",
        correo: "tu@email.com",
        portatiles: [],
        vehiculos: []
    }
];
```

Luego agrega un nuevo botón en la función `crearBotones()`.

### Funcionamiento Automático

El script ahora hace **TODO automáticamente**:

- ✅ Llena todos los campos del formulario
- ✅ Navega entre los pasos
- ✅ Hace clic en "Crear Persona"
- ✅ Espera el mensaje de éxito
- ✅ Muestra notificación visual
- ✅ Limpia el formulario automáticamente

No necesitas hacer nada más que hacer clic en uno de los 3 botones de ejemplo.

## 🐛 Debugging

### Ver los Logs en la Consola

1. Abre las **Herramientas de Desarrollador** (F12)
2. Ve a la pestaña **Console**
3. Verás logs detallados como:
   ```
   🤖 [CTAccess Autofill] Script cargado
   🔄 Inicializando CTAccess Autofill...
   ✅ Formulario detectado
   🎨 Creando panel de control...
   ✅ Panel de control creado exitosamente
   🎉 CTAccess Autofill inicializado correctamente
   ```

### Problemas Comunes

#### El panel no aparece
- ✅ Verifica que estás en la URL correcta: `http://127.0.0.1:8000/personas/registrarse`
- ✅ Abre la consola y busca errores en rojo
- ✅ Recarga la página (Ctrl + R o F5)

#### Los campos no se llenan
- ✅ Abre la consola y verifica si hay mensajes de error
- ✅ Verifica que el formulario esté visible (debes estar en el Paso 1)
- ✅ Intenta recargar la página y volver a intentar

#### El script no avanza de paso
- ✅ Verifica que los campos requeridos estén llenos (nombre y tipo de persona)
- ✅ Aumenta los tiempos de espera en el código (`sleep()`)

## 📝 Notas

- El script solo funciona en **desarrollo local** (`127.0.0.1:8000` o `localhost:8000`)
- Para usarlo en producción, agrega la URL en la sección `@match` del script:
  ```javascript
  // @match        https://tupagina.com/personas/registrarse
  ```
- El script es **seguro** y solo manipula el DOM del navegador, no hace peticiones externas

## 🤝 Soporte

Si tienes problemas o sugerencias, abre un issue en el repositorio o contacta al equipo de desarrollo.

---

**Versión**: 2.0
**Última actualización**: 2025-01-17
**Compatible con**: Vue 3 + Inertia.js + Laravel 11
