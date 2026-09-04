/**
 * useBreadcrumb — injecte un JSON-LD `BreadcrumbList` par page fille
 * disposant d'un fil d'Ariane structuré.
 *
 * Calqué sur `useServiceSchema` — même contrat d'injection via `useHead`.
 *
 * Le JSON-LD BreadcrumbList est distinct du composant visuel `SiteBreadcrumb`
 * qui rend le `<nav>` HTML accessible : l'un est pour les moteurs, l'autre
 * pour les utilisateurs. Les deux sont alimentés par la même liste d'items.
 *
 * Usage : invoquer dans `<script setup>` de la page ou du slug dispatcher,
 * avec les mêmes items passés à `SiteBreadcrumb`. L'ordre des items doit
 * être Accueil → ... → Page courante (dernier item sans `to`).
 */

import { buildCanonical } from "~/utils/canonical"

export interface BreadcrumbSchemaItem {
  /** Libellé affiché et déclaré dans le JSON-LD `name`. */
  readonly label: string
  /** Route Nuxt existante ; omis pour la page courante (dernier item). */
  readonly to?: string
}

interface BreadcrumbListItem {
  "@type": "ListItem"
  position: number
  name: string
  item?: string
}

interface BreadcrumbListSchema {
  "@context": "https://schema.org"
  "@type": "BreadcrumbList"
  itemListElement: BreadcrumbListItem[]
}

export function useBreadcrumb(items: readonly BreadcrumbSchemaItem[]): void {
  const config = useRuntimeConfig()
  const siteUrl = config.public.siteUrl as string

  const schema: BreadcrumbListSchema = {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: items.map((item, index) => {
      const listItem: BreadcrumbListItem = {
        "@type": "ListItem",
        position: index + 1,
        name: item.label,
      }
      if (item.to) {
        listItem.item = buildCanonical({ siteUrl, path: item.to })
      }
      return listItem
    }),
  }

  useHead({
    script: [
      {
        type: "application/ld+json",
        id: "devzair-breadcrumb-schema",
        innerHTML: JSON.stringify(schema),
      },
    ],
  })
}
