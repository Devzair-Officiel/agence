/**
 * Constantes légales Devzair — source de vérité unique.
 *
 * SIREN, SIRET, numéro TVA et hébergeur : données vérifiées.
 * Identité de l'éditeur (nom, adresse, téléphone, directeur de publication) :
 * volontairement `null` jusqu'à validation explicite par le responsable légal.
 * Ne jamais inventer ou deviner ces valeurs — cf. AGENTS.md règle 1.
 *
 * Conformité LCEN art. 6-III-1 : les mentions obligatoires pour un éditeur
 * professionnel personne physique incluent nom, adresse et numéro de téléphone.
 * La conformité reste BLOQUÉE tant que `publisherName` / `publisherAddress` /
 * `publisherPhone` sont null.
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
  // Identité éditeur — à renseigner lors de la validation légale finale.
  publisherName: null,
  publisherAddress: null,
  publisherPhone: null,
  publicationDirector: null,

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
