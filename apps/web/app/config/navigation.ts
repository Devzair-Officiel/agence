/**
 * Navigation Devzair — source de vérité unique consommée par le header,
 * le menu mobile et le footer.
 *
 * Chaque item déclare explicitement si sa cible est déjà une route
 * (`isRoute: true`) ou non (`isRoute: false`). Les composants de layout se
 * servent de ce drapeau pour décider s'ils appliquent l'attribut
 * `external` à `NuxtLink` — sans lui, vue-router émet un warning R0004
 * pour les cibles inconnues.
 *
 * Règle éditoriale (AGENTS.md §11 + brief Phase 7A) : cette configuration
 * ne doit jamais contenir de lien mort. Si une route est prévue mais pas
 * encore livrée, on l'omet ou on la remplace par une ancre équivalente
 * (typiquement `/#realisations` tant que `/realisations` n'existe pas).
 */

export interface NavigationItem {
  readonly label: string
  readonly to: string
  /**
   * `true` quand `to` pointe sur une route Nuxt réellement livrée (ou une
   * ancre locale à une page réelle). `false` quand la cible est une route
   * non encore créée : le rendu bascule alors sur un `<a href>` plat pour
   * éviter le warning vue-router.
   */
  readonly isRoute: boolean
}

export interface NavigationGroup {
  readonly title: string
  readonly items: readonly NavigationItem[]
}

export const primaryNavigation: readonly NavigationItem[] = [
  { label: "Expertises", to: "/expertises", isRoute: true },
  { label: "Agence", to: "/agence", isRoute: true },
  { label: "Réalisations", to: "/#realisations", isRoute: true },
]

// La cible `#contact` est une ancre locale à `/` : le composant
// NuxtLink doit forcer `external` pour éviter un warning quand la
// page en cours n'est pas `/`.
export const primaryCta: NavigationItem = {
  label: "Parler de votre projet",
  to: "/#contact",
  isRoute: false,
}

export const footerNavigation: readonly NavigationGroup[] = [
  {
    title: "Découvrir",
    items: [
      { label: "L'agence", to: "/agence", isRoute: true },
      { label: "Nos expertises", to: "/expertises", isRoute: true },
      { label: "Réalisations", to: "/#realisations", isRoute: false },
    ],
  },
]

// Les mentions légales et la politique de confidentialité seront ajoutées
// quand les pages correspondantes seront livrées (phase à venir).
// Aucune entrée listée ici tant que la route n'existe pas — cf. AGENTS.md §11.
export const legalNavigation: readonly NavigationItem[] = []
