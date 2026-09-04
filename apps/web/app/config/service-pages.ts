/**
 * Définitions typées des huit pages de service commerciales.
 *
 * Source de vérité pour le statut de publication et les données de présentation
 * de chaque page fille `/services/{slug}`. Distincte de `expertise-pages.ts`,
 * qui porte les pôles d'expertise Devzair.
 *
 * Règle stricte : une page dont `status === "planned"` ne doit jamais
 * apparaître dans le sitemap, la navigation, les liens internes ni être
 * accessible comme route publique.
 *
 * Les champs déclarés ici sont ceux consommés par la phase actuelle (SEO-COM-1).
 * Les champs SEO détaillés (seoTitle, introduction, etc.) seront ajoutés lors
 * de l'implémentation de chaque page fille (SEO-COM-2 et suivants).
 */

export type ServicePageStatus = "planned" | "published"

export interface ServicePageDefinition {
  /** Identifiant stable — égal au slug, clé de résolution dans la config. */
  readonly id: string
  /** Segment URL, sans slash. */
  readonly slug: string
  /** Route complète absolue au sein du site. */
  readonly route: string
  /** État de publication — contrôle liens, sitemap et route. */
  readonly status: ServicePageStatus
  /** Libellé court pour la navigation et le footer. */
  readonly shortTitle: string
  /** Titre affiché dans les cartes et listes. */
  readonly title: string
  /** Résumé d'une phrase, autonome — utilisé dans la carte du hub. */
  readonly summary: string
}

export const servicePages: readonly ServicePageDefinition[] = [
  {
    id: "creation-site-internet",
    slug: "creation-site-internet",
    route: "/services/creation-site-internet",
    status: "planned",
    shortTitle: "Site internet",
    title: "Création de site internet",
    summary:
      "Un site vitrine professionnel, pensé pour votre activité et facile à trouver en ligne.",
  },
  {
    id: "site-e-commerce",
    slug: "site-e-commerce",
    route: "/services/site-e-commerce",
    status: "planned",
    shortTitle: "E-commerce",
    title: "Site e-commerce",
    summary:
      "Une boutique en ligne fiable, avec catalogue, panier et gestion des commandes.",
  },
  {
    id: "application-web-metier",
    slug: "application-web-metier",
    route: "/services/application-web-metier",
    status: "planned",
    shortTitle: "Application métier",
    title: "Application web métier",
    summary:
      "Un outil sur mesure qui s'adapte à vos processus internes, pas l'inverse.",
  },
  {
    id: "design-ui-ux-identite-visuelle",
    slug: "design-ui-ux-identite-visuelle",
    route: "/services/design-ui-ux-identite-visuelle",
    status: "planned",
    shortTitle: "Design",
    title: "Design et identité visuelle",
    summary:
      "Une identité cohérente et une interface claire, conçues avant le développement.",
  },
  {
    id: "photographie-creation-contenu",
    slug: "photographie-creation-contenu",
    route: "/services/photographie-creation-contenu",
    status: "planned",
    shortTitle: "Contenu",
    title: "Photographie et contenu web",
    summary:
      "Des photos professionnelles et des textes qui décrivent ce que vous faites réellement.",
  },
  {
    id: "seo-referencement-naturel",
    slug: "seo-referencement-naturel",
    route: "/services/seo-referencement-naturel",
    status: "planned",
    shortTitle: "SEO",
    title: "SEO et référencement naturel",
    summary:
      "Un travail de fond sur la visibilité organique : technique, éditorial et local.",
  },
  {
    id: "visibilite-locale",
    slug: "visibilite-locale",
    route: "/services/visibilite-locale",
    status: "planned",
    shortTitle: "Visibilité locale",
    title: "Visibilité locale",
    summary:
      "Apparaître sur les recherches de proximité et tenir votre présence locale cohérente.",
  },
  {
    id: "maintenance-accompagnement",
    slug: "maintenance-accompagnement",
    route: "/services/maintenance-accompagnement",
    status: "planned",
    shortTitle: "Maintenance",
    title: "Maintenance et accompagnement",
    summary:
      "Un suivi technique régulier, des mises à jour appliquées et des évolutions livrées au fil du besoin.",
  },
]

/**
 * Résout la route publique d'un service à partir de son identifiant.
 *
 * Retourne `null` si l'id ne correspond à aucun service publié. Règle :
 * une page `planned` ne retourne jamais de route, même si l'entrée existe.
 */
export function resolveServiceRoute(serviceId: string): string | null {
  const page = servicePages.find(
    (candidate) => candidate.id === serviceId && candidate.status === "published",
  )
  return page ? page.route : null
}
