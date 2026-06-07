# Inventario CSS UI

Fecha de corte: 2026-06-05

## Resumen actual

- Bloques style en vistas deskapp: 39
- Estilos inline en vistas deskapp: 780
- CSS propio central identificado: [public/assets/src/styles/sgl_blue_template.css](public/assets/src/styles/sgl_blue_template.css)
- Base SGL importada por el template: [public/assets/src/styles/sgl_layout_2026.css](public/assets/src/styles/sgl_layout_2026.css)

## Base central existente

- Layout shell: [app/Views/layout/_main_shell.php](app/Views/layout/_main_shell.php)
- Header admin: [app/Views/deskapp/includes/_header.php](app/Views/deskapp/includes/_header.php)
- Header cliente: [app/Views/deskapp/includes/_header_cliente.php](app/Views/deskapp/includes/_header_cliente.php)
- CSS principal SGL en runtime: [public/assets/src/styles/sgl_blue_template.css](public/assets/src/styles/sgl_blue_template.css)
- Base importada por el template: [public/assets/src/styles/sgl_layout_2026.css](public/assets/src/styles/sgl_layout_2026.css)

## Hotspots principales

- [app/Views/deskapp/cobranza/_detail.php](app/Views/deskapp/cobranza/_detail.php)
Motivo: concentración alta de estilos inline, gradientes repetidos, modales y componentes reutilizables hoy incrustados en markup.

- [app/Views/deskapp/clientes/tramites_show.php](app/Views/deskapp/clientes/tramites_show.php)
Motivo: mezcla grande de bloque style inicial más un volumen alto de inline styles para ribbons, timeline, docs y badges.

- [app/Views/deskapp/cobranza/index.php](app/Views/deskapp/cobranza/index.php)
Motivo: bloque style propio de módulo y paginación/controles que pueden convertirse en componentes compartidos.

- [app/Views/deskapp/dashboard/dashboard_cliente.php](app/Views/deskapp/dashboard/dashboard_cliente.php)
Motivo: theme local completo dentro de la vista; conviene extraer a CSS de módulo o promover piezas al layout central.

- [app/Views/deskapp/includes/_header.php](app/Views/deskapp/includes/_header.php)
- [app/Views/deskapp/includes/_header_cliente.php](app/Views/deskapp/includes/_header_cliente.php)
Motivo: contienen reglas amplias de layout incrustadas en la vista; deben migrar gradualmente al CSS central.

## Limpiezas ya iniciadas

- `sgl-liston` disponible en la fuente central [public/assets/src/styles/sgl_blue_template.css](public/assets/src/styles/sgl_blue_template.css) vía la base SGL importada.
- CSS completo de [app/Views/deskapp/clientes/tramites_show.php](app/Views/deskapp/clientes/tramites_show.php) migrado a [public/assets/src/styles/clientes_tramites_show.css](public/assets/src/styles/clientes_tramites_show.css)
- Utilidades del detalle de Cobranza migradas a [public/assets/src/styles/cobranza_detail.css](public/assets/src/styles/cobranza_detail.css)
- Reglas duplicadas de header/session/cliente-context expuestas ahora mediante [public/assets/src/styles/sgl_blue_template.css](public/assets/src/styles/sgl_blue_template.css)
- CSS de [app/Views/deskapp/dashboard/dashboard_admin.php](app/Views/deskapp/dashboard/dashboard_admin.php) y [app/Views/deskapp/dashboard/alertas.php](app/Views/deskapp/dashboard/alertas.php) centralizado en [public/assets/src/styles/sgl_blue_template.css](public/assets/src/styles/sgl_blue_template.css)
- Duplicación removida de:
  - [app/Views/deskapp/tramitesn/search.php](app/Views/deskapp/tramitesn/search.php)
  - [app/Views/deskapp/bitacora/bitacora_search.php](app/Views/deskapp/bitacora/bitacora_search.php)
  - [app/Views/deskapp/tramites/audit_search.php](app/Views/deskapp/tramites/audit_search.php)
  - [app/Views/deskapp/clientes/tramites_show.php](app/Views/deskapp/clientes/tramites_show.php)

## Orden recomendado de migración

1. Extraer utilidades repetidas de búsqueda, alerts, empty states y paginación al CSS central.
2. Separar CSS de Cobranza en archivos de módulo y reducir inline styles del detalle.
3. Separar CSS de Clientes trámites show en archivo de módulo y convertir ribbons/docs/timelines a clases semánticas.
4. Mover reglas grandes de header al layout CSS central.
5. Auditar vistas dashboard y extra-pages para eliminar nuevos bloques style antes de seguir creciendo la deuda.