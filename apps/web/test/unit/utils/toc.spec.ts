import { describe, expect, it } from "vitest"
import { processHeadings } from "~/utils/toc"

describe("processHeadings", () => {
  it("injecte un id sur un H2 sans attribut", () => {
    const { html, toc } = processHeadings("<h2>Introduction</h2>")
    expect(html).toBe('<h2 id="introduction">Introduction</h2>')
    expect(toc).toHaveLength(1)
    expect(toc[0]).toEqual({ id: "introduction", text: "Introduction", level: 2 })
  })

  it("injecte un id sur un H3 sans attribut", () => {
    const { html, toc } = processHeadings("<h3>Sous-section</h3>")
    expect(html).toBe('<h3 id="sous-section">Sous-section</h3>')
    expect(toc[0].level).toBe(3)
  })

  it("traite les accents et caractères spéciaux français", () => {
    const { html, toc } = processHeadings("<h2>Création d'un site</h2>")
    expect(toc[0].id).toBe("creation-dun-site")
    expect(html).toContain('id="creation-dun-site"')
  })

  it("traite les points d'interrogation et symboles", () => {
    const { toc } = processHeadings("<h2>Qu'est-ce qu'une agence digitale ?</h2>")
    expect(toc[0].id).toBe("quest-ce-quune-agence-digitale")
  })

  it("déduplique les titres identiques avec suffixe -2, -3", () => {
    const html = "<h2>Section</h2><h2>Section</h2><h2>Section</h2>"
    const { toc } = processHeadings(html)
    expect(toc[0].id).toBe("section")
    expect(toc[1].id).toBe("section-2")
    expect(toc[2].id).toBe("section-3")
  })

  it("préserve les IDs existants et les inclut dans le TOC", () => {
    const html = '<h2 id="deja-la">Titre existant</h2>'
    const { html: out, toc } = processHeadings(html)
    expect(out).toBe('<h2 id="deja-la">Titre existant</h2>')
    expect(toc[0]).toEqual({ id: "deja-la", text: "Titre existant", level: 2 })
  })

  it("mélange H2 et H3 avec les niveaux corrects", () => {
    const html = "<h2>Section A</h2><h3>Sous-section</h3><h2>Section B</h2>"
    const { toc } = processHeadings(html)
    expect(toc).toHaveLength(3)
    expect(toc[0].level).toBe(2)
    expect(toc[1].level).toBe(3)
    expect(toc[2].level).toBe(2)
  })

  it("retourne un HTML identique et un TOC vide si aucun heading", () => {
    const input = "<p>Aucun heading ici.</p>"
    const { html, toc } = processHeadings(input)
    expect(html).toBe(input)
    expect(toc).toHaveLength(0)
  })

  it("préserve le contenu texte des headings inchangé", () => {
    const { html } = processHeadings("<h2>Mon titre complet</h2>")
    expect(html).toContain(">Mon titre complet</h2>")
  })

  it("synchronise strictement les IDs du HTML avec les href du TOC", () => {
    const html = "<h2>Alpha</h2><h3>Beta</h3><h2>Alpha</h2>"
    const { html: out, toc } = processHeadings(html)
    for (const entry of toc) {
      expect(out).toContain(`id="${entry.id}"`)
    }
  })

  it("gère le HTML vide sans erreur", () => {
    const { html, toc } = processHeadings("")
    expect(html).toBe("")
    expect(toc).toHaveLength(0)
  })

  it("ignore les headings h1 et h4+", () => {
    const html = "<h1>Titre principal</h1><h4>Petit titre</h4><h2>Inclus</h2>"
    const { toc } = processHeadings(html)
    expect(toc).toHaveLength(1)
    expect(toc[0].text).toBe("Inclus")
  })

  it("extrait le texte des headings avec balises inline (strong, em, a)", () => {
    const html = "<h2><strong>Titre</strong> en gras</h2>"
    const { toc } = processHeadings(html)
    expect(toc[0].text).toBe("Titre en gras")
    expect(toc[0].id).toBe("titre-en-gras")
  })
})
