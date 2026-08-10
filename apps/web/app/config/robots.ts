// Politique crawlers documentée pour @nuxtjs/robots — Phase 10B / DEC-096.
//
// Une seule source de vérité pour :
//   - la configuration passée au module dans `nuxt.config.ts` ;
//   - le test unitaire garde-fou `test/unit/config/robots.spec.ts` qui
//     vérifie le contrat `indexable=true`.
//
// @nuxtjs/robots remplace automatiquement ces groupes par un blocage
// global (`User-agent: * / Disallow: /`) lorsque `site.indexable=false`.
// Le contrat de préproduction est donc couvert par la suite Playwright ;
// le contrat de production est couvert par le test unitaire ci-dessus.
//
// Agents volontairement absents (voir DEC-096) :
//   - ChatGPT-User, Claude-User, Perplexity-User (fetchers user-triggered,
//     robots.txt ne s'applique pas) ;
//   - Google-Extended (décision DEFERRED : couvre à la fois entraînement
//     Gemini et grounding Gemini Apps/Vertex) ;
//   - CCBot (politique UNCHANGED : Common Crawl n'est pas exclusivement
//     un crawler d'entraînement) ;
//   - anthropic-ai, Claude-Web (non documentés officiellement par
//     Anthropic à ce jour).

export interface RobotsGroup {
  userAgent: string[]
  allow?: string[]
  disallow?: string[]
}

// Signature typée pour ré-exportation. Les tests unitaires vérifient
// l'invariance de contenu ; la mutation accidentelle est empêchée par
// `const` et par l'absence d'accès en écriture ailleurs dans le code.
export const robotsGroups: RobotsGroup[] = [
  { userAgent: ['*'], disallow: ['/admin', '/admin/'] },
  { userAgent: ['OAI-SearchBot'], allow: ['/'], disallow: ['/admin', '/admin/'] },
  { userAgent: ['GPTBot'], disallow: ['/'] },
  { userAgent: ['Claude-SearchBot'], allow: ['/'], disallow: ['/admin', '/admin/'] },
  { userAgent: ['ClaudeBot'], disallow: ['/'] },
  { userAgent: ['PerplexityBot'], allow: ['/'], disallow: ['/admin', '/admin/'] },
]
