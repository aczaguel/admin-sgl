# Guía de Debugging: Cliente No Ve Dashboard

## Paso 1: Verificar Permisos en Sesión (EN VIVO)

Accede como usuario con rol **Cliente** y luego abre estas URLs (debes ser Admin o Super Admin para verlas):

**Para ver información de sesión:**
```
http://tu-dominio/deskapp/debug/session
```

**Para ver todos los permisos cargados:**
```
http://tu-dominio/deskapp/debug/permissions
```

**Para chequear acceso específico a dashboard cliente:**
```
http://tu-dominio/deskapp/debug/check-dashboard
```

## Paso 2: Interpretar Resultados

### Si `/deskapp/debug/check-dashboard` retorna:
```json
{
  "would_allow_access": true,
  "has_permission_menu_dashboard_cliente": true
}
```
✅ **Los permisos SÍ están cargados correctamente**

Entonces el problema es en otra capa:
- Revisa el navegador (Herramientas de Dev → Console) para errores JS
- Limpia cache/cookies del navegador
- Intenta acceso en incógnito

### Si retorna:
```json
{
  "would_allow_access": false,
  "has_permission_menu_dashboard_cliente": false
}
```
❌ **El permiso NO está cargado en sesión**

Soluciones:
1. Cierra sesión y re-inicia
2. Limpia cookies del navegador
3. Ejecuta: `rm -rf writable/session/*` (servidor)
4. Verifica que el usuario realmente tiene rol Cliente en BD

## Paso 3: Verificación en Base de Datos

```bash
# Ver permisos del usuario Cliente
mysql -h localhost -u admin -p'contraseña_segura' procedures -e "
SELECT p.permission_name
FROM us_role_permissions rp
JOIN us_permissions p ON p.id = rp.permission_id
JOIN us_roles r ON r.id = rp.role_id
WHERE r.role_name = 'Cliente'
ORDER BY p.permission_name;
"
```

Debe mostrar:
```
menu_dashboard_cliente
menu_tramites_cliente
ui_sidebar_cliente
...otros permisos
```

Si NO aparece `menu_dashboard_cliente`, ejecuta:
```bash
# Ejecutar script de asignación de permisos
mysql -h localhost -u admin -p'contraseña_segura' procedures < assign_sidebar_permissions_roles.sql
```

## Paso 4: Forzar Refresco de Sesión

El filtro `AclRefreshFilter` solo recarga permisos cuando `acl_version` cambia.

Para forzar recarga:
```php
// En app/Config/Database.php o en un helper
$version = \App\Helpers\AclVersionHelper::incrementVersion();
```

O simplemente:
```bash
# Limpiar sesiones
rm -rf writable/session/*
```

## Checklist Rápido

- [ ] Verificar que el usuario tiene rol Cliente en `us_user_roles`
- [ ] Verificar que rol Cliente tiene permiso `menu_dashboard_cliente` en BD
- [ ] Ejecutar debug routes (`/deskapp/debug/check-dashboard`)
- [ ] Limpiar sesión del servidor (`rm writable/session/*`)
- [ ] Limpiar cookies del navegador
- [ ] Re-iniciar sesión
- [ ] Intentar acceso en incógnito
- [ ] Revisar console del navegador (F12) para errores JS

## Información del Sistema

**Permiso requerido:** `menu_dashboard_cliente`
**Controlador:** `app/Controllers/Deskapp/DashboardCliente.php`
**Método de verificación:** `requireClienteAccess()`
**Filtro de ACL:** `app/Filters/AclRefreshFilter.php`

---

**NOTA:** Los debug routes deben ser removidos antes de pasar a producción. 
Borrar archivos:
- `app/Controllers/Deskapp/DebugPermisos.php`
- Las 3 rutas de debug en `app/Config/Routes.php`
