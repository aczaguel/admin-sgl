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
    function render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $tra_status_id, $reembolso_status_id, $cobro_status_id, $step)
    {
        $html = '';

        helper(['permissions']);
        [$roles, $perms] = session_roles_perms($session);
        $canWriteStep = can_write_tramite_step((int) $step, $perms, $roles);

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
                        ' . render_form_fields($field_values, 0, $half_fields, $session, $tra_status_id, $reembolso_status_id, $cobro_status_id, $step) . '
                    </div>
                    <div class="col-md-6">
                        ' . render_form_fields($field_values, $half_fields, $total_fields, $session, $tra_status_id, $reembolso_status_id, $cobro_status_id, $step) . '
                    </div>
                </div>';

        // Botones de acción
        $canSubmitForm = false;
        if (puede_editar_modulo($session->get('user_roles'), $tra_status_id, 'botones', $reembolso_status_id, $cobro_status_id, $step)) {
            if ((int) $step === 5 && $submit_permission === 'editar_final') {
                $canSubmitForm = has_permission('section_final_costos', $session->get('user_permissions'), $session->get('user_roles'));
            } else {
                $canSubmitForm = $canWriteStep && has_permission($submit_permission, $session->get('user_permissions'), $session->get('user_roles'));
            }

            if ($canSubmitForm) {
                $html .= '<div class="button-group" id="boton_autorizar">
                            <a href="' . esc($cancel_url) . '" class="btn-wizard btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a> 
                            <button type="submit" class="btn-wizard btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>';
            }
        }

        // Cerrar formulario
        $html .= form_close();

        return $html;
    }
}
?>
