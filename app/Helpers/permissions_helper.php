<?php
if (! function_exists('has_permission')) {
    function has_permission($required_permission, $user_permissions) {
        return in_array($required_permission, $user_permissions);
    }
}
if (! function_exists('is_super_admin')) {
    function is_super_admin(array $roles)
    {
        return in_array("Super Admin", $roles);
    }
}