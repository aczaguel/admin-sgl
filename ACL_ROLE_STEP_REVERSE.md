# Ingeniería inversa: Roles → Steps tocados

Regla: se construye un catálogo de permisos por step (1–5) y, por cada rol, si tiene al menos un permiso del step entonces “toca” ese step.

Fuente DB: `us_roles`, `us_role_permissions`, `us_permissions` (solo permisos activos `status=1`).

## Matriz (resumen)

| Rol | Paso 1 | Paso 2 | Paso 3 | Paso 4 | Paso 5 | |
|---|:---:|:---:|:---:|:---:|:---:|---|
| Admin | ✅ | ✅ | ✅ | ✅ | ✅ | 5 steps |
| Authorizer Editor | — | — | — | — | — | 0 steps |
| Authorizer Simple | — | — | — | — | — | 0 steps |
| Cliente | ✅ | — | — | — | — | 1 steps |
| Closer | — | — | — | ✅ | — | 1 steps |
| Developer | — | — | — | — | — | 0 steps |
| Executer | ✅ | ✅ | — | — | ✅ | 3 steps |
| Extras_Temporales | ✅ | ✅ | ✅ | ✅ | ✅ | 5 steps |
| Reporter | — | — | — | — | — | 0 steps |
| Starter | ✅ | — | — | — | — | 1 steps |
| Super Admin | — | — | — | — | — | 0 steps |
| Viewer | ✅ | — | — | — | — | 1 steps |

## Detalle por rol (permisos que hacen match)

### Admin

- Paso 1: `delete_tramite`, `editar_tramite`, `listar_tramite`, `menu_tramites`, `section_inicial_datos`

- Paso 2: `section_asigna_gestor`, `tramite_view_gestor`

- Paso 3: `section_documentos_pago`, `section_linea_captura`, `section_pago_derechos`

- Paso 4: `important_pasar_a_pagos`

- Paso 5: `important_cancelar_tramite`, `important_concluir_tramite`, `listar_final_tramite`, `menu_proceso_final`, `section_final_costos`

- Admin permisos (fuera de pasos 1–5): `header_buttons`, `menu_clientes`, `menu_configuracion`, `menu_documentos`, `menu_gestores`, `menu_permisos`

### Authorizer Editor

- Paso 1: —

- Paso 2: —

- Paso 3: —

- Paso 4: —

- Paso 5: —

- Admin permisos (fuera de pasos 1–5): —

### Authorizer Simple

- Paso 1: —

- Paso 2: —

- Paso 3: —

- Paso 4: —

- Paso 5: —

- Admin permisos (fuera de pasos 1–5): —

### Cliente

- Paso 1: `export_tramite`, `menu_tramites`, `print_tramite`, `read_tramite`

- Paso 2: —

- Paso 3: —

- Paso 4: —

- Paso 5: —

- Admin permisos (fuera de pasos 1–5): —

### Closer

- Paso 1: —

- Paso 2: —

- Paso 3: —

- Paso 4: `editar_pago_gestor`, `section_pago_gestor`

- Paso 5: —

- Admin permisos (fuera de pasos 1–5): `header_buttons`

### Developer

- Paso 1: —

- Paso 2: —

- Paso 3: —

- Paso 4: —

- Paso 5: —

- Admin permisos (fuera de pasos 1–5): —

### Executer

- Paso 1: `editar_tramite`, `export_tramite`, `listar_tramite`, `menu_tramites`

- Paso 2: `tramite_view_gestor`

- Paso 3: —

- Paso 4: —

- Paso 5: `listar_concluidos_tramite`, `listar_final_tramite`, `menu_proceso_final`

- Admin permisos (fuera de pasos 1–5): —

### Extras_Temporales

- Paso 1: `editar_tramite`, `read_tramite`, `section_inicial_datos`

- Paso 2: `section_asigna_gestor`

- Paso 3: `section_documentos_pago`, `section_linea_captura`, `section_pago_derechos`

- Paso 4: `important_pasar_a_pagos`

- Paso 5: `important_cancelar_tramite`, `important_concluir_tramite`, `section_final_costos`

- Admin permisos (fuera de pasos 1–5): `header_buttons`

### Reporter

- Paso 1: —

- Paso 2: —

- Paso 3: —

- Paso 4: —

- Paso 5: —

- Admin permisos (fuera de pasos 1–5): —

### Starter

- Paso 1: `listar_tramite`, `menu_tramites`

- Paso 2: —

- Paso 3: —

- Paso 4: —

- Paso 5: —

- Admin permisos (fuera de pasos 1–5): —

### Super Admin

- Paso 1: —

- Paso 2: —

- Paso 3: —

- Paso 4: —

- Paso 5: —

- Admin permisos (fuera de pasos 1–5): —

### Viewer

- Paso 1: `listar_tramite`

- Paso 2: —

- Paso 3: —

- Paso 4: —

- Paso 5: —

- Admin permisos (fuera de pasos 1–5): —

