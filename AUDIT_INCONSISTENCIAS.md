# Inconsistencias y cosas “raras” detectadas (10 de febrero de 2026)

Este documento resume inconsistencias/rareza detectada durante el hardening de permisos, multi-tenancy y endpoints GroceryCrud (`single_*`) en el módulo Deskapp.

> Nota: varios puntos aquí **no son bugs confirmados**, pero sí patrones que tienden a romper la validación server-side, abrir bypass por API, o generar comportamientos no deterministas.

---

## Hallazgos críticos (P0)

### 1) Uso de `esc()` con roles/permisos para lógica server-side
**Qué se ve:** patrones como `esc($session->get('user_permissions'))` / `esc($session->get('user_roles'))` que luego se pasan a `has_permission()`.

**Por qué es raro/riesgoso:** `esc()` es para *output encoding* (HTML/atributos) y puede:
- transformar arrays en strings,
- alterar contenido,
- romper comparaciones,
- producir denegaciones o (peor) bypass si `has_permission()` termina evaluando algo inesperado.

**Dónde se detecta (ejemplos):**
- `app/Controllers/Deskapp/Tramites.php`
- `app/Controllers/Deskapp/Proceso.php`
- `app/Controllers/Deskapp/TramiteWizard.php`
- `app/Controllers/Deskapp/Customers.php`
- `app/Controllers/Deskapp/Users.php`
- `app/Controllers/Deskapp/Documentos.php`
- `app/Controllers/Deskapp/Concluido.php` (se ve en zonas iniciales)

**Recomendación:**
- No usar `esc()` para roles/permisos.
- Normalizar a arrays siempre:
	- `$roles = $session->get('user_roles') ?? []; if (!is_array($roles)) $roles = [$roles];`
	- `$perms = $session->get('user_permissions') ?? []; if (!is_array($perms)) $perms = [$perms];`

**Acción aplicada (parcial):**
- `app/Controllers/Deskapp/Concluido.php` (`final()`): roles/permisos normalizados (sin `esc()`).
- `app/Controllers/Deskapp/TramiteWizard.php`: removido `esc()` en validaciones de permisos/roles (múltiples endpoints del wizard).
- `app/Controllers/Deskapp/Tramites.php`: removido `esc()` en checks de permisos/roles (listados GroceryCrud y pantallas `update_*` con `is_read_only()`/`has_permission()`).
- `app/Controllers/Deskapp/Proceso.php`: removido `esc()` en checks de permisos/roles (p.ej. `cobro_cliente()` y `final()`).
- `app/Controllers/Deskapp/Users.php`: removido `esc()` de `guardManagementAccess()` para roles/permisos.
- `app/Controllers/Deskapp/Customers.php`: removido `esc()` en roles/permisos y checks de export/print/read.
- `app/Controllers/Deskapp/Documentos.php`: removido `esc()` de `guardDocumentosAccess()` para roles/permisos.
- `app/Views/deskapp/includes/`: removido `esc()` en checks de `has_permission()` (menús/header/sidebar).
- `app/Views/deskapp/extra-pages/tramite_concluido_final.php`: removido `esc()` en checks `has_permission()`.
- `app/Views/deskapp/extra-pages/tramite_finalizados_view.php`: removido `esc()` en checks `has_permission()`.
- `app/Helpers/`: removido `esc()` en helpers de sesión/formularios; se soporta normalización robusta en `has_permission()`.
- `app/Views/deskapp/includes/_sidebar.php.bak`: eliminado (backup no usado que contenía `esc()` en permisos).

---

### 2) Archivo “backup” dentro de Controllers
**Qué se ve:** existía `app/Controllers/Deskapp/Tramites_backup.php` con lógica y rutas tipo `setApiUrlPath()` y referencias con IDs hardcodeados.

**Por qué es raro/riesgoso:** si por configuración/routing/autoload llega a quedar accesible en runtime, se vuelve una **superficie paralela** con reglas distintas (o antiguas) que puede reintroducir bypasses.

**Acción aplicada:** movido a `_archive/Controllers/Deskapp/Tramites_backup.php` para evitar que sea superficie accesible por routing/autoload.

---

## Hallazgos importantes (P1)

### 3) Callbacks duplicados de GroceryCrud (posible sobreescritura)
**Qué se ve:** en algunos métodos hay múltiples llamadas a `callbackAfterInsert/Update/Delete` para el mismo CRUD.

**Por qué es raro/riesgoso:** según implementación de GroceryCrud, el último callback puede **sobrescribir** el anterior, causando:
- pérdida de bitácora (o logs),
- validaciones que “parecen” estar, pero no corren,
- comportamiento inconsistente entre ambientes.

**Ejemplo claro:** `single_evidencias_finales()` en `app/Controllers/Deskapp/Concluido.php` define callbacks de bitácora y luego vuelve a definir callbacks “para registrar el log”.

**Acción aplicada:**
- Consolidado en `single_evidencias_finales()` para que `callbackAfterInsert` y `callbackAfterUpdate` ejecuten **Bitácora + logOperation** en un único callback (evita sobreescritura silenciosa).
- En `app/Controllers/Deskapp/Concluido.php` también se consolidó en:
	- `single_documentostatus()` (consolidación de `callbackAfterUpdate`)
	- `single_evidencias()` (consolidación de `callbackAfterInsert/Update`)
- Mismo hardening aplicado en `app/Controllers/Deskapp/Tramites.php` en:
	- `single_documentostatus()` (consolidación de `callbackAfterUpdate`)
	- `single_evidencias()` (consolidación de `callbackAfterInsert/Update`)
	- `single_evidencias_finales()` (consolidación de `callbackAfterInsert/Update`)

---

### 4) Operaciones de filesystem y permisos amplios (0777)
**Qué se ve:** creación de carpetas con `mkdir(..., 0777, true)` y `chmod(..., 0777)` en módulos de uploads.

**Por qué es raro/riesgoso:**
- 0777 no es necesario en la mayoría de despliegues y aumenta impacto ante cualquier compromiso.
- combinado con nombres de archivo no sanitizados (cuando existe), abre camino a path traversal o borrados no autorizados.

**Recomendación:**
- Usar permisos más restrictivos (p.ej. 0755 / 0775 según grupo) y controlarlo vía config.
- Mantener sanitización estricta (`basename`, bloquear `..` y `\0`).

---

### 5) Carpeta compartida para “evidencias”
**Qué se ve:** múltiples controladores usan `assets/uploads/evidencias/` como destino de `setFieldUploadMultiple()` sin subcarpeta por trámite.

**Por qué es raro/riesgoso:**
- En multi-tenant, mezclar archivos de distintos trámites/clientes en el mismo directorio aumenta riesgo de colisiones y exposición accidental.
- Dificulta limpieza/borrado seguro por trámite.

**Dónde se detecta (ejemplos):**
- `app/Controllers/Deskapp/Tramites.php`
- `app/Controllers/Deskapp/Concluido.php`
- `app/Controllers/Deskapp/Proceso.php`
- `app/Controllers/Deskapp/Cancelado.php`
- `app/Controllers/Deskapp/Customers.php`

**Recomendación:**
- Migrar a estructura por trámite: `assets/uploads/evidencias/{tramite_id}/`.
- Alinear DB + filesystem para que el tenant-bound sea natural.

---

## Hallazgos de consistencia (P2)

### 6) Respuestas inconsistentes (JSON vs string vs body)
**Qué se ve:** mezcla de:
- `return json_encode([...])` (string),
- `$this->response->setJSON([...])`,
- `$this->response->setStatusCode(...)->setBody('Acceso denegado')`.

**Riesgo:** clientes JS/GC pueden comportarse distinto si el content-type/código HTTP no coincide; también complica estandarizar 401/403/400.

**Recomendación:**
- Estándar: para API/AJAX siempre `setStatusCode()->setJSON()`.
- Para HTML: redirects con flash.

---

### 7) Inconsistencia en nombres de permisos
**Qué se ve:** permisos por “sección” (`section_*`) conviven con permisos de acción (`editar_*`, `delete_*`, `menu_*`, `important_*`).

**Riesgo:**
- es fácil bloquear UI pero permitir API,
- o permitir UI pero bloquear API,
- o usar un permiso equivocado en un endpoint crítico.

**Recomendación:**
- Documentar un mapa canónico “acción → permiso” por endpoint.
- Reusar guards centralizados.

---

## Recomendaciones de cierre (siguientes pasos)

1) **Eliminar `esc()` de roles/permisos** en todos los controladores Deskapp.
2) **Sacar `Tramites_backup.php`** fuera de `app/Controllers`.
3) **Consolidar callbacks duplicados** en GroceryCrud (especialmente en evidencias finales).
4) **Normalizar uploads**: carpeta por trámite y permisos de filesystem más restrictivos.
5) **Estandarizar respuestas**: JSON con HTTP codes para API/AJAX, redirect para HTML.

---

## Notas
- Parte del hardening ya aplicado en `Concluido.php` y `Tramites.php` protege callbacks con `unlink()` y valida tenant/permisos antes de mutar o borrar.
- Este documento no reemplaza una auditoría formal; sirve como lista accionable de deuda técnica y riesgos recurrentes.
