// Options UX pour l'étape Contenu (ContentNeed).
// Trois sections indépendantes : identité visuelle, contenu textuel, photographie.
// Codes alignés sur ContentNeed.php.

export type IdentityStatus = "ready" | "partial" | "needed"
export type ContentStatus = "ready" | "with_help" | "to_write"

export interface IdentityOption {
  readonly value: IdentityStatus
  readonly label: string
  readonly description: string
}

export interface ContentTextOption {
  readonly value: ContentStatus
  readonly label: string
  readonly description: string
}

export const IDENTITY_OPTIONS: readonly IdentityOption[] = [
  {
    value: "ready",
    label: "Mon identité est prête",
    description: "Logo, couleurs et charte graphique disponibles.",
  },
  {
    value: "partial",
    label: "Elle est partiellement définie",
    description: "Quelques éléments existent, mais la charte est incomplète.",
  },
  {
    value: "needed",
    label: "Je pars de zéro",
    description: "Création complète de l'identité visuelle à prévoir.",
  },
]

export const CONTENT_TEXT_OPTIONS: readonly ContentTextOption[] = [
  {
    value: "ready",
    label: "Mon contenu est fourni",
    description: "Textes, données et médias sont prêts à intégrer.",
  },
  {
    value: "with_help",
    label: "J'ai besoin d'accompagnement",
    description: "J'ai des idées ; je veux être guidé pour les structurer.",
  },
  {
    value: "to_write",
    label: "La rédaction est à déléguer",
    description: "Les textes sont à produire entièrement.",
  },
]
