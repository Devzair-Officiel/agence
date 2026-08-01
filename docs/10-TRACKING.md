# Suivi, décisions et changements

> État vivant du projet : tâches, décisions et historique.

## 24. Registre de suivi

| ID | Phase | Tâche | Priorité | Statut | Preuve attendue | Prochaine action |
|---|---|---|---|---|---|---|
| DEV-001 | 0 | Confirmer les services du MVP | Haute | À faire | Liste validée | Cadrage éditorial |
| DEV-002 | 0 | Confirmer la zone desservie | Haute | À faire | Information écrite | Valider les pages locales |
| DEV-003 | 0 | Inventorier les réalisations publiables | Haute | À faire | Dossiers et autorisations | Préparer les études de cas |
| DEV-004 | 1 | Créer Dockerfile.dev | Haute | À faire | Build Docker réussi | Écrire le fichier |
| DEV-005 | 1 | Créer compose.yaml | Haute | À faire | Site accessible sur le port 3000 | Configurer le service web |
| DEV-006 | 1 | Initialiser Git à la racine | Haute | À faire | Premier commit | Vérifier l’absence de dépôt imbriqué |
| DEV-007 | 2 | Installer ESLint et le typecheck | Haute | À faire | Commandes vertes | Ajouter scripts npm |
| DEV-008 | 2 | Créer l’architecture Nuxt | Haute | À faire | Arborescence validée | Créer layout et première page |
| DEV-009 | 4 | Créer `usePageSeo` | Haute | À faire | Test du HTML serveur | Centraliser les métadonnées |
| DEV-010 | 4 | Configurer robots, sitemap et Schema.org | Haute | À faire | URLs publiques valides | Évaluer Nuxt SEO |
| DEV-011 | 3 | Poser les tokens CSS (couleurs, typos, espacements, radius, ombres, animations, z-index) | Haute | Terminé | `apps/web/app/assets/css/tokens.css` + `global.css` + `animations.css` chargés par Nuxt | Utiliser les tokens dans les pages futures |
| DEV-012 | 3 | Créer les composants de base (BaseContainer, BaseEyebrow, BaseButton, BaseLink) | Haute | Terminé | Composants + tests Vitest (7 cas BaseButton) verts | Étendre au besoin sans surconfigurer |
| DEV-013 | 3 | Créer le header, la nav mobile accessible et le footer | Haute | Terminé | `SiteHeader`, `MobileNavigation` (Teleport, aria-modal, focus trap, Escape, scroll lock, restauration du focus), `SiteFooter`, tests Vitest (10 cas) verts | Ajouter les tests clavier/contrastes et zoom (Phase 3) |
| DEV-014 | 3 | Ajouter la page interne `/design-preview` (noindex) | Basse | Terminé | Page rendue en SSR avec `<meta name="robots" content="noindex, nofollow">` | Supprimer à la clôture du lot design system |
| DEV-015 | 2 | Câbler Vitest 3 + @vue/test-utils + happy-dom, ajouter `vue-tsc` et `typescript` en devDeps, scripts `typecheck` et `test` | Haute | Terminé | `docker compose exec web npm run test` (20 verts), `typecheck` OK, `build` OK | Ajouter ESLint et Playwright plus tard |

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

---

## 26. Journal des changements

| Date | Version | Changement |
|---|---|---|
| 2026-08-01 | 1.0 | Création du référentiel initial |
| 2026-08-01 | 2.0 | Architecture Nuxt/Docker/Symfony confirmée, phases réorganisées, SOLID/DRY et implémentation SEO/GEO Nuxt ajoutés |
| 2026-08-01 | 2.1 | Lot design system posé (Phase 3 partielle) : tokens CSS, composants base, layout (header/mobile nav/footer), composable `useMobileNavigation`, page interne `/design-preview` noindex, Vitest opérationnel (20 tests verts), typecheck et build verts |

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
