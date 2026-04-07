# ROLE_BASED_GATES

Listado de líneas donde el acceso/flujo depende de helpers de rol (`is_*`).
Mientras existan estos gates, activar/desactivar permisos no dará control total (porque hay bypass o bloqueo por rol).

## Resumen (conteo por helper)

| Helper | Ocurrencias |
|---|---:|
| `is_super_admin` | 1 |
| `is_admin` | 0 |
| `is_starter` | 0 |
| `is_executer` | 0 |
| `is_closer` | 0 |
| `is_viewer` | 0 |
| `is_client` | 0 |
| `is_read_only` | 0 |
| `is_authorizer_editor` | 0 |
| `is_authorizer_simple` | 0 |

## Detalle (archivo / línea / snippet)

- app/Helpers/permissions_helper.php#L92 — `if (is_super_admin($roles)) {`

---
Generado por `php utils/scan_role_based_gates.php`.