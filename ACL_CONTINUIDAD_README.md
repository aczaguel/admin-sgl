# ACL Continuidad

Fecha de corte: 2026-04-13

## Objetivo de este documento

Este archivo deja documentado el estado exacto de la conversación para poder retomarla sin reconstruir el contexto desde cero.

Incluye:

- la implementación ya realizada del selector de rol Debug en perfil
- el modelo conceptual acordado para renombrar permisos ACL
- los hallazgos de auditoría sobre permisos, roles y asignaciones reales
- los siguientes pasos recomendados

## 1. Trabajo ya implementado en la aplicación

Se implementó un selector de rol en el perfil para usuarios con capacidad Debug.

### Comportamiento acordado

- el usuario Debug entra con privilegios efectivos de Admin
- puede cambiar su rol efectivo desde un combo en perfil
- al cambiar de rol, el marcador Debug permanece para que el combo nunca desaparezca
- al recargar la página, cambia lo que el usuario puede ver y hacer porque cambia el ACL efectivo en sesión

### Enfoque técnico aplicado

Se separó la identidad real del usuario de la identidad efectiva simulada:

- identidad real/auth: auth_user_roles, auth_user_permissions, auth_is_debug, auth_debug_role_name
- identidad efectiva: user_roles, user_permissions

Con eso el usuario puede simular otro rol sin perder la capacidad de seguir cambiando.

### Archivos involucrados

- app/Controllers/Deskapp/Login.php
- app/Controllers/Deskapp/Users.php
- app/Models/UserModel.php
- app/Views/deskapp/users/profile.php
- app/Config/Routes.php

### Ajustes de UI ya resueltos

- el combo se dejó fijo para usuarios Debug
- se evitó que desapareciera al cambiar a un rol más restrictivo
- se corrigió la deformación del perfil
- se corrigió el desvanecimiento automático: el bloque Debug ya no usa el mismo comportamiento visual de las alertas temporales

## 2. Modelo conceptual acordado para el ACL

### Regla base de pantalla

Toda pantalla parte de esta estructura fija:

- header
- sidebar
- footer
- content

### Regla principal acordada

Header, sidebar y footer son estructura global.

Las zonas funcionales viven dentro de content.

Esto implica:

- notifications, profile, user menu y similares no son zonas: son elementos del header
- los menús de navegación no son zonas: son elementos del sidebar
- las zonas reales del negocio viven dentro de content

### Modelo semántico acordado

La taxonomía final quedó así:

- estructura global: header, sidebar, footer, content
- zona funcional: dashboard, tramites_listado, tramite_detalle, paso_1, paso_2, paso_3, paso_4, paso_5, usuarios, roles, permisos, reportes, monitoreo
- elemento: tabla, formulario, filtros, tabs, panel, resumen, menu, dropzone, card, boton, quick_actions, evidencias, bitacora, documentos, notifications, profile_menu
- acción: ver, listar, crear, editar, eliminar, exportar, subir, ejecutar, autorizar, filtrar, buscar

### Convención de nombres acordada

Se acordó usar guiones bajos y una estructura corta, legible y consistente.

#### Para estructura global

- global_header_notifications_ver
- global_header_profile_menu_ver
- global_sidebar_menu_tramites_ver

#### Para zonas de negocio dentro de content

- dashboard_panel_resumen_ver
- tramites_listado_tabla_listar
- tramites_listado_filtros_filtrar
- tramite_detalle_tabs_ver
- paso_3_formulario_editar
- paso_4_dropzone_pago_gestor_subir
- usuarios_formulario_editar
- permisos_auditoria_ver

### Decisión importante

No usar zona para nombrar header, sidebar o footer.

La palabra zona queda reservada para bloques funcionales dentro de content.

Eso evita mezclar layout persistente con negocio.

## 3. Zonas iniciales aprobadas para content

Estas fueron las primeras zonas propuestas como base del sistema:

1. dashboard
2. tramites_listado
3. tramite_detalle
4. paso_1
5. paso_2
6. paso_3
7. paso_4
8. paso_5
9. usuarios
10. roles
11. permisos
12. reportes

Zonas adicionales ya identificadas como posibles extensiones:

- notificaciones
- monitoreo

## 4. Hallazgos de auditoría sobre permisos

### Estado general

Se cruzó el inventario de permisos usados en código con el catálogo real de permisos y la relación rol-permiso.

### Hallazgos confirmados

- no se encontraron duplicados exactos en us_role_permissions para la combinación role_id + permission_id
- sí existe al menos un permiso duplicado exacto en el catálogo us_permissions: important_concluir_tramite aparece más de una vez con distinto id
- hay muchos permisos asignados a roles que ya no aparecen referenciados por el código actual

### Familias de permisos legados o no usados en código

Se detectaron, entre otros, estos grupos:

- familias crear_, editar_, eliminar_ y listar_ de catálogos viejos
- clone_, export_, print_, read_, search_ y pagination_ de módulos viejos
- permisos de flujo viejo como editar_derechos, editar_bancario, editar_final, final_autoriza_termino, tramite_pasa_a_final, update_*_save, upload_cobro_cliente
- todos, que hoy parece más simbólico porque el bypass real del super admin ocurre por rol/permisos ya resueltos en helper

### Distinción importante

No usado en código no significa automáticamente que se pueda borrar.

Todavía había que cruzarlo con usuarios reales para ver si esos roles seguían vivos.

Ese cruce ya se hizo parcialmente con us_user_roles.

## 5. Hallazgos de auditoría sobre roles

### Roles con permisos vacíos

Con la relación us_role_permissions analizada, estos roles estaban vacíos:

- 9 Developer
- 10 Reporter

### Roles muy traslapados

Se detectó alto traslape, aunque no identidad exacta, entre:

- 13 Authorizer Editor y 14 Authorizer Simple
- 3 Closer y 15 Closer Simple

## 6. Hallazgos de auditoría sobre roles realmente asignados a usuarios

Con el cruce posterior de us_user_roles se concluyó lo siguiente.

### Roles vivos hoy

- 1 Admin: 3 usuarios
- 2 Executer: 7 usuarios
- 3 Closer: 6 usuarios
- 4 Viewer: 1 usuario
- 7 Cliente: 4 usuarios
- 8 Super Admin: 1 usuario
- 10 Reporter: 1 usuario
- 11 Starter: 1 usuario
- 12 Extras_Temporales: 7 usuarios
- 13 Authorizer Editor: 4 usuarios
- 14 Authorizer Simple: 4 usuarios
- 15 Closer Simple: 5 usuarios

### Roles muertos o prácticamente muertos

- 9 Developer: sin usuarios y sin permisos. Candidato real a retiro.
- 10 Reporter: no está muerto porque tiene 1 usuario asignado, pero sí está hueco porque no tiene permisos.

### Lectura operativa

- Extras_Temporales no está muerto: está ampliamente usado y no se puede borrar a la ligera
- los roles 13, 14 y 15 ya están operando en producción y no deben tratarse como experimentales
- existe mucho apilamiento de roles para compensar permisos

### Patrones de apilamiento observados

Patrones recurrentes:

- Executer + Extras_Temporales
- Executer + Extras_Temporales + Closer
- Authorizer Editor + Closer Simple
- Authorizer Simple + Closer Simple

Outlier fuerte:

- un usuario llegó a tener 6 roles simultáneos, lo que sugiere compensación manual de permisos en lugar de perfiles limpios

## 7. Artefactos temporales de análisis

Durante la auditoría se generaron archivos temporales fuera del repo, principalmente en /tmp, para poder cruzar los datasets que se compartieron en la conversación.

### CSV creados

- /tmp/permissions.csv: catálogo temporal id -> permission_name extraído del dump de us_permissions compartido en la conversación
- /tmp/role_permissions.csv: relación temporal role_id -> permission_id construida a partir del dump de us_role_permissions compartido en la conversación
- /tmp/us_user_roles.csv: relación temporal user_id -> role_id construida a partir del dump de us_user_roles compartido en la conversación
- /tmp/not_used_by_code.csv: salida temporal con permisos presentes en DB pero no referenciados por el inventario de permisos usados en código
- /tmp/role_perm_named.csv: salida temporal para enriquecer la relación role-permission con el nombre del permiso y facilitar el análisis manual

### Archivos auxiliares no CSV

- /tmp/used_permissions.txt: lista temporal de permisos detectados en código a partir de ACL_PERMISSIONS_INVENTORY.md
- /tmp/role_signatures.txt: firmas normalizadas de permisos por rol para detectar traslapes
- /tmp/role_signatures_raw.txt: versión intermedia del cálculo de firmas por rol

### Nota importante

Estos archivos fueron artefactos de trabajo para análisis y no forman parte del repositorio.

## 8. Conclusiones operativas acordadas

### Sobre nomenclatura

- separar estructura global de zona funcional
- no llamar zona a header, sidebar o footer
- reservar los nombres de zona para lo que vive en content
- usar nombres con guiones bajos, cortos y consistentes

### Sobre limpieza ACL

- Developer es el mejor candidato a retiro inmediato
- Reporter no debe eliminarse hasta reasignar o rediseñar a su usuario actual
- Extras_Temporales debe descomponerse o redistribuirse, no eliminarse sin análisis
- los permisos duplicados y semánticamente duplicados requieren plan de migración, no borrado directo

## 9. Siguientes pasos recomendados

Cuando se retome el trabajo, el siguiente bloque lógico es uno de estos:

1. construir la matriz final rol por estado: vivo, hueco, legado, fusionable, eliminable
2. sacar el diff exacto entre 13 vs 14 y 3 vs 15 para decidir si se fusionan o se diferencian mejor
3. construir la tabla completa permiso actual -> permiso canónico nuevo
4. definir el catálogo maestro de elementos permitidos
5. definir el catálogo maestro de acciones permitidas
6. preparar un plan de migración ACL con tres grupos: conservar, migrar con alias, eliminar

## 10. Regla para retomar la conversación

Si se reanuda después de una interrupción, partir desde aquí:

- la base visual ya quedó fijada: header, sidebar, footer y content
- las zonas empiezan dentro de content
- el formato preferido de permisos es corto y con guiones bajos
- ya existe evidencia de roles vivos, roles huecos, permisos legados y duplicados exactos en catálogo
- el siguiente paso no es volver a discutir la estructura base, sino convertir permisos y depurar roles

## 11. Aclaraciones detectadas al retomar el 2026-04-14

Al repasar este documento contra los artefactos posteriores del repo aparecieron dos aclaraciones que conviene dejar explícitas para no mezclar conclusiones.

### Diferencia entre catálogo histórico y catálogo activo

ACL_DB_DIFF.md reporta esto en la base localhost/procedures:

- 65 permisos distintos usados en código
- 62 permisos activos en DB
- 3 faltantes en DB: menu_notifications, sincronizar_tramites y wizard_list_only_own
- 0 permisos activos sin uso en código

Esto no contradice necesariamente los hallazgos de permisos legados descritos arriba.

La lectura correcta es esta:

- la sección de auditoría de este documento habla del catálogo histórico completo y de asignaciones heredadas
- ACL_DB_DIFF.md habla del catálogo activo actual en una base concreta
- por tanto, no debe asumirse que hoy sigue habiendo un gran bloque de permisos activos sobrantes; esa conclusión necesita distinguir histórico vs activo

### Precaución al leer la matriz Roles -> Steps

ACL_ROLE_STEP_REVERSE.md muestra que Authorizer Editor, Authorizer Simple, Reporter y Super Admin aparecen con 0 steps.

Eso no significa automáticamente que esos roles no sirvan o no tengan permisos efectivos.

La lectura correcta es:

- esa matriz solo cubre el mapeo de permisos asociados a los steps 1-5
- deja fuera permisos administrativos, header, sidebar, reportes y otras zonas fuera de ese recorte
- por eso un rol puede salir con 0 steps y aun asi seguir vivo o seguir teniendo efecto operativo

### Ajuste de prioridad recomendado

Con estas aclaraciones, el siguiente corte logico recomendado ya no es volver a medir permisos usados vs no usados de forma global, sino esto:

1. sacar el diff exacto de permisos por rol entre 13 vs 14 y 3 vs 15
2. clasificar cada permiso como conservar, fusionar, migrar o retirar
3. solo despues construir la tabla permiso actual -> permiso canonico nuevo

## 12. Avance 2026-04-14: diff fino de roles con export vivo

Se integró evidencia directa de us_roles y us_role_permissions compartida en la conversación.

Este bloque corrige el corte anterior que estaba limitado por los artefactos versionados del repo.

### Fuentes usadas para este corte

- export vivo de us_roles compartido en la conversación
- export vivo de us_role_permissions compartido en la conversación
- admin/sqls/acl_permissions.csv.sql para mapear permission_id -> permission_name
- admin/sqls/unify_acl_roles_permissions_prod.sql para contrastar el objetivo del ACL
- app/Helpers/permissions_helper.php para contrastar la semántica aplicada en código

### Nota metodológica

- para 13 vs 14 el conteo quedó expresado por asignaciones role_id + permission_id del export vivo
- para 3 vs 15 también se hizo lectura semántica por permission_name, porque important_concluir_tramite existe duplicado en catálogo con ids distintos
- los ids 209, 210, 211 y 212 aparecen en el export vivo pero no en el acl_permissions.csv.sql versionado; como están compartidos por 13 y 14, no afectan el diff exclusivo entre ellos

### Diff exacto 13 vs 14 con evidencia viva

Hallazgo clave:

- 13 Authorizer Editor y 14 Authorizer Simple comparten 62 asignaciones role-permission
- 13 tiene 65 asignaciones totales
- 14 tiene 64 asignaciones totales

#### Solo en 13 Authorizer Editor

- important_pasar_a_pagos
- monitoreo_auditoria_tramite
- clone_tramite_cancelado

#### Solo en 14 Authorizer Simple

- tramite_view_gestor
- section_asigna_gestor

#### Lectura operativa 13 vs 14

- 13 sí conserva la capacidad explícita de autorizar paso a pagos
- 13 sí conserva un permiso de monitoreo que 14 no tiene
- 13 también quedó con el permiso de clonar trámites cancelados
- 14, en cambio, quedó más orientado a ver y operar la asignación de gestor
- ambos comparten hoy pago gestor, quick actions y uploads de pago derechos y pago gestor; esto corrige la lectura anterior basada solo en dumps incompletos
- por tanto, no son roles equivalentes, pero la frontera real entre ambos es más estrecha de lo que parecía en el corte previo

### Diff exacto 3 vs 15 con evidencia viva

Hallazgo clave:

- 3 Closer y 15 Closer Simple sí pueden compararse ya de forma exacta
- comparando por permission_name y colapsando el duplicado exacto de important_concluir_tramite, comparten 50 permisos semánticos
- 3 tiene 51 permisos semánticos
- 15 tiene 57 permisos semánticos

#### Solo en 3 Closer

- menu_dashboard_admin

#### Solo en 15 Closer Simple

- can_upload_dropzone_pago_derechos
- can_upload_dropzone_pago_gestor
- important_pasar_a_pagos
- section_documentos_pago
- section_linea_captura
- section_pago_derechos
- write_tramite_pago_derechos

#### Lectura operativa 3 vs 15

- 15 no es un Closer reducido; en realidad es un Closer extendido hacia permisos del paso 3
- 15 mezcla responsabilidades de cierre final con capacidades previas al cierre, especialmente sobre pago de derechos y transición a pagos
- 3 conserva solo un permiso extra que 15 no tiene: menu_dashboard_admin
- eso explica por qué el apilamiento con Authorizer y Closer Simple puede estar funcionando como compensación manual de perfiles mal cortados
- también sugiere que el nombre Closer Simple es engañoso: por permisos reales, no es más simple, sino más híbrido

### Hallazgo transversal importante

El catálogo sigue cargando deuda técnica visible:

- important_concluir_tramite existe duplicado en us_permissions y ambos roles 3 y 15 cargan ambas entradas
- el script objetivo de unificación para Closer en admin/sqls/unify_acl_roles_permissions_prod.sql no coincide completamente con la fotografía viva del rol 3 ni con la del rol 15

### Conclusión operativa después del diff fino

- 13 vs 14 no deben fusionarse sin una decisión explícita sobre autorización vs asignación de gestor
- 3 vs 15 tampoco deben fusionarse a ciegas, porque 15 invade claramente terreno de paso 3
- el problema ya no es falta de evidencia, sino falta de definición canónica del perfil funcional de cada rol

### Siguiente paso recomendado después de este corte

El siguiente bloque lógico ya no es conseguir más evidencia, sino decidir diseño:

1. definir si 15 Closer Simple debe existir como rol híbrido o si debe perder todos los permisos de paso 3
2. definir si 13 Authorizer Editor debe conservar monitoreo y cancelados o si esos permisos fueron deriva accidental
3. preparar la tabla permiso actual -> permiso canónico nuevo usando este diff ya consolidado

## 14. Mapa de nomenclatura

Se dejó un mapa inicial de renombre en ACL_NOMENCLATURA_MAPA.md para trabajar la migración permiso actual -> permiso canónico sin mezclarlo con el análisis de roles.

## 13. Propuesta concreta: depurar 15 Closer Simple

Con la evidencia viva ya consolidada, la propuesta más defendible es esta:

- mantener a 15 Closer Simple como rol de cierre final
- quitarle de inmediato los permisos de paso 3 y el permiso de autorización hacia pagos
- no tocar todavía sus permisos de paso 4 en este corte, para evitar mezclar dos decisiones distintas en una sola intervención

### Recorte propuesto para este primer ajuste

Permisos a retirar de 15 Closer Simple en el primer corte:

- important_pasar_a_pagos
- section_pago_derechos
- section_linea_captura
- section_documentos_pago
- write_tramite_pago_derechos
- can_upload_dropzone_pago_derechos

### Permisos que se conservan en este primer corte

Se conservan por ahora, aunque merecen revisión posterior:

- can_upload_dropzone_pago_gestor
- editar_pago_gestor
- section_pago_gestor
- quick_action_pago_gestor
- quick_action_pago_gestor_add
- quick_action_pago_gestor_edit
- quick_action_pago_gestor_delete

Y se conserva el bloque final que sí es coherente con un closer:

- important_concluir_tramite
- important_cancelar_tramite
- section_final_costos
- list_cobro_cliente
- can_upload_dropzone_cobro_cliente
- upload_cobro_cliente
- quick_action_cobros_cliente
- quick_action_cobros_cliente_add
- quick_action_cobros_cliente_edit
- quick_action_cobros_cliente_delete

### Por qué este recorte es el adecuado

- important_pasar_a_pagos habilita una responsabilidad de authorizer, no de closer
- section_pago_derechos, section_linea_captura y section_documentos_pago abren visual y funcionalmente el paso 3
- write_tramite_pago_derechos y can_upload_dropzone_pago_derechos convierten ese acceso en capacidad real de edición y carga
- dejar fuera por ahora pago gestor evita tocar también la frontera paso 4 vs paso 5 en el mismo movimiento

### Impacto esperado en la aplicación

Después del recorte, Closer Simple ya no debería:

- ver ni operar la zona de pago de derechos
- subir documentos del paso 3
- autorizar el pase a pagos

Pero sí debería seguir pudiendo:

- entrar al proceso final
- ver costos finales
- trabajar cobro cliente
- concluir o cancelar cuando el flujo lo permita

### Riesgo principal

El mayor riesgo no está en el ACL sino en el apilamiento de roles.

Si hoy hay usuarios Authorizer Editor + Closer Simple o Authorizer Simple + Closer Simple, el comportamiento visible puede seguir permitiendo paso 3 porque ese acceso vendrá del otro rol.

Eso no invalida la depuración; solo implica que la validación debe hacerse sobre usuarios con rol 15 aislado y sobre usuarios apilados.

### Script propuesto

Se dejó un script de trabajo en admin/sqls/cleanup_closer_simple_step3.sql.

Ese script:

- previsualiza las filas a retirar
- elimina exactamente el bloque de permisos de paso 3 y autorización de Closer Simple
- muestra el set restante
- termina en ROLLBACK por seguridad

### QA mínimo recomendado

Antes de hacer COMMIT en base, validar al menos estos casos:

1. usuario solo con Closer Simple ya no puede autorizar ni editar pago de derechos
2. usuario solo con Closer Simple sí puede entrar a cobro cliente y operar cierre final
3. usuario Authorizer Editor + Closer Simple mantiene autorización por el rol 13, no por el 15
4. usuario Authorizer Simple + Closer Simple mantiene lo necesario para paso 4 si ese es el diseño deseado