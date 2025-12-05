#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
COLLECTION_PATH="${1:-$ROOT_DIR/postman/technova-api.postman_collection.json}"
ENV_PATH="${2:-$ROOT_DIR/postman/local.postman_environment.json}"

if [[ ! -f "$COLLECTION_PATH" ]]; then
  echo "Collection introuvable : $COLLECTION_PATH" >&2
  exit 1
fi

if [[ ! -f "$ENV_PATH" ]]; then
  echo "Environnement introuvable : $ENV_PATH" >&2
  exit 1
fi

if command -v newman >/dev/null 2>&1; then
  NEWMAN_CMD="newman"
else
  NEWMAN_CMD="npx --yes newman"
fi

echo "[TechNova] Lancement des tests Postman via $NEWMAN_CMD"
set -x
$NEWMAN_CMD run "$COLLECTION_PATH" -e "$ENV_PATH" --color on "$@"
