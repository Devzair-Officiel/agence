import { existsSync, readdirSync, readFileSync } from "node:fs"
import { dirname, resolve } from "node:path"
import { fileURLToPath } from "node:url"
import { beforeAll, describe, expect, it } from "vitest"

/**
 * Garde-fou sur les sources éditoriales du monorepo (`content/resources/` et
 * `scripts/editorial-content-bootstrap.sh`).
 *
 * Deux articles ne sont pas publiés en production (404) et leurs sources sont
 * archivées : aucune source publiable ne doit pointer vers leurs URL, et le
 * bootstrap ne doit pas les republier.
 *
 * Ces fichiers sont hors de `apps/web` : le conteneur `web` ne les monte pas,
 * la suite est donc ignorée en local Docker et exécutée en CI (runner natif,
 * dépôt complet) ou via `npx vitest` lancé hors conteneur.
 */

const repoRoot = resolve(dirname(fileURLToPath(import.meta.url)), "../../../../..")
const resourcesDir = resolve(repoRoot, "content/resources")
const bootstrapPath = resolve(repoRoot, "scripts/editorial-content-bootstrap.sh")

const RETIRED_SLUGS = ["seo-creation-site-internet", "site-vitrine-ou-sur-mesure"] as const

function bootstrapSlugs(script: string): string[] {
  const block = /^SLUGS=\(\n([\s\S]*?)^\)/m.exec(script)
  if (!block) throw new Error("Liste SLUGS introuvable dans le bootstrap")
  return block[1]!
    .split("\n")
    .map((line) => line.trim())
    .filter((line) => line !== "" && !line.startsWith("#"))
}

function readSources(): { name: string; body: string }[] {
  return readdirSync(resourcesDir)
    .filter((name) => name.endsWith(".md") && name !== "README.md")
    .map((name) => ({ name, body: readFileSync(resolve(resourcesDir, name), "utf8") }))
}

describe.skipIf(!existsSync(resourcesDir) || !existsSync(bootstrapPath))(
  "sources éditoriales publiables",
  () => {
    // Lecture différée : le corps d'un describe ignoré est tout de même collecté.
    let sources: { name: string; body: string }[] = []
    let script = ""
    beforeAll(() => {
      sources = readSources()
      script = readFileSync(bootstrapPath, "utf8")
    })

    it("aucune source publiable ne pointe vers un article retiré", () => {
      for (const { name, body } of sources) {
        for (const slug of RETIRED_SLUGS) {
          expect(body, `${name} → /ressources/${slug}`).not.toContain(`/ressources/${slug}`)
        }
      }
    })

    it("les sources des articles retirés ne sont plus dans content/resources", () => {
      const names = sources.map((s) => s.name)
      for (const slug of RETIRED_SLUGS) expect(names).not.toContain(`${slug}.md`)
    })

    it("le bootstrap n'importe ni ne publie les articles retirés", () => {
      const slugs = bootstrapSlugs(script)
      for (const slug of RETIRED_SLUGS) {
        expect(slugs).not.toContain(slug)
        expect(script).not.toMatch(new RegExp(`\\[${slug}\\]=`))
      }
    })

    it("chaque slug du bootstrap a une source et une date de publication", () => {
      const slugs = bootstrapSlugs(script)
      expect(slugs.length).toBeGreaterThan(0)
      for (const slug of slugs) {
        expect(existsSync(resolve(resourcesDir, `${slug}.md`)), slug).toBe(true)
        expect(script, slug).toMatch(new RegExp(`\\[${slug}\\]="\\d{4}-`))
      }
    })

    it("les liens internes vers /ressources/* ciblent une source publiable", () => {
      const slugs = new Set(bootstrapSlugs(script))
      for (const { name, body } of sources) {
        for (const match of body.matchAll(/\]\(\/ressources\/([a-z0-9-]+)\)/g)) {
          expect(slugs.has(match[1]!), `${name} → /ressources/${match[1]}`).toBe(true)
        }
      }
    })
  },
)
