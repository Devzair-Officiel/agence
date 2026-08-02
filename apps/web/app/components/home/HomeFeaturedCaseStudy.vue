<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « Réalisations » — état honnête.
 *
 * Aucun projet Devzair n'est aujourd'hui documenté avec l'ensemble des
 * éléments exigés par `docs/01-CONTENT.md §7.6` (client autorisé, contexte,
 * résultats mesurables, méthode de mesure, période, visuels autorisés).
 * L'AGENTS.md rule 1 interdit d'inventer un client, un résultat ou un visuel.
 *
 * Cette section publie donc la version « en préparation » validée par la
 * politique éditoriale Phase 5C : eyebrow, titre, paragraphe explicatif,
 * ancre `#realisations` opérationnelle. Ni faux bouton « Voir toutes les
 * réalisations », ni faux chiffre, ni skeleton loader. Le CTA hero
 * « Découvrir nos réalisations » atterrit ici et l'utilisateur comprend
 * immédiatement l'état réel.
 *
 * Le jour où DEV-003 sera livré, on remplacera le bloc `honest-state` par
 * la variante détaillée sans changer l'ancre ni le titre externe.
 */
</script>

<template>
  <section
    id="realisations"
    class="home-case"
    aria-labelledby="home-case-title"
  >
    <BaseContainer class="home-case__container">
      <header class="home-case__intro">
        <BaseEyebrow class="home-case__eyebrow">Réalisations</BaseEyebrow>
        <h2 id="home-case-title" class="home-case__title">
          Des solutions concrètes, pas seulement de belles interfaces.
        </h2>
        <p class="home-case__lead">
          Nos réalisations sont conçues pour répondre à un besoin métier réel :
          rendre une entreprise visible, structurer une activité, faciliter le
          quotidien d'une équipe. La forme découle du fond, jamais l'inverse.
        </p>
      </header>

      <article
        class="home-case__placeholder"
        aria-labelledby="home-case-placeholder-title"
      >
        <BaseEyebrow class="home-case__placeholder-eyebrow">
          Études de cas en préparation
        </BaseEyebrow>
        <h3
          id="home-case-placeholder-title"
          class="home-case__placeholder-title"
        >
          Nos réalisations détaillées seront bientôt disponibles.
        </h3>
        <p class="home-case__placeholder-description">
          Nous préparons la publication d'études de cas détaillées, avec
          l'accord de nos clients et des indicateurs vérifiables. Nous
          préférons prendre le temps d'écrire des cas honnêtes plutôt que
          d'aligner des logos ou des chiffres invérifiables.
        </p>
      </article>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-case {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-16);
}

.home-case__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.home-case__intro {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.home-case__eyebrow {
  margin-bottom: var(--space-2);
}

.home-case__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 24ch;
}

.home-case__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.home-case__placeholder {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-8);
  background-color: var(--surface-primary);
  border: 1px dashed var(--border-default);
  border-radius: var(--radius-md);
  max-width: 60rem;
}

.home-case__placeholder-eyebrow {
  margin-bottom: var(--space-1);
}

.home-case__placeholder-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(1.125rem, 2vw, 1.375rem);
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
  max-width: 34ch;
}

.home-case__placeholder-description {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

@media (min-width: 768px) {
  .home-case {
    padding-block: var(--space-20);
  }

  .home-case__placeholder {
    padding: var(--space-10) var(--space-12);
  }
}

@media (min-width: 1024px) {
  .home-case {
    padding-block: var(--space-24);
  }
}
</style>
