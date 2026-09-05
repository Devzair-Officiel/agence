<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import type { ServicePageDefinition } from "~/config/service-pages"

/**
 * Page `/services/seo-referencement-naturel`.
 *
 * Rôle : expliquer ce que recouvre un travail SEO honnête — technique,
 * éditorial, architecture — sans promettre de positions ni inventer de résultats
 * sur les projets Devzair.
 *
 * SEO, JSON-LD et breadcrumb sont posés dans `pages/services/[slug].vue`.
 */

interface Props {
  page: ServicePageDefinition
}

defineProps<Props>()

const faqItems = [
  {
    question: "Combien de temps avant de voir des résultats ?",
    answer:
      "Le SEO est un travail qui s'étend sur plusieurs mois. Les premières améliorations techniques sont visibles rapidement (indexation, Core Web Vitals), mais l'impact sur les positions dans les résultats de recherche prend généralement entre 3 et 9 mois selon la concurrence du secteur et l'état de départ du site. Nous n'avançons pas de délais garantis parce que les algorithmes ne se pilotent pas.",
  },
  {
    question: "Pouvez-vous garantir la première position sur Google ?",
    answer:
      "Non. Personne ne peut garantir une position précise sur Google — ni nous, ni personne d'autre. Ce qui est possible : identifier les pages et les requêtes où le site a une marge réelle d'amélioration, corriger les obstacles techniques, améliorer la structure et le contenu, et suivre l'évolution dans le temps. Les positions résultent du travail, pas d'une promesse.",
  },
  {
    question: "La refonte de mon site va-t-elle faire baisser mon SEO ?",
    answer:
      "C'est un risque réel quand une refonte est conduite sans audit SEO préalable. Les redirections manquantes, les changements de structure d'URL et la suppression de pages existantes peuvent faire perdre une visibilité acquise en plusieurs mois. Nous intégrons l'audit SEO au cadrage d'une refonte pour anticiper ces points.",
  },
  {
    question: "La rédaction de contenu est-elle incluse ?",
    answer:
      "La stratégie éditoriale et les recommandations de contenu font partie de la mission SEO. La rédaction effective des textes dépend du périmètre convenu — elle peut être incluse ou confiée à votre équipe selon vos ressources et vos délais.",
  },
  {
    question: "Qu'est-ce que le SEO technique ?",
    answer:
      "Le SEO technique recouvre tout ce qui facilite l'exploration et l'indexation par les moteurs : vitesse de chargement, balisage correct des pages (title, meta, H1, schema.org), architecture d'URL propre, fichiers robots.txt et sitemap à jour, gestion des doublons et des redirections. C'est la base sans laquelle le travail éditorial porte moins.",
  },
  {
    question: "Quel est le lien entre SEO et visibilité locale ?",
    answer:
      "Le SEO général et la visibilité locale partagent des bases communes — technique, contenu, structure — mais la visibilité locale ajoute une dimension géographique : optimisation de la fiche Google Business Profile, cohérence des informations (nom, adresse, téléphone) sur les annuaires, et contenu orienté vers des requêtes de proximité. Ce sont deux chantiers qui se complètent.",
  },
]
</script>

<template>
  <div class="seo-page">
    <!-- A. Hero -->
    <EditorialHero
      eyebrow="SEO et référencement naturel"
      title="Un travail de fond sur la structure et le contenu, pas une promesse de position."
      lead="Le référencement naturel est le résultat d'un travail technique et éditorial patient. Corriger ce qui bloque l'indexation, organiser le contenu autour de ce que vos clients cherchent, améliorer la structure de vos pages — c'est ce qui construit une visibilité durable."
    >
      <template #actions>
        <NuxtLink
          to="/contact"
          class="seo-page__hero-cta seo-page__hero-cta--primary"
        >
          Parler de votre projet
        </NuxtLink>
        <NuxtLink
          to="/estimer-mon-projet"
          class="seo-page__hero-cta seo-page__hero-cta--secondary"
        >
          Estimer mon projet
        </NuxtLink>
      </template>
    </EditorialHero>

    <!-- B. Ce que recouvre un travail SEO -->
    <EditorialSection
      tone="subtle"
      eyebrow="Périmètre"
      title="Ce que recouvre un travail SEO."
      section-id="perimetre"
    >
      <div class="seo-page__pillars">
        <div class="seo-page__pillar">
          <div class="seo-page__pillar-icon" aria-hidden="true">
            <span class="seo-page__pillar-bar seo-page__pillar-bar--1" />
            <span class="seo-page__pillar-bar seo-page__pillar-bar--2" />
            <span class="seo-page__pillar-bar seo-page__pillar-bar--3" />
          </div>
          <h3 class="seo-page__pillar-title">Technique</h3>
          <p class="seo-page__pillar-text">
            Vitesse de chargement, balisage (title, meta, schema.org), architecture
            d'URL, gestion des doublons, sitemap et robots. Ce qui permet aux
            moteurs d'explorer et d'indexer correctement chaque page.
          </p>
        </div>
        <div class="seo-page__pillar">
          <div class="seo-page__pillar-icon" aria-hidden="true">
            <span class="seo-page__pillar-line seo-page__pillar-line--1" />
            <span class="seo-page__pillar-line seo-page__pillar-line--2" />
            <span class="seo-page__pillar-line seo-page__pillar-line--3" />
          </div>
          <h3 class="seo-page__pillar-title">Éditorial</h3>
          <p class="seo-page__pillar-text">
            Recherche de mots-clés pertinents pour votre secteur, structure des
            pages, hiérarchie des titres, contenu factuel organisé autour de ce
            que vos clients cherchent réellement.
          </p>
        </div>
        <div class="seo-page__pillar">
          <div class="seo-page__pillar-icon" aria-hidden="true">
            <span class="seo-page__pillar-node seo-page__pillar-node--a" />
            <span class="seo-page__pillar-node seo-page__pillar-node--b" />
            <span class="seo-page__pillar-edge" />
          </div>
          <h3 class="seo-page__pillar-title">Maillage interne</h3>
          <p class="seo-page__pillar-text">
            Organisation des liens entre pages pour distribuer la valeur SEO,
            aider les moteurs à comprendre la structure du site et guider les
            visiteurs vers les pages les plus importantes.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- C. Ce qu'on fait, ce qu'on ne fait pas -->
    <EditorialSection
      eyebrow="Honnêteté"
      title="Ce qu'on fait, et ce qu'on ne fait pas."
      section-id="honnêteté"
    >
      <div class="seo-page__limits">
        <div class="seo-page__limits-col seo-page__limits-col--do">
          <h3 class="seo-page__limits-heading">Ce que nous faisons</h3>
          <ul class="seo-page__limits-list">
            <li>Audit technique et identification des blocages d'indexation</li>
            <li>Recommandations de structure de pages et de titres</li>
            <li>Recherche de mots-clés adaptée à votre activité réelle</li>
            <li>Optimisation des balises et du balisage schema.org</li>
            <li>Amélioration de la vitesse et des Core Web Vitals</li>
            <li>Suivi de l'évolution de la visibilité dans le temps</li>
          </ul>
        </div>
        <div class="seo-page__limits-col seo-page__limits-col--dont">
          <h3 class="seo-page__limits-heading">Ce que nous ne faisons pas</h3>
          <ul class="seo-page__limits-list">
            <li>Garantir une position précise ou un délai de résultat</li>
            <li>Techniques de manipulation algorithmique (black hat SEO)</li>
            <li>Achat de liens ou schémas de netlinking artificiels</li>
            <li>Rédaction de contenu sans brief de votre part</li>
            <li>Promettre un trafic ou un chiffre d'affaires supplémentaire</li>
          </ul>
        </div>
      </div>
    </EditorialSection>

    <!-- D. Audit initial -->
    <EditorialSection
      tone="subtle"
      eyebrow="Point de départ"
      title="L'audit : comprendre l'état réel avant d'agir."
      section-id="audit"
    >
      <div class="seo-page__audit">
        <div class="seo-page__audit-prose">
          <p class="seo-page__paragraph seo-page__paragraph--lead">
            Un travail SEO commence par un état des lieux. Pas pour produire
            un rapport de 80 pages, mais pour identifier les points qui bloquent
            ou limitent la visibilité actuelle du site.
          </p>
          <p class="seo-page__paragraph">
            Cela inclut l'analyse technique (erreurs d'exploration, pages non
            indexées, temps de réponse), l'analyse du contenu existant (pages
            trop courtes, doublons, titres inadaptés) et une première lecture
            du positionnement actuel sur les requêtes clés de votre secteur.
          </p>
          <p class="seo-page__paragraph">
            C'est à partir de cet audit que nous définissons quelles actions
            auront le plus d'impact, dans quel ordre les traiter et quel
            volume de travail est nécessaire.
          </p>
        </div>
        <div class="seo-page__audit-visual" aria-hidden="true">
          <div class="seo-page__audit-block seo-page__audit-block--1" />
          <div class="seo-page__audit-block seo-page__audit-block--2" />
          <div class="seo-page__audit-block seo-page__audit-block--3" />
          <div class="seo-page__audit-block seo-page__audit-block--4" />
        </div>
      </div>
    </EditorialSection>

    <!-- E. Durée et suivi -->
    <EditorialSection
      eyebrow="Temporalité"
      title="Un travail qui s'étale dans le temps."
      section-id="duree"
    >
      <div class="seo-page__timeline">
        <div class="seo-page__timeline-item">
          <span class="seo-page__timeline-label">Mois 1–2</span>
          <div class="seo-page__timeline-content">
            <h3 class="seo-page__timeline-title">Audit et corrections techniques</h3>
            <p class="seo-page__timeline-text">
              Identification et correction des obstacles d'indexation. Mise
              en place des balises prioritaires, correction des redirections,
              amélioration des performances de chargement.
            </p>
          </div>
        </div>
        <div class="seo-page__timeline-item">
          <span class="seo-page__timeline-label">Mois 2–4</span>
          <div class="seo-page__timeline-content">
            <h3 class="seo-page__timeline-title">Optimisation éditoriale</h3>
            <p class="seo-page__timeline-text">
              Restructuration des pages prioritaires, amélioration du contenu
              existant, création de nouvelles pages sur des requêtes identifiées,
              optimisation du maillage interne.
            </p>
          </div>
        </div>
        <div class="seo-page__timeline-item">
          <span class="seo-page__timeline-label">Mois 4 et au-delà</span>
          <div class="seo-page__timeline-content">
            <h3 class="seo-page__timeline-title">Suivi et ajustements</h3>
            <p class="seo-page__timeline-text">
              Mesure de l'évolution des positions et du trafic organique,
              identification des nouvelles opportunités, ajustements en
              fonction de ce qui fonctionne réellement.
            </p>
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- F. Relation SEO / création de site -->
    <EditorialSection
      tone="subtle"
      eyebrow="Lien avec le reste"
      title="SEO et création de site : deux chantiers liés."
      section-id="relation"
    >
      <div class="seo-page__relation">
        <div class="seo-page__relation-prose">
          <p class="seo-page__paragraph">
            Le meilleur moment pour poser les bases SEO d'un site, c'est lors
            de sa création — pas après. L'architecture d'URL, la hiérarchie des
            pages, la structure des titres et le balisage schema.org sont
            beaucoup moins coûteux à faire correctement dès le départ qu'à
            corriger ensuite.
          </p>
          <p class="seo-page__paragraph">
            Quand la création de site et le travail SEO sont menés ensemble,
            nous définissons la structure en tenant compte des requêtes cibles
            dès la phase de conception — ce qui évite d'avoir à restructurer
            des pages quelques mois après la mise en ligne.
          </p>
        </div>
        <div class="seo-page__relation-ctas">
          <NuxtLink
            to="/services/creation-site-internet"
            class="seo-page__cta seo-page__cta--secondary"
          >
            Création de site internet
          </NuxtLink>
          <NuxtLink
            to="/services/visibilite-locale"
            class="seo-page__cta seo-page__cta--secondary"
          >
            Visibilité locale
          </NuxtLink>
        </div>
      </div>
    </EditorialSection>

    <!-- G. FAQ -->
    <EditorialSection
      eyebrow="Questions fréquentes"
      title="Ce que nos clients demandent le plus souvent sur le SEO."
      section-id="faq"
    >
      <dl class="seo-page__faq">
        <div
          v-for="item in faqItems"
          :key="item.question"
          class="seo-page__faq-item"
        >
          <dt class="seo-page__faq-question">{{ item.question }}</dt>
          <dd class="seo-page__faq-answer">{{ item.answer }}</dd>
        </div>
      </dl>
    </EditorialSection>

    <!-- H. CTA final -->
    <EditorialCallout
      eyebrow="Votre visibilité"
      title="Vous voulez améliorer votre référencement naturel ?"
      description="Racontez-nous l'état actuel de votre site et ce que vous ciblez. Nous regardons ce qui bloque, ce qui est prioritaire et ce qui est réaliste à votre échelle."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />
  </div>
</template>

<style scoped>
.seo-page {
  display: flex;
  flex-direction: column;
}

/* ─── Hero CTA ─── */
.seo-page__hero-cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-3) var(--space-6);
  border-radius: var(--radius-pill);
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  white-space: nowrap;
  text-decoration: none;
  transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}

.seo-page__hero-cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.seo-page__hero-cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.seo-page__hero-cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.seo-page__hero-cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .seo-page__hero-cta { transition: none; }
}

/* ─── Pillars ─── */
.seo-page__pillars {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.seo-page__pillar {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--surface-primary);
}

.seo-page__pillar-icon {
  display: flex;
  align-items: flex-end;
  gap: 4px;
  height: 36px;
}

/* Bar chart icon for "Technique" */
.seo-page__pillar-bar {
  display: block;
  width: 10px;
  background-color: var(--action-primary);
  border-radius: 2px 2px 0 0;
  opacity: 0.6;
}

.seo-page__pillar-bar--1 { height: 14px; }
.seo-page__pillar-bar--2 { height: 24px; opacity: 0.8; }
.seo-page__pillar-bar--3 { height: 36px; opacity: 1; }

/* Lines icon for "Éditorial" */
.seo-page__pillar-line {
  display: block;
  height: 3px;
  background-color: var(--action-primary);
  border-radius: 2px;
  position: absolute;
}

.seo-page__pillar-icon:has(.seo-page__pillar-line) {
  position: relative;
  flex-direction: column;
  gap: 6px;
  align-items: flex-start;
  justify-content: center;
}

.seo-page__pillar-line--1 { width: 100%; opacity: 1; position: static; }
.seo-page__pillar-line--2 { width: 75%; opacity: 0.7; position: static; }
.seo-page__pillar-line--3 { width: 50%; opacity: 0.45; position: static; }

/* Node icon for "Maillage interne" */
.seo-page__pillar-icon:has(.seo-page__pillar-node) {
  position: relative;
  align-items: center;
  justify-content: center;
}

.seo-page__pillar-node {
  display: block;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background-color: var(--action-primary);
  position: absolute;
}

.seo-page__pillar-node--a { left: 20%; top: 30%; opacity: 0.9; }
.seo-page__pillar-node--b { right: 20%; bottom: 20%; opacity: 0.6; }

.seo-page__pillar-edge {
  display: block;
  width: 52%;
  height: 2px;
  background-color: var(--action-primary);
  opacity: 0.35;
  position: absolute;
  left: 24%;
  top: 50%;
  transform: rotate(-18deg);
}

.seo-page__pillar-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

.seo-page__pillar-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .seo-page__pillars {
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-6);
  }
}

/* ─── Limits (do / don't) ─── */
.seo-page__limits {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.seo-page__limits-heading {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0 0 var(--space-5);
}

.seo-page__limits-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: 0;
  margin: 0;
  list-style: none;
}

.seo-page__limits-col--do .seo-page__limits-list li::before {
  content: "✓ ";
  color: var(--text-accent);
  font-weight: var(--font-weight-body-strong);
}

.seo-page__limits-col--dont .seo-page__limits-list li::before {
  content: "— ";
  color: var(--text-secondary);
}

.seo-page__limits-list li {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
}

@media (min-width: 768px) {
  .seo-page__limits {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-12);
  }
}

/* ─── Audit ─── */
.seo-page__audit {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: start;
}

.seo-page__audit-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.seo-page__paragraph {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.75;
  color: var(--text-secondary);
  margin: 0;
}

.seo-page__paragraph--lead {
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  color: var(--text-primary);
}

.seo-page__audit-visual {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: var(--space-6);
}

.seo-page__audit-block {
  height: 8px;
  border-radius: 4px;
  background-color: var(--action-primary);
}

.seo-page__audit-block--1 { width: 100%; opacity: 0.9; }
.seo-page__audit-block--2 { width: 78%; opacity: 0.65; }
.seo-page__audit-block--3 { width: 55%; opacity: 0.4; }
.seo-page__audit-block--4 { width: 30%; opacity: 0.2; }

@media (min-width: 900px) {
  .seo-page__audit {
    grid-template-columns: 1fr auto;
    gap: var(--space-16);
  }
}

/* ─── Timeline ─── */
.seo-page__timeline {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.seo-page__timeline-item {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: var(--space-6);
  align-items: start;
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.seo-page__timeline-item:last-child {
  border-bottom: 1px solid var(--border-default);
}

.seo-page__timeline-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-accent);
  white-space: nowrap;
  padding-top: 5px;
  min-width: 8ch;
}

.seo-page__timeline-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.seo-page__timeline-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

.seo-page__timeline-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

/* ─── Relation ─── */
.seo-page__relation {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.seo-page__relation-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 72ch;
}

.seo-page__relation-ctas {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4);
}

.seo-page__cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-3) var(--space-6);
  border-radius: var(--radius-pill);
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  white-space: nowrap;
  text-decoration: none;
  transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.seo-page__cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.seo-page__cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .seo-page__cta { transition: none; }
}

/* ─── FAQ ─── */
.seo-page__faq {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin: 0;
}

.seo-page__faq-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.seo-page__faq-item:last-child {
  border-bottom: 1px solid var(--border-default);
}

.seo-page__faq-question {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

.seo-page__faq-answer {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.75;
  color: var(--text-secondary);
  margin: 0;
  max-width: 72ch;
}
</style>
