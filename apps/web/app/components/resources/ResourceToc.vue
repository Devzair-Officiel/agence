<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue"
import type { TocEntry } from "~/utils/toc"

interface Props {
  entries: readonly TocEntry[]
}

const props = defineProps<Props>()

const activeId = ref<string | null>(null)
let observer: IntersectionObserver | null = null

onMounted(() => {
  if (!("IntersectionObserver" in window) || props.entries.length === 0) return

  observer = new IntersectionObserver(
    (entries) => {
      // Keep track of the topmost visible heading
      const visible = entries
        .filter((e) => e.isIntersecting)
        .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)
      const first = visible[0]
      if (first) {
        activeId.value = first.target.id
      }
    },
    {
      // Zone active : juste en dessous du header jusqu'à la moitié haute de la fenêtre
      rootMargin: "-100px 0px -55% 0px",
      threshold: 0,
    },
  )

  for (const entry of props.entries) {
    const el = document.getElementById(entry.id)
    if (el) observer.observe(el)
  }
})

onUnmounted(() => {
  observer?.disconnect()
})
</script>

<template>
  <nav
    v-if="entries.length > 0"
    class="resource-toc"
    aria-label="Sommaire de l'article"
  >
    <p class="resource-toc__heading" aria-hidden="true">Dans cet article</p>
    <ol class="resource-toc__list" role="list">
      <li
        v-for="entry in entries"
        :key="entry.id"
        class="resource-toc__item"
        :data-level="entry.level"
      >
        <a
          :href="`#${entry.id}`"
          class="resource-toc__link"
          :aria-current="activeId === entry.id ? 'true' : undefined"
        >{{ entry.text }}</a>
      </li>
    </ol>
  </nav>
</template>

<style scoped>
.resource-toc__heading {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.09em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0 0 var(--space-4);
}

.resource-toc__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
}

.resource-toc__link {
  display: block;
  padding-block: 0.3125rem; /* 5px — confortable sans être trop aéré */
  padding-left: var(--space-3);
  border-left: 2px solid transparent;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.4;
  color: var(--text-secondary);
  text-decoration: none;
  transition: color 150ms var(--ease-out), border-color 150ms var(--ease-out);
}

.resource-toc__item[data-level="3"] .resource-toc__link {
  padding-left: var(--space-5);
  font-size: 0.875rem;
  color: var(--text-muted);
}

.resource-toc__link:hover,
.resource-toc__link:focus-visible {
  color: var(--text-primary);
  border-left-color: var(--color-petrol);
  outline-offset: 2px;
}

.resource-toc__link[aria-current="true"] {
  color: var(--text-primary);
  border-left-color: var(--color-petrol);
  font-weight: 500;
}

@media (prefers-reduced-motion: reduce) {
  .resource-toc__link {
    transition: none;
  }
}
</style>
