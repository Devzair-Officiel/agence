<script setup lang="ts">
import { footerNavigation, legalNavigation } from "~/config/navigation"
import { site } from "~/config/site"

const currentYear = new Date().getFullYear()
</script>

<template>
  <footer class="site-footer">
    <BaseContainer as="div" width="wide" class="site-footer__body">
      <div class="site-footer__grid">

        <!-- Zone 1 : Marque -->
        <div class="site-footer__brand">
          <!-- Logo + nom sur la même ligne -->
          <NuxtLink
            to="/"
            class="site-footer__brand-link"
            :aria-label="`${site.name} — Accueil`"
          >
            <img
              class="site-footer__logo"
              alt=""
              src="/brand/logo_devzaire_agency.png"
              width="40"
              height="40"
              loading="lazy"
              decoding="async"
            >
            <span class="site-footer__brand-name" aria-hidden="true">{{ site.name }}</span>
          </NuxtLink>

          <p class="site-footer__eyebrow">Agence digitale</p>
          <p class="site-footer__tagline">
            Sites. Applications.<br>Image. Visibilité.
          </p>
          <p class="site-footer__description">
            Nous concevons des solutions digitales cohérentes pour aider
            les entreprises à être visibles, crédibles et efficaces en ligne.
          </p>
        </div>

        <!-- Zones 2 + 3 : Expertises et Découvrir -->
        <nav
          class="site-footer__nav"
          aria-label="Navigation du pied de page"
        >
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
                >{{ item.label }}</NuxtLink>
              </li>
            </ul>
            <!-- CTA bordé sous la liste (colonne Découvrir uniquement) -->
            <NuxtLink
              v-if="group.cta"
              :to="group.cta.to"
              :external="group.cta.isRoute ? undefined : true"
              class="site-footer__cta"
            >
              {{ group.cta.label }}
              <span class="site-footer__cta-icon" aria-hidden="true">→</span>
            </NuxtLink>
          </div>
        </nav>

      </div>
    </BaseContainer>

    <!-- Barre légale -->
    <BaseContainer as="div" width="wide" class="site-footer__legal">
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
          >{{ item.label }}</NuxtLink>
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
 * Base
 * ------------------------------------------------------------------ */

.site-footer {
  background-color: var(--background-inverse-deep);
  color: var(--text-inverse-muted);
  padding-top: var(--space-14);
  margin-top: auto;
}

/* ------------------------------------------------------------------ *
 * Grille principale — mobile first
 * ------------------------------------------------------------------ */

.site-footer__body {
  padding-bottom: var(--space-10);
}

.site-footer__grid {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

/* ------------------------------------------------------------------ *
 * Zone 1 — Marque
 * ------------------------------------------------------------------ */

.site-footer__brand {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

/* Logo + nom sur la même ligne */
.site-footer__brand-link {
  display: inline-flex;
  align-items: center;
  gap: var(--space-3);
  width: fit-content;
  border-radius: var(--radius-md);
  margin-bottom: var(--space-2);
}

.site-footer__logo {
  display: block;
  width: 40px;
  height: 40px;
  object-fit: contain;
  flex-shrink: 0;
}

.site-footer__brand-name {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.0625rem;
  letter-spacing: -0.01em;
  color: var(--text-inverse);
}

.site-footer__eyebrow {
  /* Space Mono tout en capitales — AA sur navy-deep : cream-subtle ≈ 5.2:1 */
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  line-height: 1;
  color: var(--color-cream-subtle);
}

.site-footer__tagline {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(1.5rem, 2.5vw, 2rem);
  letter-spacing: -0.025em;
  line-height: 1.2;
  color: var(--text-inverse);
}

.site-footer__description {
  max-width: 40ch;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.65;
  color: var(--color-cream-subtle);
}

/* ------------------------------------------------------------------ *
 * Zones 2 + 3 — Navigation
 * ------------------------------------------------------------------ */

.site-footer__nav {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
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
  /* AA sur navy-deep : cream-subtle ≈ 5.2:1 */
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
 * Liens de navigation — underline pétrole au survol
 * ------------------------------------------------------------------ */

.site-footer__link {
  display: block;
  padding-block: var(--space-2);
  /* WCAG 2.2 SC 2.5.8 : cible ≥ 24 px. */
  min-height: 2.5rem;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.4;
  color: var(--color-cream-muted);
  text-decoration-line: underline;
  text-decoration-thickness: 1px;
  text-underline-offset: 4px;
  text-decoration-color: transparent;
  transition:
    color var(--duration-fast) var(--ease-out),
    text-decoration-color var(--duration-fast) var(--ease-out);
}

.site-footer__link:hover,
.site-footer__link:focus-visible {
  color: var(--text-inverse);
  text-decoration-color: var(--color-petrol);
}

/* ------------------------------------------------------------------ *
 * CTA bordé — "Parler de votre projet"
 * ------------------------------------------------------------------ */

.site-footer__cta {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  margin-top: var(--space-5);
  padding: var(--space-2) var(--space-4);
  border: 1px solid color-mix(
    in srgb,
    var(--color-cream-muted) 30%,
    transparent
  );
  border-radius: var(--radius-sm);
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  color: var(--color-cream-muted);
  /* min-height cible WCAG 2.2 SC 2.5.8 */
  min-height: 2.5rem;
  transition:
    color var(--duration-fast) var(--ease-out),
    border-color var(--duration-fast) var(--ease-out);
}

.site-footer__cta:hover,
.site-footer__cta:focus-visible {
  color: var(--text-inverse);
  border-color: var(--color-cream-muted);
}

.site-footer__cta-icon {
  flex-shrink: 0;
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
  transition: transform var(--duration-fast) var(--ease-out);
}

.site-footer__back-to-top:hover .site-footer__back-to-top-icon,
.site-footer__back-to-top:focus-visible .site-footer__back-to-top-icon {
  transform: translateY(-3px);
}

/* ------------------------------------------------------------------ *
 * Responsive — placé après les règles de base pour préserver la cascade
 * ------------------------------------------------------------------ */

/* Tablette : marque pleine largeur, navigation en deux colonnes. */
@media (min-width: 768px) {
  .site-footer__nav {
    grid-template-columns: minmax(0, 9fr) minmax(0, 7fr);
  }
}

/* Desktop : une masse marque large, puis deux colonnes de navigation. */
@media (min-width: 1024px) {
  .site-footer__grid {
    flex-direction: row;
    align-items: flex-start;
    gap: var(--space-16);
  }

  .site-footer__brand {
    flex: 0 0 40%;
    max-width: 40%;
  }

  .site-footer__nav {
    flex: 1;
    grid-template-columns: minmax(0, 9fr) minmax(0, 7fr);
  }
}

/* ------------------------------------------------------------------ *
 * prefers-reduced-motion — défense en profondeur
 * ------------------------------------------------------------------ */

@media (prefers-reduced-motion: reduce) {
  .site-footer__link,
  .site-footer__cta,
  .site-footer__legal-link,
  .site-footer__back-to-top,
  .site-footer__back-to-top-icon {
    transition: none;
  }
}
</style>
