<?php

use CodeIgniter\Config\Services;

if (!function_exists('render_form_fields')) {
    /**
     * Renderiza un conjunto de campos de formulario a partir de un array de configuración.
     *
     * @param array $fields Array de configuración de campos.
     * @param int $start Índice de inicio de los campos.
     * @param int $length Número de campos a renderizar.
     * @return string HTML de los campos generados.
     */
    function render_form_fields(array $fields, int $start, int $length, $session, $tra_status_id, $reembolso_status_id, $cobro_status_id, $step): string
    {
        helper(['permissions']);

        $formHtml = '';
        $sliceFields = array_slice($fields, $start, $length, true);

        [$roles, $perms] = session_roles_perms($session);
        $canWriteStep = can_write_tramite_step((int) $step, $perms, $roles);

        foreach ($sliceFields as $field_name => $field_info) {
            if ($field_info['type'] === 'hr') {
                $formHtml .= "<hr>";
                continue;
            }
            if ($field_info['type'] === 'divider') {
                $id = isset($field_info['id']) ? "id='{$field_info['id']}'" : '';
                $formHtml .= "<div {$id} class='form-divider'></div>";
                continue;
            }

            $value = $field_info['value'] ?? set_value($field_name);
            $required = $field_info['required'] ?? false ? 'required' : '';
            $readonly = $field_info['readonly'] ?? false ? 'readonly' : '';
            $disabled = $field_info['disabled'] ?? false ? 'disabled' : '';
            
            if(isset($field_info['just_read'])) {
                $can_edit = !$field_info['just_read'];
            } else {
                $can_edit = puede_editar_modulo($session->get('user_roles'), $tra_status_id, $field_name, $reembolso_status_id, $cobro_status_id, $step);
            }

            // Gate global por paso (1-3): sin permiso write_* todo queda readonly.
            if (!$canWriteStep) {
                $can_edit = false;
            }

            if (!$can_edit) {
                $readonly = 'readonly';
                $disabled = 'disabled';
            }

            if ($field_info['type'] === 'hidden') {
                // Campo oculto
                $formHtml .= "<input type=\"hidden\" id=\"{$field_name}\" name=\"{$field_name}\" value=\"{$value}\">";
            } else {
                // Renderizar campos visibles
                $formHtml .= "
                    <div class=\"mb-3 row\">
                        <label for=\"{$field_name}\" class=\"col-sm-4 col-form-label\">{$field_info['label']}</label>
                        <div class=\"col-sm-8\">";

                // Lógica para los diferentes tipos de campo
                switch ($field_info['type']) {
                    case 'text':
                    case 'number':
                        $formHtml .= render_input($field_name, $field_info['type'], $value, $required, $readonly, $disabled, $field_info);
                        break;
                    case 'select':
                        $formHtml .= render_select($field_name, $field_info, $value, $readonly, $disabled);
                        break;
                    case 'textarea':
                        $formHtml .= render_textarea($field_name, $value, $required, $readonly, $disabled, $field_info);
                        break;
                    case 'checkbox':
                        $formHtml .= render_checkbox($field_name, $value, $readonly, $disabled);
                        break;
                    case 'radio':
                        $formHtml .= render_radio($field_name, $field_info, $value, $readonly, $disabled);
                        break;
                    case 'datetime':
                        $formHtml .= render_datetime($field_name, $value, $disabled);
                        break;
                    default:
                        $formHtml .= '';
                        break;
                }

                // Mensaje de validación
                $formHtml .= "
                            <div class=\"invalid-feedback\">" . \Config\Services::validation()->showError($field_name) . "</div>
                        </div>
                    </div>";
            }
        }

        return $formHtml;
    }

    // Funciones auxiliares para renderizar los diferentes tipos de campos
    function render_input(string $name, string $type, string $value, string $required, string $readonly, string $disabled, array $field_info = []): string
    {
        $step = $type === 'number' ? 'step="any"' : '';
        $maxlength = isset($field_info['maxlength']) ? 'maxlength="' . (int) $field_info['maxlength'] . '"' : '';
        return "<input type=\"{$type}\" class=\"form-control {$name}\" id=\"{$name}\" name=\"{$name}\" value=\"{$value}\" {$step} {$maxlength} {$required} {$readonly} {$disabled}>";
    }

    function render_select(string $name, array $field_info, string $value, string $readonly, string $disabled): string
    {
        $options = $field_info['options'] ?? [];
        $useNative = !empty($field_info['native']);
        $extraClass = trim((string) ($field_info['class'] ?? ''));
        $baseClass = $useNative ? 'form-control form-control-sm' : 'form-control select2';
        $classAttr = trim($baseClass . ' ' . $extraClass . ' ' . $name);

        $selectHtml = "<select class=\"{$classAttr}\" id=\"{$name}\" name=\"{$name}\" {$readonly} {$disabled}>";
        $selectHtml .= "<option value=\"\">Seleccione</option>";
        foreach ($options as $option_value => $option_label) {
            $selected = set_select($name, $option_value, $value == $option_value);
            $selectHtml .= "<option value=\"{$option_value}\" {$selected}>{$option_label}</option>";
        }
        $selectHtml .= "</select>";
        return $selectHtml;
    }

    function render_textarea(string $name, string $value, string $required, string $readonly, string $disabled, array $field_info = []): string
    {
        $maxlength = isset($field_info['maxlength']) ? 'maxlength="' . (int) $field_info['maxlength'] . '"' : '';
        return "<textarea class=\"form-control {$name}\" id=\"{$name}\" name=\"{$name}\" {$maxlength} {$required} {$readonly} {$disabled}>{$value}</textarea>";
    }

    function render_checkbox(string $name, string $value, string $readonly, string $disabled): string
    {
        $checked = set_checkbox($name, '1', $value == '1');
        return "<input type=\"checkbox\" class=\"form-check-input {$name}\" id=\"{$name}\" name=\"{$name}\" value=\"1\" {$checked} {$readonly} {$disabled}>";
    }

    function render_radio(string $name, array $field_info, string $value, string $readonly, string $disabled): string
    {
        if ($name !== 'status') {
            return '';
        }
        $activeChecked = set_radio('status', '1', $value == '1');
        $inactiveChecked = set_radio('status', '0', $value == '0');
        return "
            <div class=\"form-check form-check-inline\">
                <input class=\"form-check-input {$name}\" type=\"radio\" name=\"status\" id=\"status_active\" value=\"1\" {$activeChecked} {$readonly} {$disabled}>
                <label class=\"form-check-label\" for=\"status_active\">Activo</label>
            </div>
            <div class=\"form-check form-check-inline\">
                <input class=\"form-check-input {$name}\" type=\"radio\" name=\"status\" id=\"status_inactive\" value=\"0\" {$inactiveChecked} {$readonly} {$disabled}>
                <label class=\"form-check-label\" for=\"status_inactive\">Inactivo</label>
            </div>";
    }
    function render_datetime(string $name, string $value, string $disabled): string
    {
        return "<input type=\"text\" class=\"form-control datetime-picker\" id=\"{$name}\" name=\"{$name}\" value=\"{$value}\" {$disabled}>";
    }
}
