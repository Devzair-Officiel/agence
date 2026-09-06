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

La phase est découpée en trois jalons — **6A backend** (livré),
**6B frontend** (livré) et **6C mise en production du transport SMTP**
(livré) — pour ne pas coupler le durcissement de l’endpoint à la
livraison du widget navigateur ni au branchement d’OVHcloud.

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

### Phase 6B — Front, formulaire ancré `#contact` et widget Turnstile (TERMINÉE)

Le formulaire est intégré à la section finale `#contact` de l'accueil (pas de
page `/contact` dédiée : le one-pager reste la surface publique). La stratégie
de découplage (composable Vue pur, sans coupleur Nuxt) et le mode dev-noop
Turnstile sont documentés dans les commentaires de tête des fichiers.

- [x] Composant `ContactForm.vue` accessible orchestrateur (labels visibles,
      erreurs par champ, focus management, `role="status"`/`role="alert"`,
      `aria-live`, `prefers-reduced-motion` respecté).
- [x] Primitives `ContactFormField.vue` (label + input/textarea + hint + error
      relié via `aria-describedby`) et `ContactFormStatus.vue` (bandeau
      focusable succès/erreur avec `request_id`).
- [x] Composable `useContactForm` (état réactif, validation client miroir non
      strict du DTO Symfony, mapping HTTP 200/202/400/403/413/429 → codes
      ADR-007, protection double-clic, reset sur succès).
- [x] Types partagés `types/contact.ts` (union discriminée
      `ContactSubmitResponse`, énuméré `ProjectType`).
- [x] Widget Cloudflare Turnstile `TurnstileWidget.vue` client-only avec
      fallback `dev-noop` quand la site-key est vide (accepté côté API par
      `AlwaysAllowTurnstileVerifier`).
- [x] Honeypot `website` (position hors flux, `aria-hidden`, `tabindex=-1`,
      transmis tel quel au serveur).
- [x] Notice de confidentialité RGPD reliée au `<form>` via
      `aria-describedby` (base légale, conservation, droits).
- [x] Intégration dans `HomeCallToAction.vue` : suppression du mailto
      conditionnel et de la notice preprod (placeholder Phase 5D).
- [x] Runtime config `NUXT_PUBLIC_TURNSTILE_SITE_KEY` propagée depuis
      `nuxt.config.ts`, `.env.example` et `compose.yaml`.
- [x] Tests unitaires : 20 tests `useContactForm` (validation + mapping HTTP)
      et 6 tests `ContactForm` (structure, honeypot, ARIA, submit disabled).
- [x] Tests E2E `contact-form.spec.ts` : SSR, happy path avec Turnstile
      dev-noop, HTTP 400 par champ, HTTP 429 avec `Retry-After`, honeypot
      transmis, validation client bloque une soumission vide, Axe WCAG 2.2 AA
      restreint au form, `prefers-reduced-motion`.
- [x] Tests E2E `home-cta-final.spec.ts` réalignés (présence du `<form>` +
      champs, suppression des assertions preprod/mailto obsolètes).
- [x] Suite Playwright : 117 passed / 0 failed / 0 flaky.
- [x] Poids build : 2.85 MB brut / 740 kB gzip (dans le budget +80 KB / +20 KB
      par rapport à la baseline 2.10 de 2.8 MB / 728 kB).

### Phase 6C — Configuration OVHcloud, Turnstile facultatif et 503 sur échec SMTP (TERMINÉE)

Phase 6C prépare l’activation réelle du formulaire chez OVHcloud sans
mettre en production de secret, sans envoyer un vrai email en CI, et
sans imposer Turnstile tant que le trafic ne le justifie pas. Décisions
consignées dans `docs/adr/ADR-008-mailer-ovhcloud-turnstile-optionnel.md` ;
séquence opérationnelle dans `docs/checklists/PRODUCTION-CONTACT.md`.

- [x] Transport SMTP piloté par `MAILER_DSN` uniquement (aucun nom
      d’hôte dans le code, aucun secret en Git). Formes admises :
      `null://null` (dev/test), `smtps://…:465` (prod recommandée),
      `smtp://…:587` (STARTTLS hors prod).
- [x] Turnstile facultatif via **deux flags alignés**
      `TURNSTILE_ENABLED` (API) et `NUXT_PUBLIC_TURNSTILE_ENABLED`
      (front). Défaut sûr `false` des deux côtés : aucun script
      Cloudflare n’est injecté, le widget émet immédiatement le token
      `dev-noop`, bannière visible « Mode dev » sur le composant.
- [x] Réponse HTTP `503 temporary_error` (jamais 202/200 fictif) sur
      toute `TransportExceptionInterface` ou `CONTACT_RECIPIENT` absent.
      Front : bandeau verbatim « Le service est momentanément
      indisponible. […] », valeurs saisies conservées, aucun retry
      automatique navigateur.
- [x] Marker de domaine `ContactTemporarilyUnavailableException` +
      `SymfonyContactMessageSender` qui chaîne l’exception d’origine
      pour le debug interne uniquement.
- [x] Log Monolog `contact.mailer_unavailable` (canal `contact`, niveau
      warning), aucun PII — mêmes garanties que le happy path.
- [x] Service pur `ContactConfigurationValidator` (aucune I/O) inspectant
      `MAILER_DSN`, `CONTACT_RECIPIENT`, `CONTACT_FROM_EMAIL`,
      `CONTACT_FROM_NAME`, Turnstile activé ⇒ secret présent,
      `CONTACT_ORIGIN_ALLOWLIST`, `TRUSTED_PROXIES`. Émet errors +
      warnings sans révéler DSN complet, secret ni email en clair.
- [x] Commande CLI `bin/console app:contact:check` (SymfonyStyle,
      exit `0`/`1`) exposant le rapport pour dev, CI et image de prod.
- [x] Suite PHPUnit étendue : `ContactConfigurationValidatorTest`
      (14 règles + assertion non-fuite de secret),
      `ContactCheckCommandTest` (3 cas pure `TestCase`, sans KernelTestCase :
      valeurs valides → exit 0, erreurs → exit 1 avec codes listés,
      aucune fuite de DSN/secret),
      `SymfonyContactMessageSenderTest::testSendConvertsTransportException…`,
      `ContactSubmissionControllerTest::testMailerFailureReturns503…`,
      `ContactLoggingTest::testMailerFailureLogsWarningWithoutPii`.
      `InMemoryContactMessageSender` étendu (`failNextTemporarily`) —
      seam explicite pour simuler l’échec SMTP sans réseau.
- [x] Suite Vitest étendue : `useContactForm.spec.ts` (nouveau cas
      HTTP 503 → code `temporary_error` + valeurs préservées) et
      `ContactForm.spec.ts` (bandeau verbatim + valeurs préservées).
- [x] Suite Playwright étendue : `contact-form.spec.ts` — scénario 503
      (backend mocké 503 → bandeau + valeurs préservées) et scénario
      « Turnstile désactivé (défaut) : aucun script Cloudflare chargé »
      (zéro requête vers `challenges.cloudflare.com`, dev-notice visible).
- [x] Documentation opérationnelle : ADR-008,
      `docs/checklists/PRODUCTION-CONTACT.md` (10 sections dont
      pré-déploiement, test réel maîtrisé, dégradation contrôlée,
      revue sécurité, rollback), READMEs (racine, `apps/api`,
      `apps/web`), `.env.example`, `compose.yaml`.

### Critère de sortie (Phase 6 complète)

Une demande réelle est transmise de façon sûre, traçable et conforme,
depuis le formulaire jusqu’à la boîte de réception. Toute défaillance
SMTP transitoire est signalée honnêtement à l’utilisateur (bandeau 503
verbatim + valeurs préservées) et à l’opérateur (log
`contact.mailer_unavailable`, aucun message perdu silencieusement).

---

## Phase 7 — Architecture éditoriale, expertises et réalisations

La phase est découpée en trois jalons — **7A architecture éditoriale et
pages institutionnelles** (livré), **7B pages d'expertise détaillées**
(à venir) et **7C réalisations et autorité éditoriale** (à venir) — pour
livrer d'abord une base éditoriale saine, réutilisable et sans lien mort
avant de multiplier les pages profondes.

### Phase 7A — Architecture éditoriale et pages `/agence` + `/expertises` (TERMINÉE)

- [x] Configuration typée `app/config/expertise-pages.ts` (5 pages
      planifiées, `status: "planned"` unique valeur autorisée en 7A,
      slug/route/title/shortTitle/summary/services miroirs de
      `expertise-pillars.ts`). Aucune route `/expertises/{slug}` créée.
- [x] Composants éditoriaux réutilisables : `EditorialHero.vue`
      (eyebrow + H1 + lead + slot actions),
      `EditorialSection.vue` (H2 obligatoire, eyebrow/intro/slot
      optionnels, variantes `default` / `subtle` / `inverse`,
      `aria-labelledby` via `useId`),
      `EditorialCallout.vue` (bandeau CTA H2 + primaryCta + secondaryCta
      facultatif, variantes `inverse` / `accent`),
      `ExpertiseOverviewCard.vue` (`<article>`, H3 non lié, description
      longue, 3 services `<ul>`, badge d'ordre `aria-hidden`, **aucun
      lien en 7A**).
- [x] `pages/agence.vue` — H1 verbatim, eyebrow `L'agence`, introduction
      verbatim, sections Positionnement / Fonctionnement / Valeurs,
      callout final vers `/expertises`. `usePageSeo` avec title
      `Agence digitale à taille humaine`.
- [x] `pages/expertises/index.vue` — H1 verbatim, eyebrow
      `Nos expertises`, introduction verbatim, section « Notre approche »,
      grille de 5 `ExpertiseOverviewCard`, callout final vers `/agence`.
      `usePageSeo` avec title `Expertises web, design, contenu et SEO`.
- [x] Pré-rendu `/agence` et `/expertises` (`routeRules`) — SSR vérifié
      sur `.output/public/{agence,expertises}/index.html`.
- [x] Navigation principale recablée : `Expertises` (`/expertises`),
      `Agence` (`/agence`), `Réalisations` (`/#realisations`), CTA
      `/#contact`. Liens morts supprimés (`Méthode`, `Ressources`).
      Footer réduit au groupe `Découvrir` (3 entrées vivantes).
      `legalNavigation = []` (aucune page légale publiée). Ajout d'un
      champ `isRoute: boolean` sur `NavigationItem` pour piloter
      `NuxtLink :external` proprement (fin des warnings vue-router R0004
      sur les ancres).
- [x] Tests unitaires : +51 tests (config `expertise-pages` ×11,
      `EditorialHero` ×7, `EditorialSection` ×9, `EditorialCallout` ×7,
      `ExpertiseOverviewCard` ×6, page `/agence` ×6, page `/expertises`
      ×7). Total Vitest 213/213 verts.
- [x] Tests E2E `institutional-pages.spec.ts` (18 cas) : SSR HTML,
      unique H1 verbatim, eyebrow + introduction en SSR, SEO complet
      (title, description, canonical *ou* noindex, `og:url`), lien réel
      vers `/#contact`, absence de lien vers `/expertises/{slug}`, 5
      cartes sans `<a>`, navigation principale exposant les 3 entrées +
      CTA, maillage `/agence` ↔ `/expertises`, responsive 390/768/1440,
      Axe WCAG 2.2 AA, `prefers-reduced-motion`.
- [x] Build production : 2.92 MB brut / 759 kB gzip (+70 KB / +18 KB vs
      Phase 6C 2.85 MB / 741 kB). Toujours pré-rendu pour les 3 pages
      publiques (`/`, `/agence`, `/expertises`).

Critère de sortie Phase 7A : atteint. Trois pages publiques cohérentes,
un maillage inter-pages sans lien mort, une architecture éditoriale
réutilisable prête pour 7B (pages `/expertises/{slug}` — 5 pages
détaillées, contenu à valider avec le client) et 7C (études de cas
réelles).

### Phase 7B — Pages d'expertise détaillées (TERMINÉE)

Cinq pages publiques `/expertises/{slug}` livrées via une route dynamique
unique `pages/expertises/[slug].vue`, adossées à une extension typée du
contrat `expertise-pages.ts` (11 champs éditoriaux supplémentaires, tous
`readonly`, aucune valeur inventée). Contenu verbatim par pôle
(introduction, `À qui cela s'adresse`, `Notre approche`, livrables, bénéfices,
maillage vers 2 pôles connexes) ; SEO complet (title, description,
canonical, `og:*`, Schema.org `Service`) ; fil d'Ariane accessible ;
HTTP 404 explicite pour un slug inconnu (aucun fallback silencieux).

- [x] Extension du contrat `app/config/expertise-pages.ts` : passage à
      `status: "published"` pour les 5 entrées + 11 champs verbatim
      (`eyebrow`, `introduction`, `seoTitle`, `seoDescription`,
      `needTitle`, `needDescription`, `approachTitle`,
      `approachDescription`, `deliverables`, `benefits`,
      `relatedPillarIds`). Nouveau type `ExpertiseBenefit`. Tous les
      champs `readonly` (immuabilité du contrat).
- [x] Route dynamique `app/pages/expertises/[slug].vue` : lit
      `useRoute().params.slug`, résout via
      `expertisePages.find(p => p.slug === slug && p.status === "published")`,
      lance `createError({ statusCode: 404, fatal: true })` sinon.
      Orchestre `SiteBreadcrumb` + `ExpertisePageHero` + 4
      `EditorialSection` + `EditorialCallout` (retour vers `/expertises`).
- [x] Composable `app/composables/useExpertiseServiceSchema.ts` : émet un
      JSON-LD Schema.org `Service` avec `@id = ${url}#service` et
      `provider: { "@id": ${origin}/#organization }` — référence
      l'`@id` de l'Organization global (`useSiteSchema`), aucun
      `offers`/`price`/`aggregateRating`/`review` fictif.
- [x] Composants livrés : `SiteBreadcrumb.vue`
      (`<nav aria-label="Fil d'Ariane">`, `<ol role="list">`,
      `aria-current="page"` sur le dernier item, séparateur `›`
      `aria-hidden`), `ExpertisePageHero.vue` (H1 unique par page,
      eyebrow + titre + introduction), `ExpertiseDeliverables.vue`
      (`<ul role="list">` de pastilles, marqueurs `aria-hidden`),
      `ExpertiseBenefits.vue` (grille responsive de bénéfices H3+p),
      `ExpertiseRelatedPillars.vue` (2 cartes-liens vers les pôles
      connexes, filtrage `status === "published"` — dégradation
      silencieuse).
- [x] `ExpertiseOverviewCard.vue` refactorée en carte cliquable
      conditionnelle : `<NuxtLink v-if="isPublished()">` +
      `<article v-else>` (pas de `<component :is>` — casserait
      l'hydratation NuxtLink). CTA `Découvrir ce pôle →` en
      `--color-petrol` (contraste ≥ 6:1 sur cream/sand, vs 3.19:1 avec
      `--color-devzair-blue`).
- [x] `pages/expertises/index.vue` joint chaque `pillar` à sa
      `ExpertisePageDefinition` via `pagesByPillarId` (Map computed) :
      passage de `:page="pagesByPillarId.get(pillar.id)"` à chaque
      carte pour activer le lien lorsque la page est publiée.
- [x] Pré-rendu ajouté (`nuxt.config.ts routeRules`) sur les 5 nouvelles
      routes : `/expertises/{concevoir,construire,valoriser,visibilite,faire-evoluer}`.
      Vérifié sur `.output/public/expertises/{slug}/index.html`.
- [x] Sitemap XML : les 5 nouvelles routes apparaissent automatiquement
      (chaînage `@nuxtjs/sitemap` sur les `routeRules.prerender`).
      Aucune configuration supplémentaire.
- [x] Tests unitaires : 258/258 verts (+45 sur 213 Phase 7A) —
      `expertise-pages` étendu (+9 assertions groupées : 11 champs
      requis, `status === "published"` universel, 2 `relatedPillarIds`
      par entrée, pas d'auto-référence, cible existante et publiée…),
      `SiteBreadcrumb` ×6, `ExpertisePageHero` ×5, `ExpertiseDeliverables`
      ×4, `ExpertiseBenefits` ×5, `ExpertiseRelatedPillars` ×5,
      `useExpertiseServiceSchema` ×5, `pages/expertise-slug` ×7 (mount
      dynamique par slug via `vi.resetModules()` + `await import`).
- [x] Tests E2E : 200/200 verts (+22 sur 178 Phase 7A) — nouvelle suite
      `test/e2e/expertise-pages.spec.ts` (SSR 200 par slug, HTTP 404
      slug inconnu, présence sitemap XML, JSON-LD `@type":"Service"`
      dans le HTML pré-rendu, fil d'Ariane accessible, absence de
      contenu placeholder/lorem/TODO, 2 liens de pôles connexes,
      navigation mobile fonctionnelle, responsive 320/390/768/1024/1440,
      Axe WCAG 2.2 AA sans violation `serious`/`critical`,
      `prefers-reduced-motion`). Régex tolérante à l'encodage
      HTML des apostrophes (`'` ⇔ `&#39;` ⇔ `&#x27;`) en SSR.
      Playwright `--workers=1 --retries=0` → 200 passed, 0 flaky,
      0 failed.
- [x] Test E2E `institutional-pages.spec.ts` réaligné : l'assertion
      « ne rend aucune carte comme lien tant que Phase 7B n'est pas
      livrée » inversée — la page `/expertises` doit désormais
      exposer 5 liens vers les pages détaillées.
- [x] Build production : 3.00 MB brut / 782 kB gzip (+80 KB / +23 KB
      vs Phase 7A 2.92 MB / 759 kB). 8 pages publiques pré-rendues
      (`/`, `/agence`, `/expertises`, 5 × `/expertises/{slug}`).

Critère de sortie Phase 7B : atteint. 5 pages publiques cohérentes
avec l'architecture éditoriale posée en 7A, aucune route morte, HTTP 404
strict sur slug inconnu, Schema.org `Service` réel adossé à
l'Organization globale, navigation croisée entre pôles opérationnelle.

### Phase 7C — Réalisations et autorité éditoriale (À VENIR)

- [ ] Modèle d’étude de cas.
- [ ] Preuves et autorisations.
- [ ] Périodes et méthodes de mesure.
- [ ] Composants de liste et détail.
- [ ] Schema.org adapté.
- [ ] Maillage vers les services.
- [ ] Pages auteurs réels si utiles.
- [ ] Politique de mise à jour.

### Critère de sortie (Phase 7 complète)

Chaque expertise dispose d'une page publique cohérente, chaque preuve
publiée est vérifiable et contextualisée, et le maillage `/agence` ↔
`/expertises` ↔ `/expertises/{slug}` ↔ `/#realisations` est complet
sans lien mort.

---

## Phase 8 — Extension du backend (données et persistance)

Le squelette `apps/api` et l’image Docker PHP ont été introduits en
Phase 6A. La Phase 8 est découpée en trois jalons — **8A domaine
éditorial + persistance + API publique de lecture** (livré),
**8B écriture et rendu markdown** (à venir), **8C administration
authentifiée** (à venir).

### Phase 8A — Domaine éditorial, PostgreSQL et lecture publique (TERMINÉE)

- [x] ~~Créer `apps/api` avec Symfony 7.4 LTS.~~ (livré Phase 6A)
- [x] ~~Ajouter Docker PHP.~~ (livré Phase 6A)
- [x] Ajouter PostgreSQL 17-alpine (version verrouillée, ADR-009). Service
      Compose interne uniquement, healthcheck `pg_isready`, jamais publié
      sur l'hôte.
- [x] Installer Doctrine ORM 3 (`^3.3`) + DoctrineBundle (`^2.13`) +
      MigrationsBundle (`^3.4`). Extension `pdo_pgsql` ajoutée au
      Dockerfile.dev.
- [x] Mapping Doctrine par attributs PHP sur `App\Editorial\Domain` uniquement
      (le reste de l'API reste sans ORM). Racine migrations `App\Migrations`.
- [x] Domaine `Editorial` : agrégat `Article`, VO `ArticleSlug`, `Author`,
      `SeoMetadata`, enums `ArticleStatus` (Draft/Published/Archived),
      `AuthorType` (organization/person), `ExpertiseIdentifier` (5 piliers
      alignés sur `expertise-pillars.ts` côté Nuxt).
      Invariants : Published ⇔ publishedAt non nul, dédoublonnage des piliers,
      transitions `publish()` / `archive()` idempotentes, ClockInterface
      injecté (SystemClock en prod, FixedClock en test).
- [x] Migration `Version20260803120000` : table `editorial_article` avec
      UUID natif, slug unique, `expertise_ids` en `jsonb`, timestamps `timestamptz`,
      index composite `(status, published_at)`.
- [x] Port `ArticleRepositoryInterface` + implémentation Doctrine
      (`DoctrineArticleRepository`) + double en mémoire pour les tests
      unitaires (`InMemoryArticleRepository`).
- [x] Couche Application : `ListPublishedArticles(Handler)` + `GetPublishedArticle(Handler)`,
      DTO de vue `ArticleSummaryView` / `ArticleDetailView` / `PaginationView`.
- [x] API publique de lecture (Caddy strippe `/api`) :
      - `GET /api/resources?page=1&per_page=10` → 200 avec `{items, pagination, request_id}`.
        Bornes : `page ≥ 1`, `per_page ∈ [1, 50]`, invalide → 400 `validation_error`.
      - `GET /api/resources/{slug}` → 200 (article publié), 404 (draft/archivé/inconnu),
        400 (slug malformé).
      - Contrat `X-Request-Id` (UUID v7) + `Cache-Control: public, max-age=60, s-maxage=300`
        sur les 200 ; `no-store` sur les 4xx.
      - Payload markdown brut ; le rendu HTML (renderer + sanitizer) est
        repoussé en Phase 8B.
- [x] Canal Monolog `editorial` dédié aux lectures publiques. Aucun PII n'est
      pertinent ici mais on garde le pattern Request-Id + corrélation logs.
- [x] Suite PHPUnit étendue : tests Domain (Article, ArticleSlug, Author,
      SeoMetadata, ExpertiseIdentifier), Application (List/Get handlers via
      InMemory repo), Infrastructure (DoctrineArticleRepositoryTest sur
      `devzair_test` avec transaction rollback), Presentation (WebTestCase
      couvrant contrat JSON, cache headers, pagination, 400/404).
- [x] Workflow CI `.github/workflows/api-quality.yml` étendu : service
      `postgres:17-alpine` (5432 exposé), extension `pdo_pgsql`, étapes
      `doctrine:migrations:migrate` puis `doctrine:schema:validate` avant PHPUnit.
- [x] Documentation : ADR-009 (persistance PostgreSQL + Doctrine ORM 3 +
      choix UUID / JSONB / markdown brut), READMEs racine + `apps/api` mis à
      jour, `docs/03-SEO-NUXT.md` (Phase 8B introduira SEO côté Nuxt),
      `docs/05-SECURITY-PRIVACY.md` (Postgres jamais exposé), `docs/06-ARCHITECTURE-CODE.md`
      (arborescence Editorial), `docs/07-QUALITY-DELIVERY.md` (nouveaux jeux de tests).

Critère de sortie Phase 8A : atteint. L'API publique lit un article publié
sous URL stable, retourne un contrat JSON versionnable, garantit qu'aucun
brouillon ni archive ne fuit, et permet à la Phase 8B de brancher un flux
d'écriture sans modifier ni le domaine ni le contrat public.

### Consolidation qualité E2E (DEV-045, 2026-08-04)

Entre Phase 8A et Phase 8B, correctifs frontaux ciblés (aucune modification
`apps/api/`) pour lever 3 violations Axe WCAG 2.2 AA (nested-interactive sur
`HomeEcosystemGraph`, contraste dev-notice `TurnstileWidget`, viewBox SVG écrêté),
2 régressions comportementales du formulaire de contact (label `consent`
non-cliquable à cause d'un décor sans `pointer-events: none`, bouton primaire
sans `type="submit"` explicite), et 5 flakies Playwright liés à la course
d'hydratation Vue sous `nuxt dev`. Nouveau pattern formalisé DEC-073 :
marqueur `data-hydrated="true"` posé dans `onMounted` du composant *parent
qui porte le handler* (Vue 3 monte les enfants avant le parent — un signal
enfant ne prouve pas l'attachement du listener parent). Deux passes
Playwright complètes vertes sur `nuxt dev` fraîchement redémarré :
retries=0 workers=1 → **228/0/0** et retries=1 workers=1 → **228/0/0**.
Vitest 258/258, PHPUnit 110/229 inchangé, curl SEO OK sur `/`, `/contact`,
`/sitemap.xml`. Aucun `waitForTimeout`, aucun `force: true`, aucun test
désactivé, aucune règle Axe désactivée. La Phase 8B peut démarrer sur une
base E2E propre.

### Phase 8B — Écriture, rendu markdown et validation avancée (TERMINÉE)

**Découpage** :

- **8B1 Pipeline éditorial sécurisé et cache HTTP conditionnel** (livré) :
  import CLI de Markdown vers Postgres, publication manuelle explicite,
  cache HTTP conditionnel `ETag`/`Last-Modified` avec 304, refus des
  publications futures. Aucune page Nuxt éditoriale, aucun endpoint
  d'écriture HTTP, aucun back-office.
- **8B2 Rendu HTML Markdown côté serveur + pages `/ressources`** (livré) :
  rendu HTML backend (League CommonMark + `MarkdownSecurityPolicy`),
  `content_html` calculé à la requête (non persisté, aucune migration
  Doctrine, aucune commande `app:editorial:rerender`), ETag détail
  `v1 → v2` après évolution du payload, pages Nuxt SSR complètes,
  frontière `v-html` unique (`ResourceContent`), cache Nitro respectant
  HTTP, sitemap dynamique honnête.

#### Phase 8B1 — Pipeline éditorial sécurisé et cache HTTP conditionnel (TERMINÉE)

- [x] Format YAML front matter + Markdown body versionné et documenté
      (`ArticleFrontMatter` typé, clés inconnues refusées, `publishedAt`
      interdit dans le front matter).
- [x] Parseur `MarkdownArticleFileParser` : fichier ≤ 512 Kio, UTF-8
      strict (BOM refusé), délimiteurs `---`, refus d'un champ racine /
      SEO / auteur / expertise inconnu ou d'un `AuthorType` invalide.
- [x] Validateur `MarkdownContentValidator` sur AST CommonMark : refus
      explicite de tout `HtmlBlock` / `HtmlInline` et de toute URL dont
      le schéma n'est pas dans `[http, https, mailto, tel]`. Violations
      agrégées dans une seule exception.
- [x] Politique `MarkdownSecurityPolicy` figée par un test dédié
      (`html_input=strip`, `allow_unsafe_links=false`,
      `max_nesting_level=15`, `max_delimiters_per_line=100`) — la
      configuration ne peut pas être affaiblie sans casser la suite.
- [x] Handler `ImportArticleFromMarkdownHandler` : import « create only »
      (collision de slug refusée), article importé toujours en `Draft`,
      support `--dry-run` (aucun flush).
- [x] Handler `PublishArticleBySlugHandler` : refus `publishedAt` futur,
      idempotence sur article déjà publié, `ClockInterface` injecté.
- [x] Ajout au port `ArticleRepositoryInterface` d'une méthode neutre
      `findBySlug(ArticleSlug): ?Article` (utilisée uniquement par
      Import et Publish).
- [x] Modification signature `getPublishedBySlug/listPublished/countPublished`
      pour recevoir `\DateTimeImmutable $now` — double-filtre `status =
      Published AND publishedAt <= :now` en Doctrine et en mémoire.
- [x] `Article::publish($publishedAt, $now)` refuse `$publishedAt > $now`.
- [x] Commandes CLI `app:editorial:import <path> [--dry-run]` et
      `app:editorial:publish <slug> [--published-at=<ISO8601>]`.
      Publication programmée : la date doit être ISO 8601 avec fuseau
      explicite (`Z` ou `+HH:MM`) ; « naïve » refusée.
- [x] Cache HTTP conditionnel : `ArticleETag` faible sur les deux
      endpoints (`W/"sha256(...)"`), `Last-Modified` sur le détail
      uniquement (RFC 7231), 304 Not Modified sur `If-None-Match` et
      `If-Modified-Since` matchant. `X-Request-Id` réécrit sur 304 pour
      préserver la corrélation logs.
- [x] Suite PHPUnit : Markdown (parser + validator + policy = 24 tests),
      handlers Import/Publish (9 tests), CLI via `CommandTester` (11
      tests), HTTP conditional cache (7 tests) — plus ajustement des
      tests Domain/Application existants pour la nouvelle signature
      (double-filtre `$now`, refus des dates futures).
- [x] ADR-010 documente les choix (import CLI, refus des dates futures,
      cache faible avec `Last-Modified` sur le détail uniquement).
- [x] Aucun changement frontend, aucune migration Doctrine, aucun
      endpoint HTTP nouveau, aucun fichier `content/` publié dans Git.

#### Phase 8B2 — Rendu HTML Markdown côté Nuxt et pages `/ressources` (TERMINÉE)

Débloquée le 2026-08-05 par DEV-048 (Playwright Nitro : 228/228, 0 flaky).
Livrée le 2026-08-05 par DEV-049 : intégration SSR complète des ressources
éditoriales, avec sept corrections obligatoires appliquées (voir
`docs/adr/ADR-011-ssr-nuxt-editorial-cache-nitro.md`).

- [x] Rendu Markdown → HTML côté **backend** (League CommonMark avec
      `MarkdownSecurityPolicy` : `html_input=strip`, URLs restreintes,
      alignée sur la politique CLI Phase 8B1). `content_html` calculé à la
      requête par `GetPublishedArticleHandler` via `CommonMarkArticleRenderer`
      et exposé dans `ArticleDetailView` (aucune persistance HTML, aucune
      nouvelle migration). Bump `ArticleETag` v1→v2 pour invalider les
      caches HTTP après ajout du champ au payload détail.
- [x] Pages `/ressources` (index) et `/ressources/{slug}` (détail) côté
      Nuxt, SSR strict (`useAsyncData` avec clés stables).
- [x] Endpoints Nitro internes `/_editorial/list` et
      `/_editorial/detail/{slug}` (préfixe non conflictuel avec Caddy),
      cache local `useStorage("editorial")` respectant HTTP (validateur
      `If-None-Match` posé UNIQUEMENT depuis le cache local, jamais du
      navigateur).
- [x] Composant unique frontière de confiance `ResourceContent.vue` (seul
      point d'usage `v-html` du site, `vue/no-v-html` désactivé localement
      avec justification).
- [x] Métadonnées SEO dynamiques (`useArticleSeo`) : JSON-LD `BlogPosting`
      référençant l'`Organization` par `@id`, `BreadcrumbList` Accueil →
      Ressources → Article, canonical propre.
- [x] Sitemap dynamique via source `/__sitemap__/resources` (échec honnête
      en 503 si l'API tombe — refus d'un sitemap partiel).
- [x] Contrat d'erreur strict côté page : 404 (slug inconnu / brouillon /
      archivé / futur / hors bornes), 503 (API indisponible / payload
      invalide), 301 sur `?page=1` → `/ressources` (URL canonique unique).
- [x] Commande console `app:editorial:e2e-fixtures <load|clear>` scopée
      dev/test (`#[When]`), suppression restreinte au préfixe `e2e-8b2-`
      (jamais de TRUNCATE ni de DELETE global) — script orchestrateur
      `scripts/e2e-fixtures.sh` pour découpler Playwright de docker.sock.
- [x] Tests : 55 tests unitaires Vitest (contrat, api, cache, composables,
      composants), 13 scénarios Playwright dédiés `/ressources` via Caddy
      (241/241 E2E verts au total).

### Phase 8C — Administration authentifiée

Décomposée en quatre itérations indépendantes. Décisions techniques
consignées dans `docs/adr/ADR-012-administration-symfony-ssr-authentifiee.md`.

#### Phase 8C1 — Socle authentification admin (TERMINÉE)

Livrée le 2026-08-06 par DEV-050. Socle SSR authentifié Symfony/Twig
same-origin sous `/admin/**`, sans capacité éditoriale ni API JSON
dédiée (voir ADR-012).

- [x] Rendu SSR Symfony + Twig sous `/admin/**` (Caddy préserve le
      préfixe, aucun `strip_prefix` sur cette famille de routes).
      Ajout de `TwigBundle` et de `apps/api/config/packages/twig.yaml`.
- [x] Domaine `Admin` : agrégat `AdminUser` (UUID v7, ROLE_ADMIN
      implicite, pas de colonne `roles` en base), VO `AdminEmail`
      (normalisation `mb_strtolower(trim())` + index unique
      `normalized_email`), invariants métier, repository Doctrine
      dédié. Migration `Version20260805221410`.
- [x] Firewall Symfony `admin` (`^/admin`) : `form_login` avec
      `post_only: true`, CSRF `authenticate` sur login, `logout`
      POST-only avec CSRF `logout` et `invalidate_session: true`,
      `session_fixation_strategy: migrate` explicite,
      `login_throttling` 5 tentatives / 15 minutes (fenêtres
      `_login_local_admin` + `_login_global_admin`).
- [x] `AdminUserProvider` (lookup par email normalisé,
      `UserNotFoundException` générique) et `AdminUserChecker`
      (refuse `is_active = false` via `DisabledException`).
- [x] Password hashing `auto` (Argon2id en prod) ; override
      `bcrypt cost 4` en test pour compresser le temps de hash.
- [x] Session cookie dédié `DZ_ADMIN_SESSID` (HttpOnly, SameSite=Lax,
      Secure=auto), `cookie_lifetime` et `gc_maxlifetime` pilotés par
      `ADMIN_SESSION_LIFETIME` (défaut 28 800 s). Stockage filesystem
      local (single instance).
- [x] Deux vues Twig uniquement : `/admin/login` (formulaire, aucun
      JS) et `/admin` (dashboard = profil + logout). `AdminLoginController`
      redirige les authentifiés vers `admin_dashboard`.
      `AdminDashboardController` protégé par
      `#[IsGranted('ROLE_ADMIN')]`.
- [x] Message d'erreur générique unique (« Identifiants invalides »)
      — aucune divulgation d'existence d'un compte.
- [x] Headers défensifs scopés `/admin/*` via
      `AdminSecurityHeadersSubscriber` : CSP durcie
      (`default-src 'none'; script-src 'none'; style-src 'self';
      img-src 'self' data:; font-src 'self'; form-action 'self';
      base-uri 'none'; frame-ancestors 'none'`, CSS externalisé dans
      `apps/api/public/admin/assets/admin.css`, `unsafe-inline`
      supprimé), `X-Frame-Options: DENY`, `X-Content-Type-Options:
      nosniff`, `Referrer-Policy: no-referrer`, `Permissions-Policy`
      restrictive, COOP/CORP `same-origin`, `X-Robots-Tag: noindex,
      nofollow`.
- [x] Non-indexation renforcée dans `apps/web/nuxt.config.ts` :
      `Disallow: /admin` (et `/admin/`) pour tous les groupes de
      bots incluant IA (`GPTBot`, `Google-Extended`, `ClaudeBot`,
      `PerplexityBot`, `CCBot`).
- [x] Canal Monolog `admin` + `AdminAuditListener` :
      `admin.login_failure` avec short class name uniquement (aucune
      PII, pas d'email, pas d'IP), `admin.logout` avec UUID admin
      uniquement. `RecordSuccessfulLoginListener` met à jour
      `last_login_at` sur l'entité.
- [x] Commandes CLI `app:admin:create-user`,
      `app:admin:reset-password`, `app:admin:disable` — mot de passe
      via `--password-stdin` ou `askHidden` interactif uniquement
      (jamais via `--password=xxx`). Toutes idempotentes.
- [x] Tests : intégration Symfony `WebTestCase` (auth, redirection,
      throttling, disabled, session fixation, CSRF, headers,
      logout) + E2E Playwright via Caddy (`admin.spec.ts` :
      accessibilité Axe WCAG 2.2 AA sur `/admin/login` et `/admin`,
      login valide, login invalide, headers de sécurité). Overrides
      `services_test.yaml` : session `mock_file`, `InMemoryStorage`
      pour les deux fenêtres de throttling.
- [x] Documentation : ADR-012 acceptée le 2026-08-06,
      `.env.example` documente `ADMIN_SESSION_LIFETIME`,
      `ADMIN_LOGIN_MAX_ATTEMPTS`, `ADMIN_LOGIN_INTERVAL`.

#### Phase 8C2 — Mutations métier explicites de l'agrégat Article (TERMINÉE)

Livrée le 2026-08-06 par DEV-051. **Backend only** — aucune route
HTTP, aucun contrôleur admin, aucun template Twig, aucune migration,
aucun changement Nuxt. La phase prépare l'IHM éditoriale à venir
(reportée) en verrouillant côté domaine et application les mutations
autorisées sur un article en `Draft`.

- [x] 7 mutations métier explicites ajoutées à l'agrégat `Article`
      (`changeTitle`, `changeExcerpt`, `rewriteBody`, `changeSeo`,
      `changeAuthor`, `changeExpertises`, `restore`). Toutes refusent
      la mutation si le statut n'est pas `Draft` (via
      `ArticleNotEditableException`), sont no-op si valeur identique
      (préserve `updatedAt` et donc l'ETag), refusent un `$now`
      antérieur à `updatedAt` (horloge monotone garantie).
- [x] `restore(now)` : `Archived → Draft` (perd `publishedAt` à
      dessein, un brouillon ne porte pas de date active) ; `Draft →`
      no-op ; `Published → InvalidArticleTransitionException`.
      L'édition d'un article publié doit passer par le chemin
      `archive() → restore() → Draft`, jamais directement.
- [x] `slug` reste immuable en Phase 8C ; aucune colonne `version` ;
      aucun historique de révisions.
- [x] Handlers Application : `UpdateDraftArticleHandler` (validation
      atomique — construit tous les VOs + valide Markdown avant
      d'appliquer la moindre mutation, un seul `flush`),
      `ArchiveArticleHandler`, `RestoreArticleHandler`. Contrat
      `Result` typé (`mutated`/`unchanged`, `archived`/`alreadyArchived`,
      `alreadyDraft`) pour piloter la couche présentation future.
- [x] `ArticleRepositoryInterface::findById(Uuid)` ajouté (port +
      `DoctrineArticleRepository` + `InMemoryArticleRepository` +
      tests). `findBySlug` reste la seule lecture neutre pour
      import/publish, `findById` la seule lecture neutre pour
      mutation admin. Aucune écriture n'est exposée publiquement.
- [x] Constante partagée `MarkdownContentValidator::MAX_BODY_BYTES =
      524_288` (512 Kio) — utilisée à la fois par le pipeline
      d'import CLI (`MarkdownArticleFileParser`) et par le handler
      admin, plus de valeur dupliquée entre les deux chemins.
- [x] Validation Markdown des mutations admin réutilise
      `MarkdownContentValidator` + `MarkdownSecurityPolicy` de la
      Phase 8B1 (rejet HTML brut, schémas d'URL dangereux, taille
      excessive, contenu vide, VOs invalides). L'agrégat reste seul
      responsable de sa cohérence — les VOs sont construits avant
      les mutations pour garantir l'atomicité.
- [x] `ArticleETag::CONTRACT_VERSION = 'v2'` et `ArticleListETag`
      `'v1'` inchangés — les mutations ne modifient pas la forme du
      payload public, donc pas d'invalidation en masse à propager.
- [x] Tests : PHPUnit +49 (Domain `Article` +23 : chaque mutation +
      no-op + rejet publié/archivé + horloge monotone + `restore` × 4 ;
      Application `Command` handlers +19 : `ArchiveArticleHandlerTest`
      ×4, `RestoreArticleHandlerTest` ×4, `UpdateDraftArticleHandlerTest`
      ×11 dont test explicite d'atomicité `testValidatesAllBeforeMutating`
      ; Infrastructure `DoctrineArticleRepositoryTest` +2 sur `findById`
      ; Markdown `MarkdownContentValidatorTest` +2 sur la nouvelle
      constante partagée). Suite Editorial complète après 8C2 : 154
      tests / 340 assertions.
- [x] Non-goals confirmés Phase 8C2 : aucune route HTTP admin, aucun
      contrôleur, aucun template Twig, aucun formulaire, aucun DTO
      d'entrée HTTP, aucune migration Doctrine, aucun changement de
      slug, aucun historique de révisions, aucun changement Nuxt /
      Caddy / frontend.

#### Phase 8C3 — IHM éditoriale et publication depuis l'IHM (TERMINÉE)

Sortie 2026-08-06. Cf. DEV-052 et changelog 2.22 (`docs/10-TRACKING.md`),
DEC-087 à DEC-091. ADR-012 (Phase 8C1) reste seule ADR admin : la
frontière n'a pas changé (IHM Twig SSR, aucun endpoint HTTP JSON admin,
aucun upload d'images).

- [x] Liste des articles éditoriaux dans `/admin/articles` (Twig SSR,
      pagination, filtre statut) — nouveau port CQRS
      `AdminArticleReadRepositoryInterface` distinct du port public
      (cf. DEC-088), tri stable `updated_at DESC, id DESC`.
- [x] Formulaire de création d'un article (brouillon) —
      `CreateDraftArticleHandler` avec pipeline atomique (validation
      complète VOs + Markdown, `findBySlug` pré-check, interception
      `UniqueConstraintViolationException` → `ArticleSlugAlreadyExistsException`,
      cf. DEC-091). Le `slug` est absent du formulaire d'édition
      (immuable, cf. DEC-091).
- [x] Formulaire d'édition d'un brouillon — appelle
      `UpdateDraftArticleHandler` (livré 8C2), `ArchiveArticleHandler`
      (livré 8C2), `RestoreArticleHandler` (livré 8C2).
- [x] Transition brouillon → publié via l'IHM :
      `PublishDraftArticleHandler` **distinct** de
      `PublishArticleBySlugHandler` (chemin CLI 8B1) — chargement par
      UUID, refus strict `Published`/`Archived` via
      `InvalidArticleTransitionException::cannotPublishFrom()`
      (cf. DEC-089).
- [x] Rate-limiting par UUID admin (jamais IP/session/email —
      cf. DEC-088) : `AdminActionRateLimiter` enveloppe deux
      `RateLimiterFactory` (`admin_write` 30/min, `admin_publish`
      10/min).
- [x] CSP admin durcie inchangée depuis 8C1 (`style-src 'self'`) grâce
      au correctif racine `apps/api/public/index.php` — le serveur PHP
      built-in `cli-server` court-circuite désormais le kernel sur les
      fichiers réels du dossier `public/` (cf. DEC-087). Le no-op est
      strict en prod (php-fpm derrière Caddy/nginx).
- [x] `Cache-Control: private, no-store, no-cache, must-revalidate`
      sur toutes les réponses `/admin/*` (extension
      `AdminSecurityHeadersSubscriber`).
- [x] Journalisation audit : canal Monolog `admin` avec 8 événements
      (`admin.article.created/.updated/.update_noop/.published/
      .archived/.restored/.action_failed/.rate_limited`) — seul
      identifiant admin loggé = UUID (jamais email, jamais titre ni
      Markdown).
- [x] Suite E2E Playwright dédiée
      `apps/web/test/e2e/admin-editorial.spec.ts` (5 scénarios en
      `describe.serial`, Axe WCAG 2.2 AA sur la liste et le
      formulaire), CI `.github/workflows/web-quality.yml` étendue,
      `scripts/e2e-admin-articles.sh` (purge `slug LIKE 'e2e-8c3-%'`).
- [x] Ajustement WCAG 2.2 §2.5.8 « Target Size (Minimum) » :
      `.button-small` gagne `min-height/-width: 24px` +
      `.row-actions gap 0.5rem` (cf. DEC-090).

**Hors périmètre confirmé Phase 8C3** :
upload d'images (report Phase 9), invalidation coordonnée du cache
Nitro sur publication (aucune purge locale explicite — le cache Nitro
respire par TTL, à traiter avec le futur `ArticleUpdatedEvent`),
historique persistant des transitions (non planifié — aucune ADR à ce
jour, à requalifier si un besoin de conformité le formalise).

#### Phase 8C4 — Prévisualisation éditoriale et recette finale de sécurité (TERMINÉE)

Livraison de la prévisualisation authentifiée d'un brouillon, avec le
même renderer Markdown que le public (aucun sanitizer parallèle,
aucune divergence de rendu), et recette finale avant ouverture prod.

- [x] Route admin d'aperçu `/admin/articles/{id}/preview` (GET-only,
      `#[IsGranted('ROLE_ADMIN')]`, rendu Twig SSR utilisant
      `CommonMarkArticleRenderer` — même pipeline que le contrat public,
      aucun sanitizer parallèle). Handler `GetAdminArticlePreviewHandler`
      + DTO `AdminArticlePreviewView` + route requirement
      `[0-9a-fA-F-]{36}` sur l'UUID. Aucune méthode d'écriture atteignable
      depuis le handler (vérifié par introspection réflexive du port
      `AdminArticleReadRepositoryInterface`).
- [x] Headers `X-Robots-Tag: noindex, nofollow`, `Cache-Control: private,
      no-store, no-cache, must-revalidate`, CSP `default-src 'none'`,
      `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`,
      `Referrer-Policy: no-referrer` : posés par
      `AdminSecurityHeadersSubscriber` (Phase 8C1), vérifiés par
      PHPUnit (`testSecurityHeadersAreAppliedToPreview`) et Playwright
      (« en-têtes de sécurité admin appliqués à la preview »).
- [x] Recette E2E complète du parcours éditorial
      (`apps/web/test/e2e/admin-preview.spec.ts`, 7 scénarios) : login →
      création draft via UI → preview → publication → vérification
      `/ressources/{slug}` public 200 → archivage → 404 public →
      restauration en draft → preview draft. Axe WCAG 2.2 AA + WCAG 2.1
      AAA sans violation.
- [x] Recette de sécurité admin formalisée dans
      `docs/checklists/ADMIN-SECURITY-REVIEW.md` : revue ciblée inspirée
      des contrôles OWASP ASVS applicables à la surface d'administration
      (11 sections : auth, access, session, CSRF, sanitisation, headers,
      robots, DB isolation, logs, non-mutation, fin de recette) avec
      références aux catégories OWASP Top 10 2021 correspondantes. **Pas
      une certification ASVS complète** : la grille officielle n'a pas été
      auditée dans son intégralité. Chaque item pointe soit vers un test
      automatique nommé, soit vers une commande shell reproductible.
- [x] Préparation production : la checklist ci-dessus fait office de
      liste d'ouverture prod. Le compte E2E est provisionné via
      `app:admin:create-user` (même commande que la prod). CSP,
      throttling (5/15min login), rate-limiting (30/min write, 10/min
      publish), cookies (`DZ_ADMIN_SESSID` HttpOnly SameSite=Lax,
      Secure=auto) sont vérifiés par Playwright sur la stack docker
      compose complète (Caddy + Nitro + Symfony + PostgreSQL).

**Différé au-delà de Phase 8C4** (aucune date, aucune ADR — à
requalifier si le besoin change) :

- MFA (TOTP) — à réévaluer si l'exposition ou le volume de comptes
  admin change.
- Table d'audit persistante — pour l'instant les 8 événements sont
  logués Monolog canal `admin` (UUID admin, jamais PII). Basculer
  vers une table dédiée si un référentiel conformité le formalise.
- Reset de mot de passe HTTP — reste CLI-only (`app:admin:reset-password`)
  tant que l'admin ne devient pas multi-utilisateur.
- Bascule stockage session vers Redis — reste filesystem tant que
  l'API n'est pas mise à l'échelle horizontalement.
- Multi-instance des sessions — même condition (scaling horizontal
  du conteneur `api`).
- Rôles multiples (éditeur, publicateur) — nécessite migration
  colonne `roles` et refonte `access_control`, non planifié.

### Critère de sortie (Phase 8 complète)

L’API retourne des contrats stables, validés, documentés et testés,
depuis la persistance jusqu'à l'administration protégée.

---

## Phase 9 — Média éditorial et intégration du blog dans Nuxt

Décomposée en deux jalons indépendants — **9A fondation média
éditoriale côté admin** (livré 2026-08-08), **9B rattachement des
médias à l'article + exposition publique + intégration Nuxt** (non
démarré). La 9A introduit uniquement la bibliothèque et le
téléversement authentifié ; **elle n'ajoute AUCUNE relation à
`Article`, AUCUNE route publique, AUCUN alt-text contextuel** — ces
préoccupations sont volontairement isolées en 9B pour que le socle
image (pipeline sécurisé, stockage privé, audit) puisse vivre et
mûrir sans coupler le contrat public.

### Phase 9A — Fondation média éditoriale (TERMINÉE)

Livrée le 2026-08-08 par DEV-055. Bibliothèque admin, téléversement
authentifié avec normalisation image + strip EXIF, stockage privé
hors webroot, prévisualisation authentifiée. Aucun rattachement à
`Article`, aucune route publique.

- [x] Domaine `EditorialMedia` : agrégat `MediaAsset` (UUID v7,
      créé via factory `fromNormalized()` — jamais construit à la
      main côté application), VO `StorageKey` (format canonique
      `{uuid}/original.{jpg|png|webp}` généré côté serveur —
      **jamais dérivé du nom d'origine** du fichier client),
      `Sha256` (64 hex, calculé sur le fichier NORMALISÉ),
      `MediaDimensions` (width/height positifs), enum `MediaType`
      (JPEG/PNG/WEBP, extensions et MIME depuis l'enum), exceptions
      `EditorialMediaInvariantViolation` et
      `MediaAssetNotFoundException`, port
      `MediaAssetRepositoryInterface`. Aucun champ `alt`, aucune
      FK vers `editorial_article`.
- [x] Application : commande `UploadEditorialMedia` (source path +
      original filename) + handler `UploadEditorialMediaHandler`
      qui orchestre le pipeline (détection MIME serveur → policy
      → normalisation → stockage → persistence Doctrine, avec
      compensation `MediaStorageInterface::delete()` si le flush
      échoue). `MediaUploadPolicy` : `MAX_SIZE_BYTES = 8 * 1024 * 1024`
      (8 MiB), `MAX_WIDTH = MAX_HEIGHT = 8000`,
      `MAX_TOTAL_PIXELS = 40_000_000` (garde-fou pixel-flood
      indépendant des dimensions). Cinq exceptions applicatives
      typées (`MediaTooLargeException`,
      `UnsupportedMediaTypeException`, `InvalidImageException`,
      `ImageDimensionsExceededException`, `MediaStorageException`).
      Query `ListAdminMedia(Handler)` + DTO plats
      `AdminMediaListItem` / `AdminMediaListPage` avec
      `PaginationView` réutilisée d'`Editorial`.
- [x] Infrastructure : `GdImageProcessor` — décode via
      `imagecreatefrom{jpeg,png,webp}`, ré-encode via
      `imagejpeg/png/webp` : **le ré-encodage GD supprime
      systématiquement les métadonnées EXIF / IPTC / GPS**, jamais
      d'appel `exif_read_data`. `LocalMediaStorage` — racine
      pilotée par `EDITORIAL_MEDIA_STORAGE_DIR` (défaut
      `/app/var/editorial-media`, **hors webroot** derrière Caddy),
      dossiers 0750, fichiers 0640, défense en profondeur
      path-traversal via `realpath` (rejette toute clé qui
      s'échapperait de la racine). `FinfoMimeTypeDetector` (jamais
      `mime_content_type`, jamais l'en-tête HTTP client).
      `DoctrineMediaAssetRepository` (implémente le port : `save`
      sans flush, `findById`, `listPaginated`, `count`).
      `AdminMediaUploadRateLimiter` — enveloppe `RateLimiterFactory`
      `admin_media_upload` (token_bucket, **20 uploads / 10 minutes
      par UUID admin**, jamais IP), pilotable via env
      `ADMIN_MEDIA_UPLOAD_LIMIT` et `ADMIN_MEDIA_UPLOAD_INTERVAL`.
      `MediaAdminAuditLogger` — canal Monolog `admin` (partagé
      avec 8C4), 4 événements : `admin.media.uploaded`,
      `.upload_failed`, `.upload_rate_limited`, `.previewed`.
      **Seul identifiant admin loggé = UUID** ; jamais email,
      jamais nom de fichier arbitraire dans les logs de succès.
- [x] Presentation `Admin/Presentation/Http/` (3 contrôleurs,
      tous `#[IsGranted('ROLE_ADMIN')]`) :
      `AdminMediaListController` (GET `/admin/media`, pagination),
      `AdminMediaUploadController` (GET + POST `/admin/media/new`,
      PRG vers `/admin/media` avec flash succès contenant
      `original_filename`, formulaire multipart, CSRF token
      `media_upload`, rate-limit → 429 + template
      `rate_limited.html.twig` + `Retry-After`),
      `AdminMediaPreviewController` (GET-only
      `/admin/media/{id}/preview` avec route requirement
      `[0-9a-fA-F-]{36}`, `StreamedResponse` streamé depuis
      `MediaStorageInterface::openStream()`,
      `Content-Type` **fixé par l'enum `MediaType`** — jamais
      renvoyé côté client, `Content-Disposition: inline`, headers
      admin `X-Robots-Tag`/`X-Frame-Options`/CSP hérités du
      subscriber Phase 8C1, `Cache-Control: private, no-store`).
- [x] Templates `apps/api/templates/admin/media/` (3 fichiers) :
      `list.html.twig` (table `.admin-table` : miniature via lien
      preview, `original_filename`, `mime_type`, dimensions,
      poids ; état vide ; pagination), `new.html.twig` (formulaire
      multipart avec `<label for="media-file">` explicite, notice
      « JPEG, PNG, WebP — 8 MiB max, 8000×8000 px max »),
      `rate_limited.html.twig`.
- [x] Migration `Version20260808120000` : table
      `editorial_media_asset` (`id` UUID PK, `storage_key`
      VARCHAR(255) UNIQUE, `original_filename` VARCHAR(255),
      `mime_type` VARCHAR(64), `size_bytes` BIGINT,
      `width`/`height` INT, `sha256` VARCHAR(64),
      `created_at` TIMESTAMPTZ). Un seul index chronologique
      `(created_at DESC, id DESC)` pour la pagination stable.
      **Aucune FK vers `editorial_article`**, aucun statut de
      publication, aucun champ `alt` — tout cela est reporté en 9B.
- [x] Configuration : `apps/api/docker/php/editorial-media.ini`
      (couvre 8 MiB `upload_max_filesize=12M`, `post_max_size=16M`,
      `memory_limit=256M`, `max_file_uploads=1`) chargé
      exclusivement par l'image API Devzair via `Dockerfile.dev`.
      `apps/api/config/services.yaml` étendu (câblage complet des
      alias ports → adaptateurs Doctrine/GD/Finfo/LocalStorage +
      wiring `AdminMediaUploadRateLimiter` sur la factory).
      `apps/api/config/packages/doctrine.yaml` étend le mapping
      Doctrine (namespace additionnel `App\EditorialMedia\Domain`).
      `apps/api/config/packages/framework.yaml` déclare le limiteur
      `admin_media_upload` (token_bucket, pilotable via env).
      `apps/api/config/routes/admin.yaml` déclare les 3 routes
      `admin_media_list`/`admin_media_upload`/`admin_media_preview`.
      `apps/api/templates/admin/_layout.html.twig` gagne l'entrée
      de navigation « Médias ».
      `apps/api/public/admin/assets/admin.css` étend les styles
      pour la table médias, le bloc miniature, le formulaire
      upload et l'état vide.
- [x] Tests : PHPUnit +96 (Domain `MediaAssetTest` ×6,
      `StorageKeyTest` ×4, `Sha256Test` ×3, `MediaDimensionsTest`
      ×2, `MediaTypeTest` ×5 ; Application
      `MediaUploadPolicyTest` ×9, `UploadEditorialMediaHandlerTest`
      ×10 dont **compensation storage** sur flush failure,
      `ListAdminMediaHandlerTest` ×4 ; Infrastructure
      `GdImageProcessorTest` ×7 dont **strip EXIF/GPS vérifié sur
      un JPEG source contenant `exif_read_data` positive**,
      `FinfoMimeTypeDetectorTest` ×6, `LocalMediaStorageTest` ×6
      dont défense path-traversal, `DoctrineMediaAssetRepositoryTest`
      ×6 avec rollback, `MediaAdminAuditLoggerTest` ×4 ;
      Presentation `AdminMediaListControllerTest` ×6,
      `AdminMediaUploadControllerTest` ×9 dont 429 + `Retry-After`,
      `AdminMediaPreviewControllerTest` ×9 dont **`Content-Type`
      fixé serveur et non côté client**, headers de sécurité,
      405 sur POST). Support tests `EditorialMediaDatabaseCleanup`,
      `FakeImageProcessor`, `FakeMediaStorage`,
      `FakeMimeTypeDetector`, `InMemoryMediaAssetRepository`,
      `MediaAssetBuilder`.
- [x] E2E Playwright `apps/web/test/e2e/admin-media.spec.ts` :
      `test.describe.serial` (throttling firewall + rate-limiter),
      6 scénarios — (1) anonyme → 302 `/admin/login`, (2)
      bibliothèque accessible Axe WCAG 2.2 AA, (3) formulaire
      d'upload accessible Axe WCAG 2.2 AA, (4) cycle complet
      upload PNG → PRG → média visible → preview privée
      (`content-type: image/png`, `content-disposition: inline`,
      `cache-control` contient `private` + `no-store`,
      `x-robots-tag: noindex, nofollow`, `x-frame-options: DENY`,
      magic bytes PNG), (5) preview anonyme → 302 `/admin/login`,
      (6) en-têtes de sécurité admin sur `/admin/media` et
      `/admin/media/new`. Préfixe strict `e2e-9a-`.
- [x] Orchestration `scripts/e2e-admin-media.sh` : constante
      `readonly E2E_FILENAME_PREFIX='e2e-9a-'` codée en dur
      (garde-fou n°1), `DELETE FROM editorial_media_asset WHERE
      original_filename LIKE '${E2E_FILENAME_PREFIX}%'` (garde-fou
      n°2 — jamais TRUNCATE), suppression fichier-par-fichier via
      `rm -f --` sur chaque `storage_key` retourné par le SELECT
      (garde-fou n°3 — jamais `rm -rf` racine) avec défense en
      profondeur contre `..`, `/*`, `-*`, `ON_ERROR_STOP=1`.
      Symétrique aux scripts 8B2/8C1/8C3/8C4 avec la spécificité
      **filesystem cleanup en plus** (les articles n'ont pas de
      fichier binaire, les médias oui).
- [x] CI `.github/workflows/web-quality.yml` : path triggers
      étendus (`scripts/e2e-admin-media.sh`), étape
      `./scripts/e2e-admin-media.sh load` avant Playwright, étape
      `./scripts/e2e-admin-media.sh clear` avec `if: always()` en
      fin — symétrique aux fixtures 8B2/8C1/8C3/8C4.

**Hors périmètre confirmé Phase 9A** : aucune colonne
`hero_image_id`/`cover_image_id`/`alt` sur `editorial_article`
(Phase 9B), aucun rattachement d'un média à un article, aucun
alt-text contextuel (le VO `MediaAsset` porte l'identité serveur,
pas le contexte éditorial), aucune route publique
(`/api/media/{id}` ou variante — le stockage reste **strictement
privé** derrière le firewall admin), aucun rendu Nuxt côté
`/ressources/**`, aucune indexation dans le sitemap public, aucun
WYSIWYG ni éditeur d'image, aucun redimensionnement `srcset`
(l'original normalisé est le seul rendu, les variantes seront
introduites en 9B si le contrat public en a besoin), aucune
suppression de média (report Phase 9B — supprimer un média
référencé par un article devra propager une invalidation),
aucune modification du contrat public éditorial existant (list /
detail views intactes, ETag `v2` inchangé), aucun changement
Nuxt (`apps/web/app/**`), aucun changement Caddy.

### Phase 9B — Rattachement à l'article et intégration Nuxt (NON DÉMARRÉE)

- [ ] Ajouter la relation `MediaAsset ↔ Article` (colonnes
      `hero_image_id`/`content_image_ids`, alt-text contextuel
      porté par la relation, pas par l'agrégat média).
- [ ] Exposer les médias associés dans le contrat public
      (`ArticleDetailView.heroImage`/`.contentImages`,
      **bumper `ArticleETag::CONTRACT_VERSION` de `v2` à `v3`**).
- [ ] Décider et livrer la stratégie d'exposition publique du
      binaire (proxy signé, CDN, ou route publique dédiée avec
      cache long — arbitrage sécurité vs performance).
- [ ] Créer les types partagés frontend.
- [ ] Créer `ArticleRepository` côté Nuxt.
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
- [ ] Traiter la suppression d'un média référencé par un article
      (soft-delete, replace-then-delete, ou refus).

### Critère de sortie (Phase 9 complète)

Un article publié dans Symfony est visible sous une URL Nuxt canonique avec HTML, métadonnées et données structurées corrects — image d'entête incluse, servie depuis un canal explicitement décidé (privé signé ou public caché), avec un contrat versionnable.

---

## Phase 10 — Contenu éditorial et SEO section Ressources

### Phase 10A — Contenu éditorial pillar et maillage interne (TERMINÉE — 2026-08-09)

- [x] Rédiger 6 articles evergreen (900–1600 mots) pour `/ressources` sans invention de client, chiffre, témoignage, certification ou garantie SEO.
- [x] Positionner Devzair comme agence digitale à taille humaine, jamais comme freelance.
- [x] Importer via `app:editorial:import` (dry-run puis réel, create-only).
- [x] Publier via `app:editorial:publish` avec dates réelles séquentielles (2026-08-04 à 2026-08-09).
- [x] Maillage interne 2–5 liens par article vers `/expertises/{slug}`, `/agence`, autres `/ressources/*`, `/contact`.
- [x] Vérifier SEO SSR : title, description, canonical, OG, Twitter, JSON-LD BlogPosting + BreadcrumbList, sitemap.
- [x] Créer `scripts/editorial-content-bootstrap.sh` idempotent (skip si slug existe, re-publication idempotente sur dates identiques).
- [x] Ajouter `apps/web/test/e2e/resources-phase10.spec.ts` (listing paginé, sitemap, SSR SEO, JSON-LD, corps Markdown, Axe /ressources + article).
- [x] Corriger `apps/web/test/e2e/resources.spec.ts` pour agnosticité de position — assertions par pattern `e2e-8b2-*` sur tous les slugs plutôt que sur la page 1.

### Critère de sortie 10A

Les 6 articles pillar sont publiés, indexables, cohérents avec l'architecture existante, et leur bootstrap est reproductible en dev comme en CI.

### Phase 10A2 — Maillage éditorial et découverte des ressources

État actuel : EN COURS.

Objectifs :

- [ ] Ajouter le filtrage des ressources par expertise.
- [ ] Relier les pages expertise aux ressources Published pertinentes.
- [ ] Relier les articles à leurs pages expertise publiques.
- [ ] Ajouter Ressources à la navigation publique lorsque pertinent.
- [ ] Préserver pagination, ETags et cache HTTP avec les filtres.
- [ ] Garantir le SSR du maillage.
- [ ] Éviter l’indexation des variantes filtrées.
- [ ] Vérifier responsive et WCAG 2.2 AA.
- [ ] Ajouter les tests backend, frontend et E2E concernés.

Critère de sortie :

Les ressources et les expertises forment un maillage public cohérent,
explorable et maintenable, sans duplication SEO ni contenu éditorial
obsolète lié à une stratégie de rendu inadéquate.

### Phase 10B — SEO local et GEO avancé (TERMINÉE — 2026-08-10)

- [x] Politique crawlers IA robots.txt — six groupes explicites
      (DEC-096 + DEC-101). `OAI-SearchBot` Allow, `GPTBot` Disallow,
      `Claude-SearchBot` Allow, `ClaudeBot` Disallow, `PerplexityBot`
      Allow, `Google-Extended` Disallow. Fetchers user-triggered
      volontairement absents (robots.txt ne s'applique pas). CCBot
      volontairement `UNCHANGED`.
- [x] Éligibilité locale auditée — `Local eligibility = UNDETERMINED`,
      `LocalBusiness = NOT EMITTED` (DEC-097 + DEC-100). Aucune donnée
      publique vérifiée, aucune émission `LocalBusiness`, aucune
      adresse/téléphone/zone/horaire, aucune page ville, aucun `sameAs`
      inventé. Représentation locale différée jusqu'à validation métier
      explicite.
- [x] Google-Extended explicitement refusé (DEC-101) — la directive
      contrôle l'entraînement Gemini et le grounding Gemini Apps /
      Vertex AI ; Google précise qu'elle n'affecte ni l'inclusion ni le
      classement Google Search. Google Search / AI Overviews / AI Mode
      restent crawlables via Googlebot.
- [x] Graphe des entités vérifié — `Organization` + `WebSite` en
      `@graph` avec `@id` stables, `Service.provider` et
      `BlogPosting.publisher` référencent l'`@id` Organization, aucun
      champ fictif (`address`, `aggregateRating`, `sameAs`).
- [x] Sources et dates éditoriales — quatre articles enrichis avec
      références primaires (Google, web.dev, W3C, MDN, CISA). Cycle de
      vie `publishedAt` verrouillé côté domaine (DEC-099).
- [x] Query set GEO stable — `docs/geo/GEO-QUERY-SET.md` (12 requêtes
      marque + expertises + problèmes prospects, aucune requête
      géographique tant que l'éligibilité locale reste UNDETERMINED).
- [x] Plan de mesure GEO documenté — `docs/geo/GEO-MEASUREMENT.md`.
      `AI referral implementation : DEFERRED`, `measurement plan :
      DOCUMENTED`. Aucun tracker ajouté. Visibilité Google Search AI
      mesurable indépendamment de `Google-Extended` (DEC-101).
- [x] `llms.txt` étudié et non retenu (DEC-098) — `STUDIED`,
      `NOT IMPLEMENTED`. Réévaluable si un besoin non couvert par
      `robots.txt`, `sitemap.xml`, HTML SSR et Schema.org émerge.
- [x] WAF et CDN reportés — `NOT CONFIGURED` en dev/preprod, à
      configurer en Phase production (hors périmètre 10B).

### Critère de sortie 10B (atteint)

L'identité, les contenus et la politique des robots sont cohérents et
vérifiables. L'absence volontaire de `LocalBusiness` n'est pas un
échec du critère : elle reflète l'état des données validées.
`Google-Extended` est arbitré (DEC-101). Les profils officiels et
l'harmonisation des informations d'entreprise restent explicitement
conditionnés à une future validation métier des données locales — hors
périmètre de clôture de Phase 10B.

---

## Phase 11 — Sécurité, performance et accessibilité

Phase 11A (audit passif) terminée le 2026-08-12. Phase 11B (sécurité
applicative + supply-chain + durcissement HTTP) terminée le 2026-08-13
sur DEV-061 / DEC-102. Phase 11C (accessibilité, performance, images)
reste à ouvrir.

- [ ] Audit OWASP (revue continue en Phase 12).
- [ ] Revue des secrets (revue finale Phase 12).
- [x] Revue des dépendances (nanoid H-01 patché, esbuild L-01 non-applicable Linux, bulletin Nuxt juillet 2026 patché en 4.5.1).
- [x] CSP (Report-Only publique via Caddy, enforced stricte admin via subscriber Symfony — DEC-102).
- [x] En-têtes (nosniff, Referrer-Policy, Permissions-Policy, X-Frame-Options, suppression X-Powered-By / Server côté Caddy et subscriber admin).
- [x] Permissions (`camera=(), microphone=(), geolocation=(), interest-cohort=(), browsing-topics=()` public et admin).
- [x] Analyse des formulaires (audit Phase 11A + rate limits `contact_ip`, `admin_write`, `admin_publish`, `admin_media_upload` déjà en place).
- [x] Contrôle des contenus HTML (validator import + renderer strip + tests régression XSS étendus Phase 11B).
- [ ] Audit WCAG 2.2 AA (audit dynamique — Phase 11C).
- [ ] Navigation clavier (audit dynamique — Phase 11C).
- [ ] Lecteur d’écran (audit dynamique — Phase 11C).
- [ ] Mesures LCP, INP et CLS (Phase 11C avec build production HTTPS Phase 12).
- [ ] Budget JavaScript (Phase 11C).
- [ ] Optimisation des images (M-03 logos + variantes responsives — Phase 11C).
- [ ] Test de charge raisonnable (Phase 11A a exécuté un run `ab` initial ; charge cible réaliste à définir Phase 12).
- [ ] Corrections (au fil des phases 11C et 12).

### Critère de sortie

Aucun défaut critique connu et aucune barrière majeure sur les parcours principaux.

---

## Phase 12 — Préproduction et production

### Infrastructure Docker (terminée localement — 2026-08-28)

- [x] `apps/web/Dockerfile.prod` — build Nuxt multi-stage, Nitro non-root.
- [x] Variables SEO injectées au BUILD (`NUXT_PUBLIC_SITE_URL`, `NUXT_PUBLIC_SITE_INDEXABLE`, `NUXT_PUBLIC_API_BASE_URL`) avec contrôle bloquant du HTML pré-rendu.
- [x] Variables Turnstile injectées au BUILD (`NUXT_PUBLIC_TURNSTILE_SITE_KEY`, `NUXT_PUBLIC_TURNSTILE_ENABLED`) — /contact est pré-rendue, les valeurs runtime seules ne suffisent pas. Contrôle bloquant ajouté sur `/contact/index.html`.
- [x] HEALTHCHECK Nuxt corrigé : `localhost` → `127.0.0.1` (évite la résolution IPv6 `::1`), route `/_nitro/health` (404) → `/` (200).
- [x] `apps/api/Dockerfile.prod` — FrankenPHP PHP 8.4, non-root, opcache prod.
- [x] `APP_RUNTIME_OPTIONS={"disable_dotenv":true}` — corrige `PathException` (Symfony Runtime cherchait `/app/.env` absent en production). `cache:warmup` déplacé dans l'entrypoint avec les vraies vars Docker.
- [x] `apps/api/docker/frankenphp/Caddyfile` — écoute :8000, trusted_proxies.
- [x] `apps/api/docker/php/opcache.prod.ini` — validate_timestamps=0, JIT.
- [x] `compose.prod.yaml` — sans Caddy, réseaux `devzair_internal` + `web` externe.
- [x] `.env.prod.example` — template complet des variables de production.
- [ ] Réseau Docker `web` vérifié sur le VPS (`docker network ls`).
- [ ] Images buildées et testées sur le VPS.
- [ ] Migrations Doctrine exécutées (`doctrine:migrations:migrate`).
- [ ] Compte admin créé (`app:admin:create-user`).
- [ ] Caddy global configuré pour `devzair.fr` (TLS auto, routing /api/* /admin/*).

### Recette VPS (non démarrée)

- [ ] Préproduction protégée.
- [ ] Recette fonctionnelle.
- [ ] Recette éditoriale.
- [ ] Recette SEO.
- [ ] Recette sécurité (OWASP, revue secrets).
- [ ] Recette confidentialité.
- [ ] Recette analytics.
- [ ] Sauvegarde et restauration testées.
- [ ] Plan de rollback validé.
- [ ] TLS Let's Encrypt actif.
- [ ] Redirections de domaine.
- [ ] Smoke tests automatisés.
- [ ] Soumission Search Console.
- [ ] Soumission Bing Webmaster Tools.
- [ ] Monitoring et alertes.
- [ ] Journal de version.

### Critère de sortie

Verdict explicite `GO`, `GO CONDITIONNEL` ou `NO-GO` après validation VPS complète.

**La Phase 12 n'est pas terminée** tant que le déploiement VPS, TLS, migrations,
smoke tests, sauvegarde et rollback n'ont pas été réellement vérifiés.

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

## Programme Estimateur — Module `/estimer-mon-projet`

Source de vérité détaillée : `docs/12-PROJECT-ESTIMATOR.md`.

Ce programme est un **sous-projet indépendant** dont le développement peut être conduit en parallèle des phases globales restantes (9B, 10A2, 11C, 12).
Sa **mise en production** reste soumise aux gates habituelles : sécurité, privacy, QA, recette et GO explicite.
Elle ne présuppose pas que Phase 12 soit terminée, mais une recette de déploiement coordonnée est requise lors de EST-9.
Il n'interfère pas avec les phases 1–13 et ne les renumérote pas.

### EST-0 — Cadrage et documentation

**État actuel : TERMINÉE**

- [x] Créer `docs/12-PROJECT-ESTIMATOR.md` (source de vérité fonctionnelle, 24 sections).
- [x] Documenter les principes validés, le parcours, les branches conditionnelles, l'écran résultat et l'architecture cible.
- [x] Mettre à jour `docs/08-ROADMAP.md`.
- [x] Mettre à jour `docs/10-TRACKING.md` (DEV-064 à DEV-073).
- [x] Identifier et classer toutes les questions ouvertes (Q-01 à Q-14) par phase bloquante.

**Critère de sortie :** La source de vérité est complète, tous les montants inconnus sont marqués À VALIDER, les questions ouvertes sont classées par phase bloquante, aucun code applicatif n'a été écrit.

**Statut : atteint.**

---

### EST-1 — Domaine + moteur d'estimation (Symfony)

**État actuel : TERMINÉE** (EST-1A, EST-1B et EST-1C terminées)

EST-1 est décomposé en trois sous-jalons indépendants. EST-1A ne nécessite aucune grille tarifaire réelle.

#### EST-1A — Contrat métier — **TERMINÉE** (2026-08-29)

- [x] DTO `ProjectEstimateInput` (types, enums `ProjectType`, value objects, validation métier).
- [x] DTO `EstimateResult` (fourchette, lignes détaillées, `pricingVersion`, hypothèses).
- [x] Invariants métier (cohérence input/output, règles de composition sans montant).
- [x] Fixtures tarifaires explicitement marquées **TEST** — aucune valeur Devzair réelle.
- [x] PHPUnit domaine (93 tests, 137 assertions — suite complète 706/706).

**Dépendances :** EST-0 terminé.
**Critère de sortie :** Le contrat métier est défini, typé, testé avec fixtures TEST. Aucune grille Devzair réelle. EST-1A peut démarrer sans Q-01.

#### EST-1B — Calibration + moteur — **TERMINÉE** (2026-08-30)

- [x] `ProjectEstimationEngine` (pur, déterministe, sans I/O).
- [x] `PricingCatalogInterface` + `DevzairPricingCatalogV1` (grille réelle `2026-v1`).
- [x] Grille Devzair V1 (Q-01 validée) — socles, scale, features, contenus, visibilité, récurrent.
- [x] Politique d'arrondi conservateur (50 €) — MIN vers le bas, MAX vers le haut.
- [x] Anti-doublon récurrent (un seul tier de maintenance, SEO continu orthogonal).
- [x] PHPUnit moteur : 208 tests, 376 assertions. Suite globale 821/821.

**Dépendances :** EST-1A, Q-01 validée par l'équipe Devzair.
**Critère de sortie :** Le moteur calcule des fourchettes MIN/MAX réelles pour chaque type de projet.

#### EST-1C — API HTTP — **TERMINÉE** (2026-08-30)

- [x] Contrôleur `POST /api/estimate` (`EstimateController`, pipeline 9 étapes).
- [x] DTO `EstimateRequest` + `EstimateRequestMapper` (sans PII, validation Symfony Validator).
- [x] `EstimateResponseFactory` (`outcome`, amounts en minor units int, `period: month`, codes snake_case).
- [x] Validation HTTP de l'input (payload invalide → 400 `validation_error`).
- [x] Origin allowlist stricte (réutilisation `OriginAllowlist`, même env `CONTACT_ORIGIN_ALLOWLIST`).
- [x] Rate limiting (token bucket `estimate_ip` dédié — 30/min par défaut, `InMemoryStorage` en test).
- [x] Mapping d'erreurs (400, 413, 429 + `Retry-After`), `X-Request-Id` UUID v7, `Cache-Control: no-store`.
- [x] Canal Monolog `estimator` dédié — aucun PII ni payload loggués.
- [x] PHPUnit : `EstimateRequestMapperTest` (11 cas), `EstimateResponseFactoryTest` (9 cas), `EstimateControllerTest` (21 cas WebTestCase).
- [x] `ESTIMATE_RATE_LIMIT` / `ESTIMATE_RATE_INTERVAL` dans `.env.example` et `.env.test`.

**Dépendances :** EST-1B.
**Critère de sortie :** `POST /api/estimate` retourne un `EstimateResult` valide et versionné pour chaque type de projet, avec les garanties de sécurité HTTP.

---

### EST-2 — Shell UX du configurateur (Nuxt)

**État actuel : Implémentation technique livrée — validation visuelle requise** (2026-08-30)

- [x] Page `/estimer-mon-projet` (`usePageSeo`, SSR, `data-hydrated`).
- [x] Composable `useEstimator` (état in-memory, `maxStep`, `canGoNext`, `goNext`/`goPrev`, `setProjectType`).
- [x] Config `estimator-project-types.ts` (6 codes alignés sur l'enum Symfony `ProjectType`).
- [x] `EstimatorHero.vue` — intro compacte visible en viewport sans scroll.
- [x] `EstimatorShell.vue` — orchestrateur avec `maxStep=1` (EST-2).
- [x] `EstimatorProgress.vue` — `role=progressbar`, `aria-valuenow/min/max`.
- [x] `EstimatorProjectTypeStep.vue` — `<fieldset>/<legend>`, 6 radios natifs, cartes accessibles WCAG 2.2.
- [x] `EstimatorNavigation.vue` — Retour / Continuer, Continuer désactivé sans sélection.
- [x] Vitest : 5 fichiers spec (config, composable, Progress, ProjectTypeStep, Navigation).
- [x] Playwright + Axe : `estimator-shell.spec.ts` (10 scénarios WCAG 2.2 AA).

**Critère de sortie :** Shell navigable, accessible, responsive. Aucun questionnaire ni calcul fonctionnel requis.

**Dépendances :** EST-1 (contrat API).

---

### EST-3 — Questionnaires conditionnels

**État actuel : TERMINÉE**

- [x] `useEstimator` étendu : état complet (projectType, objectives, currentSituation, scale,
  features, contentNeeds, visibilityNeeds, careNeeds), `path` / `totalSteps` calculés, `canGoNext`
  par étape, `isComplete`, `toEstimatePayload()` pur sans appel API.
- [x] Chemin standard (7 étapes) : SituationStep → ScopeStep → FeaturesStep → ContentStep →
  VisibilityStep → CareStep.
- [x] Chemin unknown / objectif-first (6 étapes) : ObjectivesStep → SituationStep → ContentStep →
  VisibilityStep → CareStep.
- [x] Composant `EstimatorCheckboxCard.vue` (réutilisable, multi-select, WCAG AA).
- [x] 7 composants de step créés, focusables au changement d'étape (`tabindex="-1"` sur
  `legend`/`h2`, focus via `nextTick` dans la Shell).
- [x] `EstimatorShell.vue` orchestre les deux chemins, passe `totalSteps` dynamique à la barre de
  progression, affiche « Terminer → » à la dernière étape.
- [x] Nettoyage conditionnel : changement de `projectType` efface `objectives`, `scale`, `features`
  (mais préserve `currentSituation`, `contentNeeds`, `visibilityNeeds`, `careNeeds`).
- [x] Configs ajoutées : `estimator-content-options.ts`, `estimator-visibility-options.ts`,
  `estimator-care-options.ts`.
- [x] 64 tests unitaires Vitest (useEstimator ×50, SituationStep, ObjectivesStep, ScopeStep,
  FeaturesStep, ContentStep, VisibilityStep, CareStep, estimator-features config) + suite E2E
  `estimator-questionnaire.spec.ts`. Total suite verte : 777 tests.
- [x] Lint, typecheck, build production verts.

**Écarts documentés (backend / docs §12 ↔ implémentation) :**
- Horizon de projet (§6 étape 02) : non collecté — aucun champ backend.
- `CurrentSituation` : 5 options UX (doc propose 3) pour couvrir les 5 codes backend.
- ContentNeed « prêt » : code vide dans le tableau (pas de code "complete" côté Symfony).
- Champ libre « Autre » objectif : différé à EST-6.
- Unknown path Q2 (B2B/B2C) : non collecté — aucun champ backend.
- CareNeed autonomie : tableau vide = autonomie (pas de code "autonomy" côté Symfony).

**Critère de sortie :** atteint (lint, typecheck, 777 tests unitaires verts, build OK).

**Dépendances :** EST-2.

---

### EST-3.1 — Récapitulatif dynamique + Besoin complémentaire

**État actuel : TERMINÉE** (2026-08-30)

- [x] `useEstimator` étendu : `additionalFeatureNote` (string, `""`), `careAnswered` (bool), `maxVisitedStep` (int),
  `goToStep(n)`, `setAdditionalFeatureNote()`. Nettoyage conditionnel : `additionalFeatureNote` effacé si bascule
  vers chemin unknown ; `careAnswered` et `maxVisitedStep` réinitialisés si `projectType` change.
- [x] `EstimatorFeaturesStep.vue` étendu : textarea « Une autre fonctionnalité à prévoir ? »,
  counter X / 400 (`aria-live="polite"`), disclaimer non contractuel, prop `additionalFeatureNote`
  + émission `update:additionalFeatureNote`. La note n'est pas transmise à `toEstimatePayload()`.
- [x] `EstimatorProjectSummary.vue` créé : récapitulatif des réponses par section, contrôle
  « Modifier » (émet `go-to-step`), mobile accordion CSS (`summary__body--expanded`), desktop
  sticky colonne gauche 300 px (≥ 1024 px). Autonomie distinguée via `careAnswered`. Labels
  humains pour content needs via table interne. Note complémentaire tronquée à 72 chars.
  Contraste WCAG AA : `color-mix(in srgb, var(--color-ink) 68%, var(--color-cream-elevated))`.
- [x] `EstimatorShell.vue` restructuré : grille 2 colonnes (≥ 1024 px), summary sticky gauche,
  step + navigation droite. Props `additionalFeatureNote` / `careAnswered` / `maxVisitedStep` /
  `goToStep` passés aux composants.
- [x] 51 tests unitaires ajoutés (useEstimator ×40, FeaturesStep ×6, ProjectSummary ×35).
  Total suite : **828 tests verts**.
- [x] 5 tests E2E `estimator-summary.spec.ts` : scénario A enrichissement, B Modifier, C payload
  isolation, D textarea/counter, E note dans récapitulatif. **55 tests E2E verts**.
- [x] Lint, typecheck, build production verts.

**Critère de sortie :** atteint.

**Dépendances :** EST-3.

---

### EST-4 — Calcul + écran résultat

**État actuel : Implémentation technique terminée — validation visuelle requise (2026-08-30)**

- [x] Connexion questionnaire → API Symfony (`useEstimatorApi`, `POST /api/estimate`).
- [x] Composant `EstimatorResult.vue` — branches `estimated` et `human_scoping_required`.
- [x] Fourchette MIN / MAX, investissement initial (one_off_items), accompagnement récurrent (recurring_items + period).
- [x] Assumptions éditoriales, besoin complémentaire non chiffré.
- [x] `estimate-labels.ts` : mapping complet V1 (line items, récurrents, assumptions, types de projet).
- [x] `format-money.ts` : `formatMoneyMinor` + `formatMoneyRangeMinor` (Intl.NumberFormat fr-FR, centimes→€).
- [x] Loading state, double-submit protégé, erreurs 400/403/413/429/500/réseau.
- [x] Invalidation résultat sur "Modifier mes réponses".
- [x] `additionalFeatureNote` jamais dans le payload POST.
- [x] Fourchette fallback `human_scoping_required` jamais affichée.
- [x] 929 tests verts. Lint, typecheck, build OK.
- [ ] Validation visuelle humaine de l'écran résultat (requise avant EST-5).

**Critère de sortie :** Un parcours complet aboutit à un résultat affiché, cohérent, accessible et non contractuel.

**Dépendances :** EST-3, EST-1.

---

### EST-5 — Modalités de paiement / récurrent

**État actuel : Implémentation technique terminée — validation visuelle requise (2026-08-30)**

- [x] `estimator-payment-options.ts` — `getMaxInstallments()` pure, limites en centimes (< 100 000 → 2, 100 000–250 000 → 3, > 250 000 → 4).
- [x] `EstimatorPaymentTerms.vue` — affiche "Jusqu'à N échéances possibles", jamais de montant par échéance, jamais "/ mois", jamais "crédit"/"financement".
- [x] `EstimatorResult.vue` — Q-05 validé ("ne constitue pas un devis"), `EstimatorPaymentTerms` inséré après one_off_items, absent de la branche `human_scoping_required`.
- [x] Tests unitaires : 12 tests `estimator-payment-options.spec.ts`, 9 tests `EstimatorPaymentTerms.spec.ts`, 7 tests nouveaux dans `EstimatorResult.spec.ts`.
- [x] Tests E2E : 6 nouveaux cas A–E + anti-confusion dans `estimator-result.spec.ts`.
- [x] Q-03 validée — règles d'échelonnement (2/3/4 selon maximum). Q-11 validée — nombre de mensualités aligné sur Q-03. Q-05 validée — texte "ne constitue pas un devis" actif.
- [x] 956 tests verts. Lint, typecheck, build OK.
- [ ] Validation visuelle humaine (requise avant EST-6).

**Critère de sortie :** Les modalités sont affichées avec avertissements. Aucune confusion possible avec un paiement réel.

**Dépendances :** EST-4.

---

### EST-6 — Lead qualifié + persistence / API

**État actuel : Implémentation technique terminée (2026-08-30)**

- [x] CTA "Parler de mon projet" après résultat — jamais bloquant.
- [x] Formulaire lead : nom, email, téléphone (opt.), société (opt.), honeypot.
- [x] Endpoint `POST /api/estimate/lead` (Symfony, Caddy strippe `/api`).
- [x] Recalcul Symfony de l'estimation — prix clients jamais lus.
- [x] Persistence PostgreSQL (`estimator_lead`, snapshots JSONB).
- [x] Notification email Devzair (best-effort, échec = warning).
- [x] Rate limit dédié `estimate_lead_ip` (10/min, bucket distinct).
- [x] Honeypot + Origin allowlist + CSRF stateless.
- [x] Logs zéro PII.
- [x] `human_scoping_required` → `estimate_minimum_minor = NULL`.
- [x] Vie privée inline (pas de politique de confidentialité nécessaire).
- [x] Tests PHPUnit (controller + entity) + tests unitaires frontend.
- [x] Tests E2E Playwright.
- [x] Mise à jour docs (05, 06, 12, 08, 10).

**Critère de sortie :** Un lead est transmis, reçu, persisté, sans PII dans les logs.

**Dépendances :** EST-5. Durée de conservation validée (Q-02).

---

### EST-7 — Parcours partenariat

**État actuel : IMPLÉMENTATION TECHNIQUE TERMINÉE (2026-08-30) — validation visuelle requise**

- [x] Type `EstimatePartnershipPayload` + `PartnershipInitialContact` + codes union TypeScript.
- [x] `toPartnershipPayload()` — fonction pure, aucun champ de prix frontend.
- [x] `useEstimatorPartnershipApi` — double-submit guard, mapping erreurs HTTP, `resetPartnership`.
- [x] `EstimatorPartnershipForm.vue` — 2 fieldsets, radios type/stade/revenu, textareas + compteurs, honeypot, WCAG 2.2 AA.
- [x] `EstimatorResult.vue` — `activeForm: 'none' | 'lead' | 'partnership'`, CTA secondaire discret, pré-remplissage contact.
- [x] `EstimatorLeadForm.vue` — emit `submitted` enrichi avec données contact.
- [x] Migration `Version20260830130000` — table `estimator_partnership_proposal`.
- [x] Entité `EstimatorPartnershipProposal` — `status` toujours `pending_review`, aucun champ de calcul.
- [x] `DoctrineEstimatorPartnershipRepository`.
- [x] `EstimatePartnershipRateLimiter` (bucket `estimate_partnership_ip`, séparé du lead).
- [x] DTO `EstimatorPartnershipRequest` + `EstimatorPartnershipRequestMapper`.
- [x] `SubmitEstimatorPartnership` — recalcul autoritaire backend, `human_scoping` → montants null.
- [x] `EstimatorPartnershipController` — pipeline 8 étapes, réponse `{ status: "submitted", proposal_id, request_id }`.
- [x] Config : routes, services, framework, doctrine, monolog, services_test, .env.
- [x] PHPUnit : `EstimatorPartnershipProposalTest` (9 cas) + `EstimatorPartnershipControllerTest` (17 cas).
- [x] Vitest : `estimator-partnership-payload.spec.ts` + `useEstimatorPartnershipApi.spec.ts` + `EstimatorPartnershipForm.spec.ts` + `EstimatorResult.spec.ts` mis à jour.
- [x] Playwright : `estimator-partnership.spec.ts` (19 scénarios).
- [ ] Validation visuelle humaine.
- [ ] Notification email Devzair (V1 : dette documentée — persistence suffisante).

**Garanties implémentées :**
- Jamais une alternative de paiement automatique — `status` = `pending_review` uniquement.
- Backend recalcule l'estimation — prix client jamais lus.
- Aucun champ `percentage`, `equity`, `commissionRate`, `revenueShareRate`, `valuation` dans l'entité ni la réponse.
- `human_scoping_required` → `estimateMinimum = null`, `estimateMaximum = null`.

**Critère de sortie :** Parcours distinct, accessible, sans calcul automatique de conditions.

**Dépendances :** EST-6.

---

### EST-8 — Administration tarifaire

**État actuel : À FAIRE**

- [ ] CRUD sur les lignes tarifaires.
- [ ] Gestion des versions tarifaires.
- [ ] Interface Twig SSR (back-office existant).

**Critère de sortie :** L'administrateur peut modifier une grille et créer une version sans intervention technique.

**Dépendances :** EST-1, Phase 8C (administration authentifiée existante).

---

### EST-9 — Intégration site + QA + lancement

**État actuel : TERMINÉ (2026-09-02) — GO-LIVE READY (hardening pass)**

- [x] Intégration dans la navigation principale (desktop + mobile).
- [x] SSR standard confirmé (Q-13 VALIDÉE). Pas de pré-rendu.
- [x] Indexabilité en production contrôlée par `siteIndexable` global (Q-08 VALIDÉE).
- [x] Rétention RGPD 24 mois validée (Q-02 résolue). `EstimatorRetentionPolicy::MONTHS = 24`.
- [x] Commande `app:estimator:purge-expired` (dry-run + purge réelle, zéro PII en sortie). Fréquence recommandée : **quotidienne** (heures creuses).
- [x] Commande `app:estimator:preflight` (vérifications pré-déploiement). Gates prod durcies : `null://` MAILER_DSN → FAIL, `APP_ADMIN_BASE_URL` vide/localhost/HTTP → FAIL.
- [x] Tests PHPUnit retention policy + purge command (intégration KernelTestCase).
- [x] Tests PHPUnit preflight command (12 cas unitaires — MAILER_DSN, APP_ADMIN_BASE_URL, IPv6 loopback).
- [x] Notification email partenariat opérationnelle (`EstimatorPartnershipNotifier`).
- [x] Hydratation E2E déterministe : marqueur `data-estimator-hydrated` sur `EstimatorShell.vue`, helper `waitForEstimatorHydrated()`, tous les specs estimateur mis à jour. `estimator-shell.spec.ts` : 11/11 ×2.
- [x] PHPUnit global : 1 035/1 035 ×2 (2026-09-02).
- [x] Frontend : lint clean, typecheck clean, Vitest 1 046/1 046 (2026-09-02).

**Critère de sortie :** L'outil est publié, indexable si décidé, accessible, sécurisé, conforme RGPD, lié depuis la navigation.

**Dépendances :** EST-8 ou accord de déploiement sans administration.

---

---

## Programme SEO-COM — SEO commercial et architecture sémantique

Source de vérité fonctionnelle : `docs/13-SEO-COMMERCIAL.md`.

Ce programme est un **chantier transversal** ouvert en septembre 2026. Il peut être conduit en parallèle des phases globales restantes (10A2, 11C, 12), sous réserve que les pages produites respectent les gates habituelles : SSR, accessibilité, sécurité, tests, critère de terminé.

Il ne renumérote pas les phases 1–13 et n'interfère pas avec le programme Estimateur.

### Règle d'exécution

Ne jamais implémenter plusieurs phases SEO-COM importantes dans une seule intervention.

Pour chaque phase :

1. lire `docs/13-SEO-COMMERCIAL.md` ;
2. lire uniquement les autres documents utiles ;
3. inspecter les fichiers existants ;
4. annoncer les fichiers visés ;
5. identifier les risques SEO / accessibilité / régression ;
6. implémenter uniquement la phase demandée ;
7. écrire ou adapter les tests ;
8. exécuter lint, typecheck, tests unitaires concernés, E2E concernés, build ;
9. inspecter le HTML SSR des pages SEO touchées ;
10. mettre à jour `08-ROADMAP.md` et `10-TRACKING.md` seulement si les critères sont réellement remplis ;
11. fournir un rapport final précis.

Ne pas commencer automatiquement la phase suivante. Attendre une nouvelle instruction.

---

### SEO-COM-0 — Cadrage et documentation

**État actuel : TERMINÉE (2026-09-04)**

- [x] Lire et analyser tous les documents projet pertinents.
- [x] Inspecter l'implémentation existante (pages, configs, composables).
- [x] Créer `docs/13-SEO-COMMERCIAL.md` (source de vérité du chantier).
- [x] Ajouter le programme SEO-COM dans `docs/08-ROADMAP.md`.
- [x] Ajouter les tâches correspondantes dans `docs/10-TRACKING.md`.
- [x] Corriger les mentions obsolètes de « Phase 1 = phase actuelle » dans `AGENTS.md` et `docs/09-WORKFLOW.md`.

**Critère de sortie :** Le chantier est documenté, borné et compatible avec l'architecture actuelle. Aucune page n'a été créée.

**Statut : atteint.**

---

### SEO-COM-1 — Architecture services

**État actuel : TERMINÉE (2026-09-04)**

- [x] Créer `app/config/service-pages.ts` (8 services, tous `planned`, typés strictement).
- [x] Créer `app/composables/useServiceSchema.ts` (générique, calqué sur `useExpertiseServiceSchema`).
- [x] Créer `app/pages/services/index.vue` (hub éditorial : orientation, grille 8 services, CTA).
- [x] Créer la route dynamique `app/pages/services/[slug].vue` (404 pour tout slug `planned` ou inconnu).
- [x] Intégrer `/services` dans la navigation principale (header + footer).
- [x] Ajouter `/services` au prerender et au sitemap ; exclure toute route `status: "planned"`.
- [x] Écrire tests unitaires config (11 cas) et page (10 cas).
- [x] Écrire tests E2E SSR, H1, SEO, no-dead-links, sitemap, 404-guard, responsive, Axe WCAG AA.
- [x] Lint propre, typecheck OK, 1066 tests unitaires verts, build OK, HTML SSR inspecté.

**Critère de sortie :** La page hub `/services` est publiée avec un contenu éditorial réel. Aucune page service détaillée liée tant que son `status` n'est pas `published`.

**Dépendances :** SEO-COM-0.

---

### SEO-COM-2 — Page prioritaire création de site internet

**État actuel : TERMINÉE (2026-09-04)**

- [x] Publier `/services/creation-site-internet` (`status: "planned"` → `"published"`).
- [x] Étendre `ServicePageDefinition` avec `seoTitle` et `seoDescription` ; ajouter les valeurs pour les 8 entrées.
- [x] Créer `app/composables/useBreadcrumb.ts` (JSON-LD `BreadcrumbList`).
- [x] Mettre à jour `app/pages/services/[slug].vue` : SEO, Service JSON-LD, BreadcrumbList, dispatch vers `CreationSiteInternetPage`.
- [x] Créer `app/components/services/creation-site-internet/CreationSiteInternetPage.vue` (12 sections éditoriales : hero, situations, périmètre, design sur mesure, étapes projet, contenu, SEO base, déroulement, budget, réalisations vitrines, FAQ, CTA).
- [x] Ajouter `/services/creation-site-internet` au prerender (`nuxt.config.ts`).
- [x] Maillage entrant : lien depuis `/expertises/construire` (`ConstruireProjectCallout.vue`), carte hub `/services` maintenant cliquable.
- [x] Maillage sortant : liens vers `/estimer-mon-projet`, `/contact`, `/services/design-ui-ux-identite-visuelle`, `/services/photographie-creation-contenu`, `/services/seo-referencement-naturel`.
- [x] Tests unitaires mis à jour (14 cas — `seoTitle`/`seoDescription`, `creation-site-internet` publié, 7 autres planifiés, `resolveServiceRoute` retourne la route pour le publié). `services.spec.ts` mis à jour (lien hub conditionnel).
- [x] Tests E2E créés : `test/e2e/service-creation-site-internet.spec.ts` (200, H1, SEO, fil d'Ariane HTML, JSON-LD Service + BreadcrumbList, sections éditoriales, CTA, sitemap, 404 guard, responsive, Axe WCAG 2.2 AA). `services-hub.spec.ts` étendu (carte `creation-site-internet` cliquable).
- [x] Lint propre, typecheck OK, 1069 tests unitaires verts, build OK, HTML SSR inspecté (title, H1, canonical, JSON-LD, réalisations, FAQ).

**Critère de sortie :** La page est publiée, SSR complet, maillage cohérent, JSON-LD valide, tests verts.

**Dépendances :** SEO-COM-1.

---

### SEO-COM-3 — Estimateur : prix et budget

**État actuel : TERMINÉE** (2026-09-04)

- [x] Ajouter une section éditoriale SSR à `/estimer-mon-projet` (après le configurateur).
- [x] Adapter le title SEO et le H1 pour couvrir l'intention prix.
- [x] Vérifier que le contenu éditorial est dans le HTML serveur (pas uniquement client-side).
- [x] Ajouter un lien contextuel vers `/services/creation-site-internet` depuis l'éditorial.
- [x] Écrire ou adapter les tests SEO et SSR concernés.

**Critère de sortie :** L'estimateur répond également aux intentions liées au prix. Le configurateur reste l'élément central.

**Dépendances :** SEO-COM-0, Programme Estimateur livré.

### SEO-COM-3A — Refonte visuelle de l'éditorial estimateur

**État actuel : TERMINÉE** (2026-09-04)

- [x] Diagnostiquer le déséquilibre visuel desktop (colonne unique étroite, variables CSS manquantes).
- [x] Corriger le bug de contraste du lien hover (4.35:1 → 4.78:1 via suppression du fond rgba sur l'aside navy).
- [x] Refonte en 4 bandes visuelles distinctes (fourchette, facteurs, principe Devzair, FAQ).
- [x] Composition desktop : section A en 2 colonnes, section B en grille numérotée, section C navy avec aside bordé, section D FAQ 2 colonnes.
- [x] Éliminer toutes les variables CSS inexistantes (`--color-sand-light`, `--color-sand-dark`, `--color-text-muted`).
- [x] Axe WCAG 2.2 AA : aucune violation (16/16 tests verts).

**Critère de sortie :** Composition graphique cohérente avec l'identité Devzair, responsive, accessible.

---

### SEO-COM-4 — Réalisations et études de cas

**État actuel : TERMINÉE — Recette finale validée le 2026-09-05**

- [x] Étendre `case-studies.ts` : `slug`, `route`, `status`, `seoTitle`, `seoDescription` ajoutés à l'interface ; 4 études publiées (Kitchen Meat, Nidemiel, Mizan, Al Mumayiz) ; E-Shop Admin et Haramain Prestige maintenu `planned`.
- [x] **Haramain Prestige `planned`** (recette 2026-09-05) : autorisation de publication client non confirmée. Composant `HaramainPrestigePage.vue` conservé mais route non générée, page retourne 404. Lien et contenu resteront invisibles jusqu'à confirmation explicite.
- [x] Créer `app/pages/realisations/index.vue` (hub éditorial pré-rendu) : H1 "Des projets web conçus pour un besoin réel.", grille des 4 études publiées, section éditoriale + maillage `/services/creation-site-internet` + aside avec lien estimateur.
- [x] Créer `app/pages/realisations/[slug].vue` (dispatcher + guard 404) : même pattern que `/expertises/[slug].vue`, `usePageSeo`, `useBreadcrumb`, Schema.org BreadcrumbList uniquement.
- [x] Créer `CaseStudyCard.vue` pour la grille hub.
- [x] Créer 5 composants de page détail (KitchenMeatPage, NidemielPage, MizanPage, HaramainPrestigePage, AlMumayizPage) — chacun avec hero, contexte projet, 4 livrables, section maillage, EditorialCallout.
- [x] Ne jamais inventer de résultat quantifié.
- [x] Maillage câblé : `/services/creation-site-internet` depuis vitrines, `/expertises/concevoir` depuis SaaS/e-commerce, `/realisations` depuis chaque page détail.
- [x] Navigation principale : "Réalisations" ajouté entre Services et Agence (`navigation.ts` primary + footer `/#realisations` → `/realisations`).
- [x] `nuxt.config.ts` : pré-rendu + sitemap pour hub + 4 pages détail publiées.
- [x] `HomeFeaturedCaseStudy.vue` : CTA "Voir l'étude de cas →" utilise `study.route`, lien "Voir toutes les réalisations" ajouté.
- [x] `CreationSiteInternetPage.vue` : cartes vitrines liées à `/realisations/{slug}` (plus d'externe direct).
- [x] Contrôles : 1085 tests unitaires verts (+16), lint 0 error, vue-tsc exit 0, build OK.
- [x] Tests : `test/unit/config/case-studies.spec.ts` (16 cas), `test/e2e/realisations-hub.spec.ts` (11 cas), `test/e2e/realisations-detail.spec.ts` (7 × 4 + 3 = 31 cas).
- [x] Recette : Axe WCAG 2.2 AA 0 violation sur `/realisations` + 4 pages détail. SSR HTTP 200 sur 5 routes, 404 sur haramain-prestige + e-shop-admin + slug inconnu. Sitemap : 5 URLs (hub + 4 publiées, pas haramain). Médias : 150–256 KB webp, fetchpriority="high" sur hero images. Éditorial : 0 chiffre inventé, 0 superlatif, 0 résultat non vérifiable.

**Critère de sortie :** Les réalisations sont publiées avec un contenu factuel, maillées vers les services réels.

**Dépendances :** SEO-COM-1.

---

### SEO-COM-5A — Services construction

**État actuel : VALIDÉE**

- [x] Publier `/services/site-e-commerce`.
- [x] Publier `/services/application-web-metier`.
- [x] Écrire les tests pour chaque page.
- [x] Recette corrective SEO-COM-5A-R : lien planned supprimé, formulations factuelles corrigées, overflow header 1024px corrigé, 43/43 E2E verts, build OK.

**Dépendances :** SEO-COM-1, SEO-COM-2.

---

### SEO-COM-5B — Design et contenus

**État actuel : VALIDÉE**

- [x] Publier `/services/design-ui-ux-identite-visuelle`.
- [x] Publier `/services/photographie-creation-contenu`.
- [x] Écrire les tests pour chaque page.
- [x] Maillage : KitchenMeatPage lien design, SiteEcommercePage lien design restauré.
- [x] 570/570 E2E verts, build Docker OK, SSR 8/8, sitemap 2/2 nouvelles routes.

**Dépendances :** SEO-COM-1.

---

### SEO-COM-5C — Visibilité et maintenance

**État actuel : TERMINÉE**

- [x] Publier `/services/seo-referencement-naturel` — SeoReferencementNaturelPage.vue (SEO honnête, sans position garantie, audit + timeline + FAQ).
- [x] Publier `/services/visibilite-locale` — VisibiliteLocalePage.vue (dots de proximité, Google Business Profile, cohérence NAP, pas de carte fictive).
- [x] Publier `/services/maintenance-accompagnement` — MaintenanceAccompagnementPage.vue (corrective / préventive / évolution, cycle visuel, pas de promesse 24/7).
- [x] Dispatcher `[slug].vue` refactorisé en `Record<string, Component>` (8 entrées, 0 v-if).
- [x] Maillage : VisibiliteProjectCallout → SEO + Local ; FaireEvoluerProjectCallout → Maintenance.
- [x] nuxt.config.ts : prerender des 3 nouvelles routes.
- [x] `service-pages.ts` : 8 published, 0 planned.
- [x] Tests : service-pages.spec.ts (8/0), services-hub.spec.ts (8 cartes + guard), 3 nouveaux specs E2E.
- [x] Lint + typecheck + 1085 unit tests verts + build OK + SSR vérifié (Service JSON-LD + BreadcrumbList sur les 3 routes).

**Dépendances :** SEO-COM-1.

---

### SEO-COM-6 — Cluster « prix / site internet pas cher »

**État actuel : TERMINÉE (2026-09-05)**

- [x] Micro-correction SEO-COM-5C : summary `seo-referencement-naturel` mis à jour ("structurel" au lieu de "local").
- [x] Article `site-internet-pas-cher` rédigé et publié via `app:editorial:import` + `app:editorial:publish` (UUID `01a0725e-c287-7d87-a4a3-085fcb7fa668`, publishedAt 2026-09-05).
- [x] Maillage sortant : `/services/photographie-creation-contenu`, `/services/maintenance-accompagnement`, `/estimer-mon-projet`.
- [x] Maillage entrant : EstimatorEditorial FAQ item "réduire le budget" → BaseLink vers `/ressources/site-internet-pas-cher`.
- [x] SSR vérifié : H1, title, BlogPosting + BreadcrumbList JSON-LD, canonical, sitemap.
- [x] Tests E2E `resource-site-internet-pas-cher.spec.ts` : 17/17 verts (contre port 3001).
- [x] Lint + typecheck + 1085 unit tests + build OK.
- [x] Backlog documenté (non publié) : `prix-site-internet`, `prix-site-vitrine`, `refaire-site-internet-budget`.

**Dépendances :** SEO-COM-3, pipeline éditorial Phase 8B fonctionnel.

---

### SEO-COM-7 — Audit global et consolidation SEO/Architecture

**État actuel : TERMINÉE (2026-09-05)**

- [x] Audit complet : routes, maillage, Schema.org, navigation, sitemap, breadcrumbs.
- [x] Correction 1 — `useArticleSeo.ts` : `BlogPosting.author` bascule sur `{"@id": ".../#organization"}` (plus d'Organization inline dupliquée).
- [x] Correction 2 — `[slug].vue` : injection `BreadcrumbList` JSON-LD cohérente avec le `<nav aria-label="Fil d'Ariane">` HTML (5 pages expertise).
- [x] Correction 3 — `ValoriserNeedSection.vue` : lien contextuel `NuxtLink` vers `/services/photographie-creation-contenu` (maillage Valoriser → Photo).
- [x] Correction 4 — `ConcevoirProjectCallout.vue` : note de service avec `BaseLink` vers `/services/design-ui-ux-identite-visuelle` (maillage Concevoir → Design).
- [x] Correction 5 — `navigation.ts` : commentaire périmé corrigé (services publiés, non plus `planned`).
- [x] Lint + typecheck + 1086 unit tests (dont nouveaux : Organization @id ×1) + build Docker OK.
- [x] E2E : 7 nouveaux tests — BreadcrumbList JSON-LD sur 5 expertises, maillage concevoir→design, maillage valoriser→photo ; 134 verts sur expertise-pages.spec.ts.
- [x] SSR vérifié : BreadcrumbList sur `/expertises/concevoir`, `/expertises/valoriser`, etc. ; author `@id` sur `/ressources/site-internet-pas-cher`.

**Périmètre audité — résultat :** navigation principale et footer conformes ; maillage expertise↔service partiellement renforcé (2 liens ajoutés) ; Schema.org consolidé ; breadcrumbs HTML + JSON-LD alignés sur les 5 expertises ; 2 pré-existants hors scope (visibilité/faire-évoluer CTA) documentés.

**Dépendances :** SEO-COM-1 à 6.

---

### SEO-COM-8 — Optimisation accueil et page Agence

**État actuel : TERMINÉE (2026-09-06)**

Corrections livrées :

1. `HomeFeaturedCaseStudy.vue` — filtrage `status === "published"` appliqué sur `publishedStudies` : total, preload, slides, pagination. Les études `e-shop-admin` et `haramain-prestige` (`planned`) n'apparaissent plus dans le DOM.
2. `HomeHero.vue` — commentaire obsolète corrigé ("tant que la page cible n'existe pas" retiré) ; `#realisations` conservé (parcours Hero → preview → hub `/realisations` cohérent).
3. `HomeCallToAction.vue` — CTA secondaire `/estimer-mon-projet` ajouté (2 niveaux d'engagement) ; CSS fond inverse pour bouton secondaire ; commentaire mis à jour.
4. `HomeTrust.vue` — lead reformulé ("que nos clients retrouvent" → "Cinq principes qui orientent chaque décision, du premier brief au suivi post-lancement").
5. `HomeExpertisePillars.vue` — lien discret "Voir les prestations détaillées → /services" ajouté dans l'intro (5 pôles = comment Devzair travaille / Services = ce que Devzair réalise concrètement).
6. `AgenceHero.vue` — lead reformulé ("Une équipe réduite" → "Un interlocuteur direct") ; pilier "Globale" → "Intégrée".
7. `agence.vue` — intro positionnement reformulée (suppression "l'équipe qui conçoit, celle qui construit") ; lien `/services` ajouté dans la section fonctionnement ; callout secondary `/contact` → `/realisations` (maillage agence → réalisations) ; commentaire "Phase 7A" supprimé.
8. `home-sections-secondary.spec.ts` — test nav header "Réalisations" mis à jour (`#realisations` → `/realisations`, obsolète depuis SEO-COM-5).
9. Tests unitaires mis à jour : `HomeCallToAction.spec.ts` (2 CTAs), `HomeFeaturedCaseStudy.spec.ts` (published seulement), `agence.spec.ts` (lead, pilier Intégrée, route `/realisations` autorisée).
10. `home-cta-final.spec.ts` — test mis à jour (2 CTAs : /contact + /estimer-mon-projet).

**Résultats :** 1086/1086 unit tests verts, 136/136 E2E expertise-pages, 45/45 E2E home-hero/structure/cta, 28/28 home-sections-secondary, lint 0 erreur, typecheck OK, build Docker OK, SSR vérifié.

**Dépendances :** SEO-COM-1, pages services publiées.

---

### SEO-COM-9 — Identité, local et autorité

**État actuel : EN ATTENTE DE VALIDATION MÉTIER**

Aucune action possible tant que les informations suivantes n'ont pas été validées par l'humain :

- [ ] Raison sociale / nom légal à publier.
- [ ] E-mail professionnel.
- [ ] Téléphone professionnel.
- [ ] Ville et zone réellement desservie.
- [ ] Profils sociaux officiels.
- [ ] Logo et image Open Graph.
- [ ] Éligibilité réelle au référencement local (Google Business Profile).

Une fois validées : mettre à jour `site.ts` et les données structurées pertinentes. Ne créer `LocalBusiness` que si réellement justifié (DEC à ouvrir).

**Dépendances :** Validation métier explicite (données locales). Voir DEC-097 et DEC-100.

---

### SEO-COM-10 — Recette production

**État actuel : À FAIRE**

- [ ] Vérifier domaine canonique, `NUXT_PUBLIC_SITE_URL`, `NUXT_PUBLIC_SITE_INDEXABLE=true`.
- [ ] Vérifier `X-Robots-Tag`, `<meta robots>`, robots.txt, sitemap.xml, canonicals.
- [ ] Contrôler HTML SSR pour chaque page stratégique (title, H1, contenu, JSON-LD).
- [ ] Vérifier HTTP (200, 301, 404, absence de soft-404, liens cassés).
- [ ] Valider Schema.org (Organization, WebSite, Service, BreadcrumbList, BlogPosting).
- [ ] Contrôler LCP, CLS, INP, images, fonts, JavaScript.
- [ ] Soumettre le sitemap dans Search Console.
- [ ] Inspecter les pages principales et surveiller les impressions.

**Critère de sortie :** Toutes les pages commerciales publiées sont indexables, sans erreur, avec Schema.org valide.

**Dépendances :** SEO-COM-1 à 8, Phase 12 (production VPS).

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
