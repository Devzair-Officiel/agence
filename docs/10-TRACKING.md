# Suivi, décisions et changements

> État vivant du projet : tâches, décisions et historique.

## 24. Registre de suivi

| ID | Phase | Tâche | Priorité | Statut | Preuve attendue | Prochaine action |
|---|---|---|---|---|---|---|
| DEV-001 | 0 | Confirmer les services du MVP | Haute | À faire | Liste validée | Cadrage éditorial |
| DEV-002 | 0 | Confirmer la zone desservie | Haute | À faire | Information écrite | Valider les pages locales |
| DEV-003 | 0 | Inventorier les réalisations publiables | Haute | À faire | Dossiers et autorisations | Préparer les études de cas |
| DEV-004 | 1 | Créer Dockerfile.dev | Haute | Terminé | `apps/web/Dockerfile.dev` (Node 24 Alpine, `npm ci` au build) + `.dockerignore` | Écrire un Dockerfile de production en Phase 12 |
| DEV-005 | 1 | Créer compose.yaml | Haute | Terminé | Site accessible sur `http://localhost:3001` (mapping 3001→3000 conteneur), `docker compose config` OK, volumes `web_node_modules` et `web_nuxt_cache` | Ajouter le service `api` (Symfony) en Phase 8 |
| DEV-006 | 1 | Initialiser Git à la racine | Haute | En cours | `.git` racine présent, `.gitignore` posé, un seul `.git` dans l’arbre | Créer le premier commit propre (humain) |
| DEV-007 | 2 | Installer ESLint et le typecheck | Haute | Terminé | `@nuxt/eslint` en devDep, `eslint.config.mjs` en Flat Config (ignore `.nuxt`/`.output`, refuse `no-explicit-any`), `npm run lint` et `npm run typecheck` verts | Étendre les règles au besoin sans surcharger |
| DEV-008 | 2 | Créer l’architecture Nuxt | Haute | Terminé | `app/{assets,components,pages,composables,layouts}` en place, `error.vue` et `app.vue` présents | Créer `server/` et `shared/` quand un besoin réel apparaît (Phase 8+) |
| DEV-009 | 4 | Créer `usePageSeo` | Haute | À faire | Test du HTML serveur | Centraliser les métadonnées |
| DEV-010 | 4 | Configurer robots, sitemap et Schema.org | Haute | À faire | URLs publiques valides | Évaluer Nuxt SEO |
| DEV-011 | 3 | Poser les tokens CSS (couleurs, typos, espacements, radius, ombres, animations, z-index) | Haute | Terminé | `apps/web/app/assets/css/tokens.css` + `global.css` + `animations.css` chargés par Nuxt | Utiliser les tokens dans les pages futures |
| DEV-012 | 3 | Créer les composants de base (BaseContainer, BaseEyebrow, BaseButton, BaseLink) | Haute | Terminé | Composants + tests Vitest (7 cas BaseButton) verts | Étendre au besoin sans surconfigurer |
| DEV-013 | 3 | Créer le header, la nav mobile accessible et le footer | Haute | Terminé | `SiteHeader`, `MobileNavigation` (Teleport, aria-modal, focus trap, Escape, scroll lock, restauration du focus), `SiteFooter`, tests Vitest (10 cas) verts | Ajouter les tests clavier/contrastes et zoom (Phase 3) |
| DEV-014 | 3 | Ajouter la page interne `/design-preview` (noindex) | Basse | Terminé | Page rendue en SSR avec `<meta name="robots" content="noindex, nofollow">` | Supprimer à la clôture du lot design system |
| DEV-015 | 2 | Câbler Vitest 3 + @vue/test-utils + happy-dom, ajouter `vue-tsc` et `typescript` en devDeps, scripts `typecheck` et `test` | Haute | Terminé | `docker compose exec web npm run test` (20 verts), `typecheck` OK, `build` OK | Ajouter ESLint et Playwright plus tard |
| DEV-016 | 1-2 | Clôturer les fondations techniques (Docker reproductible, ESLint Flat Config, Playwright prep, CI GitHub Actions, `@nuxt/fonts`, README, `.env.example`) | Haute | Terminé | `docker compose build` OK, `npm run quality` vert (lint + typecheck + tests 20/20 + build), workflow `.github/workflows/web-quality.yml` en place, smoke test Playwright écrit | Enchaîner sur la Phase 5 (hero + graphe SVG) |
| DEV-017 | 2 | Corriger la régression vue-router R0004 (accueil `/` manquant, `<a>` internes vers routes inexistantes, `@font-face` sur fichiers absents) | Haute | Terminé | Console navigateur propre sur `/design-preview`, page `/` placeholder noindex, prop `external` sur `BaseButton`/`BaseLink` pour les liens vers pages non encore livrées | Retirer `external` au fur et à mesure que les pages sont créées |

### Statuts autorisés

- `À faire`
- `En cours`
- `Bloqué`
- `En revue`
- `Terminé`
- `Abandonné`

---

## 25. Journal des décisions

| Date | ID | Décision | Raisons | Conséquences |
|---|---|---|---|---|
| 2026-08-01 | DEC-001 | Présenter Devzair comme une agence digitale à taille humaine | Refléter l’offre globale et le fonctionnement projet | Employer « nous », ne pas utiliser le positionnement freelance |
| 2026-08-01 | DEC-002 | Utiliser Nuxt 4 avec Vue et TypeScript strict | SSR, routage, métadonnées et maintenabilité | Ne pas construire une SPA Vue pure |
| 2026-08-01 | DEC-003 | Utiliser un monorepo Docker Compose | Préparer l’ajout de Symfony sans restructuration | Git unique à la racine |
| 2026-08-01 | DEC-004 | Utiliser Symfony 7.4 LTS pour l’API future | Stabilité et maintenance longue | API ajoutée dans `apps/api` |
| 2026-08-01 | DEC-005 | Appliquer SOLID de façon pragmatique | Séparer les responsabilités sans surarchitecture | Pages, composants, composables, services et repositories distincts |
| 2026-08-01 | DEC-006 | Centraliser les métadonnées communes | Éviter les incohérences et répétitions | Création de `usePageSeo` |
| 2026-08-01 | DEC-007 | Pré-rendre les pages marketing et garder le SSR pour le blog initial | Performance et fraîcheur | Cache dynamique décidé plus tard |
| 2026-08-01 | DEC-008 | Ne pas installer de modules pendant le scaffolding | Base maîtrisée | Modules ajoutés avec contrôle et tests |
| 2026-08-01 | DEC-009 | Utiliser des imports explicites (Vue, composables, composants layout) plutôt que les auto-imports Nuxt dans les composants testés | Vitest n'exécute pas la transformation d'auto-import : dépendre de celle-ci masque les erreurs et rend la suite fragile | Chaque composant sous test importe explicitement ses dépendances, `~/composables` et `~/components/layout` |
| 2026-08-01 | DEC-010 | Refs de module (pas `useState`) pour `useMobileNavigation` | Le menu mobile est un singleton par onglet ; l'hydratation SSR n'a pas besoin d'être partagée par clé, et `useState` couple le composable au runtime Nuxt (donc à Vitest) | Composable pur, testable sans mocks Nuxt |
| 2026-08-01 | DEC-011 | Épingler `typescript` à `~5.9.0` | `vue-tsc` 3.3 ne supporte pas encore les changements d'exports internes de TypeScript 7 (`./lib/tsc`) | À revoir quand `vue-tsc` publie une version compatible TS 7 |
| 2026-08-01 | DEC-012 | Ne pas publier de coordonnées avant validation | Règle 1 (rien inventer) ; `site.contact.{email,phone,city}` = `null` | Le footer ne rend rien pour les coordonnées absentes |
| 2026-08-02 | DEC-013 | Installer les dépendances **au build Docker** (`npm ci` dans `Dockerfile.dev`) et exposer `node_modules` via un volume nommé | Éviter un `npm install` à chaque `docker compose up` (lent, sensible aux différences d’UID hôte, source de désync `package-lock`) | Rebuild explicite requis quand `package-lock.json` change |
| 2026-08-02 | DEC-014 | Rester en `USER root` dans le Dockerfile de dev | Compatibilité avec le bind mount `./apps/web:/app` (UID hôte différent selon la machine) | Le Dockerfile de production, à écrire en Phase 12, utilisera `USER node` |
| 2026-08-02 | DEC-015 | Utiliser Flat Config pour ESLint via `@nuxt/eslint` | Format supporté officiellement par Nuxt 4 et ESLint 10 ; permet d’ajouter nos règles sans casser la base auto-générée | Toute règle projet vit dans `apps/web/eslint.config.mjs` (surcouche de `./.nuxt/eslint.config.mjs`) |
| 2026-08-02 | DEC-016 | Choisir `@nuxt/fonts` pour l’auto-hébergement des polices | Détecte les `font-family` du CSS, télécharge au build, sert depuis notre origine, injecte un fallback métrique (size-adjust) pour limiter le CLS. Aucun CDN tiers en runtime | Les fichiers woff2 manuels de `/public/fonts/` deviennent inutiles ; on ne référence plus de fichier absent |
| 2026-08-02 | DEC-017 | Prop `external` sur `BaseButton` et `BaseLink` pour les liens vers pages non encore livrées | `NuxtLink` interne appelle `router.resolve` et émet `[VUE_ROUTER_R0004] No match found` pour toute route inexistante ; `external` force un `<a href>` neutre | Retirer la prop à mesure que les pages cibles sont créées (Phase 5) |
| 2026-08-02 | DEC-018 | CI GitHub Actions déclenchée uniquement sur changements de `apps/web/**` ou du workflow lui-même | Éviter les runs superflus pour de la doc pure ; garder le feedback rapide (~5-10 min) | Ajouter un workflow séparé pour Symfony en Phase 8 |

---

## 26. Journal des changements

| Date | Version | Changement |
|---|---|---|
| 2026-08-01 | 1.0 | Création du référentiel initial |
| 2026-08-01 | 2.0 | Architecture Nuxt/Docker/Symfony confirmée, phases réorganisées, SOLID/DRY et implémentation SEO/GEO Nuxt ajoutés |
| 2026-08-01 | 2.1 | Lot design system posé (Phase 3 partielle) : tokens CSS, composants base, layout (header/mobile nav/footer), composable `useMobileNavigation`, page interne `/design-preview` noindex, Vitest opérationnel (20 tests verts), typecheck et build verts |
| 2026-08-02 | 2.2 | Clôture des fondations techniques (Phases 1 et 2) : `.gitignore` racine, `Dockerfile.dev` reproductible (npm ci au build, plus de `npm install` à chaque boot), `compose.yaml` refactorisé, `.env.example` sans secret, ESLint Flat Config via `@nuxt/eslint`, Playwright installé avec smoke test `/design-preview`, `@nuxt/fonts` en charge des polices, workflow CI `web-quality.yml`, READMEs racine et `apps/web` mis à jour. `npm run quality` vert (lint, typecheck, tests 20/20, build) |

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
