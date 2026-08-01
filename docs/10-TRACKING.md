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

---

## 26. Journal des changements

| Date | Version | Changement |
|---|---|---|
| 2026-08-01 | 1.0 | Création du référentiel initial |
| 2026-08-01 | 2.0 | Architecture Nuxt/Docker/Symfony confirmée, phases réorganisées, SOLID/DRY et implémentation SEO/GEO Nuxt ajoutés |

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
