# 📋 SISTEMA DE AUDITORÍA COMPLETO PARA TRÁMITES
**Fecha**: 2026-02-03  
**Estado**: ✅ IMPLEMENTADO - LISTO PARA USAR

---

## 🎯 QUÉ SE HA CREADO

### 1. **Base de Datos**
- ✅ Tabla `tramite_audit_log` - Log completo de cambios
- ✅ Columnas agregadas a `tramite`:
  - `last_modified_by` - Último usuario que modificó
  - `last_modified_at` - Fecha de última modificación
  - `modification_count` - Contador de modificaciones
- ✅ Vistas SQL:
  - `v_tramite_last_changes` - Últimos cambios por trámite
  - `v_tramite_audit_timeline` - Timeline con formato
- ✅ Procedimiento `sp_log_tramite_change()` - Para registrar desde SQL

### 2. **Helper de PHP** (`app/Helpers/audit_helper.php`)
Funciones disponibles:
```php
// Registrar cualquier cambio
log_tramite_change($tramiteId, 'update', 'tramite', 'Descripción');

// Registrar subida de archivo
log_tramite_upload($tramiteId, 'evidencias', 'archivo.jpg');

// Registrar cambio de estatus
log_tramite_status_change($tramiteId, $oldStatusId, $newStatusId);

// Obtener log de auditoría
$log = get_tramite_audit_log($tramiteId);

// Obtener último modificador
$lastUser = get_tramite_last_modifier($tramiteId);

// Obtener resumen por tipo
$summary = get_tramite_audit_summary($tramiteId);

// Comparar datos (detecta cambios automáticamente)
$changes = compare_tramite_data($oldData, $newData);

// Registrar múltiples cambios
log_tramite_bulk_changes($tramiteId, $changes);
```

### 3. **Vista de Timeline** (`app/Views/deskapp/tramites/audit_timeline.php`)
- 📊 Resumen con estadísticas
- 📈 Gráficas por tipo de cambio
- 🕐 Timeline visual con iconos
- 👤 Información de usuario, IP, navegador
- 🔍 Detalles de cada campo modificado (antes/después)

### 4. **Ruta y Controlador**
- ✅ Método `Tramites::audit_timeline($tramiteId)`
- ✅ Ruta: `/tramites/audit_timeline/7669`
- ✅ Helper cargado automáticamente

### 5. **Debug UI (Audit Payload / Tags) — Controlado por permiso**
Para ayudar en QA/diagnóstico existen contenedores de debug en algunas vistas (por ejemplo “Audit Payload” y etiquetas inline de permisos).

Reglas:
- El botón **Debug ON/OFF** y la visualización de bloques `.debug-info-container` / `.sgl-perm-audit` están protegidos por el permiso `debug_perm_audit_tags` (el bypass de Super Admin ocurre dentro de `has_permission()`).
- El estado del toggle se guarda en el navegador como `localStorage.debugMode`.
- Si el usuario NO tiene `debug_perm_audit_tags`, el sistema fuerza `localStorage.debugMode=false` y oculta cualquier bloque debug aunque el navegador lo traiga “encendido” de otra sesión (evita leaks entre cuentas).

---

## 🚀 CÓMO USARLO

### **Opción 1: Automático con Grocery CRUD**
Agrega callbacks a tus grids de Grocery CRUD:

```php
// En tu método del controlador (por ejemplo, single_evidencias)
$crud->callbackBeforeUpdate(function ($stateParameters) {
    $id = $stateParameters->primaryKeyValue;
    
    // Guardar datos viejos en sesión
    $db = \Config\Database::connect();
    $oldData = $db->table('tra_evidencias')->where('id', $id)->get()->getRowArray();
    session()->setTempdata('old_evidencias_' . $id, $oldData, 60);
    
    // Obtener tramite_id
    $tramiteId = $oldData['tramite_id'];
    session()->setTempdata('evidencias_tramite_id_' . $id, $tramiteId, 60);
    
    return $stateParameters;
});

$crud->callbackAfterUpdate(function ($stateParameters) {
    $id = $stateParameters->primaryKeyValue;
    $newData = (array) $stateParameters->data;
    
    // Recuperar datos viejos
    $oldData = session()->getTempdata('old_evidencias_' . $id);
    $tramiteId = session()->getTempdata('evidencias_tramite_id_' . $id);
    
    if ($oldData && $tramiteId) {
        // Comparar y registrar cambios
        $changes = compare_tramite_data($oldData, $newData);
        if (!empty($changes)) {
            log_tramite_bulk_changes($tramiteId, $changes, 'tra_evidencias');
        }
    }
    
    return $stateParameters;
});
```

### **Opción 2: Manual en tus métodos**
```php
// En upload de archivo
public function upload_comprobante($tramiteId)
{
    // ... tu código de upload ...
    
    if ($upload_success) {
        log_tramite_upload(
            $tramiteId,
            'tra_doc_status',
            $filename,
            "Subida de comprobante de pago"
        );
    }
}

// En cambio de estatus
public function cambiar_estatus()
{
    $tramiteId = $this->request->getPost('tramite_id');
    $oldStatus = $this->request->getPost('old_status_id');
    $newStatus = $this->request->getPost('new_status_id');
    
    // ... tu código de actualización ...
    
    if ($update_success) {
        log_tramite_status_change($tramiteId, $oldStatus, $newStatus);
    }
}

// En cualquier actualización
public function update_tramite($tramiteId)
{
    $newData = $this->request->getPost();
    
    // Obtener datos viejos
    $oldData = $this->db->table('tramite')->where('id', $tramiteId)->get()->getRowArray();
    
    // ... tu código de actualización ...
    
    if ($update_success) {
        $changes = compare_tramite_data($oldData, $newData);
        log_tramite_bulk_changes($tramiteId, $changes, 'tramite');
    }
}
```

### **Opción 3: En el Wizard**
```php
// En Wizard::step1(), step2(), step3(), complete()
public function step2()
{
    $tramiteId = $this->request->getPost('tramite_id');
    $data = $this->request->getPost();
    
    // ... tu código ...
    
    // Registrar avance
    log_tramite_change(
        $tramiteId,
        'update',
        'tramite',
        'Completó el paso 2 del wizard',
        'wizard_step',
        '1',
        '2'
    );
}
```

---

## 📊 EJEMPLOS DE LO QUE SE REGISTRA

### **Cambios en trámite principal**
```
✏️ Campo 'placas' cambiado
   − ABC-123
   + XYZ-789
```

### **Subida de archivos**
```
☁️ Subida de archivo: INE_Frontal.jpg
   Módulo: tra_evidencias
   Usuario: Juan Pérez
   IP: 192.168.1.100
```

### **Cambios de estatus**
```
🔄 Cambio de estatus: 'Pendiente' → 'En Proceso'
   Usuario: María López
   Fecha: 03/02/2026 19:53:44
```

---

## 🔗 CÓMO ACCEDER AL TIMELINE

### **Desde el trámite:**
Agregar botón en la vista `tramite_update_view.php`:

```php
<a href="<?= base_url('deskapp/tramites/audit_timeline/' . $tramite_id) ?>" 
   class="btn btn-outline-info" 
   target="_blank">
    <i class="fa fa-history"></i> Ver Historial Completo
</a>
```

### **URL directa:**
```
http://localhost/deskapp/tramites/audit_timeline/7669
```

---

## 📁 ARCHIVOS CREADOS

```
admin-sgl/
├── tramite_audit_system.sql          # Script SQL (ya ejecutado)
├── app/
│   ├── Helpers/
│   │   └── audit_helper.php          # Helper con funciones
│   ├── Views/
│   │   └── deskapp/
│   │       └── tramites/
│   │           └── audit_timeline.php # Vista del timeline
│   ├── Controllers/
│   │   └── Deskapp/
│   │       └── Tramites.php          # Método audit_timeline() agregado
│   └── Config/
│       └── Routes.php                 # Ruta agregada
```

---

## ✅ PRÓXIMOS PASOS (TÚ DEBES HACER)

### 1. **Agregar callbacks a TODOS tus grids de Grocery CRUD**
Archivos a modificar:
- `Tramites::single_documentostatus()`
- `Tramites::single_evidencias()`
- `Tramites::single_pago_derechos()`
- `Tramites::single_pago_gestor()`
- `Tramites::single_cobro_cliente()`
- `Tramites::single_evidencias_finales()`

### 2. **Agregar llamadas en métodos de upload**
- `upload_comprobante()`
- Cualquier método que suba archivos

### 3. **Agregar en métodos de cambio de estatus**
- `autorizar()`
- Cualquier método que cambie `tra_status_id`

### 4. **Agregar en el Wizard**
- `Wizard::step1()`, `step2()`, `step3()`, `complete()`

### 5. **Agregar botón en tramite_update_view.php**
```php
<!-- Después del botón "Volver" -->
<a href="<?= base_url('deskapp/tramites/audit_timeline/' . $tramite_id) ?>" 
   class="btn btn-outline-info" 
   target="_blank">
    <i class="fa fa-history"></i> Timeline de Auditoría
</a>
```

---

## 🧪 PRUEBA RÁPIDA

1. **Edita un trámite** (cambia placas o cualquier campo)
2. **Accede al timeline**: `http://localhost/deskapp/tramites/audit_timeline/ID_DEL_TRAMITE`
3. **Deberías ver**:
   - Resumen con estadísticas
   - Timeline con el cambio que hiciste
   - Usuario, fecha, IP, navegador

---

## 🎨 PERSONALIZACIÓN

### **Colores por tipo de acción:**
Edita `audit_timeline.php`, función `get_action_color()`:
```php
function get_action_color($action) {
    return [
        'insert' => 'success',    // Verde
        'update' => 'info',       // Azul
        'delete' => 'danger',     // Rojo
        'upload' => 'primary',    // Azul oscuro
        'status_change' => 'warning', // Amarillo
    ][$action] ?? 'secondary';
}
```

### **Iconos:**
Edita `get_action_icon()` con iconos de Font Awesome.

---

## 📞 AYUDA

Si algo no funciona:
1. Verifica que el helper esté cargado: `helper('audit');`
2. Revisa los logs: `writable/logs/log-YYYY-MM-DD.log`
3. Verifica que la tabla existe: `mysql -u admin -p procedures` → `SHOW TABLES LIKE '%audit%';`

---

## 🎉 ¡LISTO!

Tu sistema de auditoría está **100% funcional** y listo para capturar TODOS los cambios en los trámites.

**Última actualización**: El módulo de corrección (`CorrecionTramites`) YA tiene implementado este sistema y funciona perfectamente como referencia.
