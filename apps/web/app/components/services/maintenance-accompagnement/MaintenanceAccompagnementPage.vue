<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import type { ServicePageDefinition } from "~/config/service-pages"

/**
 * Page `/services/maintenance-accompagnement`.
 *
 * Rôle : expliquer la distinction entre maintenance corrective, préventive
 * et évolution — sans promettre de disponibilité garantie ni de "zéro downtime".
 *
 * SEO, JSON-LD et breadcrumb sont posés dans `pages/services/[slug].vue`.
 */

interface Props {
  page: ServicePageDefinition
}

defineProps<Props>()

const faqItems = [
  {
    question: "Maintenez-vous des sites que vous n'avez pas développés ?",
    answer:
      "C'est possible, mais cela dépend de la technologie utilisée et de l'état du code. Avant de prendre en charge un site que nous n'avons pas développé, nous faisons un audit technique pour comprendre la base sur laquelle on travaille, identifier les risques et estimer le temps nécessaire à une prise en main correcte.",
  },
  {
    question: "Que se passe-t-il si le site tombe en panne la nuit ou le week-end ?",
    answer:
      "Nous ne proposons pas de service d'astreinte 24h/24, 7j/7. Les interventions d'urgence en dehors des heures ouvrées ne font pas partie de nos contrats standards. Si votre activité nécessite une disponibilité critique permanente, c'est un point à aborder au cadrage — cela implique une organisation spécifique que nous ne pouvons pas promettre dans le cadre général.",
  },
  {
    question: "Quelle est la différence entre mise à jour et évolution ?",
    answer:
      "Une mise à jour technique (sécurité, correctifs, dépendances) maintient le site dans un état sain sans modifier ses fonctionnalités. Une évolution ajoute ou modifie une fonctionnalité — un nouveau formulaire, une section, un module. Les deux sont dans le périmètre de l'accompagnement, mais ne relèvent pas du même budget ni du même planning.",
  },
  {
    question: "À quelle fréquence le site est-il mis à jour ?",
    answer:
      "La fréquence des mises à jour de sécurité dépend des versions publiées pour chaque technologie utilisée. Pour les CMS et les frameworks actifs, nous appliquons les mises à jour critiques dès que possible et les mises à jour mineures lors des cycles de maintenance réguliers. Les évolutions fonctionnelles sont planifiées selon le backlog convenu.",
  },
  {
    question: "Faut-il un contrat pour des interventions ponctuelles ?",
    answer:
      "Non. Nous intervenons aussi ponctuellement — correction d'un bug, ajustement d'une page, petite modification de contenu. Ces interventions sont facturées au temps passé. Un contrat de maintenance est pertinent quand le volume d'interventions devient régulier et que vous souhaitez une visibilité sur les délais et les budgets.",
  },
  {
    question: "Que comprend un contrat de maintenance ?",
    answer:
      "Cela varie selon les besoins. En règle générale : mises à jour de sécurité des composants, sauvegardes régulières, surveillance de la disponibilité du site, et un quota d'heures pour les corrections et évolutions mineures. Le périmètre exact est défini au cadrage selon la technologie, le volume attendu et votre organisation.",
  },
]
</script>

<template>
  <div class="maint-page">
    <!-- A. Hero -->
    <EditorialHero
      eyebrow="Maintenance et accompagnement"
      title="Un suivi technique régulier plutôt que des interventions en urgence."
      lead="Un site laissé sans maintenance accumule des mises à jour manquées, des correctifs de sécurité en retard et des petites dérives qui finissent par coûter cher à corriger. Un suivi préventif est moins visible — et moins coûteux — qu'une réparation d'urgence."
    >
      <template #actions>
        <NuxtLink
          to="/contact"
          class="maint-page__hero-cta maint-page__hero-cta--primary"
        >
          Parler de votre projet
        </NuxtLink>
        <NuxtLink
          to="/estimer-mon-projet"
          class="maint-page__hero-cta maint-page__hero-cta--secondary"
        >
          Estimer mon projet
        </NuxtLink>
      </template>
    </EditorialHero>

    <!-- B. Les trois types d'intervention -->
    <EditorialSection
      tone="subtle"
      eyebrow="Types d'intervention"
      title="Trois niveaux d'intervention distincts."
      section-id="types"
    >
      <div class="maint-page__types">
        <div class="maint-page__type">
          <div class="maint-page__type-cycle" aria-hidden="true">
            <div class="maint-page__cycle-ring maint-page__cycle-ring--1" />
          </div>
          <h3 class="maint-page__type-title">Maintenance corrective</h3>
          <p class="maint-page__type-text">
            Correction de bugs, ajustements d'affichage, résolution d'erreurs
            techniques. Ce qui ne fonctionne pas comme prévu et doit être
            réparé. Ces interventions sont prioritaires.
          </p>
        </div>
        <div class="maint-page__type">
          <div class="maint-page__type-cycle" aria-hidden="true">
            <div class="maint-page__cycle-ring maint-page__cycle-ring--2" />
          </div>
          <h3 class="maint-page__type-title">Maintenance préventive</h3>
          <p class="maint-page__type-text">
            Mises à jour de sécurité, correctifs des dépendances, sauvegardes
            régulières, surveillance de la disponibilité. Ce qui évite que
            les problèmes arrivent plutôt que de les corriger après.
          </p>
        </div>
        <div class="maint-page__type">
          <div class="maint-page__type-cycle" aria-hidden="true">
            <div class="maint-page__cycle-ring maint-page__cycle-ring--3" />
          </div>
          <h3 class="maint-page__type-title">Évolutions</h3>
          <p class="maint-page__type-text">
            Ajout de fonctionnalités, modification de pages, nouvelles sections.
            Ce qui fait évoluer le site en fonction de votre activité.
            Planifié selon un backlog et un budget convenu.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- C. Pourquoi la maintenance préventive -->
    <EditorialSection
      eyebrow="Prévention"
      title="Pourquoi les mises à jour régulières ne sont pas optionnelles."
      section-id="prevention"
    >
      <div class="maint-page__prevention">
        <div class="maint-page__prevention-prose">
          <p class="maint-page__paragraph maint-page__paragraph--lead">
            Un site web utilise des dizaines de composants — framework,
            bibliothèques, plugins, dépendances. Chacun reçoit des mises à
            jour de sécurité dès que des vulnérabilités sont identifiées.
          </p>
          <p class="maint-page__paragraph">
            Un site non maintenu accumule ces retards. Une faille non corrigée
            dans un plugin populaire peut compromettre un site en quelques
            heures après que la vulnérabilité est rendue publique. Ce n'est
            pas une hypothèse — c'est ce qui arrive régulièrement aux sites
            laissés sans suivi.
          </p>
          <p class="maint-page__paragraph">
            La maintenance préventive ne garantit pas l'invulnérabilité — aucun
            système ne peut promettre ça. Elle réduit la surface d'exposition
            et raccourcit le délai de réaction quand un problème survient.
          </p>
        </div>
        <div class="maint-page__prevention-timeline" aria-hidden="true">
          <div class="maint-page__tl-row">
            <span class="maint-page__tl-dot maint-page__tl-dot--done" />
            <span class="maint-page__tl-line" />
            <span class="maint-page__tl-dot maint-page__tl-dot--done" />
            <span class="maint-page__tl-line" />
            <span class="maint-page__tl-dot maint-page__tl-dot--active" />
            <span class="maint-page__tl-line maint-page__tl-line--pending" />
            <span class="maint-page__tl-dot maint-page__tl-dot--pending" />
          </div>
          <p class="maint-page__tl-label">Cycle de maintenance régulier</p>
        </div>
      </div>
    </EditorialSection>

    <!-- D. Ce qu'on couvre -->
    <EditorialSection
      tone="subtle"
      eyebrow="Périmètre"
      title="Ce que couvre un suivi régulier."
      section-id="perimetre"
    >
      <div class="maint-page__scope">
        <div class="maint-page__scope-item">
          <span class="maint-page__scope-num" aria-hidden="true">01</span>
          <div>
            <h3 class="maint-page__scope-title">Mises à jour de sécurité</h3>
            <p class="maint-page__scope-text">
              Application des correctifs de sécurité dès leur publication pour
              les technologies utilisées — framework, bibliothèques, serveur.
            </p>
          </div>
        </div>
        <div class="maint-page__scope-item">
          <span class="maint-page__scope-num" aria-hidden="true">02</span>
          <div>
            <h3 class="maint-page__scope-title">Sauvegardes régulières</h3>
            <p class="maint-page__scope-text">
              Sauvegardes planifiées de la base de données et des fichiers,
              stockées en dehors du serveur de production. Procédure de
              restauration testée périodiquement.
            </p>
          </div>
        </div>
        <div class="maint-page__scope-item">
          <span class="maint-page__scope-num" aria-hidden="true">03</span>
          <div>
            <h3 class="maint-page__scope-title">Surveillance de disponibilité</h3>
            <p class="maint-page__scope-text">
              Monitoring du site pour détecter rapidement une panne ou une
              dégradation — et intervenir avant que vos clients ne la signalent.
            </p>
          </div>
        </div>
        <div class="maint-page__scope-item">
          <span class="maint-page__scope-num" aria-hidden="true">04</span>
          <div>
            <h3 class="maint-page__scope-title">Corrections et évolutions</h3>
            <p class="maint-page__scope-text">
              Quota d'heures mensuel ou trimestriel pour les corrections mineures,
              ajustements de contenu et petites évolutions planifiées.
            </p>
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- E. Ce qu'on ne promet pas -->
    <EditorialSection
      eyebrow="Limites honnêtes"
      title="Ce que la maintenance ne garantit pas."
      section-id="limites"
    >
      <div class="maint-page__limits">
        <div class="maint-page__limits-prose">
          <p class="maint-page__paragraph">
            Nous ne promettons pas de disponibilité à 100% ni de "zéro downtime".
            Un serveur peut tomber indépendamment de la qualité du code. Une
            mise à jour peut introduire une incompatibilité. Ces situations
            arrivent — l'objectif de la maintenance est de les détecter vite
            et d'intervenir rapidement.
          </p>
          <p class="maint-page__paragraph">
            Nous ne proposons pas d'astreinte 24h/24, 7j/7. Les interventions
            en dehors des heures ouvrées ne font pas partie de nos contrats
            standards. Si votre activité requiert une disponibilité critique
            permanente, c'est un point à discuter en amont — pas une promesse
            que nous formulons par défaut.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- F. Liens vers services complémentaires -->
    <EditorialSection
      tone="subtle"
      eyebrow="Continuité"
      title="Maintenance et évolution : deux temps d'un même projet."
      section-id="continuite"
    >
      <div class="maint-page__continuity">
        <div class="maint-page__continuity-prose">
          <p class="maint-page__paragraph">
            Un site mis en ligne est le début d'un travail, pas sa fin. Les
            besoins évoluent, les technologies changent, les contenus vieillissent.
            La maintenance préventive et les évolutions régulières sont ce
            qui maintient un site en phase avec l'activité qu'il représente.
          </p>
          <p class="maint-page__paragraph">
            Quand nous créons un site, nous pensons dès le départ à sa
            maintenabilité — code structuré, documentation des choix techniques,
            convention de déploiement établie. C'est ce qui rend les évolutions
            futures moins coûteuses.
          </p>
        </div>
        <div class="maint-page__continuity-ctas">
          <NuxtLink
            to="/services/creation-site-internet"
            class="maint-page__cta maint-page__cta--secondary"
          >
            Création de site internet
          </NuxtLink>
          <NuxtLink
            to="/services/application-web-metier"
            class="maint-page__cta maint-page__cta--secondary"
          >
            Application web métier
          </NuxtLink>
        </div>
      </div>
    </EditorialSection>

    <!-- G. FAQ -->
    <EditorialSection
      eyebrow="Questions fréquentes"
      title="Ce que nos clients demandent sur la maintenance."
      section-id="faq"
    >
      <dl class="maint-page__faq">
        <div
          v-for="item in faqItems"
          :key="item.question"
          class="maint-page__faq-item"
        >
          <dt class="maint-page__faq-question">{{ item.question }}</dt>
          <dd class="maint-page__faq-answer">{{ item.answer }}</dd>
        </div>
      </dl>
    </EditorialSection>

    <!-- H. CTA final -->
    <EditorialCallout
      eyebrow="Votre site"
      title="Votre site a besoin d'un suivi technique régulier ?"
      description="Dites-nous ce que vous avez — la technologie, le contexte, le niveau d'activité du site. Nous regardons ce qui est pertinent et ce qui ne l'est pas pour votre situation."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />
  </div>
</template>

<style scoped>
.maint-page {
  display: flex;
  flex-direction: column;
}

/* ─── Hero CTA ─── */
.maint-page__hero-cta {
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

.maint-page__hero-cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.maint-page__hero-cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.maint-page__hero-cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.maint-page__hero-cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .maint-page__hero-cta { transition: none; }
}

/* ─── Types ─── */
.maint-page__types {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
}

.maint-page__type {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--surface-primary);
}

.maint-page__type-cycle {
  width: 36px;
  height: 36px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.maint-page__cycle-ring {
  position: absolute;
  border-radius: 50%;
  border-style: solid;
  border-color: var(--action-primary);
}

.maint-page__cycle-ring--1 {
  width: 24px;
  height: 24px;
  border-width: 2px;
  opacity: 0.9;
}

.maint-page__cycle-ring--2 {
  width: 28px;
  height: 28px;
  border-width: 2px;
  border-style: dashed;
  opacity: 0.6;
}

.maint-page__cycle-ring--3 {
  width: 24px;
  height: 24px;
  border-width: 2px;
  opacity: 0.35;
  border-top-color: transparent;
  transform: rotate(45deg);
}

.maint-page__type-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

.maint-page__type-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .maint-page__types {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* ─── Prevention ─── */
.maint-page__prevention {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: start;
}

.maint-page__prevention-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.maint-page__paragraph {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.75;
  color: var(--text-secondary);
  margin: 0;
}

.maint-page__paragraph--lead {
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  color: var(--text-primary);
}

/* Timeline visual */
.maint-page__prevention-timeline {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  align-items: center;
  padding: var(--space-8);
}

.maint-page__tl-row {
  display: flex;
  align-items: center;
  gap: 0;
}

.maint-page__tl-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
}

.maint-page__tl-dot--done {
  background-color: var(--action-primary);
}

.maint-page__tl-dot--active {
  background-color: var(--action-primary);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--action-primary) 25%, transparent);
}

.maint-page__tl-dot--pending {
  background-color: transparent;
  border: 2px solid var(--border-default);
}

.maint-page__tl-line {
  width: 40px;
  height: 2px;
  background-color: var(--action-primary);
}

.maint-page__tl-line--pending {
  background-color: var(--border-default);
}

.maint-page__tl-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-tertiary);
  margin: 0;
  text-align: center;
}

@media (min-width: 900px) {
  .maint-page__prevention {
    grid-template-columns: 1fr auto;
    gap: var(--space-16);
  }
}

/* ─── Scope ─── */
.maint-page__scope {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.maint-page__scope-item {
  display: flex;
  gap: var(--space-6);
  align-items: flex-start;
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.maint-page__scope-item:last-child {
  border-bottom: 1px solid var(--border-default);
}

.maint-page__scope-num {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-accent);
  padding-top: 5px;
  flex-shrink: 0;
  min-width: 2rem;
}

.maint-page__scope-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0 0 var(--space-3);
}

.maint-page__scope-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

/* ─── Limits ─── */
.maint-page__limits {
  max-width: 72ch;
}

.maint-page__limits-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

/* ─── Continuity ─── */
.maint-page__continuity {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.maint-page__continuity-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 72ch;
}

.maint-page__continuity-ctas {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4);
}

.maint-page__cta {
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

.maint-page__cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.maint-page__cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .maint-page__cta { transition: none; }
}

/* ─── FAQ ─── */
.maint-page__faq {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin: 0;
}

.maint-page__faq-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.maint-page__faq-item:last-child {
  border-bottom: 1px solid var(--border-default);
}

.maint-page__faq-question {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

.maint-page__faq-answer {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.75;
  color: var(--text-secondary);
  margin: 0;
  max-width: 72ch;
}
</style>
