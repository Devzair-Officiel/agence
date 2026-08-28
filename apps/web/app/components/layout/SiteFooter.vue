<script setup lang="ts">
import { footerNavigation, legalNavigation } from "~/config/navigation"
import { site } from "~/config/site"

const currentYear = new Date().getFullYear()
</script>

<template>
  <footer class="site-footer">
    <!-- Corps : marque + colonnes de navigation -->
    <BaseContainer as="div" width="wide" class="site-footer__body">
      <div class="site-footer__grid">
        <!-- Colonne marque -->
        <div class="site-footer__brand">
          <NuxtLink to="/" class="site-footer__logo-link" aria-label="Devzair — accueil">
            <img
              class="site-footer__logo"
              alt=""
              src="/brand/logo_devzaire_agency.png"
              width="64"
              height="64"
              loading="lazy"
              decoding="async"
            >
          </NuxtLink>
          <p class="site-footer__eyebrow">Agence digitale</p>
          <p class="site-footer__tagline">
            Sites. Applications.<br >Image. Visibilité.
          </p>
          <p class="site-footer__description">
            Nous construisons des présences digitales complètes pour les
            entreprises qui veulent être visibles, crédibles et efficaces
            en ligne.
          </p>
        </div>

        <!-- Colonnes de navigation groupées dans un seul landmark -->
        <nav class="site-footer__nav" aria-label="Navigation du pied de page">
          <div
            v-for="group in footerNavigation"
            :key="group.title"
            class="site-footer__column"
          >
            <h3 class="site-footer__column-title">{{ group.title }}</h3>
            <ul class="site-footer__list" role="list">
              <li v-for="item in group.items" :key="item.to">
                <NuxtLink
                  :to="item.to"
                  :external="item.isRoute ? undefined : true"
                  class="site-footer__link"
                >
                  <span class="site-footer__link-label">{{ item.label }}</span>
                  <span class="site-footer__link-arrow" aria-hidden="true">→</span>
                </NuxtLink>
              </li>
            </ul>
          </div>
        </nav>
      </div>
    </BaseContainer>

    <!-- Séparateur graphique -->
    <BaseContainer as="div" width="wide" class="site-footer__sep-wrap" aria-hidden="true">
      <div class="site-footer__separator"/>
    </BaseContainer>

    <!-- Wordmark décoratif — signature visuelle Devzair -->
    <BaseContainer as="div" width="wide" class="site-footer__wordmark-wrap">
      <span class="site-footer__wordmark" aria-hidden="true">DEVZAIR</span>
    </BaseContainer>

    <!-- Barre légale -->
    <BaseContainer as="div" class="site-footer__legal">
      <span class="site-footer__copyright">© {{ currentYear }} {{ site.name }}</span>
      <ul
        v-if="legalNavigation.length > 0"
        class="site-footer__legal-list"
        role="list"
      >
        <li v-for="item in legalNavigation" :key="item.to">
          <NuxtLink
            :to="item.to"
            :external="item.isRoute ? undefined : true"
            class="site-footer__legal-link"
          >
            {{ item.label }}
          </NuxtLink>
        </li>
      </ul>
      <a href="#top" class="site-footer__back-to-top">
        Retour en haut
        <span class="site-footer__back-to-top-icon" aria-hidden="true">↑</span>
      </a>
    </BaseContainer>
  </footer>
</template>

<style scoped>
/* ------------------------------------------------------------------ *
 * Base du footer
 * ------------------------------------------------------------------ */

.site-footer {
  background-color: var(--background-inverse-deep);
  color: var(--text-inverse-muted);
  padding-top: var(--space-16);
  margin-top: auto;
}

/* ------------------------------------------------------------------ *
 * Grille principale : marque (large) + nav
 * ------------------------------------------------------------------ */

.site-footer__body {
  padding-bottom: var(--space-12);
}

.site-footer__grid {
  display: flex;
  flex-direction: column;
  gap: var(--space-12);
}

@media (min-width: 1024px) {
  .site-footer__grid {
    flex-direction: row;
    align-items: flex-start;
    gap: var(--space-16);
  }
}

/* ------------------------------------------------------------------ *
 * Colonne marque
 * ------------------------------------------------------------------ */

.site-footer__brand {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

@media (min-width: 1024px) {
  .site-footer__brand {
    flex: 0 0 30%;
    max-width: 30%;
  }
}

.site-footer__logo-link {
  display: inline-flex;
  width: fit-content;
  border-radius: var(--radius-md);
  margin-bottom: var(--space-1);
}

.site-footer__logo {
  display: block;
  width: 56px;
  height: 56px;
  object-fit: contain;
}

.site-footer__eyebrow {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  line-height: 1;
  /* AA sur --background-inverse-deep : cream-subtle ≈ 5.2:1 */
  color: var(--color-cream-subtle);
}

.site-footer__tagline {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(1.0625rem, 1.8vw, 1.25rem);
  letter-spacing: -0.02em;
  line-height: 1.25;
  color: var(--text-inverse);
}

.site-footer__description {
  max-width: 38ch;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.65;
  color: var(--color-cream-subtle);
}

/* ------------------------------------------------------------------ *
 * Colonnes de navigation
 * ------------------------------------------------------------------ */

.site-footer__nav {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

@media (min-width: 600px) {
  .site-footer__nav {
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-6);
  }
}

@media (min-width: 1024px) {
  .site-footer__nav {
    flex: 1;
  }
}

/* Titre de colonne — override des styles globaux h3 */
.site-footer__column-title {
  margin-bottom: var(--space-4);
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  line-height: 1;
  color: var(--color-cream-subtle);
}

.site-footer__list {
  display: flex;
  flex-direction: column;
  margin: 0;
  padding: 0;
  list-style: none;
}

/* ------------------------------------------------------------------ *
 * Liens avec micro-interaction
 * ------------------------------------------------------------------ */

.site-footer__link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-2);
  padding-block: var(--space-2);
  /* WCAG 2.2 SC 2.5.8 : 24px min. On vise 40px pour un confort clavier. */
  min-height: 2.5rem;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  color: var(--color-cream-muted);
  transition: color var(--duration-fast) var(--ease-out);
}

.site-footer__link:hover,
.site-footer__link:focus-visible {
  color: var(--text-inverse);
}

.site-footer__link-label {
  flex: 1;
}

.site-footer__link-arrow {
  flex: 0 0 auto;
  font-style: normal;
  opacity: 0.3;
  transition:
    transform 150ms var(--ease-out),
    opacity 150ms var(--ease-out);
}

.site-footer__link:hover .site-footer__link-arrow,
.site-footer__link:focus-visible .site-footer__link-arrow {
  transform: translateX(4px);
  opacity: 1;
}

/* ------------------------------------------------------------------ *
 * Séparateur graphique
 * ------------------------------------------------------------------ */

.site-footer__sep-wrap {
  padding-bottom: 0;
}

.site-footer__separator {
  position: relative;
  height: 1px;
  background-color: var(--border-inverse);
}

.site-footer__separator::after {
  content: "";
  position: absolute;
  left: 50%;
  top: 50%;
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background-color: var(--color-cream-subtle);
  transform: translate(-50%, -50%);
  box-shadow: 0 0 0 4px var(--background-inverse-deep);
}

/* ------------------------------------------------------------------ *
 * Wordmark décoratif DEVZAIR
 * ------------------------------------------------------------------ */

.site-footer__wordmark-wrap {
  overflow: hidden;
  padding-block: var(--space-3) var(--space-2);
}

.site-footer__wordmark {
  display: block;
  font-family: var(--font-family-heading);
  font-weight: 700;
  font-size: clamp(3.5rem, 17vw, 13rem);
  letter-spacing: 0.08em;
  line-height: 1;
  color: var(--color-cream);
  opacity: 0.05;
  white-space: nowrap;
  pointer-events: none;
  user-select: none;
}

/* ------------------------------------------------------------------ *
 * Barre légale
 * ------------------------------------------------------------------ */

.site-footer__legal {
  border-top: 1px solid var(--border-inverse);
  padding-block: var(--space-5);
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4);
  align-items: center;
  justify-content: space-between;
}

.site-footer__copyright {
  font-family: var(--font-family-body);
  font-size: 0.75rem;
  color: var(--color-cream-subtle);
}

.site-footer__legal-list {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-6);
  margin: 0;
  padding: 0;
  list-style: none;
}

.site-footer__legal-link {
  font-family: var(--font-family-body);
  font-size: 0.75rem;
  color: var(--color-cream-subtle);
  transition: color var(--duration-fast) var(--ease-out);
}

.site-footer__legal-link:hover {
  color: var(--text-inverse);
}

.site-footer__back-to-top {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
  font-family: var(--font-family-body);
  font-size: 0.75rem;
  color: var(--color-cream-subtle);
  min-height: 2rem;
  transition: color var(--duration-fast) var(--ease-out);
}

.site-footer__back-to-top:hover,
.site-footer__back-to-top:focus-visible {
  color: var(--text-inverse);
}

.site-footer__back-to-top-icon {
  display: inline-block;
  transition: transform 150ms var(--ease-out);
}

.site-footer__back-to-top:hover .site-footer__back-to-top-icon,
.site-footer__back-to-top:focus-visible .site-footer__back-to-top-icon {
  transform: translateY(-3px);
}

/* ------------------------------------------------------------------ *
 * prefers-reduced-motion — défense en profondeur
 * (la règle globale de animations.css tue déjà toutes les transitions)
 * ------------------------------------------------------------------ */

@media (prefers-reduced-motion: reduce) {
  .site-footer__link-arrow,
  .site-footer__back-to-top-icon {
    transition: none;
  }
}
</style>
