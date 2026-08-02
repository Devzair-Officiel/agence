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
- [x] Préparer Playwright (smoke test unique sur la page interne d'alors, retirée en 5D).
- [x] Ajouter les scripts npm (`lint`, `lint:fix`, `typecheck`, `test`, `test:e2e`, `quality`).
- [x] Créer l’arborescence `app` (server et shared seront créés Phase 8+).
- [x] Créer `app.vue`.
- [x] Créer le layout par défaut.
- [x] Créer `error.vue`.
- [x] Créer la première page (`/` placeholder + page interne temporaire, retirée en 5D).
- [x] Ajouter la configuration centralisée du site (`runtimeConfig` + `NUXT_PUBLIC_*`).
- [x] Documenter les conventions de nommage (fait en clôture Phase 3, cf. `06-ARCHITECTURE-CODE.md` §16.9).
- [x] Ajouter une CI minimale (`.github/workflows/web-quality.yml`).
- [x] Exécuter lint, typecheck, tests et build.

### Critère de sortie

Les outils empêchent l’introduction de code non typé, mal structuré ou non testable.

---

## Phase 3 — Design system et accessibilité de base

### État actuel : TERMINÉE

- [x] Définir les tokens de couleur.
- [x] Définir typographies et espacements.
- [x] Définir conteneurs et grille.
- [x] Créer les composants de base nécessaires seulement.
- [x] Créer header, footer et navigation.
- [x] Tester clavier et focus (E2E `keyboard-navigation.spec.ts` + `mobile-navigation.spec.ts`).
- [x] Tester les contrastes (Axe `serious`/`critical` bloquants ; suite déplacée sur `/` à la clôture Phase 5D).
- [x] Tester mobile et zoom (E2E responsive 390 / 768 / 1440 ; contrôles manuels 320 / zoom 200 % documentés).
- [x] Documenter les variantes (`docs/02-DESIGN-ACCESSIBILITY.md` + prop `variant`/`tone`).
- [x] Éviter les composants universels surconfigurés.

### Critère de sortie

Le layout et les composants indispensables sont accessibles, typés et réutilisables.

---

## Phase 4 — Socle SEO Nuxt

### État actuel : TERMINÉE

- [x] Définir `siteUrl` (`NUXT_PUBLIC_SITE_URL`, source unique de vérité, jamais dérivé du header Host).
- [x] Configurer la langue (`site.language` = `fr`, `<html lang="fr">`).
- [x] Configurer le title template (`%s | Devzair`).
- [x] Créer `usePageSeo` (`app/composables/usePageSeo.ts`).
- [x] Créer l’image Open Graph par défaut (emplacement `apps/web/public/og/` créé, image reportée — aucune image fictive publiée).
- [x] Ajouter les canonicals (absolus, sans query/fragment, jamais sur une page noindex).
- [x] Définir la politique d’indexation par environnement (`NUXT_PUBLIC_SITE_INDEXABLE`, défaut `false`).
- [ ] Configurer le pré-rendu des pages marketing (reporté en Phase 5 — accueil actuelle est un placeholder).
- [x] Évaluer puis installer Nuxt SEO (Option 2 « à la carte » retenue, cf. ADR-004).
- [x] Générer robots.txt (`@nuxtjs/robots` v5.7, règles OAI-SearchBot / GPTBot).
- [x] Générer sitemap.xml (`@nuxtjs/sitemap` v7.6, une seule route `/` depuis la clôture Phase 5D).
- [x] Ajouter `Organization` et `WebSite` (`useSiteSchema` dans le layout par défaut, aucune donnée fictive).
- [x] Tester les métadonnées dans le HTML serveur (`test/e2e/seo.spec.ts`, 10 cas).
- [x] Tester les codes HTTP et redirections (`/robots.txt`, `/sitemap.xml`, `X-Robots-Tag`).
- [x] Ajouter des tests SEO automatisés ciblés (unitaires : `site-url.spec.ts`, `canonical.spec.ts`, `usePageSeo.spec.ts` — E2E : `seo.spec.ts`).

### Critère de sortie

Une page de démonstration contient toutes ses métadonnées dans le HTML initial et l’environnement de préproduction ne peut pas être indexé.

**Statut : atteint.** Le HTML SSR de `/` expose `<title>`, `description`, `robots`, `og:*`, `twitter:*` et le JSON-LD Organization + WebSite avant hydratation. Avec les défauts (`NUXT_PUBLIC_SITE_INDEXABLE=false`) : `X-Robots-Tag: noindex, nofollow`, `robots.txt` bloque tous les user-agents, aucune page ne peut apparaître dans un index. La bascule vers un environnement indexable ne dépend que d'une seule variable.

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

### Phase 5A — Accueil : hero et graphe des cinq pôles

État actuel : TERMINÉE.

Voir aussi Phase 5B ci-dessous pour les trois sections suivantes.

Périmètre volontairement restreint au premier écran de l'accueil. Les
sections suivantes (« Le constat », « Réponse globale », « Cinq pôles
détaillés », etc.) sont traitées en Phase 5B.

- [x] Configuration typée des cinq pôles d'expertise (`app/config/expertise-pillars.ts`).
- [x] Composant `HomeHero.vue` (eyebrow, H1, introduction, deux CTA vers `#contact` et `#realisations`, réassurance).
- [x] Composant `HomeEcosystemGraph.vue` — SVG accessible (`role="img"`, `<title>`, `<desc>`, `aria-labelledby` via `useId()`, sous-groupes `aria-hidden`).
- [x] Animation signature CSS pure (~1,4 s, non bouclée, désactivée en `prefers-reduced-motion` par une règle locale de défense en profondeur).
- [x] Contenu éditorial validé, aucun chiffre, témoignage ni preuve fictifs.
- [x] `usePageSeo` réel sur `/` (title, description, canonical, OG, Twitter).
- [x] Pré-rendu de `/` (`routeRules: { '/': { prerender: true } }`).
- [x] Tests unitaires : `expertise-pillars`, `HomeHero`, `HomeEcosystemGraph`.
- [x] Tests E2E : `home-hero.spec.ts` (SEO, un seul H1, CTA, graphe SVG, `prefers-reduced-motion`, Axe, responsive 390/768/1440).
- [ ] Image OG statique `og/devzair-home.png` — **reportée** : aucun logo Devzair n'est disponible dans le dépôt. `defaultOgImage` reste `null`, aucune image fictive n'est publiée, `usePageSeo` retombe automatiquement sur `twitter:card=summary` (cf. DEV-025 dans `10-TRACKING.md`).

Critère de sortie : atteint (lint, typecheck, 69 tests unitaires, 51 E2E,
build de production, tous verts).

### Phase 5B — Accueil : constat, réponse Devzair et cinq pôles détaillés

État actuel : TERMINÉE.

Trois sections livrées sous le hero, dans l'ordre éditorial de
`docs/01-CONTENT.md §7.1` (Constat → Réponse → Cinq pôles). Les sections
suivantes (réalisations, méthode, pourquoi Devzair, FAQ, CTA final) sont
traitées dans les phases 5C+ / 6+.

- [x] Extension de `expertise-pillars.ts` : ajout de `longDescription` (phrase narrative) et `services` (3 par pôle), ajout de la variante `accent` (Faire évoluer). Aucun refactor de `HomeEcosystemGraph.vue` (le champ `description` court reste consommé par le SVG).
- [x] `HomeProblems.vue` — section « Le constat », H2 unique, `<ol>` de 5 items numérotés en Space Mono, layout 2 colonnes ≥768px (bloc éditorial sticky à gauche / liste à droite), 1 colonne <768px. Aucun élément interactif.
- [x] `HomeConnectedApproach.vue` — section « La réponse Devzair » sur fond navy, parcours en 5 étapes en `<ol>` sémantique, source unique `expertisePillars` (aucun label dupliqué). Flèches décoratives `aria-hidden` (verticales <768px, horizontales →768px). Description longue publiée en `sr-only` sur chaque étape pour les lecteurs d'écran, sans surcharger le rendu visuel.
- [x] `HomeExpertisePillars.vue` — grille détaillée, ancre `#expertises`. Mobile : carrousel scroll-snap CSS-natif (aucun JS, aucun bouton, 5 cartes toujours dans le DOM). Tablette : grille 2 colonnes. Desktop ≥1024px : grille asymétrique 3×2 (Concevoir spans 2 rows, Faire évoluer avec fond petrol). Hint « Faites défiler horizontalement » masqué ≥768px, doublé en sr-only.
- [x] `pages/index.vue` orchestre les 4 sections (hero + 3 nouvelles). Ordre édito conforme à §7.1.
- [x] Sémantique WCAG 2.2 AA : un seul H1, un H2 par section, H3 pour les items. Aucune carte focusable, aucun `tabindex`, aucun faux bouton, listes réelles (`<ol>`/`<ul>`).
- [x] Contraste AA vérifié (Axe) : corrections appliquées — `HomeConnectedApproach` step-index en cream (Devzair blue échouait à 4.41:1 sur navy-elevated), `HomeExpertisePillars` accent-card index/tag/puces en cream/cream-muted (Devzair blue échouait à 2.08:1 sur petrol).
- [x] `prefers-reduced-motion` : aucune animation nouvelle, `scroll-behavior: auto` forcé sur le carrousel — défense en profondeur.
- [x] Tests unitaires : 90 verts (+21 : expertise-pillars étendu, HomeProblems×6, HomeConnectedApproach×6, HomeExpertisePillars×8).
- [x] Tests E2E : 72 verts (+21 : `home-sections-primary.spec.ts` — ordre édito, un H2 par section, 5 items par section, ancre #expertises, SSR HTML, Axe, reduced-motion, responsive 320/390/768/1440).
- [x] Build production : 2.79 MB / 725 kB gzip (+40 KB brut / +11 KB gzip vs Phase 5A 2.75 MB / 714 kB). Toujours pré-rendue (`routeRules['/'] = { prerender: true }`).
- [ ] Sections différées explicites : `#realisations` (Phase 5C/7), `#contact` (Phase 6). Les CTA hero pointent toujours vers ces ancres (no-op) — remplaçables en une ligne. Le lien header « Expertises » (nav) conserve `/expertises` (route future) plutôt que d'être détourné vers `/#expertises`.

Critère de sortie Phase 5B : atteint (lint, typecheck, 90 tests unitaires,
72 E2E, build de production, tous verts).

### Phase 5C — Accueil : réalisations, méthode et pourquoi Devzair

État actuel : TERMINÉE.

Trois sections livrées sous `HomeExpertisePillars`, dans l'ordre éditorial
de `docs/01-CONTENT.md §7.1` (Réalisations → Méthode → Pourquoi). Sections
suivantes (FAQ éditoriale, CTA final, ancre `#contact`) traitées en 5D/6+.

- [x] Configurations typées : `app/config/project-process.ts` (6 étapes) et `app/config/trust-promises.ts` (5 promesses), sources uniques verbatim du brief 5C.
- [x] `HomeFeaturedCaseStudy.vue` — section ancrée `#realisations`. Variante « état honnête » : eyebrow `Études de cas en préparation`, titre `Nos réalisations détaillées seront bientôt disponibles.`, paragraphe explicatif. Aucun faux client, aucun faux screenshot, aucun bouton vers route inexistante (AGENTS.md rule 1 + DEV-003 « À faire »).
- [x] `HomeProcess.vue` — section « Notre méthode », H2 unique, `<ol>` de 6 étapes verbatim (`Découverte`, `Cadrage`, `Conception`, `Développement`, `Lancement`, `Évolution`). Mobile : timeline verticale décorative (point + trait via `::before`/`::after`). Tablette 640+ : grille 2 colonnes. Desktop ≥1024px : grille 3×2 (6 colonnes serrées écartées pour la lisibilité).
- [x] `HomeTrust.vue` — section « Pourquoi Devzair », H2 unique, `<ul>` de 5 promesses verbatim (`Approche personnalisée`, `Vision globale`, `Qualité technique`, `Transparence`, `Accompagnement durable`). Aucune interaction. Mobile : liste verticale. Tablette : 2 colonnes + 5ᵉ carte pleine largeur. Desktop ≥1024px : grille asymétrique 3+2 centré.
- [x] `pages/index.vue` orchestre les 7 sections (hero + 3 primaires + 3 secondaires). Ordre édito conforme à §7.1.
- [x] `app/config/navigation.ts` : lien header `Réalisations` recablé de `/realisations` (route inexistante) vers `/#realisations` (ancre active).
- [x] CTA hero « Découvrir nos réalisations » (`#realisations`) : commence à fonctionner sans modification (le composant préexistant pointait déjà sur l'ancre, désormais réellement présente).
- [x] Sémantique WCAG 2.2 AA : un seul H1, un H2 par section, H3 pour les items. Aucune carte focusable, aucun `tabindex`, aucun faux bouton, `<ol>` pour la méthode (ordre significatif), `<ul>` pour les promesses.
- [x] `prefers-reduced-motion` : aucune animation introduite ; sections visibles et exploitables en mode réduit.
- [x] Tests unitaires : 120 verts (+30 vs 5B : projectProcess×5, trustPromises×6, HomeFeaturedCaseStudy×7, HomeProcess×6, HomeTrust×6).
- [x] Tests E2E : 97 verts (+25 : `home-sections-secondary.spec.ts` — ordre édito 7 sections, honest-state SSR, 6 étapes / 5 promesses, ancre CTA + header nav vers `#realisations`, Axe, reduced-motion, responsive 320/390/768/1024/1440).
- [x] Build production : 2.83 MB / 733 kB gzip (+45 KB brut / +8 KB gzip vs Phase 5B 2.79 MB / 725 kB). Toujours pré-rendue (`routeRules['/'] = { prerender: true }`).
- [x] Sections différées explicites levées : CTA final + ancre `#contact` livrés en Phase 5D ; FAQ éditoriale déplacée en Phase 6+ (attachée au parcours de contact réel).

Critère de sortie Phase 5C : atteint (lint, typecheck, 120 tests unitaires,
97 E2E, build de production, tous verts).

### Phase 5D — Accueil : CTA final, ancre `#contact` et clôture Phase 5

État actuel : TERMINÉE.

Livrée : la huitième et dernière section de l'accueil, avec harmonisation
éditoriale, suppression de la page interne `/design-preview` et migration
de sa couverture E2E utile vers `/`.

- [x] `HomeCallToAction.vue` — section ancrée `#contact`, dernière du flux
  éditorial. Verbatim eyebrow `Parlons de votre projet`, H2
  `Construisons une présence digitale à la hauteur de votre entreprise.`,
  paragraphe explicatif. Stratégie de contact conditionnelle : mailto
  `BaseButton` si `site.contact.email` est défini, sinon rien.
- [x] Notice de preprod discrète : `Le moyen de contact en ligne sera
  activé avant la mise en production.` publiée uniquement si
  `!site.contact.email && runtimeConfig.public.siteIndexable === false`.
  Aucun accès à `process.env` depuis le composant (règle DEC-022).
- [x] `pages/index.vue` orchestre les 8 sections (hero → constat →
  réponse → 5 pôles → réalisations → méthode → pourquoi → CTA final).
- [x] Ancrage `#contact` recâblé partout : `primaryCta.to = "/#contact"`
  (une seule source pour header desktop, footer, menu mobile), CTA hero
  principal conservé sur `#contact`. Header nav « Expertises » recablé
  de `/expertises` (route absente) vers `/#expertises` (ancre active).
- [x] Suppression de `apps/web/app/pages/design-preview.vue` et de
  `test/e2e/design-preview.spec.ts`. Coverage utile migrée dans
  `test/e2e/home-structure.spec.ts` (console/router, single H1, skip
  link, header/footer, no fake contact). Sitemap : plus d'exclusion
  explicite requise (la route n'existe plus). `/design-preview` renvoie
  désormais 404, vérifié par `test/e2e/seo.spec.ts`.
- [x] Tests unitaires : 130 verts (+10 : `HomeCallToAction.spec.ts` avec
  `vi.mock('~/config/site')` pour piloter email null/set × indexable
  true/false).
- [x] Tests E2E : `home-cta-final.spec.ts` (+13 : SSR, ancre depuis hero
  / header desktop / footer / menu mobile 390px, notice preprod
  visible, no fake contact, responsive 320/390/768/1440, Axe WCAG 2.2
  AA sur `#contact`, `prefers-reduced-motion`) et `home-structure.spec.ts`
  (+5). Suite `home-sections-secondary.spec.ts` mise à jour pour 8
  sections. Retrait de `design-preview.spec.ts` (-9).
- [x] `npm run quality` vert (lint, typecheck, tests unitaires 130/130,
  build production).

Critère de sortie Phase 5D : atteint. Phase 5 close dans son ensemble
(accueil publique complète, une seule page publique, aucun placeholder
éditorial, aucune coordonnée fictive, ancrage `#contact` fonctionnel).

---

## Phase 6 — Formulaire de contact

La phase est découpée en deux jalons — **6A backend** (livré) et
**6B frontend** (à venir) — pour ne pas coupler le durcissement de
l’endpoint à la livraison du widget navigateur.

### Phase 6A — Backend et transport (TERMINÉE)

- [x] Créer `apps/api` (Symfony 7.4 LTS, PHP 8.4, sans DB ni Doctrine).
- [x] Créer `infra/caddy/` : reverse proxy en frontal, port hôte unique
      `3001:80`, `/api/*` → `api:8000`, reste → `web:3000`.
- [x] Ajouter le service `api` à `compose.yaml`, retirer le port hôte
      direct de `web` (accessible uniquement via Caddy).
- [x] DTO `App\Contact\Dto\ContactRequest` avec contraintes Symfony
      Validator (nom, email, société, téléphone, type projet, message,
      consentement, honeypot `website`, `turnstileToken`).
- [x] Contrôleur `ContactSubmissionController` → service
      `SubmitContactMessage` → interface `ContactMessageSenderInterface`
      (implémentation Symfony Mailer + fake mémoire pour tests).
- [x] Honeypot silencieux (202 générique, aucun email).
- [x] Cloudflare Turnstile côté serveur via `TurnstileVerifierInterface`
      (impl `AlwaysAllow` en dev/test, `Cloudflare` en prod) — factory
      fail-closed si `TURNSTILE_ENABLED=true` sans secret.
- [x] Symfony RateLimiter (token bucket) par IP + `Retry-After` sur 429.
- [x] CSRF Option B (Origin allowlist stricte, aucune session ni cookie).
- [x] Payload > 10 KB → 413.
- [x] `MAILER_DSN=null://null` par défaut ; `From` app-controlled,
      `Reply-To` visiteur, corps texte brut.
- [x] Aucun PII loggué (canal Monolog `contact`) — vérifié par test.
- [x] `Request-Id` UUID v7 dans la réponse JSON, l’en-tête HTTP et tous
      les logs.
- [x] `GET /api/health` → `{"status":"ok"}`.
- [x] Suite PHPUnit : DTO, endpoint (happy path + honeypot + validation +
      Origin + rate limit + payload trop grand), mailer, logs sans PII,
      factory Turnstile, verifier Cloudflare (via `MockHttpClient`).
- [x] Job CI dédié `.github/workflows/api-quality.yml`
      (`composer validate --strict`, `lint:yaml config`, `lint:container`,
      `phpunit`).
- [x] Stabilisation Playwright : suppression des cinq
      `page.waitForLoadState('networkidle')` (remplacés par des attentes
      déterministes sur des éléments hydratés). CI conserve
      `workers: 1`, `retries: 1`, reporter `line` distinguant
      passed/flaky/failed.
- [x] ADR-006 (runtime Symfony/Caddy), ADR-007 (sécurité endpoint contact).

### Phase 6B — Front, page /contact et widget Turnstile (à venir)

- [ ] Créer la page publique `/contact` (Nuxt, SSR).
- [ ] Composant formulaire accessible (labels, erreurs annoncées,
      focus management, `prefers-reduced-motion`).
- [ ] Intégration widget Cloudflare Turnstile côté navigateur.
- [ ] Messages d’état accessibles (succès, erreur, rate limit).
- [ ] Notice de confidentialité (RGPD, base légale, durée).
- [ ] Événements analytics après décision de consentement.
- [ ] Tests E2E : parcours complet, erreurs de validation, honeypot,
      rate limit visible côté UI.

### Critère de sortie (Phase 6 complète)

Une demande réelle est transmise de façon sûre, traçable et conforme,
depuis le formulaire jusqu’à la boîte de réception.

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

## Phase 8 — Extension du backend (données et persistance)

Le squelette `apps/api` et l’image Docker PHP ont été introduits en
Phase 6A. La Phase 8 se concentre sur l’ajout de la persistance et des
domaines métier.

- [x] ~~Créer `apps/api` avec Symfony 7.4 LTS.~~ (livré Phase 6A)
- [x] ~~Ajouter Docker PHP.~~ (livré Phase 6A)
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
