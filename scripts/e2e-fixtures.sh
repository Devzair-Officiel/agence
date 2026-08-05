#!/usr/bin/env bash
# Orchestrateur des fixtures E2E Phase 8B2.
#
# Rôle : encapsuler l'appel à `docker compose exec api bin/console
# app:editorial:e2e-fixtures <action>` pour que Playwright n'ait AUCUNE
# dépendance sur docker.sock ou sur la topologie Docker (voir correction 7
# du brief Phase 8B2). Playwright appelle uniquement Caddy via HTTP.
#
# Usage :
#   scripts/e2e-fixtures.sh load    # purge + insertion du jeu déterministe
#   scripts/e2e-fixtures.sh clear   # purge seulement (préfixe e2e-8b2-*)
#
# Pré-requis : la stack Docker Compose doit être démarrée
# (`docker compose up -d`). Le script échoue explicitement si l'API n'est
# pas joignable — pas de silencieux « rien à faire ».

set -euo pipefail

ACTION="${1:-}"

if [[ "$ACTION" != "load" && "$ACTION" != "clear" ]]; then
  echo "Usage: $0 <load|clear>" >&2
  exit 2
fi

# On se place à la racine du repo pour que compose.yaml soit trouvé
# indépendamment du répertoire courant de l'appelant.
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

if ! docker compose ps --status=running --services 2>/dev/null | grep -q '^api$'; then
  echo "ERREUR : le service 'api' n'est pas démarré. Lancez 'docker compose up -d' d'abord." >&2
  exit 1
fi

docker compose exec -T api bin/console app:editorial:e2e-fixtures "$ACTION"
