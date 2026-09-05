/**
 * Réalisations Devzair — source de vérité unique.
 *
 * Consommée par HomeFeaturedCaseStudy (carrousel home) et les pages
 * /realisations (hub) et /realisations/[slug] (études de cas).
 *
 * AGENTS.md rule 1 : n'inscrire ici que des projets réellement livrés par
 * Devzair, avec l'accord du client. Aucun chiffre inventé, aucun
 * témoignage, aucune image sans autorisation. `year` n'est renseigné que
 * si la date de livraison est confirmée ; `href` uniquement si la cible
 * existe réellement et que le client a autorisé le lien.
 *
 * Pour ajouter un projet :
 *   1. déposer l'image principale dans `public/portfolio/{id}.webp` ;
 *   2. renseigner un `imageAlt` descriptif (lu par les lecteurs d'écran) ;
 *   3. fixer `status` à "published" une fois le contenu de la page vérifié ;
 *   4. `category`, `description`, `longDescription`, `tags` doivent rester
 *      factuels — pas de superlatif, pas de résultat mesurable non
 *      vérifiable (chiffres de trafic, taux de conversion, CA, etc.).
 */

export interface CaseStudy {
  readonly id: string
  /** Slug d'URL — doit correspondre au nom du composant de la page détail. */
  readonly slug: string
  /** Route interne Nuxt — `/realisations/{slug}`. */
  readonly route: string
  /** "published" → page /realisations/[slug] livrée et indexable. */
  readonly status: "published" | "planned"
  readonly name: string
  readonly category: string
  readonly description: string
  readonly longDescription: string
  readonly tags: readonly string[]
  readonly imageSrc: string
  readonly imageAlt: string
  /**
   * Titre SEO de la page détail — affiché dans <title> et OG:title.
   * Ne doit pas contenir "| Devzair" (injecté par le titleTemplate global).
   * Obligatoire pour les études `published`.
   */
  readonly seoTitle: string
  /** Meta description de la page détail. Obligatoire pour les études `published`. */
  readonly seoDescription: string
  /**
   * URL absolue du site client en production. Rendu comme lien externe
   * (`target="_blank"` + `rel="noopener noreferrer"`) par le CTA du
   * carrousel et de la page détail. À ne renseigner que si le site est
   * effectivement en ligne et que le client a autorisé la mise en avant.
   */
  readonly href?: string
  readonly year?: string
  /**
   * Overlay décoratif optionnel — objet emblématique du projet, détouré sur
   * fond transparent, positionné devant la carte pour créer un effet de
   * profondeur. Purement décoratif : rendu avec `alt=""` et
   * `aria-hidden="true"`. Ne renseigner que si l'asset existe réellement
   * dans `public/portfolio/`.
   */
  readonly overlayImageSrc?: string
}

export const caseStudies: readonly CaseStudy[] = [
  {
    id: "nidemiel",
    slug: "nidemiel",
    route: "/realisations/nidemiel",
    status: "published",
    name: "Nidemiel",
    category: "E-commerce",
    description:
      "Boutique en ligne dédiée aux miels d'exception, sélectionnés avec passion.",
    longDescription:
      "Nidemiel réunit une sélection resserrée de miels rares autour d'une exigence : rendre la provenance, la récolte et la fiche sensorielle de chaque pot lisibles en quelques secondes. Nous avons conçu une boutique sobre où le produit reste au centre, avec un tunnel d'achat direct et une base éditoriale prête à accueillir de nouvelles récoltes.",
    tags: ["E-commerce", "UX/UI", "Éditorial produit"],
    href: "https://nidemiel.com",
    imageSrc: "/portfolio/nidemiel.webp",
    imageAlt:
      "Aperçu du site Nidemiel : maquettes desktop et mobile de la boutique en ligne de miels artisanaux",
    overlayImageSrc: "/portfolio/nidemiel-honey-jar.webp",
    seoTitle: "Boutique en ligne Nidemiel — E-commerce miels artisanaux",
    seoDescription:
      "Boutique e-commerce Nidemiel : architecture produit, éditorial sensoriel et tunnel d'achat pour une sélection de miels rares.",
  },
  {
    id: "kitchen-meat",
    slug: "kitchen-meat",
    route: "/realisations/kitchen-meat",
    status: "published",
    name: "Kitchen Meat",
    category: "Site vitrine — Restauration",
    description:
      "Restaurant de grillades à Lyon — vitrine immersive et prise de contact directe.",
    longDescription:
      "Kitchen Meat est un projet dédié à l'univers de la grillade. Nous avons conçu une vitrine immersive mettant en avant les spécialités, l'identité du restaurant et une expérience claire pour découvrir la carte et réserver. La hiérarchie de contenu et l'ambiance chaleureuse guident le visiteur vers la table.",
    tags: ["Site vitrine", "Identité visuelle", "UI/UX", "Réservation"],
    href: "https://kitchen-meat.fr",
    imageSrc: "/portfolio/kitchen-meat.webp",
    imageAlt:
      "Aperçu du site Kitchen Meat : maquette de la vitrine du restaurant de grillades lyonnais",
    overlayImageSrc: "/portfolio/kitchen-meat-plate.webp",
    seoTitle: "Création de site vitrine Kitchen Meat — Restaurant à Lyon",
    seoDescription:
      "Site vitrine Kitchen Meat : vitrine immersive pour un restaurant lyonnais. Identité visuelle, UI/UX et parcours de réservation.",
  },
  {
    id: "e-shop-admin",
    slug: "e-shop-admin",
    route: "/realisations/e-shop-admin",
    status: "planned",
    name: "E-Shop Admin",
    category: "SaaS — Interface d'administration",
    description:
      "Interface d'administration SaaS pour piloter une boutique en ligne au quotidien.",
    longDescription:
      "E-Shop Admin est une interface d'administration pensée pour les e-commerçants qui veulent centraliser commandes, catalogue, clients et expéditions dans un même tableau de bord. Nous avons dessiné les écrans clés — vue synthétique des ventes, suivi des statuts de commande, gestion produit, top ventes — pour rendre le pilotage lisible d'un coup d'œil, en desktop comme en mobile.",
    tags: ["SaaS", "UI/UX", "Dashboard", "E-commerce"],
    imageSrc: "/portfolio/saas-ecommerce.webp",
    imageAlt:
      "Aperçu de l'interface E-Shop Admin : tableau de bord SaaS d'administration e-commerce avec ventes, statuts de commande et top produits",
    overlayImageSrc: "/portfolio/saas-ecommerce-phone.webp",
    seoTitle: "Interface SaaS E-Shop Admin — Tableau de bord e-commerce",
    seoDescription:
      "Interface d'administration SaaS E-Shop Admin : dashboard centralisé, gestion des commandes et du catalogue, mobile et desktop.",
  },
  {
    id: "mizan",
    slug: "mizan",
    route: "/realisations/mizan",
    status: "published",
    name: "Mizan",
    category: "SaaS — Gestion de commerce",
    description:
      "Application tout-en-un pour la gestion quotidienne d'un commerce.",
    longDescription:
      "Mizan est une solution SaaS pensée pour les commerçants et e-commerçants qui veulent piloter stock, commandes, clients, messages et calcul de zakat depuis une même application mobile-first. Nous avons dessiné l'interface autour d'un tableau de bord clair, d'un suivi des ventes hebdomadaires et d'une gestion des commandes prête à l'usage terrain — le tout dans un vocabulaire visuel sobre, aligné avec l'identité Mizan.",
    tags: ["SaaS", "Mobile-first", "UI/UX", "Dashboard"],
    href: "https://mizan-commerce.com",
    imageSrc: "/portfolio/mizan.webp",
    imageAlt:
      "Aperçu du projet Mizan : maquette de l'application SaaS de gestion de commerce, vue laptop et mobile avec tableau de bord, stock et commandes",
    overlayImageSrc: "/portfolio/mizan-phone.webp",
    seoTitle: "Application SaaS Mizan — Gestion de commerce mobile-first",
    seoDescription:
      "Application SaaS Mizan : interface mobile-first pour piloter stock, commandes, clients et calcul de zakat depuis un tableau de bord.",
  },
  {
    id: "haramain-prestige",
    slug: "haramain-prestige",
    route: "/realisations/haramain-prestige",
    status: "planned",
    name: "Haramain Prestige",
    category: "Site vitrine — Conciergerie",
    description:
      "Conciergerie de séjour à Makkah et Madinah — hôtels sélectionnés et accompagnement local.",
    longDescription:
      "Haramain Prestige est une conciergerie qui propose des hôtels soigneusement sélectionnés à tarifs négociés, associés à un accompagnement local et personnalisé, pour vivre le séjour à Makkah et Madinah en toute sérénité. Nous avons dessiné la vitrine pour rendre lisibles les piliers du service — sélection des hôtels, tarifs négociés, accompagnement sur place — et fluidifier la prise de contact des pèlerins et voyageurs.",
    tags: ["Site vitrine", "UI/UX", "Conciergerie", "Réservation"],
    imageSrc: "/portfolio/haramain-prestige.webp",
    imageAlt:
      "Aperçu du site Haramain Prestige : maquette tablette de la conciergerie de séjour à Makkah et Madinah, avec calendrier de réservation en avant-plan",
    overlayImageSrc: "/portfolio/haramain-prestige-calendar.webp",
    seoTitle: "Site vitrine Haramain Prestige — Conciergerie Makkah et Madinah",
    seoDescription:
      "Site vitrine Haramain Prestige : conception pour une conciergerie de séjour à Makkah et Madinah. Lisibilité du service et prise de contact.",
  },
  {
    id: "al-mumayiz",
    slug: "al-mumayiz",
    route: "/realisations/al-mumayiz",
    status: "published",
    name: "Al Mumayiz",
    category: "Site vitrine B2B",
    description:
      "Site vitrine dédié aux professionnels du secteur des chachias.",
    longDescription:
      "Al Mumayiz est un site vitrine pensé pour les professionnels — revendeurs, distributeurs et boutiques spécialisées. Nous avons conçu une présentation claire qui met en avant le savoir-faire de l'atelier, la constance des produits et la relation directe avec les artisans, afin de simplifier la prise de contact pour un public métier.",
    tags: ["Site vitrine", "B2B", "UX/UI"],
    href: "https://al-mumayiz.com",
    imageSrc: "/portfolio/al-mumayiz.webp",
    imageAlt:
      "Aperçu du site Al Mumayiz : maquette du site vitrine dédié aux professionnels des chachias",
    overlayImageSrc: "/portfolio/al-mumayiz-chechia.webp",
    seoTitle: "Site vitrine B2B Al Mumayiz — Artisan spécialiste des chachias",
    seoDescription:
      "Site vitrine B2B Al Mumayiz : présentation du savoir-faire artisanal et structure professionnelle pour revendeurs et distributeurs de chachias.",
  },
]
