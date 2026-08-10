import { describe, expect, it } from 'vitest'
import { robotsGroups } from '../../../app/config/robots'

// Preuve automatisée du contrat `indexable=true` (Phase 10B / DEC-096 + DEC-101).
//
// `@nuxtjs/robots` produit `robots.txt` à partir de `robotsGroups` uniquement
// lorsque `site.indexable=true`. En préproduction (`indexable=false`), le
// module remplace tout par `User-agent: * / Disallow: /` — comportement
// vérifié par la suite Playwright.
//
// Ce test inspecte la source de vérité (`app/config/robots.ts`) sans démarrer
// un second serveur Nuxt : la config passée au module est déterministe, donc
// tester la config prouve mécaniquement le contenu généré.

describe("Politique crawlers IA (config source, DEC-096 + DEC-101)", () => {
  it("expose exactement sept groupes (wildcard admin + cinq agents IA + Google-Extended)", () => {
    expect(robotsGroups).toHaveLength(7)
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

  it("configure Google-Extended en Disallow / (DEC-101, aucun impact Google Search)", () => {
    // Google indique explicitement que Google-Extended ne conditionne ni
    // l'inclusion ni le ranking Google Search — le token contrôle seulement
    // l'entraînement Gemini et le grounding Gemini Apps / Vertex AI.
    const group = robotsGroups.find((entry) =>
      entry.userAgent.includes("Google-Extended"),
    )
    expect(group, "groupe Google-Extended").toBeDefined()
    expect(group!.disallow).toEqual(["/"])
    expect(group!.allow ?? []).toEqual([])
  })

  it("ne déclare aucun agent volontairement absent (fetchers, CCBot, agents non documentés)", () => {
    // Ces agents ne doivent pas apparaître (DEC-096) :
    //   - user-triggered fetchers → robots.txt ne s'applique pas ;
    //   - CCBot → politique UNCHANGED (crawler généraliste) ;
    //   - anthropic-ai / Claude-Web → non documentés officiellement.
    // Google-Extended n'est plus dans cette liste (DEC-101 : Disallow / actif).
    const declaredAgents = robotsGroups.flatMap((group) => group.userAgent)
    for (const unwanted of [
      "ChatGPT-User",
      "Claude-User",
      "Perplexity-User",
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
