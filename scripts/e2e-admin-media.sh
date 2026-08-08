#!/usr/bin/env bash
# Orchestrateur des fixtures E2E — Phase 9A (téléversement média).
#
# Rôle : purger les médias éditoriaux créés par la suite Playwright
# (`admin-media.spec.ts`). La suite CRÉE les médias via l'interface admin
# elle-même (upload réel via `/admin/media/new`) — c'est précisément ce
# qu'on veut tester. Le script n'a donc qu'un rôle de nettoyage, avec
# UNE PARTICULARITÉ propre à la 9A par rapport aux scripts articles :
# chaque asset possède un fichier binaire dans le stockage privé côté
# conteneur `api`. Il faut donc supprimer LES DEUX pour ne pas laisser
# de fichier orphelin sur le volume `api_var`.
#
# Deux modes :
#   - `load`  : garantit un état de départ propre (DELETE préventif +
#                unlink des fichiers), de sorte que la suite ne trouve
#                jamais de résidu d'un run précédent qui aurait crashé
#                avant `clear` ;
#   - `clear` : symétrique post-suite.
#
# Garde-fous (identiques aux scripts articles + spécificité média) :
#   - la constante `E2E_FILENAME_PREFIX` est CODÉE EN DUR (`e2e-9a-`) ;
#     aucune substitution possible via variable d'environnement ;
#   - `DELETE ... WHERE original_filename LIKE 'e2e-9a-%'` — jamais
#     `TRUNCATE`, jamais `DELETE` sans clause de préfixe ;
#   - la suppression filesystem cible EXCLUSIVEMENT les `storage_key`
#     retournés par la requête SELECT ; aucun `rm -rf` du dossier racine ;
#   - `ON_ERROR_STOP=1` : psql échoue au premier problème ;
#   - le script échoue explicitement si api/postgres ne sont pas démarrés.
#
# Usage :
#   scripts/e2e-admin-media.sh load    # purge préventive (état propre)
#   scripts/e2e-admin-media.sh clear   # purge post-suite
#
# Variables d'environnement :
#   POSTGRES_USER — utilisateur psql côté conteneur (défaut `devzair`)
#   POSTGRES_DB   — base ciblée (défaut `devzair`)

set -euo pipefail

# Constante volontairement codée en dur — garde-fou n°1.
# Toute correspondance avec le préfixe utilisé côté Playwright
# (`admin-media.spec.ts`) est indispensable au bon nettoyage.
readonly E2E_FILENAME_PREFIX='e2e-9a-'
# Racine du stockage privé côté conteneur `api`. Alignée avec la valeur
# par défaut de `compose.yaml` (`EDITORIAL_MEDIA_STORAGE_DIR`).
readonly MEDIA_STORAGE_ROOT='/app/var/editorial-media'

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

# 1) Récupérer la liste des storage_keys concernés AVANT de les supprimer
# en base — sinon on perd la référence physique. `tr -d '[:space:]'` élimine
# les espaces/newlines résiduels de la sortie psql (mode `-A -t`).
STORAGE_KEYS_RAW="$(docker compose exec -T postgres \
  psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" \
  -A -t \
  -c "SELECT storage_key FROM editorial_media_asset WHERE original_filename LIKE '${E2E_FILENAME_PREFIX}%';" \
)"

# 2) Supprimer les fichiers physiques UN PAR UN, uniquement ceux
# retournés par la requête. Aucun `rm -rf` du dossier racine.
if [[ -n "$STORAGE_KEYS_RAW" ]]; then
  while IFS= read -r STORAGE_KEY; do
    STORAGE_KEY="${STORAGE_KEY//[$'\r\n\t ']}"
    if [[ -z "$STORAGE_KEY" ]]; then
      continue
    fi
    # Défense en profondeur : refuse toute clé contenant `..` (traversal),
    # `/` en tête (chemin absolu) ou `-` en tête (parasitage d'options).
    # Le VO PHP StorageKey garantit le format canonique `{uuid}/original.{ext}`,
    # mais le shell ne doit jamais se reposer sur cette invariance côté
    # application. NB : les octets nuls ne sont pas testables en bash (les
    # chaînes bash tronquent au premier NUL) ; ils sont de toute façon rejetés
    # côté psql avant même d'atteindre ce point.
    if [[ "$STORAGE_KEY" == *".."* \
       || "$STORAGE_KEY" == /* \
       || "$STORAGE_KEY" == -* ]]; then
      echo "ERREUR : storage_key suspect refusé (${STORAGE_KEY})." >&2
      exit 3
    fi
    # `-f` : silencieux si absent (idempotent).
    # `--` : arrête le parsing des options — protection contre un nom
    # commençant par `-` (théoriquement impossible vu le format canonique).
    docker compose exec -T api \
      rm -f -- "${MEDIA_STORAGE_ROOT}/${STORAGE_KEY}"
    # Le dossier UUID parent peut rester vide — on tente rmdir best-effort
    # (silencieux si non-vide ou déjà supprimé) pour ne pas laisser
    # de coquille sur le volume.
    STORAGE_DIR="$(dirname "${STORAGE_KEY}")"
    if [[ -n "$STORAGE_DIR" && "$STORAGE_DIR" != "." ]]; then
      docker compose exec -T api \
        sh -c "rmdir --ignore-fail-on-non-empty -- '${MEDIA_STORAGE_ROOT}/${STORAGE_DIR}' 2>/dev/null || true"
    fi
  done <<< "$STORAGE_KEYS_RAW"
fi

# 3) Supprimer les lignes en base — DELETE ciblé, jamais TRUNCATE.
docker compose exec -T postgres \
  psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" \
  -c "DELETE FROM editorial_media_asset WHERE original_filename LIKE '${E2E_FILENAME_PREFIX}%';" \
  > /dev/null

case "$ACTION" in
  load)
    echo "État de départ propre : médias E2E purgés (préfixe '${E2E_FILENAME_PREFIX}')."
    ;;
  clear)
    echo "Médias E2E purgés (préfixe '${E2E_FILENAME_PREFIX}')."
    ;;
esac
