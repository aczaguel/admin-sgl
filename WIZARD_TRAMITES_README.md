# Módulo Wizard de Trámites - Admin SGL

## 📋 Resumen

Módulo moderno y profesional para la creación de trámites mediante un wizard de 4 pasos, completamente independiente del sistema existente con GroceryCrud.

## ✨ Características Principales

### 1. **Wizard Interactivo de 4 Pasos**
- **Paso 1: Datos del Vehículo** - Folio auto-generado, contrato, unidad, serie, placas
- **Paso 2: Tipo y Ubicación** - Tipo de trámite, entidad, municipio
- **Paso 3: Cliente y Asignación** - Cliente directo, ejecutivo, empresa gestora, gestor
- **Paso 4: Documentos y Confirmación** - Subida de archivos + resumen completo

### 2. **Funcionalidades Avanzadas**
✅ Validación en tiempo real  
✅ Guardado automático de borrador cada 30 segundos  
✅ Drag & drop para subir archivos  
✅ Preview de archivos antes de subir  
✅ Selectores dependientes (entidad→municipio, cliente→ejecutivo, empresa→gestor)  
✅ Barra de progreso visual  
✅ Animaciones suaves entre pasos  
✅ Responsive design (móvil, tablet, desktop)

### 3. **Exportación de Datos**
- **Excel**: Listado completo con filtros aplicados
- **Formato profesional** con encabezados en color y columnas auto-ajustadas

### 4. **Multi-Tenancy Integrado**
- Usuarios admin ven todos los trámites
- Usuarios regulares solo ven trámites de sus clientes asignados
- Validación de permisos en creación y listado

### 5. **Gestión de Archivos**
- Subida múltiple de documentos
- Límite 10MB por archivo
- Formatos soportados: PDF, JPG, PNG
- Almacenamiento organizado por trámite

## 📁 Archivos Creados

### Controlador
```
app/Controllers/Deskapp/TramiteWizard.php
```
**Métodos principales:**
- `index()` - Vista del wizard
- `listado()` - Listado de trámites
- `guardar()` - Crear trámite completo
- `guardar_borrador()` - Auto-save
- `recuperar_borrador()` - Cargar borrador guardado
- `exportar_excel()` - Exportar a Excel
- `get_municipios()` - API AJAX
- `get_ejecutivos_cliente()` - API AJAX
- `get_gestores()` - API AJAX

### Vistas
```
app/Views/deskapp/tramite_wizard/index.php    (Wizard principal)
app/Views/deskapp/tramite_wizard/listado.php  (Listado con filtros)
```

### Base de Datos
```
create_tramite_borrador_table.sql
```
Tabla para guardar borradores:
- `id`, `user_id`, `datos` (JSON), `paso_actual`, `created_at`, `updated_at`

### Configuración
- **Routes.php** - 8 rutas nuevas
- **_sidebar.php** - Nuevo menú "Wizard Trámites"

## 🚀 Instalación

### 1. Crear tabla de borradores
```bash
cd /Users/miguelangel/Sites/admin-sgl
mysql -u [usuario] -p [base_de_datos] < create_tramite_borrador_table.sql
```

### 2. Instalar PhpSpreadsheet (para exportar Excel)
```bash
composer require phpoffice/phpspreadsheet
```

### 3. Verificar permisos de escritura
```bash
chmod -R 777 writable/uploads/tramites/
```

## 🎯 Acceso al Módulo

### Desde el menú lateral:
**Wizard Trámites** → Opciones:
- **Crear Nuevo Trámite** → `/deskapp/tramitewizard`
- **Listado de Trámites** → `/deskapp/tramitewizard/listado`

### URLs directas:
- Wizard: `http://tu-dominio/deskapp/tramitewizard`
- Listado: `http://tu-dominio/deskapp/tramitewizard/listado`
- Exportar Excel: `http://tu-dominio/deskapp/tramitewizard/exportar_excel`

## 📊 Uso del Sistema

### Crear un Trámite

1. **Paso 1 - Datos del Vehículo**
   - El folio se sugiere automáticamente (formato: TR-2026-000001)
   - Completar contrato, unidad, serie, placas
   - Agregar observaciones si es necesario
   - Click "Siguiente"

2. **Paso 2 - Tipo y Ubicación**
   - Seleccionar tipo de trámite
   - Seleccionar entidad → Se cargan los municipios automáticamente
   - Seleccionar municipio
   - Click "Siguiente"

3. **Paso 3 - Cliente y Asignación**
   - Seleccionar cliente directo (filtrado por permisos)
   - Seleccionar ejecutivo → Se cargan los ejecutivos del cliente
   - Seleccionar empresa gestora → Se cargan los gestores
   - Seleccionar gestor
   - Opcionalmente asignar usuario responsable
   - Click "Siguiente"

4. **Paso 4 - Documentos y Confirmación**
   - Arrastrar archivos o hacer click para seleccionar
   - Revisar el resumen completo del trámite
   - Click "Crear Trámite"

### Guardado Automático
- El sistema guarda un borrador cada 30 segundos
- Al regresar, se ofrece recuperar el borrador guardado
- Solo se mantiene un borrador por usuario

### Exportar a Excel
1. Ir al listado de trámites
2. Aplicar filtros deseados (fecha inicio, fecha fin, status)
3. Click en "Exportar a Excel"
4. Se descargará un archivo .xlsx con todos los trámites filtrados

## 🔧 Funciones Técnicas

### Validación de Datos
```php
// Campos requeridos
- folio, contrato, serie
- tra_tipos_id, ent_municipio_id
- cli_directo_id, empresa_gestora_id, gestor_id

// Validación multi-tenancy
- Se valida que el usuario tenga acceso al cliente seleccionado
```

### Auto-Save
```javascript
// Guardado automático cada 30 segundos
setInterval(() => {
    WizardManager.saveDraft();
}, 30000);
```

### Selectores Dependientes
```javascript
// Entidad → Municipios
loadMunicipios(entidadId)

// Cliente → Ejecutivos
loadEjecutivos(clienteId)

// Empresa Gestora → Gestores
loadGestores(empresaId)
```

## 🎨 Diseño UI/UX

### Colores de Estado
- **Verde** (#28a745) - Trámites recientes (< 5 días local, < 10 días foráneo)
- **Amarillo** (#ffc107) - En progreso (5-7 días local, 10-12 días foráneo)
- **Rojo** (#dc3545) - Urgentes (8-11 días local, 13-15 días foráneo)
- **Violeta** (#6f42c1) - Muy urgentes (≥12 días local, ≥16 días foráneo)
- **Azul** (#007bff) - Concluidos
- **Gris** (#6c757d) - Cancelados

### Animaciones
- Transición suave entre pasos (fadeIn 0.3s)
- Indicador de guardado automático (slideInRight)
- Hover effects en botones y archivos
- Loading overlay durante el guardado

## 📈 Estadísticas en Listado

El listado muestra 4 tarjetas con métricas:
1. **Total Trámites** - Contador total
2. **En Proceso** - Trámites activos
3. **Concluidos** - Trámites finalizados
4. **Urgentes** - Trámites con más de 10 días

## 🔐 Seguridad

### Multi-Tenancy
- Filtrado automático por `cliente_user`
- Usuarios admin ven todos los trámites
- Usuarios regulares solo ven sus clientes asignados
- Validación en backend y frontend

### Validación de Archivos
- Tamaño máximo: 10MB por archivo
- Formatos permitidos: PDF, JPG, PNG
- Validación de tipo MIME
- Almacenamiento seguro en `writable/uploads/tramites/{id}/`

### Autenticación
- Todas las rutas requieren filtro `'auth'`
- Verificación de sesión en cada método
- Redirección automática a login si no autenticado

## 🐛 Solución de Problemas

### Error: "Tabla tramite_borrador no existe"
```bash
mysql -u [usuario] -p [db] < create_tramite_borrador_table.sql
```

### Error: "Class 'PhpOffice\PhpSpreadsheet\Spreadsheet' not found"
```bash
composer require phpoffice/phpspreadsheet
```

### Error: "No se pueden guardar archivos"
```bash
chmod -R 777 writable/uploads/
mkdir -p writable/uploads/tramites/
```

### Los municipios no cargan
- Verificar que la tabla `rel_ent_municipio` tenga datos
- Verificar consola del navegador para errores AJAX
- Verificar que el campo `ent_state_id` coincida con `entidad.id`

## 📝 Diferencias con el Sistema Antiguo

| Característica | Sistema Antiguo (GroceryCrud) | Wizard Moderno |
|---|---|---|
| **Interfaz** | Tablas con modales | Wizard paso a paso |
| **UX** | Confuso para usuarios | Intuitivo y guiado |
| **Validación** | Al enviar formulario | En tiempo real |
| **Guardado** | Manual | Auto-save cada 30s |
| **Archivos** | Subida individual | Drag & drop múltiple |
| **Dependientes** | Recarga de página | AJAX en tiempo real |
| **Exportación** | Básica | Excel profesional |
| **Responsive** | Limitado | Totalmente responsive |
| **Intrusivo** | Modifica existente | 100% independiente |

## 🎯 Próximas Mejoras Sugeridas

1. **Exportación PDF** - Generar PDF individual del trámite
2. **Historial de cambios** - Bitácora detallada de modificaciones
3. **Notificaciones** - Alertas cuando se crea/modifica un trámite
4. **Dashboard del wizard** - Estadísticas específicas de trámites creados con wizard
5. **Templates de trámites** - Guardar configuraciones frecuentes
6. **Validación avanzada** - Verificar duplicados por serie/contrato
7. **Firma electrónica** - Aprobar trámites con firma digital
8. **Chat interno** - Comentarios en el trámite
9. **Adjuntar desde cámara** - Foto directa desde móvil
10. **Exportar a otros formatos** - CSV, JSON, XML

## 📞 Soporte

Para cualquier duda o problema con el módulo:
- Revisar este archivo de documentación
- Verificar los logs en `writable/logs/`
- Consultar la consola del navegador para errores JavaScript
- Verificar permisos de usuario en la base de datos

---

**Versión**: 1.0.0  
**Fecha**: Febrero 2026  
**Autor**: GitHub Copilot  
**Framework**: CodeIgniter 4
