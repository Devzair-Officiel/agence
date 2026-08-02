/**
 * Étapes de la méthode Devzair — source de vérité unique.
 *
 * Consommée par `HomeProcess.vue` (section « Notre méthode » de la home) et,
 * plus tard, par la page `/methode`.
 *
 * L'ordre `1..6` reflète le déroulé réel d'un projet ; la liste est rendue
 * comme un `<ol>` sémantique. Le texte éditorial est verbatim celui du brief
 * Phase 5C et ne doit pas être reformulé sans mise à jour du plan éditorial
 * (`docs/01-CONTENT.md §7.1`).
 */

export interface ProcessStep {
  readonly id: string
  readonly label: string
  readonly description: string
  readonly order: number
}

export const projectProcess: readonly ProcessStep[] = [
  {
    id: "decouverte",
    label: "Découverte",
    description:
      "Nous prenons le temps de comprendre votre activité, vos objectifs, vos contraintes et l'existant. Aucun projet ne démarre sans cette phase.",
    order: 1,
  },
  {
    id: "cadrage",
    label: "Cadrage",
    description:
      "Nous formalisons le périmètre, les priorités, les responsabilités et le calendrier. Vous savez ce qui sera livré, quand, et par qui.",
    order: 2,
  },
  {
    id: "conception",
    label: "Conception",
    description:
      "Architecture d'information, parcours, maquettes, choix techniques : les décisions structurantes sont validées avant tout développement.",
    order: 3,
  },
  {
    id: "developpement",
    label: "Développement",
    description:
      "Développement itératif, contrôles continus (qualité, SEO, sécurité, accessibilité), revues régulières. Le code produit est celui qui partira en ligne.",
    order: 4,
  },
  {
    id: "lancement",
    label: "Lancement",
    description:
      "Recette, préparation technique, mise en production, contrôles post-lancement. La mise en ligne est un moment planifié, pas un saut dans le vide.",
    order: 5,
  },
  {
    id: "evolution",
    label: "Évolution",
    description:
      "Suivi, mesure, maintenance, corrections et évolutions fonctionnelles. Un site vivant se pilote dans le temps, pas seulement le jour de sa livraison.",
    order: 6,
  },
]
