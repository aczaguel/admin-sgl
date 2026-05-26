# Auditoria de Rutas vs Menu

Fecha: 2026-05-25

## Resumen

- El menu visible real se concentra en [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php) y [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php).
- Hay rutas explicitas que si corresponden a navegacion activa, pero tambien existen aliases legacy, rutas demo/template y varios endpoints internos que no deben clasificarse como menu.
- El estado actual de [app/Config/Routes.php](app/Config/Routes.php) mezcla tres superficies:
  - navegacion de negocio vigente
  - endpoints internos AJAX o formularios
  - rutas legacy/demo que siguen vivas por declaracion explicita o por AutoRoute
- `setAutoRoute(true)` en [app/Config/Routes.php](app/Config/Routes.php#L22) mantiene viva una superficie mas amplia y hoy mascara algunas rutas comentadas o no explicitadas.

## Navegacion

Rutas con consumo visible en sidebar o header:

- Dashboard admin: [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L53)
  - `/deskapp/dashboardadmin`
  - `/deskapp/dashboardadmin/alertas`
  - `/deskapp/dashboardadmin/financiero`
  - `/deskapp/dashboardadmin/reportes`
  - `/deskapp/dashboardadmin/por_cliente`
- Tramites operativos: [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L97)
  - `/deskapp/tramitesn/tramite`
  - `/deskapp/tramitesn/search`
  - `/deskapp/flotillas/import`
  - `/deskapp/tramites_masivos/import`
  - `/deskapp/concluido/final`
  - `/deskapp/tramites/tenencias`
- Cobranza: [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L143)
  - `/deskapp/cobranza`
- Cierre de tramites: [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L170)
  - `/deskapp/proceso/final`
  - `/deskapp/tramites/cancelados`
- Gestores: [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L198)
  - `/deskapp/gestores/gestores`
  - `/deskapp/gestores/gestor`
- Clientes: [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L220)
  - `/deskapp/clientes/cliente`
  - `/deskapp/clidirecto/clidirecto`
  - `/deskapp/clidirecto/ejecutivo`
- Configuracion: [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L248)
  - `/deskapp/tramites/tipo`
  - `/deskapp/tramites/status`
- Documentos: [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L270)
  - `/deskapp/documentos/documento`
  - `/deskapp/documentos/status`
- Roles y permisos: [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L292)
  - `/deskapp/roles/roles`
  - `/deskapp/permisos/permisos`
  - `/deskapp/roles/role_permissions`
  - `/deskapp/users/users`
  - `/deskapp/users/user_roles`
- Monitoreo: [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L322)
  - `/bitacora/search`
  - `/correccion-tramites`
  - `/deskapp/tramites/audit_search`
- Sidebar cliente: [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L36)
  - `/deskapp/clientes/dashboard`
  - `/deskapp/clientes/tramites`

## Internas

Rutas usadas por fetch, POST, formularios o acciones internas y que no forman parte del menu:

- Notificaciones: [app/Views/deskapp/includes/_notifications_dropdown.php](app/Views/deskapp/includes/_notifications_dropdown.php#L255)
  - `/deskapp/notifications/api_unread`
  - `/deskapp/notifications/api_mark_read/:id`
  - `/deskapp/notifications/api_mark_all_read`
  - `/deskapp/notifications/api_delete/:id`
- Pantalla completa de notificaciones: [app/Views/deskapp/notifications/index.php](app/Views/deskapp/notifications/index.php#L297)
  - `/deskapp/notifications/api_mark_all_read`
  - `/deskapp/notifications/api_mark_read/:id`
  - `/deskapp/notifications/api_delete/:id`
- Cliente tramite list: [app/Views/deskapp/clientes/tramites_list.php](app/Views/deskapp/clientes/tramites_list.php#L209)
  - `/deskapp/clientes/tramites/data`
- Tramite wizard: [app/Views/deskapp/tramite_wizard/index.php](app/Views/deskapp/tramite_wizard/index.php#L870)
  - `/deskapp/tramitewizard/guardar`
  - `/deskapp/tramitewizard/guardar_borrador`
  - `/deskapp/tramitewizard/recuperar_borrador`
  - `/deskapp/tramitewizard/get_municipios`
  - `/deskapp/tramitewizard/get_ejecutivos_cliente`
  - `/deskapp/tramitewizard/get_gestores`
- Bitacora search: [app/Views/deskapp/bitacora/bitacora_search.php](app/Views/deskapp/bitacora/bitacora_search.php#L239)
  - `/bitacora/timeline` con query string por `tramite_id`, `folio` o `contrato`
- API externa versionada:
  - `/api/v1/tramites`
  - `/api/v1/tramites/referencia/:segment`
  - `/api/v1/tramites/:num`

## Acceso Directo Legitimo

Rutas que no aparecen directamente en menu pero si son deep links validos o pantallas buscables:

- `/deskapp/tramitesn/update/:id`
- `/deskapp/tramitesn/ver_seccion_pago_gestor/:id`
- `/deskapp/tramitesn/ver_seccion_evidencias_finales/:id`
- `/deskapp/tramitesn/ver_seccion_cobro_cliente/:id`
- `/deskapp/cobranza/expediente/:id`
- `/deskapp/tramites/audit_timeline/:id`
- `/bitacora/timeline`
- `/customers/tramite/:id` usado como deep link desde [app/Views/deskapp/clientes/tramites_list.php](app/Views/deskapp/clientes/tramites_list.php#L256)
- `/deskapp/notifications` enlazado desde [app/Views/deskapp/includes/_notifications_dropdown.php](app/Views/deskapp/includes/_notifications_dropdown.php#L36)

## Huerfanas

Rutas explicitas sin consumo claro en menu principal, header, vistas o JS revisado:

- `/tramites/demo_multigrid` en [app/Config/Routes.php](app/Config/Routes.php#L136)
- `/wizard` y sus pasos en [app/Config/Routes.php](app/Config/Routes.php#L200)
- `/tramites/recoleccion` en [app/Config/Routes.php](app/Config/Routes.php#L206)
- `/tramites/en_tramite` en [app/Config/Routes.php](app/Config/Routes.php#L209)
- `/tramites/autorizar` en [app/Config/Routes.php](app/Config/Routes.php#L212)
- `/tramites/tramite_2024` y `/tramites/tramite_2025`
- `/tramites/mios`
- Grupo legacy `single_*` del controlador Tramites, salvo que sobreviva por enlaces no detectados:
  - `/tramites/single_documentostatus`
  - `/tramites/single_documentostatus/:id`
  - `/tramites/single_evidencias/:id`
  - `/tramites/single_pago_derechos/:id`
  - `/tramites/single_pago_gestor/:id`
  - `/tramites/single_cobro_cliente/:id`

## Sospechosas por Typo o Legacy

### Typos de destino en GET

Estas rutas tienen un solo `:` en vez de `::` en el destino del controlador, por lo que el GET es sospechoso mientras el POST vecino apunta bien:

- `/gestores/gestores` en [app/Config/Routes.php](app/Config/Routes.php#L236)
- `/clidirecto/clidirecto` en [app/Config/Routes.php](app/Config/Routes.php#L246)
- `/clidirecto/ejecutivo` en [app/Config/Routes.php](app/Config/Routes.php#L249)
- `/tradocstatus/documento` en [app/Config/Routes.php](app/Config/Routes.php#L252)
- `/bitacora/index/:tramite_id` en [app/Config/Routes.php](app/Config/Routes.php#L255)

### Placeholders malformados

Estas rutas declaran `(:id)` en la URI y tambien dejan `(:id)` literal en el target, en vez de usar `$1`:

- `/tramites/single_documentostatus/:id` en [app/Config/Routes.php](app/Config/Routes.php#L148)
- `/tramites/single_evidencias/:id` en [app/Config/Routes.php](app/Config/Routes.php#L158)
- `/tramites/single_pago_derechos/:id` en [app/Config/Routes.php](app/Config/Routes.php#L161)
- `/tramites/single_pago_gestor/:id` en [app/Config/Routes.php](app/Config/Routes.php#L165)
- `/tramites/single_cobro_cliente/:id` en [app/Config/Routes.php](app/Config/Routes.php#L169)

### Aliases heredados con y sin prefijo

Patron repetido en varios modulos:

- Dashboard admin con y sin `/deskapp` en [app/Config/Routes.php](app/Config/Routes.php#L462)
- Usuarios, roles y toggles con y sin `/deskapp` en el bloque inicial de [app/Config/Routes.php](app/Config/Routes.php)
- Notificaciones definidas sin `/deskapp` en [app/Config/Routes.php](app/Config/Routes.php#L528) pero consumidas desde vistas como `/deskapp/notifications/...`, lo cual hoy funciona por AutoRoute

### Rutas usadas pero comentadas o no explicitadas

- Las rutas cliente modernas estan comentadas en [app/Config/Routes.php](app/Config/Routes.php#L63) pero se usan desde:
  - [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L44)
  - [app/Views/deskapp/clientes/tramites_list.php](app/Views/deskapp/clientes/tramites_list.php#L209)
- Esto implica que hoy sobreviven por AutoRoute:
  - `/deskapp/clientes/tramites`
  - `/deskapp/clientes/tramites/data`
  - `/deskapp/clientes/ver/:id`

## Conclusiones

- La navegacion de negocio vigente esta relativamente clara y se concentra en rutas con prefijo `/deskapp`.
- La superficie mas riesgosa hoy no es falta de rutas, sino combinacion de:
  - typos en destinos
  - aliases legacy duplicados
  - rutas comentadas que siguen vivas por AutoRoute
  - modulos demo/template que amplian ruido del inventario
- Para limpieza segura conviene primero corregir typos evidentes y documentar que rutas dependen hoy de AutoRoute antes de endurecerlo.

## Recomendacion Operativa

Orden sugerido para siguientes PRs:

1. Corregir typos de `:` por `::` y placeholders `(:id)` por `$1` sin cambiar URLs.
2. Re-explicitar las rutas cliente modernas que hoy dependen de AutoRoute.
3. Retirar aliases heredados duplicados y dejar como unico destino las rutas canonicas con `/deskapp`.
4. Separar o eliminar rutas demo/template y superficie antigua huerfana despues del demo.