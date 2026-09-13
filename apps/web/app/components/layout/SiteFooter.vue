<script setup lang="ts">
import { footerNavigation, legalNavigation } from "~/config/navigation"
import { site } from "~/config/site"

const currentYear = new Date().getFullYear()
</script>

<template>
  <footer class="site-footer">
    <BaseContainer as="div" width="full" class="site-footer__body">
      <div class="site-footer__grid">

        <!-- Zone 1 : Marque -->
        <div class="site-footer__brand">
          <NuxtLink
            to="/"
            class="site-footer__logo-link"
            aria-label="Devzair — accueil"
          >
            <img
              class="site-footer__logo"
              alt=""
              src="/brand/logo_devzaire_agency.png"
              width="128"
              height="128"
              loading="lazy"
              decoding="async"
            >
          </NuxtLink>
          <p class="site-footer__tagline">Sites – Applications – Image – Visibilité</p>
          <p class="site-footer__description">
            Nous concevons des solutions digitales cohérentes pour aider
            les entreprises à être visibles, crédibles et efficaces en ligne.
          </p>
        </div>

        <!-- Zones 2-4 : Expertises, Services et Découvrir -->
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
            <!-- CTA bordé sous la liste (Services et Découvrir) -->
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
    <BaseContainer as="div" width="full" class="site-footer__legal">
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
/* ── Conteneur ─────────────────────────────────────────────────────── */

.site-footer {
  background-color: var(--background-inverse-deep);
  color: var(--text-inverse-muted);
  padding-top: var(--space-10);
  margin-top: auto;
}

/*
 * Surcharge des gouttières du BaseContainer uniquement dans le contexte
 * du footer — CSS custom properties héritées par les descendants DOM,
 * sans toucher BaseContainer lui-même.
 */
@media (min-width: 1024px) {
  .site-footer {
    --container-gutter-desktop: var(--space-4); /* 1 rem au lieu de 3 rem */
  }
}

@media (min-width: 1440px) {
  .site-footer {
    --container-gutter-wide: var(--space-4); /* 1 rem au lieu de 4 rem */
  }
}

.site-footer__body {
  padding-bottom: var(--space-8);
}

/* Grille : colonne sur mobile, ligne sur desktop */
.site-footer__grid {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

@media (min-width: 1024px) {
  .site-footer__grid {
    flex-direction: row;
    align-items: start;
    gap: var(--space-12);
  }

  .site-footer__brand {
    flex: 0 0 30%;
    align-items: flex-start;
    text-align: left;
  }

  .site-footer__nav {
    flex: 1;
    gap: var(--space-10);
  }
}

/* ── Zone marque ────────────────────────────────────────────────────── */

.site-footer__brand {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: var(--space-4);
}

.site-footer__logo-link {
  display: inline-flex;
  border-radius: var(--radius-md);
  margin-bottom: var(--space-2);
}

.site-footer__logo {
  display: block;
  width: 128px;
  height: 128px;
  object-fit: contain;
}

.site-footer__tagline {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.25rem;
  letter-spacing: -0.01em;
  line-height: 1.25;
  color: var(--text-inverse);
}

.site-footer__description {
  max-width: 36ch;
  font-size: 1.0625rem;
  line-height: 1.65;
  color: var(--color-cream-subtle);
}

/* ── Navigation colonnes ─────────────────────────────────────────────── */

.site-footer__nav {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: var(--space-10);
  align-items: start;
}

@media (min-width: 640px) {
  .site-footer__nav {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-8);
  }
}

@media (min-width: 768px) {
  .site-footer__nav {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

/* Centré sur mobile colonne unique, gauche dès 640 px (colonnes côte à côte) */
.site-footer__column {
  text-align: center;
}

@media (min-width: 640px) {
  .site-footer__column {
    text-align: left;
  }

  .site-footer__column-title {
    margin-bottom: var(--space-5);
  }
}

.site-footer__column-title {
  margin-bottom: var(--space-2);
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.9375rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  line-height: 1;
  color: var(--text-inverse);
}

.site-footer__column-title::after {
  content: '';
  display: block;
  width: 24px;
  height: 2px;
  margin-top: var(--space-3);
  background-color: var(--color-petrol);
}

@media (max-width: 639px) {
  .site-footer__column-title::after {
    margin-inline: auto;
  }
}

.site-footer__list {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 0;
  padding: 0;
  list-style: none;
}

@media (min-width: 640px) {
  .site-footer__list {
    align-items: flex-start;
  }
}

.site-footer__link {
  display: inline-flex;
  align-items: center;
  padding-block: var(--space-2);
  min-height: 2.75rem;
  font-size: 1rem;
  line-height: 1.4;
  color: var(--color-cream-muted);
  text-decoration-line: underline;
  text-decoration-thickness: 1px;
  text-underline-offset: 4px;
  text-decoration-color: transparent;
  transition: color 150ms var(--ease-out), text-decoration-color 150ms var(--ease-out);
}

.site-footer__link:hover,
.site-footer__link:focus-visible {
  color: var(--text-inverse);
  text-decoration-color: var(--color-petrol);
}

.site-footer__cta {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  margin-top: var(--space-4);
  padding: var(--space-2) var(--space-4);
  min-height: 2.5rem;
  border: 1px solid rgba(196, 203, 211, 0.3);
  border-radius: var(--radius-sm);
  font-size: 0.9375rem;
  color: var(--color-cream-muted);
  transition: color 150ms var(--ease-out), border-color 150ms var(--ease-out);
}

.site-footer__cta:hover,
.site-footer__cta:focus-visible {
  color: var(--text-inverse);
  border-color: var(--color-cream-muted);
}

/* ── Barre légale ───────────────────────────────────────────────────── */

.site-footer__legal {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-4);
  padding-block: var(--space-5);
  border-top: 1px solid var(--border-inverse);
}

.site-footer__copyright,
.site-footer__legal-link,
.site-footer__back-to-top {
  font-size: 0.8125rem;
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

.site-footer__legal-link { transition: color 150ms var(--ease-out); }
.site-footer__legal-link:hover { color: var(--text-inverse); }

.site-footer__back-to-top {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
  min-height: 2rem;
  transition: color 150ms var(--ease-out);
}

.site-footer__back-to-top:hover,
.site-footer__back-to-top:focus-visible { color: var(--text-inverse); }

.site-footer__back-to-top-icon {
  display: inline-block;
  transition: transform 150ms var(--ease-out);
}

.site-footer__back-to-top:hover .site-footer__back-to-top-icon,
.site-footer__back-to-top:focus-visible .site-footer__back-to-top-icon {
  transform: translateY(-3px);
}

@media (prefers-reduced-motion: reduce) {
  .site-footer__link,
  .site-footer__cta,
  .site-footer__legal-link,
  .site-footer__back-to-top,
  .site-footer__back-to-top-icon { transition: none; }
}
</style>
