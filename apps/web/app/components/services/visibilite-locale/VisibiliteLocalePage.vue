<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import type { ServicePageDefinition } from "~/config/service-pages"

/**
 * Page `/services/visibilite-locale`.
 *
 * Rôle : expliquer le référencement local — Google Business Profile, cohérence
 * des données de présence, requêtes de proximité — sans inventer de zones
 * géographiques couvertes ni de carte Google Maps fictive pour Devzair.
 *
 * SEO, JSON-LD et breadcrumb sont posés dans `pages/services/[slug].vue`.
 */

interface Props {
  page: ServicePageDefinition
}

defineProps<Props>()

const faqItems = [
  {
    question: "Qu'est-ce que la visibilité locale ?",
    answer:
      "La visibilité locale désigne la capacité d'un site ou d'un établissement à apparaître sur les recherches géographiques — « plombier Lyon », « restaurant près de moi », etc. Elle passe par l'optimisation de la fiche Google Business Profile, la cohérence des informations sur les annuaires, et un contenu orienté vers des requêtes de proximité.",
  },
  {
    question: "Ma fiche Google Business Profile est-elle suffisante pour être visible localement ?",
    answer:
      "La fiche Google Business Profile est un point de départ essentiel, mais pas suffisant seul. Son impact dépend de la qualité et de la complétude des informations renseignées, de la régularité des mises à jour, des avis clients, et de la cohérence avec ce que dit votre site web. Une fiche mal remplie ou incohérente avec les autres sources peut nuire à votre positionnement.",
  },
  {
    question: "Est-ce que les avis clients influencent le référencement local ?",
    answer:
      "Oui. Le volume d'avis, leur récence et la note moyenne sont des signaux que Google prend en compte pour le classement local. Nous aidons à mettre en place une démarche cohérente pour solliciter des avis authentiques — mais nous ne créons pas de faux avis ni ne participons à des pratiques contraires aux règles Google.",
  },
  {
    question: "Combien d'annuaires locaux faut-il être référencé ?",
    answer:
      "La quantité importe moins que la cohérence. Les informations (nom, adresse, téléphone) doivent être identiques sur tous les annuaires où vous apparaissez. Une présence dans vingt annuaires avec des données incohérentes est moins efficace qu'une présence dans cinq annuaires avec des données identiques et à jour.",
  },
  {
    question: "La visibilité locale s'applique-t-elle aussi aux activités sans vitrine physique ?",
    answer:
      "Oui, dans une certaine mesure. Les freelances et prestataires de services peuvent créer une fiche Google Business Profile en masquant leur adresse et en indiquant leur zone d'intervention. Les leviers sont légèrement différents — moins de trafic en boutique, mais les recherches de service à domicile ou de prestataire de proximité restent pertinentes.",
  },
  {
    question: "Quelle est la différence entre SEO classique et référencement local ?",
    answer:
      "Le SEO classique vise la visibilité sur des requêtes générales, sans dimension géographique. Le référencement local cible spécifiquement les recherches de proximité et inclut des leviers propres : fiche d'établissement, citations locales, contenu orienté vers des zones géographiques. Les deux se complètent — une base technique et éditoriale solide renforce aussi la visibilité locale.",
  },
]
</script>

<template>
  <div class="vl-page">
    <!-- A. Hero -->
    <EditorialHero
      eyebrow="Visibilité locale"
      title="Apparaître sur les recherches de proximité de manière cohérente."
      lead="Quand quelqu'un cherche votre type de service dans votre zone, les résultats qu'il voit dépendent de la qualité et de la cohérence de votre présence locale en ligne. Fiche Google, annuaires, contenu de proximité — chaque élément compte."
    >
      <template #actions>
        <NuxtLink
          to="/contact"
          class="vl-page__hero-cta vl-page__hero-cta--primary"
        >
          Parler de votre projet
        </NuxtLink>
        <NuxtLink
          to="/estimer-mon-projet"
          class="vl-page__hero-cta vl-page__hero-cta--secondary"
        >
          Estimer mon projet
        </NuxtLink>
      </template>
    </EditorialHero>

    <!-- B. Ce qui compose la visibilité locale -->
    <EditorialSection
      tone="subtle"
      eyebrow="Composantes"
      title="Ce qui compose une présence locale solide."
      section-id="composantes"
    >
      <div class="vl-page__components">
        <div class="vl-page__component">
          <div class="vl-page__component-dot vl-page__component-dot--a" aria-hidden="true" />
          <h3 class="vl-page__component-title">Fiche Google Business Profile</h3>
          <p class="vl-page__component-text">
            Nom, adresse, téléphone, horaires, catégorie d'activité, photos,
            description — chaque champ a une influence. Une fiche incomplète
            ou mal catégorisée limite la visibilité même pour des recherches
            directes sur votre nom.
          </p>
        </div>
        <div class="vl-page__component">
          <div class="vl-page__component-dot vl-page__component-dot--b" aria-hidden="true" />
          <h3 class="vl-page__component-title">Cohérence des données NAP</h3>
          <p class="vl-page__component-text">
            Le nom, l'adresse et le téléphone (NAP) doivent être identiques sur
            votre site, votre fiche Google et les principaux annuaires locaux.
            Les incohérences — un numéro différent ici, une adresse abrégée
            différemment là — affaiblissent le signal de confiance.
          </p>
        </div>
        <div class="vl-page__component">
          <div class="vl-page__component-dot vl-page__component-dot--c" aria-hidden="true" />
          <h3 class="vl-page__component-title">Contenu de proximité</h3>
          <p class="vl-page__component-text">
            Des pages ou sections qui mentionnent naturellement les zones
            géographiques que vous couvrez, les types de clients que vous
            servez localement, les spécificités de votre activité dans ce
            contexte. Pas du remplissage géographique — du contenu pertinent.
          </p>
        </div>
        <div class="vl-page__component">
          <div class="vl-page__component-dot vl-page__component-dot--d" aria-hidden="true" />
          <h3 class="vl-page__component-title">Avis et réputation</h3>
          <p class="vl-page__component-text">
            Le volume et la récence des avis influencent le classement dans les
            résultats locaux. Nous aidons à mettre en place une démarche
            pour solliciter des avis authentiques de vos clients.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- C. Fiche Google Business Profile -->
    <EditorialSection
      eyebrow="Google Business Profile"
      title="Optimiser la fiche qui représente votre établissement."
      section-id="google-business"
    >
      <div class="vl-page__gbp">
        <div class="vl-page__gbp-prose">
          <p class="vl-page__paragraph vl-page__paragraph--lead">
            La fiche Google Business Profile est souvent le premier résultat
            que voit un utilisateur quand il recherche un établissement local.
            C'est aussi la source d'informations que Google utilise pour
            afficher votre numéro, vos horaires et votre localisation dans
            les résultats de recherche et Google Maps.
          </p>
          <p class="vl-page__paragraph">
            Une fiche bien configurée ne suffit pas à se classer en premier,
            mais une fiche mal configurée — catégorie incorrecte, horaires
            manquants, description vide — plafonne votre visibilité même sur
            des recherches directes sur votre nom.
          </p>
          <p class="vl-page__paragraph">
            Nous auditons l'état actuel de la fiche, identifions les champs
            prioritaires à compléter ou à corriger, et mettons en place un
            suivi des modifications pour éviter les dérives dans le temps.
          </p>
        </div>
        <div class="vl-page__gbp-visual" aria-hidden="true">
          <div class="vl-page__proximity-dots">
            <div class="vl-page__dot vl-page__dot--center" />
            <div class="vl-page__dot vl-page__dot--1" />
            <div class="vl-page__dot vl-page__dot--2" />
            <div class="vl-page__dot vl-page__dot--3" />
            <div class="vl-page__dot vl-page__dot--4" />
            <div class="vl-page__dot vl-page__dot--5" />
            <div class="vl-page__proximity-ring vl-page__proximity-ring--1" />
            <div class="vl-page__proximity-ring vl-page__proximity-ring--2" />
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- D. Annuaires et citations -->
    <EditorialSection
      tone="subtle"
      eyebrow="Citations locales"
      title="La cohérence entre sources, pas la quantité."
      section-id="citations"
    >
      <div class="vl-page__citations">
        <div class="vl-page__citations-prose">
          <p class="vl-page__paragraph">
            Les moteurs de recherche consolident les informations sur votre
            établissement à partir de plusieurs sources : votre site, Google
            Business Profile, les annuaires généralistes (Pages Jaunes, Yelp,
            Bing Places) et les annuaires sectoriels.
          </p>
          <p class="vl-page__paragraph">
            Ce qui compte, c'est la cohérence de ces données — pas le nombre
            de sources. Nous auditons les sources existantes, identifions les
            incohérences et les corrigeons, puis mettons en place une liste de
            référence à maintenir dans le temps.
          </p>
        </div>
        <div class="vl-page__citations-list">
          <div class="vl-page__citation-item">
            <span class="vl-page__citation-label">Nom de l'établissement</span>
            <span class="vl-page__citation-status vl-page__citation-status--ok">Identique partout</span>
          </div>
          <div class="vl-page__citation-item">
            <span class="vl-page__citation-label">Adresse</span>
            <span class="vl-page__citation-status vl-page__citation-status--ok">Identique partout</span>
          </div>
          <div class="vl-page__citation-item">
            <span class="vl-page__citation-label">Téléphone</span>
            <span class="vl-page__citation-status vl-page__citation-status--warn">À vérifier</span>
          </div>
          <div class="vl-page__citation-item">
            <span class="vl-page__citation-label">Horaires</span>
            <span class="vl-page__citation-status vl-page__citation-status--warn">À compléter</span>
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- E. Ce qu'on peut dire et ce qu'on ne peut pas -->
    <EditorialSection
      eyebrow="Honnêteté"
      title="Ce qu'un travail de visibilité locale peut apporter — et ce qu'il ne garantit pas."
      section-id="honneteté"
    >
      <div class="vl-page__honesty">
        <div class="vl-page__honesty-col">
          <h3 class="vl-page__honesty-heading">Ce qui est possible</h3>
          <ul class="vl-page__honesty-list">
            <li>Améliorer la complétude et la précision de la fiche Google</li>
            <li>Résoudre les incohérences entre sources locales</li>
            <li>Mettre en place une démarche de sollicitation d'avis</li>
            <li>Créer du contenu orienté vers des requêtes de proximité</li>
            <li>Suivre l'évolution de la visibilité locale dans le temps</li>
          </ul>
        </div>
        <div class="vl-page__honesty-col">
          <h3 class="vl-page__honesty-heading">Ce qui n'est pas garanti</h3>
          <ul class="vl-page__honesty-list">
            <li>Apparaître en premier sur une requête locale spécifique</li>
            <li>Un délai précis avant d'être visible dans le pack local</li>
            <li>Le volume de contacts ou de visites générés</li>
            <li>Maintenir un positionnement face à la concurrence locale</li>
          </ul>
        </div>
      </div>
    </EditorialSection>

    <!-- F. Lien avec SEO général -->
    <EditorialSection
      tone="subtle"
      eyebrow="Complémentarité"
      title="Visibilité locale et SEO général : deux leviers qui se renforcent."
      section-id="complementarite"
    >
      <div class="vl-page__complement">
        <div class="vl-page__complement-prose">
          <p class="vl-page__paragraph">
            Le référencement local partage ses bases avec le SEO classique :
            technique, contenu, structure. Un site qui se charge vite,
            avec un balisage correct et un contenu organisé, performe mieux
            sur les recherches locales comme sur les recherches générales.
          </p>
          <p class="vl-page__paragraph">
            La spécificité du local, c'est la dimension géographique — fiche
            d'établissement, citations sur les annuaires, contenu de proximité.
            Les deux chantiers sont distincts mais se renforcent mutuellement.
          </p>
        </div>
        <div class="vl-page__complement-ctas">
          <NuxtLink
            to="/services/seo-referencement-naturel"
            class="vl-page__cta vl-page__cta--secondary"
          >
            SEO et référencement naturel
          </NuxtLink>
        </div>
      </div>
    </EditorialSection>

    <!-- G. FAQ -->
    <EditorialSection
      eyebrow="Questions fréquentes"
      title="Ce que nos clients demandent sur la visibilité locale."
      section-id="faq"
    >
      <dl class="vl-page__faq">
        <div
          v-for="item in faqItems"
          :key="item.question"
          class="vl-page__faq-item"
        >
          <dt class="vl-page__faq-question">{{ item.question }}</dt>
          <dd class="vl-page__faq-answer">{{ item.answer }}</dd>
        </div>
      </dl>
    </EditorialSection>

    <!-- H. CTA final -->
    <EditorialCallout
      eyebrow="Votre présence locale"
      title="Vous voulez améliorer votre visibilité sur les recherches de proximité ?"
      description="Racontez-nous où vous êtes et ce que vous proposez. Nous regardons l'état actuel de votre présence locale et identifions ce qui peut être amélioré."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />
  </div>
</template>

<style scoped>
.vl-page {
  display: flex;
  flex-direction: column;
}

/* ─── Hero CTA ─── */
.vl-page__hero-cta {
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

.vl-page__hero-cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.vl-page__hero-cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.vl-page__hero-cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.vl-page__hero-cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .vl-page__hero-cta { transition: none; }
}

/* ─── Components ─── */
.vl-page__components {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
}

.vl-page__component {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--surface-primary);
}

.vl-page__component-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.vl-page__component-dot--a { background-color: var(--action-primary); }
.vl-page__component-dot--b { background-color: var(--action-primary); opacity: 0.75; }
.vl-page__component-dot--c { background-color: var(--action-primary); opacity: 0.5; }
.vl-page__component-dot--d { background-color: var(--action-primary); opacity: 0.3; }

.vl-page__component-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

.vl-page__component-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .vl-page__components {
    grid-template-columns: 1fr 1fr;
  }
}

/* ─── GBP section ─── */
.vl-page__gbp {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: start;
}

.vl-page__gbp-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.vl-page__paragraph {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.75;
  color: var(--text-secondary);
  margin: 0;
}

.vl-page__paragraph--lead {
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  color: var(--text-primary);
}

/* Proximity dots visual */
.vl-page__gbp-visual {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-8);
}

.vl-page__proximity-dots {
  position: relative;
  width: 160px;
  height: 160px;
}

.vl-page__proximity-ring {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border-radius: 50%;
  border: 1px solid var(--action-primary);
}

.vl-page__proximity-ring--1 {
  width: 80px;
  height: 80px;
  opacity: 0.3;
}

.vl-page__proximity-ring--2 {
  width: 140px;
  height: 140px;
  opacity: 0.15;
}

.vl-page__dot {
  position: absolute;
  border-radius: 50%;
  background-color: var(--action-primary);
}

.vl-page__dot--center {
  width: 14px;
  height: 14px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  opacity: 1;
}

.vl-page__dot--1 { width: 8px; height: 8px; top: 20%; left: 35%; opacity: 0.65; }
.vl-page__dot--2 { width: 8px; height: 8px; top: 30%; left: 70%; opacity: 0.55; }
.vl-page__dot--3 { width: 8px; height: 8px; top: 65%; left: 25%; opacity: 0.5; }
.vl-page__dot--4 { width: 8px; height: 8px; top: 70%; left: 65%; opacity: 0.4; }
.vl-page__dot--5 { width: 8px; height: 8px; top: 15%; left: 60%; opacity: 0.3; }

@media (min-width: 900px) {
  .vl-page__gbp {
    grid-template-columns: 1fr auto;
    gap: var(--space-16);
  }
}

/* ─── Citations ─── */
.vl-page__citations {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: start;
}

.vl-page__citations-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 60ch;
}

.vl-page__citations-list {
  display: flex;
  flex-direction: column;
  gap: 0;
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.vl-page__citation-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: var(--space-4);
  padding: var(--space-4) var(--space-5);
  border-bottom: 1px solid var(--border-default);
  background-color: var(--surface-primary);
}

.vl-page__citation-item:last-child {
  border-bottom: none;
}

.vl-page__citation-label {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  color: var(--text-secondary);
}

.vl-page__citation-status {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: var(--radius-pill);
  white-space: nowrap;
}

.vl-page__citation-status--ok {
  color: var(--text-accent);
  border: 1px solid var(--border-default);
}

.vl-page__citation-status--warn {
  color: var(--text-secondary);
  border: 1px solid var(--border-default);
}

@media (min-width: 900px) {
  .vl-page__citations {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-12);
  }
}

/* ─── Honesty ─── */
.vl-page__honesty {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.vl-page__honesty-heading {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0 0 var(--space-5);
}

.vl-page__honesty-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: 0;
  margin: 0;
  list-style: none;
}

.vl-page__honesty-col:first-child .vl-page__honesty-list li::before {
  content: "✓ ";
  color: var(--text-accent);
  font-weight: var(--font-weight-body-strong);
}

.vl-page__honesty-col:last-child .vl-page__honesty-list li::before {
  content: "— ";
  color: var(--text-secondary);
}

.vl-page__honesty-list li {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
}

@media (min-width: 768px) {
  .vl-page__honesty {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-12);
  }
}

/* ─── Complement ─── */
.vl-page__complement {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.vl-page__complement-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 72ch;
}

.vl-page__complement-ctas {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4);
}

.vl-page__cta {
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

.vl-page__cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.vl-page__cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .vl-page__cta { transition: none; }
}

/* ─── FAQ ─── */
.vl-page__faq {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin: 0;
}

.vl-page__faq-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.vl-page__faq-item:last-child {
  border-bottom: 1px solid var(--border-default);
}

.vl-page__faq-question {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

.vl-page__faq-answer {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.75;
  color: var(--text-secondary);
  margin: 0;
  max-width: 72ch;
}
</style>
