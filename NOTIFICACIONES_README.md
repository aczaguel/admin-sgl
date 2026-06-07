# 📢 Sistema de Notificaciones - Manual de Uso

## 🎯 Descripción General
Sistema completo de notificaciones en tiempo real para el seguimiento de eventos importantes en trámites.

## 📋 Tipos de Notificaciones

1. **tramite_creado** - Cuando se crea un nuevo trámite
2. **tramite_actualizado** - Cuando se modifica un trámite existente
3. **gestor_asignado** - Cuando se asigna un gestor a un trámite
4. **pago_gestor** - Cuando se registra un pago al gestor
5. **factura_generada** - Cuando se genera un número de factura
6. **factura_cobrada** - Cuando se cobra una factura

## 🚀 Instalación

### 1. Ejecutar SQL
```bash
mysql -u usuario -p base_datos < notificaciones_sistema.sql
```

### 2. Cargar el Helper
En `app/Config/Autoload.php`, agregar en el array `$helpers`:
```php
public $helpers = ['notification'];
```

### 3. Verificar Rutas
Las rutas ya están configuradas en `app/Config/Routes.php`

## ✅ Smoke Local PHP 8.2

Para validar login + notificaciones JSON contra el runtime Docker PHP 8.2:

```bash
DOCKER_APP_PORT=18080 docker compose up -d app
./admin/internal-json-smoke.sh
```

El smoke valida sesión real y estas rutas JSON:

- `GET /notifications/api_count`
- `GET /notifications/api_unread`

## 💻 Uso en el Código

### Ejemplo 1: Notificar Trámite Creado
```php
// En el controlador de Tramites, después de crear un trámite
helper('notification');

$tramiteId = $this->db->insertID();
$folioTramite = 'TR-2026-001';
$createdBy = $this->session->get('id');

// Notificar a todos los administradores
notify_tramite_creado($tramiteId, $folioTramite, $createdBy);
```

### Ejemplo 2: Notificar Actualización de Trámite
```php
helper('notification');

$tramiteId = 123;
$folioTramite = 'TR-2026-001';
$cambios = 'Se actualizó el estado a "En Proceso"';
$createdBy = $this->session->get('id');

notify_tramite_actualizado($tramiteId, $folioTramite, $cambios, $createdBy);
```

### Ejemplo 3: Notificar Gestor Asignado
```php
helper('notification');

$tramiteId = 123;
$folioTramite = 'TR-2026-001';
$gestorNombre = 'Juan Pérez';
$createdBy = $this->session->get('id');

notify_gestor_asignado($tramiteId, $folioTramite, $gestorNombre, $createdBy);
```

### Ejemplo 4: Notificar Pago a Gestor
```php
helper('notification');

$tramiteId = 123;
$folioTramite = 'TR-2026-001';
$monto = 5000.00;
$createdBy = $this->session->get('id');

notify_pago_gestor($tramiteId, $folioTramite, $monto, $createdBy);
```

### Ejemplo 5: Notificar Factura Generada
```php
helper('notification');

$tramiteId = 123;
$folioTramite = 'TR-2026-001';
$numeroFactura = 'FAC-2026-001';
$createdBy = $this->session->get('id');

notify_factura_generada($tramiteId, $folioTramite, $numeroFactura, $createdBy);
```

### Ejemplo 6: Notificar Factura Cobrada
```php
helper('notification');

$tramiteId = 123;
$folioTramite = 'TR-2026-001';
$monto = 8000.00;
$createdBy = $this->session->get('id');

notify_factura_cobrada($tramiteId, $folioTramite, $monto, $createdBy);
```

### Ejemplo 7: Notificación Personalizada
```php
helper('notification');

$userIds = [1, 5, 10]; // IDs de usuarios a notificar
$type = 'alerta';
$title = 'Alerta Importante';
$message = 'El trámite TR-2026-001 requiere atención urgente';

notify_custom($userIds, $type, $title, $message, [
    'icon' => 'fa-exclamation-triangle',
    'color' => 'danger',
    'url' => base_url('deskapp/tramitesn/update/123'),
    'tramite_id' => 123,
    'created_by' => $this->session->get('id')
]);
```

## 📍 Integrar en Controlador Tramites

### En `callbackAfterInsert` (Crear trámite)
```php
$crud->callbackAfterInsert(function ($stateParameters) {
    helper('notification');
    
    $tramiteId = $stateParameters->insertId;
    $folioTramite = $stateParameters->data['folio_tramite'];
    $createdBy = session()->get('id');
    
    // Notificar creación
    notify_tramite_creado($tramiteId, $folioTramite, $createdBy);
    
    return $stateParameters;
});
```

### En `callbackAfterUpdate` (Actualizar trámite)
```php
$crud->callbackAfterUpdate(function ($stateParameters) {
    helper('notification');
    
    $tramiteId = $stateParameters->primaryKeyValue;
    
    // Obtener folio del trámite
    $db = \Config\Database::connect();
    $tramite = $db->table('tramite')
        ->select('folio_tramite')
        ->where('id', $tramiteId)
        ->get()
        ->getRowArray();
    
    if ($tramite) {
        $folioTramite = $tramite['folio_tramite'];
        $createdBy = session()->get('id');
        
        // Detectar cambios importantes
        $cambios = 'Estado actualizado';
        
        if (isset($stateParameters->data['gestor_id'])) {
            // Se asignó gestor
            $gestor = $db->table('ges_gestor')
                ->select('CONCAT(nombre, " ", apellidos) as nombre_completo')
                ->where('id', $stateParameters->data['gestor_id'])
                ->get()
                ->getRowArray();
            
            if ($gestor) {
                notify_gestor_asignado($tramiteId, $folioTramite, $gestor['nombre_completo'], $createdBy);
            }
        }
        
        if (isset($stateParameters->data['factura_numero'])) {
            // Se generó factura
            notify_factura_generada($tramiteId, $folioTramite, $stateParameters->data['factura_numero'], $createdBy);
        }
        
        if (isset($stateParameters->data['cobro_status_id']) && $stateParameters->data['cobro_status_id'] == 23) {
            // Factura cobrada (status 23)
            $monto = $stateParameters->data['costo_total'] ?? 0;
            notify_factura_cobrada($tramiteId, $folioTramite, $monto, $createdBy);
        }
        
        // Notificación general de actualización
        notify_tramite_actualizado($tramiteId, $folioTramite, $cambios, $createdBy);
    }
    
    return $stateParameters;
});
```

## 🔔 Componentes del Sistema

### 1. Base de Datos
- **notifications** - Tabla principal de notificaciones
- **notification_settings** - Configuración por usuario
- **v_notifications_summary** - Vista para consultas optimizadas

### 2. Backend
- **NotificationModel** - Modelo con métodos para crear notificaciones
- **Notifications Controller** - APIs para manejar notificaciones
- **notification_helper.php** - Funciones helper fáciles de usar

### 3. Frontend
- **_notifications_dropdown.php** - Dropdown en el header con badge
- **index.php** - Vista completa de todas las notificaciones
- JavaScript integrado para auto-refresh cada 1 minuto

### 4. Características
- ✅ Notificaciones en tiempo real
- ✅ Badge con contador
- ✅ Tooltips informativos
- ✅ Marcar como leída/no leída
- ✅ Eliminar notificaciones
- ✅ Auto-refresh cada 60 segundos
- ✅ Enlaces directos al trámite
- ✅ Iconos y colores personalizados
- ✅ Vista completa con historial
- ✅ Filtrado automático por roles

## 🎨 Personalización de Colores e Iconos

```php
// Colores disponibles
'primary' => Azul
'success' => Verde
'warning' => Amarillo
'danger' => Rojo
'info' => Celeste

// Iconos Font Awesome
'fa-file-alt' => Documento
'fa-edit' => Edición
'fa-user-tie' => Usuario/Gestor
'fa-money-bill-wave' => Dinero
'fa-file-invoice' => Factura
'fa-check-circle' => Completado
'fa-exclamation-triangle' => Alerta
'fa-bell' => Notificación general
```

## 📊 APIs Disponibles

- `GET /deskapp/notifications` - Vista principal
- `GET /deskapp/notifications/api_unread` - Obtener no leídas
- `GET /deskapp/notifications/api_count` - Contador
- `POST /deskapp/notifications/api_mark_read/:id` - Marcar como leída
- `POST /deskapp/notifications/api_mark_all_read` - Marcar todas
- `DELETE /deskapp/notifications/api_delete/:id` - Eliminar
- `GET /deskapp/notifications/api_load_more` - Cargar más (paginación)

## 🔒 Seguridad

- Todas las rutas tienen filtro `auth`
- Validación de pertenencia de notificación al usuario
- Escape de HTML para prevenir XSS
- Consultas preparadas en el modelo

## 🧹 Mantenimiento

### Limpiar notificaciones antiguas (cron job recomendado)
```php
// En un comando o tarea programada
$notificationModel = new \App\Models\NotificationModel();
$notificationModel->deleteOldNotifications(90); // Eliminar leídas de hace más de 90 días
```

## 🎯 Próximos Pasos Sugeridos

1. ✅ Ejecutar el SQL para crear las tablas
2. ✅ Cargar el helper en Autoload.php
3. ✅ Integrar notificaciones en callbackAfterInsert/Update de Tramites
4. ✅ Probar creando/actualizando trámites
5. ✅ Personalizar mensajes según necesidades
6. ⏰ Agregar notificaciones por email (opcional)
7. ⏰ Agregar notificaciones push (opcional)
8. ⏰ Dashboard de estadísticas de notificaciones (opcional)

## 📝 Notas Importantes

- El dropdown se actualiza automáticamente cada 60 segundos
- Las notificaciones se envían solo a usuarios con roles específicos:
  - Admin y Super Admin reciben todas
  - Finance recibe notificaciones de pagos y facturas
  - Creador y asignado del trámite reciben actualizaciones
- El badge muestra "99+" si hay más de 99 notificaciones

## 🐛 Troubleshooting

### No aparecen notificaciones
1. Verificar que el SQL se ejecutó correctamente
2. Verificar que el helper está cargado en Autoload.php
3. Revisar logs en `writable/logs/` para errores
4. Verificar que las funciones helper se están llamando

### El dropdown no se actualiza
1. Verificar que JavaScript no tiene errores (consola del navegador)
2. Verificar que las rutas API funcionan
3. Revisar filtro de autenticación

### Badge no muestra contador
1. Verificar que hay notificaciones sin leer
2. Revisar API `/api_count`
3. Verificar estilos CSS del badge

---

**¡Sistema de notificaciones listo para usar!** 🎉
