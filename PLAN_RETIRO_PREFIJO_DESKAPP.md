# Plan de Retiro del Prefijo /deskapp

Fecha: 2026-05-26

## Decision de diseno

- Objetivo final: retirar `/deskapp` de las rutas internas del modulo autenticado.
- Restriccion: no hacerlo antes de estabilizar rutas explicitas y de cerrar el frente de migracion a PHP 8.2.
- Politica: no sostener dos convenciones indefinidamente. Si se necesita compatibilidad temporal, debe ser corta, medible y con fecha de retiro.

## Por que no conviene hacerlo primero

- Hoy `/deskapp` es el prefijo canonico real de la navegacion autenticada en [app/Config/Routes.php](app/Config/Routes.php#L39).
- El menu admin y cliente enlazan directamente a rutas con `/deskapp` desde [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L53) y [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L36).
- Notificaciones, cobranza, tramites y dashboards consumen URLs con `/deskapp` desde vistas y JS, por ejemplo [app/Views/deskapp/includes/_notifications_dropdown.php](app/Views/deskapp/includes/_notifications_dropdown.php#L255), [app/Views/deskapp/cobranza/_detail.php](app/Views/deskapp/cobranza/_detail.php#L81) y [app/Views/deskapp/notifications/index.php](app/Views/deskapp/notifications/index.php#L297).
- Helpers, modelos, servicios y pruebas tambien dependen del prefijo, por ejemplo [app/Helpers/acl_guard_helper.php](app/Helpers/acl_guard_helper.php#L58), [app/Models/NotificationModel.php](app/Models/NotificationModel.php#L201), [app/Services/CobranzaDashboardService.php](app/Services/CobranzaDashboardService.php#L293) y [tests/app/Controllers/Deskapp/CobranzaControllerTest.php](tests/app/Controllers/Deskapp/CobranzaControllerTest.php#L54).
- Ademas siguen existiendo rutas que hoy funcionan por `AutoRoute`, como el bloque cliente comentado en [app/Config/Routes.php](app/Config/Routes.php#L64) pero consumido desde [app/Views/deskapp/clientes/tramites_list.php](app/Views/deskapp/clientes/tramites_list.php#L209).

## Condiciones previas obligatorias

Antes de tocar el prefijo deben cumplirse estas condiciones:

1. Todas las rutas usadas por UI y JS deben quedar explicitadas en [app/Config/Routes.php](app/Config/Routes.php).
2. Debe existir una lista cerrada de consumidores internos del prefijo `/deskapp`.
3. La migracion a PHP 8.2 debe estar al menos estable en QA para no mezclar dos refactors de infraestructura.
4. No deben introducirse nuevas referencias hardcoded a `/deskapp` mientras se prepara el cambio.

## Superficies afectadas

El retiro no es solo de rutas. Afecta al menos estas capas:

- Rutas explicitas en [app/Config/Routes.php](app/Config/Routes.php).
- Navegacion y breadcrumbs en vistas bajo [app/Views/deskapp](app/Views/deskapp).
- JS y formularios que usan `site_url()` o `base_url()` con `/deskapp` hardcoded.
- Redirects de auth y ACL en [app/Helpers/acl_guard_helper.php](app/Helpers/acl_guard_helper.php#L58).
- URLs persistidas o fabricadas en modelos y servicios, por ejemplo [app/Models/NotificationModel.php](app/Models/NotificationModel.php#L201) y [app/Services/CobranzaDashboardService.php](app/Services/CobranzaDashboardService.php#L293).
- Pruebas controller/integration que hoy afirman rutas con `/deskapp`.
- Bookmarks internos, enlaces compartidos y cualquier integracion no versionada fuera de app.

## Estrategia recomendada

La ruta segura no es quitar `/deskapp` de golpe. La estrategia correcta es desacoplar primero y recortar despues.

### Fase 0. Congelamiento del prefijo como canon temporal

- Declarar `/deskapp/...` como unica convension canonica mientras se prepara el retiro.
- No agregar nuevas rutas sin prefijo salvo APIs versionadas o modulos publicos.
- No agregar nuevos aliases de aplicacion para compatibilidad.

Resultado esperado:

- La base deja de moverse en dos direcciones al mismo tiempo.

### Fase 1. Explicitar todo lo que hoy depende de AutoRoute

- Rehabilitar y declarar explicitamente las rutas cliente modernas que hoy estan comentadas.
- Declarar cualquier endpoint de notificaciones, wizard, cobranza y tramitesn que siga sobreviviendo por AutoRoute.
- Verificar que cada URL usada por menu, JS o formularios tenga route explicita.

Resultado esperado:

- El sistema funciona completo sin depender de `AutoRoute` para rutas internas principales.

### Fase 2. Desacoplar referencias hardcoded

- Sustituir cadenas literales tipo `deskapp/...` por helpers centralizados o rutas nombradas.
- Empezar por superficies transversales:
  - sidebars
  - breadcrumbs
  - notifications dropdown
  - servicios que fabrican URLs
  - modelos que persisten URLs
  - redirects ACL/auth
- Actualizar las pruebas para que validen helpers o rutas nombradas y no el literal del prefijo cuando sea posible.

Resultado esperado:

- El prefijo deja de estar regado por vistas, servicios, helpers y pruebas.

### Fase 3. Introducir nuevas rutas canonicas sin /deskapp

- Crear la nueva superficie canonica sin `/deskapp` en [app/Config/Routes.php](app/Config/Routes.php).
- Mantener por un tiempo acotado redirects desde `/deskapp/...` hacia la ruta nueva.
- Esa compatibilidad debe vivir como redirect controlado, no como doble ruta de negocio permanente.
- No aceptar nuevos consumidores internos del prefijo viejo durante esta fase.

Resultado esperado:

- Toda navegacion interna de la app ya usa la nueva convension sin `/deskapp`.

### Fase 4. Corte y retiro

- Medir accesos a rutas con `/deskapp` durante una ventana corta en QA y produccion controlada.
- Corregir los ultimos enlaces externos o bookmarks detectados.
- Eliminar redirects y definiciones antiguas una vez que la ventana cierre en cero o en ruido aceptable.

Resultado esperado:

- Solo existe una convension de rutas internas y `/deskapp` desaparece de la app.

## Orden exacto de ejecucion

Si se quisiera calendarizar, el orden recomendado es este:

1. Cerrar dependencia de `AutoRoute` en rutas cliente y endpoints internos.
2. Crear un helper o convension central para construir rutas internas.
3. Reemplazar referencias hardcoded en vistas, helpers, modelos, servicios y tests.
4. Pasar smoke tests sobre login, dashboard, notificaciones, tramitesn, cobranza y cliente.
5. Crear rutas nuevas sin `/deskapp`.
6. Redirigir temporalmente `/deskapp/...` a las nuevas rutas.
7. Medir consumo residual.
8. Retirar definitivamente el prefijo viejo.

## Criterios de aceptacion

El cambio solo debe considerarse listo cuando se cumpla todo esto:

- No hay vistas activas apuntando a `/deskapp/...`.
- No hay helpers, modelos o servicios fabricando URLs con `/deskapp/...`.
- No hay tests nuevos o actualizados que dependan del prefijo viejo.
- El menu, breadcrumbs y formularios funcionan completos con la nueva convension.
- Cobranza, notificaciones, dashboard admin, cliente y tramitesn pasan smoke tests.
- `AutoRoute` ya no es requisito para que la superficie interna principal funcione.

## Riesgos principales

- Romper redirects de auth y ACL si se cambia el home autenticado sin tocar helpers.
- Dejar URLs persistidas en notificaciones apuntando al prefijo viejo.
- Romper formularios AJAX de cobranza o notificaciones por referencias hardcoded en JS.
- Mezclar este cambio con la migracion a PHP 8.2 y perder capacidad de aislar fallas.

## Recomendacion final

- Si el objetivo es reducir ruido de naming, primero hay que volver explicita y estable la superficie actual.
- Si el objetivo es simplificar la arquitectura, el retiro de `/deskapp` si vale la pena, pero debe ir despues de PHP 8.2 y despues de sacar a la app de la dependencia en `AutoRoute`.
- Si hubiera que elegir una sola prioridad hoy, conviene estabilizar PHP 8.2 y dejar este plan listo para ejecutarse despues, no antes.