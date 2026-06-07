# Gobernanza CSS UI

Fecha base: 2026-06-05

## Objetivo

Reducir la fragmentación visual del sistema y establecer una sola fuente de verdad para layout, componentes compartidos y reglas de extensión por módulo.

## Fuente de verdad

- Entry point del CSS propio del sistema: [public/assets/src/styles/sgl_blue_template.css](public/assets/src/styles/sgl_blue_template.css)
- Base SGL importada por el template: [public/assets/src/styles/sgl_layout_2026.css](public/assets/src/styles/sgl_layout_2026.css)
- Shell principal: [app/Views/layout/_main_shell.php](app/Views/layout/_main_shell.php)
- Header/sidebar legacy con carga del layout SGL: [app/Views/deskapp/includes/_header.php](app/Views/deskapp/includes/_header.php) y [app/Views/deskapp/includes/_header_cliente.php](app/Views/deskapp/includes/_header_cliente.php)

## Reglas

1. Todo patrón reutilizable debe vivir en CSS central.
Casos típicos: alertas/listones, paginación, chips, ribbons, cards, tablas, headers de búsqueda, estados vacíos.

2. No se permiten nuevos bloques style dentro de vistas de aplicación.
Excepciones aceptables: impresión, emails renderizados, pruebas temporales muy acotadas que se retiren en la misma tarea.

3. No se permiten nuevos estilos inline salvo valores realmente dinámicos.
Ejemplos aceptables: width calculado desde backend, color proveniente de dato, posiciones generadas por lógica.

4. El CSS específico de módulo debe ir a un archivo dedicado en [public/assets/src/styles](public/assets/src/styles), no dentro de la vista.
Formato sugerido: nombre por módulo o pantalla, por ejemplo cobranza.css, clientes_tramites.css, bitacora.css.

5. Las librerías visuales compartidas no deben cargarse ad hoc por vista si ya forman parte del shell.
Si un recurso visual es necesario en varias pantallas, se promueve al layout y no se duplica localmente.

6. Cuando una vista necesite ajuste visual, el orden de decisión es:
   - primero revisar si el patrón ya existe en el layout CSS
   - después extender el CSS central si el patrón es compartible
   - solo al final crear CSS de módulo si de verdad es específico

## Convención operativa

- `sgl_blue_template.css`: fuente única a cargar para el CSS propio del sistema.
- `sgl_layout_2026.css`: base histórica importada por `sgl_blue_template.css`.
- CSS de módulo: reglas funcionales de una pantalla o dominio concreto.
- Vista PHP: markup y clases; no diseño embebido.

## Primera limpieza ejecutada

- El patrón repetido `sgl-liston` fue movido al CSS central.
- Dashboard Admin y Alertas dejaron de depender de bloques `style` embebidos; sus reglas viven ahora en [public/assets/src/styles/sgl_blue_template.css](public/assets/src/styles/sgl_blue_template.css).
- Se retiró CSS duplicado de:
  - [app/Views/deskapp/tramitesn/search.php](app/Views/deskapp/tramitesn/search.php)
  - [app/Views/deskapp/bitacora/bitacora_search.php](app/Views/deskapp/bitacora/bitacora_search.php)
  - [app/Views/deskapp/tramites/audit_search.php](app/Views/deskapp/tramites/audit_search.php)

## Siguiente criterio de migración

- Prioridad alta: vistas con más inline style y patrones repetidos.
- Prioridad media: vistas con un solo bloque style pero sin reutilización clara.
- Prioridad baja: vistas de impresión o casos muy aislados.