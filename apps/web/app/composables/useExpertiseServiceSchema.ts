/**
 * useExpertiseServiceSchema — injecte un JSON-LD `Service` par page fille
 * `/expertises/{slug}`.
 *
 * Rôle :
 *   - émettre UN bloc `application/ld+json` supplémentaire, en plus du
 *     graphe global émis par `useSiteSchema` au niveau du layout ;
 *   - référencer l'`Organization` globale via son `@id`
 *     (`${origin}/#organization`), au lieu de dupliquer le nom, la
 *     description et l'URL de l'agence dans chaque service.
 *
 * Règles strictes (règle 1 du référentiel : ne rien inventer) :
 *   - PAS de `offers`, `price`, `priceRange` ni `priceSpecification` — nous
 *     ne publions aucun tarif ;
 *   - PAS de `aggregateRating` ni `review` — nous n'avons pas d'avis
 *     collectés publiés ;
 *   - PAS de `areaServed` fictif — nous laissons Schema.org sans
 *     restriction géographique par défaut ;
 *   - PAS de `serviceOutput` inventé — nous nous limitons aux livrables
 *     réellement listés sur la page.
 *
 * Contrat d'appel : la page fille passe un `title`, une `description`, un
 * `path` (`/expertises/{slug}`) et un `serviceType` (libellé court du
 * pôle, ex. « Développement web »). Le composable construit l'URL absolue
 * canonique et l'`@id` unique du service.
 */

import { buildCanonical } from "~/utils/canonical"
import { normalizeSiteUrl } from "~/utils/site-url"

export interface ExpertiseServiceSchemaInput {
  /** Nom du service (H1 de la page). */
  readonly title: string
  /** Description courte (meta description ou introduction condensée). */
  readonly description: string
  /** Chemin canonique de la page (`/expertises/{slug}`). */
  readonly path: string
  /** Type de service concis (libellé court du pôle, ex. « SEO »). */
  readonly serviceType: string
}

interface ServiceSchema {
  "@context": "https://schema.org"
  "@type": "Service"
  "@id": string
  name: string
  description: string
  url: string
  serviceType: string
  provider: { "@id": string }
}

export function useExpertiseServiceSchema(input: ExpertiseServiceSchemaInput): void {
  const config = useRuntimeConfig()
  const siteUrl = config.public.siteUrl as string
  const origin = normalizeSiteUrl(siteUrl)

  const url = buildCanonical({ siteUrl, path: input.path })
  const serviceId = `${url}#service`
  const organizationId = `${origin}/#organization`

  const service: ServiceSchema = {
    "@context": "https://schema.org",
    "@type": "Service",
    "@id": serviceId,
    name: input.title,
    description: input.description,
    url,
    serviceType: input.serviceType,
    provider: { "@id": organizationId },
  }

  useHead({
    script: [
      {
        type: "application/ld+json",
        // Un id stable par service permet à useHead de dédupliquer les
        // occurrences si un composant enfant relance l'appel — mais la
        // convention reste : une seule invocation, dans la page.
        id: `devzair-service-schema-${input.path}`,
        innerHTML: JSON.stringify(service),
      },
    ],
  })
}
