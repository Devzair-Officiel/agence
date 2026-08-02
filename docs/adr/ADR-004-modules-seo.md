# ADR-004 — Modules SEO retenus pour le socle Nuxt

- Statut : accepté
- Date : 2026-08-02
- Décideurs : équipe frontend Devzair

## Contexte

La Phase 4 du projet Devzair impose un socle SEO technique centralisé,
compatible avec le rendu SSR, une politique d'indexation par environnement,
un sitemap, un robots.txt dynamique et des données structurées globales.

L'écosystème Nuxt propose plusieurs modules autour de nuxt-site-config
(bundle Nuxt SEO). Installer tous les modules d'un coup ajoute du poids
de configuration et de build pour un site qui n'a aujourd'hui que
2 routes réelles (dont 1 noindex).

## Options étudiées

1. **Bundle Nuxt SEO** (`@nuxtjs/seo`) — regroupe sitemap, robots,
   schema-org, link-checker, og-image, seo-utils.
2. **Modules séparés à la carte** — n'installer que ce qui est utile
   maintenant, ajouter les autres au fur et à mesure.
3. **Implémentation maison** — robots.txt et sitemap.xml via routes
   serveur Nitro, Schema.org via `useHead`.

## Décision

**Option 2, avec un scope minimal** :

- **`@nuxtjs/robots` v5.7** — retenu pour la politique d'indexation par
  environnement (X-Robots-Tag global, meta robots, robots.txt dynamique,
  règles OAI-SearchBot / GPTBot).
- **`@nuxtjs/sitemap` v7.6** — retenu pour la génération du sitemap
  depuis les routes Nuxt, avec exclusion automatique des routes noindex.
- **Schema.org géré à la main** via `useSiteSchema` (composable maison
  qui injecte le graphe Organization + WebSite dans `useHead`).

Sont **différés** :

- `nuxt-schema-org` : deux entités statiques ne justifient pas une
  dépendance supplémentaire ; ré-évaluer à l'ajout du blog (Phase 9)
  quand Article, BlogPosting et BreadcrumbList apparaîtront.
- `nuxt-link-checker` : aucun bénéfice sur 2 routes ; ré-évaluer en
  Phase 5 (pages marketing) puis Phase 9 (blog).
- `nuxt-seo-utils` : utilitaires pratiques mais pas requis maintenant ;
  ré-évaluer en Phase 5.
- `@nuxtjs/seo` (bundle) : trop de modules d'un coup, préférence pour
  l'ajout incrémental.
- `nuxt-og-image` : la génération dynamique d'image OG est coûteuse en
  build ; on préfère une image statique validée par le client.

## Raisons

- **KISS + DRY** : deux modules ciblés couvrent 100 % du périmètre
  Phase 4, sans ajouter d'abstractions inutilisées.
- **Une seule source de vérité** : `NUXT_PUBLIC_SITE_INDEXABLE` alimente
  simultanément `runtimeConfig.public.siteIndexable`, `site.indexable`
  (nuxt-site-config, embarqué), et donc @nuxtjs/robots. Impossible
  d'oublier la politique globale.
- **Sécurité par défaut** : `siteIndexable=false` est le défaut, la
  preprod ne peut jamais devenir indexable par omission de variable.
- **Testabilité** : les modules choisis exposent `/robots.txt` et
  `/sitemap.xml` comme routes Nitro, testables via un simple `fetch`
  Playwright — pas de mock nécessaire.
- **Maintenance** : les deux modules ont une adoption large
  (nuxtseo.com), une compatibilité Nuxt 4 stable, et suivent le calendrier
  de leur bundle parent.

## Conséquences

### Positives

- Le contrat SEO tient dans deux composables (`usePageSeo`, `useSiteSchema`)
  et deux utilitaires purs (`site-url.ts`, `canonical.ts`), tous testés.
- La politique d'indexation est imposée globalement, aucune page ne peut
  y échapper.
- Le sitemap exclut automatiquement les routes marquées noindex par
  `usePageSeo`.
- L'ajout d'articles Symfony (Phase 9) pourra brancher son propre
  provider de sitemap via l'API de `@nuxtjs/sitemap` sans refonte.

### Limites

- Le graphe Schema.org maison devra être étendu manuellement pour
  chaque nouveau type d'entité (Service, Article, Person). Au 3ᵉ type,
  ré-évaluer l'installation de `nuxt-schema-org`.
- La politique GEO (`OAI-SearchBot: Allow` / `GPTBot: Disallow`) est
  déclarée dans `nuxt.config.ts` ; toute évolution demande un rebuild
  (acceptable pour une décision aussi structurante).

### Travaux associés

- DEV-021 (Phase 4) — implémentation du socle SEO.
- Réévaluer les modules différés à l'ouverture de la Phase 9.

## Références

- Nuxt SEO — https://nuxtseo.com/learn-seo/nuxt
- @nuxtjs/robots — https://nuxtseo.com/robots
- @nuxtjs/sitemap — https://nuxtseo.com/sitemap
- docs/03-SEO-NUXT.md §10.8 — Implémentation SEO obligatoire avec Nuxt
- docs/04-SEO-CONTENT-GEO.md §13.3 — Accessibilité technique aux
  systèmes d'IA
