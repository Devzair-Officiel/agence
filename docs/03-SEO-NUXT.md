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

Créer un seul composable pour les métadonnées communes afin d’éviter la répétition :

```ts
// apps/web/app/composables/usePageSeo.ts
export interface PageSeoInput {
  title: string
  description: string
  image?: string
  canonicalPath?: string
  robots?: 'index, follow' | 'noindex, follow' | 'noindex, nofollow'
  type?: 'website' | 'article'
}

export function usePageSeo(input: PageSeoInput) {
  const route = useRoute()
  const config = useRuntimeConfig()

  const canonicalUrl = computed(() => {
    const path = input.canonicalPath ?? route.path
    return new URL(path, config.public.siteUrl).toString()
  })

  const imageUrl = computed(() => new URL(
    input.image ?? '/images/og/default.jpg',
    config.public.siteUrl,
  ).toString())

  useSeoMeta({
    title: input.title,
    description: input.description,
    robots: input.robots ?? 'index, follow',
    ogTitle: input.title,
    ogDescription: input.description,
    ogType: input.type ?? 'website',
    ogUrl: () => canonicalUrl.value,
    ogImage: () => imageUrl.value,
    twitterCard: 'summary_large_image',
    twitterTitle: input.title,
    twitterDescription: input.description,
    twitterImage: () => imageUrl.value,
  })

  useHead({
    link: [
      {
        rel: 'canonical',
        href: () => canonicalUrl.value,
      },
    ],
  })
}
```

### Règles du composable

- `siteUrl` doit être une URL absolue définie dans `runtimeConfig.public`.
- `route.path` est préféré à `route.fullPath` afin d’exclure les paramètres de tracking.
- Les images sociales doivent être absolues.
- Les titres, descriptions et images restent propres à chaque page.
- Ne pas utiliser `useServerSeoMeta` dans le nouveau code : il est déprécié ; utiliser `useSeoMeta`, éventuellement dans `if (import.meta.server)` pour des métadonnées statiques.
- Ne pas appeler `usePageSeo` depuis plusieurs composants d’une même page.
- Une page dynamique doit attendre ses données avec `useFetch` ou `useAsyncData` avant de définir ses métadonnées.
- Une ressource absente doit générer une vraie erreur 404 avec `createError`.

### Exemple d’une page statique

```vue
<script setup lang="ts">
usePageSeo({
  title: 'Agence digitale pour les entreprises',
  description: 'Devzair conçoit des sites, applications et stratégies de visibilité adaptés aux entreprises.',
  canonicalPath: '/',
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
  canonicalPath: `/ressources/${article.value.slug}`,
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

### Modules SEO

Ne pas ajouter de module avant que le socle Nuxt passe `build`, `typecheck` et `lint`.

Après ce point de contrôle, la solution recommandée est d’évaluer puis d’installer le module consolidé **Nuxt SEO** :

```bash
docker compose exec web npx nuxt module add seo
```

Il peut centraliser notamment :

- la configuration du site ;
- le sitemap XML ;
- robots.txt ;
- Schema.org ;
- les images Open Graph ;
- les utilitaires SEO.

Avant validation :

- contrôler la compatibilité avec la version verrouillée de Nuxt ;
- consulter les changements de version ;
- vérifier le HTML réellement généré ;
- ne pas accepter une configuration automatique sans test ;
- ne pas ajouter d’autres modules couvrant le même besoin.

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
