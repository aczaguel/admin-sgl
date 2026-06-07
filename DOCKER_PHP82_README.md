# Docker PHP 8.2

## Objetivo

Levantar la aplicacion sobre PHP 8.2 sin depender del runtime instalado en el host y dejar un camino simple para servidor: construir imagen y ejecutar contenedor.

## Que Se Agrego

- [Dockerfile](Dockerfile): imagen basada en `php:8.2-apache` con extensiones necesarias para el proyecto.
- [docker-compose.yml](docker-compose.yml):
  - servicio `app` para servir la aplicacion
  - servicio `php82-spike` para correr el spike de compatibilidad
- [docker-compose.prod.yml](docker-compose.prod.yml): variante de despliegue para servidor, sin bind mount del codigo y con volumen persistente para `writable/`.
- [docker/apache/000-default.conf](docker/apache/000-default.conf): Apache con la raiz del repo como docroot para respetar el front controller activo del proyecto y los assets bajo `/public/`.
- [docker/php/php.ini](docker/php/php.ini): configuracion base para timezone, memoria y uploads.
- [docker/entrypoint.sh](docker/entrypoint.sh): inicializa permisos y estructura de `writable/` al arrancar el contenedor.

## Requisitos

- Docker
- Docker Compose plugin (`docker compose`)
- Un archivo `.env` valido en la raiz del proyecto

## Uso Local

Levantar la app:

```bash
docker compose up --build -d app
```

Queda publicada en:

```text
http://localhost:8080
```

Correr el spike PHP 8.2 dentro del contenedor:

```bash
docker compose --profile tools run --rm php82-spike
```

Resultado validado en esta rama:

- `spark` arranca dentro del contenedor PHP 8.2.
- Las pruebas focalizadas del spike pasan:
  - [tests/app/Controllers/Deskapp/TramitesStatusWebhookTest.php](tests/app/Controllers/Deskapp/TramitesStatusWebhookTest.php)
  - [tests/app/Controllers/Deskapp/TramitesnSessionRedirectTest.php](tests/app/Controllers/Deskapp/TramitesnSessionRedirectTest.php)
- Smoke HTTP real de API externa disponible en [admin/external-api-smoke.sh](admin/external-api-smoke.sh).
- Smoke HTTP autenticado para APIs JSON internas disponible en [admin/internal-json-smoke.sh](admin/internal-json-smoke.sh).
- Smoke web manual validado en `http://localhost:18080`:
  - login
  - dashboard
  - buscador de trámites
  - cobranza

Detener la app:

```bash
docker compose down
```

## Uso En Servidor

La idea operativa es esta:

1. Subir el repo con su `.env` del entorno.
2. Ejecutar `./admin/docker-prod.sh up`.
3. Si quieres validar compatibilidad antes de exponer trafico, ejecutar `./admin/docker-prod.sh spike`.

Con eso el host ya no necesita tener PHP 7.4 o 8.2 instalado para correr la app.

### Diferencia Entre Local Y Servidor

- Local: monta `./writable` desde el host para inspeccionar logs y archivos fácilmente.
- Servidor: usa un volumen Docker persistente para `writable/` y evita bind mounts del código durante la ejecución.
- La URL base dentro del contenedor se controla con `DOCKER_BASE_URL`. En local queda en `http://localhost:PUERTO/` por default; en servidor conviene apuntarla a la URL pública real.
- El host de base de datos dentro del contenedor se controla con `DOCKER_DB_HOST`. En local queda en `host.docker.internal` por default; en servidor el wrapper usa `localhost` por default.

### Comandos De Servidor

Levantar app:

```bash
./admin/docker-prod.sh up
```

Con URL pública explícita:

```bash
DOCKER_BASE_URL=http://tu-dominio-o-ip/ ./admin/docker-prod.sh up
```

Con host de base de datos explícito:

```bash
DOCKER_DB_HOST=127.0.0.1 ./admin/docker-prod.sh up
```

Ver estado:

```bash
./admin/docker-prod.sh ps
```

Ver logs:

```bash
./admin/docker-prod.sh logs
```

Ejecutar spike:

```bash
./admin/docker-prod.sh spike
```

Ver configuracion expandida:

```bash
./admin/docker-prod.sh config
```

Si el servidor necesita otro puerto publicado, se puede sobreescribir antes del comando:

```bash
DOCKER_APP_PORT=8081 ./admin/docker-prod.sh up
```

Si además necesitas que los redirects y assets salgan con otra URL base:

```bash
DOCKER_APP_PORT=8081 DOCKER_BASE_URL=http://tu-dominio-o-ip:8081/ ./admin/docker-prod.sh up
```

Y si la base vive fuera del mismo host:

```bash
DOCKER_APP_PORT=8081 DOCKER_BASE_URL=http://tu-dominio-o-ip:8081/ DOCKER_DB_HOST=10.0.0.25 ./admin/docker-prod.sh up
```

## Notas

- Este setup asume que el repo ya incluye `vendor/` al momento de construir la imagen.
- La base de datos sigue viniendo de la configuracion del proyecto a traves de `.env`.
- El directorio `writable/` se monta desde host para conservar logs y archivos generados.
- La imagen fue optimizada para copiar el repo con propietario `www-data` y evitar un `chown -R` costoso en cada build.
- En servidor, `docker-compose.prod.yml` reemplaza ese bind mount por un volumen persistente `writable_data`.