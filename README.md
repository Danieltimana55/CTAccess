# CTAccess - Sistema de Control de Acceso

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.2-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

Sistema integral de gestión y control de acceso para instituciones, con soporte para registro de personas, vehículos, portátiles y verificación mediante códigos QR.

[Características](#-características) • [Tecnologías](#-tecnologías) • [Instalación](#-instalación) • [Documentación](#-documentación-de-la-api) • [Arquitectura](#-arquitectura)

</div>

---

## Descripción

**CTAccess** es una aplicación web full-stack diseñada para gestionar el control de acceso en instituciones. Permite:

- **Registro y gestión de personas** (empleados, visitantes, estudiantes, etc.)
- **Control de vehículos** asociados a cada persona
- **Gestión de portátiles** con códigos QR únicos
- **Dashboard en tiempo real** con WebSockets (Laravel Reverb)
- **Sistema de autenticación multi-guard** (personas y usuarios del sistema)
- **Verificación por QR** para entrada y salida
- **Analytics y reportes** de accesos
- **Roles y permisos** (Administrador y Celador)

## Características

### Sistema de Autenticación Dual
- **Guard Web**: Para personas registradas (empleados, visitantes)
- **Guard System**: Para usuarios del sistema (administradores, celadores)
- Autenticación basada en Laravel Breeze + Inertia.js
- Sistema RBAC (Role-Based Access Control) completo

### Dashboard Analítico
- Gráficos en tiempo real con Chart.js
- Visualización de accesos por hora
- Comparativas de entradas/salidas
- Tendencias mensuales
- Actualizaciones en vivo mediante WebSockets

### Gestión de Accesos
- Registro de entrada/salida con timestamp
- Asociación de portátiles y vehículos por acceso
- Estados: `activo`, `finalizado`, `incidencia`
- Historial completo de accesos
- Verificación mediante escaneo de QR

### Gestión de Personas
- CRUD completo con API RESTful
- Validaciones robustas (documentos únicos)
- Asociación múltiple de portátiles y vehículos
- Generación automática de códigos QR
- Sistema de perfiles personales

### Panel de Administración
- Gestión de usuarios del sistema
- Control de permisos granular
- Administración de personas, portátiles y vehículos
- Reportes y estadísticas
- Gestión de incidencias con prioridades

### Panel de Celador
- Registro rápido de accesos
- Verificación QR en tiempo real
- Vista de accesos activos
- Historial del día
- Gestión de incidencias

## Tecnologías

### Backend
- **Laravel 12.0** - Framework PHP
- **PHP 8.2** - Lenguaje de programación
- **MySQL** - Base de datos
- **Laravel Sanctum** - Autenticación API
- **Laravel Reverb** - WebSockets en tiempo real
- **DomPDF** - Generación de reportes PDF

### Frontend
- **Vue.js 3.5** - Framework JavaScript
- **Inertia.js 2.0** - SPA sin API
- **Tailwind CSS 3.2** - Framework CSS
- **Vite** - Build tool
- **Chart.js** - Gráficos y visualizaciones
- **Pinia** - State management
- **Lucide Icons** - Iconografía
- **vue-qrcode-reader** - Escaneo de QR

### DevOps
- **Vite PWA Plugin** - Progressive Web App
- **Concurrently** - Desarrollo multi-proceso
- **Laravel Pail** - Logs en tiempo real

## Instalación

### Requisitos Previos
```bash
PHP >= 8.2
Composer
Node.js >= 18
MySQL >= 8.0
```

### Clonar el repositorio
```bash
git clone https://github.com/Danieltimana55/CTAccess.git
cd CTAccess
```

### Instalar dependencias
```bash
# Backend
composer install

# Frontend
npm install
```

### Configurar entorno
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### Configurar base de datos
Editar `.env` con tus credenciales:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ctaccess
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### Migrar base de datos
```bash
php artisan migrate --seed
```

### Iniciar desarrollo
```bash
# Opción 1: Comando integrado (servidor + queue + logs + vite)
composer dev

# Opción 2: Comandos separados
php artisan serve
php artisan queue:listen
npm run dev
```

### Acceder a la aplicación
```
http://localhost:8000
```

## Arquitectura

### Estructura del Proyecto
```
CTAccess/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                    # Autenticación personas
│   │   │   ├── System/                  # Controladores del sistema
│   │   │   │   ├── Admin/               # Panel administrador
│   │   │   │   ├── Celador/             # Panel celador
│   │   │   │   └── Auth/                # Autenticación sistema
│   │   │   └── Personas/                # Controladores personas
│   │   ├── Middleware/                  # Middlewares personalizados
│   │   ├── Requests/                    # Form requests
│   │   └── Resources/                   # API Resources
│   ├── Models/                          # Modelos Eloquent
│   ├── Services/                        # Lógica de negocio
│   ├── Policies/                        # Políticas de autorización
│   ├── Events/                          # Eventos
│   └── Mail/                            # Mailables
├── database/
│   ├── migrations/                      # Migraciones
│   ├── seeders/                         # Seeders
│   └── factories/                       # Factories
├── resources/
│   ├── js/
│   │   ├── Components/                  # Componentes Vue
│   │   ├── Layouts/                     # Layouts
│   │   ├── Pages/                       # Páginas Inertia
│   │   └── composables/                 # Composables Vue
│   ├── css/                             # Estilos
│   └── views/                           # Vistas Blade
└── routes/
    ├── web.php                          # Rutas web
    ├── api.php                          # Rutas API
    ├── auth.php                         # Rutas autenticación
    └── channels.php                     # Canales WebSocket
```

### Modelos Principales
- **Persona** - Entidad principal (empleados, visitantes, etc.)
- **Acceso** - Registro de entradas/salidas
- **Portatil** - Equipos portátiles con QR
- **Vehiculo** - Vehículos asociados
- **UsuarioSistema** - Usuarios administrativos
- **Incidencia** - Registro de incidencias
- **Role/Permission** - Sistema RBAC

## Documentación de la API

### Endpoints Principales

#### Personas
```http
GET    /api/v1/personas              # Listar (paginado)
GET    /api/v1/personas/{id}         # Ver detalle
POST   /api/v1/personas              # Crear
PUT    /api/v1/personas/{id}         # Actualizar
DELETE /api/v1/personas/{id}         # Eliminar
```

#### Accesos
```http
GET    /api/accesos/recientes        # Últimos 10 accesos
GET    /api/analytics/charts         # Datos para gráficos
```

### Validaciones Clave
- **personas.documento**: Único (puede ser null)
- **portatiles.qrCode**: Único, requerido
- **vehiculos.placa**: Único, requerido
- **personas.Nombre**: Requerido en creación
- **personas.TipoPersona**: Requerido en creación

### Ejemplo: Listar Personas con Relaciones
```http
GET /api/v1/personas?with_relations=1&per_page=15
```

**Respuesta:**
```json
{
  "data": [
    {
      "id": 1,
      "documento": "12345678",
      "nombre": "Juan Pérez",
      "tipoPersona": "Empleado",
      "foto": null,
      "createdAt": "2025-09-15T10:23:45.000000Z",
      "updatedAt": "2025-09-15T10:23:45.000000Z",
      "portatiles": [
        {
          "id": 5,
          "qrCode": "QR-ABC-001",
          "marca": "Dell",
          "modelo": "Latitude 7420",
          "serial": "SN123456"
        }
      ],
      "vehiculos": [
        {
          "id": 10,
          "tipo": "Auto",
          "placa": "ABC-123",
          "modelo": "Toyota Corolla"
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

### Ejemplo: Crear Persona con Relaciones
```http
POST /api/v1/personas
Content-Type: application/json

{
  "documento": "12345678",
  "Nombre": "Juan Pérez",
  "TipoPersona": "Empleado",
  "correo": "juan.perez@example.com",
  "portatiles": [
    {
      "qrCode": "QR-ABC-001",
      "marca": "Dell",
      "modelo": "Latitude 7420",
      "serial": "SN123456"
    }
  ],
  "vehiculos": [
    {
      "tipo": "Auto",
      "placa": "ABC-123",
      "modelo": "Toyota Corolla"
    }
  ]
}
```

### Ejemplo: Actualizar Persona (Parcial)
```http
PUT /api/v1/personas/1
Content-Type: application/json

{
  "Nombre": "Juan P. Actualizado",
  "portatiles": [
    {
      "id": 5,
      "marca": "Lenovo",
      "modelo": "T14 Gen 3"
    }
  ]
}
```

## Guías de Desarrollo

### Integración con Vue 3

#### Instalación de Axios
```bash
npm install axios
```

#### Componente de Lista de Personas
```vue
<template>
  <div class="p-6">
    <h1 class="text-3xl font-bold mb-6">Personas</h1>
    
    <div class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documento</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Portátiles</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehículos</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="persona in personas" :key="persona.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ persona.id }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ persona.documento ?? '—' }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ persona.nombre }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                {{ persona.tipoPersona }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">{{ persona.portatiles?.length ?? 0 }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ persona.vehiculos?.length ?? 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'

const personas = ref([])

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/v1/personas', {
      params: { 
        with_relations: 1, 
        per_page: 20 
      }
    })
    personas.value = data.data
  } catch (error) {
    console.error('Error al cargar personas:', error)
  }
})
</script>
```

#### Formulario de Creación
```vue
<template>
  <form @submit.prevent="crearPersona" class="space-y-4 p-6 bg-white rounded-lg shadow">
    <div>
      <label class="block text-sm font-medium text-gray-700">Documento</label>
      <input 
        v-model="form.documento" 
        type="text" 
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
      >
    </div>
    
    <div>
      <label class="block text-sm font-medium text-gray-700">Nombre</label>
      <input 
        v-model="form.Nombre" 
        type="text" 
        required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
      >
    </div>
    
    <div>
      <label class="block text-sm font-medium text-gray-700">Tipo de Persona</label>
      <select 
        v-model="form.TipoPersona" 
        required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
      >
        <option value="Empleado">Empleado</option>
        <option value="Visitante">Visitante</option>
        <option value="Estudiante">Estudiante</option>
        <option value="Proveedor">Proveedor</option>
      </select>
    </div>

    <button 
      type="submit" 
      class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700"
    >
      Crear Persona
    </button>
  </form>
</template>

<script setup>
import axios from 'axios'
import { reactive } from 'vue'

const form = reactive({
  documento: '',
  Nombre: '',
  TipoPersona: 'Empleado',
  correo: '',
  portatiles: [],
  vehiculos: []
})

async function crearPersona() {
  try {
    const { data } = await axios.post('/api/v1/personas', form)
    console.log('Persona creada:', data)
    // Resetear formulario o redirigir
  } catch (error) {
    console.error('Error de validación:', error.response?.data)
  }
}
</script>
```

### WebSocket en Tiempo Real

El proyecto utiliza Laravel Reverb para actualizaciones en tiempo real.

#### Configuración del Cliente
```javascript
// resources/js/echo.js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
})
```

#### Escuchar Eventos
```vue
<script setup>
import { onMounted, ref } from 'vue'

const accesos = ref([])

onMounted(() => {
  // Escuchar nuevos accesos
  window.Echo.channel('accesos')
    .listen('AccesoRegistrado', (e) => {
      accesos.value.unshift(e.acceso)
    })
})
</script>
```

## Sistema de Roles y Permisos

### Roles Disponibles
- **Administrador**: Acceso completo al sistema
- **Celador**: Registro de accesos y verificación QR

### Guards de Autenticación
- **web**: Para personas registradas
- **system**: Para usuarios administrativos

### Middleware Personalizado
```php
// app/Http/Middleware/CheckSystemRole.php
Route::middleware('check.system.role:administrador')->group(function () {
    // Rutas solo para administradores
});
```

## Analytics y Reportes

### Gráficos Disponibles
1. **Accesos por Hora**: Distribución de accesos en 24 horas
2. **Comparativa Semanal**: Entradas vs salidas últimos 7 días
3. **Estado Actual**: Personas dentro/fuera en tiempo real
4. **Tendencia Mensual**: Accesos diarios del mes

### Endpoint de Analytics
```http
GET /api/analytics/charts
```

**Respuesta:**
```json
{
  "accesosPorHora": [5, 12, 18, 25, ...],
  "ultimosSieteDias": {
    "entradas": [45, 52, 48, 60, ...],
    "salidas": [43, 50, 47, 58, ...],
    "labels": ["Lun 14", "Mar 15", ...]
  },
  "estadoHoy": {
    "entradas": 85,
    "salidas": 80,
    "dentro": 5
  },
  "tendenciaMes": {
    "dias": ["1", "2", "3", ...],
    "accesos": [45, 52, 48, ...]
  }
}
```

## Testing

```bash
# Ejecutar todas las pruebas
composer test

# Pruebas específicas
php artisan test --filter PersonaTest
```

## Comandos Artisan Útiles

```bash
# Limpiar caché
php artisan optimize:clear

# Ejecutar migraciones
php artisan migrate

# Rollback migraciones
php artisan migrate:rollback

# Ejecutar seeders
php artisan db:seed

# Cola de trabajos
php artisan queue:work

# Logs en tiempo real
php artisan pail

# Generar recursos
php artisan make:controller NombreController
php artisan make:model Nombre -mf
php artisan make:request NombreRequest
```

## Deployment

### Railway (Configurado)
El proyecto incluye configuración para Railway:
- `Procfile` - Procesos de inicio
- `railway-start.sh` - Script de inicio

### Variables de Entorno Importantes
```env
APP_NAME=CTAccess
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

MAIL_MAILER=smtp
# Configuración de correo...

REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
```

## Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Autores

- **Daniel Timana** - [@Danieltimana55](https://github.com/Danieltimana55)

## Agradecimientos

- Laravel Framework
- Vue.js Community
- Inertia.js Team
- Tailwind CSS

---

<div align="center">
  <p>Hecho con ❤️ para mejorar el control de acceso institucional</p>
  <p>
    <a href="https://github.com/Danieltimana55/CTAccess">GitHub</a> •
    <a href="https://github.com/Danieltimana55/CTAccess/issues">Reportar Bug</a> •
    <a href="https://github.com/Danieltimana55/CTAccess/issues">Solicitar Feature</a>
  </p>
</div>

## Formato de respuesta JSON

Las respuestas usan API Resources para un contrato estable:

- Persona:
```json
{
  "id": 1,
  "documento": "12345678",
  "nombre": "Juan Pérez",
  "tipoPersona": "Empleado",
  "foto": "https://.../juan.jpg",
  "createdAt": "2025-09-15T01:23:45.000000Z",
  "updatedAt": "2025-09-15T01:23:45.000000Z",
  "portatiles": [ { "id": 5, "qrCode": "QR-ABC-001", "marca": "Dell", "modelo": "Latitude 7420" } ],
  "vehiculos": [ { "id": 10, "tipo": "Auto", "placa": "ABC-123" } ]
}
```

Las colecciones (index) incluyen paginación estándar de Laravel y `status: "success"` en `additional`.

## Ejemplos de payloads para el frontend

- Crear persona con relaciones:
```json
{
  "documento": "12345678",
  "nombre": "Juan Pérez",
  "tipoPersona": "Empleado",
  "foto": "https://mi-cdn/fotos/juan.jpg",
  "portatiles": [
    { "qrCode": "QR-ABC-001", "marca": "Dell", "modelo": "Latitude 7420" },
    { "qrCode": "QR-XYZ-002", "marca": "HP", "modelo": "ProBook 450" }
  ],
  "vehiculos": [
    { "tipo": "Auto", "placa": "ABC-123" },
    { "tipo": "Moto", "placa": "XYZ-987" }
  ]
}
```

- Actualizar persona (parcial) con alta/edición de relaciones:
```json
{
  "nombre": "Juan P. Actualizado",
  "portatiles": [
    { "id": 5, "marca": "Lenovo", "modelo": "T14 Gen 3" },
    { "qrCode": "QR-NEW-003", "marca": "Apple", "modelo": "MacBook Air M2" }
  ],
  "vehiculos": [
    { "id": 10, "placa": "DEF-456" },
    { "tipo": "Camioneta", "placa": "GHI-789" }
  ]
}
```

## Integración rápida con Vue 3 + Tailwind

A continuación ejemplos simples usando Axios.

### Instalar Axios (si no lo tienes)

```bash
npm i axios
```

### Listar personas en una vista (tabla Tailwind)

```vue
<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Personas</h1>
    <table class="min-w-full bg-white shadow rounded">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="p-3">ID</th>
          <th class="p-3">Documento</th>
          <th class="p-3">Nombre</th>
          <th class="p-3">Tipo</th>
          <th class="p-3">Portátiles</th>
          <th class="p-3">Vehículos</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in personas" :key="p.id" class="border-t">
          <td class="p-3">{{ p.id }}</td>
          <td class="p-3">{{ p.documento ?? '—' }}</td>
          <td class="p-3">{{ p.nombre }}</td>
          <td class="p-3">{{ p.tipoPersona }}</td>
          <td class="p-3">{{ p.portatiles?.length ?? 0 }}</td>
          <td class="p-3">{{ p.vehiculos?.length ?? 0 }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'

const personas = ref([])

onMounted(async () => {
  const { data } = await axios.get('/api/v1/personas', {
    params: { with_relations: 1, per_page: 20 }
  })
  // data.data contiene el array paginado
  personas.value = data.data
})
</script>
```

### Crear persona desde un formulario simple

```vue
<script setup>
import axios from 'axios'
import { reactive } from 'vue'

const form = reactive({
  documento: '',
  nombre: '',
  tipoPersona: '',
  foto: '',
  portatiles: [],
  vehiculos: []
})

async function crearPersona() {
  try {
    const { data } = await axios.post('/api/v1/personas', form)
    // Manejar éxito (toasts, redirección, etc.)
    console.log('Creado', data)
  } catch (e) {
    // Manejar errores de validación (422)
    console.error(e?.response?.data)
  }
}
</script>
```

### Ver detalle de persona

```js
import axios from 'axios'

async function obtenerPersona(idPersona) {
  const { data } = await axios.get(`/api/v1/personas/${idPersona}`)
  return data
}
```

## Puesta en marcha

1. Ejecutar migraciones (aplican índices únicos):
   ```bash
   php artisan migrate
   ```
2. Servir la aplicación:
   ```bash
   php artisan serve
   ```
3. Consumir endpoints desde el frontend en `http://localhost:8000/api/v1/...`

## Notas

- Las relaciones se gestionan desde `PersonaService` con transacciones para mantener consistencia.
- El modelo `Persona` usa `idPersona` como clave primaria y para el route model binding.
- Si necesitas CRUD separado para `portátiles` o `vehículos`, se puede añadir fácilmente siguiendo el mismo patrón (Controller + Requests + Resources).
