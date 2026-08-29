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
              width="140"
              height="140"
              loading="lazy"
              decoding="async"
            >
          </NuxtLink>
          <p class="site-footer__tagline">Sites - Applications - Image - Visibilité</p>
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
  padding-top: var(--space-14);
  margin-top: auto;
}

.site-footer__body {
  padding-bottom: var(--space-10);
}

/* Grille : colonne sur mobile, ligne sur desktop */
.site-footer__grid {
  display: flex;
  flex-direction: column;
  gap: var(--space-12);
}

@media (min-width: 1024px) {
  .site-footer__grid {
    flex-direction: row;
    align-items: center;
    gap: var(--space-16);
  }

  .site-footer__brand { flex: 0 0 40%; }
  .site-footer__nav   { flex: 1; }
}

/* ── Zone marque — colonne centrée ─────────────────────────────────── */

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
  width: 140px;
  height: 140px;
  object-fit: contain;
}

.site-footer__tagline {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 0.9375rem;
  letter-spacing: -0.01em;
  line-height: 1.3;
  color: var(--text-inverse);
}

.site-footer__description {
  max-width: 40ch;
  font-size: 0.875rem;
  line-height: 1.65;
  color: var(--color-cream-subtle);
}

/* ── Navigation 2 colonnes ─────────────────────────────────────────── */

.site-footer__nav {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--space-8);
  align-items: start;
}

.site-footer__column-title {
  margin-bottom: var(--space-4);
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 1rem;
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

.site-footer__link {
  display: inline-flex;
  align-items: center;
  padding-block: var(--space-2);
  min-height: 2.5rem;
  font-size: 0.875rem;
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
  margin-top: var(--space-5);
  padding: var(--space-2) var(--space-4);
  min-height: 2.5rem;
  border: 1px solid rgba(196, 203, 211, 0.3);
  border-radius: var(--radius-sm);
  font-size: 0.8125rem;
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
