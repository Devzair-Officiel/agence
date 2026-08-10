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

test.describe("Politique crawlers IA (robots.txt, DEC-096 + DEC-101)", () => {
  // Ces tests inspectent les six agents que nous configurons explicitement :
  // OAI-SearchBot, GPTBot, Claude-SearchBot, ClaudeBot, PerplexityBot
  // (DEC-096) et Google-Extended (DEC-101).
  //
  // Contexte : `@nuxtjs/robots` bascule automatiquement `robots.txt` sur
  // `User-agent: * / Disallow: /` lorsque `indexable=false`. Les groupes
  // par agent ne sont donc visibles qu'en mode indexable (production).
  // Quand la suite tourne en préproduction, ces tests détectent le mode
  // via le préambule `# START nuxt-robots (indexing disabled)` et se
  // marquent `skip` — la politique reste vérifiée par
  // `apps/web/nuxt.config.ts` et le test `robots.txt bloque tous les
  // crawlers` couvre le contrat de préproduction.
  //
  // Les fetchers user-triggered (ChatGPT-User, Claude-User, Perplexity-User)
  // ne sont volontairement pas testés : ils n'ont pas de groupe dédié et
  // suivent la politique publique globale du site.

  function extractGroup(body: string, userAgent: string): string {
    const escaped = userAgent.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    const match = body.match(
      new RegExp(
        `User-agent:\\s*${escaped}\\s*\\n([\\s\\S]*?)(?=\\n\\s*User-agent:|\\n\\s*Sitemap:|$)`,
        'i',
      ),
    )
    return match ? match[1]! : ''
  }

  function isIndexingDisabled(body: string): boolean {
    return /indexing disabled/i.test(body)
  }

  test('OAI-SearchBot est configuré en allow avec exclusion /admin', async ({
    request,
  }) => {
    const response = await request.get('/robots.txt')
    const body = await response.text()
    test.skip(isIndexingDisabled(body), 'robots.txt en mode blocage global')
    const group = extractGroup(body, 'OAI-SearchBot')
    expect(group, 'groupe OAI-SearchBot présent').not.toBe('')
    expect(group).toMatch(/Allow:\s*\/\s*$/im)
    expect(group).toMatch(/Disallow:\s*\/admin/i)
  })

  test('GPTBot est configuré en disallow complet', async ({ request }) => {
    const response = await request.get('/robots.txt')
    const body = await response.text()
    test.skip(isIndexingDisabled(body), 'robots.txt en mode blocage global')
    const group = extractGroup(body, 'GPTBot')
    expect(group, 'groupe GPTBot présent').not.toBe('')
    expect(group).toMatch(/Disallow:\s*\/\s*$/im)
  })

  test('Claude-SearchBot est configuré en allow avec exclusion /admin', async ({
    request,
  }) => {
    const response = await request.get('/robots.txt')
    const body = await response.text()
    test.skip(isIndexingDisabled(body), 'robots.txt en mode blocage global')
    const group = extractGroup(body, 'Claude-SearchBot')
    expect(group, 'groupe Claude-SearchBot présent').not.toBe('')
    expect(group).toMatch(/Allow:\s*\/\s*$/im)
    expect(group).toMatch(/Disallow:\s*\/admin/i)
  })

  test('ClaudeBot est configuré en disallow complet', async ({ request }) => {
    const response = await request.get('/robots.txt')
    const body = await response.text()
    test.skip(isIndexingDisabled(body), 'robots.txt en mode blocage global')
    const group = extractGroup(body, 'ClaudeBot')
    expect(group, 'groupe ClaudeBot présent').not.toBe('')
    expect(group).toMatch(/Disallow:\s*\/\s*$/im)
  })

  test('PerplexityBot est configuré en allow avec exclusion /admin', async ({
    request,
  }) => {
    const response = await request.get('/robots.txt')
    const body = await response.text()
    test.skip(isIndexingDisabled(body), 'robots.txt en mode blocage global')
    const group = extractGroup(body, 'PerplexityBot')
    expect(group, 'groupe PerplexityBot présent').not.toBe('')
    expect(group).toMatch(/Allow:\s*\/\s*$/im)
    expect(group).toMatch(/Disallow:\s*\/admin/i)
  })

  test('Google-Extended est configuré en disallow complet (DEC-101)', async ({
    request,
  }) => {
    // Le token Google-Extended contrôle l'entraînement Gemini et le grounding
    // dans Gemini Apps / Vertex AI. Il n'affecte NI l'inclusion NI le ranking
    // Google Search (documentation officielle Google). Devzair refuse ce
    // bundle pour rester cohérent avec le refus GPTBot / ClaudeBot.
    const response = await request.get('/robots.txt')
    const body = await response.text()
    test.skip(isIndexingDisabled(body), 'robots.txt en mode blocage global')
    const group = extractGroup(body, 'Google-Extended')
    expect(group, 'groupe Google-Extended présent').not.toBe('')
    expect(group).toMatch(/Disallow:\s*\/\s*$/im)
  })

  test("aucun groupe n'est déclaré pour les agents non retenus", async ({
    request,
  }) => {
    // Ces user-agents ne doivent PAS apparaître dans robots.txt : soit ils
    // sont user-triggered et ignorent robots.txt (ChatGPT-User, Claude-User,
    // Perplexity-User), soit leur politique reste inchangée (CCBot). Les
    // agents anthropic-ai / Claude-Web ne sont pas documentés officiellement.
    // Google-Extended a été retiré de cette liste — DEC-101 le déclare
    // explicitement en Disallow /.
    const response = await request.get('/robots.txt')
    const body = await response.text()
    for (const unwanted of [
      'ChatGPT-User',
      'Claude-User',
      'Perplexity-User',
      'CCBot',
      'anthropic-ai',
      'Claude-Web',
    ]) {
      expect(
        new RegExp(`User-agent:\\s*${unwanted}\\b`, 'i').test(body),
        `${unwanted} ne doit pas apparaître dans robots.txt`,
      ).toBe(false)
    }
  })

  test('en mode indexable=false, le blocage global est prioritaire', async ({
    request,
  }) => {
    // En préproduction, `@nuxtjs/robots` remplace la configuration par
    // groupes par un blocage total (`User-agent: * / Disallow: /`) — c'est
    // le comportement attendu. Ce test verrouille ce contrat côté
    // préproduction et se marque `skip` en indexable pour ne pas dupliquer
    // les assertions du groupe précédent.
    const response = await request.get('/robots.txt')
    const body = await response.text()
    test.skip(!isIndexingDisabled(body), 'robots.txt en mode indexable=true')
    expect(body).toMatch(/User-agent:\s*\*/i)
    expect(body).toMatch(/Disallow:\s*\/\s*$/im)
    expect(body).not.toMatch(/User-agent:\s*OAI-SearchBot/i)
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
