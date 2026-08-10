import { describe, expect, it } from 'vitest'
import { robotsGroups } from '../../../app/config/robots'

// Preuve automatisée du contrat `indexable=true` (Phase 10B / DEC-096).
//
// `@nuxtjs/robots` produit `robots.txt` à partir de `robotsGroups` uniquement
// lorsque `site.indexable=true`. En préproduction (`indexable=false`), le
// module remplace tout par `User-agent: * / Disallow: /` — comportement
// vérifié par la suite Playwright.
//
// Ce test inspecte la source de vérité (`app/config/robots.ts`) sans démarrer
// un second serveur Nuxt : la config passée au module est déterministe, donc
// tester la config prouve mécaniquement le contenu généré.

describe("Politique crawlers IA (config source, DEC-096)", () => {
  it("expose exactement six groupes (wildcard admin + cinq agents IA)", () => {
    expect(robotsGroups).toHaveLength(6)
  })

  it("verrouille /admin pour le wildcard (défense en profondeur ADR-012)", () => {
    const wildcard = robotsGroups.find((group) =>
      group.userAgent.includes("*"),
    )
    expect(wildcard, "groupe wildcard *").toBeDefined()
    expect(wildcard!.disallow).toEqual(["/admin", "/admin/"])
    // Le wildcard ne doit pas exposer un Allow global : le contrôle
    // d'indexation reste piloté par @nuxtjs/robots (indexable=true → aucun
    // Disallow: /, indexable=false → Disallow: / imposé par le module).
    expect(wildcard!.allow ?? []).toEqual([])
  })

  it("configure OAI-SearchBot en Allow / avec exclusion /admin", () => {
    const group = robotsGroups.find((entry) =>
      entry.userAgent.includes("OAI-SearchBot"),
    )
    expect(group, "groupe OAI-SearchBot").toBeDefined()
    expect(group!.allow).toEqual(["/"])
    expect(group!.disallow).toEqual(["/admin", "/admin/"])
  })

  it("configure GPTBot en Disallow / (aucun Allow, aucun override)", () => {
    const group = robotsGroups.find((entry) =>
      entry.userAgent.includes("GPTBot"),
    )
    expect(group, "groupe GPTBot").toBeDefined()
    expect(group!.disallow).toEqual(["/"])
    expect(group!.allow ?? []).toEqual([])
  })

  it("configure Claude-SearchBot en Allow / avec exclusion /admin", () => {
    const group = robotsGroups.find((entry) =>
      entry.userAgent.includes("Claude-SearchBot"),
    )
    expect(group, "groupe Claude-SearchBot").toBeDefined()
    expect(group!.allow).toEqual(["/"])
    expect(group!.disallow).toEqual(["/admin", "/admin/"])
  })

  it("configure ClaudeBot en Disallow / (aucun Allow, aucun override)", () => {
    const group = robotsGroups.find((entry) =>
      entry.userAgent.includes("ClaudeBot"),
    )
    expect(group, "groupe ClaudeBot").toBeDefined()
    expect(group!.disallow).toEqual(["/"])
    expect(group!.allow ?? []).toEqual([])
  })

  it("configure PerplexityBot en Allow / avec exclusion /admin", () => {
    const group = robotsGroups.find((entry) =>
      entry.userAgent.includes("PerplexityBot"),
    )
    expect(group, "groupe PerplexityBot").toBeDefined()
    expect(group!.allow).toEqual(["/"])
    expect(group!.disallow).toEqual(["/admin", "/admin/"])
  })

  it("ne déclare aucun agent volontairement absent (fetchers, Google-Extended, CCBot)", () => {
    // Ces agents ne doivent pas apparaître (DEC-096) :
    //   - user-triggered fetchers → robots.txt ne s'applique pas ;
    //   - Google-Extended → décision DEFERRED (arbitrage grounding vs training) ;
    //   - CCBot → politique UNCHANGED (crawler généraliste) ;
    //   - anthropic-ai / Claude-Web → non documentés officiellement.
    const declaredAgents = robotsGroups.flatMap((group) => group.userAgent)
    for (const unwanted of [
      "ChatGPT-User",
      "Claude-User",
      "Perplexity-User",
      "Google-Extended",
      "CCBot",
      "anthropic-ai",
      "Claude-Web",
    ]) {
      expect(
        declaredAgents,
        `${unwanted} ne doit pas apparaître dans la config`,
      ).not.toContain(unwanted)
    }
  })

  it("respecte l'ordre wildcard-en-tête (compatibilité parseurs stricts)", () => {
    // Certains parseurs anciens exigent que le groupe wildcard apparaisse en
    // premier avant les user-agents spécifiques. Le contrat de sortie de
    // `@nuxtjs/robots` reproduit l'ordre de la config source.
    expect(robotsGroups[0]!.userAgent).toEqual(["*"])
  })
})
