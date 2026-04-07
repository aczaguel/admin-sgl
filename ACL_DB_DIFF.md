# Diff de permisos: Código vs DB

DB: **localhost** / **procedures**

- Permisos distintos en código: **65**
- Permisos distintos en DB (`us_permissions`) (todos): **171**
- Permisos distintos en DB (`us_permissions`) (activos `status=1`): **62**
- Faltantes en DB (no existen, referenciados en código): **3**
- Faltantes activos (no están activos `status=1`, referenciados en código): **3**
- Activos sin uso en código (solo en DB con `status=1`): **0**
- Inactivos pero usados (existen en DB pero `status=0`): **0**

## Faltantes en DB

- `menu_notifications`
- `sincronizar_tramites`
- `wizard_list_only_own`

## Sin uso en código

(Ninguno)

## Inactivos pero usados (DB status=0)

(Ninguno)
