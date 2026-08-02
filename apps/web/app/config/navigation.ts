/**
 * Navigation Devzair — source de vérité unique consommée par le header,
 * le menu mobile et le footer.
 *
 * Les URLs pointent vers des routes qui seront livrées dans les prochains lots.
 * Un lien listé ici n'implique pas que la page existe déjà : le composant
 * NuxtLink gère naturellement la navigation quand la page apparaîtra.
 */

export interface NavigationItem {
  readonly label: string
  readonly to: string
}

export interface NavigationGroup {
  readonly title: string
  readonly items: readonly NavigationItem[]
}

export const primaryNavigation: readonly NavigationItem[] = [
  { label: "Expertises", to: "/#expertises" },
  { label: "Réalisations", to: "/#realisations" },
  { label: "Méthode", to: "/methode" },
  { label: "Agence", to: "/agence" },
]

// La cible `#contact` est une ancre locale à `/` tant que la Phase 6
// (formulaire dédié) n'est pas livrée. Un seul point de vérité pour
// header, footer, menu mobile et CTA hero.
export const primaryCta: NavigationItem = {
  label: "Parler de votre projet",
  to: "/#contact",
}

export const footerNavigation: readonly NavigationGroup[] = [
  {
    title: "Expertises",
    items: [
      { label: "Concevoir", to: "/expertises/concevoir" },
      { label: "Construire", to: "/expertises/construire" },
      { label: "Valoriser", to: "/expertises/valoriser" },
      { label: "Visibilité", to: "/expertises/visibilite" },
      { label: "Faire évoluer", to: "/expertises/faire-evoluer" },
    ],
  },
  {
    title: "Agence",
    items: [
      { label: "Réalisations", to: "/realisations" },
      { label: "Méthode", to: "/methode" },
      { label: "À propos", to: "/agence" },
      { label: "Ressources", to: "/ressources" },
    ],
  },
]

export const legalNavigation: readonly NavigationItem[] = [
  { label: "Mentions légales", to: "/mentions-legales" },
  { label: "Confidentialité", to: "/confidentialite" },
]
