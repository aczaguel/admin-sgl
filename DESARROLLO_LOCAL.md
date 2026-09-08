# Guía de Desarrollo Local — SGL

Instrucciones completas para levantar el Sistema de Gestión de Licencias (SGL) en un entorno de desarrollo local.

---

## Requisitos previos

| Herramienta | Versión mínima | Notas |
|---|---|---|
| Docker Desktop | 4.x | Incluye Docker Compose v2 |
| Git | 2.x | |
| MySQL | 8.x | Externo al container (corre en el host o en otro container) |
| AWS CLI | 2.x | Solo si usas S3 local |

> **Base de datos:** El container de la app **no incluye MySQL**. La DB corre en el host (MAMP, Homebrew, TablePlus, etc.) o en un container separado.

---

## 1. Clonar el repositorio

```bash
git clone git@github.com:aczaguel/admin-sgl.git
cd admin-sgl
git checkout chore/php82-diagnostic   # rama de desarrollo activa
```

---

## 2. Configurar variables de entorno

### `.env` — Configuración principal

```bash
cp .env.example .env   # si existe, o edita .env directamente
```

Variables clave a configurar:

```ini
# Entorno
CI_ENVIRONMENT = development

# Base de datos (host externo al container)
database.default.hostname = host.docker.internal
database.default.database = procedures
database.default.username = root
database.default.password = TU_PASSWORD_MYSQL
database.default.DBDriver = MySQLi

# URL base (debe coincidir con el puerto del container)
app.baseURL = 'http://localhost:18080/'

# Almacenamiento de archivos
FILE_STORAGE_DRIVER = local   # usa 'local' para desarrollo, 's3' para prod
```

### `docker/aws.env` — Credenciales AWS (solo si usas S3)

```bash
cp docker/aws.env.example docker/aws.env   # si existe
```

Edita con las credenciales del bucket de desarrollo:

```ini
AWS_ACCESS_KEY_ID=AKIAXXXXXXXXXXXXXXXX
AWS_SECRET_ACCESS_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
AWS_DEFAULT_REGION=us-east-1
S3_BUCKET=bucket-sgl-uploads-dev
FILE_STORAGE_DRIVER=s3
```

> Si `docker/aws.env` no existe, créalo vacío para que Docker no falle:
> ```bash
> touch docker/aws.env
> ```

---

## 3. Construir y levantar el container

```bash
# Primera vez (construye la imagen PHP 8.2)
docker compose build

# Levantar en background
docker compose up -d

# Ver logs en tiempo real
docker compose logs -f app
```

La aplicación estará disponible en: **http://localhost:18080**

### Puerto personalizado

Si el puerto 18080 está ocupado, cámbialo en `.env`:

```ini
DOCKER_APP_PORT=18081
```

---

## 4. Verificar que funciona

1. Abre **http://localhost:18080/deskapp/auth/login**
2. Ingresa con las credenciales de tu DB local
3. Si ves el dashboard → todo está bien

### Debugging de conexión a DB

```bash
# Entrar al container
docker exec -it admin-sgl-app bash

# Probar conexión MySQL desde dentro del container
php -r "
\$conn = new mysqli('host.docker.internal', 'root', 'password', 'procedures');
echo \$conn->connect_error ? 'ERROR: '.\$conn->connect_error : 'OK';
"
```

---

## 5. Comandos útiles del día a día

```bash
# Levantar
docker compose up -d

# Detener
docker compose down

# Reiniciar solo la app (después de cambios de config)
docker compose restart app

# Ver logs
docker compose logs app --tail=50 -f

# Entrar al container
docker exec -it admin-sgl-app bash

# Limpiar caché de CodeIgniter
docker exec admin-sgl-app php spark cache:clear

# Ver estado de los containers
docker compose ps
```

---

## 6. BookStack (documentación)

BookStack corre en un `docker-compose.yml` separado en `~/Sites/bookstack/`:

```bash
cd ~/Sites/bookstack
docker compose up -d
```

Accesible en: **http://localhost:8090**

Credenciales por defecto: `admin@admin.com` / `password`

### Actualizar contenido de la documentación

```bash
cd ~/Sites/admin-sgl
pip3 install requests   # solo la primera vez
python3 docs/bookstack/seed.py \
  --url http://localhost:8090 \
  --token TU_TOKEN_ID:TU_TOKEN_SECRET
```

El token se obtiene en BookStack: **Tu nombre → Editar perfil → Tokens API**.

---

## 7. Estructura del proyecto

```
admin-sgl/
├── app/
│   ├── Controllers/Deskapp/   # Controllers principales
│   ├── Models/                # Modelos de base de datos
│   ├── Views/deskapp/         # Vistas del sistema
│   └── Config/                # Configuración de CI4
├── public/
│   └── assets/src/            # CSS y JS del frontend
├── docker/
│   └── aws.env                # Credenciales AWS (gitignored)
├── docs/
│   └── bookstack/             # Scripts de documentación
├── terraform-prod-iaas/       # Infraestructura de producción (Terraform)
├── docker-compose.yml         # Entorno de desarrollo local
├── Dockerfile                 # Imagen PHP 8.2
├── .env                       # Variables de entorno (gitignored)
├── DESARROLLO_LOCAL.md        # Este archivo
└── OPERACIONES.md             # Guía de operaciones en producción
```

---

## 8. Ramas del repositorio

| Rama | Propósito |
|---|---|
| `main` | Código estable en producción |
| `chore/php82-diagnostic` | Rama de desarrollo activa (PHP 8.2 + nuevo flujo) |

```bash
# Cambiar a la rama de desarrollo
git checkout chore/php82-diagnostic

# Actualizar
git pull origin chore/php82-diagnostic
```

---

## 9. Despliegue a producción

Ver **OPERACIONES.md** para el proceso completo de despliegue al EC2.

Resumen rápido:

```bash
# En el EC2
cd /opt/sgl
git pull origin chore/php82-diagnostic
docker compose restart app
docker compose exec app php spark cache:clear
```

---

## 10. Problemas comunes

### "Cannot connect to MySQL"
- Verifica que MySQL esté corriendo en el host.
- Usa `host.docker.internal` (no `localhost`) en `.env`.
- Confirma usuario, contraseña y nombre de la DB.

### "Class not found" o errores de autoload
```bash
docker compose restart app
docker exec admin-sgl-app php spark cache:clear
```

### El puerto 18080 está ocupado
Cambia `DOCKER_APP_PORT` en `.env` y reinicia:
```bash
docker compose down && docker compose up -d
```

### Imágenes/archivos no cargan (modo local)
Verifica que `FILE_STORAGE_DRIVER=local` en `.env`. Los archivos se sirven desde `public/assets/uploads/`.

### BookStack no carga en http://localhost:8090
```bash
cd ~/Sites/bookstack
docker compose ps          # verificar que ambos containers estén Up
docker compose restart bookstack   # reiniciar si hay errores
```
