#!/usr/bin/env bash
# Orchestrateur des fixtures E2E — Phase 9B (image principale d'article).
#
# Rôle : purger les articles ET les médias créés par la suite Playwright
# `hero-image-lifecycle.spec.ts`. La suite CRÉE ces fixtures via l'interface
# admin elle-même (upload réel via `/admin/media/new`, formulaire d'article
# via `/admin/articles/new`) — c'est précisément ce qu'on veut tester.
#
# Ce script combine les deux natures de nettoyage vues séparément en 8C3
# (`e2e-admin-articles.sh`, DB seule) et en 9A (`e2e-admin-media.sh`, DB
# + fichiers physiques). L'ordre est LOGIQUE (bien que non contraint par
# une FK — la 9B a délibérément retiré la FK inter-contextes pour préserver
# l'isolation entre bounded contexts Editorial et EditorialMedia) :
#   1. On supprime d'abord les articles préfixés `e2e-9b-` — cela libère
#      la référence `hero_media_id` sans impact FK, puis rend visible tout
#      article qui aurait été omis (aucun résidu de test doit subsister).
#   2. On collecte les `storage_key` des médias préfixés `e2e-9b-` AVANT
#      de les supprimer en base — indispensable pour retrouver les fichiers
#      physiques sur le volume `api_var`.
#   3. On supprime les fichiers physiques UN PAR UN, avec les mêmes garde-
#      fous que la 9A (rejet des traversals, rmdir best-effort).
#   4. On supprime les lignes `editorial_media_asset`.
#
# Deux modes :
#   - `load`  : garantit un état de départ propre (purge préventive),
#                de sorte que la suite ne trouve jamais de résidu d'un
#                run précédent qui aurait crashé avant `clear` ;
#   - `clear` : symétrique post-suite.
#
# Garde-fous :
#   - Les préfixes `E2E_SLUG_PREFIX` (articles) et `E2E_FILENAME_PREFIX`
#     (médias) sont CODÉS EN DUR — aucune substitution possible via variable
#     d'environnement ;
#   - `DELETE ... WHERE slug LIKE 'e2e-9b-%'` et
#     `DELETE ... WHERE original_filename LIKE 'e2e-9b-%'` — jamais TRUNCATE,
#     jamais DELETE sans clause de préfixe ;
#   - La suppression filesystem cible EXCLUSIVEMENT les `storage_key`
#     retournés par la requête SELECT ; aucun `rm -rf` du dossier racine ;
#   - `ON_ERROR_STOP=1` : psql échoue au premier problème ;
#   - Le script échoue explicitement si api/postgres ne sont pas démarrés.
#
# Usage :
#   scripts/e2e-9b-hero-image.sh load    # purge préventive (état propre)
#   scripts/e2e-9b-hero-image.sh clear   # purge post-suite
#
# Variables d'environnement :
#   POSTGRES_USER — utilisateur psql côté conteneur (défaut `devzair`)
#   POSTGRES_DB   — base ciblée (défaut `devzair`)

set -euo pipefail

# Constantes volontairement codées en dur — garde-fous n°1.
# Toute correspondance avec les préfixes utilisés côté Playwright
# (`hero-image-lifecycle.spec.ts`) est indispensable au bon nettoyage.
readonly E2E_SLUG_PREFIX='e2e-9b-'
readonly E2E_FILENAME_PREFIX='e2e-9b-'
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

# --- 1) DELETE articles préfixés (libère toute référence hero_media_id) ----
docker compose exec -T postgres \
  psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" \
  -c "DELETE FROM editorial_article WHERE slug LIKE '${E2E_SLUG_PREFIX}%';" \
  > /dev/null

# --- 2) Collecte des storage_keys AVANT DELETE médias ---------------------
STORAGE_KEYS_RAW="$(docker compose exec -T postgres \
  psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" \
  -A -t \
  -c "SELECT storage_key FROM editorial_media_asset WHERE original_filename LIKE '${E2E_FILENAME_PREFIX}%';" \
)"

# --- 3) Suppression physique des fichiers (idempotente, garde-fous stricts) --
if [[ -n "$STORAGE_KEYS_RAW" ]]; then
  while IFS= read -r STORAGE_KEY; do
    STORAGE_KEY="${STORAGE_KEY//[$'\r\n\t ']}"
    if [[ -z "$STORAGE_KEY" ]]; then
      continue
    fi
    # Défense en profondeur : refuse traversal, chemin absolu, préfixe d'option.
    # Le VO PHP StorageKey garantit `{uuid}/original.{ext}`, mais le shell
    # ne doit jamais se reposer sur cette invariance côté application.
    if [[ "$STORAGE_KEY" == *".."* \
       || "$STORAGE_KEY" == /* \
       || "$STORAGE_KEY" == -* ]]; then
      echo "ERREUR : storage_key suspect refusé (${STORAGE_KEY})." >&2
      exit 3
    fi
    docker compose exec -T api \
      rm -f -- "${MEDIA_STORAGE_ROOT}/${STORAGE_KEY}"
    STORAGE_DIR="$(dirname "${STORAGE_KEY}")"
    if [[ -n "$STORAGE_DIR" && "$STORAGE_DIR" != "." ]]; then
      docker compose exec -T api \
        sh -c "rmdir --ignore-fail-on-non-empty -- '${MEDIA_STORAGE_ROOT}/${STORAGE_DIR}' 2>/dev/null || true"
    fi
  done <<< "$STORAGE_KEYS_RAW"
fi

# --- 4) DELETE lignes editorial_media_asset préfixées ----------------------
docker compose exec -T postgres \
  psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" \
  -c "DELETE FROM editorial_media_asset WHERE original_filename LIKE '${E2E_FILENAME_PREFIX}%';" \
  > /dev/null

case "$ACTION" in
  load)
    echo "État de départ propre : articles + médias E2E 9B purgés (préfixe '${E2E_SLUG_PREFIX}')."
    ;;
  clear)
    echo "Articles + médias E2E 9B purgés (préfixe '${E2E_SLUG_PREFIX}')."
    ;;
esac
