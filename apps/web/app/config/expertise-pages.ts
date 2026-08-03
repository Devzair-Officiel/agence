/**
 * Définitions typées des cinq pages d'expertise dédiées.
 *
 * Source de vérité pour **le contenu éditorial ET le statut de publication**
 * de chaque page fille `/expertises/{slug}`. Distincte de
 * `expertise-pillars.ts`, qui reste la source de vérité pour les libellés
 * courts et la narration transverse consommée par la home (grille + graphe
 * SVG + parcours numéroté) et la vue d'ensemble `/expertises`.
 *
 * Les deux configurations coexistent volontairement :
 *   - `expertise-pillars.ts` porte les données de pôle réutilisées en
 *     plusieurs endroits (label, longDescription, services affichés dans
 *     les cartes) — invariant transverse au site ;
 *   - `expertise-pages.ts` porte les données spécifiques à la page fille
 *     dédiée (H1, introduction, besoin, approche, livrables, bénéfices,
 *     pôles liés, SEO) — invariant local à la route détaillée.
 *
 * Phase 7B : les cinq définitions passent à `status: "published"`. La route
 * dynamique `pages/expertises/[slug].vue` les consomme via une résolution
 * par slug ; toute autre valeur retourne un 404 explicite. La carte
 * `ExpertiseOverviewCard` devient un `NuxtLink` conditionnel sur ce statut.
 */

export type ExpertisePageStatus = "planned" | "published"

/**
 * Bénéfice concret exposé sur la page fille — pas une promesse commerciale,
 * une description factuelle de ce que l'entreprise obtient à l'issue du
 * projet. Le titre est court (≤ 60 caractères) ; la description tient en
 * une phrase autonome.
 */
export interface ExpertiseBenefit {
  readonly title: string
  readonly description: string
}

export interface ExpertisePageDefinition {
  /** Identifiant stable, égal à `pillarId` — clé de rendu et de résolution. */
  readonly id: string
  /** Référence à l'entrée correspondante de `expertisePillars`. */
  readonly pillarId: string
  /** Segment URL, sans slash — `"concevoir"`, `"faire-evoluer"`, etc. */
  readonly slug: string
  /** Route complète absolue au sein du site. */
  readonly route: string
  /** État de publication : `"planned"` masque le lien, `"published"` l'active. */
  readonly status: ExpertisePageStatus

  /** Sur-titre affiché au-dessus du H1 (« Expertise · Concevoir », etc.). */
  readonly eyebrow: string
  /** H1 unique de la page fille — reprise dans `<title>` via `usePageSeo`. */
  readonly title: string
  /** Libellé court, utilisable dans la navigation, le breadcrumb, une carte. */
  readonly shortTitle: string
  /** Introduction narrative rendue immédiatement sous le H1. */
  readonly introduction: string
  /** Résumé d'une phrase, autonome — utilisé par la carte overview. */
  readonly summary: string

  /** Titre `<title>` (sera suffixé « | Devzair » par le titleTemplate). */
  readonly seoTitle: string
  /** Meta description — 140 à 160 caractères, sans redite du titre. */
  readonly seoDescription: string

  /** Titre de la section « À qui cela s'adresse ». */
  readonly needTitle: string
  /** Corps de la section « À qui cela s'adresse ». */
  readonly needDescription: string

  /** Titre de la section « Comment nous procédons ». */
  readonly approachTitle: string
  /** Corps de la section « Comment nous procédons ». */
  readonly approachDescription: string

  /** Trois livrables représentatifs — miroir de `expertisePillars[].services`. */
  readonly services: readonly string[]
  /** Liste étendue des livrables détaillés rendus sur la page fille. */
  readonly deliverables: readonly string[]
  /** Bénéfices concrets — trois à cinq entrées. */
  readonly benefits: readonly ExpertiseBenefit[]

  /**
   * Ids des deux pôles connexes mis en avant dans la section « Aller plus
   * loin ». Contraintes vérifiées en test unitaire :
   *   - pas d'auto-référence (`pillarId ∉ relatedPillarIds`) ;
   *   - toutes les valeurs pointent vers un pôle existant ;
   *   - exactement deux entrées.
   */
  readonly relatedPillarIds: readonly string[]
}

export const expertisePages: readonly ExpertisePageDefinition[] = [
  {
    id: "concevoir",
    pillarId: "concevoir",
    slug: "concevoir",
    route: "/expertises/concevoir",
    status: "published",

    eyebrow: "Expertise · Concevoir",
    title: "Concevoir : identité, expérience et architecture",
    shortTitle: "Concevoir",
    introduction:
      "Avant tout développement, nous posons les fondations visuelles et fonctionnelles de votre projet. Identité, architecture d'information, parcours et maquettes permettent de valider les intentions et de limiter les allers-retours coûteux plus tard.",
    summary:
      "Nous posons les fondations visuelles et fonctionnelles de votre projet avant tout développement.",

    seoTitle: "Concevoir : identité, design et architecture d'information",
    seoDescription:
      "Identité de marque, design d'interface et architecture d'information. Nous cadrons les fondations visuelles et fonctionnelles avant toute ligne de code.",

    needTitle: "À qui cela s'adresse",
    needDescription:
      "Aux entreprises qui lancent un nouveau site, qui refondent une plateforme existante, ou qui souhaitent aligner leur identité visuelle avec un projet numérique cohérent. Le pôle Concevoir intervient dès qu'il faut décider avant de construire.",

    approachTitle: "Comment nous procédons",
    approachDescription:
      "Nous partons de vos objectifs et de vos utilisateurs. Nous cartographions les parcours, hiérarchisons les contenus, produisons les maquettes et validons chaque étape avec vous. La conception est un travail de décisions, pas d'illustrations : nous documentons ce qui est retenu et pourquoi.",

    services: [
      "Identité de marque",
      "Design d'interface",
      "Architecture d'information",
    ],
    deliverables: [
      "Cadrage des objectifs et des utilisateurs",
      "Architecture d'information et arborescence",
      "Système de design (typographies, couleurs, composants)",
      "Maquettes d'interface responsive",
      "Guide d'usage remis à l'équipe",
    ],
    benefits: [
      {
        title: "Des décisions prises en amont",
        description:
          "Les choix structurants sont arbitrés avant le développement, ce qui réduit les allers-retours coûteux plus tard.",
      },
      {
        title: "Une identité cohérente",
        description:
          "Le système visuel s'applique uniformément sur tous les supports, du site aux documents commerciaux.",
      },
      {
        title: "Une base durable",
        description:
          "Les composants d'interface sont pensés pour évoluer sans devoir refaire l'ensemble du système.",
      },
    ],
    relatedPillarIds: ["construire", "valoriser"],
  },
  {
    id: "construire",
    pillarId: "construire",
    slug: "construire",
    route: "/expertises/construire",
    status: "published",

    eyebrow: "Expertise · Construire",
    title: "Construire : sites, e-commerce et applications",
    shortTitle: "Construire",
    introduction:
      "Nous développons vos sites, plateformes e-commerce et applications métier sur des bases techniques modernes, testées et pensées pour durer. Le code est notre outil, pas notre objectif : il sert le projet et se maintient dans le temps.",
    summary:
      "Nous développons sites, plateformes e-commerce et applications métier sur des bases techniques modernes et pensées pour durer.",

    seoTitle: "Construire : sites, e-commerce et applications sur mesure",
    seoDescription:
      "Développement de sites vitrines, plateformes e-commerce et applications métier. Un socle technique moderne, testé et pensé pour évoluer sereinement.",

    needTitle: "À qui cela s'adresse",
    needDescription:
      "Aux entreprises qui ont besoin d'un site vitrine sur mesure, d'une boutique en ligne fiable ou d'une application métier taillée pour leurs processus. Le pôle Construire prend en charge la construction technique une fois les fondations posées par la Conception.",

    approachTitle: "Comment nous procédons",
    approachDescription:
      "Nous travaillons par itérations, en livrant des incréments testables plutôt qu'un gros projet en fin de parcours. Chaque brique est développée, testée et documentée. Les choix techniques favorisent la clarté, la maintenabilité et l'accessibilité — pas la démonstration technique.",

    services: [
      "Site vitrine sur mesure",
      "Plateforme e-commerce",
      "Application métier",
    ],
    deliverables: [
      "Site vitrine responsive et accessible",
      "Plateforme e-commerce (catalogue, panier, commande)",
      "Application métier adaptée à vos processus",
      "Intégrations aux outils existants",
      "Documentation technique remise en fin de projet",
    ],
    benefits: [
      {
        title: "Un socle technique moderne",
        description:
          "Les frameworks retenus sont maintenus, documentés et adoptés par une large communauté — vous ne dépendez pas d'une brique obscure.",
      },
      {
        title: "Un produit testé",
        description:
          "Les fonctionnalités importantes sont couvertes par des tests automatisés qui protègent les évolutions futures.",
      },
      {
        title: "Une maintenance sereine",
        description:
          "La structure du code, la documentation et la couverture de tests permettent de reprendre le projet sans mois d'apprentissage.",
      },
    ],
    relatedPillarIds: ["concevoir", "faire-evoluer"],
  },
  {
    id: "valoriser",
    pillarId: "valoriser",
    slug: "valoriser",
    route: "/expertises/valoriser",
    status: "published",

    eyebrow: "Expertise · Valoriser",
    title: "Valoriser : contenus visuels et éditoriaux",
    shortTitle: "Valoriser",
    introduction:
      "Nous produisons les contenus visuels et éditoriaux qui rendent votre message crédible et exploitable. Un site technique bien construit sans contenus adaptés reste muet ; nous comblons cet écart.",
    summary:
      "Nous produisons les contenus visuels et éditoriaux qui rendent votre message crédible et exploitable.",

    seoTitle: "Valoriser : photographie, contenus et structuration de l'offre",
    seoDescription:
      "Photographie professionnelle, contenus rédactionnels et structuration de votre offre. Nous produisons les éléments qui rendent votre message lisible et crédible.",

    needTitle: "À qui cela s'adresse",
    needDescription:
      "Aux entreprises qui ont un site en place mais dont les contenus ne reflètent plus la réalité de leur offre, à celles qui lancent un projet et n'ont pas encore de matière visuelle exploitable, ou qui souhaitent structurer leur discours commercial de manière claire.",

    approachTitle: "Comment nous procédons",
    approachDescription:
      "Nous partons de votre offre réelle et de vos publics. Nous préparons les prises de vue, produisons les textes, structurons les pages et vérifions que chaque contenu sert un objectif précis. Aucun contenu de remplissage : chaque bloc a une raison d'être.",

    services: [
      "Photographie professionnelle",
      "Contenus rédactionnels",
      "Structuration de l'offre",
    ],
    deliverables: [
      "Reportage photographique adapté à votre activité",
      "Textes rédigés et relus pour chaque page importante",
      "Structuration des pages d'offre et de service",
      "Bibliothèque de visuels prête à réemployer",
      "Guide éditorial pour vos futures publications",
    ],
    benefits: [
      {
        title: "Un discours qui vous ressemble",
        description:
          "Les contenus sont écrits à partir de vos mots, pas à partir de formules génériques recyclées d'un autre site.",
      },
      {
        title: "Des visuels cohérents",
        description:
          "La série photographique respecte votre univers et se réutilise sur vos supports commerciaux et vos réseaux.",
      },
      {
        title: "Une offre lisible",
        description:
          "La structuration des pages permet à vos visiteurs de comprendre rapidement ce que vous proposez et à qui vous vous adressez.",
      },
    ],
    relatedPillarIds: ["visibilite", "concevoir"],
  },
  {
    id: "visibilite",
    pillarId: "visibilite",
    slug: "visibilite",
    route: "/expertises/visibilite",
    status: "published",

    eyebrow: "Expertise · Visibilité",
    title: "Développer la visibilité : SEO, local et éditorial",
    shortTitle: "Visibilité",
    introduction:
      "Nous travaillons la visibilité durable de votre entreprise : référencement naturel, présence locale et stratégie éditoriale. La visibilité ne se décrète pas — elle se construit, page par page, requête par requête.",
    summary:
      "Nous travaillons la visibilité durable de votre entreprise : référencement, présence locale et stratégie éditoriale.",

    seoTitle: "Visibilité : SEO, référencement local et stratégie éditoriale",
    seoDescription:
      "Référencement naturel, présence locale et contenus éditoriaux orientés intention de recherche. Nous construisons une visibilité durable, pas des pics artificiels.",

    needTitle: "À qui cela s'adresse",
    needDescription:
      "Aux entreprises qui ont un site mais peu de trafic qualifié, à celles qui veulent apparaître sur les recherches locales de leur zone, ou qui souhaitent structurer une production de contenus dans la durée sans dépendre exclusivement de la publicité payante.",

    approachTitle: "Comment nous procédons",
    approachDescription:
      "Nous analysons les intentions de recherche réellement pertinentes pour votre activité, corrigeons les blocages techniques du site, produisons ou révisons les contenus stratégiques, et suivons les résultats dans le temps. Nous privilégions les gains durables aux quick wins fragiles.",

    services: [
      "Référencement naturel (SEO)",
      "Visibilité locale",
      "Stratégie éditoriale",
    ],
    deliverables: [
      "Audit SEO technique, sémantique et éditorial",
      "Optimisation des pages existantes",
      "Fiche d'établissement locale et cohérence NAP",
      "Plan éditorial trimestriel ou semestriel",
      "Tableau de suivi des positions et du trafic organique",
    ],
    benefits: [
      {
        title: "Une visibilité durable",
        description:
          "Les gains obtenus par un travail SEO éditorial et technique se maintiennent dans le temps sans coût publicitaire récurrent.",
      },
      {
        title: "Un trafic qualifié",
        description:
          "Les contenus visent les requêtes correspondant à vos publics, pas les volumes flatteurs sans conversion.",
      },
      {
        title: "Une présence locale crédible",
        description:
          "La cohérence entre votre site, votre fiche d'établissement et vos citations locales renforce votre légitimité sur votre zone.",
      },
    ],
    relatedPillarIds: ["valoriser", "faire-evoluer"],
  },
  {
    id: "faire-evoluer",
    pillarId: "faire-evoluer",
    slug: "faire-evoluer",
    route: "/expertises/faire-evoluer",
    status: "published",

    eyebrow: "Expertise · Faire évoluer",
    title: "Faire évoluer : maintenance, mesure et évolutions",
    shortTitle: "Faire évoluer",
    introduction:
      "Nous assurons le suivi dans le temps de votre site : mesures, mises à jour, sécurité, évolutions fonctionnelles. Un site vivant reste utile ; un site figé se dégrade en quelques mois.",
    summary:
      "Nous assurons le suivi dans le temps : mesures, mises à jour, sécurité et évolutions fonctionnelles.",

    seoTitle: "Faire évoluer : maintenance, mesure et évolutions du site",
    seoDescription:
      "Maintenance, sécurité, mesure d'usage et évolutions fonctionnelles. Nous gardons votre site aligné sur vos priorités et à jour dans le temps.",

    needTitle: "À qui cela s'adresse",
    needDescription:
      "Aux entreprises dont le site est en production et qui veulent le garder à jour, mesurer son usage réel, corriger les défauts et ajouter les fonctionnalités que l'activité rend nécessaires. Le pôle Faire évoluer prend le relais une fois le site en ligne.",

    approachTitle: "Comment nous procédons",
    approachDescription:
      "Nous mettons en place un suivi mesuré (usages, performances, erreurs), planifions les mises à jour techniques régulières, priorisons les évolutions avec vous et livrons les correctifs sans attendre un chantier annuel. La maintenance est un flux, pas un événement.",

    services: [
      "Maintenance et sécurité",
      "Mesure et amélioration continue",
      "Évolutions fonctionnelles",
    ],
    deliverables: [
      "Mises à jour techniques et de sécurité régulières",
      "Suivi des mesures d'usage et des performances",
      "Correctifs livrés sur cycle court",
      "Évolutions fonctionnelles priorisées avec vous",
      "Point trimestriel sur l'état du site",
    ],
    benefits: [
      {
        title: "Un site à jour",
        description:
          "Les mises à jour de sécurité et de dépendances sont appliquées régulièrement, ce qui évite les dettes techniques bloquantes.",
      },
      {
        title: "Des décisions informées",
        description:
          "Les mesures d'usage réelles permettent de prioriser les évolutions qui servent vraiment vos publics.",
      },
      {
        title: "Une réactivité maîtrisée",
        description:
          "Les correctifs et les petites évolutions sont livrés sans attendre un cycle long, dans un cadre de coûts prévisible.",
      },
    ],
    relatedPillarIds: ["construire", "visibilite"],
  },
]
