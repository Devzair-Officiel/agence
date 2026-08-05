import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ResourceContent from "~/components/resources/ResourceContent.vue"

/**
 * ResourceContent est la SEULE frontière de confiance autorisée à
 * utiliser `v-html` (ADR-011). Les tests documentent ce contrat :
 *   - le HTML fourni est bien rendu (élément produit par CommonMark) ;
 *   - il est confiné à un unique conteneur `.resource-content` ;
 *   - le composant n'accepte AUCUN autre input HTML — seul `contentHtml`
 *     traverse la frontière.
 *
 * La sécurité effective de `contentHtml` est assurée en amont côté
 * Symfony (`MarkdownSecurityPolicy`, ADR-010). Ce test-là vérifie la
 * discipline de rendu, pas la neutralisation d'entrées malveillantes
 * (qui n'ont pas leur place ici : `contentHtml` est déjà sûr).
 */

describe("ResourceContent (frontière de confiance HTML éditoriale)", () => {
  it("rend le HTML fourni par la prop dans un conteneur unique", () => {
    const html = "<h2>Section</h2><p>Paragraphe.</p>"
    const wrapper = mount(ResourceContent, { props: { contentHtml: html } })
    const container = wrapper.find(".resource-content")
    expect(container.exists()).toBe(true)
    expect(container.html()).toContain("<h2>Section</h2>")
    expect(container.html()).toContain("<p>Paragraphe.</p>")
  })

  it("expose exactement UN nœud racine avec la classe .resource-content", () => {
    const wrapper = mount(ResourceContent, { props: { contentHtml: "<p>x</p>" } })
    const roots = wrapper.findAll(".resource-content")
    expect(roots).toHaveLength(1)
  })

  it("préserve les balises typographiques attendues (a, blockquote, pre, table)", () => {
    const html = `
      <p><a href="/x">lien</a></p>
      <blockquote>citation</blockquote>
      <pre><code>bloc</code></pre>
      <table><tr><th>H</th></tr></table>
    `
    const wrapper = mount(ResourceContent, { props: { contentHtml: html } })
    const container = wrapper.find(".resource-content")
    expect(container.find("a").exists()).toBe(true)
    expect(container.find("blockquote").exists()).toBe(true)
    expect(container.find("pre code").exists()).toBe(true)
    expect(container.find("table th").exists()).toBe(true)
  })

  it("gère un contenu vide sans jeter", () => {
    const wrapper = mount(ResourceContent, { props: { contentHtml: "" } })
    expect(wrapper.find(".resource-content").exists()).toBe(true)
  })
})
