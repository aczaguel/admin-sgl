# AWS Secrets Manager — Resolución de credenciales RDS

> **Audiencia:** DevOps / operadores con acceso a la consola y CLI de AWS.
> **Objetivo:** habilitar que la app CodeIgniter 4 resuelva las credenciales de la base
> de datos RDS en tiempo de ejecución desde un **secreto** de AWS Secrets Manager,
> en lugar de guardar los **valores** de la contraseña en `.env` o en el repositorio.
> **Alcance:** solo el secreto de RDS. Ver §7 para lo que queda fuera de alcance.

---

## 0. TL;DR

1. El operador crea el secreto RDS en Secrets Manager como un **blob JSON**
   (`host` / `port` / `username` / `password` / `dbname`). Ver §2.
2. El operador adjunta una política IAM al **Instance Profile** del EC2 que otorga
   `secretsmanager:GetSecretValue` **solo** sobre ese secreto. Ver §5.
3. El operador activa el proveedor `aws` en `.env` con la **referencia** del secreto
   y la región (nunca el valor). Ver §4 y §6.
4. Revertir es inmediato: `SECRETS_PROVIDER=env` (o quitar la variable). Ver §6.

La app autentica con AWS usando el **IAM Instance Profile** del EC2 mediante la cadena
de credenciales por defecto del SDK. **No se colocan llaves de acceso de AWS en `.env`
ni en el repositorio**, igual que en `INFRA_AWS_S3_MIGRACION.md` §4.2.

---

## 1. Panorama: cuándo usar `aws` vs `env`

Un único interruptor en `.env` (`SECRETS_PROVIDER`) alterna entre dos proveedores
intercambiables, tal como `FILE_STORAGE_DRIVER=local|s3` alterna el almacenamiento:

| Proveedor | Cuándo usarlo | Origen de las credenciales |
|---|---|---|
| `env` (por defecto) | Desarrollo local y despliegues existentes que aún usan `.env` plano | Lee `database.default.hostname` / `database` / `username` / `password` del `.env` local. No contacta AWS. |
| `aws` | Producción en EC2 con Instance Profile | Descarga el blob JSON del secreto RDS vía `GetSecretValue` y lo mapea a `Config\Database`. |

- Si `SECRETS_PROVIDER` está **ausente o vacío**, la app se comporta **idéntico** al
  sistema actual con `.env` plano (proveedor `env`). Nada cambia hasta que un operador
  ponga `SECRETS_PROVIDER=aws`.
- El cambio es **controlado y reversible**: no requiere tocar código de la aplicación.

---

## 2. Crear el secreto RDS

El valor del secreto es un **único objeto JSON** con estos campos. Es el único lugar
donde viven las credenciales reales; **nunca** se copian al repositorio.

| Campo JSON | Clave en `Config\Database` `default` | Requerido | Default |
|---|---|---|---|
| `host` | `hostname` | sí | — |
| `username` | `username` | sí | — |
| `password` | `password` | sí | — |
| `dbname` | `database` | sí | — |
| `port` | `port` | no | `3306` |

Crear el secreto con la CLI (reemplazar los valores marcados como placeholder por los
valores reales al ejecutar el comando; **no dejar valores reales en documentos**):

```bash
# El blob JSON es el ÚNICO lugar donde viven las credenciales reales.
aws secretsmanager create-secret \
  --name "sgl/prod/rds-credentials" \
  --description "SGL production RDS credentials" \
  --secret-string '{"username":"REPLACE_ME","password":"CHANGE_ME","host":"REPLACE_ME.rds.amazonaws.com","port":3306,"dbname":"REPLACE_ME"}' \
  --region us-east-1
```

- `--name` es la **referencia** del secreto (nombre) que luego va en `.env`.
  También se puede usar el ARN completo como referencia.
- El campo `port` es opcional; si se omite, la app aplica `3306` (MySQL) por defecto.
- Todos los campos requeridos (`host`, `username`, `password`, `dbname`) deben estar
  presentes y no vacíos, o la resolución falla en cerrado (la app no abre conexión con
  credenciales parciales).

---

## 3. Rotar / reemplazar el secreto

Rotar la contraseña o mover de host RDS se hace **sin tocar el repositorio** publicando
un nuevo valor sobre el mismo secreto:

```bash
# Publica un nuevo valor (los placeholders se reemplazan al ejecutar):
aws secretsmanager put-secret-value \
  --secret-id "sgl/prod/rds-credentials" \
  --secret-string '{"username":"REPLACE_ME","password":"CHANGE_ME","host":"REPLACE_ME.rds.amazonaws.com","port":3306,"dbname":"REPLACE_ME"}' \
  --region us-east-1
```

- La app cachea el secreto resuelto **en memoria por la duración de cada request**, así
  que el nuevo valor se toma en los requests siguientes (o antes si expira el TTL, ver §4).
- La rotación **automática** con lambdas de rotación queda fuera de alcance (ver §7);
  aquí solo se documenta la rotación manual con `put-secret-value`.

---

## 4. Claves `.env` para el proveedor `aws`

Para el proveedor `aws`, `.env` contiene **únicamente** la referencia, la región y el
flag del proveedor. **Jamás** valores de secreto ni llaves de acceso de AWS.

Las cuatro claves soportadas (mostradas como referencia, sin valores sensibles):

- `SECRETS_PROVIDER` — `aws` o `env`. Por defecto `env` cuando está ausente.
- `SECRETS_RDS_REFERENCE` — nombre o ARN del secreto RDS (una referencia, nunca un valor).
- `SECRETS_REGION` — región de AWS del cliente de Secrets Manager.
- `SECRETS_CACHE_TTL` — opcional; segundos de vida del cache por request. `0` (o ausente)
  = válido durante todo el request.

Ejemplo de sección en `.env` (la referencia es un nombre, no un secreto):

```dotenv
SECRETS_PROVIDER=aws
SECRETS_RDS_REFERENCE=sgl/prod/rds-credentials
SECRETS_REGION=us-east-1
SECRETS_CACHE_TTL=0
```

> No existe ninguna clave que transporte un **valor** de secreto (no hay claves tipo
> "password" ni "access key" en la configuración). En el proveedor `aws`, el SDK obtiene
> credenciales temporales del **IAM Instance Profile** del EC2; no se define ninguna llave
> de acceso de AWS en `.env` ni en el repositorio.

---

## 5. Política IAM (adjuntar al rol del Instance Profile del EC2)

La app no usa llaves de acceso: el SDK toma credenciales temporales del **Instance
Profile** del EC2 vía la cadena de credenciales por defecto (endpoint de metadata).
El rol del Instance Profile debe otorgar `secretsmanager:GetSecretValue` **acotado al
ARN del secreto** (mínimo privilegio):

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "AllowReadSglRdsSecret",
      "Effect": "Allow",
      "Action": "secretsmanager:GetSecretValue",
      "Resource": "arn:aws:secretsmanager:us-east-1:<ACCOUNT_ID>:secret:sgl/prod/rds-credentials-*"
    }
  ]
}
```

- Reemplazar `<ACCOUNT_ID>` por el ID de la cuenta y ajustar región/nombre del secreto.
- El sufijo `-*` cubre los seis caracteres aleatorios que AWS añade al ARN del secreto;
  el permiso queda limitado a **este único secreto**.
- No se crea ni se usa ninguna llave de acceso de AWS: la autenticación es exclusivamente
  por el Instance Profile.

---

## 6. Activar y revertir (interruptor controlado y reversible)

**Activar** la resolución vía AWS (en el `.env` del EC2 con el Instance Profile ya
configurado según §5):

```dotenv
SECRETS_PROVIDER=aws
SECRETS_RDS_REFERENCE=sgl/prod/rds-credentials
SECRETS_REGION=us-east-1
```

**Revertir** (rollback inmediato) a credenciales `.env` planas:

```dotenv
SECRETS_PROVIDER=env
```

- También se puede **quitar** `SECRETS_PROVIDER`: al estar ausente, la app usa `env`
  por defecto y se comporta idéntico al sistema actual.
- El cambio no requiere modificaciones en el código de la aplicación; solo el `.env`.
- Si el secreto no se puede resolver (inalcanzable, inexistente, JSON inválido o campo
  faltante), la resolución **falla en cerrado**: la app no abre conexión con credenciales
  parciales y el error nombra la referencia y el motivo, **nunca** el valor del secreto.

---

## 7. Fuera de alcance

Los siguientes puntos **no** forman parte de esta funcionalidad y se mencionan solo como
trabajo futuro/relacionado:

- **Rotación automática de secretos** (lambdas de rotación de Secrets Manager). Aquí solo
  se cubre la rotación manual con `put-secret-value` (§3).
- **Gestión de llaves KMS** (creación/rotación de claves de cifrado del secreto).
- **Manejo de credenciales de GroceryCrud Enterprise**: el uploader propio de GroceryCrud
  y cualquier credencial específica suya no se cubren en esta integración.
