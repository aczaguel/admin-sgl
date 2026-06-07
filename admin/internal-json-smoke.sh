#!/bin/bash

set -euo pipefail

SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
REPO_ROOT="$(CDPATH= cd -- "$SCRIPT_DIR/.." && pwd)"

fail() {
  echo "ERROR: $1" >&2
  exit 1
}

BASE_URL="${SGL_BASE_URL:-http://localhost:${DOCKER_APP_PORT:-18080}}"
BASE_URL="${BASE_URL%/}"
APP_SERVICE="${SGL_APP_SERVICE:-app}"
WEB_USERNAME="${SGL_WEB_USERNAME:-luisa.flores}"
WEB_PASSWORD="${SGL_WEB_PASSWORD:-Demo1234!}"

TMP_DIR="$(mktemp -d)"
COOKIE_FILE="$TMP_DIR/cookies.txt"
BODY_FILE="$TMP_DIR/body.txt"
trap 'rm -rf "$TMP_DIR"' EXIT

echo
echo "== Seed demo data =="
docker compose exec -T "$APP_SERVICE" php spark sgl:demo-data --count=2

echo
echo "== Login =="
LOGIN_STATUS="$(curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" -o "$BODY_FILE" -w '%{http_code}' \
  -X POST "$BASE_URL/deskapp/login/auth" \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  --data "username=$WEB_USERNAME&password=$WEB_PASSWORD")"
head -n 5 "$BODY_FILE"
echo
[[ "$LOGIN_STATUS" == "302" || "$LOGIN_STATUS" == "303" ]] || fail "Se esperaba redirect exitoso en login y llegó $LOGIN_STATUS"
grep -q 'ci_session' "$COOKIE_FILE" || fail "No se generó cookie de sesión tras el login"

echo
echo "== GET /notifications/api_count =="
NOTIF_COUNT_STATUS="$(curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" -o "$BODY_FILE" -w '%{http_code}' \
  -H 'Accept: application/json' \
  "$BASE_URL/notifications/api_count")"
cat "$BODY_FILE"
echo
[[ "$NOTIF_COUNT_STATUS" == "200" ]] || fail "Se esperaba 200 en notifications/api_count y llegó $NOTIF_COUNT_STATUS"
grep -q '"success"[[:space:]]*:[[:space:]]*true' "$BODY_FILE" || fail "notifications/api_count no devolvió success=true"
grep -q '"count"' "$BODY_FILE" || fail "notifications/api_count no devolvió count"

echo
echo "== GET /notifications/api_unread =="
NOTIF_UNREAD_STATUS="$(curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" -o "$BODY_FILE" -w '%{http_code}' \
  -H 'Accept: application/json' \
  "$BASE_URL/notifications/api_unread")"
cat "$BODY_FILE"
echo
[[ "$NOTIF_UNREAD_STATUS" == "200" ]] || fail "Se esperaba 200 en notifications/api_unread y llegó $NOTIF_UNREAD_STATUS"
grep -q '"success"[[:space:]]*:[[:space:]]*true' "$BODY_FILE" || fail "notifications/api_unread no devolvió success=true"
grep -q '"notifications"' "$BODY_FILE" || fail "notifications/api_unread no devolvió notifications"

echo
echo "== GET /notifications/api_load_more =="
NOTIF_LOAD_MORE_STATUS="$(curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" -o "$BODY_FILE" -w '%{http_code}' \
  -H 'Accept: application/json' \
  "$BASE_URL/notifications/api_load_more?offset=0")"
cat "$BODY_FILE"
echo
[[ "$NOTIF_LOAD_MORE_STATUS" == "200" ]] || fail "Se esperaba 200 en notifications/api_load_more y llegó $NOTIF_LOAD_MORE_STATUS"
grep -q '"success"[[:space:]]*:[[:space:]]*true' "$BODY_FILE" || fail "notifications/api_load_more no devolvió success=true"
grep -q '"has_more"' "$BODY_FILE" || fail "notifications/api_load_more no devolvió has_more"

echo
echo "== POST /notifications/api_mark_all_read =="
NOTIF_MARK_ALL_STATUS="$(curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" -o "$BODY_FILE" -w '%{http_code}' \
  -X POST \
  -H 'Accept: application/json' \
  "$BASE_URL/notifications/api_mark_all_read")"
cat "$BODY_FILE"
echo
[[ "$NOTIF_MARK_ALL_STATUS" == "200" ]] || fail "Se esperaba 200 en notifications/api_mark_all_read y llegó $NOTIF_MARK_ALL_STATUS"
grep -q '"success"[[:space:]]*:[[:space:]]*true' "$BODY_FILE" || fail "notifications/api_mark_all_read no devolvió success=true"

echo
echo "== GET /deskapp/dashboardadmin/api_kpis =="
DASH_KPIS_STATUS="$(curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" -o "$BODY_FILE" -w '%{http_code}' \
  -H 'Accept: application/json' \
  "$BASE_URL/deskapp/dashboardadmin/api_kpis")"
cat "$BODY_FILE"
echo
[[ "$DASH_KPIS_STATUS" == "200" ]] || fail "Se esperaba 200 en dashboardadmin/api_kpis y llegó $DASH_KPIS_STATUS"
grep -q 'tramites_activos' "$BODY_FILE" || fail "dashboardadmin/api_kpis no devolvió tramites_activos"
grep -q 'eficiencia_cobro' "$BODY_FILE" || fail "dashboardadmin/api_kpis no devolvió eficiencia_cobro"

echo
echo "== GET /deskapp/dashboardadmin/api_metricas =="
DASH_METRICAS_STATUS="$(curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" -o "$BODY_FILE" -w '%{http_code}' \
  -H 'Accept: application/json' \
  "$BASE_URL/deskapp/dashboardadmin/api_metricas")"
cat "$BODY_FILE"
echo
[[ "$DASH_METRICAS_STATUS" == "200" ]] || fail "Se esperaba 200 en dashboardadmin/api_metricas y llegó $DASH_METRICAS_STATUS"
grep -q 'total_ingresados' "$BODY_FILE" || fail "dashboardadmin/api_metricas no devolvió total_ingresados"
grep -q 'total_concluidos' "$BODY_FILE" || fail "dashboardadmin/api_metricas no devolvió total_concluidos"

echo
echo "== GET /deskapp/dashboardadmin/api_alertas =="
DASH_ALERTAS_STATUS="$(curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" -o "$BODY_FILE" -w '%{http_code}' \
  -H 'Accept: application/json' \
  "$BASE_URL/deskapp/dashboardadmin/api_alertas")"
cat "$BODY_FILE"
echo
[[ "$DASH_ALERTAS_STATUS" == "200" ]] || fail "Se esperaba 200 en dashboardadmin/api_alertas y llegó $DASH_ALERTAS_STATUS"
grep -Eq 'retrasados|pendientes_cobro|estancados|\[\]' "$BODY_FILE" || fail "dashboardadmin/api_alertas no devolvió una estructura JSON reconocible"

echo
echo "Smoke JSON interno PHP 8.2 completado."
echo "- Base URL: $BASE_URL"
echo "- Usuario: $WEB_USERNAME"