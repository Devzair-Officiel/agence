// Politique crawlers documentée pour @nuxtjs/robots — Phase 10B / DEC-096 + DEC-101.
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
// Google-Extended (DEC-101) : Disallow /. Un unique token Google bundle
// à la fois l'entraînement des futures générations Gemini ET le grounding
// dans Gemini Apps / Vertex AI (source officielle Google : Google-Extended
// n'affecte NI l'inclusion, NI le ranking Google Search). Refuser
// Google-Extended reste cohérent avec le refus GPTBot / ClaudeBot pour
// l'entraînement, sans altérer la visibilité Google Search classique ni
// les fonctionnalités génératives (AI Overviews, AI Mode) qui dépendent
// du Search index et de Googlebot.
//
// Agents volontairement absents (voir DEC-096) :
//   - ChatGPT-User, Claude-User, Perplexity-User (fetchers user-triggered,
//     robots.txt ne s'applique pas) ;
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
  { userAgent: ['Google-Extended'], disallow: ['/'] },
]
