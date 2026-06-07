#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

export DOCKER_APP_PORT="${DOCKER_APP_PORT:-80}"
export DOCKER_BASE_URL="${DOCKER_BASE_URL:-http://localhost:${DOCKER_APP_PORT}/}"
export DOCKER_DB_HOST="${DOCKER_DB_HOST:-localhost}"

cd "${ROOT_DIR}"

compose() {
  docker compose -f docker-compose.yml -f docker-compose.prod.yml "$@"
}

usage() {
  cat <<'EOF'
Uso: ./admin/docker-prod.sh [up|down|ps|logs|spike|config]

Comandos:
  up      Construye y levanta la app en segundo plano.
  down    Baja los servicios de la variante de servidor.
  ps      Muestra el estado actual.
  logs    Sigue los logs de la app.
  spike   Ejecuta el spike PHP 8.2 en la compose de servidor.
  config  Imprime la configuracion expandida de Docker Compose.

Variables:
  DOCKER_APP_PORT   Puerto publicado en el host. Default: 80.
  DOCKER_BASE_URL   URL base efectiva dentro del contenedor. Default: http://localhost:${DOCKER_APP_PORT}/
  DOCKER_DB_HOST    Host de base de datos dentro del contenedor. Default: localhost.
EOF
}

command="${1:-up}"

case "${command}" in
  up)
    compose up --build -d app
    ;;
  down)
    compose down
    ;;
  ps)
    compose ps
    ;;
  logs)
    compose logs -f app
    ;;
  spike)
    compose --profile tools run --rm php82-spike
    ;;
  config)
    compose config
    ;;
  help|-h|--help)
    usage
    ;;
  *)
    usage >&2
    exit 1
    ;;
esac