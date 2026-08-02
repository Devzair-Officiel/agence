/**
 * useSiteSchema — injecte le graphe Schema.org global du site.
 *
 * Deux entités seulement pour l'instant, cohérentes avec les données validées :
 *   - Organization : identité de l'agence (nom, URL, description, logo si présent)
 *   - WebSite : le site public (URL, langue, éditeur = Organization via @id)
 *
 * Règles :
 *   - à appeler UNE fois dans le layout par défaut ; jamais dans une page ni
 *     un composant enfant, sinon duplication du JSON-LD ;
 *   - aucune donnée fictive : address, phone, sameAs, aggregateRating, employees,
 *     founder ne sont ajoutés que si validés dans app/config/site.ts ;
 *   - pas de SearchAction : aucune recherche interne réelle n'existe encore ;
 *   - JSON-LD injecté via useHead → présent dans le HTML SSR initial.
 */

import { buildAbsoluteAssetUrl } from "~/utils/canonical"
import { normalizeSiteUrl } from "~/utils/site-url"
import { site } from "~/config/site"

type JsonLdValue = string | number | boolean | null | JsonLdValue[] | { [key: string]: JsonLdValue }

interface OrganizationSchema {
  "@type": "Organization"
  "@id": string
  name: string
  legalName?: string
  url: string
  description: string
  logo?: string
  sameAs?: string[]
  contactPoint?: {
    "@type": "ContactPoint"
    contactType: "customer support"
    email?: string
    telephone?: string
  }
}

interface WebSiteSchema {
  "@type": "WebSite"
  "@id": string
  url: string
  name: string
  description: string
  inLanguage: string
  publisher: { "@id": string }
}

export function useSiteSchema(): void {
  const config = useRuntimeConfig()
  const origin = normalizeSiteUrl(config.public.siteUrl as string)

  const organizationId = `${origin}/#organization`
  const websiteId = `${origin}/#website`

  const organization: OrganizationSchema = {
    "@type": "Organization",
    "@id": organizationId,
    name: site.name,
    url: origin,
    description: site.description,
  }
  if (site.legalName) organization.legalName = site.legalName
  if (site.defaultOgImage) {
    organization.logo = buildAbsoluteAssetUrl(origin, site.defaultOgImage)
  }
  if (site.socialProfiles.length > 0) {
    organization.sameAs = [...site.socialProfiles]
  }
  if (site.contact.email || site.contact.phone) {
    const contactPoint: NonNullable<OrganizationSchema["contactPoint"]> = {
      "@type": "ContactPoint",
      contactType: "customer support",
    }
    if (site.contact.email) contactPoint.email = site.contact.email
    if (site.contact.phone) contactPoint.telephone = site.contact.phone
    organization.contactPoint = contactPoint
  }

  const website: WebSiteSchema = {
    "@type": "WebSite",
    "@id": websiteId,
    url: origin,
    name: site.name,
    description: site.description,
    inLanguage: site.language,
    publisher: { "@id": organizationId },
  }

  const graph: { "@context": string; "@graph": JsonLdValue[] } = {
    "@context": "https://schema.org",
    "@graph": [organization as unknown as JsonLdValue, website as unknown as JsonLdValue],
  }

  useHead({
    script: [
      {
        type: "application/ld+json",
        // Un id stable permet à useHead de dédupliquer si l'appel se répète.
        id: "devzair-site-schema",
        innerHTML: JSON.stringify(graph),
      },
    ],
  })
}
