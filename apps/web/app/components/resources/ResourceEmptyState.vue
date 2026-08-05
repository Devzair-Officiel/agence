<script setup lang="ts">
import BaseButton from "~/components/base/BaseButton.vue"

/**
 * État vide honnête pour `/ressources` lorsque l'API renvoie
 * `pagination.total === 0`.
 *
 * Rôle strict :
 *   - dire, sans détour, qu'aucune ressource n'est encore publiée ;
 *   - proposer une bascule de navigation utile (accueil, contact) ;
 *   - ne PAS inventer de contenu de placeholder, ne PAS afficher de
 *     « bientôt disponible » avec date fictive (règle 1 du référentiel).
 *
 * Ce composant ne s'affiche JAMAIS en cas d'erreur API : l'endpoint Nitro
 * renvoie alors 502/503 et Nuxt bascule sur la page d'erreur. Ce
 * composant représente uniquement l'état applicatif « 0 résultat ».
 */
</script>

<template>
  <section class="resource-empty-state" aria-labelledby="resource-empty-title">
    <div class="resource-empty-state__content">
      <p class="resource-empty-state__eyebrow">Aucune ressource pour le moment</p>
      <h2 id="resource-empty-title" class="resource-empty-state__title">
        Notre bibliothèque de ressources se construit.
      </h2>
      <p class="resource-empty-state__lead">
        Nous n'avons pas encore publié d'article ici. Les premières
        ressources seront ajoutées progressivement, sans placeholder.
        En attendant, découvrez notre approche ou parlez-nous directement
        de votre projet.
      </p>
      <div class="resource-empty-state__actions">
        <BaseButton to="/agence" variant="secondary">
          Découvrir l'agence
        </BaseButton>
        <BaseButton to="/contact" variant="primary">
          Parler de votre projet
        </BaseButton>
      </div>
    </div>
  </section>
</template>

<style scoped>
.resource-empty-state {
  display: flex;
  justify-content: center;
  padding-block: var(--space-10) var(--space-12);
}

.resource-empty-state__content {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 42rem;
  padding: var(--space-8);
  border: 1px dashed var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--background-secondary);
  text-align: left;
}

.resource-empty-state__eyebrow {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-accent);
  margin: 0;
}

.resource-empty-state__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 2.6vw, 2rem);
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
}

.resource-empty-state__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
}

.resource-empty-state__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
  margin-top: var(--space-3);
}
</style>
