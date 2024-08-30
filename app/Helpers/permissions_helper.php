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
        $roRoles = ['Starter', 'Cliente', 'Viewer'];
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

