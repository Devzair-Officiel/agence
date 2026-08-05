<script setup lang="ts">
import { computed } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import ResourceContent from "~/components/resources/ResourceContent.vue"
import ResourceHero from "~/components/resources/ResourceHero.vue"
import SiteBreadcrumb from "~/components/layout/SiteBreadcrumb.vue"
import { useArticleSeo } from "~/composables/useArticleSeo"
import { useResourceDetail } from "~/composables/useResources"

/**
 * Route dynamique `/ressources/{slug}` — page détail d'une ressource
 * éditoriale (Phase 8B2).
 *
 * Contrat d'état applicatif (correction obligatoire 6) :
 *   - slug invalide (format non conforme) → 400 côté endpoint Nitro,
 *     remonté ici en 503 par le composable (protection : ce cas ne peut
 *     survenir que si vue-router a laissé passer un segment non nettoyé) ;
 *   - slug inconnu (404 amont)            → 404 fatal ;
 *   - payload invalide (502 amont)        → 502 fatal ;
 *   - API indisponible (503 amont)        → 503 fatal.
 *
 * Le composable `useResourceDetail` prend en charge la traduction des
 * statuts amont en `createError({fatal: true})`. La page se contente
 * d'orchestrer.
 *
 * `ResourceContent` est le SEUL composant du projet autorisé à utiliser
 * `v-html`. Il consomme `article.contentHtml` produit et sécurisé côté
 * Symfony (`MarkdownSecurityPolicy`, ADR-010/011). Toute autre stratégie
 * de rendu HTML est un changement de frontière de confiance.
 */

const route = useRoute()

const slug = computed(() => {
  const raw = route.params.slug
  return Array.isArray(raw) ? raw[0] : raw
})

// Défense en profondeur : le format du slug est déjà validé côté Nitro
// (`SLUG_PATTERN` dans `/_editorial/detail/[slug]`). Ici on rejette
// simplement l'absence — vue-router garantit le reste.
if (!slug.value || typeof slug.value !== "string") {
  throw createError({
    statusCode: 404,
    statusMessage: "Ressource introuvable",
    fatal: true,
  })
}

const { article } = await useResourceDetail(slug.value)

const path = computed(() => `/ressources/${article.value.slug}`)

const breadcrumbItems = computed(() => [
  { label: "Accueil", to: "/" },
  { label: "Ressources", to: "/ressources" },
  { label: article.value.title },
])

useArticleSeo({ article: article.value, path: path.value })
</script>

<template>
  <div class="resource-detail">
    <SiteBreadcrumb :items="breadcrumbItems" />

    <ResourceHero :article="article" />

    <section class="resource-detail__section" aria-labelledby="resource-body-title">
      <BaseContainer class="resource-detail__container">
        <h2 id="resource-body-title" class="resource-detail__visually-hidden">
          Contenu de la ressource
        </h2>
        <ResourceContent :content-html="article.contentHtml" />
      </BaseContainer>
    </section>

    <EditorialCallout
      eyebrow="Aller plus loin"
      title="Un besoin qui rejoint ces sujets ?"
      description="Racontez-nous votre contexte — nous vous dirons franchement si nous pouvons vous aider, et sur quel périmètre."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Voir toutes les ressources', to: '/ressources' }"
    />
  </div>
</template>

<style scoped>
.resource-detail {
  display: flex;
  flex-direction: column;
}

.resource-detail__section {
  padding-block: var(--space-10) var(--space-14);
  background-color: var(--background-primary);
}

.resource-detail__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

/* Titre de section masqué visuellement — la structure sémantique reste
   correcte (H1 dans le hero, H2 pour la section de corps), mais on ne
   veut pas afficher un libellé redondant avec la page. */
.resource-detail__visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
