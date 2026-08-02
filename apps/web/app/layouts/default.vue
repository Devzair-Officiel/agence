<script setup lang="ts">
// Layout par défaut : skip-link, header sticky, main, footer.
// La navigation mobile est teleportée sur <body> et n'apparaît que sur mobile.
//
// useSiteSchema() injecte le graphe Schema.org global (Organization + WebSite)
// dans le HTML SSR. À n'appeler qu'ici — appeler depuis une page produirait
// des JSON-LD dupliqués.
import SiteHeader from "~/components/layout/SiteHeader.vue"
import MobileNavigation from "~/components/layout/MobileNavigation.vue"
import SiteFooter from "~/components/layout/SiteFooter.vue"

useSiteSchema()
</script>

<template>
  <div class="app-shell">
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>
    <SiteHeader />
    <MobileNavigation />
    <main id="main-content" class="app-shell__main">
      <slot />
    </main>
    <SiteFooter />
  </div>
</template>

<style scoped>
.app-shell {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  min-height: 100dvh;
}

.app-shell__main {
  flex: 1 1 auto;
}
</style>
