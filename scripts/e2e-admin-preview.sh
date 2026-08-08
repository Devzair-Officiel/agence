#!/usr/bin/env bash
# Orchestrateur des fixtures E2E — Phase 8C4 (prévisualisation admin).
#
# Rôle : purger les articles créés par la suite Playwright de prévisualisation
# admin (`admin-preview.spec.ts`). Comme la suite 8C3, la suite 8C4 CRÉE les
# articles via l'interface admin elle-même — c'est précisément ce qu'on veut
# tester. Le script n'a donc qu'un rôle de nettoyage :
#   - `load`  : garantit un état de départ propre (DELETE préventif),
#                de sorte que la suite ne trouve jamais de résidu d'un
#                run précédent qui aurait crashé avant `clear` ;
#   - `clear` : supprime les articles créés pendant la suite.
#
# Garde-fous (identiques à `e2e-admin-articles.sh`) :
#   - la constante `E2E_SLUG_PREFIX` est CODÉE EN DUR (`e2e-8c4-preview-`) ;
#     aucune substitution possible via variable d'environnement ;
#   - `DELETE ... WHERE slug LIKE 'e2e-8c4-preview-%'` — jamais `TRUNCATE`,
#     jamais `DELETE` sans clause de préfixe ; le préfixe est plus long
#     que celui de la 8C3 pour éviter toute ambiguïté si un futur script
#     `e2e-8c4-quelquechose` était ajouté ;
#   - `ON_ERROR_STOP=1` : psql échoue au premier problème, jamais silencieux ;
#   - le script échoue explicitement si api/postgres ne sont pas démarrés.
#
# Usage :
#   scripts/e2e-admin-preview.sh load    # purge préventive (état propre)
#   scripts/e2e-admin-preview.sh clear   # purge post-suite
#
# Variables d'environnement :
#   POSTGRES_USER — utilisateur psql côté conteneur (défaut `devzair`)
#   POSTGRES_DB   — base ciblée (défaut `devzair`)

set -euo pipefail

# Constante volontairement codée en dur — garde-fou n°1.
readonly E2E_SLUG_PREFIX='e2e-8c4-preview-'

ACTION="${1:-}"

if [[ "$ACTION" != "load" && "$ACTION" != "clear" ]]; then
  echo "Usage: $0 <load|clear>" >&2
  exit 2
fi

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

if ! docker compose ps --status=running --services 2>/dev/null | grep -q '^api$'; then
  echo "ERREUR : le service 'api' n'est pas démarré. Lancez 'docker compose up -d' d'abord." >&2
  exit 1
fi

if ! docker compose ps --status=running --services 2>/dev/null | grep -q '^postgres$'; then
  echo "ERREUR : le service 'postgres' n'est pas démarré. Lancez 'docker compose up -d' d'abord." >&2
  exit 1
fi

POSTGRES_USER="${POSTGRES_USER:-devzair}"
POSTGRES_DB="${POSTGRES_DB:-devzair}"

# `load` et `clear` exécutent la même requête : purger tout article dont le
# slug commence par le préfixe E2E. Un `load` en tête de suite garantit un
# état propre si le `clear` du run précédent n'a pas pu s'exécuter (crash).
docker compose exec -T postgres \
  psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" \
  -c "DELETE FROM editorial_article WHERE slug LIKE '${E2E_SLUG_PREFIX}%';" \
  > /dev/null

case "$ACTION" in
  load)
    echo "État de départ propre : articles preview E2E purgés (préfixe '${E2E_SLUG_PREFIX}')."
    ;;
  clear)
    echo "Articles preview E2E purgés (préfixe '${E2E_SLUG_PREFIX}')."
    ;;
esac
