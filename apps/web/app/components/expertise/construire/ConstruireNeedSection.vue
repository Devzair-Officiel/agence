<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « À qui cela s'adresse » de la page Construire.
 *
 * Rendu éditorial (colonnes narratives, séparateurs mono) plutôt que
 * cartes SaaS génériques. Compose :
 *   - eyebrow « À qui cela s'adresse » (préservé pour l'e2e existant) ;
 *   - H2 validé « Quand le projet doit devenir un outil. » ;
 *   - `needDescription` verbatim (issu de `expertise-pages.ts`) ;
 *   - trois situations validées (PRÉSENTER / VENDRE / ORGANISER) avec un
 *     mot-clef, un objectif court et le format retenu.
 *
 * Les trois situations sont statiques (le brief éditorial les fige) et
 * n'inventent aucune promesse ni chiffre : elles reformulent les mots déjà
 * présents dans `services` (Site vitrine / E-commerce / Application métier).
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
  readonly href: string
}

const situations: readonly Situation[] = [
  {
    key: "presenter",
    verb: "Présenter",
    label: "Présenter votre activité clairement, avec un site cohérent avec votre identité.",
    format: "Site vitrine sur mesure",
    href: "/services/creation-site-internet",
  },
  {
    key: "vendre",
    verb: "Vendre",
    label: "Vendre en ligne avec une plateforme fiable, adaptée à votre catalogue et à vos flux.",
    format: "Plateforme e-commerce",
    href: "/services/site-e-commerce",
  },
  {
    key: "organiser",
    verb: "Organiser",
    label: "Organiser vos opérations quotidiennes avec un outil taillé pour vos processus.",
    format: "Application métier",
    href: "/services/application-web-metier",
  },
]

const generatedId = useId()
const titleId = computed(() => `construire-need-title-${generatedId}`)
</script>

<template>
  <section
    class="construire-need"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="construire-need__container">
      <header class="construire-need__header">
        <BaseEyebrow class="construire-need__eyebrow">
          À qui cela s'adresse
        </BaseEyebrow>
        <h2 :id="titleId" class="construire-need__title">
          Quand le projet doit devenir un outil.
        </h2>
        <p class="construire-need__lead">
          {{ needDescription }}
        </p>
      </header>

      <ol class="construire-need__situations" role="list">
        <li
          v-for="(situation, index) in situations"
          :key="situation.key"
          class="construire-need__situation"
        >
          <span class="construire-need__situation-index" aria-hidden="true">
            {{ String(index + 1).padStart(2, "0") }}
          </span>
          <div class="construire-need__situation-body">
            <p class="construire-need__situation-verb" aria-hidden="true">
              {{ situation.verb }}
            </p>
            <p class="construire-need__situation-label">
              {{ situation.label }}
            </p>
            <p class="construire-need__situation-format">
              <span class="construire-need__situation-format-label">Format</span>
              <NuxtLink
                :to="situation.href"
                class="construire-need__situation-format-value construire-need__situation-format-link"
              >{{ situation.format }}</NuxtLink>
            </p>
          </div>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.construire-need {
  position: relative;
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.construire-need::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(
    to right,
    transparent,
    rgba(46, 134, 217, 0.4) 20%,
    rgba(12, 91, 87, 0.4) 80%,
    transparent
  );
}

.construire-need__container {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: var(--space-10);
  align-items: start;
}

.construire-need__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 52rem;
}

.construire-need__eyebrow {
  margin: 0;
}

.construire-need__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 3.8vw, 2.75rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 22ch;
}

.construire-need__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

.construire-need__situations {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  border-top: 1px solid var(--border-default);
  max-width: 46rem;
}

.construire-need__situation {
  position: relative;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  column-gap: var(--space-5);
  row-gap: 0;
  padding: var(--space-7) 0;
  border-bottom: 1px solid var(--border-default);
}

.construire-need__situation-index {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.75rem;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
  align-self: start;
  padding-top: 0.15rem;
}

.construire-need__situation-body {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  min-width: 0;
}

.construire-need__situation-verb {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.75rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.construire-need__situation-label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(1.0625rem, 1.6vw, 1.1875rem);
  line-height: 1.35;
  color: var(--text-primary);
  margin: 0;
  max-width: 40ch;
}

.construire-need__situation-format {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  margin: var(--space-1) 0 0;
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.construire-need__situation-format-label {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.6875rem;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.construire-need__situation-format-value {
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-size: 0.75rem;
  font-family: var(--font-family-mono);
  color: var(--color-petrol);
}

.construire-need__situation-format-link {
  text-decoration: none;
  border-bottom: 1px solid currentColor;
  padding-bottom: 1px;
  transition: color var(--duration-fast, 0.12s) ease;
}

.construire-need__situation-format-link:hover {
  color: var(--color-navy);
}

@media (min-width: 768px) {
  .construire-need {
    padding-block: var(--space-16) var(--space-16);
  }
}

/*
 * Desktop : composition 2 colonnes.
 * Le bloc titre reste compact à gauche (≈ 40 %), les situations forment
 * une pile éditoriale à droite (≈ 60 %) plutôt qu'un tableau large.
 */
@media (min-width: 1024px) {
  .construire-need__container {
    grid-template-columns: minmax(0, 4fr) minmax(0, 6fr);
    column-gap: var(--space-12);
    row-gap: var(--space-8);
  }

  .construire-need__header {
    position: sticky;
    top: calc(var(--site-header-height) + var(--space-6));
    align-self: start;
    scroll-margin-top: calc(var(--site-header-height) + var(--space-6));
  }

  .construire-need__situations {
    max-width: none;
  }

  .construire-need__situation {
    padding: var(--space-8) 0;
    grid-template-columns: 3rem minmax(0, 1fr);
    column-gap: var(--space-6);
  }
}

@media (min-width: 1440px) {
  .construire-need__container {
    column-gap: var(--space-14);
  }
}
</style>
