#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
  writable/cache \
  writable/debugbar \
  writable/logs \
  writable/session \
  writable/uploads

chown -R www-data:www-data /var/www/html/writable

exec "$@"