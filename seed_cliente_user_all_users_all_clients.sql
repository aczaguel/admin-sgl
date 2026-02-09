-- Seed temporal: asignar (casi) todos los usuarios a (todos) los clientes
-- Objetivo: para el demo, dejar “todos con todos” y luego depurar manualmente.
-- NOTA: este script NO asume que el campo `id` sea insertable (si es AUTO_INCREMENT, mejor no tocarlo).
-- Motor esperado: MySQL/MariaDB.

START TRANSACTION;

-- ===============================
-- Opción A (recomendada):
-- Insertar relaciones para una lista de usuarios contra TODOS los registros de `cliente`.
-- Evita duplicados aunque no exista unique key (user_id, cliente_id).
-- ===============================

INSERT INTO cliente_user (user_id, cliente_id)
SELECT u.id AS user_id,
       c.id AS cliente_id
FROM users u
JOIN (
    SELECT 4  AS id UNION ALL
    SELECT 6  UNION ALL
    SELECT 7  UNION ALL
    SELECT 8  UNION ALL
    SELECT 9  UNION ALL
    SELECT 10 UNION ALL
    SELECT 11 UNION ALL
    SELECT 12 UNION ALL
    SELECT 13 UNION ALL
    SELECT 14 UNION ALL
    SELECT 15 UNION ALL
    SELECT 16 UNION ALL
    SELECT 17 UNION ALL
    SELECT 18 UNION ALL
    SELECT 19 UNION ALL
    SELECT 20 UNION ALL
    SELECT 21 UNION ALL
    SELECT 22 UNION ALL
    SELECT 23 UNION ALL
    SELECT 24
) allowed_users ON allowed_users.id = u.id
CROSS JOIN cliente c
LEFT JOIN cliente_user cu
       ON cu.user_id = u.id
      AND cu.cliente_id = c.id
WHERE cu.user_id IS NULL;

-- ===============================
-- Opción B (alternativa):
-- Insertar SOLO las relaciones específicas que compartiste.
-- (Útil si NO quieres dar todos los clientes a todos.)
-- ===============================

-- INSERT INTO cliente_user (user_id, cliente_id) VALUES
-- (18, 23),
-- (18, 26),
-- (18, 22),
-- (18, 18),
-- (19, 17),
-- (19, 27),
-- (19, 42),
-- (19, 23),
-- (19, 26),
-- (19, 29),
-- (19, 43),
-- (19, 34),
-- (19, 32),
-- (19, 35),
-- (19, 22),
-- (19, 39),
-- (19, 40),
-- (19, 18),
-- (19, 19),
-- (19, 41),
-- (19, 38),
-- (19, 33),
-- (19, 31),
-- (19, 20),
-- (19, 37),
-- (18, 21),
-- (18, 1),
-- (18, 2),
-- (18, 7),
-- (18, 25),
-- (18, 6),
-- (18, 15),
-- (18, 4),
-- (18, 5),
-- (18, 16),
-- (18, 12),
-- (18, 11),
-- (18, 24),
-- (18, 14),
-- (18, 3)
-- ;

COMMIT;

-- Verificación rápida (opcional):
-- SELECT user_id, COUNT(*) AS clientes_asignados
-- FROM cliente_user
-- WHERE user_id IN (4,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24)
-- GROUP BY user_id
-- ORDER BY user_id;

-- Rollback manual (USAR CON CUIDADO):
-- Esto elimina TODAS las relaciones de esos usuarios (incluye relaciones previas legítimas).
-- DELETE FROM cliente_user WHERE user_id IN (4,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24);
