/**
 * useServiceSchema — injecte un JSON-LD `Service` par page fille
 * `/services/{slug}`.
 *
 * Calqué sur `useExpertiseServiceSchema` — même contrat, même restrictions.
 *
 * Règles strictes (règle 1 du référentiel : ne rien inventer) :
 *   - PAS de `offers`, `price`, `priceRange` ni `priceSpecification` ;
 *   - PAS de `aggregateRating` ni `review` ;
 *   - PAS de `areaServed` fictif ;
 *   - PAS de `serviceOutput` inventé.
 *
 * Usage : une seule invocation par page fille publiée, dans `<script setup>`.
 * La page hub `/services` n'émet pas ce schema (elle n'est pas un service).
 */

import { buildCanonical } from "~/utils/canonical"
import { normalizeSiteUrl } from "~/utils/site-url"

export interface ServiceSchemaInput {
  /** Nom du service (H1 ou titre principal de la page). */
  readonly title: string
  /** Description courte (meta description ou introduction condensée). */
  readonly description: string
  /** Chemin canonique de la page (`/services/{slug}`). */
  readonly path: string
  /** Type de service concis (libellé court, ex. « Référencement naturel »). */
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

export function useServiceSchema(input: ServiceSchemaInput): void {
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
        id: `devzair-service-schema-${input.path}`,
        innerHTML: JSON.stringify(service),
      },
    ],
  })
}
