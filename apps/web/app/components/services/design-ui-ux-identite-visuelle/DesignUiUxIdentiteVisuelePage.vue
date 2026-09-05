<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import { caseStudies } from "~/config/case-studies"
import type { ServicePageDefinition } from "~/config/service-pages"

/**
 * Page dédiée `/services/design-ui-ux-identite-visuelle`.
 *
 * Rôle : expliquer en quoi le design — identité visuelle et interface —
 * précède et conditionne la qualité du développement, sans survendre de
 * résultats commerciaux ni de métriques inventées.
 *
 * Ce composant NE pose AUCUN SEO ni JSON-LD : ces appels sont faits
 * dans `pages/services/[slug].vue` avant le rendu.
 *
 * Distinction avec les autres services :
 *   - Création de site répond à « présenter mon activité » ;
 *   - cette page répond à « avec quelle identité et quelle interface ? ».
 */

interface Props {
  page: ServicePageDefinition
}

defineProps<Props>()

const kitchenMeat = caseStudies.find((cs) => cs.id === "kitchen-meat")

const faqItems = [
  {
    question: "Est-ce que l'identité visuelle est incluse dans une création de site ?",
    answer:
      "Pas automatiquement. Si vous avez déjà un logo, une palette et une typographie définies, nous travaillons à partir de votre existant. Si votre identité est à construire ou à formaliser, c'est un chantier à part, cadré séparément — il peut se dérouler en amont ou en parallèle du développement.",
  },
  {
    question: "Combien de temps faut-il pour une identité visuelle ?",
    answer:
      "Un travail d'identité complet — logo, palette, typographie, règles d'usage — prend en général entre deux et quatre semaines selon la complexité du projet et la disponibilité des retours. Une déclinaison partielle sur la base d'un existant est plus rapide. Le planning est défini au cadrage.",
  },
  {
    question: "Peut-on faire le design sans développer le site chez vous ?",
    answer:
      "Oui. Nous pouvons livrer les fichiers de design — maquettes, composants, spécifications — à une équipe de développement externe. Dans ce cas, nous prenons en compte les contraintes techniques de l'équipe qui va intégrer pour rester dans le réalisable.",
  },
  {
    question: "Qu'est-ce qu'un système de design et en avons-nous besoin ?",
    answer:
      "Un système de design est un ensemble de composants, de règles et de documentation qui garantissent la cohérence visuelle sur un projet complexe ou multi-écrans. Pour un site vitrine ou une boutique de taille standard, un système complet est rarement nécessaire — un guide de composants léger suffit généralement. Nous calibrons l'outil au besoin réel.",
  },
  {
    question: "Comment se passe la validation des maquettes ?",
    answer:
      "Nous travaillons de façon itérative : une première proposition, vos retours, une consolidation. Le nombre de cycles de retour est défini au démarrage. Nous partageons les maquettes dans un outil de prototypage interactif — vous pouvez naviguer entre les écrans, voir les transitions et valider chaque étape avant la phase de développement.",
  },
  {
    question: "Le design est-il pensé pour tous les écrans ?",
    answer:
      "Oui. Le design responsive — adaptation aux écrans mobiles, tablettes et desktop — est intégré par défaut. Nous définissons les points de rupture pertinents selon le contexte d'usage et concevons les maquettes pour chaque format. Aucune adaptation n'est laissée à l'improvisation du développement.",
  },
]
</script>

<template>
  <div class="dv-page">
    <!-- A. Hero -->
    <EditorialHero
      eyebrow="Design et identité visuelle"
      title="Une identité cohérente et une interface claire, conçues avant le développement."
      lead="Le design ne décide pas seulement de ce que le site aura l'air. Il décide de ce que l'utilisateur comprend, où il regarde en premier et quelle impression il emporte. Travailler le design avant le code, c'est éviter de construire quelque chose de solide techniquement mais difficile à utiliser."
    >
      <template #actions>
        <NuxtLink
          to="/contact"
          class="dv-page__hero-cta dv-page__hero-cta--primary"
        >
          Parler de votre projet
        </NuxtLink>
        <NuxtLink
          to="/estimer-mon-projet"
          class="dv-page__hero-cta dv-page__hero-cta--secondary"
        >
          Estimer mon projet
        </NuxtLink>
      </template>
    </EditorialHero>

    <!-- B. Les trois dimensions -->
    <EditorialSection
      tone="subtle"
      eyebrow="Ce que recouvre le design"
      title="Identité, interface et usage : trois questions distinctes."
      section-id="trois-dimensions"
    >
      <div class="dv-page__dimensions">
        <div class="dv-page__dimension">
          <span class="dv-page__dimension-num" aria-hidden="true">01</span>
          <div class="dv-page__dimension-content">
            <h3 class="dv-page__dimension-title">Identité visuelle</h3>
            <p class="dv-page__dimension-text">
              Logo, palette de couleurs, typographie, direction artistique —
              l'ensemble des éléments qui définissent l'apparence de votre marque
              et garantissent sa cohérence sur tous les supports.
            </p>
          </div>
        </div>
        <div class="dv-page__dimension">
          <span class="dv-page__dimension-num" aria-hidden="true">02</span>
          <div class="dv-page__dimension-content">
            <h3 class="dv-page__dimension-title">Interface utilisateur (UI)</h3>
            <p class="dv-page__dimension-text">
              Composants visuels, hiérarchie des informations, espacement,
              couleurs fonctionnelles — la façon dont les éléments sont disposés
              à l'écran pour guider l'œil et faciliter la lecture.
            </p>
          </div>
        </div>
        <div class="dv-page__dimension">
          <span class="dv-page__dimension-num" aria-hidden="true">03</span>
          <div class="dv-page__dimension-content">
            <h3 class="dv-page__dimension-title">Expérience utilisateur (UX)</h3>
            <p class="dv-page__dimension-text">
              Parcours, interactions, transitions, accessibilité — la façon dont
              l'utilisateur navigue, comprend et accomplit une action sans
              friction ni ambiguïté.
            </p>
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- C. Identité existante ou à construire -->
    <EditorialSection
      eyebrow="Point de départ"
      title="Vous avez déjà une identité, ou elle reste à définir."
      intro="Les deux situations sont courantes. Ce qui change, c'est le périmètre du travail — pas la méthode."
      section-id="identite"
    >
      <div class="dv-page__identity-grid">
        <div class="dv-page__identity-card">
          <h3 class="dv-page__identity-title">Identité existante</h3>
          <p class="dv-page__identity-text">
            Vous avez un logo, une palette, des éléments graphiques utilisés
            jusqu'ici. Nous partons de cet existant, l'analysons et construisons
            les maquettes en cohérence avec ce que vous avez déjà établi.
          </p>
          <p class="dv-page__identity-text">
            Si certains éléments manquent de précision ou de cohérence, nous
            pouvons proposer des ajustements ciblés — sans remettre en question
            l'ensemble de l'identité.
          </p>
        </div>
        <div class="dv-page__identity-card">
          <h3 class="dv-page__identity-title">Identité à construire</h3>
          <p class="dv-page__identity-text">
            Vous démarrez sans identité visuelle formalisée, ou votre marque a
            évolué et l'existant ne reflète plus ce que vous faites. Nous
            définissons ensemble les fondations : direction artistique, palette,
            typographie, logo si nécessaire.
          </p>
          <p class="dv-page__identity-text">
            Ce travail est cadré avant le démarrage du design d'interface pour
            éviter des allers-retours coûteux en cours de projet.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- D. Interface claire -->
    <EditorialSection
      tone="subtle"
      eyebrow="Lisibilité"
      title="Une interface claire n'est pas une interface vide."
      section-id="interface"
    >
      <div class="dv-page__prose">
        <p class="dv-page__paragraph dv-page__paragraph--lead">
          La clarté vient de choix précis : quoi montrer en premier, quoi
          regrouper, quoi espacer. Ce n'est pas une question de minimalisme —
          c'est une question de hiérarchie.
        </p>
        <p class="dv-page__paragraph">
          Une page chargée visuellement peut être lisible si les éléments sont
          organisés avec cohérence. Une page épurée peut être déroutante si
          l'utilisateur ne sait pas où regarder. Ce que nous travaillons, c'est
          la relation entre les éléments — pas seulement leur apparence isolée.
        </p>
        <p class="dv-page__paragraph">
          Concrètement, cela concerne la taille relative des titres, la densité
          du texte, la façon dont un bouton se distingue d'un lien, la distance
          entre les blocs — des décisions qui s'accumulent et qui font la
          différence entre un site qu'on comprend immédiatement et un site sur
          lequel on doit insister.
        </p>
      </div>
    </EditorialSection>

    <!-- E. Personnaliser sans compliquer -->
    <EditorialSection
      eyebrow="Équilibre"
      title="Personnaliser sans compliquer."
      section-id="personnalisation"
    >
      <div class="dv-page__balance">
        <div class="dv-page__balance-prose">
          <p class="dv-page__paragraph">
            Un design distinctif ne signifie pas un design complexe. Les
            contraintes — typographiques, chromatiques, d'espacement —
            permettent de créer de la cohérence sur l'ensemble des pages sans
            que chaque élément ait besoin d'une décision spécifique.
          </p>
          <p class="dv-page__paragraph">
            C'est aussi ce qui rend un projet maintenable : quand les règles
            sont définies en amont, les décisions visuelles suivantes découlent
            naturellement du système plutôt que de chaque demande individuelle.
          </p>
          <p class="dv-page__paragraph">
            Pour un projet de taille moyenne, un guide de composants léger —
            couleurs, typographie, boutons, formulaires, états — est suffisant.
            Un système de design complet est réservé aux projets complexes ou
            multi-surfaces.
          </p>
        </div>
        <div class="dv-page__balance-marks" aria-hidden="true">
          <div class="dv-page__mark dv-page__mark--a" />
          <div class="dv-page__mark dv-page__mark--b" />
          <div class="dv-page__mark dv-page__mark--c" />
        </div>
      </div>
    </EditorialSection>

    <!-- F. Du design au développement -->
    <EditorialSection
      tone="subtle"
      eyebrow="Handoff"
      title="Du design au développement, sans perte en route."
      section-id="handoff"
    >
      <div class="dv-page__handoff">
        <div class="dv-page__handoff-step">
          <span class="dv-page__handoff-num" aria-hidden="true">01</span>
          <div>
            <h3 class="dv-page__handoff-title">Maquettes interactives</h3>
            <p class="dv-page__handoff-text">
              Les écrans principaux sont prototypés dans un outil de conception
              partageable. Vous naviguez entre les états, validez les
              interactions avant que la première ligne de code soit écrite.
            </p>
          </div>
        </div>
        <div class="dv-page__handoff-step">
          <span class="dv-page__handoff-num" aria-hidden="true">02</span>
          <div>
            <h3 class="dv-page__handoff-title">Spécifications précises</h3>
            <p class="dv-page__handoff-text">
              Espacements, tailles, couleurs en valeurs exactes, comportements
              au survol, états de formulaire — rien n'est laissé à
              l'interprétation de l'intégrateur.
            </p>
          </div>
        </div>
        <div class="dv-page__handoff-step">
          <span class="dv-page__handoff-num" aria-hidden="true">03</span>
          <div>
            <h3 class="dv-page__handoff-title">Composants réutilisables</h3>
            <p class="dv-page__handoff-text">
              Les éléments répétés — cartes, boutons, formulaires, sections —
              sont conçus comme des composants documentés. Le développement
              peut les implémenter une seule fois et les utiliser partout.
            </p>
          </div>
        </div>
        <div class="dv-page__handoff-step">
          <span class="dv-page__handoff-num" aria-hidden="true">04</span>
          <div>
            <h3 class="dv-page__handoff-title">Assets livrés</h3>
            <p class="dv-page__handoff-text">
              Logos en SVG, icônes, illustrations — les fichiers sources sont
              livrés dans des formats compatibles avec l'intégration web.
            </p>
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- G. Budget -->
    <EditorialSection
      eyebrow="Budget et périmètre"
      title="Ce qui fait varier le coût d'une mission design."
      section-id="budget"
    >
      <div class="dv-page__budget">
        <div class="dv-page__budget-prose">
          <p class="dv-page__paragraph">
            Le périmètre d'une mission design varie selon plusieurs variables :
            construire une identité de zéro ou partir de l'existant, concevoir
            deux écrans principaux ou vingt-cinq, inclure ou non le mobile,
            prévoir un système de composants ou un guide minimal.
          </p>
          <p class="dv-page__paragraph">
            Une mission de déclinaison d'identité existante sur une interface
            simple est plus courte qu'un projet qui inclut la définition
            identitaire, la conception de toutes les pages et la documentation
            des composants. Ce qui est inclus est défini au cadrage, pas en
            cours de mission.
          </p>
        </div>
        <div class="dv-page__budget-ctas">
          <NuxtLink to="/estimer-mon-projet" class="dv-page__budget-cta dv-page__budget-cta--primary">
            Estimer mon projet
          </NuxtLink>
          <NuxtLink to="/contact" class="dv-page__budget-cta dv-page__budget-cta--secondary">
            Nous contacter
          </NuxtLink>
        </div>
      </div>
    </EditorialSection>

    <!-- H. Réalisation Kitchen Meat -->
    <EditorialSection
      v-if="kitchenMeat"
      tone="subtle"
      eyebrow="Réalisation"
      :title="`${kitchenMeat.name} — ${kitchenMeat.description}`"
      section-id="realisation"
    >
      <div class="dv-page__case">
        <div class="dv-page__case-content">
          <p class="dv-page__paragraph">
            Pour Kitchen Meat, un restaurant lyonnais spécialisé dans la grillade,
            nous avons travaillé l'identité visuelle depuis les fondations :
            direction artistique, palette de couleurs, typographie et éléments
            graphiques alignés sur l'univers de la grillade — chaleur, matières,
            caractère.
          </p>
          <p class="dv-page__paragraph">
            L'interface a été conçue pour guider le visiteur vers la carte et la
            réservation sans friction : hiérarchie claire, composants responsifs,
            animations légères. L'identité traverse l'ensemble des écrans sans
            rupture.
          </p>
          <ul class="dv-page__case-tags" aria-label="Interventions">
            <li
              v-for="tag in kitchenMeat.tags"
              :key="tag"
              class="dv-page__case-tag"
            >
              {{ tag }}
            </li>
          </ul>
          <NuxtLink
            :to="kitchenMeat.route"
            class="dv-page__case-link"
          >
            Voir la réalisation →
          </NuxtLink>
        </div>
        <div class="dv-page__case-visual" aria-hidden="true">
          <img
            :src="kitchenMeat.imageSrc"
            :alt="kitchenMeat.imageAlt"
            class="dv-page__case-img"
            width="560"
            height="350"
            loading="lazy"
            decoding="async"
          >
        </div>
      </div>
    </EditorialSection>

    <!-- I. FAQ -->
    <EditorialSection
      eyebrow="Questions fréquentes"
      title="Ce que nos clients demandent le plus souvent."
      section-id="faq"
    >
      <dl class="dv-page__faq">
        <div
          v-for="item in faqItems"
          :key="item.question"
          class="dv-page__faq-item"
        >
          <dt class="dv-page__faq-question">{{ item.question }}</dt>
          <dd class="dv-page__faq-answer">{{ item.answer }}</dd>
        </div>
      </dl>
    </EditorialSection>

    <!-- J. CTA final -->
    <EditorialCallout
      eyebrow="Votre projet"
      title="Un projet avec une composante design ?"
      description="Racontez-nous votre contexte — ce que vous avez déjà, ce que vous voulez construire et pour qui. Un échange court permet d'identifier ce qui relève du design et ce qui relève du développement."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />
  </div>
</template>

<style scoped>
.dv-page {
  display: flex;
  flex-direction: column;
}

/* ─── Hero CTA ─── */
.dv-page__hero-cta {
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

.dv-page__hero-cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.dv-page__hero-cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.dv-page__hero-cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.dv-page__hero-cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .dv-page__hero-cta { transition: none; }
}

/* ─── Trois dimensions ─── */
.dv-page__dimensions {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.dv-page__dimension {
  display: flex;
  gap: var(--space-5);
  align-items: flex-start;
}

.dv-page__dimension-num {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-accent);
  padding-top: 4px;
  flex-shrink: 0;
  min-width: 2rem;
}

.dv-page__dimension-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.dv-page__dimension-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.125rem, 1.5vw, 1.25rem);
  color: var(--text-primary);
  margin: 0;
}

.dv-page__dimension-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .dv-page__dimensions {
    gap: var(--space-10);
  }
}

/* ─── Identité grid ─── */
.dv-page__identity-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
}

.dv-page__identity-card {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--surface-primary);
}

.dv-page__identity-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.125rem, 1.5vw, 1.25rem);
  color: var(--text-primary);
  margin: 0;
}

.dv-page__identity-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .dv-page__identity-grid {
    grid-template-columns: 1fr 1fr;
  }
}

/* ─── Prose ─── */
.dv-page__prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 72ch;
}

.dv-page__paragraph {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.75;
  color: var(--text-secondary);
  margin: 0;
}

.dv-page__paragraph--lead {
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  color: var(--text-primary);
}

/* ─── Équilibre ─── */
.dv-page__balance {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: start;
}

.dv-page__balance-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.dv-page__balance-marks {
  display: flex;
  gap: var(--space-4);
  padding: var(--space-8);
  justify-content: center;
}

.dv-page__mark {
  border-radius: var(--radius-md);
  flex-shrink: 0;
}

.dv-page__mark--a {
  width: 48px;
  height: 72px;
  background-color: var(--action-primary);
  opacity: 0.85;
}

.dv-page__mark--b {
  width: 72px;
  height: 72px;
  background-color: var(--action-primary);
  opacity: 0.45;
  border-radius: 50%;
}

.dv-page__mark--c {
  width: 36px;
  height: 72px;
  background-color: var(--action-primary);
  opacity: 0.25;
}

@media (min-width: 900px) {
  .dv-page__balance {
    grid-template-columns: 1fr auto;
    gap: var(--space-16);
  }
}

/* ─── Handoff ─── */
.dv-page__handoff {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.dv-page__handoff-step {
  display: flex;
  gap: var(--space-5);
  align-items: flex-start;
}

.dv-page__handoff-num {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-accent);
  padding-top: 4px;
  flex-shrink: 0;
  min-width: 2rem;
}

.dv-page__handoff-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0 0 var(--space-2);
}

.dv-page__handoff-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .dv-page__handoff {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-8) var(--space-12);
  }
}

/* ─── Budget ─── */
.dv-page__budget {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.dv-page__budget-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 72ch;
}

.dv-page__budget-ctas {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4);
}

.dv-page__budget-cta {
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

.dv-page__budget-cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.dv-page__budget-cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.dv-page__budget-cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.dv-page__budget-cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .dv-page__budget-cta { transition: none; }
}

/* ─── Réalisation ─── */
.dv-page__case {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
  align-items: start;
}

.dv-page__case-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.dv-page__case-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.dv-page__case-tag {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-accent);
  padding: 4px 10px;
  border: 1px solid var(--border-default);
  border-radius: var(--radius-pill);
}

.dv-page__case-link {
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--text-accent);
  text-decoration: none;
  align-self: flex-start;
}

.dv-page__case-link:hover {
  text-decoration: underline;
}

.dv-page__case-visual {
  border-radius: var(--radius-lg);
  overflow: hidden;
  line-height: 0;
}

.dv-page__case-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

@media (min-width: 900px) {
  .dv-page__case {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-12);
  }
}

/* ─── FAQ ─── */
.dv-page__faq {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin: 0;
}

.dv-page__faq-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.dv-page__faq-item:last-child {
  border-bottom: 1px solid var(--border-default);
}

.dv-page__faq-question {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

.dv-page__faq-answer {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.75;
  color: var(--text-secondary);
  margin: 0;
  max-width: 72ch;
}
</style>
