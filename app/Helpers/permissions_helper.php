<?php
if (! function_exists('has_permission')) {
    function has_permission($required_permission, $user_permissions, $roles) {
        if (is_super_admin($roles)) {
            return true;
        }
        return in_array($required_permission, $user_permissions);
    }
}
if (! function_exists('is_super_admin')) {
    function is_super_admin(array $roles)
    {
        return in_array("Super Admin", $roles);
    }
}

if (! function_exists('is_admin')) {
    function is_admin(array $roles)
    {
        return in_array("Admin", $roles);
    }
}

if (! function_exists('is_client')) {
    function is_client(array $roles)
    {
        return in_array("Cliente", $roles);
    }
}

if (! function_exists('is_read_only')) {
    function is_read_only(array $roles)
    {
        $roRoles = ['Starter', 'Cliente', 'Viewer', 'Executer'];
        foreach ($roRoles as $role) {
            if (in_array($role, $roles)) {
                return true; // Retorna true si al menos uno de los roles está presente
            }
        }
        return false; // Retorna false si ninguno de los roles está presente
    }
}

if (! function_exists('is_starter')) {
    function is_starter(array $roles)
    {
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
    function is_executer(array $roles)
    {
        $roRoles = ['Executer'];
        foreach ($roRoles as $role) {
            if (in_array($role, $roles)) {
                return true; // Retorna true si al menos uno de los roles está presente
            }
        }
        return false; // Retorna false si ninguno de los roles está presente
    }
}

if (! function_exists('is_upper_role')) {
    function is_upper_role(array $roles)
    {
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

        // Si es cliente definitivamente no puede modificar
        if(is_client($roles)){
            return false;
        }
            
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
        if($step == 4 && $current_step == 5) {  // Si estamos en paso 4
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
    function puede_editar_modulo($roles, $estado, $campo, $reembolso_status_id = null, $cobro_status_id = null, $current_step) {
        // Si es cliente definitivamente no puede modificar
        if(is_client($roles)){
            return false;
        }
        
        if(is_admin($roles)){
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