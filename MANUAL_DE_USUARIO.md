# 📘 Manual de Usuario - CTAccess
## Sistema de Control de Acceso

---

<div align="center">

**Versión 1.0**  
**Fecha: Octubre 2025**

![CTAccess](https://img.shields.io/badge/CTAccess-Sistema_de_Control-0066cc?style=for-the-badge)

*Manual completo para usuarios del Sistema de Control de Acceso*

</div>

---

## 📑 Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Requisitos del Sistema](#2-requisitos-del-sistema)
3. [Acceso al Sistema](#3-acceso-al-sistema)
4. [Tipos de Usuarios](#4-tipos-de-usuarios)
5. [Módulo de Registro Público](#5-módulo-de-registro-público)
6. [Panel de Administrador](#6-panel-de-administrador)
7. [Panel de Celador](#7-panel-de-celador)
8. [Portal de Personas](#8-portal-de-personas)
9. [Sistema de Códigos QR](#9-sistema-de-códigos-qr)
10. [Preguntas Frecuentes](#10-preguntas-frecuentes)
11. [Solución de Problemas](#11-solución-de-problemas)
12. [Glosario](#12-glosario)

---

## 1. Introducción

### 1.1 ¿Qué es CTAccess?

**CTAccess** es un sistema integral de gestión y control de acceso diseñado para instituciones que necesitan registrar, monitorear y controlar el ingreso y salida de personas, vehículos y equipos portátiles.

### 1.2 Características Principales

- ✅ **Registro digital de personas** con datos completos
- ✅ **Control de accesos** en tiempo real con entrada y salida
- ✅ **Gestión de vehículos** asociados a cada persona
- ✅ **Control de portátiles** mediante códigos QR únicos
- ✅ **Verificación rápida** con escaneo de códigos QR
- ✅ **Dashboard analítico** con estadísticas en tiempo real
- ✅ **Sistema de roles** (Administrador, Celador, Persona)
- ✅ **Reportes e historiales** exportables a PDF
- ✅ **Gestión de incidencias** con prioridades

### 1.3 Beneficios

- 📊 **Trazabilidad completa** de todos los accesos
- ⚡ **Agilidad en el registro** de entradas y salidas
- 🔒 **Seguridad mejorada** mediante autenticación
- 📱 **Acceso desde cualquier dispositivo** con navegador web
- 📈 **Análisis de datos** para toma de decisiones
- 🎯 **Control específico** de equipos y vehículos

---

## 2. Requisitos del Sistema

### 2.1 Requisitos del Navegador

El sistema es compatible con los siguientes navegadores actualizados:

- ✅ Google Chrome (versión 90 o superior)
- ✅ Mozilla Firefox (versión 88 o superior)
- ✅ Microsoft Edge (versión 90 o superior)
- ✅ Safari (versión 14 o superior)

### 2.2 Requisitos de Dispositivos

- **Computadora de escritorio/portátil**: Windows 7+, macOS 10.12+, Linux
- **Tablet**: iPad, Android tablet
- **Smartphone**: iPhone (iOS 12+), Android (8.0+)
- **Cámara**: Para escaneo de códigos QR (opcional pero recomendado)

### 2.3 Conexión a Internet

- Conexión estable a Internet (mínimo 2 Mbps)
- Para funciones en tiempo real se recomienda 5 Mbps o superior

---

## 3. Acceso al Sistema

### 3.1 URL de Acceso

Ingrese a la aplicación a través de:
```
https://[dominio-de-su-institucion]/
```

### 3.2 Pantalla de Inicio

Al ingresar verá:
- **Página principal** con información general
- Botón **"Registrarse"** para nuevos usuarios
- Enlaces de **inicio de sesión** según tipo de usuario

### 3.3 Tipos de Inicio de Sesión

El sistema cuenta con **tres tipos** de autenticación:

1. **Inicio de Sesión de Personas** (`/login`)
   - Para empleados, visitantes, estudiantes registrados
   - Acceso al portal personal

2. **Inicio de Sesión del Sistema** (`/system/login`)
   - Para administradores y celadores
   - Acceso a paneles de control

3. **Registro Público** (`/personas/registrarse`)
   - Para nuevas personas que desean registrarse
   - No requiere autenticación previa

---

## 4. Tipos de Usuarios

### 4.1 Persona (Usuario Registrado)

**¿Quién es?**
- Empleado, visitante, estudiante o cualquier persona registrada en el sistema

**¿Qué puede hacer?**
- ✅ Registrarse de forma pública
- ✅ Iniciar sesión en su portal personal
- ✅ Ver su código QR personal
- ✅ Consultar su historial de accesos
- ✅ Gestionar sus datos personales
- ✅ Ver vehículos y portátiles asociados

### 4.2 Celador

**¿Quién es?**
- Personal de seguridad encargado del control de accesos

**¿Qué puede hacer?**
- ✅ Registrar entradas y salidas
- ✅ Escanear códigos QR
- ✅ Buscar personas en el sistema
- ✅ Registrar incidencias
- ✅ Ver accesos activos en tiempo real
- ✅ Consultar historial del día
- ✅ Generar reportes PDF

### 4.3 Administrador

**¿Quién es?**
- Personal con acceso completo al sistema

**¿Qué puede hacer?**
- ✅ Todo lo que puede hacer el Celador
- ✅ Gestionar usuarios del sistema
- ✅ Administrar personas registradas
- ✅ Gestionar portátiles y vehículos
- ✅ Configurar permisos
- ✅ Ver estadísticas avanzadas
- ✅ Acceder a todos los módulos

---

## 5. Módulo de Registro Público

### 5.1 ¿Cómo Registrarse?

#### Paso 1: Acceder al Formulario
1. En la página principal, haga clic en **"Registrarse"**
2. O navegue directamente a `/personas/registrarse`

#### Paso 2: Completar Información Personal

**Datos Básicos:**
- **Tipo de Documento**: CC, TI, CE, Pasaporte
- **Número de Documento**: Sin espacios ni guiones
- **Nombres**: Nombres completos
- **Apellidos**: Apellidos completos
- **Correo Electrónico**: Válido y activo
- **Teléfono**: Número de contacto
- **Dirección**: Dirección de residencia

**Información Adicional:**
- **Tipo de Persona**: Empleado, Visitante, Estudiante, Contratista, Otro
- **Género**: Masculino, Femenino, Otro, Prefiero no decir
- **Fecha de Nacimiento**: Formato DD/MM/AAAA

**Datos Institucionales (opcional):**
- **Jornada**: Mañana, Tarde, Noche, Mixta
- **Programa de Formación**: Si aplica

#### Paso 3: Agregar Vehículos (Opcional)

Si posee vehículo(s), puede registrarlos:
1. Haga clic en **"+ Agregar Vehículo"**
2. Complete:
   - **Placa**: Formato ABC123
   - **Tipo**: Carro, Moto, Bicicleta, Otro
   - **Marca**: Ej: Toyota, Yamaha
   - **Modelo**: Ej: Corolla 2020
   - **Color**: Ej: Blanco

Puede agregar múltiples vehículos haciendo clic nuevamente en el botón.

#### Paso 4: Agregar Portátiles (Opcional)

Si posee equipo(s) portátil(es):
1. Haga clic en **"+ Agregar Portátil"**
2. Complete:
   - **Serial**: Número de serie del equipo
   - **Marca**: Ej: Dell, HP, Lenovo
   - **Modelo**: Ej: Latitude 5420
   - **Descripción**: Detalles adicionales

Se generará automáticamente un código QR único por portátil.

#### Paso 5: Revisar y Enviar

1. Verifique que todos los datos sean correctos
2. Haga clic en **"Registrar"**
3. El sistema validará la información
4. Si todo es correcto, recibirá un mensaje de éxito

### 5.2 Después del Registro

Una vez registrado:
- ✅ Recibirá un correo con su **código QR personal**
- ✅ Podrá iniciar sesión con su documento y contraseña
- ✅ Accederá a su portal personal
- ✅ El personal de seguridad podrá registrar sus accesos

### 5.3 Consejos para el Registro

- ⚠️ Use su **documento real** (será verificado)
- ✅ Proporcione un **correo válido** (recibirá notificaciones)
- ✅ Si comete un error, contacte al administrador
- ⚠️ Los datos de portátiles y vehículos deben ser **exactos**

---

## 6. Panel de Administrador

### 6.1 Acceso al Panel

1. Navegue a `/system/login`
2. Ingrese sus credenciales de administrador
3. Será redirigido al dashboard administrativo

### 6.2 Dashboard Principal

El dashboard muestra:

**Métricas Principales:**
- Total de personas registradas
- Accesos del día
- Entradas activas (personas dentro)
- Incidencias pendientes

**Gráficos en Tiempo Real:**
- Accesos por hora
- Tendencias de entrada/salida
- Distribución por tipo de persona
- Estado de incidencias

### 6.3 Gestión de Usuarios del Sistema

#### Ver Usuarios
1. En el menú lateral, seleccione **"Usuarios"**
2. Verá lista con:
   - Nombre completo
   - Email
   - Rol (Administrador/Celador)
   - Estado

#### Crear Nuevo Usuario
1. Haga clic en **"+ Nuevo Usuario"**
2. Complete el formulario:
   - Nombre completo
   - Email
   - Contraseña
   - Confirmar contraseña
   - Rol
3. Clic en **"Guardar"**

#### Editar Usuario
1. En la fila del usuario, clic en ícono de **editar** ✏️
2. Modifique los datos necesarios
3. Clic en **"Actualizar"**

#### Eliminar Usuario
1. Clic en ícono de **eliminar** 🗑️
2. Confirme la acción
3. El usuario será eliminado

⚠️ **Importante**: No puede eliminar su propio usuario mientras está autenticado.

### 6.4 Gestión de Personas

#### Ver Todas las Personas
1. Menú lateral → **"Personas"**
2. Verá tabla con datos principales
3. Use la barra de búsqueda para filtrar

#### Crear Nueva Persona (Administrativamente)
1. Clic en **"+ Nueva Persona"**
2. Complete todos los campos requeridos
3. Agregue vehículos y portátiles si aplica
4. Clic en **"Guardar"**

#### Editar Persona
1. Busque la persona en la lista
2. Clic en ícono de **editar** ✏️
3. Modifique datos necesarios
4. Puede agregar/eliminar vehículos y portátiles
5. Clic en **"Actualizar"**

#### Ver Detalles de Persona
1. Clic en el nombre o ícono de **ver** 👁️
2. Verá:
   - Datos completos
   - Vehículos asociados
   - Portátiles con códigos QR
   - Historial de accesos

#### Eliminar Persona
1. Clic en ícono de **eliminar** 🗑️
2. Confirme la acción
3. **Nota**: Esto eliminará también vehículos y portátiles asociados

### 6.5 Gestión de Portátiles

#### Ver Portátiles
1. Menú lateral → **"Portátiles"**
2. Lista completa de equipos registrados
3. Información mostrada:
   - Serial
   - Marca y modelo
   - Persona propietaria
   - Código QR

#### Agregar Portátil a Persona Existente
1. Clic en **"+ Nuevo Portátil"**
2. Seleccione la persona propietaria
3. Complete:
   - Serial (único)
   - Marca
   - Modelo
   - Descripción
4. Clic en **"Guardar"**
5. Se genera automáticamente el código QR

#### Editar Portátil
1. Clic en ícono de **editar** ✏️
2. Modifique datos necesarios
3. **Nota**: No puede cambiar la persona propietaria
4. Clic en **"Actualizar"**

#### Eliminar Portátil
1. Clic en ícono de **eliminar** 🗑️
2. Confirme la acción
3. Se elimina el portátil y su código QR

#### Descargar/Imprimir Código QR
1. Clic en el ícono de **QR** o **descargar**
2. Se abrirá/descargará el código QR
3. Imprima y adhiera al equipo

### 6.6 Gestión de Vehículos

#### Ver Vehículos
1. Menú lateral → **"Vehículos"**
2. Lista completa de vehículos registrados
3. Información:
   - Placa
   - Tipo
   - Marca/Modelo
   - Color
   - Propietario

#### Agregar Vehículo
1. Clic en **"+ Nuevo Vehículo"**
2. Seleccione persona propietaria
3. Complete datos del vehículo
4. Clic en **"Guardar"**

#### Editar Vehículo
1. Clic en ícono de **editar** ✏️
2. Modifique datos necesarios
3. Clic en **"Actualizar"**

#### Eliminar Vehículo
1. Clic en ícono de **eliminar** 🗑️
2. Confirme la acción

### 6.7 Control de Accesos

El administrador tiene acceso completo al módulo de accesos (igual que el celador).

Ver sección [7. Panel de Celador](#7-panel-de-celador) para detalles.

### 6.8 Verificación QR

El administrador puede usar el módulo de verificación QR.

Ver sección [7.4 Verificación por QR](#74-verificación-por-qr) para detalles.

### 6.9 Gestión de Incidencias

#### Ver Incidencias
1. Menú lateral → **"Incidencias"**
2. Lista de todas las incidencias
3. Filtros disponibles:
   - Por estado (Pendiente, En proceso, Resuelta)
   - Por prioridad (Alta, Media, Baja)
   - Por fecha

#### Crear Incidencia
1. Clic en **"+ Nueva Incidencia"**
2. Seleccione:
   - Acceso relacionado
   - Tipo de incidencia
   - Prioridad
   - Descripción detallada
3. Clic en **"Registrar"**

#### Actualizar Estado de Incidencia
1. Clic en la incidencia
2. Cambie estado:
   - Pendiente → En proceso
   - En proceso → Resuelta
3. Agregue notas/observaciones
4. Clic en **"Actualizar"**

### 6.10 Historial y Reportes

#### Ver Historial
1. Menú lateral → **"Historial"**
2. Seleccione rango de fechas
3. Aplique filtros:
   - Por persona
   - Por tipo de acceso
   - Por estado

#### Exportar a PDF
1. Configure los filtros deseados
2. Clic en **"Exportar PDF"**
3. Se descargará reporte completo con:
   - Resumen de accesos
   - Detalles por entrada
   - Incidencias registradas
   - Estadísticas del período

### 6.11 Gestión de Permisos

#### Ver Permisos del Sistema
1. Menú lateral → **"Permisos"**
2. Lista de todos los permisos disponibles
3. Permisos por módulo:
   - `personas.*` - Gestión de personas
   - `accesos.*` - Control de accesos
   - `incidencias.*` - Gestión de incidencias
   - `portatiles.*` - Gestión de portátiles
   - `vehiculos.*` - Gestión de vehículos

#### Asignar Permisos a Roles
1. Seleccione un rol (Administrador/Celador)
2. Marque/desmarque permisos
3. Clic en **"Guardar Cambios"**

**Permisos típicos por rol:**

**Administrador:**
- ✅ Todos los permisos (acceso completo)

**Celador:**
- ✅ Ver personas
- ✅ Registrar accesos
- ✅ Ver y crear incidencias
- ✅ Ver portátiles y vehículos
- ❌ Eliminar personas
- ❌ Gestionar usuarios del sistema

---

## 7. Panel de Celador

### 7.1 Acceso al Panel

1. Navegue a `/system/login`
2. Ingrese credenciales de celador
3. Será redirigido al dashboard de celador

### 7.2 Dashboard del Celador

**Información visible:**
- Accesos del día actual
- Personas actualmente dentro
- Incidencias del día
- Total de personas registradas

**Acceso rápido a:**
- Registro de nuevo acceso
- Verificación QR
- Accesos activos
- Incidencias

### 7.3 Registro Manual de Accesos

#### Paso 1: Buscar Persona
1. Menú lateral → **"Accesos"** → **"Nuevo Acceso"**
2. En el campo de búsqueda, ingrese:
   - Número de documento
   - Nombre
   - Apellido
3. Seleccione la persona de la lista

#### Paso 2: Seleccionar Portátiles (si aplica)
1. Si la persona ingresa con portátil(es):
   - Marque el/los equipo(s) que ingresa
   - El sistema mostrará los portátiles registrados a esa persona
2. Si no tiene portátiles registrados, puede continuar sin seleccionar

#### Paso 3: Seleccionar Vehículos (si aplica)
1. Si la persona ingresa con vehículo:
   - Marque el vehículo correspondiente
   - Se mostrará: placa, tipo, marca, modelo
2. Si ingresa a pie, no seleccione ningún vehículo

#### Paso 4: Observaciones
1. Campo opcional para notas
2. Ej: "Ingresa por reunión", "Visita al área X"

#### Paso 5: Registrar Entrada
1. Revise que todo sea correcto
2. Clic en **"Registrar Entrada"**
3. El sistema:
   - Guarda fecha/hora automática
   - Asocia portátiles y vehículos
   - Marca el acceso como "activo"

### 7.4 Verificación por QR

#### Configurar Cámara
1. Menú lateral → **"Verificación QR"**
2. El navegador solicitará permiso para usar la cámara
3. Clic en **"Permitir"**

#### Escanear Código QR - Entrada
1. La persona muestra su código QR personal
2. Enfoque el código en la cámara
3. El sistema automáticamente:
   - Lee el código
   - Busca a la persona
   - Muestra su información
   - Portátiles y vehículos registrados

4. Confirme:
   - Seleccione portátiles que ingresa (si aplica)
   - Seleccione vehículo (si aplica)
   - Agregue observaciones (opcional)
5. Clic en **"Confirmar Entrada"**

#### Escanear Código QR - Salida
1. Escanee nuevamente el código QR
2. Si la persona tiene un acceso activo:
   - Sistema muestra datos del acceso
   - Hora de entrada
   - Portátiles/vehículos con los que ingresó
3. Clic en **"Registrar Salida"**
4. El acceso cambia a estado "finalizado"

#### Búsqueda Alternativa
Si el QR no funciona:
1. Clic en **"Buscar Manualmente"**
2. Ingrese número de documento
3. Seleccione la persona
4. Proceda normalmente

### 7.5 Ver Accesos Activos

1. Menú lateral → **"Accesos"** → **"Activos"**
2. Lista en tiempo real de personas dentro
3. Información mostrada:
   - Nombre completo
   - Documento
   - Hora de entrada
   - Portátiles ingresados
   - Vehículo (si aplica)
   - Tiempo transcurrido

#### Registrar Salida Manual
1. Localice la persona en la lista
2. Clic en **"Registrar Salida"**
3. Confirme la acción
4. Se marca hora de salida automáticamente

### 7.6 Gestión de Incidencias

#### ¿Qué es una Incidencia?
Cualquier evento anormal o importante:
- Problema con portátil (no coincide serial)
- Vehículo con documentos vencidos
- Persona sospechosa
- Incidente de seguridad
- Acceso fuera de horario autorizado

#### Registrar Nueva Incidencia
1. Menú lateral → **"Incidencias"** → **"Nueva"**
2. Seleccione el acceso relacionado
3. Complete:
   - **Tipo**: Portátil, Vehículo, Persona, Seguridad, Otro
   - **Prioridad**: Alta, Media, Baja
   - **Descripción**: Detalle completo del evento
4. Clic en **"Registrar Incidencia"**

#### Ver Incidencias del Día
1. Menú lateral → **"Incidencias"**
2. Lista de incidencias registradas
3. Estados:
   - 🔴 **Pendiente**: Sin atender
   - 🟡 **En proceso**: En atención
   - 🟢 **Resuelta**: Solucionada

### 7.7 Consultar Personas

#### Búsqueda Rápida
1. Menú lateral → **"Personas"**
2. Use la barra de búsqueda
3. Busque por:
   - Documento
   - Nombre
   - Apellido
   - Email

#### Ver Detalles de Persona
1. Clic en la persona
2. Información visible:
   - Datos personales
   - Foto (si tiene)
   - Vehículos registrados
   - Portátiles registrados
   - Código QR personal
   - Historial de accesos recientes

#### Ver Historial de Accesos
1. En los detalles de persona, sección **"Historial"**
2. Lista de todos sus accesos:
   - Fecha y hora de entrada
   - Fecha y hora de salida
   - Portátiles ingresados
   - Vehículo usado
   - Observaciones
   - Estado del acceso

### 7.8 Historial del Día

#### Ver Todos los Accesos del Día
1. Menú lateral → **"Historial"**
2. Por defecto muestra el día actual
3. Información de cada acceso:
   - Persona
   - Entrada/Salida
   - Duración
   - Portátiles/Vehículos
   - Estado

#### Filtrar Historial
- **Por fecha**: Seleccione día específico
- **Por estado**: Activo, Finalizado, Incidencia
- **Por tipo de persona**: Empleado, Visitante, etc.

#### Exportar Reporte
1. Configure filtros deseados
2. Clic en **"Exportar PDF"**
3. Se genera reporte profesional con:
   - Encabezado institucional
   - Fecha del reporte
   - Total de accesos
   - Detalles de cada acceso
   - Resumen estadístico

---

## 8. Portal de Personas

### 8.1 Inicio de Sesión Personal

1. En la página principal, clic en **"Iniciar Sesión"** (no el del sistema)
2. O navegue a `/login`
3. Ingrese:
   - **Documento**: Su número de identificación
   - **Contraseña**: La que recibió por correo o estableció
4. Clic en **"Ingresar"**

### 8.2 Dashboard Personal

Al iniciar sesión verá:

**Información Principal:**
- Sus datos personales
- Su código QR personal
- Acceso actual (si está dentro)

**Secciones disponibles:**
- Inicio
- Mi Perfil
- Mis Accesos
- Mis Vehículos
- Mis Portátiles

### 8.3 Mi Código QR

#### Ver y Descargar
1. En el dashboard, sección **"Mi Código QR"**
2. Visualice su código único
3. Opciones:
   - **Descargar**: Clic en botón de descarga
   - **Imprimir**: Use función de impresión del navegador
   - **Compartir**: Guarde en su móvil

#### ¿Para qué sirve?
- ✅ Registro rápido de entrada/salida
- ✅ Identificación única en la institución
- ✅ Evita búsquedas manuales
- ✅ Agiliza el proceso de acceso

#### Recomendaciones
- 📱 Guarde una copia en su smartphone
- 🖨️ Imprima y plastifique una versión física
- ⚠️ No comparta su código QR con terceros
- ✅ Tenga siempre acceso a su código

### 8.4 Mi Perfil

#### Ver Información Personal
1. Menú lateral → **"Mi Perfil"**
2. Verá todos sus datos:
   - Información básica
   - Datos de contacto
   - Información institucional
   - Fecha de registro

#### Actualizar Datos
1. Clic en **"Editar Perfil"**
2. Modifique los campos permitidos:
   - ✅ Teléfono
   - ✅ Dirección
   - ✅ Email
   - ❌ Documento (no editable)
   - ❌ Nombres/Apellidos (contacte al admin)
3. Clic en **"Guardar Cambios"**

#### Cambiar Contraseña
1. En la sección de perfil, clic en **"Cambiar Contraseña"**
2. Ingrese:
   - Contraseña actual
   - Nueva contraseña
   - Confirmar nueva contraseña
3. Clic en **"Actualizar Contraseña"**

### 8.5 Mis Vehículos

#### Ver Vehículos Registrados
1. Menú lateral → **"Mis Vehículos"**
2. Lista de sus vehículos:
   - Placa
   - Tipo
   - Marca/Modelo
   - Color

#### Solicitar Agregar Vehículo
Si necesita registrar un vehículo nuevo:
1. Contacte al administrador
2. Proporcione:
   - Placa
   - Tarjeta de propiedad
   - SOAT vigente
3. El administrador lo agregará al sistema

⚠️ **Nota**: Por seguridad, solo el administrador puede agregar/modificar vehículos.

### 8.6 Mis Portátiles

#### Ver Equipos Registrados
1. Menú lateral → **"Mis Portátiles"**
2. Lista de sus portátiles:
   - Serial
   - Marca/Modelo
   - Descripción
   - Código QR del equipo

#### Descargar Código QR de Portátil
1. Clic en el ícono de QR junto al equipo
2. Se descarga el código QR
3. **Importante**: Imprima y adhiera al equipo físico

#### Solicitar Agregar Portátil
Para registrar un nuevo equipo:
1. Contacte al administrador
2. Tenga a mano:
   - Serial del equipo (generalmente en etiqueta inferior)
   - Factura o documento de propiedad
3. El administrador lo registrará

### 8.7 Historial de Mis Accesos

#### Ver Todos Mis Accesos
1. Menú lateral → **"Mis Accesos"**
2. Lista completa cronológica
3. Información de cada acceso:
   - Fecha y hora de entrada
   - Fecha y hora de salida (si aplica)
   - Duración de permanencia
   - Portátiles que ingresaste
   - Vehículo usado
   - Estado

#### Filtrar Historial
- Por rango de fechas
- Por estado (activos, finalizados)
- Por mes

#### Ver Estadísticas Personales
- Total de accesos en el mes
- Promedio de tiempo de permanencia
- Días con más accesos
- Portátiles más usados

---

## 9. Sistema de Códigos QR

### 9.1 ¿Qué son los Códigos QR en CTAccess?

Los códigos QR son identificadores únicos visuales que contienen:
- **QR de Persona**: ID único de cada persona registrada
- **QR de Portátil**: ID único de cada equipo portátil

### 9.2 Tipos de Códigos QR

#### QR Personal
- **¿Quién lo tiene?** Cada persona registrada
- **¿Qué contiene?** ID de la persona + datos básicos
- **¿Para qué sirve?** Registro rápido de entrada/salida
- **¿Dónde obtenerlo?**
  - Portal personal después de iniciar sesión
  - Correo electrónico enviado al registrarse
  - Solicitarlo al administrador

#### QR de Portátil
- **¿Quién lo tiene?** Cada equipo registrado
- **¿Qué contiene?** Serial del equipo + propietario
- **¿Para qué sirve?** Identificación rápida del equipo
- **¿Dónde obtenerlo?**
  - Portal personal, sección "Mis Portátiles"
  - El administrador puede imprimirlo
- **Uso recomendado**: Adherir físicamente al equipo

### 9.3 Uso del Sistema QR

#### Para la Persona (Usuario)
1. **Tenga su QR siempre disponible**:
   - En su smartphone (captura de pantalla o archivo)
   - Impreso y plastificado en carnet
   - En un documento digital accesible

2. **Al llegar a la institución**:
   - Muestre su QR al celador
   - Espere la confirmación del escaneo
   - Ingrese cuando se le autorice

3. **Al salir de la institución**:
   - Muestre nuevamente su QR
   - Confirme que se registre su salida

#### Para el Celador
1. **Verificar QR de Persona**:
   - Abra módulo de verificación QR
   - Permita acceso a la cámara
   - Enfoque el código mostrado
   - El sistema carga automáticamente los datos
   - Confirme entrada o salida según corresponda

2. **Verificar QR de Portátil**:
   - Al registrar un acceso con portátil
   - Escanee el QR adherido al equipo
   - Sistema valida que el serial coincida
   - Si coincide, registre el acceso
   - Si no coincide, registre incidencia

### 9.4 Ventajas del Sistema QR

✅ **Velocidad**: Registro en segundos  
✅ **Precisión**: Sin errores de digitación  
✅ **Trazabilidad**: Registro automático  
✅ **Seguridad**: Códigos únicos no duplicables  
✅ **Facilidad**: No requiere capacitación extensa  
✅ **Compatibilidad**: Funciona con cualquier cámara

### 9.5 Solución de Problemas con QR

#### El código QR no escanea

**Posibles causas y soluciones:**

1. **Código borroso o dañado**
   - Solución: Descargue e imprima nuevo código desde su portal

2. **Mala iluminación**
   - Solución: Mueva a un área con mejor luz o use linterna del móvil

3. **Cámara sin permisos**
   - Solución: En el navegador, permita el acceso a la cámara

4. **Código muy pequeño o muy grande**
   - Solución: Ajuste la distancia entre la cámara y el código

5. **Reflejo en código plastificado**
   - Solución: Cambie el ángulo para evitar reflejo de luz

#### Alternativa sin QR
Si el sistema QR no funciona:
1. El celador puede buscar manualmente por documento
2. Clic en "Buscar Manualmente" en el módulo QR
3. Ingrese número de documento
4. Seleccione la persona
5. Proceda con el registro normal

---

## 10. Preguntas Frecuentes

### 10.1 Registro y Acceso

**P: ¿Cómo obtengo acceso al sistema?**  
R: Regístrese públicamente en `/personas/registrarse`. Una vez aprobado, recibirá sus credenciales por correo.

**P: Olvidé mi contraseña, ¿qué hago?**  
R: Use la opción "Olvidé mi contraseña" en la página de login. Recibirá un correo para restablecerla.

**P: ¿Puedo cambiar mi número de documento después de registrarme?**  
R: No, el documento es inmutable. Si necesita cambiarlo, contacte al administrador.

**P: ¿Cuánto tiempo tarda en activarse mi cuenta?**  
R: Si se registra públicamente, su cuenta está activa de inmediato. Si un admin lo registra, también es inmediato.

### 10.2 Códigos QR

**P: ¿Dónde encuentro mi código QR personal?**  
R: En su portal personal después de iniciar sesión, o en el correo que recibió al registrarse.

**P: Perdí mi código QR, ¿cómo lo recupero?**  
R: Inicie sesión en su portal personal y descárguelo nuevamente. También puede solicitarlo al administrador.

**P: ¿El código QR caduca?**  
R: No, su código QR es permanente mientras esté activo en el sistema.

**P: ¿Puedo usar una foto de mi QR en lugar del original?**  
R: Sí, puede usar una captura de pantalla o foto en su smartphone.

### 10.3 Vehículos y Portátiles

**P: ¿Cómo registro un vehículo?**  
R: Durante el registro inicial o contactando al administrador para agregarlo posteriormente.

**P: ¿Puedo registrar múltiples vehículos?**  
R: Sí, puede tener tantos vehículos como necesite registrados.

**P: ¿Qué hago si cambio de vehículo?**  
R: Solicite al administrador eliminar el anterior y agregar el nuevo.

**P: ¿Es obligatorio registrar mi portátil?**  
R: Depende de la política de su institución. Generalmente, si ingresa con él, debe estar registrado.

**P: ¿Dónde coloco el código QR del portátil?**  
R: En un lugar visible y accesible del equipo, preferiblemente en la parte superior o trasera.

### 10.4 Accesos

**P: ¿Qué pasa si olvido registrar mi salida?**  
R: El celador puede registrarla manualmente desde "Accesos Activos". Contacte al personal de seguridad.

**P: ¿Puedo ver cuánto tiempo permanecí en la institución?**  
R: Sí, en su portal personal, sección "Mis Accesos", verá la duración de cada visita.

**P: ¿Por qué necesito mostrar mi QR dos veces (entrada y salida)?**  
R: Para trazabilidad completa. El sistema registra tanto su ingreso como su salida.

**P: ¿Puedo entrar sin mi código QR?**  
R: Sí, el celador puede buscarlo manualmente por su documento.

### 10.5 Incidencias

**P: ¿Qué es una incidencia?**  
R: Cualquier evento anormal: problemas con equipos, vehículos, o situaciones de seguridad.

**P: ¿Quién puede registrar incidencias?**  
R: Celadores y administradores.

**P: ¿Me notifican si hay una incidencia sobre mi acceso?**  
R: Actualmente no hay notificaciones automáticas. Consulte con el administrador.

### 10.6 Roles y Permisos

**P: ¿Cuál es la diferencia entre celador y administrador?**  
R: El administrador tiene acceso completo al sistema, puede gestionar usuarios, personas, equipos, etc. El celador se enfoca en el registro de accesos e incidencias.

**P: ¿Puedo solicitar acceso como celador?**  
R: Debe solicitarlo al administrador del sistema de su institución.

**P: ¿Los administradores ven todos mis accesos?**  
R: Sí, por motivos de seguridad y auditoría, los administradores tienen acceso completo al historial.

### 10.7 Reportes

**P: ¿Puedo exportar mi historial personal?**  
R: Actualmente los reportes PDF son generados por celadores/admins. Solicite su reporte al personal autorizado.

**P: ¿Qué información contiene el reporte PDF?**  
R: Fecha, persona, entradas, salidas, portátiles, vehículos, incidencias, y resumen estadístico.

---

## 11. Solución de Problemas

### 11.1 Problemas de Acceso

#### No puedo iniciar sesión

**Síntoma**: Al ingresar credenciales, muestra error.

**Soluciones**:
1. Verifique que está usando el login correcto:
   - Personas: `/login`
   - Sistema (Celador/Admin): `/system/login`
2. Confirme que su usuario/documento está escrito correctamente
3. Verifique que la contraseña es correcta (distingue mayúsculas/minúsculas)
4. Use "Olvidé mi contraseña" si es necesario
5. Contacte al administrador si el problema persiste

#### El sistema me cierra sesión constantemente

**Síntoma**: Sesión se cierra sin querer.

**Soluciones**:
1. Verifique su conexión a Internet (debe ser estable)
2. No use modo incógnito del navegador
3. Permita cookies en su navegador
4. Actualice su navegador a la última versión
5. Limpie caché y cookies del navegador

#### No recibí el correo de registro

**Síntoma**: Completé el registro pero no llegó el correo.

**Soluciones**:
1. Revise la carpeta de SPAM/Correo no deseado
2. Verifique que el correo ingresado es correcto
3. Espere 5-10 minutos (puede haber demora)
4. Contacte al administrador para reenviar el correo

### 11.2 Problemas con Códigos QR

#### La cámara no funciona para escanear QR

**Soluciones**:
1. Verifique que dio permisos de cámara al navegador
2. Cierre otras aplicaciones que puedan estar usando la cámara
3. Refresque la página (F5) y permita acceso nuevamente
4. Use otro navegador (Chrome recomendado)
5. Use "Buscar Manualmente" como alternativa

#### El QR escanea pero no carga datos

**Soluciones**:
1. Verifique conexión a Internet
2. Confirme que el QR no está dañado o modificado
3. Descargue e imprima un QR nuevo
4. Contacte al administrador si el problema persiste

### 11.3 Problemas con Datos

#### No puedo editar mis datos personales

**Soluciones**:
1. Algunos datos son inmutables (documento, nombres, apellidos)
2. Para cambiar estos datos, contacte al administrador
3. Datos editables: teléfono, dirección, email

#### No aparecen mis vehículos/portátiles

**Soluciones**:
1. Verifique que fueron registrados correctamente
2. Refresque la página (F5)
3. Cierre sesión y vuelva a iniciar
4. Contacte al administrador para verificar

### 11.4 Problemas de Rendimiento

#### El sistema está lento

**Soluciones**:
1. Verifique su conexión a Internet
2. Cierre pestañas del navegador que no usa
3. Limpie caché del navegador
4. Reinicie el navegador
5. Use un navegador moderno actualizado

#### Las páginas no cargan completamente

**Soluciones**:
1. Refresque la página (F5 o Ctrl+F5 para forzar)
2. Verifique que JavaScript está habilitado
3. Desactive extensiones del navegador temporalmente
4. Pruebe en modo incógnito
5. Use otro navegador

### 11.5 Errores Comunes

#### "Token CSRF inválido"

**Causa**: La sesión expiró.  
**Solución**: Refresque la página (F5) e intente nuevamente.

#### "No autorizado"

**Causa**: No tiene permisos para esa acción.  
**Solución**: Verifique su rol y permisos con el administrador.

#### "Registro duplicado"

**Causa**: Documento, placa o serial ya existe.  
**Solución**: Verifique los datos ingresados. Si el error persiste, contacte al admin.

#### "Error de validación"

**Causa**: Datos incompletos o formato incorrecto.  
**Solución**: Revise que todos los campos requeridos estén completos y en el formato correcto.

### 11.6 Contacto de Soporte

Si ninguna solución funciona:

1. **Documente el problema**:
   - ¿Qué estaba haciendo?
   - ¿Qué mensaje de error apareció?
   - ¿En qué página ocurrió?
   - Captura de pantalla si es posible

2. **Contacte al administrador del sistema**:
   - Proporcione toda la información documentada
   - Mencione su usuario/documento
   - Indique navegador y dispositivo usado

---

## 12. Glosario

**Acceso**: Registro de entrada y/o salida de una persona a la institución.

**Acceso Activo**: Persona que ha registrado entrada pero aún no ha registrado salida (está dentro).

**Acceso Finalizado**: Persona que ha registrado tanto entrada como salida (ya salió).

**Administrador**: Usuario del sistema con todos los permisos de gestión y configuración.

**Celador**: Usuario del sistema encargado del registro de accesos e incidencias.

**Código QR**: Código de barras bidimensional que contiene información codificada digitalmente.

**Dashboard**: Panel de control con estadísticas e información resumida.

**Documento**: Número de identificación (CC, TI, CE, Pasaporte).

**Guard**: Sistema de autenticación que separa usuarios web de usuarios del sistema.

**Historial**: Registro cronológico de todos los accesos de una persona o del sistema.

**Incidencia**: Evento anormal o importante registrado durante un acceso.

**Jornada**: Periodo de tiempo (Mañana, Tarde, Noche, Mixta) asociado a una persona.

**Perfil Personal**: Área donde una persona ve y gestiona su información.

**Permisos**: Autorizaciones específicas para realizar acciones en el sistema.

**Persona**: Usuario registrado en el sistema (empleado, visitante, estudiante, etc.).

**Portal Personal**: Área privada de cada persona después de iniciar sesión.

**Portátil**: Equipo informático portátil (laptop, tablet) registrado en el sistema.

**Programa de Formación**: Curso, carrera o programa asociado a estudiantes.

**QR Personal**: Código QR único de identificación de cada persona.

**QR de Portátil**: Código QR único adherido a cada equipo portátil.

**Registro**: Acción de ingresar datos de una persona, vehículo o portátil al sistema.

**Reporte**: Documento (generalmente PDF) con información exportada del sistema.

**Rol**: Tipo de usuario del sistema (Administrador, Celador).

**Salida**: Registro de que una persona abandonó la institución.

**Serial**: Número de serie único de un equipo portátil.

**Sistema**: Conjunto de módulos y funcionalidades de CTAccess.

**Tipo de Persona**: Categoría (Empleado, Visitante, Estudiante, Contratista, Otro).

**Usuario del Sistema**: Persona con acceso al panel administrativo (admin o celador).

**Vehículo**: Automóvil, motocicleta, bicicleta u otro medio de transporte registrado.

**Verificación QR**: Módulo para escanear códigos QR y registrar accesos rápidamente.

---

## Anexos

### A. Atajos de Teclado

| Acción | Atajo |
|--------|-------|
| Refrescar página | `F5` o `Ctrl + R` |
| Buscar en página | `Ctrl + F` |
| Cerrar sesión rápido | `Alt + L` (si disponible) |
| Ir al inicio | `Alt + H` (si disponible) |

### B. Formatos de Datos

**Documento**:
- Solo números
- Sin puntos ni guiones
- Ej: `1012345678`

**Placa de Vehículo**:
- Formato estándar colombiano: `ABC123`
- 3 letras + 3 números
- Sin guiones ni espacios

**Serial de Portátil**:
- Alfanumérico
- Generalmente formato del fabricante
- Ej: `SN123456789`, `ABCD1234`

**Email**:
- Formato estándar: `usuario@dominio.com`
- Válido y activo

**Teléfono**:
- 10 dígitos para celular: `3001234567`
- 7 dígitos para fijo: `6012345`

### C. Navegadores Recomendados

| Navegador | Versión Mínima | Recomendado |
|-----------|----------------|-------------|
| Google Chrome | 90+ | ✅ Sí |
| Mozilla Firefox | 88+ | ✅ Sí |
| Microsoft Edge | 90+ | ✅ Sí |
| Safari | 14+ | ⚠️ Limitado |
| Opera | 76+ | ✅ Sí |

### D. Requisitos de Contraseña

- Mínimo 8 caracteres
- Al menos una letra mayúscula
- Al menos una letra minúscula
- Al menos un número
- Caracteres especiales recomendados (@, #, $, %, etc.)

### E. Política de Privacidad

Los datos registrados en CTAccess son de uso exclusivo de la institución para:
- Control de acceso
- Seguridad
- Auditoría
- Estadísticas institucionales

Sus datos **no serán compartidos** con terceros sin su consentimiento explícito.

---

## Información de Contacto

**Sistema**: CTAccess - Control de Acceso  
**Versión del Manual**: 1.0  
**Fecha**: Octubre 2025

**Soporte Técnico**:  
Contacte al administrador del sistema de su institución.

**Actualizaciones**:  
Este manual se actualiza periódicamente. Consulte la última versión en el sistema.

---

<div align="center">

**© 2025 CTAccess - Todos los derechos reservados**

*Este manual ha sido diseñado para facilitar el uso del sistema.*  
*Para consultas específicas, contacte al administrador de su institución.*

</div>
