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

### Phase 8B — Écriture, rendu markdown et validation avancée (EN COURS)

**Découpage** :

- **8B1 Pipeline éditorial sécurisé et cache HTTP conditionnel** (livré) :
  import CLI de Markdown vers Postgres, publication manuelle explicite,
  cache HTTP conditionnel `ETag`/`Last-Modified` avec 304, refus des
  publications futures. Aucune page Nuxt éditoriale, aucun endpoint
  d'écriture HTTP, aucun back-office.
- **8B2 Rendu HTML Markdown côté Nuxt et pages `/ressources`** (à venir) :
  branchement du renderer CommonMark côté serveur (Nuxt), pages
  `/ressources` et `/ressources/{slug}`.

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

#### Phase 8B2 — Rendu HTML Markdown côté Nuxt et pages `/ressources` (À VENIR)

- [ ] Pages `/ressources` (index) et `/ressources/{slug}` (détail) côté Nuxt.
- [ ] Rendu Markdown → HTML côté serveur (Nuxt) avec les mêmes garanties
      que la politique CLI (`html_input=strip`, URLs restreintes).
- [ ] Métadonnées SEO dynamiques + Schema.org `Article`.
- [ ] Sitemap dynamique adossé à l'API publique.
- [ ] Tests SSR + Playwright + Axe pour les deux pages.

### Phase 8C — Administration authentifiée (À VENIR)

- [ ] Auth admin (mode et provider à décider).
- [ ] UI d'édition (Nuxt route protégée ou Symfony admin dédiée).
- [ ] Journal éditorial (qui a publié/archivé, quand).
- [ ] Rôles fins si besoin (multi-auteurs).

### Critère de sortie (Phase 8 complète)

L’API retourne des contrats stables, validés, documentés et testés,
depuis la persistance jusqu'à l'administration protégée.

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
