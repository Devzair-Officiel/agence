/**
 * Constantes légales Devzair — source de vérité unique.
 *
 * SIREN, SIRET, numéro TVA et hébergeur : données vérifiées.
 * Identité de l'éditeur (nom, adresse, téléphone, directeur de publication) :
 * validée le 2026-09-15 par le responsable légal.
 *
 * Conformité LCEN art. 6-III-1 : les mentions obligatoires pour un éditeur
 * professionnel personne physique (nom, adresse, téléphone) sont renseignées.
 * Ne jamais modifier ces valeurs sans validation explicite — cf. AGENTS.md règle 1.
 */

export interface LegalConfig {
  /** Nom complet de l'éditeur (personne physique ou morale). Null = non validé. */
  readonly publisherName: string | null
  /** Adresse complète de l'éditeur. Null = non validée. */
  readonly publisherAddress: string | null
  /** Numéro de téléphone de l'éditeur. Null = non validé. */
  readonly publisherPhone: string | null
  /** Directeur de la publication. Null = non validé. */
  readonly publicationDirector: string | null

  /** Numéro SIREN (9 chiffres). */
  readonly siren: string
  /** Numéro SIRET (14 chiffres). */
  readonly siret: string
  /** Greffe d'immatriculation. */
  readonly rcs: string
  /** Code NAF / APE. */
  readonly naf: string
  /** Numéro de TVA intracommunautaire. */
  readonly vatNumber: string

  /** Nom de l'hébergeur. */
  readonly hostName: string
  /** Adresse de l'hébergeur. */
  readonly hostAddress: string
  /** Site web de l'hébergeur. */
  readonly hostWebsite: string
}

export const legalConfig: LegalConfig = {
  // Identité éditeur — validée le 2026-09-15.
  publisherName: "AURELIEN BOUDON",
  publisherAddress: "39 avenue Edouard Herriot, Lyon",
  publisherPhone: "06 87 76 37 84",
  publicationDirector: "AURELIEN BOUDON",

  // Données entreprise vérifiées.
  siren: "835 317 413",
  siret: "835 317 413 00036",
  rcs: "RCS Versailles",
  naf: "6201Z",
  vatNumber: "FR28835317413",

  // Hébergeur déclaré.
  hostName: "OVHcloud",
  hostAddress: "2 rue Kellermann, 59100 Roubaix, France",
  hostWebsite: "https://www.ovhcloud.com",
}
