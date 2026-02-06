# Módulo de Corrección de Trámites

## Descripción
Módulo administrativo para corregir errores en **Tipo de Trámite** y **Estatus** cuando un ejecutivo se equivoca al registrar o actualizar un trámite.

## Características

### ✅ Funcionalidades
- **Edición controlada**: Solo permite modificar `tra_tipos_id` y `tra_status_id`
- **Campos bloqueados**: Folio, contrato, unidad, serie y placas son de solo lectura
- **Auditoría automática**: Cada cambio se registra con:
  - Fecha y hora
  - Usuario que realizó el cambio
  - Valores anteriores y nuevos
  - Folio del trámite
- **Historial completo**: Vista de últimas 500 correcciones
- **Acceso restringido**: Solo usuarios con rol Admin

### 🔒 Seguridad
- Autenticación requerida (filtro `auth`)
- Validación de rol Admin mediante `is_admin()`
- CSRF protection habilitado
- Registro de auditoría inmutable

## Estructura de Archivos

```
app/
├── Controllers/Deskapp/
│   └── CorrecionTramites.php       # Controlador principal
├── Views/deskapp/correccion_tramites/
│   ├── index.php                    # Vista de listado y edición
│   └── historial.php                # Vista de historial de cambios
└── Config/
    └── Routes.php                   # Rutas configuradas

create_log_table.sql                 # Script de tabla de auditoría
```

## Instalación

### 1. Crear tabla de auditoría
```bash
mysql -u root -p nombre_base_datos < create_log_table.sql
```

O ejecutar manualmente:
```sql
CREATE TABLE IF NOT EXISTS `tramite_correccion_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tramite_id` int(11) NOT NULL,
  `folio` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `cambios` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tramite_id` (`tramite_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Verificar rutas (ya configuradas)
Las siguientes rutas están en [app/Config/Routes.php](app/Config/Routes.php):

```php
$routes->get('/correccion-tramites', 'Deskapp/CorrecionTramites::index',['filter' => 'auth']);
$routes->post('/correccion-tramites', 'Deskapp/CorrecionTramites::index');
$routes->get('/correccion-tramites/historial', 'Deskapp/CorrecionTramites::historial',['filter' => 'auth']);
```

### 3. Menú en sidebar (ya configurado)
Se agregó el enlace en el menú "Dashboard Admin" solo visible para administradores.

## Uso

### Acceso al módulo
1. Iniciar sesión como Admin
2. Ir a **Dashboard Admin → Corrección de Trámites**
3. O acceder directamente: `https://tudominio.com/correccion-tramites`

### Editar un trámite
1. En la tabla, clic en ícono de edición del trámite
2. Modificar solo los campos permitidos:
   - **Tipo de Trámite**: Seleccionar del dropdown
   - **Estatus del Trámite**: Seleccionar del dropdown
3. Guardar cambios
4. El sistema registra automáticamente en el historial

### Ver historial
1. Clic en botón **"Ver Historial de Cambios"**
2. Se muestra tabla con últimas 500 correcciones:
   - Fecha/hora
   - Folio del trámite (enlace al detalle)
   - Usuario
   - Cambios realizados

## Ejemplo de registro en log

```
Fecha: 30/01/2026 14:35
Folio: F-2026-00123
Usuario: admin
Cambios: Tipo de Trámite: 'Alta' → 'Baja' | Estatus: 'En Proceso' → 'Pendiente'
```

## API Endpoints

### Buscar trámites
```
GET /correccion-tramites/buscar?q=F-2026
```
Respuesta JSON con trámites que coincidan.

## Tablas de base de datos involucradas

- `tramite` - Tabla principal de trámites
- `tra_tipos` - Catálogo de tipos de trámite
- `tra_status` - Catálogo de estatus
- `tramite_correccion_log` - Log de correcciones (nueva)

## Notas importantes

⚠️ **Importante**:
- Solo Admin puede acceder
- No se pueden agregar ni eliminar trámites, solo editar
- Los cambios son permanentes e irreversibles
- El historial no se puede modificar (auditoría)

## Soporte

Para problemas o dudas, contactar al equipo de desarrollo.
