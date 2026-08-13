<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

type FaqItem = {
  question: string
  answer: readonly string[]
}

/**
 * FAQ de fin de page.
 *
 * Le composant s'appuie sur `<details>/<summary>` : l'accordéon reste
 * utilisable au clavier et sans JavaScript, et tout son contenu est présent
 * dans le HTML SSR. L'attribut `name` limite le groupe à une réponse ouverte
 * à la fois sur les navigateurs qui prennent en charge ce comportement natif.
 */
const faqItems: readonly FaqItem[] = [
  {
    question: "Quels types de projets pouvez-vous accompagner ?",
    answer: [
      "Nous accompagnons les entreprises sur différents types de projets digitaux : création ou refonte de sites internet, e-commerce, applications web et outils métier, design UI/UX, identité visuelle, création de contenu, photographie professionnelle, référencement naturel et maintenance.",
      "Notre approche consiste à réunir les compétences réellement utiles au projet plutôt qu’à proposer une solution standardisée.",
    ],
  },
  {
    question:
      "Pouvez-vous prendre en charge un projet digital dans sa globalité ?",
    answer: [
      "Oui. Selon les besoins, nous pouvons intervenir depuis la réflexion initiale jusqu’à la mise en ligne et au suivi du projet : stratégie, architecture, UX/UI, développement, contenu, photographie, SEO, optimisation et maintenance.",
      "L’objectif est de construire un ensemble cohérent plutôt que de traiter séparément le site, l’image et la visibilité de l’entreprise.",
    ],
  },
  {
    question: "Comment se déroule un projet avec Devzair ?",
    answer: [
      "Nous commençons par comprendre votre activité, vos objectifs, vos utilisateurs et les contraintes du projet. Nous définissons ensuite le périmètre, les priorités et la solution à mettre en place.",
      "Le projet peut ensuite passer par différentes étapes : cadrage, conception UX/UI, création des contenus, développement, contrôles, mise en ligne puis accompagnement et évolution.",
    ],
  },
  {
    question: "Proposez-vous des solutions entièrement sur mesure ?",
    answer: [
      "Nous adaptons la solution au besoin réel de chaque entreprise. Cela peut concerner le design, l'architecture du site, les fonctionnalités, les outils utilisés ou encore les intégrations avec votre environnement existant.",
      "Nous privilégions une solution utile, maintenable et évolutive plutôt que d’ajouter des fonctionnalités qui n’apportent pas de valeur au projet.",
    ],
  },
  {
    question:
      "Le référencement naturel est-il pris en compte lors de la création d’un site ?",
    answer: [
      "Oui. La structure du site, ses performances, son contenu, son accessibilité aux moteurs de recherche et plusieurs éléments techniques peuvent être pensés pour favoriser une base SEO saine dès la conception.",
      "Lorsqu’un accompagnement SEO plus complet est nécessaire, nous pouvons également travailler sur la stratégie sémantique, les contenus, le maillage interne, la visibilité locale et le suivi du référencement.",
    ],
  },
  {
    question: "Comment sont déterminés le budget et le délai d’un projet ?",
    answer: [
      "Ils dépendent notamment du périmètre, du nombre de pages ou de fonctionnalités, du niveau de personnalisation, des contenus à produire, des intégrations nécessaires et des contraintes techniques.",
      "Après un premier échange et un cadrage du besoin, nous pouvons définir plus précisément la solution à réaliser, les priorités du projet et son organisation. Nous préférons établir une proposition adaptée plutôt que d’annoncer un prix ou un délai générique sans connaître le projet.",
    ],
  },
  {
    question:
      "Pouvez-vous continuer à nous accompagner après la mise en ligne ?",
    answer: [
      "Oui. Un projet digital continue généralement d’évoluer après son lancement. Selon les besoins convenus, nous pouvons assurer la maintenance, les mises à jour, la supervision, les corrections, les optimisations et le développement de nouvelles fonctionnalités.",
      "L’objectif est de conserver une solution fiable et de pouvoir la faire évoluer avec l’activité de l’entreprise.",
    ],
  },
]
</script>

<template>
  <section
    id="faq"
    class="home-faq"
    aria-labelledby="home-faq-title"
  >
    <BaseContainer width="wide" class="home-faq__container">
      <header class="home-faq__intro">
        <BaseEyebrow>Questions fréquentes</BaseEyebrow>
        <h2 id="home-faq-title" class="home-faq__title">
          <span class="home-faq__title-line">Vos questions,</span>
          {{ " " }}
          <span class="home-faq__title-accent">nos réponses.</span>
        </h2>
        <p class="home-faq__lead">
          Les informations essentielles pour comprendre notre manière de
          concevoir, réaliser et faire évoluer votre projet digital.
        </p>
      </header>

      <div class="home-faq__list">
        <details
          v-for="(item, index) in faqItems"
          :key="item.question"
          class="home-faq__item"
          name="home-faq"
          :open="index === 0"
        >
          <summary class="home-faq__summary">
            <span class="home-faq__index" aria-hidden="true">
              {{ String(index + 1).padStart(2, "0") }}
            </span>
            <span class="home-faq__question">{{ item.question }}</span>
            <span class="home-faq__icon" aria-hidden="true">
              <span class="home-faq__icon-line home-faq__icon-line--horizontal" />
              <span class="home-faq__icon-line home-faq__icon-line--vertical" />
            </span>
          </summary>

          <div class="home-faq__answer">
            <p v-for="paragraph in item.answer" :key="paragraph">
              {{ paragraph }}
            </p>
          </div>
        </details>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-faq {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  padding-block: var(--space-16) var(--space-20);
  background-color: var(--background-secondary);
  color: var(--text-primary);
  border-bottom: 1px solid var(--border-default);
}

.home-faq::before {
  content: "";
  position: absolute;
  z-index: -1;
  top: -14rem;
  right: -12rem;
  width: 34rem;
  aspect-ratio: 1;
  border-radius: 50%;
  background: radial-gradient(
    circle,
    rgba(46, 134, 217, 0.11) 0%,
    rgba(46, 134, 217, 0) 68%
  );
  pointer-events: none;
}

.home-faq__container {
  display: grid;
  gap: var(--space-10);
}

.home-faq__intro {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--space-4);
  max-width: 34rem;
}

.home-faq__title {
  margin: var(--space-2) 0 0;
  max-width: 13ch;
  font-family: var(--font-family-heading);
  font-size: clamp(2.25rem, 5vw, 4.25rem);
  font-weight: var(--font-weight-heading);
  line-height: 0.98;
  letter-spacing: -0.045em;
  color: var(--text-primary);
}

.home-faq__title-line {
  display: block;
}

.home-faq__title-accent {
  color: var(--color-petrol);
}

.home-faq__lead {
  margin: var(--space-2) 0 0;
  max-width: 43ch;
  color: var(--text-secondary);
  font-size: 1rem;
  line-height: 1.65;
}

.home-faq__list {
  min-width: 0;
  border-top: 1px solid var(--border-default);
}

.home-faq__item {
  border-bottom: 1px solid var(--border-default);
  transition: background-color var(--duration-base) var(--ease-out);
}

.home-faq__item[open] {
  background-color: var(--surface-primary);
}

.home-faq__summary {
  display: grid;
  grid-template-columns: 2rem minmax(0, 1fr) 2.75rem;
  gap: var(--space-3);
  align-items: center;
  min-height: 5.5rem;
  padding: var(--space-5) var(--space-2);
  color: var(--text-primary);
  cursor: pointer;
  list-style: none;
}

.home-faq__summary::-webkit-details-marker {
  display: none;
}

.home-faq__summary::marker {
  content: "";
}

.home-faq__index {
  align-self: start;
  padding-top: 0.22rem;
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: var(--font-weight-mono);
  letter-spacing: 0.06em;
  color: var(--color-petrol);
}

.home-faq__question {
  font-family: var(--font-family-heading);
  font-size: clamp(1.0625rem, 1.8vw, 1.25rem);
  font-weight: var(--font-weight-heading-medium);
  line-height: 1.35;
  letter-spacing: -0.015em;
  text-wrap: balance;
}

.home-faq__icon {
  position: relative;
  display: grid;
  place-items: center;
  width: 2.75rem;
  height: 2.75rem;
  border: 1px solid rgba(46, 134, 217, 0.45);
  border-radius: 50%;
  background-color: rgba(46, 134, 217, 0.08);
  color: var(--color-devzair-blue);
  transition:
    background-color var(--duration-fast) var(--ease-out),
    border-color var(--duration-fast) var(--ease-out),
    transform var(--duration-base) var(--ease-out);
}

.home-faq__icon-line {
  position: absolute;
  width: 0.875rem;
  height: 1.5px;
  border-radius: var(--radius-pill);
  background-color: currentColor;
  transition: transform var(--duration-base) var(--ease-out);
}

.home-faq__icon-line--vertical {
  transform: rotate(90deg);
}

.home-faq__item[open] .home-faq__icon {
  border-color: var(--color-devzair-blue);
  background-color: rgba(46, 134, 217, 0.16);
  transform: rotate(180deg);
}

.home-faq__item[open] .home-faq__icon-line--vertical {
  transform: rotate(0deg);
}

.home-faq__answer {
  display: grid;
  gap: var(--space-3);
  padding: 0 calc(2.75rem + var(--space-5)) var(--space-6)
    calc(2rem + var(--space-5));
  animation: home-faq-reveal var(--duration-base) var(--ease-out) both;
}

.home-faq__answer p {
  margin: 0;
  max-width: 72ch;
  color: var(--text-secondary);
  font-size: 0.9375rem;
  line-height: 1.7;
}

.home-faq__summary:hover .home-faq__icon {
  border-color: var(--color-devzair-blue);
  background-color: rgba(46, 134, 217, 0.16);
}

@keyframes home-faq-reveal {
  from {
    opacity: 0;
    transform: translateY(-0.35rem);
  }

  to {
    opacity: 1;
    transform: none;
  }
}

@media (min-width: 768px) {
  .home-faq {
    padding-block: var(--space-20) var(--space-24);
  }

  .home-faq__summary {
    grid-template-columns: 3rem minmax(0, 1fr) 2.75rem;
    gap: var(--space-4);
    padding-inline: var(--space-4);
  }

  .home-faq__answer {
    padding-right: calc(2.75rem + var(--space-8));
    padding-left: calc(3rem + var(--space-8));
  }
}

@media (min-width: 1024px) {
  .home-faq__container {
    grid-template-columns: minmax(17rem, 0.72fr) minmax(0, 1.28fr);
    gap: clamp(var(--space-12), 7vw, 7rem);
    align-items: start;
  }

  .home-faq__intro {
    position: sticky;
    top: calc(var(--site-header-height) + var(--space-8));
  }
}

@media (prefers-reduced-motion: reduce) {
  .home-faq__item,
  .home-faq__icon,
  .home-faq__icon-line {
    transition: none;
  }

  .home-faq__answer {
    animation: none;
    opacity: 1;
    transform: none;
  }
}
</style>
