#!/usr/bin/env bash

set -euo pipefail

DB_NAME="${1:-technova}"
DB_USER="${DB_USER:-baptiste}"
DB_HOST="${DB_HOST:-127.0.0.1}"

echo "[1/4] Terminer les sessions actives sur ${DB_NAME}..." >&2
psql -h "$DB_HOST" -U "$DB_USER" -d postgres -c "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname='${DB_NAME}' AND pid <> pg_backend_pid();" >/dev/null

echo "[2/4] Drop + create" >&2
php bin/console doctrine:database:drop --force --if-exists
php bin/console doctrine:database:create

echo "[3/4] Migrations" >&2
php bin/console doctrine:migrations:migrate --no-interaction

echo "[4/4] Fixtures" >&2
php bin/console doctrine:fixtures:load --no-interaction

echo "Base ${DB_NAME} recréée avec succès." >&2
