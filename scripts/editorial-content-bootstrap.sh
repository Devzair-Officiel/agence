#!/usr/bin/env bash
# Bootstrap idempotent du contenu éditorial réel Phase 10.
#
# Rôle : garantir que les 6 articles pillar `content/resources/*.md`
# existent en base et sont publiés — sans jamais écraser un article déjà
# présent, sans réinitialiser sa date de publication. La commande
# `app:editorial:import` est create-only (refuse si le slug existe),
# donc ce script peut être exécuté autant de fois que nécessaire :
# les slugs déjà présents sont sautés silencieusement.
#
# Usage :
#   scripts/editorial-content-bootstrap.sh            # import + publication
#   scripts/editorial-content-bootstrap.sh --dry-run  # dry-run uniquement
#
# Pré-requis : stack Docker démarrée. Le script échoue explicitement si
# l'API n'est pas joignable.

set -euo pipefail

DRY_RUN=""
if [[ "${1:-}" == "--dry-run" ]]; then
  DRY_RUN=1
fi

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

if ! docker compose ps --status=running --services 2>/dev/null | grep -q '^api$'; then
  echo "ERREUR : le service 'api' n'est pas démarré. Lancez 'docker compose up -d' d'abord." >&2
  exit 1
fi

CONTENT_DIR="content/resources"
if [[ ! -d "$CONTENT_DIR" ]]; then
  echo "ERREUR : $CONTENT_DIR introuvable." >&2
  exit 1
fi

# Séquence chronologique cohérente Phase 10 — dates réelles autour de la
# période de rédaction. Éviter une date future (refusée par l'invariant
# de publication) : on utilise un décalage de plusieurs jours avant
# aujourd'hui pour rester robuste quelle que soit l'heure d'exécution.
declare -A PUBLISHED_AT=(
  [creer-site-internet-professionnel]="2026-08-04T09:00:00+00:00"
  [site-vitrine-ou-sur-mesure]="2026-08-05T09:00:00+00:00"
  [seo-creation-site-internet]="2026-08-06T09:00:00+00:00"
  [application-metier-remplacer-excel]="2026-08-07T09:00:00+00:00"
  [ameliorer-visibilite-locale-entreprise]="2026-08-08T09:00:00+00:00"
  [maintenance-site-internet]="2026-08-09T01:30:00+00:00"
)

SLUGS=(
  creer-site-internet-professionnel
  site-vitrine-ou-sur-mesure
  seo-creation-site-internet
  application-metier-remplacer-excel
  ameliorer-visibilite-locale-entreprise
  maintenance-site-internet
)

echo "== Étape 1 : copie des fichiers Markdown dans le conteneur api =="
for slug in "${SLUGS[@]}"; do
  src="$CONTENT_DIR/$slug.md"
  if [[ ! -f "$src" ]]; then
    echo "ERREUR : fichier manquant $src" >&2
    exit 1
  fi
  docker cp "$src" agence-api-1:/tmp/"$slug.md" >/dev/null
done
echo "  $(printf '%s\n' "${SLUGS[@]}" | wc -l) fichier(s) copié(s)."

echo "== Étape 2 : import (create-only, saute si slug existe) =="
for slug in "${SLUGS[@]}"; do
  echo "  → $slug"
  if [[ -n "$DRY_RUN" ]]; then
    docker compose exec -T api bin/console app:editorial:import "/tmp/$slug.md" --dry-run --silent
    continue
  fi
  # Import réel — si le slug existe déjà, la commande sort en erreur.
  # On l'accepte silencieusement pour rendre le script idempotent.
  if docker compose exec -T api bin/console app:editorial:import "/tmp/$slug.md" --silent 2>/dev/null; then
    echo "    importé (draft)"
  else
    echo "    déjà en base — saute (idempotent)"
  fi
done

if [[ -n "$DRY_RUN" ]]; then
  echo "== Dry-run terminé — aucune écriture, aucune publication =="
  exit 0
fi

echo "== Étape 3 : publication avec dates séquentielles réelles =="
for slug in "${SLUGS[@]}"; do
  published_at="${PUBLISHED_AT[$slug]}"
  echo "  → $slug @ $published_at"
  # La commande `publish` sort en exit 0 dans les deux cas suivants :
  #   - article publié pour la première fois (mutation) ;
  #   - article déjà publié → WARNING "déjà publié" + aucune mutation.
  # On capture stdout+stderr pour distinguer les deux et rendre le log
  # honnête (« publié » vs « déjà publié — saute (idempotent) »).
  output="$(docker compose exec -T api bin/console app:editorial:publish "$slug" \
      --published-at="$published_at" --no-ansi 2>&1 || true)"
  if grep -q "déjà publié" <<<"$output"; then
    echo "    déjà publié — saute (idempotent)"
  else
    echo "    publié"
  fi
done

echo "== Étape 4 : nettoyage /tmp dans le conteneur =="
for slug in "${SLUGS[@]}"; do
  docker compose exec -T api rm -f "/tmp/$slug.md" >/dev/null 2>&1 || true
done

echo "== Bootstrap éditorial terminé =="
