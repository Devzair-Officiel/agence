<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « À qui cela s'adresse » de la page Visibilité.
 *
 * Direction éditoriale distincte de Concevoir (deux colonnes narratives),
 * Construire (situations empilées) et Valoriser (planche à trois blocs
 * indentés) : on rend trois « zones » cartographiques évoquant trois
 * périmètres de recherche différents. Chaque zone porte :
 *   - un identifiant type ZONE 01/02/03 (mono petrol) ;
 *   - un verbe court en majuscules Space Mono ;
 *   - la portée signal-radius (petit repère mono) ;
 *   - une phrase narrative reprenant l'intention validée.
 *
 * Contenu :
 *   - eyebrow verbatim : « À qui cela s'adresse » ;
 *   - H2 validé : « Trois territoires de visibilité. » ;
 *   - `needDescription` verbatim depuis la config ;
 *   - trois zones fixes : ÊTRE TROUVÉ / ÊTRE PRÉSENT LOCALEMENT / RESTER
 *     VISIBLE, reformulations narratives des trois `services` de la config
 *     (SEO / Visibilité locale / Stratégie éditoriale).
 *
 * Aucune promesse chiffrée, aucun contenu inventé au-delà de ces intitulés.
 */

interface Props {
  needDescription: string
}

defineProps<Props>()

interface Zone {
  readonly key: string
  readonly ring: string
  readonly title: string
  readonly note: string
  readonly scope: string
}

const zones: readonly Zone[] = [
  {
    key: "found",
    ring: "Zone 01",
    title: "Être trouvé",
    note:
      "Vous avez un site mais peu de trafic qualifié. Les personnes qui cherchent votre activité ne tombent pas sur vos pages.",
    scope: "SEO · organique",
  },
  {
    key: "local",
    ring: "Zone 02",
    title: "Être présent localement",
    note:
      "Vous adressez une zone géographique précise. Vos clients potentiels cherchent sur cette zone, souvent depuis un mobile.",
    scope: "Local · NAP",
  },
  {
    key: "stay",
    ring: "Zone 03",
    title: "Rester visible dans la durée",
    note:
      "Vous voulez structurer une production de contenus qui construit une présence stable, sans dépendre uniquement de la publicité payante.",
    scope: "Éditorial · durée",
  },
]

const generatedId = useId()
const titleId = computed(() => `visibilite-need-title-${generatedId}`)
</script>

<template>
  <section
    class="visibilite-need"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="visibilite-need__container">
      <header class="visibilite-need__header">
        <BaseEyebrow class="visibilite-need__eyebrow">
          À qui cela s'adresse
        </BaseEyebrow>
        <h2 :id="titleId" class="visibilite-need__title">
          Trois territoires de visibilité.
        </h2>
        <p class="visibilite-need__lead">
          {{ needDescription }}
        </p>
      </header>

      <ol class="visibilite-need__zones" role="list">
        <li
          v-for="(zone, index) in zones"
          :key="zone.key"
          class="visibilite-need__zone"
          :data-position="index + 1"
        >
          <div class="visibilite-need__zone-rings" aria-hidden="true">
            <svg
              viewBox="0 0 60 60"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <circle cx="30" cy="30" r="26" fill="none" stroke="#0c5b57" stroke-width="0.6" opacity="0.35" />
              <circle cx="30" cy="30" r="18" fill="none" stroke="#0c5b57" stroke-width="0.8" opacity="0.55" />
              <circle cx="30" cy="30" r="10" fill="none" stroke="#0c5b57" stroke-width="1" opacity="0.85" />
              <circle cx="30" cy="30" r="3" fill="#0c5b57" />
            </svg>
          </div>

          <div class="visibilite-need__zone-body">
            <p class="visibilite-need__zone-ring" aria-hidden="true">
              {{ zone.ring }}
            </p>
            <h3 class="visibilite-need__zone-title">
              {{ zone.title }}
            </h3>
            <p class="visibilite-need__zone-note">
              {{ zone.note }}
            </p>
            <p class="visibilite-need__zone-scope">
              <span class="visibilite-need__zone-scope-label">Portée</span>
              <span class="visibilite-need__zone-scope-value">{{ zone.scope }}</span>
            </p>
          </div>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
/*
 * Section cream (background-secondary). Séparateur haut fin, pour asseoir
 * la transition depuis le hero cream-sand.
 */
.visibilite-need {
  position: relative;
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.visibilite-need::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background-color: var(--border-default);
}

.visibilite-need__container {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: var(--space-10);
}

.visibilite-need__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 52rem;
}

.visibilite-need__eyebrow {
  margin: 0;
}

.visibilite-need__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 3.8vw, 2.75rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 22ch;
}

.visibilite-need__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

/*
 * Zones : mobile en pile pleine largeur. Chaque zone est un grand bloc
 * cream-elevated avec anneaux SVG à gauche et texte à droite.
 */
.visibilite-need__zones {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.visibilite-need__zone {
  position: relative;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  column-gap: var(--space-5);
  padding: var(--space-6);
  background-color: var(--color-cream-elevated);
  border: 1px solid var(--border-default);
}

.visibilite-need__zone-rings {
  width: 3.5rem;
  height: 3.5rem;
  flex-shrink: 0;
}

.visibilite-need__zone-rings svg {
  width: 100%;
  height: 100%;
  display: block;
}

.visibilite-need__zone-body {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  min-width: 0;
}

.visibilite-need__zone-ring {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.visibilite-need__zone-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(1.125rem, 1.7vw, 1.3125rem);
  line-height: 1.25;
  color: var(--text-primary);
  margin: 0;
  max-width: 30ch;
}

.visibilite-need__zone-note {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 52ch;
}

.visibilite-need__zone-scope {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  margin: var(--space-1) 0 0;
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.visibilite-need__zone-scope-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.visibilite-need__zone-scope-value {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--color-petrol);
}

@media (min-width: 768px) {
  .visibilite-need {
    padding-block: var(--space-16) var(--space-16);
  }
}

/*
 * Desktop : composition cartographique légèrement décalée. Chaque zone
 * conserve la même largeur mais est légèrement indentée pour évoquer
 * trois périmètres qui se chevauchent visuellement, sans être
 * cartes-uniformes SaaS.
 */
@media (min-width: 1024px) {
  .visibilite-need__zones {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: var(--space-5);
  }

  .visibilite-need__zone[data-position="1"] {
    grid-column: 1 / span 9;
    padding: 1.75rem;
  }

  .visibilite-need__zone[data-position="2"] {
    grid-column: 4 / span 9;
    padding: 1.75rem;
  }

  .visibilite-need__zone[data-position="3"] {
    grid-column: 2 / span 9;
    padding: 1.75rem;
  }

  .visibilite-need__zone-rings {
    width: 4rem;
    height: 4rem;
  }
}

@media (min-width: 1440px) {
  .visibilite-need__zones {
    gap: var(--space-6);
  }
}
</style>
