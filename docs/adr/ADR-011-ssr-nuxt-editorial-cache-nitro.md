# ADR-011 — SSR Nuxt des ressources éditoriales et cache Nitro

- Statut : accepté
- Date : 2026-08-05
- Décideurs : équipe Devzair

## Contexte

La Phase 8B1 (ADR-010) livre la persistance PostgreSQL, l'import CLI
Markdown avec `MarkdownSecurityPolicy` et l'API publique
`GET /api/resources` + `GET /api/resources/{slug}`, avec cache HTTP
conditionnel (`ETag` faible, `Last-Modified` sur le détail, `304`).

La Phase 8B2 doit livrer :

1. les deux pages Nuxt publiques `/ressources` (liste paginée) et
   `/ressources/{slug}` (détail) rendues côté serveur ;
2. l'exposition du HTML rendu depuis le Markdown source, sans risque
   d'ouvrir une deuxième frontière de confiance à côté de celle validée
   en Phase 8B1 ;
3. un cache serveur côté Nitro qui protège Symfony sans altérer la
   sémantique HTTP côté navigateur.

Trois questions structurent l'ADR :

1. **Où et comment convertir le Markdown en HTML ?** Le rendu peut avoir
   lieu côté Symfony ou côté Nuxt. La contrainte sécurité rend
   l'inversion défavorable : la politique de rendu est déjà validée par
   `MarkdownSecurityPolicy` côté PHP, la déplacer côté Node ferait
   perdre la validation à l'import.
2. **Faut-il relayer l'ETag JSON amont vers le navigateur ?** L'ETag
   émis par Symfony décrit la représentation JSON de la ressource ; la
   page Nuxt en produit une représentation HTML. Ce sont deux
   représentations distinctes, et RFC 7232 interdit de conditionner la
   première par le validateur de la seconde.
3. **Comment protéger Symfony sans casser la validation HTTP ?** Faire
   circuler à chaque requête utilisateur un aller-retour complet vers
   Symfony revient à annuler le bénéfice du cache HTTP.

## Options étudiées

### Rendu Markdown → HTML

1. **Rendu côté Symfony (retenu)**. Le renderer
   `CommonMarkArticleRenderer` (déjà présent Phase 8B1) est injecté
   dans `GetPublishedArticleHandler` ; la vue de détail expose un
   nouveau champ `content_html`. Le contrat JSON reste additif
   (`body_markdown` est préservé pour d'éventuels usages hors rendu :
   debug, exports). Symfony reste le point unique où la politique
   `html_input=strip`, `allow_unsafe_links=false` et les bornes de
   complexité sont appliquées.
2. Rendu côté Nuxt (via `markdown-it`/`marked` + DOMPurify). Ajoute une
   dépendance JS, une politique de sécurité parallèle à
   `MarkdownSecurityPolicy`, et un risque de divergence entre les deux
   pipelines. Rejeté.
3. Rendu à l'import, stockage HTML en base. Rejeté : force à remigrer la
   base à chaque évolution du renderer ; incompatible avec la règle
   « stockage = format source » définie par ADR-010.

### Représentation frontière de confiance HTML côté Nuxt

1. **Un unique composant `ResourceContent.vue` autorisé à utiliser
   `v-html`, alimenté exclusivement par `content_html`** (retenu).
   La règle est documentée dans le composant, protégée par le linter
   `vue/no-v-html` (suppression locale explicite), et couverte par un
   test de trust-frontier. Aucun autre composant du projet n'a le droit
   d'introduire de HTML dynamique.
2. Composant plus large exposant `body_markdown` + rendu côté client via
   `v-html` après passage dans un renderer JS. Rejeté (voir supra).
3. `v-html` directement dans la page. Rejeté : casse le principe DDD
   « les composants présentent, les pages orchestrent » du référentiel
   AGENTS.md §6, et disperse la frontière sur plusieurs surfaces.

### Bumps de version ETag

1. **`ArticleETag::CONTRACT_VERSION = 'v2'`** (retenu) — le détail
   émet désormais `content_html` en plus de `body_markdown`. Toute
   URL détail vue par un client qui avait l'ETag `v1` doit revalider :
   un client naïf réutilisant l'ancien ETag recevrait un 304 qui
   masquerait le nouveau champ. On casse volontairement la validation
   à l'apparition du contrat v2.
2. Conserver `v1`. Le corps change mais l'ETag reste dérivé de
   `id|updatedAt|v1` : un client verrait un 200 avec nouveau contenu
   sur la première requête (car `updatedAt` peut être identique) et
   éventuellement un 304 masquant sur la seconde. Rejeté.
3. Bump aussi la version liste. Rejeté : la liste n'expose pas
   `content_html` et n'est pas concernée par le changement de contrat.
   Chaque validateur a sa propre durée de vie.

### Cache serveur Nitro

1. **Cache local `useStorage("editorial")` à clé
   `list:{page}:{perPage}` / `detail:{slug}`, contenant
   `{data, etag, lastModified, cachedAt}`, et négociation `If-None-Match`
   entre Nitro et Symfony sur l'ETag local** (retenu). Nitro n'expose
   pas cet ETag au navigateur : c'est un couplage interne. Sur 304
   côté Symfony, Nitro rejoue le payload local. Sur 200, Nitro remplace
   le cache. Sur 4xx/5xx/payload invalide, Nitro purge le cache et
   remonte le statut applicatif — le sitemap et les pages restent
   véridiques, jamais silencieusement obsolètes.
2. Passer aveuglément le `If-None-Match` du navigateur à Symfony.
   Rejeté : les représentations sont distinctes (HTML page vs JSON
   ressource) ; la RFC 7232 interdit de traiter comme équivalentes deux
   ressources dont l'un ne dérive pas verbatim de l'autre. Un `304` de
   Symfony ne signifie **pas** que la page HTML n'a pas changé.
3. Cache Redis / KV distant. Prématuré (pas de Redis en Phase 8) ; le
   backend `useStorage()` reste substituable sans changer le code.
4. Aucun cache : chaque requête utilisateur = un fetch Symfony. Rejeté :
   annule le bénéfice de l'ETag amont et transforme un pic de trafic en
   pression directe sur PostgreSQL.

### Endpoints internes Nitro

1. **`server/routes/_editorial/list.get.ts` et
   `server/routes/_editorial/detail/[slug].get.ts`** (retenu). Le
   préfixe `_editorial` évite explicitement le préfixe `/api/*` routé
   par Caddy vers Symfony. Les composables consomment ces endpoints en
   SSR ET en navigation SPA : la logique de cache et de mapping vit à
   un seul endroit.
2. `server/api/editorial/*`. Nommage plus proche de la convention Nuxt,
   mais Caddy intercepterait `/api/*` avant Nitro et les composants
   côté client tomberaient sur Symfony au lieu du cache Nitro. Rejeté.
3. Appel direct à `createEditorialApi()` depuis les composables sans
   couche endpoint. Rejeté : le cache serveur ne peut pas vivre côté
   client, et la duplication des politiques d'erreur créerait un
   contrat implicite.

### Sitemap des URLs éditoriales

1. **Source dynamique JSON `/__sitemap__/resources`, itérée par
   `@nuxtjs/sitemap` au build ; refuse de renvoyer un sitemap partiel
   ou silencieusement vide si l'API amont est indisponible (503)**
   (retenu). Le sitemap ne peut pas mentir : si Symfony est down au
   moment de la construction, le sitemap n'est pas livré et une alerte
   remonte. Le préfixe `/__sitemap__` évite le routing Caddy `/api/*`.
2. Pré-rendu statique de la liste au build. Rejeté : chaque publication
   forcerait un redéploiement.
3. Source vide silencieuse si l'API répond mal. Rejeté par correction
   obligatoire du brief : mentir sur la carte des URLs indexables est
   pire que produire zéro URL — l'un dégrade proprement, l'autre
   corrompt Google Search Console.

### Contrat d'URL de la liste

1. **`/ressources` = page 1 canonique ; `/ressources?page=N` pour N>1 ;
   `?page=1` redirige 301 vers `/ressources` ; toute valeur invalide
   ou hors bornes → 404 fatal** (retenu). Une seule URL canonique par
   inventaire évite le duplicate content.
2. Autoriser `?page=1` en 200 avec canonical vers `/ressources`.
   Rejeté : dépend d'une balise canonique correctement lue par le
   crawler, alors qu'une 301 est un signal HTTP fort.
3. Utiliser un chemin plutôt qu'une query (`/ressources/page/2`).
   Rejeté : sur-ingénierie pour un besoin qui reste très simple.

### Contrat d'état applicatif côté page

1. **Distinguer explicitement liste vide (200 + `ResourceEmptyState`),
   404 (slug inconnu, page hors bornes), 502 (payload amont invalide),
   503 (API indisponible)** (retenu). Chaque état est mappé par
   `useResources` en `createError({fatal: true})` ce qui déclenche la
   page d'erreur Nuxt avec le bon code HTTP côté SSR.
2. Fallback silencieux vers un état neutre (liste vide) en cas d'erreur.
   Rejeté : masque une indisponibilité en apparente publication vide,
   contredit la règle 11 du référentiel (« ne rien publier de
   placeholder »).

## Décision

### Pipeline HTML

- `GetPublishedArticleHandler` reçoit `CommonMarkArticleRenderer` en
  dépendance et calcule `content_html` à partir de `body_markdown`.
- `ArticleDetailView::fromEntity()` requiert désormais `contentHtml` en
  second argument ; `toArray()` expose `content_html` en plus de
  `body_markdown` (contrat additif).
- `ArticleETag::CONTRACT_VERSION = 'v2'` — invalidation cible du
  détail. La liste conserve son propre versionning.

### Trust frontier HTML côté Nuxt

- `ResourceContent.vue` est le SEUL composant du projet autorisé à
  utiliser `v-html`. La règle est documentée dans le composant,
  suppression locale explicite du linter `vue/no-v-html`, test de
  trust-frontier dans `test/unit/pages/resources-content.spec.ts`.
- Toute évolution qui étendrait `v-html` à un autre champ ou composant
  est un changement de frontière de confiance et doit passer par une
  nouvelle ADR.

### Cache Nitro

- `server/utils/editorial-cache.ts` maintient un cache local à clés
  `list:{page}:{perPage}` et `detail:{slug}` stockant
  `{data, etag, lastModified, cachedAt}`.
- Le `If-None-Match` envoyé à Symfony provient EXCLUSIVEMENT du cache
  local ; le validateur du navigateur n'est **jamais** propagé.
- Sur 304 avec cache local → payload local rejoué (`status: "ok"` côté
  service).
- Sur 304 sans cache local (validateur périmé, éviction race) → retry
  sans validateur puis mise en cache.
- Sur 4xx/5xx/`payload_invalid` → purge du cache local pour éviter de
  servir un payload obsolète.

### Endpoints internes

- `server/routes/_editorial/list.get.ts` — GET
  `/_editorial/list?page=X&per_page=Y`, mapping des statuts
  applicatifs en HTTP (200 / 400 / 404 / 502 / 503).
- `server/routes/_editorial/detail/[slug].get.ts` — GET
  `/_editorial/detail/{slug}`, validation stricte du slug
  (`SLUG_PATTERN`), mêmes statuts.
- Aucun ETag / Cache-Control posé sur ces endpoints internes : leur
  politique de cache est un détail d'implémentation Nitro, pas un
  contrat HTTP.

### Sitemap dynamique

- `server/routes/__sitemap__/resources.ts` itère la liste amont
  (`per_page=100`, boucle jusqu'à `totalPages`) et retourne
  `{loc, lastmod}[]`.
- Si un appel amont échoue (autre que `ok`), le handler jette
  `createError(503)` — le sitemap n'est pas livré, une alerte remonte.
- Enregistré dans `nuxt.config.ts` → `sitemap.sources`.
- Préfixe `/__sitemap__/` réservé à Nitro (pas de collision avec Caddy).

### Contrat d'URL et d'état

- `/ressources` = page 1 sans query.
- `/ressources?page=1` → redirect 301 vers `/ressources`.
- `?page` non-entier / ≤0 / >10 000 → 404 fatal.
- `?page` > `totalPages` (réel côté API) → 404 fatal.
- `pagination.total === 0` → 200 + `ResourceEmptyState`.
- `/ressources/{slug}` inconnu → 404 fatal.
- API amont indisponible → 503 fatal ; payload invalide → 502 fatal.

### Runtime config

- Clé privée `runtimeConfig.editorialApiBaseUrl` (surchargeable via
  `NUXT_EDITORIAL_API_BASE_URL`). En dev/Compose, pointe vers le
  conteneur `api` (`http://api:8000`). En prod, pointe vers l'URL
  interne du service Symfony — jamais vers Caddy, jamais vers un
  domaine public.
- Absence de valeur → 503 immédiat à l'initialisation du cache.
- `apiBaseUrl` (public) reste `/api` et sert uniquement aux appels
  client-side vers les endpoints Symfony publics (contact, etc.).

## Raisons

- **Éviter la double frontière de confiance HTML.** Un unique renderer
  Markdown vit côté Symfony, sous la politique déjà validée par ADR-010.
  Le front consomme du HTML déjà nettoyé ; il ne connaît pas Markdown.
- **Respecter la sémantique HTTP.** Un ETag JSON amont ne peut pas
  conditionner une réponse HTML aval. Le cache local Nitro assure la
  protection origine sans mentir sur le contrat HTTP navigateur.
- **Ne rien mentir.** Un sitemap partiel, une liste vide silencieuse
  sur erreur, un 200 sur une page qui n'existe pas — tous sont des
  mensonges structurels qui corrompent le SEO et la confiance
  utilisateur. Chaque état applicatif a un code HTTP explicite.
- **Une seule URL canonique par inventaire.** `/ressources` sans query
  pour la page 1, redirection 301 pour `?page=1`. Google, un lecteur
  RSS, un partage social — tous convergent vers la même URL.
- **Substituabilité.** `useStorage("editorial")` bascule sans code
  applicatif d'un backend mémoire à Redis quand le besoin apparaîtra.

## Conséquences

**Positives**

- Le rendu HTML est validé par un seul pipeline (Symfony
  `MarkdownSecurityPolicy`) et servi par un seul composant côté front
  (`ResourceContent.vue`).
- L'origine Symfony est protégée : à trafic modéré, la majorité des
  requêtes utilisateur se résolvent par 304 côté Nitro sans toucher
  PostgreSQL.
- Le sitemap est fiable ou explicitement absent — pas d'entre-deux
  silencieux.
- Chaque état applicatif est mappé sur un code HTTP réel, ce qui rend
  les tests E2E (Playwright via Caddy) déterministes et l'analytics
  interprétables (un 404 est un 404, pas une page vide).

**Limites / risques**

- Le cache Nitro par instance (`useStorage()` local) ne partage pas son
  état entre plusieurs pods Nuxt. Acceptable en Phase 8 (single
  instance) ; à réévaluer avec un backend distribué le jour où on scale.
- La bump `v1 → v2` de `ArticleETag` invalide tous les caches détail
  existants au déploiement. C'est le comportement voulu (contrat
  additif = nouvelle vérité) ; pas de plan de warm-up nécessaire
  vu la volumétrie.
- La topologie E2E (Playwright → Caddy → Symfony/Nitro) impose une
  orchestration de fixtures dédiée pour préparer 10 articles connus
  côté PostgreSQL sans TRUNCATE global. Documenté dans le
  README des tests E2E.

## Références

- `apps/api/src/Editorial/Application/View/ArticleDetailView.php`
  — ajout de `contentHtml`.
- `apps/api/src/Editorial/Application/Query/GetPublishedArticleHandler.php`
  — injection du renderer.
- `apps/api/src/Editorial/Presentation/Http/ConditionalCache/ArticleETag.php`
  — `CONTRACT_VERSION = 'v2'`.
- `apps/web/app/types/editorial.ts` — contrats camelCase.
- `apps/web/app/utils/editorial-contract.ts` — mapper snake→camel.
- `apps/web/app/services/editorial-api.ts` — service HTTP + statuts.
- `apps/web/server/utils/editorial-cache.ts` — cache local Nitro.
- `apps/web/server/routes/_editorial/*` — endpoints internes.
- `apps/web/server/routes/__sitemap__/resources.ts` — source dynamique.
- `apps/web/app/composables/useResources.ts` — mapping statuts → Nuxt.
- `apps/web/app/composables/useArticleSeo.ts` — BlogPosting + Breadcrumb.
- `apps/web/app/components/resources/ResourceContent.vue` — trust
  frontier HTML.
- `apps/web/app/pages/ressources/index.vue`,
  `apps/web/app/pages/ressources/[slug].vue` — orchestration.
- ADR-010 — pipeline Markdown éditorial et cache HTTP amont.
