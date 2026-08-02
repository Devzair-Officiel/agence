# SEO technique avec Nuxt

> Rendu serveur, métadonnées, indexation, canonical, Schema.org, performance et implémentation Nuxt.

## 10. SEO technique

## 10.1 Rendu et accessibilité aux robots

- le contenu stratégique doit être présent dans le HTML rendu ;
- privilégier SSR, SSG ou rendu serveur équivalent pour les pages publiques ;
- ne pas dépendre d’une interaction utilisateur pour charger le contenu principal ;
- utiliser des liens HTML explorables ;
- vérifier le rendu avec les outils d’inspection des moteurs ;
- ne pas masquer du contenu important dans des images ou vidéos sans équivalent textuel.

## 10.2 Métadonnées

Chaque page indexable doit avoir :

- un `<title>` unique, descriptif et naturel ;
- une meta description spécifique ;
- une URL canonique absolue ;
- un H1 principal cohérent ;
- une hiérarchie H2/H3 logique ;
- les balises Open Graph nécessaires ;
- les métadonnées sociales pertinentes ;
- une langue de document correcte ;
- un `noindex` explicite pour les pages non destinées aux résultats.

Les longueurs ne doivent pas être forcées mécaniquement : l’objectif est la clarté et l’absence de troncature inutile.

## 10.3 Indexation

- un sitemap XML contenant uniquement les URL canoniques, publiques et indexables ;
- `lastmod` uniquement s’il reflète une modification réelle ;
- robots.txt à la racine ;
- ne pas utiliser robots.txt pour protéger une information privée ;
- utiliser authentification, suppression, `noindex` ou en-tête X-Robots-Tag selon le cas ;
- exclure préproduction, administration, résultats internes et paramètres inutiles ;
- vérifier les codes HTTP ;
- page supprimée sans équivalent : 404 ou 410 ;
- page déplacée : 301 vers l’équivalent le plus proche ;
- éviter les chaînes et boucles de redirection ;
- corriger les soft 404.

## 10.4 Canonicalisation et variantes

- une seule version de domaine : HTTPS et choix clair entre domaine racine et `www` ;
- redirection permanente des variantes ;
- pas de canonical vers une page non équivalente ;
- paramètres de tracking sans duplication indexable ;
- cohérence entre canonical, sitemap, liens internes et redirections ;
- si multilingue : URL distincte, contenu réellement traduit, `hreflang` réciproque et canonique dans la même langue.

## 10.5 Données structurées

Implémenter uniquement les données exactes, visibles et pertinentes :

- `Organization` sur l’accueil ou la page agence ;
- sous-type approprié si la situation réelle le permet ;
- `WebSite` ;
- `BreadcrumbList` ;
- `Article` ou `BlogPosting` pour les ressources ;
- données de profil d’auteur si l’auteur est réel ;
- types complémentaires de schema.org lorsque cohérents, sans promettre de résultat enrichi.

### Règles

- JSON-LD recommandé ;
- identifiants `@id` stables ;
- logo, nom, URL, coordonnées et profils cohérents ;
- ne pas créer de notes ou avis auto-attribués ;
- ne pas baliser un contenu absent de la page ;
- valider la syntaxe et les règles Google applicables ;
- surveiller les avertissements et actions manuelles.

**Note 2026 :** les résultats enrichis FAQ ont été retirés de Google. Les sections de questions-réponses restent utiles aux visiteurs et à la compréhension du contenu, mais ne doivent pas être créées pour obtenir cet ancien affichage.

## 10.6 Images et médias

- formats modernes lorsque compatibles ;
- compression adaptée ;
- dimensions explicites ;
- `srcset` et tailles responsives ;
- chargement différé hors écran ;
- ne pas différer l’image LCP principale ;
- textes alternatifs contextuels ;
- noms de fichiers descriptifs sans sur-optimisation ;
- légendes lorsque utiles ;
- droits, crédits et autorisations documentés ;
- sitemap image seulement si nécessaire.

## 10.7 Performance

Cibles terrain au 75e centile, mobile et ordinateur :

- LCP ≤ 2,5 s ;
- INP ≤ 200 ms ;
- CLS ≤ 0,1.

### Budget initial à définir après choix technique

- poids JavaScript ;
- poids CSS ;
- poids total initial ;
- nombre de polices ;
- taille maximale des images ;
- nombre de scripts tiers ;
- temps serveur ;
- seuil Lighthouse de contrôle en CI, sans le confondre avec les données réelles utilisateurs.

### Actions

- réduire le JavaScript client ;
- supprimer le code inutilisé ;
- optimiser les polices ;
- précharger uniquement les ressources critiques ;
- cache HTTP correct ;
- CDN si pertinent ;
- compression Brotli ou gzip ;
- images responsives ;
- éviter les scripts tiers non essentiels ;
- mesurer en laboratoire et sur le terrain.

---


## 10.8 Implémentation SEO obligatoire avec Nuxt

### Principe général

Nuxt doit conserver son rendu universel. Une page publique importante ne doit pas utiliser `ssr: false`. Le HTML initial doit déjà contenir :

- le titre principal ;
- le contenu éditorial essentiel ;
- les liens internes ;
- le `<title>` ;
- la meta description ;
- la canonical ;
- les balises Open Graph ;
- les données structurées utiles.

Le chargement client ne doit servir qu’à enrichir l’expérience, pas à rendre le contenu indexable.

### Répartition des responsabilités SEO

| Élément | Emplacement recommandé |
|---|---|
| Nom, URL et identité globale | configuration centrale du site |
| Langue, favicon, title template | `app/app.vue` ou configuration globale |
| Métadonnées propres à une page | page concernée via `usePageSeo()` |
| Canonical | composable SEO central |
| Robots propres à une page | composable SEO ou règles de route |
| Schema.org global | configuration SEO ou composable global |
| Schema.org d’un article | page dynamique de l’article |
| Sitemap et robots.txt | module SEO ou route serveur dédiée |
| Redirections | `routeRules` ou reverse proxy selon le cas |
| Pages non indexables | meta robots et protection réelle si privées |

### Composable central `usePageSeo`

Implémentation vivante : [`apps/web/app/composables/usePageSeo.ts`](../apps/web/app/composables/usePageSeo.ts).

Chaque page appelle `usePageSeo()` une seule fois pour publier ses
métadonnées :

```ts
export interface PageSeoInput {
  title: string
  description: string
  path: string
  image?: string
  imageAlt?: string
  type?: 'website' | 'article'
  noindex?: boolean
  robots?: 'index, follow' | 'noindex, follow' | 'noindex, nofollow' | 'index, nofollow'
}
```

Le composable s’appuie sur deux utilitaires purs (donc testables sans
Nuxt) : [`app/utils/canonical.ts`](../apps/web/app/utils/canonical.ts) et
[`app/utils/site-url.ts`](../apps/web/app/utils/site-url.ts).

### Règles du composable

- `siteUrl` provient exclusivement de `NUXT_PUBLIC_SITE_URL` via
  `runtimeConfig.public.siteUrl` : **jamais** dérivé du header HTTP Host.
- Le canonical est absolu, sans query ni fragment (les paramètres UTM
  sont retirés dans `normalizeCanonicalPath`).
- Une page `noindex` n’émet **pas** de balise canonical (éviter le
  signal ambigu à un moteur qui l’aurait crawlée par accident).
- L’absence d’image bascule `twitter:card` de `summary_large_image` à
  `summary`. Ne pas publier de `og:image` fictive.
- `useSeoMeta` reste le point d’entrée, `useServerSeoMeta` est déprécié.
- Ne pas appeler `usePageSeo` depuis plusieurs composants d’une même page.
- Une page dynamique doit attendre ses données avec `useFetch` ou
  `useAsyncData` avant de définir ses métadonnées.
- Une ressource absente doit générer une vraie erreur 404 avec `createError`.
- Le graphe Schema.org global (Organization + WebSite) est injecté
  automatiquement par [`useSiteSchema`](../apps/web/app/composables/useSiteSchema.ts)
  dans le layout par défaut ; ne pas le dupliquer dans les pages.

### Exemple d’une page statique

```vue
<script setup lang="ts">
usePageSeo({
  title: 'Agence digitale pour les entreprises',
  description: 'Devzair conçoit des sites, applications et stratégies de visibilité adaptés aux entreprises.',
  path: '/',
})
</script>

<template>
  <main>
    <h1>Des solutions digitales complètes pour votre entreprise</h1>
  </main>
</template>
```

### Exemple futur d’un article Symfony

```vue
<script setup lang="ts">
const route = useRoute()
const slug = computed(() => String(route.params.slug))

const { data: article, error } = await useFetch(
  () => `/api/articles/${encodeURIComponent(slug.value)}`,
  {
    key: () => `article:${slug.value}`,
  },
)

if (error.value || !article.value) {
  throw createError({
    statusCode: 404,
    statusMessage: 'Article introuvable',
  })
}

usePageSeo({
  title: article.value.seoTitle || article.value.title,
  description: article.value.metaDescription,
  image: article.value.ogImage,
  path: `/ressources/${article.value.slug}`,
  type: 'article',
})
</script>
```

### Rendu hybride

La stratégie initiale doit être simple :

```ts
// apps/web/nuxt.config.ts
export default defineNuxtConfig({
  routeRules: {
    '/': { prerender: true },
    '/agence': { prerender: true },
    '/methode': { prerender: true },
    '/services': { prerender: true },
    '/services/**': { prerender: true },

    // À conserver en SSR tant que la stratégie de cache du blog
    // et son invalidation ne sont pas définies.
    '/ressources/**': { ssr: true },
  },
})
```

Règles :

- pré-rendre les pages marketing stables ;
- conserver le SSR pour les pages dynamiques au début ;
- n’activer SWR ou ISR qu’avec une durée, une invalidation et une stratégie de fraîcheur documentées ;
- ne jamais mettre en cache une réponse personnalisée ou privée ;
- toutes les pages pré-rendues doivent être accessibles par de vrais liens HTML ou déclarées explicitement.

**État actuel (Phase 5D close, 2026-08-02).** Seule `/` est pré-rendue :
elle n'a aucune donnée dynamique (les 8 sections proviennent de configs
typées locales — `expertise-pillars.ts`, `project-process.ts`,
`trust-promises.ts`). La page interne `/design-preview` a été supprimée
à la clôture de Phase 5D (accueil complet, coverage utile migrée dans
`test/e2e/home-structure.spec.ts`). Les autres routes marketing seront
ajoutées à `routeRules` au fil des phases suivantes lorsqu'elles
existeront réellement.

### Modules SEO

Ne pas ajouter de module avant que le socle Nuxt passe `build`, `typecheck` et `lint`.

**État actuel (Phase 4 close, 2026-08-02).** Après évaluation du bundle
`@nuxtjs/seo`, l’option retenue est l’installation « à la carte » (cf.
[ADR-004](adr/ADR-004-modules-seo.md)) :

- `@nuxtjs/robots` v5.7 — X-Robots-Tag global, meta robots, robots.txt
  dynamique, règles OAI-SearchBot / GPTBot ;
- `@nuxtjs/sitemap` v7.6 — sitemap.xml généré à partir des routes Nuxt,
  exclusion automatique des pages noindex ;
- Schema.org (Organization + WebSite) injecté à la main par
  `app/composables/useSiteSchema.ts`, appelé une seule fois dans le
  layout par défaut.

Modules **différés** (à réévaluer en Phase 9) : `nuxt-schema-org` (utile
à partir de 3 types d’entités), `nuxt-link-checker` (bénéfice nul sur
2 routes), `nuxt-seo-utils`, bundle `@nuxtjs/seo`, `nuxt-og-image`
(préférence pour une image OG statique validée).

Avant l’ajout d’un nouveau module SEO :

- contrôler la compatibilité avec la version verrouillée de Nuxt ;
- consulter les changements de version ;
- vérifier le HTML réellement généré ;
- ne pas accepter une configuration automatique sans test ;
- ne pas ajouter d’autres modules couvrant le même besoin ;
- documenter la décision par un ADR dans `docs/adr/`.

### Checklist SEO pour chaque page

- [ ] Une intention principale claire.
- [ ] Un `<title>` unique.
- [ ] Une meta description unique.
- [ ] Une canonical absolue et correcte.
- [ ] Un seul H1 visible.
- [ ] Une structure H2/H3 logique.
- [ ] Le contenu essentiel est rendu côté serveur.
- [ ] Les liens sont de vrais `<a href>`.
- [ ] L’image sociale existe et est accessible.
- [ ] Le statut d’indexation est volontaire.
- [ ] Les données structurées correspondent au contenu visible.
- [ ] La page retourne le bon code HTTP.
- [ ] Aucun paramètre de tracking dans la canonical.
- [ ] Aucun contenu important uniquement après `onMounted`.
- [ ] Le HTML est contrôlé avec `curl` ou une inspection équivalente.

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
