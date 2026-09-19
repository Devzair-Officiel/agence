// Compression gzip des réponses HTML SSR en mode standalone Nitro (sans proxy).
//
// En production réelle, Caddy gère la compression de toutes les réponses.
// En standalone (tests Lighthouse, staging sans proxy), ce plugin applique
// gzip aux réponses `text/html` pour réduire le payload de ~67 KB à ~18 KB.
//
// Comportement :
// - Ne compresse que si le client envoie `Accept-Encoding: gzip`.
// - Ne compresse pas les assets statiques (_nuxt/**) — servis par le static
//   handler de Nitro avec `compressPublicAssets` (voir nuxt.config.ts).
// - Ne compresse pas les erreurs ni les non-HTML (JSON, CSS…).

import { gzipSync } from 'node:zlib'

export default defineNitroPlugin((nitroApp) => {
  nitroApp.hooks.hook('beforeResponse', (event, response) => {
    if (event.path.startsWith('/_nuxt/') || event.path.startsWith('/__')) return

    const contentType = getResponseHeader(event, 'content-type') as string ?? ''
    if (!contentType.includes('text/html')) return

    const acceptEncoding = getRequestHeader(event, 'accept-encoding') ?? ''
    if (!acceptEncoding.includes('gzip')) return

    if (typeof response.body !== 'string' && !Buffer.isBuffer(response.body)) return

    const original = Buffer.isBuffer(response.body)
      ? response.body
      : Buffer.from(response.body as string, 'utf-8')

    const compressed = gzipSync(original, { level: 6 })
    response.body = compressed
    setResponseHeader(event, 'Content-Encoding', 'gzip')
    setResponseHeader(event, 'Content-Length', compressed.byteLength)
    setResponseHeader(event, 'Vary', 'Accept-Encoding')
  })
})
