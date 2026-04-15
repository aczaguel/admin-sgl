START TRANSACTION;

-- Preview exacto de permisos que se van a retirar del rol Closer Simple.
SELECT r.role_name, p.id AS permission_id, p.permission_name, rp.id AS role_permission_id
FROM us_role_permissions rp
JOIN us_roles r ON r.id = rp.role_id
JOIN us_permissions p ON p.id = rp.permission_id
WHERE r.role_name = 'Closer Simple'
  AND p.permission_name IN (
    'important_pasar_a_pagos',
    'section_pago_derechos',
    'section_linea_captura',
    'section_documentos_pago',
    'write_tramite_pago_derechos',
    'can_upload_dropzone_pago_derechos'
  )
ORDER BY p.permission_name, rp.id;

-- Depuración conservadora: quitar solo autorización y permisos de paso 3.
DELETE rp
FROM us_role_permissions rp
JOIN us_roles r ON r.id = rp.role_id
JOIN us_permissions p ON p.id = rp.permission_id
WHERE r.role_name = 'Closer Simple'
  AND p.permission_name IN (
    'important_pasar_a_pagos',
    'section_pago_derechos',
    'section_linea_captura',
    'section_documentos_pago',
    'write_tramite_pago_derechos',
    'can_upload_dropzone_pago_derechos'
  );

-- Verificación del set resultante para Closer Simple.
SELECT r.role_name, p.permission_name
FROM us_role_permissions rp
JOIN us_roles r ON r.id = rp.role_id
JOIN us_permissions p ON p.id = rp.permission_id
WHERE r.role_name = 'Closer Simple'
ORDER BY p.permission_name;

-- Cambiar a COMMIT cuando la validación sea correcta.
ROLLBACK;