#!/usr/bin/env bash
input=$(cat)

if echo "$input" | grep -Eq '"stop_hook_active"[[:space:]]*:[[:space:]]*true'; then exit 0; fi

cd "$CLAUDE_PROJECT_DIR" || exit 0

if git diff --quiet HEAD -- apps/web && [ -z "$(git ls-files --others --exclude-standard apps/web)" ]; then
  exit 0
fi

# Si le conteneur ne tourne pas, on ne bloque pas dessus
docker compose ps --status running --services 2>/dev/null | grep -q '^web$' || exit 0

out=$( { docker compose exec -T web npm run lint \
      && docker compose exec -T web npm run typecheck \
      && docker compose exec -T web npm run test; } 2>&1 ) || {
  echo "Contrôles qualité en échec. Corrige avant de conclure :" >&2
  echo "$out" | tail -40 >&2
  exit 2
}