# Documentation du projet Devzair

Cette documentation est volontairement modulaire. Un agent ne doit pas tout lire à chaque tâche.

## Fichiers

| Fichier | Contenu | Quand le lire |
|---|---|---|
| `00-PROJECT.md` | Positionnement, objectifs, périmètre, URLs et navigation | Cadrage, pages et navigation |
| `01-CONTENT.md` | Contenu page par page et modèles éditoriaux | Rédaction, CMS et composants éditoriaux |
| `02-DESIGN-ACCESSIBILITY.md` | Design system, responsive et WCAG | UI, CSS, composants et parcours |
| `03-SEO-NUXT.md` | SEO technique et implémentation Nuxt | Meta, canonical, SSR, sitemap, robots |
| `04-SEO-CONTENT-GEO.md` | SEO éditorial, local et GEO | Contenu, autorité, IA et visibilité locale |
| `05-SECURITY-PRIVACY.md` | Sécurité, secrets, formulaires et conformité | API, formulaire, authentification, données |
| `06-ARCHITECTURE-CODE.md` | Nuxt, Docker, Symfony, SOLID, DRY et KISS | Architecture et développement |
| `07-QUALITY-DELIVERY.md` | Analytics, tests, navigateurs, CI/CD | Tests, mesure et livraison |
| `08-ROADMAP.md` | Phases et critères de sortie | Avant toute nouvelle étape |
| `09-WORKFLOW.md` | Définition de terminé et méthode de travail | Revue et clôture de tâche |
| `10-TRACKING.md` | Registre, décisions et changements | Avant et après une tâche |
| `11-SOURCES.md` | Sources officielles | Vérification d’une règle externe |
| `adr/README.md` | Modèle des décisions d’architecture | Toute décision structurante |

## Principe de chargement

1. Lire `AGENTS.md`.
2. Identifier le domaine de la tâche.
3. Lire deux ou trois documents pertinents au maximum.
4. Ouvrir un autre document uniquement lorsqu’une dépendance réelle apparaît.
5. Ne jamais demander à l’agent de « lire toute la documentation » par défaut.

## Source de vérité

- Les règles stables sont réparties dans les documents spécialisés.
- L’état d’avancement appartient à `08-ROADMAP.md` et `10-TRACKING.md`.
- Les décisions techniques durables appartiennent aux ADR.
- Le code, les tests et la configuration restent la source de vérité pour les détails que l’agent peut inspecter directement.
- L’ancien fichier monolithique est conservé uniquement dans `archive/` et ne doit pas être chargé par défaut.
