# Feuille de route

> Ordre impératif des phases et critères de sortie.

## 21. Phases de réalisation — ordre impératif

Une phase ne doit pas être sautée. Une page ou un module ne commence que si le critère de sortie de la phase précédente est rempli.

## Phase 0 — Cadrage fonctionnel

- [ ] Confirmer les services du MVP.
- [ ] Confirmer les cibles prioritaires.
- [ ] Confirmer les zones réellement desservies.
- [ ] Inventorier les réalisations et preuves publiables.
- [ ] Confirmer les coordonnées professionnelles.
- [ ] Confirmer le domaine canonique.
- [ ] Définir les conversions attendues.
- [ ] Recenser les données personnelles collectées.
- [ ] Valider l’arborescence éditoriale.
- [ ] Marquer chaque information inconnue `À VALIDER`.

### Critère de sortie

Les pages initiales, leurs objectifs et leurs contenus nécessaires sont connus.

---

## Phase 1 — Dépôt et Docker Nuxt

### État actuel : TERMINÉE

- [x] Créer `apps/web` avec le template Nuxt `minimal`.
- [x] Utiliser npm.
- [x] Refuser le dépôt Git imbriqué.
- [x] Refuser l’installation automatique de modules.
- [x] Initialiser Git à la racine du monorepo.
- [x] Ajouter `.gitignore` racine.
- [x] Ajouter `Dockerfile.dev`.
- [x] Ajouter `compose.yaml`.
- [x] Ajouter `.env.example`.
- [x] Ajouter un `README.md`.
- [x] Ajouter le présent référentiel.
- [x] Vérifier l’accès à `http://localhost:3001` (mapping 3001→3000 conteneur).
- [x] Exécuter un premier `npm run build`.
- [ ] Créer le premier commit propre (à faire par l’humain).

### Critère de sortie

Le projet démarre uniquement avec Docker, le build passe et la base est versionnée.

---

## Phase 2 — Qualité et architecture Nuxt

### État actuel : TERMINÉE

- [x] Installer `@nuxt/eslint`.
- [x] Installer TypeScript et `vue-tsc`.
- [x] Activer le typecheck.
- [x] Installer Vitest et Nuxt Test Utils.
- [x] Préparer Playwright (smoke test unique sur `/design-preview`).
- [x] Ajouter les scripts npm (`lint`, `lint:fix`, `typecheck`, `test`, `test:e2e`, `quality`).
- [x] Créer l’arborescence `app` (server et shared seront créés Phase 8+).
- [x] Créer `app.vue`.
- [x] Créer le layout par défaut.
- [x] Créer `error.vue`.
- [x] Créer la première page (`/` placeholder + `/design-preview`).
- [x] Ajouter la configuration centralisée du site (`runtimeConfig` + `NUXT_PUBLIC_*`).
- [ ] Documenter les conventions de nommage (à faire en tête de Phase 3).
- [x] Ajouter une CI minimale (`.github/workflows/web-quality.yml`).
- [x] Exécuter lint, typecheck, tests et build.

### Critère de sortie

Les outils empêchent l’introduction de code non typé, mal structuré ou non testable.

---

## Phase 3 — Design system et accessibilité de base

- [x] Définir les tokens de couleur.
- [x] Définir typographies et espacements.
- [x] Définir conteneurs et grille.
- [x] Créer les composants de base nécessaires seulement.
- [x] Créer header, footer et navigation.
- [ ] Tester clavier et focus.
- [ ] Tester les contrastes.
- [ ] Tester mobile et zoom.
- [ ] Documenter les variantes.
- [x] Éviter les composants universels surconfigurés.

### Critère de sortie

Le layout et les composants indispensables sont accessibles, typés et réutilisables.

---

## Phase 4 — Socle SEO Nuxt

- [ ] Définir `siteUrl`.
- [ ] Configurer la langue.
- [ ] Configurer le title template.
- [ ] Créer `usePageSeo`.
- [ ] Créer l’image Open Graph par défaut.
- [ ] Ajouter les canonicals.
- [ ] Définir la politique d’indexation par environnement.
- [ ] Configurer le pré-rendu des pages marketing.
- [ ] Évaluer puis installer Nuxt SEO.
- [ ] Générer robots.txt.
- [ ] Générer sitemap.xml.
- [ ] Ajouter `Organization` et `WebSite`.
- [ ] Tester les métadonnées dans le HTML serveur.
- [ ] Tester les codes HTTP et redirections.
- [ ] Ajouter des tests SEO automatisés ciblés.

### Critère de sortie

Une page de démonstration contient toutes ses métadonnées dans le HTML initial et l’environnement de préproduction ne peut pas être indexé.

---

## Phase 5 — Contenus et pages principales

Ordre recommandé :

1. accueil ;
2. services ;
3. première page service complète ;
4. agence ;
5. méthode ;
6. contact ;
7. autres services ;
8. réalisations.

Pour chaque page :

- [ ] brief éditorial ;
- [ ] intention principale ;
- [ ] contenu validé ;
- [ ] composants nécessaires ;
- [ ] responsive ;
- [ ] accessibilité ;
- [ ] métadonnées ;
- [ ] canonical ;
- [ ] maillage ;
- [ ] données structurées si pertinentes ;
- [ ] test HTML serveur ;
- [ ] test fonctionnel.

### Critère de sortie

Les pages ne contiennent ni faux contenu, ni placeholder, ni duplication structurelle inutile.

---

## Phase 6 — Formulaire de contact

- [ ] Définir les champs minimaux.
- [ ] Créer un schéma de validation.
- [ ] Valider côté serveur.
- [ ] Ajouter CSRF si le mécanisme choisi l’exige.
- [ ] Ajouter limitation de débit.
- [ ] Ajouter protection anti-spam progressive.
- [ ] Ajouter e-mail transactionnel.
- [ ] Ajouter messages accessibles.
- [ ] Ajouter notice de confidentialité.
- [ ] Tester erreurs et succès.
- [ ] Ne pas exposer de clé d’e-mail au navigateur.
- [ ] Ajouter les événements analytics après décision de consentement.

### Critère de sortie

Une demande réelle est transmise de façon sûre, traçable et conforme.

---

## Phase 7 — Réalisations et autorité éditoriale

- [ ] Modèle d’étude de cas.
- [ ] Preuves et autorisations.
- [ ] Périodes et méthodes de mesure.
- [ ] Composants de liste et détail.
- [ ] Schema.org adapté.
- [ ] Maillage vers les services.
- [ ] Pages auteurs réels si utiles.
- [ ] Politique de mise à jour.

### Critère de sortie

Chaque preuve publiée est vérifiable et contextualisée.

---

## Phase 8 — Création de l’API Symfony

- [ ] Créer `apps/api` avec Symfony 7.4 LTS.
- [ ] Ajouter Docker PHP.
- [ ] Ajouter PostgreSQL.
- [ ] Configurer migrations.
- [ ] Définir Article, Catégorie et Auteur.
- [ ] Définir DTO publics.
- [ ] Définir validation.
- [ ] Définir statuts éditoriaux.
- [ ] Définir permissions.
- [ ] Créer endpoints publics.
- [ ] Créer administration protégée.
- [ ] Ajouter tests Symfony.
- [ ] Documenter OpenAPI si retenu.
- [ ] Définir la sanitisation du contenu.

### Critère de sortie

L’API retourne des contrats stables, validés, documentés et testés.

---

## Phase 9 — Intégration du blog dans Nuxt

- [ ] Créer les types partagés frontend.
- [ ] Créer `ArticleRepository`.
- [ ] Créer l’adaptateur Symfony.
- [ ] Créer les composables de liste et détail.
- [ ] Créer `/ressources`.
- [ ] Créer `/ressources/[slug]`.
- [ ] Récupérer les données pendant le SSR.
- [ ] Gérer 404 et erreurs.
- [ ] Ajouter métadonnées dynamiques.
- [ ] Ajouter Schema.org `Article`.
- [ ] Ajouter auteur et dates.
- [ ] Ajouter sitemap dynamique.
- [ ] Ajouter flux RSS si pertinent.
- [ ] Définir cache et invalidation.
- [ ] Tester la publication et la mise à jour.

### Critère de sortie

Un article publié dans Symfony est visible sous une URL Nuxt canonique avec HTML, métadonnées et données structurées corrects.

---

## Phase 10 — SEO local et GEO avancé

- [ ] Valider l’éligibilité locale.
- [ ] Harmoniser les informations d’entreprise.
- [ ] Configurer les profils officiels.
- [ ] Définir la politique `OAI-SearchBot`.
- [ ] Définir la politique `GPTBot`.
- [ ] Vérifier WAF et CDN.
- [ ] Ajouter sources et dates aux contenus.
- [ ] Vérifier le graphe des entités.
- [ ] Tester une liste stable de requêtes.
- [ ] Mesurer les référents IA identifiables.
- [ ] Étudier `llms.txt` sans le considérer obligatoire.

### Critère de sortie

L’identité, les contenus et la politique des robots sont cohérents et vérifiables.

---

## Phase 11 — Sécurité, performance et accessibilité

- [ ] Audit OWASP.
- [ ] Revue des secrets.
- [ ] Revue des dépendances.
- [ ] CSP.
- [ ] En-têtes.
- [ ] Permissions.
- [ ] Analyse des formulaires.
- [ ] Contrôle des contenus HTML.
- [ ] Audit WCAG 2.2 AA.
- [ ] Navigation clavier.
- [ ] Lecteur d’écran.
- [ ] Mesures LCP, INP et CLS.
- [ ] Budget JavaScript.
- [ ] Optimisation des images.
- [ ] Test de charge raisonnable.
- [ ] Corrections.

### Critère de sortie

Aucun défaut critique connu et aucune barrière majeure sur les parcours principaux.

---

## Phase 12 — Préproduction et production

- [ ] Préproduction protégée.
- [ ] Recette fonctionnelle.
- [ ] Recette éditoriale.
- [ ] Recette SEO.
- [ ] Recette sécurité.
- [ ] Recette confidentialité.
- [ ] Recette analytics.
- [ ] Sauvegarde et restauration.
- [ ] Plan de rollback.
- [ ] TLS.
- [ ] Redirections de domaine.
- [ ] Smoke tests.
- [ ] Soumission Search Console.
- [ ] Soumission Bing Webmaster Tools.
- [ ] Monitoring et alertes.
- [ ] Journal de version.

### Critère de sortie

Verdict explicite `GO`, `GO CONDITIONNEL` ou `NO-GO`.

---

## Phase 13 — Suivi continu

### Après lancement

- [ ] surveiller disponibilité et erreurs ;
- [ ] surveiller les formulaires ;
- [ ] contrôler indexation et crawl ;
- [ ] contrôler les données structurées ;
- [ ] analyser les conversions ;
- [ ] analyser les Core Web Vitals terrain ;
- [ ] mettre à jour dépendances et contenus ;
- [ ] tester les restaurations ;
- [ ] revoir les règles de robots ;
- [ ] améliorer les pages à partir de données réelles.

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
