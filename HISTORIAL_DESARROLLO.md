# Historial de Desarrollo — Registro Consolidado

> **Propósito:** Registro único e histórico de los trabajos realizados en el sistema SGL,
> consolidando el contenido de los múltiples archivos `.md` de seguimiento que existían.
> Cada ítem está marcado como **✅ Terminado** o **⏳ Inconcluso / Pendiente**.
>
> Este archivo reemplaza a los trackers dispersos. El detalle técnico vivo de la
> última funcionalidad (layout unificado de trámite) está en:
> `.kiro/specs/tramite-unified-layout/` (`requirements.md`, `design.md`, `tasks.md`).
>
> Última actualización: 2026-06-19

---

## 1. Pantalla Unificada de Trámite (Tramite Unified Layout)

**Spec:** `.kiro/specs/tramite-unified-layout/` · **Ruta:** `/deskapp/tramitesn/unified-layout?tramite_id={id}`
**Reemplaza el prototipo:** `tramites_layout_prototipo.php` (monolítico ~10K líneas)

| # | Entregable | Estado |
|---|------------|--------|
| 1 | CSS dedicado `public/assets/src/styles/tramite_unified_layout.css` (prefijo `tul-`) | ✅ Terminado |
| 2 | Vista orquestadora `app/Views/deskapp/tramite_unified/index.php` | ✅ Terminado |
| 3 | 5 partials por paso (`_step1_row.php` … `_step5_row.php`), layout 3 carriles (formulario · documentos · notas) | ✅ Terminado |
| 4 | Módulo JS vanilla IIFE `public/assets/src/js/tramite_unified.js` (AJAX save/upload/delete/notas) | ✅ Terminado |
| 5 | Método controlador `Tramitesn::unified_layout()` + ruta `tramitesn/unified-layout` | ✅ Terminado |

### Funcionalidad implementada (todo ✅ Terminado)
- **Guardado AJAX por paso** sin recarga, con manejo de CSRF regenerado por request (lee `csrfHash` del JSON).
- **Transiciones de status** reutilizando endpoints existentes (sin cambios de backend):
  - Paso 1 `update_save`, Paso 2 `update_gestor_save` / `update_derechos_save`, Aprobar `autorizar` (status 23),
    Paso 3 evidencias `upload_pago_gestor`, Paso 4 `update_pago_gestor`, Paso 5 `update_final_save`.
- **Revelado progresivo con gates y bloqueo (candado):**
  - Evidencias finales (Paso 3) bloqueadas hasta **aprobar** el trámite.
  - Fase financiera (Pasos 4-5) bloqueada hasta **aprobar + evidencias cargadas**.
  - Indicador de fase (3 dots) en la barra de detalle.
- **Barra de detalle sticky** con Folio, ID, Tipo, Asociados, Estatus, Cliente, Gestor, Fase.
- **Dropdown dependiente** Empresa gestora → Gestor (AJAX `getGestoresByEmpresaId`).
- **Selects buscables** con Select2 global.
- **Documentos**: subida, preview de imagen, eliminación, títulos por tipo de documento.
- **Bitácora general compartida** (Pasos 1 y 3) sincronizada en vivo por AJAX.
- **Composición de servicio** (Paso 1): tipo principal + tipos asociados (agregar/cambiar/eliminar).
- **Cálculos financieros en vivo:**
  - Paso 4: Saldo a favor (Pago total − Depósito) con banner de color (gestor/empresa/sin saldo) + costos por trámite asociado editables cuya suma alimenta el Costo del trámite.
  - Paso 5: Costo total = Sumatoria derechos + Honorarios + Comisión + IVA.
- **Acciones globales**: Aprobar (card destacado), **Cancelar** (`cancelar_tramite`, motivo, status 21) y **Concluir** (`autorizar`, status 20) con lógica de permisos (`important_cancelar_tramite`, `important_concluir_tramite`).
- **Modo readonly** server-side por permisos (los elementos de acción no se renderizan).
- **Estilos corporativos** por paso: banda de encabezado, número en círculo, borde lateral, fondo con degradado y sombra. Paleta: P1 `#123b66`, P2 `#1d5f8f`, P3 `#2878b0`, P4 `#0f766e`, P5 `#4338ca`.
- **Correcciones de layout**: ancho completo (`#main-content`), sin scroll horizontal (`overflow-x` en `html`), sticky preservado.

### Pendiente de esta pantalla (⏳ Inconcluso)
- ⏳ Pruebas E2E automatizadas sobre Docker PHP 8.2 (la validación fue manual).
- ⏳ Revisión de *deprecations* PHP 8.1+ (`trim(null)`) latentes en otros endpoints del backend (se corrigió el de `folio`).
- ⏳ Confirmar que los permisos `editar_tramite_principal`, `editar_tramite_asociado`, `delete_tramite_asociado` estén asignados en roles/DB (la composición de servicio del Paso 1 depende de ellos).

---

## 2. Sistema de Auditoría de Trámites
> Consolida: `AUDIT_FINAL_REPORT.md`, `AUDIT_INTEGRATION_COMPLETE.md`, `AUDIT_CHECKLIST.md`, `AUDIT_INCONSISTENCIAS.md`

- ✅ **Terminado** — Sistema de auditoría integrado en el módulo de Trámites: captura creación, actualización de campos, cambios de estatus (con resolución de nombres), autorizaciones, subida de evidencias, actualización de documentos. Registra usuario, IP, navegador, timestamp, last modifier y contador de modificaciones. Tabla `tramite_audit_log`. Probado.
- ✅ **Terminado** — Métodos integrados: `insert()`, `update_save()` (compara datos y registra cambios masivos con `compare_tramite_data` + `log_tramite_bulk_changes`).
- ⏳ **Inconcluso** — Retirar/encapsular la escritura legacy de `tra_user_log` (migración a `tramite_audit_log` funcionalmente completa; falta limpieza definitiva).
- ⏳ **Inconcluso** (de `AUDIT_INCONSISTENCIAS.md`) — Consolidar callbacks duplicados de GroceryCrud; sacar `Tramites_backup.php` de `app/Controllers`.

---

## 3. Auditoría de Rutas y Menú
> Consolida: `AUDIT_MENU_IMPLEMENTATION.md`, `AUDIT_RUTAS_MENU_2026-05-25.md`

- ✅ **Terminado** — Auditoría de rutas vs menú (entregable completado).
- ✅ **Terminado** — Corrección de typos y placeholders malformados en `app/Config/Routes.php`.
- ⏳ **Inconcluso** — Plan de retiro progresivo de aliases/AutoRoute y estandarización de prefijo `/deskapp` (ver `PLAN_RETIRO_PREFIJO_DESKAPP.md`).

---

## 4. Filtro Multi-Tenancy en Reportes y Dashboards
> Consolida: `IMPLEMENTACION_REPORTES_COMPLETA.md`, `IMPLEMENTACION_FILTRO_GUIA.md`

- ✅ **Terminado al 100%** — Filtrado multi-tenancy en todo el módulo de reportes/dashboards. `DashboardModel.php`: 22 métodos actualizados + helper `getClienteFilterSQL($userId)`. Cada usuario solo ve métricas/KPIs/reportes de sus clientes asignados.

---

## 5. Módulo de Corrección de Trámites
> Consolida: `CORRECCION_TRAMITES_README.md`

- ✅ **Terminado** — Módulo administrativo para corregir `tra_tipos_id` y `tra_status_id`. Campos base bloqueados, auditoría automática, historial de últimas 500 correcciones, acceso solo Admin, CSRF.

---

## 6. QA — Readonly por permisos en pasos 1-5
> Consolida: `QA_TRAMITES_READONLY_STEPS_1_3.md`, `QA_TRAMITES_READONLY_STEPS_4_5.md`

- ✅ **Terminado** — Guiones de QA para validar modo readonly server-side por permisos `write_*` en los 5 pasos (matriz de usuarios de prueba, denegación backend de POST/upload). Documentación de pruebas manuales.

---

## 7. Backlog general del proyecto (⏳ Pendiente)
> Consolida: `PENDIENTES_README.md` y planes asociados

- ⏳ **Migración a PHP 8.2** (ver `MIGRACION_PHP_8_2_README.md`, `DOCKER_PHP82_README.md`).
- ⏳ **Limpieza/estandarización de rutas** — retiro de aliases sin `/deskapp` (ver `PLAN_RETIRO_PREFIJO_DESKAPP.md`).
- ⏳ **Salida de GroceryCrud** — plan de migración (ver `PLAN_SALIDA_GROCERYCRUD.md`).
- ⏳ **Cobranza / permisos** — simplificar acceso a Cobro a Cliente sin depender de `section_final_costos`; eliminar consideraciones legacy de acceso.
- ⏳ **Permisos nuevos por mapear en roles/DB**: `editar_tramite_principal`, `editar_tramite_asociado`, `delete_tramite_asociado`.
- ✅ **Terminado** — Quitar bypass por rol/usuario especial; Super Admin ve todo por permisos efectivos asignados (no por bypass en helpers).

---

## Índice de archivos consolidados y su destino

| Archivo original | Estado | Acción |
|------------------|--------|--------|
| `PROTOTIPO_LAYOUT_REFACTOR.md` | Superado por el spec | Marcado como superado (conservado) |
| `PENDIENTES_README.md` | Consolidado aquí (§7) | Eliminado |
| `AUDIT_FINAL_REPORT.md` | Consolidado (§2) | Eliminado |
| `AUDIT_INTEGRATION_COMPLETE.md` | Consolidado (§2) | Eliminado |
| `AUDIT_CHECKLIST.md` | Consolidado (§2) | Eliminado |
| `AUDIT_INCONSISTENCIAS.md` | Consolidado (§2) | Eliminado |
| `AUDIT_MENU_IMPLEMENTATION.md` | Consolidado (§3) | Eliminado |
| `AUDIT_RUTAS_MENU_2026-05-25.md` | Consolidado (§3) | Eliminado |
| `IMPLEMENTACION_REPORTES_COMPLETA.md` | Consolidado (§4) | Eliminado |
| `IMPLEMENTACION_FILTRO_GUIA.md` | Consolidado (§4) | Eliminado |
| `QA_TRAMITES_READONLY_STEPS_1_3.md` | Consolidado (§6) | Eliminado |
| `QA_TRAMITES_READONLY_STEPS_4_5.md` | Consolidado (§6) | Eliminado |
| `CLIENTE_DASHBOARD_DEBUG.md` | Log de depuración obsoleto | Eliminado |

> **Nota:** La documentación de referencia de módulos (APIs, guías, manuales, gobernanza CSS, multi-tenancy, notificaciones, etc.) se **conserva** por seguir siendo útil como documentación viva.
