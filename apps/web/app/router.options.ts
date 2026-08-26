import type { RouterConfig } from "@nuxt/schema"

const RESOURCES_LIST_SCROLL_OFFSET = 105 // header (89 px) + espacement (16 px)

function getFirstQueryValue(value: unknown): string | null {
  if (Array.isArray(value)) {
    return typeof value[0] === "string" ? value[0] : null
  }

  return typeof value === "string" ? value : null
}

/**
 * Comportement du scroll lors des navigations Vue Router.
 *
 * Par défaut, Nuxt appelle `window.scrollTo({ top: 0 })` sans forcer de
 * `behavior`, ce qui laisse le CSS `scroll-behavior: smooth` global (défini
 * dans `reset.css` pour les ancres in-page) s'appliquer aussi aux
 * changements de page. Résultat : quand on quitte le bas d'une page, on
 * voit un défilement animé jusqu'en haut de la nouvelle page — effet non
 * désiré signalé par l'utilisateur.
 *
 * On force donc :
 *   - `instant` pour un vrai changement de page (retour immédiat en haut) ;
 *   - le début de la liste pour une pagination des ressources ;
 *   - `smooth` pour les liens hash intra-page (ex. sommaire timeline
 *     `/expertises` → `#pillar-*`) où l'animation reste souhaitable ;
 *   - la position mémorisée pour la navigation back/forward, comme le fait
 *     nativement le navigateur.
 */
export default <RouterConfig>{
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.hash) return { el: to.hash, behavior: "smooth" }

    const fromPage = getFirstQueryValue(from.query.page) ?? "1"
    const toPage = getFirstQueryValue(to.query.page) ?? "1"
    const fromExpertise = getFirstQueryValue(from.query.expertise)
    const toExpertise = getFirstQueryValue(to.query.expertise)

    if (
      to.path === "/ressources"
      && from.path === to.path
      && fromPage !== toPage
      && fromExpertise === toExpertise
    ) {
      return {
        el: "#resources-list-title",
        top: RESOURCES_LIST_SCROLL_OFFSET,
        behavior: "smooth",
      }
    }

    // Les filtres mettent à jour la query sans quitter la page : conserver la
    // position évite un retour brutal au hero.
    if (to.path === from.path && to.fullPath !== from.fullPath) return false
    return { left: 0, top: 0, behavior: "instant" }
  },
}
