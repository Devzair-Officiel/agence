<script setup lang="ts">
/**
 * Liste des livrables d'un pôle d'expertise, rendue dans une section
 * `EditorialSection` par la page parente.
 *
 * Contrat minimal : un tableau de chaînes non vides. Rendu en `<ul>`
 * accessible avec `role="list"` (nécessaire quand `list-style: none`
 * supprime la sémantique de liste sur Safari).
 *
 * Aucun état, aucun JS.
 */

interface Props {
  deliverables: readonly string[]
}

defineProps<Props>()
</script>

<template>
  <ul class="expertise-deliverables" role="list">
    <li
      v-for="deliverable in deliverables"
      :key="deliverable"
      class="expertise-deliverables__item"
    >
      <span class="expertise-deliverables__marker" aria-hidden="true" />
      <span class="expertise-deliverables__label">{{ deliverable }}</span>
    </li>
  </ul>
</template>

<style scoped>
.expertise-deliverables {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-3);
}

.expertise-deliverables__item {
  display: grid;
  grid-template-columns: auto 1fr;
  align-items: baseline;
  gap: var(--space-3);
  padding: var(--space-4) var(--space-5);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md, 0.5rem);
  background-color: var(--background-primary);
  color: var(--text-primary);
}

.expertise-deliverables__marker {
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 50%;
  background-color: var(--color-devzair-blue);
  transform: translateY(-0.15em);
}

.expertise-deliverables__label {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.5;
}

@media (min-width: 720px) {
  .expertise-deliverables {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
