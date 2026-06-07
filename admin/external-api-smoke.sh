#!/bin/bash

set -euo pipefail

SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
REPO_ROOT="$(CDPATH= cd -- "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="$REPO_ROOT/.env"

read_env_value() {
  local key="$1"

  awk -F '=' -v key="$key" '
    $1 ~ "^[[:space:]]*" key "[[:space:]]*$" {
      value = substr($0, index($0, "=") + 1)
      gsub(/^[[:space:]]+|[[:space:]]+$/, "", value)
      gsub(/^"|"$/, "", value)
      gsub(/^'"'"'|'"'"'$/, "", value)
      print value
      exit
    }
  ' "$ENV_FILE"
}

fail() {
  echo "ERROR: $1" >&2
  exit 1
}

BASE_URL="${SGL_API_BASE_URL:-http://localhost:${DOCKER_APP_PORT:-18080}}"
BASE_URL="${BASE_URL%/}"
APP_SERVICE="${SGL_APP_SERVICE:-app}"
API_KEY="${SGL_API_KEY:-$(read_env_value 'externalApi.keys' | cut -d ',' -f 1 | xargs)}"
SOURCE_SYSTEM="${SGL_SOURCE_SYSTEM:-$(read_env_value 'externalApi.defaultSourceSystem')}"
DB_HOST="${SGL_DB_HOST:-${DOCKER_DB_HOST:-host.docker.internal}}"
DB_NAME="${SGL_DB_NAME:-$(read_env_value 'database.default.database')}"
DB_USER="${SGL_DB_USER:-$(read_env_value 'database.default.username')}"
DB_PASSWORD="${SGL_DB_PASSWORD:-$(read_env_value 'database.default.password')}"

[[ -f "$ENV_FILE" ]] || fail "No existe .env en $REPO_ROOT"
[[ -n "$API_KEY" ]] || fail "No se pudo resolver SGL_API_KEY ni externalApi.keys desde .env"
[[ -n "$SOURCE_SYSTEM" ]] || fail "No se pudo resolver SGL_SOURCE_SYSTEM ni externalApi.defaultSourceSystem desde .env"
[[ -n "$DB_NAME" ]] || fail "No se pudo resolver database.default.database desde .env"
[[ -n "$DB_USER" ]] || fail "No se pudo resolver database.default.username desde .env"

TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT

echo
echo "== Seed demo data =="
docker compose exec -T "$APP_SERVICE" php spark sgl:demo-data --count=2

echo
echo "== Resolve runtime IDs =="
RESOLVED_IDS="$(docker compose exec -T \
  -e SGL_DB_HOST="$DB_HOST" \
  -e SGL_DB_NAME="$DB_NAME" \
  -e SGL_DB_USER="$DB_USER" \
  -e SGL_DB_PASSWORD="$DB_PASSWORD" \
  "$APP_SERVICE" php -r '
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(
        (string) getenv("SGL_DB_HOST"),
        (string) getenv("SGL_DB_USER"),
        (string) getenv("SGL_DB_PASSWORD"),
        (string) getenv("SGL_DB_NAME")
    );
    $scalar = static function (mysqli $db, string $sql): string {
        $result = $db->query($sql);
        $row = $result->fetch_row();
        return $row ? (string) $row[0] : "";
    };
    $cliDirectoId = (int) $scalar($db, "SELECT id FROM cli_directo WHERE razon_social = \"Cliente Directo Yolanda\" ORDER BY id ASC LIMIT 1");
    $ejecutivoId = (int) $scalar($db, "SELECT id FROM cli_directo_ejecutivo WHERE cli_directo_id = {$cliDirectoId} AND nombre = \"Ejecutivo Yolanda\" ORDER BY id ASC LIMIT 1");
    $ejecutivoUserId = (int) $scalar($db, "SELECT COALESCE(user_id, 0) FROM cli_directo_ejecutivo WHERE id = {$ejecutivoId} LIMIT 1");
    $traTiposId = (int) $scalar($db, "SELECT id FROM tra_tipos WHERE tipo_tramite = \"ALTA PADRON\" ORDER BY id ASC LIMIT 1");
    if ($traTiposId <= 0) {
        $traTiposId = (int) $scalar($db, "SELECT id FROM tra_tipos ORDER BY id ASC LIMIT 1");
    }
    $entidadId = (int) $scalar($db, "SELECT id FROM entidad ORDER BY id ASC LIMIT 1");
    $entMunicipioId = (int) $scalar($db, "SELECT ent_municipality_id FROM rel_ent_municipio WHERE id_entity = {$entidadId} ORDER BY ent_municipality_id ASC LIMIT 1");
    foreach ([
        "CLI_DIRECTO_ID" => $cliDirectoId,
        "EJECUTIVO_ID" => $ejecutivoId,
        "EJECUTIVO_USER_ID" => $ejecutivoUserId,
        "TRA_TIPOS_ID" => $traTiposId,
        "ENTIDAD_ID" => $entidadId,
        "ENT_MUNICIPIO_ID" => $entMunicipioId,
    ] as $key => $value) {
        if ((int) $value <= 0) {
            fwrite(STDERR, "No se pudo resolver {$key} para el smoke de API.\n");
            exit(1);
        }
        echo $key . "=" . (int) $value . PHP_EOL;
    }
  ')"
eval "$RESOLVED_IDS"

echo "CLI_DIRECTO_ID=$CLI_DIRECTO_ID"
echo "EJECUTIVO_ID=$EJECUTIVO_ID"
echo "EJECUTIVO_USER_ID=$EJECUTIVO_USER_ID"
echo "TRA_TIPOS_ID=$TRA_TIPOS_ID"
echo "ENTIDAD_ID=$ENTIDAD_ID"
echo "ENT_MUNICIPIO_ID=$ENT_MUNICIPIO_ID"

if [[ "$EJECUTIVO_USER_ID" -le 0 ]]; then
  fail "El ejecutivo demo no tiene user_id ligado; corre php spark sgl:demo-data con la version actualizada."
fi

echo
echo "== Unauthorized check =="
UNAUTH_FILE="$TMP_DIR/unauthorized.json"
UNAUTH_STATUS="$(curl -s -o "$UNAUTH_FILE" -w '%{http_code}' "$BASE_URL/api/v1/tramites/1")"
cat "$UNAUTH_FILE"
echo
[[ "$UNAUTH_STATUS" == "401" ]] || fail "Se esperaba 401 sin API key y llegó $UNAUTH_STATUS"

echo
echo "== Invalid API key check =="
INVALID_KEY_FILE="$TMP_DIR/invalid-key.json"
INVALID_KEY_STATUS="$(curl -s -o "$INVALID_KEY_FILE" -w '%{http_code}' \
  -H 'Accept: application/json' \
  -H 'X-API-Key: invalid-smoke-key' \
  "$BASE_URL/api/v1/tramites/1")"
cat "$INVALID_KEY_FILE"
echo
[[ "$INVALID_KEY_STATUS" == "401" ]] || fail "Se esperaba 401 con API key inválida y llegó $INVALID_KEY_STATUS"
grep -q 'Credencial de API inválida' "$INVALID_KEY_FILE" || fail "No apareció el mensaje esperado de credencial inválida"

REF="ERP-SMOKE-$(date +%Y%m%d%H%M%S)"
IDEMPOTENCY_KEY="idem-$REF"
CREATE_FILE="$TMP_DIR/create.json"
SHOW_REF_FILE="$TMP_DIR/show-ref.json"
SHOW_ID_FILE="$TMP_DIR/show-id.json"

PAYLOAD="$(printf '{"external_reference":"%s","contrato":"CTR-%s","unidad":"Unidad Smoke API","serie":"SER-%s","placas":"SMK123","tra_tipos_id":%s,"entidad_id":%s,"ent_municipio_id":%s,"cli_directo_id":%s,"cli_directo_ejecutivo_id":%s,"observaciones":"Smoke HTTP PHP 8.2"}' "$REF" "$REF" "$REF" "$TRA_TIPOS_ID" "$ENTIDAD_ID" "$ENT_MUNICIPIO_ID" "$CLI_DIRECTO_ID" "$EJECUTIVO_ID")"

echo
echo "== POST /api/v1/tramites =="
CREATE_STATUS="$(curl -s -o "$CREATE_FILE" -w '%{http_code}' -X POST "$BASE_URL/api/v1/tramites" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H "X-API-Key: $API_KEY" \
  -H "Idempotency-Key: $IDEMPOTENCY_KEY" \
  -H "X-Source-System: $SOURCE_SYSTEM" \
  --data "$PAYLOAD")"
cat "$CREATE_FILE"
echo
[[ "$CREATE_STATUS" == "201" ]] || fail "Se esperaba 201 en create y llegó $CREATE_STATUS"
grep -q '"success"[[:space:]]*:[[:space:]]*true' "$CREATE_FILE" || fail "La respuesta create no marcó success=true"

TRAMITE_ID="$(tr -d '\n' < "$CREATE_FILE" | sed -n 's/.*"tramite_id"[[:space:]]*:[[:space:]]*"\{0,1\}\([0-9][0-9]*\)"\{0,1\}.*/\1/p')"
[[ -n "$TRAMITE_ID" ]] || fail "No se pudo extraer tramite_id de la respuesta create"

echo
echo "== GET /api/v1/tramites/referencia/$REF =="
SHOW_REF_STATUS="$(curl -s -o "$SHOW_REF_FILE" -w '%{http_code}' \
  -H 'Accept: application/json' \
  -H "X-API-Key: $API_KEY" \
  -H "X-Source-System: $SOURCE_SYSTEM" \
  "$BASE_URL/api/v1/tramites/referencia/$REF")"
cat "$SHOW_REF_FILE"
echo
[[ "$SHOW_REF_STATUS" == "200" ]] || fail "Se esperaba 200 en showByReference y llegó $SHOW_REF_STATUS"
grep -q "$REF" "$SHOW_REF_FILE" || fail "La referencia externa no apareció en showByReference"

echo
echo "== GET /api/v1/tramites/$TRAMITE_ID =="
SHOW_ID_STATUS="$(curl -s -o "$SHOW_ID_FILE" -w '%{http_code}' \
  -H 'Accept: application/json' \
  -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/v1/tramites/$TRAMITE_ID")"
cat "$SHOW_ID_FILE"
echo
[[ "$SHOW_ID_STATUS" == "200" ]] || fail "Se esperaba 200 en show por id y llegó $SHOW_ID_STATUS"
grep -q "\"id\"[[:space:]]*:[[:space:]]*\"$TRAMITE_ID\"" "$SHOW_ID_FILE" || fail "El trámite esperado no apareció en show por id"

echo
echo "== Idempotency conflict check =="
CONFLICT_FILE="$TMP_DIR/conflict.json"
CONFLICT_PAYLOAD="$(printf '{"external_reference":"%s-DIFF","contrato":"CTR-%s-DIFF","unidad":"Unidad Smoke API Conflict","serie":"SER-%s-DIFF","placas":"SMK124","tra_tipos_id":%s,"entidad_id":%s,"ent_municipio_id":%s,"cli_directo_id":%s,"cli_directo_ejecutivo_id":%s,"observaciones":"Smoke conflict PHP 8.2"}' "$REF" "$REF" "$REF" "$TRA_TIPOS_ID" "$ENTIDAD_ID" "$ENT_MUNICIPIO_ID" "$CLI_DIRECTO_ID" "$EJECUTIVO_ID")"
CONFLICT_STATUS="$(curl -s -o "$CONFLICT_FILE" -w '%{http_code}' -X POST "$BASE_URL/api/v1/tramites" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H "X-API-Key: $API_KEY" \
  -H "Idempotency-Key: $IDEMPOTENCY_KEY" \
  -H "X-Source-System: $SOURCE_SYSTEM" \
  --data "$CONFLICT_PAYLOAD")"
cat "$CONFLICT_FILE"
echo
[[ "$CONFLICT_STATUS" == "409" ]] || fail "Se esperaba 409 por Idempotency-Key reutilizado con payload distinto y llegó $CONFLICT_STATUS"
grep -q 'Idempotency-Key ya fue utilizado con un payload distinto' "$CONFLICT_FILE" || fail "No apareció el mensaje esperado de conflicto idempotente"

echo
echo "Smoke API PHP 8.2 completado."
echo "- Base URL: $BASE_URL"
echo "- External reference: $REF"
echo "- Tramite ID: $TRAMITE_ID"