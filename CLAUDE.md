@AGENTS.md

# Instructions propres à Claude Code

- `AGENTS.md` est la source commune avec Codex.
- Ne charge pas tous les documents de `docs/` au démarrage : ouvre uniquement ceux indiqués par la matrice de `AGENTS.md`.
- Utilise `/context` pour vérifier les fichiers d’instructions réellement chargés.
- Les informations personnelles locales doivent aller dans `CLAUDE.local.md`, qui doit rester ignoré par Git.
- Les règles très spécifiques à un futur sous-répertoire peuvent être déplacées dans un `CLAUDE.md` imbriqué ou une règle `.claude/rules/` à portée de chemin, sans recopier le référentiel global.
