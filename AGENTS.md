# AGENTS.md — Instructions du projet Devzair

## Mission

Développer le site de l’agence digitale Devzair étape par étape, avec une base maintenable, sécurisée, accessible et optimisée pour le SEO/GEO.

## Stack confirmée

- Monorepo Git unique.
- Frontend : Nuxt 4, Vue 3 et TypeScript strict.
- Environnement : Docker Compose.
- Rendu : SSR par défaut, pré-rendu sélectif des pages marketing.
- Backend futur : Symfony 7.4 LTS.
- Base future : PostgreSQL.
- Blog : données Symfony rendues par Nuxt sous `/ressources/**`.
- Domaine public unique ; API sous `/api/**`.

## Règles non négociables

1. Ne jamais inventer de client, chiffre, témoignage, adresse, membre d’équipe, certification ou résultat.
2. Utiliser « nous » et présenter Devzair comme une agence digitale à taille humaine.
3. Ne pas désactiver le SSR des pages publiques stratégiques.
4. Conserver TypeScript strict ; ne pas introduire `any` pour contourner un problème.
5. Appliquer SOLID de façon pragmatique, ainsi que DRY et KISS.
6. Les pages orchestrent ; les composants présentent ; les composables adaptent Vue/Nuxt ; les services portent les cas d’usage ; les repositories accèdent aux données.
7. Un composant visuel ne doit pas appeler directement une API distante.
8. Ne pas créer d’abstraction sans besoin réel et stable.
9. Ne jamais placer de secret dans Git ou dans `runtimeConfig.public`.
10. Toute modification doit inclure les tests et contrôles concernés.
11. Ne jamais publier de placeholder, de contenu fictif ou de page locale clonée.
12. Ne pas modifier un domaine sans rapport avec la tâche.

## Chargement documentaire sélectif

Ne lis pas automatiquement tous les fichiers de `docs/`.

Lis d’abord `docs/README.md`, puis uniquement les documents indiqués pour la tâche :

| Tâche | Documents à lire |
|---|---|
| Positionnement, pages, navigation | `docs/00-PROJECT.md`, `docs/01-CONTENT.md` |
| UI, composants, responsive | `docs/02-DESIGN-ACCESSIBILITY.md`, `docs/06-ARCHITECTURE-CODE.md` |
| Métadonnées, sitemap, robots, SSR | `docs/03-SEO-NUXT.md` |
| Contenu SEO, local, GEO, IA | `docs/04-SEO-CONTENT-GEO.md` |
| Sécurité, formulaires, données | `docs/05-SECURITY-PRIVACY.md` |
| Docker, Nuxt, Symfony, SOLID | `docs/06-ARCHITECTURE-CODE.md` |
| Analytics, tests, CI/CD | `docs/07-QUALITY-DELIVERY.md` |
| Étape actuelle et ordre du projet | `docs/08-ROADMAP.md` |
| Définition de terminé et méthode | `docs/09-WORKFLOW.md` |
| Statuts et décisions | `docs/10-TRACKING.md` |

## Méthode obligatoire

Avant de coder :

1. lire les documents pertinents ;
2. inspecter l’existant ;
3. identifier la phase active dans `docs/08-ROADMAP.md` ;
4. vérifier les décisions dans `docs/10-TRACKING.md` ;
5. annoncer un plan court, les fichiers visés et les risques.

Pendant la modification :

- rester dans le périmètre demandé ;
- suivre les conventions existantes ;
- privilégier les fonctions pures et les petits contrats ;
- ajouter ou adapter les tests ;
- préserver SEO, accessibilité, sécurité et performance.

Avant de conclure :

1. exécuter les commandes disponibles : lint, typecheck, tests et build ;
2. inspecter le HTML serveur pour toute modification SEO ;
3. signaler honnêtement les contrôles non exécutés ;
4. mettre à jour la roadmap et le registre seulement si l’état a réellement changé ;
5. résumer les fichiers modifiés et les résultats.

## État actuel

La phase active est **Phase 1 — Dépôt et Docker Nuxt**.

Le projet Nuxt minimal existe déjà dans `apps/web`. npm a été choisi. Aucun dépôt Git imbriqué ni module Nuxt n’a été ajouté pendant le scaffolding.

La prochaine tâche est de créer le socle Docker reproductible, vérifier le démarrage et le build, puis effectuer le premier commit racine.
