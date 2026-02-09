# Manual de Demo (Datos + Guion Ejecutivo)

Fecha: 2026-02-09

Este manual sirve para:

1) Generar datos demo **sin captura manual**.
2) Validar rápidamente (“smoke test”) que multi-cliente/multi-tenancy está bien.
3) Tener un guion corto para presentar a ejecutivos.

---

## 1) Requisitos

- Estar en el proyecto (carpeta donde existe `spark`).
- PHP CLI instalado:
  - Verifica con: `php -v`
- Dependencias listas (si aplica):
  - `composer install`
- Base de datos configurada para el ambiente:
  - `.env` y/o configuración de CodeIgniter.

> Recomendación: ejecutar en **staging/demo**, no en producción.

---

## 2) Ubicación (desde terminal)

En macOS/Linux:

```bash
cd /Users/miguelangel/Sites/admin-sgl
ls -la spark
```

Si `spark` existe, estás en la ruta correcta.

---

## 3) Generar datos demo (automático)

### 3.1 Comando base

```bash
php spark sgl:demo-data
```

### 3.2 Opciones

- `--count`: cantidad de trámites demo (default 10)
- `--email`: email del usuario demo (default `luisa.flores@demo.local`)
- `--password`: password del usuario demo (default `Demo1234!`)

Ejemplo:

```bash
php spark sgl:demo-data --count=12 --email=demo.ejecutivo@demo.local --password='Demo1234!'
```

### 3.3 ¿Qué crea/reusa?

El comando es idempotente: si ya existe, lo reusa por llaves simples (por ejemplo, nombre/email/folio).

Crea/reusa:

- 2 clientes (`cliente`):
  - **Servicios Yolanda**
  - **Servicios Sofia**
- 2 clientes directos (`cli_directo`) ligados a esos clientes.
- 2 ejecutivos (`cli_directo_ejecutivo`) ligados a cada `cli_directo`.
- 1 usuario demo (`users`) con el email indicado.
- Roles al usuario demo (`us_user_roles`): **[2, 11, 12, 3]**
- Asignación de clientes al usuario (`cliente_user`).
- Trámites demo (`tramite`) repartidos 50/50 entre ambos `cli_directo`.
- Servicios asociados (`tra_tramite_asociado`).

Al final debe mostrar:

- `✅ Demo listo`
- Usuario/password
- Trámites creados/reusados

---

## 4) Smoke test (validación rápida)

### 4.1 Comando

```bash
php spark sgl:smoke-demo
```

Si usaste un email distinto:

```bash
php spark sgl:smoke-demo --email=demo.ejecutivo@demo.local
```

### 4.2 Resultado esperado

- Confirma que existe el usuario
- Muestra los 2 clientes asignados
- Muestra cuántos trámites son visibles por asignación (multi-tenancy)

---

## 5) Guion de demo para ejecutivos (5–10 min)

### 5.1 Login

- URL del ambiente: `<PON_AQUI_TU_URL>`
- Usuario: el email generado (default `luisa.flores@demo.local`)
- Password: el password generado (default `Demo1234!`)

### 5.2 Multi-cliente (selector en header)

1) Ubica el selector de **Cliente** en el header.
2) Cambia entre:
   - “Todos los clientes”
   - “Servicios Yolanda”
   - “Servicios Sofia”
3) Confirma que cambian los datos visibles (contexto) sin mezclar clientes.

### 5.3 Trámites demo

- Busca folios con prefijo:
  - `YOL-DEMO-...`
  - `SOF-DEMO-...`
- Abre al menos un trámite de cada uno.

### 5.4 Wizard / actualización de trámite

- Entra a editar/actualizar un trámite.
- Confirma que carga el stepper/estado por steps.

### 5.5 Menú (sidebar) contraíble

- En pantalla grande: colapsa a modo “iconos” y vuelve a expandir.
- En pantalla pequeña: abre/cierra el sidebar (overlay).

---

## 6) Checklist rápido antes de presentar

- [ ] `php spark sgl:demo-data` ejecutó sin errores
- [ ] `php spark sgl:smoke-demo` muestra 2 clientes asignados
- [ ] Login con usuario demo funciona
- [ ] Selector de cliente cambia el contexto
- [ ] Existen folios `YOL-DEMO-*` y `SOF-DEMO-*`
- [ ] Wizard carga correctamente
- [ ] Sidebar colapsa/abre correctamente

---

## 7) Troubleshooting

### 7.1 “No existe el usuario demo…”

- Ejecuta primero: `php spark sgl:demo-data`
- Si cambiaste `--email`, úsalo también en `sgl:smoke-demo`.

### 7.2 Actividad mensual / gráficas vacías

- Puede ocurrir si no hay registros recientes o la fecha no está presente.
- El gráfico debería mostrar últimos 6 meses aunque estén en cero.

---

## 8) (Opcional) Limpieza de demo

Recomendación: mejor restaurar un snapshot de BD del ambiente demo.

Los trámites demo se identifican por `folio`:
- `YOL-DEMO-%`
- `SOF-DEMO-%`

SQL de referencia (haz backup y ajusta a tu entorno):

```sql
DELETE FROM tra_tramite_asociado
WHERE tramite_id IN (
  SELECT id FROM tramite
  WHERE folio LIKE 'YOL-DEMO-%' OR folio LIKE 'SOF-DEMO-%'
);

DELETE FROM tramite
WHERE folio LIKE 'YOL-DEMO-%' OR folio LIKE 'SOF-DEMO-%';
```
