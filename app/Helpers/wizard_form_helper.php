<?php

use CodeIgniter\Config\Services;

if (!function_exists('render_form_fields')) {
    function render_form_fields(array $fields, int $start, int $length): string
    {
        $formHtml = '';
        $sliceFields = array_slice($fields, $start, $length, true);
    
        foreach ($sliceFields as $field_name => $field_info) {
            $value = isset($field_info['value']) ? $field_info['value'] : set_value($field_name);
            $required = isset($field_info['required']) ? $field_info['required'] : "";
            $readonly = isset($field_info['readonly']) ? $field_info['readonly'] : "";
            $disabled = isset($field_info['disabled']) ? $field_info['disabled'] : "";
    
            if ($field_info['type'] === 'hidden') {
                $formHtml .= "<input type=\"hidden\" id=\"{$field_name}\" name=\"{$field_name}\" value=\"{$value}\">";
            } else {
                $formHtml .= "
                    <div class=\"mb-3 row\">
                        <label for=\"{$field_name}\" class=\"col-sm-4 col-form-label\">{$field_info['label']}</label>
                        <div class=\"col-sm-8\">";
                switch ($field_info['type']) {
                    case 'text':
                        $formHtml .= "<input type=\"text\" class=\"form-control\" id=\"{$field_name}\" name=\"{$field_name}\" value=\"{$value}\" {$required} {$readonly} {$disabled}>";
                        break;
                    case 'select':
                        $formHtml .= "<select class=\"form-control select2\" id=\"{$field_name}\" name=\"{$field_name}\" {$readonly} {$disabled}>";
                        foreach ($field_info['options'] as $option_value => $option_label) {
                            $selected = set_select($field_name, $option_value, $value == $option_value);
                            $formHtml .= "<option value=\"{$option_value}\" {$selected}>{$option_label}</option>";
                        }
                        $formHtml .= "</select>";
                        break;
                    case 'textarea':
                        $formHtml .= "<textarea class=\"form-control\" id=\"{$field_name}\" name=\"{$field_name}\" {$required} {$readonly} {$disabled}>{$value}</textarea>";
                        break;
                    case 'checkbox':
                        $checked = set_checkbox($field_name, '1', $value == '1');
                        $formHtml .= "<input type=\"checkbox\" class=\"form-check-input\" id=\"{$field_name}\" name=\"{$field_name}\" value=\"1\" {$checked} {$readonly} {$disabled}>";
                        break;
                    case 'radio':
                        if ($field_name === 'status') {
                            $activeChecked = set_radio('status', '1', $value == '1');
                            $inactiveChecked = set_radio('status', '0', $value == '0');
                            $formHtml .= "
                                <div class=\"form-check form-check-inline\">
                                    <input class=\"form-check-input\" type=\"radio\" name=\"status\" id=\"status_active\" value=\"1\" {$activeChecked} {$readonly} {$disabled}>
                                    <label class=\"form-check-label\" for=\"status_active\">Activo</label>
                                </div>
                                <div class=\"form-check form-check-inline\">
                                    <input class=\"form-check-input\" type=\"radio\" name=\"status\" id=\"status_inactive\" value=\"0\" {$inactiveChecked} {$readonly} {$disabled}>
                                    <label class=\"form-check-label\" for=\"status_inactive\">Inactivo</label>
                                </div>";
                        }
                        break;
                    case 'datetime':
                        $formHtml .= "<input type=\"text\" class=\"form-control datetime-picker\" id=\"{$field_name}\" name=\"{$field_name}\" value=\"{$value}\" {$disabled}>";
                        break;
                }
                $formHtml .= "
                            <div class=\"invalid-feedback\">" . Services::validation()->showError($field_name) . "</div>
                        </div>
                    </div>";
            }
        }
    
        return $formHtml;
    }
}
