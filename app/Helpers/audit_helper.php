<?php

/**
 * ============================================================================
 * HELPER DE AUDITORÍA PARA TRÁMITES
 * ============================================================================
 * Sistema completo de logging para capturar todos los cambios en trámites
 * 
 * Uso:
 *   log_tramite_change($tramiteId, 'update', 'tramite', 'Cambió placas');
 *   log_tramite_upload($tramiteId, 'evidencias', 'INE_Frontal.jpg');
 *   get_tramite_audit_log($tramiteId);
 * ============================================================================
 */

if (!function_exists('log_tramite_change')) {
    /**
     * Registra un cambio en el trámite
     * 
     * @param int $tramiteId ID del trámite
     * @param string $action Tipo de acción: insert, update, delete, upload, status_change
     * @param string $entityType Tabla afectada: tramite, tra_doc_status, tra_evidencias, etc.
     * @param string $description Descripción legible del cambio
     * @param string|null $fieldName Campo específico modificado
     * @param mixed $oldValue Valor anterior
     * @param mixed $newValue Valor nuevo
     * @param array|null $metadata Datos adicionales (archivos, IPs, etc.)
     * @return bool
     */
    function log_tramite_change(
        int $tramiteId,
        string $action,
        string $entityType,
        string $description,
        ?string $fieldName = null,
        $oldValue = null,
        $newValue = null,
        ?array $metadata = null
    ): bool {
        try {
            $db = \Config\Database::connect();
            $session = session();
            
            // Obtener información del usuario
            $userId = $session->get('id') ?? 0;
            $firstname = $session->get('firstname') ?? '';
            $midname = $session->get('midname') ?? '';
            $lastname = $session->get('lastname') ?? '';
            $username = trim($firstname . ' ' . $midname . ' ' . $lastname);
            if (empty($username)) {
                $username = 'Sistema';
            }
            $userEmail = $session->get('email') ?? null;
            
            // Obtener folio
            $tramite = $db->table('tramite')->select('folio')->where('id', $tramiteId)->get()->getRowArray();
            $folio = $tramite['folio'] ?? null;
            
            // Obtener IP y User Agent
            $request = \Config\Services::request();
            $ipAddress = $request->getIPAddress();
            $userAgent = $request->getUserAgent()->getAgentString();
            
            // Convertir valores a string para almacenamiento
            $oldValueStr = is_array($oldValue) || is_object($oldValue) ? json_encode($oldValue) : (string)$oldValue;
            $newValueStr = is_array($newValue) || is_object($newValue) ? json_encode($newValue) : (string)$newValue;
            
            // Preparar metadata JSON
            $metadataJson = $metadata ? json_encode($metadata) : null;
            
            // Insertar en log
            $data = [
                'tramite_id' => $tramiteId,
                'folio' => $folio,
                'user_id' => $userId,
                'username' => $username,
                'user_email' => $userEmail,
                'action' => $action,
                'entity_type' => $entityType,
                'description' => $description,
                'field_name' => $fieldName,
                'old_value' => $oldValueStr,
                'new_value' => $newValueStr,
                'metadata' => $metadataJson,
                'ip_address' => $ipAddress,
                'user_agent' => substr($userAgent, 0, 255),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $db->table('tramite_audit_log')->insert($data);
            
            // Actualizar último modificador en tramite (si el esquema lo soporta)
            if ($result) {
                $setParts = [];
                $params = [];

                if ($db->fieldExists('last_modified_by', 'tramite')) {
                    $setParts[] = 'last_modified_by = ?';
                    $params[] = $userId;
                }

                if ($db->fieldExists('last_modified_at', 'tramite')) {
                    $setParts[] = 'last_modified_at = NOW()';
                }

                if ($db->fieldExists('modification_count', 'tramite')) {
                    $setParts[] = 'modification_count = COALESCE(modification_count, 0) + 1';
                }

                if (!empty($setParts)) {
                    $params[] = $tramiteId;
                    $db->query('UPDATE tramite SET ' . implode(', ', $setParts) . ' WHERE id = ?', $params);
                }
            }
            
            return (bool)$result;
            
        } catch (\Exception $e) {
            log_message('error', 'Error en log_tramite_change: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('log_tramite_upload')) {
    /**
     * Registra una subida de archivo
     * 
     * @param int $tramiteId ID del trámite
     * @param string $entityType Tipo: evidencias, documentos, evidencias_finales
     * @param string $filename Nombre del archivo
     * @param string|null $description Descripción opcional
     * @return bool
     */
    function log_tramite_upload(
        int $tramiteId,
        string $entityType,
        string $filename,
        ?string $description = null
    ): bool {
        $desc = $description ?? "Subida de archivo: $filename";
        return log_tramite_change(
            $tramiteId,
            'upload',
            $entityType,
            $desc,
            'archivo',
            null,
            $filename,
            ['filename' => $filename, 'size' => filesize($filename) ?? 0]
        );
    }
}

if (!function_exists('log_tramite_status_change')) {
    /**
     * Registra un cambio de estatus
     * 
     * @param int $tramiteId ID del trámite
     * @param int $oldStatusId ID del estatus anterior
     * @param int $newStatusId ID del nuevo estatus
     * @return bool
     */
    function log_tramite_status_change(
        int $tramiteId,
        int $oldStatusId,
        int $newStatusId
    ): bool {
        $db = \Config\Database::connect();
        
        // Obtener nombres de estatus
        $oldStatus = $db->table('tra_status')->select('tra_status')->where('id', $oldStatusId)->get()->getRowArray();
        $newStatus = $db->table('tra_status')->select('tra_status')->where('id', $newStatusId)->get()->getRowArray();
        
        $oldName = $oldStatus['tra_status'] ?? "Estatus #$oldStatusId";
        $newName = $newStatus['tra_status'] ?? "Estatus #$newStatusId";

        $logged = log_tramite_change(
            $tramiteId,
            'status_change',
            'tramite',
            "Cambio de estatus: '$oldName' → '$newName'",
            'tra_status_id',
            $oldName,
            $newName
        );

        if ($oldStatusId !== $newStatusId) {
            try {
                $externalTramiteService = new \App\Services\ExternalTramiteService($db);
                $externalTramiteService->queueStatusChangedEventIfTracked($tramiteId, $oldStatusId, $newStatusId);
            } catch (\Throwable $e) {
                log_message('error', 'No se pudo encolar webhook de cambio de estatus para trámite ' . $tramiteId . ': ' . $e->getMessage());
            }
        }

        return $logged;
    }
}

if (!function_exists('get_tramite_audit_log')) {
    /**
     * Obtiene el log de auditoría de un trámite
     * 
     * @param int $tramiteId ID del trámite
     * @param int|null $limit Número de registros (null = todos)
     * @return array
     */
    function get_tramite_audit_log(int $tramiteId, ?int $limit = null): array
    {
        $db = \Config\Database::connect();

        $hasNew = $db->tableExists('tramite_audit_log');
        $hasLegacy = $db->tableExists('tramite_auditoria');

        if (!$hasNew && !$hasLegacy) {
            return [];
        }

        // Intentar primero con la tabla nueva si existe y tiene registros
        if ($hasNew) {
            $row = $db->query('SELECT COUNT(*) AS c FROM tramite_audit_log WHERE tramite_id = ?', [$tramiteId])->getRowArray();
            $countNew = $row ? (int) $row['c'] : 0;
            if ($countNew > 0) {
                $builder = $db->table('tramite_audit_log');
                $builder->where('tramite_id', $tramiteId);
                $builder->orderBy('created_at', 'DESC');
                if ($limit) {
                    $builder->limit($limit);
                }
                return $builder->get()->getResultArray();
            }
        }

        // Fallback: usar tabla legacy tramite_auditoria (si existe)
        if ($hasLegacy) {
            $sql = "SELECT
                    a.tramite_id,
                    a.usuario_modificacion AS user_id,
                    CONCAT(u.firstname, ' ', IFNULL(u.midname, ''), ' ', u.lastname) AS username,
                    u.email AS user_email,
                    'update' AS action,
                    'tramite' AS entity_type,
                    CONCAT('Cambio en ', a.campo_modificado) AS description,
                    a.campo_modificado AS field_name,
                    a.valor_anterior AS old_value,
                    a.valor_nuevo AS new_value,
                    NULL AS ip_address,
                    NULL AS user_agent,
                    a.fecha_modificacion AS created_at
                FROM tramite_auditoria a
                LEFT JOIN users u ON u.id = a.usuario_modificacion
                WHERE a.tramite_id = ?
                ORDER BY a.fecha_modificacion DESC, a.id DESC";

            if ($limit) {
                $sql .= ' LIMIT ' . (int) $limit;
            }

            return $db->query($sql, [$tramiteId])->getResultArray();
        }

        return [];
    }
}

if (!function_exists('get_tramite_last_modifier')) {
    /**
     * Obtiene información del último usuario que modificó el trámite
     * 
     * @param int $tramiteId ID del trámite
     * @return array|null ['user_id', 'username', 'modified_at', 'total_changes']
     */
    function get_tramite_last_modifier(int $tramiteId): ?array
    {
        try {
            $db = \Config\Database::connect();

            $hasLastModifiedBy = $db->fieldExists('last_modified_by', 'tramite');
            $hasLastModifiedAt = $db->fieldExists('last_modified_at', 'tramite');

            // Preferir columnas denormalizadas si existen
            if ($hasLastModifiedBy && $hasLastModifiedAt) {
                $totalChangesExpr = $db->fieldExists('modification_count', 'tramite')
                    ? 't.modification_count'
                    : 'NULL';

                $result = $db->query(
                    "SELECT 
                        t.last_modified_by as user_id,
                        CONCAT(u.firstname, ' ', IFNULL(u.midname, ''), ' ', u.lastname) as username,
                        t.last_modified_at as modified_at,
                        {$totalChangesExpr} as total_changes
                    FROM tramite t
                    LEFT JOIN users u ON t.last_modified_by = u.id
                    WHERE t.id = ?",
                    [$tramiteId]
                )->getRowArray();

                return $result ?: null;
            }

            // Fallback: calcular desde el log de auditoría (no requiere ALTER TABLE en tramite)
            $result = $db->query(
                "SELECT
                    l.user_id as user_id,
                    l.username as username,
                    l.created_at as modified_at,
                    s.total_changes as total_changes
                FROM tramite_audit_log l
                CROSS JOIN (
                    SELECT COUNT(*) as total_changes
                    FROM tramite_audit_log
                    WHERE tramite_id = ?
                ) s
                WHERE l.tramite_id = ?
                ORDER BY l.created_at DESC, l.id DESC
                LIMIT 1",
                [$tramiteId, $tramiteId]
            )->getRowArray();

            return $result ?: null;
        } catch (\Throwable $e) {
            log_message('error', 'Error en get_tramite_last_modifier: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('get_tramite_audit_summary')) {
    /**
     * Obtiene un resumen de cambios por tipo de acción
     * 
     * @param int $tramiteId ID del trámite
     * @return array
     */
    function get_tramite_audit_summary(int $tramiteId): array
    {
        $db = \Config\Database::connect();
        $result = $db->query("
            SELECT 
                action,
                COUNT(*) as count,
                MAX(created_at) as last_occurrence
            FROM tramite_audit_log
            WHERE tramite_id = ?
            GROUP BY action
            ORDER BY count DESC
        ", [$tramiteId])->getResultArray();
        
        return $result;
    }
}

if (!function_exists('compare_tramite_data')) {
    /**
     * Compara dos arrays de datos y devuelve las diferencias
     * 
     * @param array $oldData Datos antiguos
     * @param array $newData Datos nuevos
     * @param array $ignoreFields Campos a ignorar
     * @return array ['field' => ['old' => value, 'new' => value]]
     */
    function compare_tramite_data(array $oldData, array $newData, array $ignoreFields = []): array
    {
        $changes = [];
        $ignoreFields = array_merge($ignoreFields, ['updated_at', 'created_at', 'modification_count']);
		
        foreach ($newData as $field => $newValue) {
            if (in_array($field, $ignoreFields)) {
                continue;
            }
			
            if (!isset($oldData[$field])) {
                $changes[$field] = [
                    'old' => null, 
                    'new' => resolve_field_name($field, $newValue)
                ];
            } elseif ($oldData[$field] != $newValue) {
                $changes[$field] = [
                    'old' => resolve_field_name($field, $oldData[$field]), 
                    'new' => resolve_field_name($field, $newValue)
                ];
            }
        }
		
        return $changes;
    }
}

if (!function_exists('resolve_field_name')) {
    /**
     * Resuelve el nombre de un campo si es un ID de relación
     * 
     * @param string $fieldName Nombre del campo
     * @param mixed $value Valor del campo
     * @return string Valor con nombre resuelto si aplica
     */
    function resolve_field_name(string $fieldName, $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        
        // Mapeo de campos ID a sus tablas y columnas de nombre
        $fieldMapping = [
            'gestor_id' => ['table' => 'ges_gestor', 'column' => 'nombre'],
            'empresa_gestora_id' => ['table' => 'ges_empresa_gestora', 'column' => 'razon_social'],
            'cli_directo_id' => ['table' => 'cli_directo', 'column' => 'nombre'],
            'cli_directo_ejecutivo_id' => ['table' => 'cli_directo_ejecutivo', 'column' => 'nombre'],
            'entidad_id' => ['table' => 'entidad', 'column' => 'entidad'],
            'ent_municipio_id' => ['table' => 'rel_ent_municipio', 'column' => 'ent_municipality'],
            'tra_status_id' => ['table' => 'tra_status', 'column' => 'descripcion'],
            'tra_tipos_id' => ['table' => 'tra_tipos', 'column' => 'tipo'],
            'reembolso_status_id' => ['table' => 'reembolso_status', 'column' => 'reembolso_status'],
            'cobro_status_id' => ['table' => 'cobro_status', 'column' => 'cobro_status'],
            'pago_gestor_status_id' => ['table' => 'pago_gestor_status', 'column' => 'pago_gestor_status']
        ];
        
        // Si el campo no está en el mapeo, devolver el valor tal cual
        if (!isset($fieldMapping[$fieldName])) {
            return (string)$value;
        }
        
        // Buscar el nombre en la base de datos
        try {
            $db = \Config\Database::connect();
            $mapping = $fieldMapping[$fieldName];
            $result = $db->table($mapping['table'])
                ->select($mapping['column'])
                ->where('id', $value)
                ->get()
                ->getRowArray();
            
            if ($result) {
                return $value . ' (' . $result[$mapping['column']] . ')';
            }
        } catch (\Exception $e) {
            log_message('warning', "[resolve_field_name] Error al resolver {$fieldName}: " . $e->getMessage());
        }
        
        return (string)$value;
    }
}

if (!function_exists('log_tramite_bulk_changes')) {
    /**
     * Registra múltiples cambios de un array comparado
     * 
     * @param int $tramiteId ID del trámite
     * @param array $changes Resultado de compare_tramite_data()
     * @param string $entityType Tipo de entidad
     * @param array $metadata Metadata adicional (form_name, form_step, form_section)
     * @return int Número de cambios registrados
     */
    function log_tramite_bulk_changes(int $tramiteId, array $changes, string $entityType = 'tramite', array $metadata = []): int
    {
        $count = 0;
        foreach ($changes as $field => $values) {
            $description = "Campo '$field' cambiado";
            if (log_tramite_change(
                $tramiteId,
                'update',
                $entityType,
                $description,
                $field,
                $values['old'],
                $values['new'],
                $metadata
            )) {
                $count++;
            }
        }
        return $count;
    }
}
