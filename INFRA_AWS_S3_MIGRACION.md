# Recomendación de Arquitectura AWS — Desacople de almacenamiento (S3) y migración de EC2

> **Audiencia:** DevOps con conocimiento de Terraform.
> **Objetivo:** (1) sacar las imágenes/documentos del disco del EC2 hacia S3 en el sistema **actual**,
> y (2) migrar la nueva versión a un EC2 nuevo (20 usuarios concurrentes, ~10 h/día) apuntando al **mismo RDS**.
> **Fecha:** 2026-07-03

---

## 0. TL;DR (orden recomendado)

1. **Primero desacoplar el almacenamiento** (tu instinto es correcto): introducir una capa de storage con driver `local | s3`, migrar los archivos existentes con `aws s3 sync`, y servirlos con **URLs prefirmadas** (bucket privado). Esto convierte la app en **stateless**.
2. **Luego migrar el compute**: con la app ya stateless, levantar el EC2 nuevo es trivial (misma AMI/Docker, mismo RDS, Elastic IP o ALB), y el cutover se hace por DNS.

La razón de este orden: mientras el estado (archivos) viva en el disco del EC2, **cualquier** migración de servidor implica copiar/perder archivos y no puedes escalar horizontalmente. Desacoplando primero, el servidor pasa a ser desechable.

---

## 1. Diagnóstico del estado actual

| Aspecto | Situación actual | Riesgo |
|---|---|---|
| Compute | 1× EC2 con **IP dinámica**, Docker (por PHP 7.4) | IP cambia en stop/start; sin HA; PHP 7.4 EOL |
| Datos | RDS (bien, ya está desacoplado) | — |
| Archivos | **Disco local del EC2** (`FCPATH/assets/uploads/...`) servidos como estáticos vía `base_url()` | Estado en el servidor → migración riesgosa, sin backup real, se pierde en terminación de instancia, no escalable |
| Subidas en código | 3 mecanismos: `move_uploaded_file()`, CI4 `$file->move()`, y uploader propio de **GroceryCrud Enterprise** | Hay que adaptar los 3 (o aislarlos) |

### Rutas de almacenamiento detectadas en el código
- `assets/uploads/documentostatus/`, `assets/uploads/docstatus/` (documentos de expediente)
- `assets/uploads/pago_derechos/{id}/`
- `assets/uploads/pago_gestor/{id}/`
- `assets/uploads/cobro_cliente/{id}/`
- `assets/uploads/evidencias/`
- `assets/uploads/avatars/`
- `WRITEPATH/uploads/tramites/{id}/` (API externa)

### Mecanismos de subida
1. **Endpoints custom del flujo unificado/nuevo** → `move_uploaded_file()` directo a `FCPATH...`. **Fáciles de redirigir a S3.**
2. **CI4 `$file->move()`** (avatares, wizard, API externa). Fáciles de adaptar.
3. **GroceryCrud Enterprise `setFieldUpload*`** → usa su propio `Upload\Storage\FileSystem`. **El caso especial**; ver §4.4.

---

## 2. Arquitectura destino recomendada

```
             Route53 (opcional, A record → EIP)
                          │
                 ┌────────▼────────┐
                 │  Elastic IP     │  IP pública ESTÁTICA (fija)
                 │  (reasignable)  │
                 └────────┬────────┘
                          │  :443 (TLS en el EC2)
                 ┌────────▼────────┐
                 │  EC2 (Docker)   │  app CI4 (stateless)
                 │  IAM Instance   │  perfil IAM → S3 (sin llaves)
                 │  Profile        │
                 └───┬─────────┬───┘
                     │         │
             ┌───────▼──┐   ┌──▼────────────┐
             │   RDS    │   │      S3       │  bucket privado (documentos)
             │ (mismo)  │   │  + URLs       │
             └──────────┘   │  prefirmadas  │
                            └───────────────┘
```

**Decisiones:**

- **IP estática (requisito confirmado) → Elastic IP directo en el EC2.** Es la opción elegida:
  - IP pública **verdaderamente fija**, sobrevive a stop/start, gratis mientras esté asociada a una instancia en uso.
  - En el cutover se **reasigna la misma EIP** al EC2 nuevo → la IP no cambia para nada externo (allowlists de terceros, A records, integraciones). Sin ALB.
  - **Advertencia — el ALB NO da IP fija:** expone un nombre DNS cuyas IPs rotan. Si el requisito de IP estática es duro (allowlist por IP, A record a IP fija), un ALB por sí solo **no** cumple. Alternativas solo si algún día se quiere balanceador con IP fija: **NLB con EIP** o **Global Accelerator** (2 IPs ancla) delante del balanceador. Para esta escala (20 usuarios, 1 EC2) **no aplica**: EIP directo es suficiente y más barato.
  - TLS: al no haber ALB, termina HTTPS en el propio EC2 (nginx/traefik + Let's Encrypt, o certificado en el reverse proxy del contenedor).
- **Compute:** ver sizing en §6. Mantener **Docker** (ya lo tienes) → la portabilidad entre EC2 viejo y nuevo es inmediata.
- **Archivos:** **S3 privado** + acceso vía **URLs prefirmadas** generadas en el backend (la app ya controla permisos server-side, así que prefirmadas encaja perfecto). Ver §4.5.
- **Credenciales:** **IAM Instance Profile** en el EC2, nunca access keys en `.env`. El SDK las toma del metadata endpoint automáticamente.
- **RDS:** se mantiene el mismo. Cuidado con el periodo de convivencia (viejo + nuevo apuntando al mismo RDS): ver §5.3.

---

## 3. Principio rector: hacer la app *stateless*

El único estado que hoy vive en el EC2 son los archivos. Al moverlos a S3:
- El EC2 se vuelve **desechable/reemplazable** (base para AMI dorada, blue/green, autoscaling futuro).
- La migración al servidor nuevo deja de ser "copiar 10 GB de imágenes y rezar" y pasa a ser "levantar contenedor + apuntar DNS".
- Habilita correr **temporalmente 2 instancias** (viejo y nuevo) leyendo el mismo S3 y mismo RDS → cutover sin ventana de pérdida de archivos.

Por eso el orden es **S3 primero, EC2 después.**

---

## 4. Fase 1 — Desacople de almacenamiento a S3 (sobre el sistema actual)

### 4.1 Bucket y layout
- 1 bucket privado, p. ej. `sgl-uploads-prod`, **Block Public Access = ON** (todo).
- Versioning **ON** (protege contra borrados/sobrescrituras accidentales).
- Prefijos que espejan la estructura actual, para migración 1:1:
  ```
  s3://sgl-uploads-prod/
    documentostatus/...
    pago_derechos/{id}/...
    pago_gestor/{id}/...
    cobro_cliente/{id}/...
    evidencias/...
    avatars/...
  ```
- Lifecycle opcional: transición a `STANDARD_IA` a los 90–180 días (documentos que casi no se re-descargan) para ahorrar.
- Cifrado en reposo: SSE-S3 (o SSE-KMS si necesitas control de llaves/auditoría).

### 4.2 IAM (mínimo privilegio)
Instance Profile con política acotada al bucket:
```json
{
  "Version": "2012-10-17",
  "Statement": [
    { "Effect": "Allow",
      "Action": ["s3:GetObject","s3:PutObject","s3:DeleteObject"],
      "Resource": "arn:aws:s3:::sgl-uploads-prod/*" },
    { "Effect": "Allow",
      "Action": ["s3:ListBucket"],
      "Resource": "arn:aws:s3:::sgl-uploads-prod" }
  ]
}
```

### 4.3 Capa de abstracción en la app (lo importante del lado código)
Introducir un **`FileStorageService`** con dos drivers seleccionables por `.env`:

```
# .env
FILE_STORAGE_DRIVER=s3        # local | s3
S3_BUCKET=sgl-uploads-prod
S3_REGION=us-east-1
# sin llaves: usa el Instance Profile
```

Interfaz mínima sugerida:
```php
interface FileStorage {
    public function put(string $key, string $localTmpPath): bool; // sube
    public function delete(string $key): bool;
    public function url(string $key, int $ttlSeconds = 300): string; // prefirmada
    public function exists(string $key): bool;
}
```
- `LocalFileStorage` → comportamiento actual (`FCPATH`, `base_url`).
- `S3FileStorage` → `aws/aws-sdk-php` (`composer require aws/aws-sdk-php`; compatible con PHP 7.2+).

**Puntos de integración (reemplazos concretos):**
- Endpoints custom (`move_uploaded_file(...)` en `Tramitesn.php`, `Tramites.php`): sustituir por `$storage->put($key, $file['tmp_name'])`. Son la mayor parte del flujo activo y los más simples.
- Generación de URL en vistas/galerías: hoy arman `base_url('/assets/uploads/...')`. Centralizar en un helper `tramite_file_url($key)` que devuelva `$storage->url($key)` (prefirmada) o la URL local según driver.
- Borrado (`@unlink(...)`) → `$storage->delete($key)`.

> **Nota:** guarda en BD la **key relativa** (p. ej. `pago_gestor/12472/abc.jpg`), no la URL completa. Así la URL se resuelve en tiempo de render y puedes cambiar de driver/bucket/CDN sin tocar datos.

### 4.4 Caso especial GroceryCrud Enterprise
GCE trae su propio `Upload\Storage\FileSystem` (escribe a disco local). Opciones, de menor a mayor esfuerzo:
- **A (pragmática):** dejar que GCE siga escribiendo local **solo en los CRUD legacy**, y sincronizar ese prefijo a S3 por `cron`/evento. Sirve si esos módulos están en vías de retiro (ya hay `PLAN_SALIDA_GROCERYCRUD.md`).
- **B:** implementar un storage adapter S3 para GCE (si tu versión lo permite vía interfaz de storage) — más trabajo, revisar la versión 3.1.7.
- **C:** migrar esos campos a los endpoints custom + `FileStorageService` cuando se retire GroceryCrud.

Recomendado: **A** ahora, **C** cuando avance el retiro de GCE. No bloquees la migración por GCE.

### 4.5 Servido de archivos: privado + URLs prefirmadas
Los documentos son sensibles (facturas, acuses, evidencias). **No** dejar el bucket público.
- **Recomendado:** bucket privado + **URL prefirmada** con TTL corto (p. ej. 5 min) generada al renderizar la galería. La app ya valida permisos antes de mostrar, así que solo emites la URL a quien ya autorizaste.
- **Alternativa con CDN:** CloudFront con **OAC** (Origin Access Control) + URLs firmadas de CloudFront. Mejor latencia/costo si hay mucho tráfico de lectura; para 20 usuarios internos es opcional.
- Evitar: servir por un proxy PHP (streaming desde el EC2) — reintroduce acoplamiento y consumo de CPU.

### 4.6 Migración de los archivos existentes (COPIA, sin borrar el origen)

> **Requisito explícito:** los documentos actuales del servidor son importantes y **NO se eliminan**.
> La migración es una **copia** hacia S3. `aws s3 sync` (sin la bandera `--delete`) **nunca borra** el
> origen: solo sube lo que falta o cambió. El disco del EC2 queda intacto y sirve como respaldo.

1. **Sync inicial** (con la app aún leyendo local, sin downtime):
   ```bash
   # sube TODO lo existente a S3; NO toca ni borra los archivos locales
   aws s3 sync ./public/assets/uploads s3://sgl-uploads-prod \
     --exclude "*.tmp" --size-only
   ```
   > Nunca uses `--delete` en esta dirección. Sin `--delete`, el comando es puramente aditivo.
2. **Doble escritura** durante la ventana: que `put()` escriba a local **y** S3 (flag temporal) para no perder subidas nuevas mientras se sincroniza.
3. **Sync incremental final** (rápido, solo diferencias):
   ```bash
   aws s3 sync ./public/assets/uploads s3://sgl-uploads-prod --size-only
   ```
4. **Verificación de integridad** antes del flip:
   ```bash
   # conteo local vs. objetos en S3 (deben coincidir)
   find ./public/assets/uploads -type f | wc -l
   aws s3 ls s3://sgl-uploads-prod --recursive | wc -l
   ```
   Con versioning ON en el bucket, cualquier sobrescritura queda recuperable.
5. **Flip de lectura:** `FILE_STORAGE_DRIVER=s3`. Verificar galerías/preview en los 5 pasos.
6. **Conservar el disco local** como respaldo (no borrar). Si en el futuro se quiere liberar espacio, hacerlo solo tras confirmar backups de S3 y con aprobación explícita — nunca de forma automática.

> **IMPORTANTE — hay DOS raíces de subidas en disco**, no una. El runbook completo (§4.7) las cubre ambas.
> El comando de arriba (`./public/assets/uploads`) es solo la primera raíz.

### 4.7 Runbook completo de subida a S3 (ambas raíces, no destructivo)

**Carpetas identificadas en el código y en disco:**

| Raíz en disco | Contenido | Prefijo/Key en S3 |
|---|---|---|
| `public/assets/uploads/` | `cobro_cliente/`, `pago_gestor/`, `pago_derechos/`, `documentostatus/`, `docstatus/`, `evidencias/` (+ sueltos) | mismo nombre de subcarpeta (`cobro_cliente/…`, `pago_gestor/<id>/…`) |
| `writable/uploads/tramites/` | documentos de la **API externa** por trámite | `tramites/<id>/…` |
| `writable/uploads/avatars/` | avatares de usuario | `avatars/…` |

> Las claves resultantes coinciden con las que espera la app (`keyFromStored()` / `buildKey()` del diseño): `<category>/<id?>/<file>`. Por eso cada raíz se sincroniza a su prefijo correspondiente.

**Precondiciones (una sola vez):**
```bash
# 1. Bucket privado con versioning y Block Public Access (Terraform o CLI)
aws s3api create-bucket --bucket sgl-uploads-prod --region us-east-1
aws s3api put-bucket-versioning --bucket sgl-uploads-prod \
  --versioning-configuration Status=Enabled
aws s3api put-public-access-block --bucket sgl-uploads-prod \
  --public-access-block-configuration BlockPublicAcls=true,IgnorePublicAcls=true,BlockPublicPolicy=true,RestrictPublicBuckets=true
# 2. Cifrado por defecto (SSE-S3)
aws s3api put-bucket-encryption --bucket sgl-uploads-prod \
  --server-side-encryption-configuration '{"Rules":[{"ApplyServerSideEncryptionByDefault":{"SSEAlgorithm":"AES256"}}]}'
# 3. IAM Instance Profile adjunto al EC2 (sin llaves). Verifica identidad:
aws sts get-caller-identity
```

**Copia inicial — ejecutar EN EL EC2 de producción, desde la raíz del proyecto.** Los archivos locales NO se tocan (sin `--delete`):
```bash
# Raíz 1: documentos de trámites
aws s3 sync ./public/assets/uploads       s3://sgl-uploads-prod           --exclude "*.tmp" --size-only

# Raíz 2a: documentos de la API externa -> prefijo tramites/
aws s3 sync ./writable/uploads/tramites   s3://sgl-uploads-prod/tramites  --exclude "*.tmp" --size-only

# Raíz 2b: avatares -> prefijo avatars/
aws s3 sync ./writable/uploads/avatars    s3://sgl-uploads-prod/avatars   --exclude "*.tmp" --size-only
```

**(Opcional) Ventana de doble escritura:** activa `FILE_STORAGE_DUAL_WRITE=true` para que las subidas nuevas caigan en local **y** S3 mientras terminas.

**Sync incremental final** (solo diferencias, rápido) — repetir los 3 comandos anteriores justo antes del flip.

**Verificación de integridad (los conteos deben coincidir):**
```bash
# Local (ambas raíces)
find ./public/assets/uploads ./writable/uploads/tramites ./writable/uploads/avatars -type f ! -name "*.tmp" | wc -l
# S3
aws s3 ls s3://sgl-uploads-prod --recursive | wc -l
# Equivalente automatizado
php spark s3:migrate-check
```

**Flip de lectura:** en `.env` → `FILE_STORAGE_DRIVER=s3`; verificar galerías/preview en los 5 pasos y avatares.

**Rollback instantáneo:** `FILE_STORAGE_DRIVER=local` (el disco local sigue intacto).

> **Notas de seguridad:** estos comandos son **aditivos** (sin `--delete`), read-only sobre el disco local. El versioning ON protege ante cualquier sobrescritura. El disco local se conserva como respaldo y no se borra automáticamente.
> **Nota de entorno:** en tu copia local de desarrollo solo existe `public/assets/uploads/cobro_cliente` y algunos sueltos; las demás subcarpetas (`pago_gestor`, `documentostatus`, etc.) existen en **producción**. El `sync` sube lo que exista en cada entorno, así que el runbook funciona igual en ambos.

---

## 5. Fase 2 — Migración al EC2 nuevo

### 5.0 Orden elegido: S3 PRIMERO, luego cómputo (decisión final)

> **Decisión:** primero se completa el desacople a S3 (spec `s3-file-storage` + runbook §4.7 + flip
> `FILE_STORAGE_DRIVER=s3`). **Después** se migra el cómputo al EC2 nuevo.

**Por qué (confirmado):** con los archivos ya en S3 y las sesiones fuera de alcance por ahora, el EC2 nuevo
**nace limpio (stateless)**: no hay que copiar ni un archivo al servidor nuevo. Solo apunta al **mismo bucket S3**
(vía Instance Profile) y al **mismo RDS**. La migración deja de ser "copiar GB y rezar" y se vuelve
"levantar contenedor + reasignar la Elastic IP", con rollback trivial y **cero riesgo de pérdida de archivos**
en el cutover (ambos servidores leen/escriben el mismo S3).

**Secuencia:**
1. **Fase S3** (primero): implementar el spec `s3-file-storage`, correr el runbook §4.7 (sync aditivo, sin borrar), verificar integridad, y hacer el flip `FILE_STORAGE_DRIVER=s3`. El servidor queda stateless respecto a archivos.
2. **Fase cómputo** (después): levantar el EC2 nuevo con la misma imagen Docker, **mismo RDS**, **mismo bucket S3** y su Instance Profile; validar contra IP temporal; **reasignar la Elastic IP**. Sin copia de archivos.

> Ya **no se requiere rsync de archivos** entre servidores. La única precaución operativa que queda para el
> cutover de cómputo es la convivencia en el mismo RDS (§5.3: migraciones expand/contract, vigilar `max_connections`).

### 5.1 Estrategia (aplica una vez S3 está activo)
Con la app ya stateless (Fase 1 completa):
1. Construir la **misma imagen Docker** (o AMI dorada con el compose) en el EC2 nuevo.
2. Adjuntar el **mismo Instance Profile** (acceso S3) y apuntar `.env` al **mismo RDS**.
3. Levantar y validar en paralelo (viejo sigue sirviendo tráfico, con su IP temporal).
4. **Cutover = reasignar la Elastic IP** del EC2 viejo al nuevo (un solo comando / `terraform apply`). La IP pública no cambia → cero impacto en allowlists, DNS o integraciones externas.
5. Rollback = reasignar la EIP de vuelta al EC2 viejo. Sin pérdida porque ambos comparten S3 + RDS.

> **Nota EIP:** al desasociar/reasociar hay un breve corte de conexiones (segundos). Coordinar en la ventana. Si necesitas cero corte, precalienta el nuevo y valida contra su IP temporal antes de mover la EIP.

### 5.2 Terraform (módulos sugeridos)
- `network`: VPC/subnets existentes (reutilizar), SG para EC2 (solo 443 —y 80 si rediriges— desde Internet o rango de oficina; 22 cerrado, acceso por SSM).
- `iam`: rol + instance profile con la política de §4.2.
- `s3`: bucket, versioning, block public access, lifecycle.
- `compute`: `aws_instance` (o `aws_launch_template` + ASG de tamaño 1 para reemplazo automático), `user_data` que arranca Docker/compose.
- `eip`: `aws_eip` + `aws_eip_association` a la instancia. Es el recurso clave de la IP estática; en el cutover se cambia la asociación al EC2 nuevo.
- `dns` (opcional): registro Route53 (A record → EIP) si usas dominio en vez de IP pelada.
- Preferir **SSM Session Manager** en vez de SSH abierto (cierra el 22).

### 5.3 Convivencia con el mismo RDS (¡cuidado!)
- Durante el periodo en que **viejo y nuevo** apunten al mismo RDS:
  - Verificar que el **esquema** sea compatible con ambas versiones de código. Si la versión nueva trae **migraciones**, aplica solo las **backward-compatible** (expand/contract): primero expandir (columnas nuevas nullable), desplegar, y contraer después de retirar el viejo.
  - Vigilar el **máximo de conexiones** del RDS (dos app servers ⇒ el doble de pools). Revisar `max_connections` según la clase de instancia.
- Ideal: ventana de cutover corta para minimizar la convivencia.

---

## 6. Sizing para 20 usuarios concurrentes, ~10 h/día

Carga real baja (app interna, picos moderados). Recomendación:

| Componente | Recomendación | Notas |
|---|---|---|
| EC2 | **t3.medium** (2 vCPU, 4 GB) | Burstable encaja bien con jornada de 10 h. Empieza aquí; si CPU credits se agotan, `t3.large` o `unlimited`. `t3.small` (2 GB) puede bastar si la imagen es liviana, pero 4 GB da margen para PHP-FPM + picos. |
| Alternativa ARM | **t4g.medium** (Graviton) | ~20% más barato. Requiere imagen Docker `arm64` de PHP 7.4 (existe). Opcional. |
| EBS | gp3 20–30 GB | Ya no guarda uploads (van a S3); solo SO + app + logs. |
| RDS | (existente) | Revisar `max_connections` para la convivencia (§5.3). |
| Elastic IP | 1, asociada al EC2 | IP estática. Gratis mientras esté asociada a instancia en uso; ~$3.6/mes si queda sin asociar. |
| ALB | **No** (descartado) | No da IP fija; innecesario a esta escala. TLS termina en el EC2. |

> PHP 7.4 está **EOL** (sin parches de seguridad). No bloquea esta migración, pero planifica la subida a 8.2 (ya existe `MIGRACION_PHP_8_2_README.md` / `DOCKER_PHP82_README.md`). Hazlo idealmente **después** del cutball de infra, como paso aparte.

---

## 7. Costos aproximados (orden de magnitud, us-east-1)

> Estimación mensual, solo referencia; validar con Pricing Calculator.

- EC2 t3.medium on-demand ~24/7: **~$30**. Si apagas fuera de horario (10 h/día) con scheduler: **~$13**.
- Elastic IP: **$0** mientras esté asociada a una instancia en ejecución (~$3.6/mes solo si queda sin asociar).
- ALB: **descartado** (no da IP fija). Ahorro vs. el diseño con ALB: ~$16–20/mes.
- S3: almacenamiento **~$0.023/GB** + requests (centavos a esta escala). 10 GB ≈ **$0.25**.
- Transferencia de salida: baja para uso interno.
- **Reserved/Savings Plan** si el EC2 será permanente: ahorra ~30–40%.

A esta escala, el mayor ahorro operativo es **apagar el EC2 fuera del horario laboral** (scheduler/Instance Scheduler) ya que solo se usa ~10 h/día.

---

## 8. Plan de ejecución por fases (checklist)

**Fase 0 — Preparación**
- [ ] Crear bucket S3 privado + versioning + block public access (Terraform).
- [ ] Crear rol/instance profile IAM y adjuntarlo al EC2 actual.
- [ ] `composer require aws/aws-sdk-php`.

**Fase 1 — Desacople de storage (código, sobre sistema actual)**
- [ ] Implementar `FileStorage` (`LocalFileStorage`, `S3FileStorage`) + config por `.env`.
- [ ] Reemplazar `move_uploaded_file`/`$file->move`/`@unlink` en endpoints custom por el servicio.
- [ ] Centralizar generación de URL en helper (prefirmadas cuando driver=s3).
- [ ] Guardar en BD la **key relativa** (no URL completa).
- [ ] Estrategia para GroceryCrud (§4.4, opción A).
- [ ] `aws s3 sync` inicial → doble escritura → sync final → `FILE_STORAGE_DRIVER=s3`.
- [ ] QA: subir, previsualizar, borrar en Pasos 1–5; avatares; API externa.

**Fase 2 — Migración de compute**
- [ ] Terraform: SG, instancia/launch template, **Elastic IP** (`aws_eip` + `aws_eip_association`), Route53 opcional.
- [ ] Levantar EC2 nuevo con misma imagen Docker + mismo RDS + mismo S3.
- [ ] Validar en paralelo contra la **IP temporal** del EC2 nuevo; aplicar migraciones backward-compatible si las hay.
- [ ] Cutover = **reasignar la EIP** al EC2 nuevo; monitorear; retirar EC2 viejo tras periodo de gracia.

**Fase 3 — Desempeño y preparación para crecer (§10)**
- [ ] Afinar **OPcache** + PHP-FPM pool sizing (quick win inmediato).
- [ ] **CloudFront** delante de assets estáticos (CSS/JS/tema).
- [ ] **Externalizar sesiones a Redis (ElastiCache)** → habilita multi-instancia (hoy están en disco local).
- [ ] Cache de aplicación (catálogos/permisos/dashboards) en Redis.
- [ ] Activar **RDS Performance Insights** + CloudWatch alarms; revisar índices de listados/dashboards.

**Fase 4 — Post-migración (aparte)**
- [ ] Programar apagado nocturno del EC2 (scheduler) mientras sea un solo nodo.
- [ ] Planificar subida PHP 7.4 → 8.2 (~20–30% más rápido).
- [ ] Escalar por etapas según métricas: read replica → RDS Proxy → ASG (NLB+EIP / Global Accelerator para conservar IP fija).
- [ ] Considerar CloudFront si crece el tráfico de lectura.

---

## 9. Riesgos y mitigaciones

| Riesgo | Mitigación |
|---|---|
| Perder subidas durante el sync | Doble escritura temporal + sync incremental final |
| GroceryCrud sigue escribiendo local | Sync programado de ese prefijo (opción A) hasta su retiro |
| URLs viejas guardadas como absolutas en BD | Migrar a keys relativas; script de normalización si ya hay URLs completas |
| Convivencia viejo/nuevo en RDS | Migraciones expand/contract; vigilar `max_connections`; ventana corta |
| Llaves AWS filtradas | Usar Instance Profile, nunca keys en `.env`/repo |
| Bucket público por error | Block Public Access ON + prefirmadas; revisar con `s3api get-public-access-block` |
| PHP 7.4 EOL | Migración a 8.2 planificada como fase separada |
| Sesiones en disco local impiden multi-instancia | Externalizar a Redis/ElastiCache antes de escalar horizontalmente (§10.0) |
| Borrado accidental de documentos importantes al migrar | `s3 sync` **sin** `--delete` (aditivo); disco local se conserva; versioning ON en el bucket (§4.6) |
| Sistema lento al crecer usuarios | OPcache + CloudFront + Redis + read replica de RDS por etapas (§10) |

---

## 10. Escalabilidad y desempeño (crecimiento de usuarios)

El sistema arranca con ~20 usuarios pero **crecerá** conforme se suscriban clientes. La carga es
mayormente **lectura de vistas + formularios** (poca CPU, mucho render y consultas). El objetivo es
que siga siendo **rápido y óptimo** sin rediseñar cada vez que suba el número de usuarios.

### 10.0 Bloqueante detectado: sesiones en disco local
Hoy las sesiones usan `CodeIgniter\Session\Handlers\FileHandler` en `WRITEPATH/session` (disco del EC2).
**Esto impide escalar horizontalmente:** si agregas un segundo servidor, un usuario cuya petición
caiga en otra instancia **pierde la sesión** (login, CSRF, flash). Es el primer requisito a resolver
antes de multi-instancia.
- **Solución:** externalizar sesiones a **Redis (ElastiCache)** con `RedisHandler`, o a la BD con
  `DatabaseHandler`. Redis es la opción óptima (rápida, TTL nativo). Ya existe `predis/predis` en
  las dependencias de dev, así que el cliente es conocido.
- Beneficio adicional: al sacar la sesión del disco, el EC2 queda **100% stateless** (junto con S3),
  requisito para ASG/autoscaling.

### 10.1 Quick wins de desempeño en un solo nodo (hazlos ya, valen para cualquier escala)
- **OPcache afinado** (el mayor impacto en PHP): `opcache.enable=1`, `memory_consumption=256`,
  `max_accelerated_files=20000`, `validate_timestamps=0` en prod (invalidar en deploy). Multiplica el throughput sin tocar código.
- **PHP-FPM pool sizing** según RAM: `pm=dynamic`, calcular `pm.max_children` = RAM disponible / (~40–60 MB por worker). Evita swapping bajo carga.
- **Subir a PHP 8.2** cuando se pueda: ~20–30% más rápido que 7.4, además de soporte/seguridad. (Fase aparte.)
- **Compresión y cache de assets:** gzip/brotli en el reverse proxy; `Cache-Control` largo para CSS/JS/img (ya usas cache-buster `?v=` para invalidar). 
- **CloudFront delante de los estáticos** (CSS/JS/imágenes de tema): descarga el EC2 y acelera el render de vistas para todos los usuarios. Los documentos privados de S3 pueden ir por CloudFront con OAC + URLs firmadas.
- **Índices de BD:** revisar consultas del listado de trámites/dashboards (las más golpeadas) y asegurar índices en columnas de filtro/join (cliente, ejecutivo, status, fechas). Activar **RDS Performance Insights** para detectar las queries lentas reales.

### 10.2 Capa de caché (Redis / ElastiCache)
Un ElastiCache (Redis) pequeño cubre dos necesidades a la vez:
1. **Sesiones compartidas** (habilita multi-instancia — §10.0).
2. **Cache de aplicación:** catálogos, menús, permisos por rol, resultados de dashboards costosos.
   CI4 tiene `Cache` con driver Redis; cachear los queries de solo-lectura frecuentes baja mucho la
   presión sobre RDS.

### 10.3 Ruta de crecimiento por etapas (preservando IP estática)

| Etapa | Usuarios aprox. | Arquitectura | IP estática |
|---|---|---|---|
| **A — hoy** | 20–50 | 1× EC2 (t3.medium) + **EIP** + OPcache + CloudFront (assets). Escalado **vertical** si hace falta (t3.large). | EIP directo |
| **B** | 50–150 | Externalizar sesiones a **Redis (ElastiCache)** + **read replica** de RDS para lecturas de reportes/dashboards. Sigue 1 EC2 (más grande) o 2 en activo/activo. | EIP (o NLB+EIP si 2 nodos) |
| **C** | 150+ | **ASG** (2+ EC2) detrás de balanceador + **RDS Proxy** (pooling de conexiones) + read replicas + CloudFront en todo. Autoscaling por CPU/conexiones. | **NLB con EIP** o **Global Accelerator** (mantienen IP fija con balanceador); alternativamente dominio Route53 |

> **Nota IP estática vs. balanceador:** cuando llegues a multi-nodo (etapa C), el ALB **no** da IP fija.
> Para conservar el requisito de IP estática se usa **NLB con Elastic IP** o **Global Accelerator**
> (dos IPs ancla fijas) delante del balanceo. Si para entonces el acceso es por **dominio**, un
> A/ALIAS record en Route53 resuelve el tema sin necesidad de IP pelada.

### 10.4 Escalado de datos (RDS)
- **Vertical primero:** subir clase de instancia RDS es el ajuste más simple y suele bastar mucho tiempo.
- **Read replica** cuando los reportes/dashboards (lectura pesada, ya multi-tenant) compitan con la operación transaccional: dirigir esas lecturas a la réplica.
- **RDS Proxy** cuando haya varios app servers: agrupa/reutiliza conexiones y protege el `max_connections`.
- Activar **Performance Insights** + **CloudWatch alarms** (CPU, conexiones, latencia de queries) para escalar con datos, no a ciegas.

### 10.5 Observabilidad (para escalar con evidencia)
- CloudWatch: métricas de EC2 (CPU credits, memoria vía agent), RDS y ElastiCache; alarmas.
- Logs centralizados (CloudWatch Logs) — sobre todo al pasar a multi-nodo.
- Métrica guía para decidir el salto de etapa: latencia p95 de las vistas y saturación de PHP-FPM workers.

### 10.6 Qué NO hacer todavía (evitar sobre-ingeniería)
- No montar Kubernetes/ECS ni microservicios para esta carga: un EC2 con Docker + EIP es lo correcto hoy.
- No introducir autoscaling antes de externalizar sesiones (se rompería).
- No optimizar queries a ciegas: medir con Performance Insights primero.

---

## 11. Resumen

Tu enfoque es el correcto: **desacoplar S3 primero, migrar EC2 después.** La pieza técnica central es una **capa `FileStorage` con drivers local/S3** que te permite hacer el cambio con un flag y un `s3 sync`, sin ventana de pérdida. Una vez la app es stateless, el nuevo EC2 con el mismo RDS y el mismo bucket se levanta y se conmuta **reasignando la Elastic IP** (IP estática), con rollback trivial. Sizing: **t3.medium** con Docker, apagado fuera de horario para ahorrar. Red: **Elastic IP directo en el EC2** (sin ALB, que no da IP fija). Servir documentos **privados con URLs prefirmadas**, credenciales por **IAM Instance Profile**, todo describible en Terraform.

**Sobre el crecimiento (§10):** el sistema está pensado para escalar sin rediseño. Quick wins que aplican desde ya: **OPcache**, **CloudFront** para estáticos e **índices** de las consultas más golpeadas. El único bloqueante real para multi-instancia es que **las sesiones viven en disco local**; externalizarlas a **Redis (ElastiCache)** deja la app 100% stateless y habilita la ruta A→B→C (vertical → read replica/Redis → ASG). La IP estática se conserva en todas las etapas (EIP directo, y NLB+EIP o Global Accelerator si algún día hay balanceador). La migración de archivos es **copia, nunca borrado**: `s3 sync` aditivo + versioning + disco local intacto.

**Migración de documentos existentes (requisito):** se **copian** todos a S3 con `aws s3 sync` (sin `--delete`), se verifica el conteo local vs. S3, y el disco del EC2 **se conserva** como respaldo. Ningún documento se elimina.
