#!/bin/bash

set -euo pipefail

SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
REPO_ROOT="$(CDPATH= cd -- "$SCRIPT_DIR/.." && pwd)"
PHP_BIN="${1:-php}"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
LOG_DIR="$REPO_ROOT/writable/logs"
LOG_FILE="$LOG_DIR/php82-spike-$TIMESTAMP.log"

mkdir -p "$LOG_DIR"

exec > >(tee -a "$LOG_FILE") 2>&1

run_step() {
  local description="$1"
  shift

  echo
  echo "== $description =="
  "$@"
}

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  echo "ERROR: No se encontro el binario PHP indicado: $PHP_BIN"
  exit 1
fi

echo "PHP 8.2 spike"
echo "Repo: $REPO_ROOT"
echo "PHP bin: $(command -v "$PHP_BIN")"
echo "Log: $LOG_FILE"

run_step "PHP version" "$PHP_BIN" -v

PHP_VERSION_ID="$($PHP_BIN -r 'echo PHP_VERSION_ID;')"

if [[ "$PHP_VERSION_ID" -lt 80200 ]]; then
  echo
  echo "ERROR: Este spike requiere PHP 8.2+ y recibio PHP_VERSION_ID=$PHP_VERSION_ID"
  exit 2
fi

cd "$REPO_ROOT"

run_step "PHP modules" "$PHP_BIN" -m
run_step "Framework bootstrap via spark" "$PHP_BIN" spark
run_step "PHPUnit version" "$PHP_BIN" vendor/phpunit/phpunit/phpunit --version
run_step "Focused test: TramitesStatusWebhookTest" "$PHP_BIN" vendor/phpunit/phpunit/phpunit --bootstrap system/Test/bootstrap.php tests/app/Controllers/Deskapp/TramitesStatusWebhookTest.php
run_step "Focused test: TramitesnSessionRedirectTest" "$PHP_BIN" vendor/phpunit/phpunit/phpunit --bootstrap system/Test/bootstrap.php tests/app/Controllers/Deskapp/TramitesnSessionRedirectTest.php

echo
echo "Spike completado. Revisar log: $LOG_FILE"