<?php
if (!function_exists('render_full_form')) {
    /**
     * Renderiza un formulario completo con base en las variables proporcionadas.
     *
     * @param string $prefix_form El prefijo para los IDs y mensajes del formulario.
     * @param string $form_action La URL donde se enviará el formulario.
     * @param string $form_id El ID del formulario.
     * @param string $cancel_url La URL del botón de cancelar.
     * @param string $submit_permission Permiso necesario para habilitar el botón de guardar.
     * @param array $field_values Los campos del formulario para renderizar.
     * @param object $session Objeto de sesión para validar permisos.
     *
     * @return string El HTML completo del formulario.
     */
    function render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session)
    {
        $html = '';

        // Abrir formulario
        $html .= form_open($form_action, ['class' => 'form-horizontal', 'id' => $form_id]);

        // Respuestas de éxito y error
        $html .= '
            <div id="' . esc($prefix_form) . '_respuesta" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                <span id="' . esc($prefix_form) . '_mensaje"></span>
            </div>
            <div id="' . esc($prefix_form) . '_respuesta_error" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none;">
                <span id="' . esc($prefix_form) . '_mensaje_error"></span>
            </div>';

        // Renderizar los campos divididos en dos columnas
        $total_fields = count($field_values);
        $half_fields = ceil($total_fields / 2);

        $html .= '<div class="row">
                    <div class="col-md-6">
                        ' . render_form_fields($field_values, 0, $half_fields) . '
                    </div>
                    <div class="col-md-6">
                        ' . render_form_fields($field_values, $half_fields, $total_fields) . '
                    </div>
                  </div>';

        // Botones de acción
        $html .= '<div class="text-center mt-4" id="boton_autorizar">
                    <a href="' . esc($cancel_url) . '" class="btn btn-secondary ml-2">Cancelar</a>';

        if (has_permission($submit_permission, esc($session->get('user_permissions')), esc($session->get('user_roles')))) {
            $html .= ' <button type="submit" class="btn btn-primary">Guardar</button>';
        }

        $html .= '</div>';

        // Cerrar formulario
        $html .= form_close();

        return $html;
    }
}
?>
