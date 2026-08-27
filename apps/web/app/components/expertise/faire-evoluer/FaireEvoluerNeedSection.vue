<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « À qui cela s'adresse » de la page Faire évoluer.
 *
 * Refonte visuelle : suppression des trois cards encadrées et de la
 * disposition triangulaire qui rendait la composition trop « cards
 * reliées par un diagramme ». Les trois situations MAINTENIR / COMPRENDRE
 * / ADAPTER vivent désormais dans un même espace, traversées par une
 * trajectoire commune (« CONTINUOUS CARE ») avec trois checkpoints
 * décoratifs. Sur desktop, elles sont séparées par de fins filets
 * verticaux ; sur mobile, elles se replient sur une ligne petrol
 * verticale continue.
 *
 * Contenu (inchangé) :
 *   - eyebrow verbatim : « À qui cela s'adresse » ;
 *   - H2 validé : « Quand la mise en ligne n'est que le début. » ;
 *   - `needDescription` verbatim depuis la config ;
 *   - trois phases MAINTENIR / COMPRENDRE / ADAPTER, chacune reliée à un
 *     `service` config (Maintenance et sécurité / Mesure et amélioration
 *     continue / Évolutions fonctionnelles).
 *
 * Accessibilité : `<section aria-labelledby>` pointe sur le H2, la liste
 * reste une vraie `<ol role="list">`, tous les décors SVG restent
 * `aria-hidden="true"`.
 */

interface Props {
  needDescription: string
}

defineProps<Props>()

interface Phase {
  readonly key: string
  readonly index: string
  readonly label: string
  readonly title: string
  readonly note: string
  readonly link: string
}

const phases: readonly Phase[] = [
  {
    key: "maintain",
    index: "01",
    label: "Maintenir",
    title: "Garder le socle sain et à jour.",
    note:
      "Vous avez un site en production. Les mises à jour techniques, les correctifs de sécurité et le suivi des dépendances doivent être appliqués régulièrement, sans attendre un incident.",
    link: "Maintenance et sécurité",
  },
  {
    key: "understand",
    index: "02",
    label: "Comprendre",
    title: "Observer l'usage réel du produit.",
    note:
      "Vous voulez savoir comment votre site est réellement utilisé — pages parcourues, points de friction, performances côté visiteur — pour orienter les évolutions avec des repères concrets.",
    link: "Mesure et amélioration continue",
  },
  {
    key: "adapt",
    index: "03",
    label: "Adapter",
    title: "Faire évoluer l'outil avec l'activité.",
    note:
      "Vos usages métier changent, vos publics évoluent, vos priorités se réajustent. Le produit doit suivre : nouvelles fonctionnalités, correctifs, contenus additionnels, priorisés avec vous.",
    link: "Évolutions fonctionnelles",
  },
]

const generatedId = useId()
const titleId = computed(() => `faire-evoluer-need-title-${generatedId}`)
</script>

<template>
  <section
    class="faire-evoluer-need"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="faire-evoluer-need__container">
      <header class="faire-evoluer-need__header">
        <div class="faire-evoluer-need__header-left">
          <BaseEyebrow class="faire-evoluer-need__eyebrow">
            À qui cela s'adresse
          </BaseEyebrow>
          <h2 :id="titleId" class="faire-evoluer-need__title">
            Quand la mise en ligne n'est que le début.
          </h2>
        </div>
        <p class="faire-evoluer-need__lead">
          {{ needDescription }}
        </p>
      </header>

      <div class="faire-evoluer-need__composition">
        <!-- Trajectoire partagée : courbe petrol continue qui traverse les
             trois zones + trois checkpoints alignés au-dessus des colonnes.
             Purement décoratif : caché sur mobile (les phases sont alors
             reliées par une ligne verticale via ::before sur la liste). -->
        <svg
          class="faire-evoluer-need__trail"
          viewBox="0 0 1200 90"
          role="presentation"
          focusable="false"
          aria-hidden="true"
          preserveAspectRatio="none"
        >
          <path
            d="M 40 60 Q 200 12, 400 46 T 800 46 T 1160 60"
            stroke="#0c5b57"
            stroke-width="1.5"
            fill="none"
            stroke-linecap="round"
          />
          <path
            d="M 40 74 Q 300 92, 600 74 T 1160 74"
            stroke="#0c5b57"
            stroke-width="1"
            fill="none"
            stroke-dasharray="3 6"
            opacity="0.35"
            stroke-linecap="round"
          />
          <circle cx="200" cy="32" r="6" fill="#0c5b57" />
          <circle cx="600" cy="42" r="6" fill="#0c5b57" />
          <circle cx="1000" cy="42" r="6" fill="#0c5b57" />
          <g
            font-family="Space Mono, monospace"
            font-size="10"
            fill="#0c5b57"
            opacity="0.65"
            font-weight="700"
          >
            <text
              x="600"
              y="16"
              text-anchor="middle"
              letter-spacing="3.4"
            >
              CONTINUOUS CARE
            </text>
          </g>
        </svg>

        <ol class="faire-evoluer-need__list" role="list">
          <li
            v-for="(phase, index) in phases"
            :key="phase.key"
            class="faire-evoluer-need__phase"
            :data-position="index + 1"
          >
            <div class="faire-evoluer-need__phase-header">
              <p class="faire-evoluer-need__phase-index" aria-hidden="true">
                {{ phase.index }}
              </p>
              <p class="faire-evoluer-need__phase-label" aria-hidden="true">
                {{ phase.label }}
              </p>
            </div>
            <h3 class="faire-evoluer-need__phase-title">
              {{ phase.title }}
            </h3>
            <p class="faire-evoluer-need__phase-note">
              {{ phase.note }}
            </p>
            <p class="faire-evoluer-need__phase-link">
              <span class="faire-evoluer-need__phase-link-label">Prestation associée</span>
              <span class="faire-evoluer-need__phase-link-value">{{ phase.link }}</span>
            </p>
          </li>
        </ol>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.faire-evoluer-need {
  position: relative;
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.faire-evoluer-need::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background-color: var(--border-default);
}

.faire-evoluer-need__container {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: var(--space-10);
}

.faire-evoluer-need__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.faire-evoluer-need__header-left {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 24ch;
}

.faire-evoluer-need__eyebrow {
  margin: 0;
}

.faire-evoluer-need__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 3.8vw, 2.75rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
}

.faire-evoluer-need__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

/*
 * Composition mobile : les phases sont reliées par une fine ligne petrol
 * verticale (via ::before sur la liste) et chaque phase porte un petit
 * point d'ancrage à sa hauteur — pas de cards, pas de bordures.
 */
.faire-evoluer-need__composition {
  position: relative;
}

.faire-evoluer-need__trail {
  display: none;
}

.faire-evoluer-need__list {
  list-style: none;
  margin: 0;
  padding: 0;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.faire-evoluer-need__list::before {
  content: "";
  position: absolute;
  left: 6px;
  top: 8px;
  bottom: 8px;
  width: 1px;
  background: linear-gradient(
    to bottom,
    var(--color-petrol) 0%,
    var(--color-petrol) 92%,
    transparent 100%
  );
  opacity: 0.5;
}

.faire-evoluer-need__phase {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding-inline-start: var(--space-6);
}

.faire-evoluer-need__phase::before {
  content: "";
  position: absolute;
  left: 0;
  top: 6px;
  width: 13px;
  height: 13px;
  border-radius: 50%;
  background-color: var(--color-petrol);
  box-shadow: 0 0 0 3px var(--background-secondary);
}

.faire-evoluer-need__phase-header {
  display: flex;
  align-items: baseline;
  gap: var(--space-3);
}

.faire-evoluer-need__phase-index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.14em;
  color: var(--color-petrol);
  margin: 0;
}

.faire-evoluer-need__phase-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: clamp(1.125rem, 2.5vw, 1.5rem);
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
  line-height: 1;
}

.faire-evoluer-need__phase-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(1.125rem, 1.7vw, 1.3125rem);
  line-height: 1.25;
  color: var(--text-primary);
  margin: var(--space-1) 0 0;
  max-width: 30ch;
}

.faire-evoluer-need__phase-note {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 52ch;
}

.faire-evoluer-need__phase-link {
  display: inline-flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--space-2);
  margin: var(--space-2) 0 0;
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.faire-evoluer-need__phase-link-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.faire-evoluer-need__phase-link-value {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  color: var(--color-petrol);
}

@media (min-width: 768px) {
  .faire-evoluer-need {
    padding-block: var(--space-16) var(--space-16);
  }
}

/*
 * Desktop : header en deux colonnes (titre gauche ~38 %, lead droit
 * ~62 %), puis composition partagée avec trajectoire petrol continue
 * au-dessus des trois zones. Pas de cards : filets verticaux fins
 * séparent les colonnes 2 et 3.
 */
@media (min-width: 1024px) {
  .faire-evoluer-need__container {
    gap: var(--space-12);
  }

  .faire-evoluer-need__header {
    display: grid;
    grid-template-columns: minmax(0, 38fr) minmax(0, 62fr);
    gap: var(--space-10);
    align-items: end;
  }

  .faire-evoluer-need__header-left {
    max-width: none;
  }

  .faire-evoluer-need__composition {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
  }

  .faire-evoluer-need__trail {
    display: block;
    width: 100%;
    height: 72px;
  }

  .faire-evoluer-need__list::before {
    display: none;
  }

  .faire-evoluer-need__list {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0;
  }

  .faire-evoluer-need__phase {
    padding: var(--space-2) var(--space-6) var(--space-2);
    padding-inline-start: var(--space-6);
    gap: var(--space-3);
  }

  .faire-evoluer-need__phase::before {
    display: none;
  }

  .faire-evoluer-need__phase[data-position="2"],
  .faire-evoluer-need__phase[data-position="3"] {
    border-left: 1px solid var(--border-default);
  }

  .faire-evoluer-need__phase[data-position="1"] {
    padding-inline-start: 0;
  }
}

@media (min-width: 1440px) {
  .faire-evoluer-need__phase {
    padding-inline: var(--space-8);
  }

  .faire-evoluer-need__phase[data-position="1"] {
    padding-inline-start: 0;
  }
}
</style>
