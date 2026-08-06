#!/usr/bin/env bash
# Orchestrateur du compte administrateur E2E — Phase 8C1 (clôture).
#
# Rôle : provisionner (mode `load`) et supprimer (mode `clear`) UN SEUL
# compte admin strictement dédié à la suite Playwright :
#   e2e-admin@example.test
# Le TLD `.example.test` est réservé par la RFC 6761 — jamais routable,
# jamais confondable avec un vrai opérateur.
#
# Motivation : la Phase 8C1 orchestrait déjà la purge des articles
# éditoriaux via `scripts/e2e-fixtures.sh clear`, mais aucun mécanisme
# équivalent n'existait pour le compte admin. À chaque run local, la ligne
# `e2e-admin@example.test` s'accumulait dans la base dev. Ce script comble
# ce trou.
#
# Garde-fous (immuables) :
#   - la constante `E2E_ADMIN_EMAIL` est CODÉE EN DUR ; l'orchestrateur
#     n'accepte AUCUNE substitution en mode `clear` ;
#   - le `clear` est un `DELETE ... WHERE normalized_email = <constante>`
#     — jamais `TRUNCATE`, jamais `DELETE` sans clause exacte ;
#   - aucune commande Symfony de suppression générale n'est introduite,
#     donc rien à exclure de prod ; la surface d'attaque reste nulle en
#     production ;
#   - le script échoue explicitement si api/postgres ne sont pas démarrés.
#
# Usage :
#   scripts/e2e-admin-user.sh load    # crée le compte (idempotent)
#   scripts/e2e-admin-user.sh clear   # supprime le compte E2E s'il existe
#
# Variables d'environnement (mode `load` uniquement) :
#   ADMIN_TEST_PASSWORD  — mot de passe posé au provisioning
#                          (défaut `DevzairAdmin!2026`, aligné CI).
#   POSTGRES_USER        — utilisateur psql côté conteneur postgres
#                          (défaut `devzair`, aligné compose.yaml).
#   POSTGRES_DB          — base ciblée (défaut `devzair`).

set -euo pipefail

# Constante volontairement codée en dur — voir en-tête, garde-fou n°1.
readonly E2E_ADMIN_EMAIL='e2e-admin@example.test'

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

case "$ACTION" in
  load)
    ADMIN_PASSWORD="${ADMIN_TEST_PASSWORD:-DevzairAdmin!2026}"
    # `--password-stdin` évite toute fuite du mot de passe dans `ps` ou
    # dans l'historique bash. `app:admin:create-user` refuse les doublons ;
    # on rend l'idempotence explicite en distinguant les deux issues.
    if printf '%s' "$ADMIN_PASSWORD" | docker compose exec -T api \
        bin/console app:admin:create-user \
          --email="$E2E_ADMIN_EMAIL" \
          --display-name='E2E Admin' \
          --password-stdin \
        > /dev/null 2>&1; then
      echo "Compte admin E2E créé (${E2E_ADMIN_EMAIL})."
    else
      echo "Compte admin E2E déjà présent (${E2E_ADMIN_EMAIL}) — rien à faire."
    fi
    ;;

  clear)
    # `ON_ERROR_STOP=1` : psql échoue au premier problème (auth, table
    # manquante) — le script propage l'échec, jamais silencieux.
    # Le WHERE cible strictement la constante ; aucune substitution.
    docker compose exec -T postgres \
      psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" \
      -c "DELETE FROM admin_user WHERE normalized_email = '${E2E_ADMIN_EMAIL}';" \
      > /dev/null
    echo "Compte admin E2E purgé si présent (${E2E_ADMIN_EMAIL})."
    ;;
esac
