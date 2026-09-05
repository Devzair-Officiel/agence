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

import { expertisePages } from "./expertise-pages"
import { servicePages } from "./service-pages"

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
  /**
   * Lien optionnel rendu comme bouton/CTA sous la liste du groupe.
   * Il reste dans la configuration afin de ne pas dupliquer son URL dans la vue.
   */
  readonly cta?: NavigationItem
}

export const primaryNavigation: readonly NavigationItem[] = [
  { label: "Accueil", to: "/", isRoute: true },
  { label: "Expertises", to: "/expertises", isRoute: true },
  { label: "Services", to: "/services", isRoute: true },
  { label: "Réalisations", to: "/realisations", isRoute: true },
  { label: "Agence", to: "/agence", isRoute: true },
  { label: "Ressources", to: "/ressources", isRoute: true },
]

// Depuis le split du formulaire vers `/contact` (page dédiée), le CTA
// principal pointe directement sur cette route Nuxt réelle.
// `isRoute: true` → NuxtLink SPA classique (pas de fallback `external`).
export const primaryCta: NavigationItem = {
  label: "Parler de votre projet",
  to: "/contact",
  isRoute: true,
}

// CTA secondaire vers l'estimateur de projet — affiché dans le header
// (lien bordé, distinct du CTA primaire) et dans le groupe "Découvrir"
// du footer. Route livrée → `isRoute: true`.
export const estimatorCta: NavigationItem = {
  label: "Estimer mon projet",
  to: "/estimer-mon-projet",
  isRoute: true,
}

// Les cinq pôles d'expertise publiés, dérivés de `expertise-pages.ts`
// pour rester synchronisés automatiquement avec tout changement de statut.
const expertisesFooterItems: readonly NavigationItem[] = expertisePages
  .filter((p) => p.status === "published")
  .map((p): NavigationItem => ({ label: p.shortTitle, to: p.route, isRoute: true }))

// Les services publiés, dérivés de `service-pages.ts` afin que le footer
// reste synchronisé automatiquement avec leur statut de publication.
const servicesFooterItems: readonly NavigationItem[] = servicePages
  .filter((p) => p.status === "published")
  .map((p): NavigationItem => ({ label: p.shortTitle, to: p.route, isRoute: true }))

export const footerNavigation: readonly NavigationGroup[] = [
  {
    title: "Expertises",
    items: expertisesFooterItems,
  },
  {
    title: "Services",
    items: servicesFooterItems,
    cta: { label: "Tous nos services", to: "/services", isRoute: true },
  },
  {
    // "Découvrir" regroupe les destinations générales.
    // `cta` est rendu séparément comme bouton bordé sous la liste.
    title: "Découvrir",
    items: [
      { label: "L'agence", to: "/agence", isRoute: true },
      { label: "Réalisations", to: "/realisations", isRoute: true },
      { label: "Ressources", to: "/ressources", isRoute: true },
      { label: "Estimer mon projet", to: "/estimer-mon-projet", isRoute: true },
    ],
    cta: primaryCta,
  },
]

// Les mentions légales et la politique de confidentialité seront ajoutées
// quand les pages correspondantes seront livrées (phase à venir).
// Aucune entrée listée ici tant que la route n'existe pas — cf. AGENTS.md §11.
export const legalNavigation: readonly NavigationItem[] = []
