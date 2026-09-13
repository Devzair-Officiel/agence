/**
 * Utilitaire TOC (table des matières) pour les articles éditoriaux.
 *
 * `processHeadings` effectue un unique passage regex sur le HTML produit
 * par CommonMark et retourne simultanément :
 *   - le HTML enrichi avec les `id` manquants sur chaque H2/H3 ;
 *   - le tableau `TocEntry[]` strictement synchronisé.
 *
 * Aucun accès au DOM n'est requis : compatible SSR et `useAsyncData`.
 * Les IDs du HTML et les `href="#..."` du sommaire sont produits par la
 * même passe → garantie d'identité sans coordination externe.
 */

export interface TocEntry {
  readonly id: string
  readonly text: string
  readonly level: 2 | 3
}

function slugify(raw: string): string {
  return raw
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/[^a-z0-9\s-]/g, "")
    .trim()
    .replace(/\s+/g, "-")
    .replace(/-{2,}/g, "-")
}

export function processHeadings(html: string): {
  html: string
  toc: readonly TocEntry[]
} {
  const toc: TocEntry[] = []
  const seen = new Map<string, number>()

  const processedHtml = html.replace(
    /<(h[23])([^>]*?)>([\s\S]*?)<\/\1>/gi,
    (match, tag: string, attrs: string, inner: string) => {
      // Heading already has an id — extract for TOC, leave HTML untouched
      if (/\bid=/i.test(attrs)) {
        const existingId = /\bid="([^"]+)"/i.exec(attrs)?.[1] ?? ""
        const text = inner.replace(/<[^>]+>/g, "").trim()
        if (text && existingId) {
          toc.push({ id: existingId, text, level: Number(tag[1]) as 2 | 3 })
        }
        return match
      }

      const text = inner.replace(/<[^>]+>/g, "").trim()
      if (!text) return match

      const base = slugify(text)
      const count = seen.get(base) ?? 0
      const id = count === 0 ? base : `${base}-${count + 1}`
      seen.set(base, count + 1)

      toc.push({ id, text, level: Number(tag[1]) as 2 | 3 })
      return `<${tag} id="${id}"${attrs}>${inner}</${tag}>`
    },
  )

  return { html: processedHtml, toc }
}
