/**
 * Identité du site Devzair.
 * Source de vérité unique pour le nom, la baseline et les coordonnées.
 *
 * `contact` reste explicitement à null tant qu'aucune coordonnée validée n'a été
 * confirmée par le client. Aucune valeur fictive ne doit être introduite ici :
 * les composants doivent gérer proprement le cas nullable.
 */

export interface SiteContact {
  readonly email: string | null
  readonly phone: string | null
  readonly city: string | null
}

export interface SiteConfig {
  readonly name: string
  readonly tagline: string
  readonly description: string
  readonly locale: string
  readonly contact: SiteContact
}

export const site: SiteConfig = {
  name: "Devzair",
  tagline: "Agence digitale à taille humaine",
  description:
    "Sites internet, applications métier, identité visuelle, contenus professionnels et référencement, orchestrés pour construire une présence digitale cohérente et évolutive.",
  locale: "fr-FR",
  contact: {
    email: null,
    phone: null,
    city: null,
  },
}
