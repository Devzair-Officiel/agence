<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « À qui cela s'adresse » de la page Valoriser.
 *
 * Direction éditoriale asymétrique — pas trois cards uniformes comme sur
 * un SaaS, pas la même composition que Construire (deux colonnes
 * situations narratives). On rend une planche à trois blocs de largeurs
 * volontairement différentes, indentés, avec repères mono, pour évoquer
 * une mise en page de magazine.
 *
 * Contenu :
 *   - eyebrow verbatim : « À qui cela s'adresse » ;
 *   - H2 validé : « Quand votre savoir-faire mérite d'être mieux montré. » ;
 *   - `needDescription` verbatim depuis la config ;
 *   - trois situations validées MONTRER / EXPLIQUER / STRUCTURER, avec
 *     un mot-clef, une phrase courte et le format retenu (reprises
 *     verbatim de `services` dans `expertise-pages.ts`).
 *
 * Aucune promesse chiffrée, aucun contenu inventé au-delà de ces mots-clefs.
 */

interface Props {
  needDescription: string
}

defineProps<Props>()

interface Situation {
  readonly key: string
  readonly verb: string
  readonly label: string
  readonly format: string
  readonly href?: string
}

const situations: readonly Situation[] = [
  {
    key: "montrer",
    verb: "Montrer",
    label:
      "Rendre visible ce que vous faites : lieux, gestes, produits, personnes.",
    format: "Photographie professionnelle",
    href: "/services/photographie-creation-contenu",
  },
  {
    key: "expliquer",
    verb: "Expliquer",
    label:
      "Traduire votre activité en mots précis, adaptés à chaque page importante.",
    format: "Contenus rédactionnels",
  },
  {
    key: "structurer",
    verb: "Structurer",
    label:
      "Organiser l'offre et les messages pour qu'un visiteur les saisisse rapidement.",
    format: "Structuration de l'offre",
  },
]

const generatedId = useId()
const titleId = computed(() => `valoriser-need-title-${generatedId}`)
</script>

<template>
  <section
    class="valoriser-need"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="valoriser-need__container">
      <header class="valoriser-need__header">
        <BaseEyebrow class="valoriser-need__eyebrow">
          À qui cela s'adresse
        </BaseEyebrow>
        <h2 :id="titleId" class="valoriser-need__title">
          Quand votre savoir-faire mérite d'être mieux montré.
        </h2>
        <p class="valoriser-need__lead">
          {{ needDescription }}
        </p>
      </header>

      <ol class="valoriser-need__spread" role="list">
        <li
          v-for="(situation, index) in situations"
          :key="situation.key"
          class="valoriser-need__panel"
          :data-position="index + 1"
        >
          <span class="valoriser-need__panel-index" aria-hidden="true">
            {{ String(index + 1).padStart(2, "0") }}
          </span>
          <div class="valoriser-need__panel-body">
            <p class="valoriser-need__panel-verb" aria-hidden="true">
              {{ situation.verb }}
            </p>
            <p class="valoriser-need__panel-label">
              {{ situation.label }}
            </p>
            <p class="valoriser-need__panel-format">
              <span class="valoriser-need__panel-format-label">Format</span>
              <NuxtLink
                v-if="situation.href"
                :to="situation.href"
                class="valoriser-need__panel-format-value valoriser-need__panel-format-link"
              >{{ situation.format }}</NuxtLink>
              <span v-else class="valoriser-need__panel-format-value">{{ situation.format }}</span>
            </p>
          </div>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
/*
 * Section cream. Séparateur mono en tête (mince ligne de composition).
 */
.valoriser-need {
  position: relative;
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.valoriser-need::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background-color: var(--border-default);
}

.valoriser-need__container {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: var(--space-10);
}

.valoriser-need__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 52rem;
}

.valoriser-need__eyebrow {
  margin: 0;
}

.valoriser-need__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 3.8vw, 2.75rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 22ch;
}

.valoriser-need__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

/*
 * Planche : mobile en pile pleine largeur. Desktop en composition
 * asymétrique — panneau 1 (MONTRER) large et dominant à gauche, panneau
 * 2 (EXPLIQUER) plus étroit indenté à droite, panneau 3 (STRUCTURER)
 * repositionné en légère indentation. On ne rend PAS trois cartes
 * uniformes.
 */
.valoriser-need__spread {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.valoriser-need__panel {
  position: relative;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  column-gap: var(--space-5);
  padding: var(--space-6);
  background-color: var(--color-cream-elevated);
  border: 1px solid var(--border-default);
}

.valoriser-need__panel-index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
  align-self: start;
  padding-top: 0.15rem;
}

.valoriser-need__panel-body {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  min-width: 0;
}

.valoriser-need__panel-verb {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.valoriser-need__panel-label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(1.0625rem, 1.6vw, 1.1875rem);
  line-height: 1.35;
  color: var(--text-primary);
  margin: 0;
  max-width: 40ch;
}

.valoriser-need__panel-format {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  margin: var(--space-1) 0 0;
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.valoriser-need__panel-format-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.valoriser-need__panel-format-value {
  font-weight: var(--font-weight-mono);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-size: 0.75rem;
  font-family: var(--font-family-mono);
  color: var(--color-petrol);
}

.valoriser-need__panel-format-link {
  text-decoration: none;
  border-bottom: 1px solid currentColor;
  padding-bottom: 1px;
  transition: color var(--duration-fast, 0.12s) ease;
}

.valoriser-need__panel-format-link:hover {
  color: var(--color-navy);
}

@media (min-width: 768px) {
  .valoriser-need {
    padding-block: var(--space-16) var(--space-16);
  }
}

/*
 * Desktop : composition asymétrique. Grille 12 colonnes ; le panneau 1
 * occupe une large bande à gauche, le panneau 2 est indenté à droite en
 * décalage, le panneau 3 est indenté à gauche en décalage — évoque une
 * mise en page éditoriale.
 */
@media (min-width: 1024px) {
  .valoriser-need__spread {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: var(--space-6) var(--space-5);
  }

  .valoriser-need__panel[data-position="1"] {
    grid-column: 1 / span 8;
    padding: 1.75rem;
  }

  .valoriser-need__panel[data-position="2"] {
    grid-column: 5 / span 7;
    padding: 1.75rem;
  }

  .valoriser-need__panel[data-position="3"] {
    grid-column: 2 / span 8;
    padding: 1.75rem;
  }

  .valoriser-need__panel[data-position="1"] .valoriser-need__panel-label {
    font-size: 1.3125rem;
    max-width: 46ch;
  }
}

@media (min-width: 1440px) {
  .valoriser-need__spread {
    gap: var(--space-8) var(--space-6);
  }
}
</style>
