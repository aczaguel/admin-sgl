<?php

/**
 * Roles y permisos (referencia rápida)
 *
 * Este proyecto usa 2 fuentes:
 * - Roles (session key: user_roles)
 * - Permisos (session key: user_permissions)
 *
 * Helpers clave:
 * - can_create_tramite($roles, $perms): alta de trámites.
 * - can_edit_tramite($roles, $perms): edición/mutaciones (bloquea roles read-only).
 *
 * Permisos más comunes en UI/rutas:
 * - Navegación/listados: menu_tramites, listar_tramite, listar_tramites_concluidos
 * - Trámite: create_tramite, read_tramite, read_final_tramite, editar_tramite
 * - Acciones: export_tramite, print_tramite, clone_tramite
 * - Sub-secciones: section_pago_derechos, section_pago_gestor, section_final_costos
 * - Mutaciones finas: editar_tramite_principal, editar_tramite_asociado, delete_tramite_asociado, editar_pago_gestor
 *
 * Expectativa por rol (lo que “debería” asignarse):
 * - Super Admin:
 *   - has_permission() devuelve true siempre; equivale a acceso total.
 * - Admin:
 *   - Suele tener acceso amplio por rol (legacy). Idealmente controlarlo solo por permisos.
 *   - Si debe editar, asignar editar_tramite (y los permisos finos/sections que apliquen).
 * - Starter (solo lectura para trámites):
 *   - Sí: menu_tramites, listar_tramite, create_tramite, read_tramite (opcional read_final_tramite).
 *   - No: editar_tramite (ni editar_tramite_* / delete_* / editar_* de módulos).
 *   - Nota: aunque por error tenga editar_tramite en perms, can_edit_tramite() lo bloquea.
 * - Executer (Rol 2):
 *   - Puede editar pasos 1–3 solo cuando el trámite NO está aprobado (ver tramite_is_aprobado_por_status()).
 *   - Requiere permisos como editar_tramite y los finos que apliquen.
 * - Authorizer Editor:
 *   - Puede autorizar (pasar a pagos / status 23) y después editar Paso 4 (Pago Gestor).
 *   - Una vez autorizado, no debe poder editar hacia atrás (pasos 1–3) (enforced server-side en Tramitesn).
 * - Authorizer Simple:
 *   - Puede editar Paso 4 (Pago Gestor) pero NO puede autorizar.
 * - Closer:
 *   - Se enfoca en el Paso 5 (Cobro a cliente / evidencias finales) y concluir trámite.
 *   - No requiere ver ni operar Paso 4 (Pago Gestor).
 * - Cliente / Viewer:
 *   - Se tratan como read-only por permisos (no asignar `editar_tramite` ni permisos de escritura).
 */

if (! function_exists('normalize_permission_list')) {
    function normalize_permission_list($value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return array_values($value);
        }

        if (is_string($value)) {
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $decoded = trim($decoded);

            if ($decoded === '') {
                return [];
            }

            $json = json_decode($decoded, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return array_values($json);
            }

            if (strpos($decoded, ',') !== false) {
                $parts = array_map('trim', explode(',', $decoded));
                $parts = array_filter($parts, static function ($p) {
                    return $p !== '';
                });
                return array_values($parts);
            }

            return [$decoded];
        }

        if (is_scalar($value)) {
            return [(string) $value];
        }

        return [];
    }
}

if (! function_exists('normalize_role_key')) {
    /**
     * Normaliza nombres de rol para comparaciones tolerantes a mayúsculas/espacios.
     * Ej: "Super Admin", "SuperAdmin", "superadmin" -> "superadmin".
     */
    function normalize_role_key($role): string
    {
        if (!is_string($role)) {
            return '';
        }

        $role = strtolower(trim($role));
        $role = preg_replace('/[\s_\-]+/', '', $role);
        return is_string($role) ? $role : '';
    }
}

if (! function_exists('has_permission')) {
    function has_permission($required_permission, $user_permissions, $roles) {
        $roles = normalize_permission_list($roles);
        if (is_super_admin($roles)) {
            return true;
        }

        $user_permissions = normalize_permission_list($user_permissions);
        return in_array($required_permission, $user_permissions, true);
    }
}

if (! function_exists('has_permission_strict')) {
    /**
     * Check estricto: NO aplica bypass por rol (por ejemplo Super Admin).
     * Útil para flags de UI que deben depender solo de permisos asignados.
     */
    function has_permission_strict($required_permission, $user_permissions): bool
    {
        $user_permissions = normalize_permission_list($user_permissions);
        return in_array($required_permission, $user_permissions, true);
    }
}

if (! function_exists('perm_audit_tag')) {
    /**
     * Renderiza una etiqueta de auditoría con el nombre del permiso.
     *
     * - Solo se imprime para Super Admin.
     * - La visibilidad la controla el Debug Toggle (localStorage.debugMode) vía JS.
     */
    function perm_audit_tag(string $permissionName, $session = null): string
    {
        $session = $session ?? session();
        $roles = normalize_permission_list($session->get('user_roles') ?? []);
        $perms = normalize_permission_list($session->get('user_permissions') ?? []);
        if (!has_permission('debug_perm_audit_tags', $perms, $roles)) {
            return '';
        }

        $perm = trim($permissionName);
        if ($perm === '') {
            return '';
        }

        $safe = esc($perm);
        return '<span class="sgl-perm-audit" data-perm="' . $safe . '" style="display:none">&gt;&gt;permiso: ' . $safe . '&lt;&lt;</span>';
    }
}

if (! function_exists('session_roles_perms')) {
    /**
     * Obtiene roles y permisos desde sesión, normalizados como arrays.
     *
     * @return array{0: array, 1: array} [$roles, $perms]
     */
    function session_roles_perms($session = null): array
    {
        $session = $session ?? session();
        $roles = normalize_permission_list($session->get('user_roles') ?? []);
        $perms = normalize_permission_list($session->get('user_permissions') ?? []);
        return [$roles, $perms];
    }
}

if (! function_exists('write_permission_for_tramite_step')) {
    /**
     * Devuelve el nombre del permiso write_* que controla escritura por paso.
     * Pasos fuera de 1-3 regresan null.
     */
    function write_permission_for_tramite_step(int $step): ?string
    {
        switch ((int) $step) {
            case 1:
                return 'write_tramite_datos_tramite';
            case 2:
                return 'write_tramite_asigna_gestor';
            case 3:
                return 'write_tramite_pago_derechos';
            default:
                return null;
        }
    }
}

if (! function_exists('can_write_tramite_step')) {
    /**
     * Indica si el usuario puede escribir/modificar el paso indicado (1-3) del trámite.
     * Para pasos fuera de 1-3 regresa true.
     */
    function can_write_tramite_step(int $step, $perms, $roles): bool
    {
        $permName = write_permission_for_tramite_step($step);
        if ($permName === null) {
            return true;
        }
        return has_permission($permName, $perms, $roles);
    }
}

if (! function_exists('can_create_tramite')) {
    function can_create_tramite($roles, $perms): bool
    {
        // Control 100% por permisos (Super Admin pasa vía has_permission()).
        return has_permission('create_tramite', $perms, $roles);
    }
}

if (! function_exists('can_edit_tramite')) {
    function can_edit_tramite($roles, $perms): bool
    {
        // Para edición, el permiso manda; Super Admin pasa vía has_permission().
        return has_permission('editar_tramite', $perms, $roles);
    }
}
if (! function_exists('is_super_admin')) {
    function is_super_admin($roles)
    {
        $roles = normalize_permission_list($roles);
        foreach ($roles as $role) {
            if (normalize_role_key($role) === 'superadmin') {
                return true;
            }
        }
        return false;
    }
}

if (! function_exists('is_admin')) {
    function is_admin($roles)
    {
        $roles = normalize_permission_list($roles);
        foreach ($roles as $role) {
            if (normalize_role_key($role) === 'admin') {
                return true;
            }
        }
        return false;
    }
}

if (! function_exists('is_client')) {
    function is_client($roles)
    {
        $roles = normalize_permission_list($roles);
        return in_array("Cliente", $roles, true);
    }
}

if (! function_exists('is_starter')) {
    function is_starter($roles)
    {
        $roles = normalize_permission_list($roles);
        $roRoles = ['Starter'];
        foreach ($roRoles as $role) {
            if (in_array($role, $roles)) {
                return true; // Retorna true si al menos uno de los roles está presente
            }
        }
        return false; // Retorna false si ninguno de los roles está presente
    }
}

if (! function_exists('is_executer')) {
    function is_executer($roles)
    {
        $roles = normalize_permission_list($roles);
        $roRoles = ['Executer'];
        foreach ($roRoles as $role) {
            if (in_array($role, $roles)) {
                return true; // Retorna true si al menos uno de los roles está presente
            }
        }
        return false; // Retorna false si ninguno de los roles está presente
    }
}

if (! function_exists('is_closer')) {
    function is_closer($roles): bool
    {
        $roles = normalize_permission_list($roles);
        return in_array('Closer', $roles, true);
    }
}

if (! function_exists('is_authorizer_editor')) {
    function is_authorizer_editor($roles): bool
    {
        $roles = normalize_permission_list($roles);
        return in_array('Authorizer Editor', $roles, true);
    }
}

if (! function_exists('is_authorizer_simple')) {
    function is_authorizer_simple($roles): bool
    {
        $roles = normalize_permission_list($roles);
        return in_array('Authorizer Simple', $roles, true);
    }
}

if (! function_exists('can_authorize_tramite')) {
    /**
     * Autorizar (pasar a pagos / status 23).
     * - Requiere permiso important_pasar_a_pagos.
     * - El permiso manda; Super Admin pasa vía has_permission().
     */
    function can_authorize_tramite($roles, $perms): bool
    {
        $roles = normalize_permission_list($roles);
        $perms = normalize_permission_list($perms);

        return has_permission('important_pasar_a_pagos', $perms, $roles);
    }
}

if (! function_exists('is_upper_role')) {
    function is_upper_role($roles)
    {
        $roles = normalize_permission_list($roles);
        $roRoles = ['Executer', 'Super Admin', 'Admin'];
        foreach ($roRoles as $role) {
            if (in_array($role, $roles)) {
                return true; // Retorna true si al menos uno de los roles está presente
            }
        }
        return false; // Retorna false si ninguno de los roles está presente
    }
}
if (!function_exists('puede_modificar')) {
    function puede_modificar($roles, $estado, $campo, $reembolso_status_id = null, $cobro_status_id = null, $step) {
        // Falta agregar la logica para los roles que pueden visualizar botones y campos
            
        // Obtener los campos editables
        $editable_fields = get_editable_fields_by_step($estado, $reembolso_status_id, $cobro_status_id, $step);

        // Verificar si el campo es editable
        if (in_array($campo, $editable_fields)) {
            return true;
        }

        // Si el trámite no está concluido (20) ni cancelado (21), todos los campos son editables por defecto
        if (!in_array($estado, [20, 21])) {
            return false;   
        }

        // Si el trámite está concluido (20) o cancelado (21), aplicar las reglas específicas
        return false;
    }
}

if (!function_exists('get_editable_fields_by_step')) {
    function get_editable_fields_by_step($estado, $reembolso_status_id, $cobro_status_id, $step) {
        $editable_fields = [];
        // if($step < 4){
        //     return $editable_fields;
        // }    
        
        // Mapeo de estados del trámite => steps (para usar estatus_editable)
        $arr_status = [
            11 => 1,  // Paso 1
            22 => 2,  // Paso 2
            25 => 3,  // Paso 3 (25, 26, 27)
            26 => 3,
            27 => 3,
            23 => 4,  // Paso 4 (reembolso)
            28 => 5,  // Paso 5 (cobro)
            20 => 6,  // Paso 6 (concluido)
            21 => 7   // Paso 7 (cancelado)   // Paso 5 (cobro)
        ];

        // Si el trámite está concluido (20) o cancelado (21)
        if (in_array($estado, [20])) {
            // Excepción: Reembolso en proceso (22) aunque el trámite esté concluido (20)
            if (in_array($reembolso_status_id, [21, 22]) && $estado == 20) {
                $editable_fields[] = 'reembolso_status_id';
                $editable_fields[] = 'deposito_gestor';
                $editable_fields[] = 'evidencias_finales_gestor';
                $editable_fields[] = 'upload_pago_gestor';
                $editable_fields[] = 'botones';
            }

            // Excepción: Cobro en proceso (21 o 22) aunque el trámite esté concluido (20)
            if (in_array($cobro_status_id, [21, 22]) && $estado == 20) {
                $editable_fields[] = 'cobro_status_id';
                $editable_fields[] = 'evidencias_finales_cliente';
                $editable_fields[] = 'upload_cobro_cliente';
                $editable_fields[] = 'botones';
            }

            // Si está cancelado (21), ningún campo es editable
            if ($estado == 21) {
                return [];
            }

            return $editable_fields;
        }

        // --- Lógica para trámites NO concluidos/cancelados ---
        // Paso actual del trámite (según $estado)
        // echo "<br>Estado: $estado, Step: $step";
        $current_step = $arr_status[$estado] ?? 0;
        // Campos base editables (dependiendo del paso actual)
        // if ($step >= 4 && in_array($reembolso_status_id, [21, 22])) {  // Si estamos en paso 4 o superior
        //     echo "<br>Estado 1: $estado, Step: $step";
        //     $editable_fields[] = 'reembolso_status_id';
        //     $editable_fields[] = 'deposito_gestor';
        //     $editable_fields[] = 'evidencias_finales_gestor';
        //     $editable_fields[] = 'upload_pago_gestor';
        //     $editable_fields[] = 'botones';
        // }else
        if ($step == 4 && $current_step >= 4) {  // Si estamos en paso 4 o superior
            $editable_fields[] = 'costo_tramite';
            $editable_fields[] = 'num_factura_gestor';
            $editable_fields[] = 'impuesto_gestoria';
            $editable_fields[] = 'gestoria_comision';
            $editable_fields[] = 'costo_paqueteria';
            $editable_fields[] = 'reembolso_status_id';
            $editable_fields[] = 'deposito_gestor';
            $editable_fields[] = 'evidencias_finales_gestor';
            $editable_fields[] = 'upload_pago_gestor';
            $editable_fields[] = 'botones';
        }

        if ($current_step >= 5) {  // Si estamos en paso 5
            $editable_fields[] = 'cobro_status_id';
            $editable_fields[] = 'evidencias_finales_cliente';
            $editable_fields[] = 'upload_cobro_cliente';
            $editable_fields[] = 'botones';
        }

        return $editable_fields;
    }
}

if (!function_exists('estatus_editable')) {
    function estatus_editable($step, $estado) {
        // Mapeo de estados (clave) => steps (valor)
        $arr_status = [
            11 => 1,  // Estado 11 → Step 1
            22 => 2,  // Estado 22 → Step 2
            25 => 3,  // Estados 25, 26, 27 → Step 3
            26 => 3,
            27 => 3,
            23 => 4,  // Estado 23 → Step 4
            28 => 5,  // Paso 5 (cobro)
            20 => 6,  // Paso 6 (concluido)
            21 => 7   // Paso 7 (cancelado)   // Estado 28 → Step 5
        ];

        // Si el estado no existe en el mapeo, considerar NO editable por defecto
        if (!isset($arr_status[$estado])) {
            return false;
        }

        // Obtener el step del estado actual
        $step_estado_actual = $arr_status[$estado];

        // ¿El step evaluado es >= al step actual? → Editable
        
        return ($step >= $step_estado_actual);

    }
}

if (!function_exists('puede_editar_modulo')) {
    function puede_editar_modulo($roles, $estado, $campo, $reembolso_status_id = null, $cobro_status_id = null, $current_step, $perms = null) {
        // Override explícito por permisos; Super Admin pasa vía has_permission().
        if (has_permission('override_puede_editar_modulo', $perms, $roles)) {
            return true;
        }

        if($estado == 21){
            return false;
        }
        
        // Obtener el step del estado actual desde el mapeo
        $arr_status = [
            11 => 1,  // Paso 1
            22 => 2,  // Paso 2
            25 => 3,  // Paso 3 (25, 26, 27)
            26 => 3,
            27 => 3,
            23 => 4,  // Paso 4 (reembolso)
            28 => 5,   // Paso 5 (cobro)
            20 => 6,  // Paso 6 (concluido)
            21 => 7   // Paso 7 (cancelado)
        ];

        // Si el estado no existe en el mapeo, no es editable
        if (!isset($arr_status[$estado])) {
            return false;
        }

        // Obtener el step del estado actual
        $step_estado_db = $arr_status[$estado];

        // Si el módulo actual es menor que el step del estado, bloquear edición, solo cuando sea la primera etapa
        if ($current_step < $step_estado_db && $step_estado_db > 3) {
            // Verificar si el campo es editable según las reglas de get_editable_fields
            $editable_fields = get_editable_fields_by_step($estado, $reembolso_status_id, $cobro_status_id, $current_step);
            return in_array($campo, $editable_fields);
        }
        // Si el módulo actual es igual o mayor al step del estado, permitir edición
        return true;
    }
}