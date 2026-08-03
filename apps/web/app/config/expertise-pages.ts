/**
 * Définitions typées des cinq pages d'expertise dédiées.
 *
 * Source de vérité pour le **routage et le statut** de chaque page fille
 * `/expertises/{slug}` — distincte de `expertise-pillars.ts`, qui reste la
 * source de vérité pour le **contenu éditorial** consommé par la home et
 * la vue d'ensemble `/expertises`.
 *
 * Les deux configurations coexistent volontairement :
 *   - `expertise-pillars.ts` change quand un pôle évolue narrativement
 *     (label, longDescription, services affichés dans les cartes) ;
 *   - `expertise-pages.ts` change quand une page fille est publiée
 *     (status → "published") ou renommée (slug/route/title).
 *
 * Phase 7A : les cinq définitions sont créées avec `status: "planned"`.
 * Aucune route `/expertises/{slug}` n'est livrée par cette phase — la
 * vue d'ensemble `/expertises` **n'utilise pas** ces définitions pour
 * générer de lien mort. Elles sont prêtes pour la Phase 7B, qui livrera
 * les pages détaillées et fera passer chaque `status` à `"published"`.
 */

export type ExpertisePageStatus = "planned" | "published"

export interface ExpertisePageDefinition {
  /** Identifiant stable, égal à `pillarId` — utilisé comme clé de rendu. */
  readonly id: string
  /** Référence à l'entrée correspondante de `expertisePillars`. */
  readonly pillarId: string
  /** Segment URL, sans slash — `"concevoir"`, `"faire-evoluer"`, etc. */
  readonly slug: string
  /** Route complète absolue au sein du site. */
  readonly route: string
  /** H1 prévu de la page dédiée (Phase 7B). */
  readonly title: string
  /** Libellé court, utilisable dans la navigation ou une carte compacte. */
  readonly shortTitle: string
  /** Résumé d'une phrase, autonome (utilisable en teaser). */
  readonly summary: string
  /** Trois livrables représentatifs — miroir de `expertisePillars[].services`. */
  readonly services: readonly string[]
  /** État de publication : `"planned"` en 7A, `"published"` en 7B au fil de l'eau. */
  readonly status: ExpertisePageStatus
}

export const expertisePages: readonly ExpertisePageDefinition[] = [
  {
    id: "concevoir",
    pillarId: "concevoir",
    slug: "concevoir",
    route: "/expertises/concevoir",
    title: "Concevoir : identité, expérience et architecture",
    shortTitle: "Concevoir",
    summary:
      "Nous posons les fondations visuelles et fonctionnelles de votre projet avant tout développement.",
    services: [
      "Identité de marque",
      "Design d'interface",
      "Architecture d'information",
    ],
    status: "planned",
  },
  {
    id: "construire",
    pillarId: "construire",
    slug: "construire",
    route: "/expertises/construire",
    title: "Construire : sites, e-commerce et applications",
    shortTitle: "Construire",
    summary:
      "Nous développons sites, plateformes e-commerce et applications métier sur des bases techniques modernes et pensées pour durer.",
    services: [
      "Site vitrine sur mesure",
      "Plateforme e-commerce",
      "Application métier",
    ],
    status: "planned",
  },
  {
    id: "valoriser",
    pillarId: "valoriser",
    slug: "valoriser",
    route: "/expertises/valoriser",
    title: "Valoriser : contenus visuels et éditoriaux",
    shortTitle: "Valoriser",
    summary:
      "Nous produisons les contenus visuels et éditoriaux qui rendent votre message crédible et exploitable.",
    services: [
      "Photographie professionnelle",
      "Contenus rédactionnels",
      "Structuration de l'offre",
    ],
    status: "planned",
  },
  {
    id: "visibilite",
    pillarId: "visibilite",
    slug: "visibilite",
    route: "/expertises/visibilite",
    title: "Développer la visibilité : SEO, local et éditorial",
    shortTitle: "Visibilité",
    summary:
      "Nous travaillons la visibilité durable de votre entreprise : référencement, présence locale et stratégie éditoriale.",
    services: [
      "Référencement naturel (SEO)",
      "Visibilité locale",
      "Stratégie éditoriale",
    ],
    status: "planned",
  },
  {
    id: "faire-evoluer",
    pillarId: "faire-evoluer",
    slug: "faire-evoluer",
    route: "/expertises/faire-evoluer",
    title: "Faire évoluer : maintenance, mesure et évolutions",
    shortTitle: "Faire évoluer",
    summary:
      "Nous assurons le suivi dans le temps : mesures, mises à jour, sécurité et évolutions fonctionnelles.",
    services: [
      "Maintenance et sécurité",
      "Mesure et amélioration continue",
      "Évolutions fonctionnelles",
    ],
    status: "planned",
  },
]
