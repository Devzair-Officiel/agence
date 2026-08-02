/**
 * Identité du site Devzair.
 *
 * Source de vérité unique pour le nom, la baseline, l'URL publique et les
 * coordonnées. Consommée par les composants layout, le composable usePageSeo
 * et le graphe Schema.org global.
 *
 * Règle stricte : aucune valeur fictive. `contact` reste null tant qu'aucune
 * coordonnée n'a été validée. `legalName`, `defaultOgImage` et `socialProfiles`
 * suivent la même règle — null ou vide tant que non confirmés.
 *
 * `url` n'est PAS lu ici : la valeur runtime provient de `runtimeConfig.public.siteUrl`,
 * qui est validée par utils/site-url.ts. Le champ existe dans l'interface pour
 * documenter le contrat, mais la valeur d'exécution passe par `useRuntimeConfig()`.
 */

export interface SiteContact {
  readonly email: string | null
  readonly phone: string | null
  readonly city: string | null
}

export interface SiteConfig {
  readonly name: string
  readonly legalName: string | null
  readonly tagline: string
  readonly description: string
  readonly language: string
  readonly locale: string
  readonly defaultTitle: string
  readonly titleTemplate: string
  readonly defaultOgImage: string | null
  readonly contact: SiteContact
  readonly socialProfiles: readonly string[]
}

export const site: SiteConfig = {
  name: "Devzair",
  legalName: null,
  tagline: "Agence digitale à taille humaine",
  description:
    "Sites internet, applications métier, identité visuelle, contenus professionnels et référencement, orchestrés pour construire une présence digitale cohérente et évolutive.",
  language: "fr",
  locale: "fr_FR",
  defaultTitle: "Devzair — Agence digitale",
  titleTemplate: "%s | Devzair",
  defaultOgImage: null,
  contact: {
    email: null,
    phone: null,
    city: null,
  },
  socialProfiles: [],
}
