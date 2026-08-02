import { expect, test } from '@playwright/test'

// Tests SEO end-to-end sur le HTML SSR (pas après hydratation).
//
// L'environnement CI/E2E démarre le serveur avec les valeurs par défaut
// (NUXT_PUBLIC_SITE_INDEXABLE=false). C'est le mode « safe » : la preprod
// ne peut pas devenir indexable par oubli d'une variable.
//
// On inspecte donc :
//   - la présence des métadonnées communes dans le HTML rendu par le serveur ;
//   - la politique globale (X-Robots-Tag + meta robots) ;
//   - le sitemap et robots.txt générés par @nuxtjs/sitemap et @nuxtjs/robots ;
//   - l'absence de la page interne `/design-preview` (supprimée Phase 5D).
//
// La couverture du mode indexable est faite en tests unitaires
// (usePageSeo.spec.ts), pour éviter de lancer deux serveurs distincts.

async function fetchSSR(request: import('@playwright/test').APIRequestContext, path: string) {
  const response = await request.get(path)
  expect(response.status(), `${path} status`).toBeLessThan(500)
  const body = await response.text()
  return { response, body }
}

test.describe('SEO SSR — HTML initial', () => {
  test('la balise <html> déclare lang="fr"', async ({ request }) => {
    const { body } = await fetchSSR(request, '/')
    expect(body).toMatch(/<html[^>]*\blang="fr"/i)
  })

  test('la page / expose title, description et meta robots (noindex en preprod)', async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, '/')
    expect(body).toMatch(/<title>[^<]*Devzair[^<]*<\/title>/i)
    expect(body).toMatch(/<meta[^>]+name="description"[^>]+content="[^"]+"/i)
    expect(body).toMatch(/<meta[^>]+name="robots"[^>]+content="[^"]*noindex[^"]*"/i)
  })

  test('la page / expose les balises Open Graph absolues', async ({ request }) => {
    const { body } = await fetchSSR(request, '/')
    // og:url doit être absolue.
    const ogUrlMatch = body.match(
      /<meta[^>]+property="og:url"[^>]+content="(https?:\/\/[^"]+)"/i,
    )
    expect(ogUrlMatch, 'og:url absolue').not.toBeNull()

    expect(body).toMatch(/<meta[^>]+property="og:title"[^>]+content="[^"]+"/i)
    expect(body).toMatch(/<meta[^>]+property="og:description"[^>]+content="[^"]+"/i)
    expect(body).toMatch(/<meta[^>]+property="og:site_name"[^>]+content="Devzair"/i)
    expect(body).toMatch(/<meta[^>]+property="og:locale"[^>]+content="fr_FR"/i)
  })

  test('le graphe Schema.org Organization + WebSite est présent dans le HTML SSR', async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, '/')
    const jsonLd = body.match(
      /<script[^>]+type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/i,
    )
    expect(jsonLd, 'JSON-LD injecté par useSiteSchema').not.toBeNull()

    const parsed = JSON.parse(jsonLd![1])
    expect(parsed['@context']).toBe('https://schema.org')
    const types = (parsed['@graph'] as Array<{ '@type': string }>).map((n) => n['@type'])
    expect(types).toContain('Organization')
    expect(types).toContain('WebSite')

    const organization = parsed['@graph'].find(
      (n: { '@type': string }) => n['@type'] === 'Organization',
    )
    expect(organization.name).toBe('Devzair')
    expect(organization.url).toMatch(/^https?:\/\//)
    // Aucune donnée fictive ne doit apparaître.
    expect(organization).not.toHaveProperty('aggregateRating')
    expect(organization).not.toHaveProperty('address')
  })

  test('/design-preview n\'est plus servi (supprimée Phase 5D)', async ({
    request,
  }) => {
    // La page interne de vérification visuelle a été retirée à la clôture
    // du lot design system. Elle doit répondre 404, jamais un HTML de page.
    const response = await request.get('/design-preview')
    expect(response.status()).toBe(404)
  })
})

test.describe('Politique d\'indexation en mode non-indexable', () => {
  test('l\'en-tête HTTP X-Robots-Tag impose noindex globalement', async ({
    request,
  }) => {
    const response = await request.get('/')
    const header = response.headers()['x-robots-tag']
    expect(header, 'X-Robots-Tag').toBeDefined()
    expect(header!.toLowerCase()).toMatch(/noindex/)
    expect(header!.toLowerCase()).toMatch(/nofollow/)
  })

  test('robots.txt bloque tous les crawlers', async ({ request }) => {
    const response = await request.get('/robots.txt')
    expect(response.status()).toBe(200)
    const body = await response.text()
    expect(body).toMatch(/User-agent:\s*\*/i)
    expect(body).toMatch(/Disallow:\s*\//)
  })
})

test.describe('Sitemap', () => {
  test('/sitemap.xml répond en 200 avec un contenu XML', async ({ request }) => {
    const response = await request.get('/sitemap.xml')
    expect(response.status()).toBe(200)
    const contentType = response.headers()['content-type'] ?? ''
    expect(contentType).toMatch(/xml/i)
  })

  test('le sitemap expose des URLs absolues', async ({ request }) => {
    const response = await request.get('/sitemap.xml')
    const body = await response.text()
    expect(body).toMatch(/<loc>\s*https?:\/\/[^<]+<\/loc>/i)
  })

  test('le sitemap n\'inclut pas /design-preview', async ({ request }) => {
    const response = await request.get('/sitemap.xml')
    const body = await response.text()
    expect(body).not.toMatch(/design-preview/)
  })
})
