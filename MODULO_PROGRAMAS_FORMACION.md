# Módulo de Programas de Formación - Documentación

## 📋 Resumen

Se ha creado un módulo completo de gestión de **Programas de Formación** para el panel de administración del sistema CTAccess, siguiendo el mismo patrón de diseño de los módulos existentes.

## 🎯 Componentes Creados

### 1. **Controlador Backend**
📁 `app/Http/Controllers/System/Admin/ProgramasFormacionController.php`

**Funcionalidades:**
- ✅ `index()` - Renderiza la vista principal
- ✅ `data()` - Obtiene datos paginados con filtros para la tabla
- ✅ `store()` - Crea un nuevo programa de formación
- ✅ `update()` - Actualiza un programa existente
- ✅ `destroy()` - Elimina un programa (solo si no tiene personas asociadas)
- ✅ `toggleActivo()` - Activa/desactiva un programa

**Validaciones incluidas:**
- Nombre requerido (máx 255 caracteres)
- Ficha requerida y única (máx 50 caracteres)
- Fechas de inicio y fin obligatorias
- Fecha fin debe ser posterior a fecha inicio
- Nivel de formación obligatorio (opciones predefinidas)
- Descripción opcional (máx 1000 caracteres)

### 2. **Vista Frontend (Vue.js)**
📁 `resources/js/Pages/System/Admin/ProgramasFormacion/Index.vue`

**Características:**
- ✅ Tabla responsiva con datos paginados
- ✅ Búsqueda en tiempo real (nombre, ficha, nivel)
- ✅ Filtros por estado: Todos, Activos, Inactivos, Vigentes, Finalizados
- ✅ Modal para crear/editar programas
- ✅ Badges de estado visual (Activo, Vigente, Finalizado, Próximamente)
- ✅ Contador de personas asociadas
- ✅ Días restantes para programas vigentes
- ✅ Botones de acción: Activar/Desactivar, Editar, Eliminar
- ✅ Diseño adaptado al tema claro/oscuro
- ✅ Colores corporativos SENA verde

**Campos del formulario:**
- Nombre del programa
- Ficha (código único)
- Nivel de formación (select con opciones)
- Fecha de inicio
- Fecha de finalización
- Descripción (opcional)
- Estado activo/inactivo

### 3. **Rutas del Sistema**
📁 `routes/web.php`

```php
// Gestión de programas de formación
Route::get('programas-formacion', [ProgramasFormacionController::class, 'index'])
    ->name('programas-formacion.index');
Route::get('programas-formacion/data', [ProgramasFormacionController::class, 'data'])
    ->name('programas-formacion.data');
Route::post('programas-formacion', [ProgramasFormacionController::class, 'store'])
    ->name('programas-formacion.store');
Route::put('programas-formacion/{programa}', [ProgramasFormacionController::class, 'update'])
    ->name('programas-formacion.update');
Route::delete('programas-formacion/{programa}', [ProgramasFormacionController::class, 'destroy'])
    ->name('programas-formacion.destroy');
Route::post('programas-formacion/{programa}/toggle', [ProgramasFormacionController::class, 'toggleActivo'])
    ->name('programas-formacion.toggle');
```

### 4. **Menú de Navegación**
📁 `config/menus.php`

Se agregó el enlace al menú del administrador:
```php
[
    'label' => 'Programas de Formación',
    'icon'  => 'heroicon-m-academic-cap',
    'route' => 'system.admin.programas-formacion.index',
]
```

### 5. **Dashboard Actualizado**
📁 `app/Http/Controllers/System/AdminDashboardController.php`
📁 `resources/js/Pages/System/Admin/Dashboard.vue`

Se agregaron **2 nuevas tarjetas** al dashboard:
- 📊 **Programas de Formación** - Total de programas en el sistema
- 🎓 **Programas Vigentes** - Programas activos y en curso

### 6. **Iconos Agregados**
📁 `resources/js/Components/Icon.vue`

Nuevos iconos disponibles:
- `BookOpen` / `book-open` - Para programas de formación
- `GraduationCap` / `graduation-cap` - Para formación académica

## 🗄️ Modelo Existente

El modelo `ProgramaFormacion` ya existía con todas las funcionalidades necesarias:

**Métodos útiles:**
- `estaVigente()` - Verifica si el programa está vigente
- `haFinalizado()` - Verifica si ya finalizó
- `getDuracionMeses()` - Obtiene la duración en meses
- `getDiasRestantes()` - Calcula días restantes

**Scopes:**
- `activos()` - Filtra programas activos
- `vigentes()` - Filtra programas vigentes (activos y dentro de fechas)
- `porFicha($ficha)` - Busca por ficha

**Relaciones:**
- `personas()` - Relación hasMany con Persona

## 🎨 Diseño y UX

### Características de diseño:
- ✅ **Responsive**: Adaptado para móvil, tablet y desktop
- ✅ **Tema dual**: Soporte para modo claro y oscuro
- ✅ **Colores SENA**: Verde corporativo #39A900
- ✅ **Iconografía consistente**: Lucide icons
- ✅ **Animaciones suaves**: Transiciones en hover y click
- ✅ **Feedback visual**: Estados claros con badges de colores
- ✅ **Accesibilidad**: Labels, placeholders y mensajes de error

### Estados visuales:
- 🟢 **Vigente**: Verde - Programa activo y en curso
- 🔴 **Finalizado**: Rojo - Programa terminado
- 🟡 **Próximamente**: Amarillo - Programa futuro
- ⚫ **Inactivo**: Gris - Programa desactivado

## 📊 Funcionalidades Destacadas

### Filtros Inteligentes
1. **Búsqueda en tiempo real**: Busca por nombre, ficha o nivel
2. **Filtro por estado**:
   - Todos
   - Activos (estado activo=true)
   - Inactivos (estado activo=false)
   - Vigentes (activos y en fechas vigentes)
   - Finalizados (fecha fin pasada)

### Validaciones de Seguridad
- ✅ No se puede eliminar un programa con personas asociadas
- ✅ Validación de fechas coherentes (fin > inicio)
- ✅ Fichas únicas en el sistema
- ✅ Campos obligatorios marcados con asterisco

### Información en Tiempo Real
- 📊 Contador de personas asociadas a cada programa
- ⏱️ Días restantes para programas vigentes
- 📅 Duración calculada en meses
- 🎯 Estado actualizado dinámicamente

## 🚀 Uso del Módulo

### Acceso
1. Iniciar sesión como **administrador**
2. Ir al menú lateral → **Programas de Formación**

### Crear un programa
1. Click en botón **"Nuevo programa"**
2. Completar formulario:
   - Nombre (ej: "Tecnología en Desarrollo de Software")
   - Ficha (ej: "2889453")
   - Nivel (seleccionar: Técnico, Tecnólogo, etc.)
   - Fecha inicio y fin
   - Descripción (opcional)
3. Click en **"Crear programa"**

### Editar un programa
1. Click en botón **"Editar"** del programa deseado
2. Modificar campos necesarios
3. Click en **"Actualizar programa"**

### Activar/Desactivar
- Click en icono de ojo para cambiar estado activo/inactivo

### Eliminar
- Click en **"Eliminar"** (solo disponible si no tiene personas asociadas)
- Confirmar la acción

## 🔗 Integración con Otros Módulos

El módulo de Programas de Formación está integrado con:

1. **Módulo de Personas**: 
   - Las personas pueden asociarse a un programa
   - Relación `programa_formacion_id` en tabla personas

2. **Dashboard Admin**:
   - Muestra estadísticas de programas
   - Total y programas vigentes

3. **Sistema de Menús**:
   - Enlace en navegación principal
   - Acceso restringido a rol administrador

## 📱 Responsividad

- **Mobile**: 2 columnas en grid de tarjetas
- **Tablet**: 4 columnas
- **Desktop**: 6 columnas con tarjetas adicionales
- **Tabla**: Scroll horizontal en móvil, completa en desktop

## ✅ Checklist de Implementación

- [x] Controlador backend con CRUD completo
- [x] Vista Vue.js responsive
- [x] Rutas configuradas
- [x] Menú de navegación actualizado
- [x] Dashboard con estadísticas
- [x] Iconos agregados
- [x] Validaciones frontend y backend
- [x] Filtros y búsqueda
- [x] Paginación
- [x] Tema claro/oscuro
- [x] Estados visuales
- [x] Integración con modelo existente

## 🎓 Niveles de Formación Disponibles

1. **Técnico**
2. **Tecnólogo**
3. **Especialización**
4. **Curso Corto**
5. **Diplomado**

## 📝 Notas Importantes

- El modelo `ProgramaFormacion` ya existía con todas las funcionalidades necesarias
- Se reutilizaron componentes y patrones existentes (Modal, Icon, SystemLayout)
- El diseño sigue los estándares de los otros módulos (Usuarios, Portátiles, Vehículos)
- Las rutas están protegidas con middleware `auth:system` y `check.system.role:administrador`

---

**Fecha de Creación**: 20 de Octubre, 2025
**Estado**: ✅ Completado y funcional
