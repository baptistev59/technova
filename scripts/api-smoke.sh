#!/usr/bin/env bash
set -euo pipefail

# Petit script de smoke-test pour l'API TechNova.
# Usage : bash scripts/api-smoke.sh https://technova.alwaysdata.net

BASE_URL="${1:-http://127.0.0.1:8000}"

echo "[1/3] Healthcheck /api/test" >&2
curl -sf "$BASE_URL/api/test" | jq .

echo "[2/3] Test audit (JWT facultatif)" >&2
curl -sf "$BASE_URL/api/test-audit" | jq .

echo "[3/3] Catalogue (public)" >&2
curl -sf "$BASE_URL/api/products" | jq '.| length'

echo "Smoke-test terminé." >&2
