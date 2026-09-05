<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import { caseStudies } from "~/config/case-studies"
import type { ServicePageDefinition } from "~/config/service-pages"

/**
 * Page dédiée `/services/photographie-creation-contenu`.
 *
 * Rôle : expliquer ce que signifie produire un contenu web factuel — textes,
 * photographie, structure éditoriale — sans promettre de résultats SEO ni
 * inventer de portfolio photographique.
 *
 * Ce composant NE pose AUCUN SEO ni JSON-LD : ces appels sont faits
 * dans `pages/services/[slug].vue` avant le rendu.
 *
 * Note : Nidemiel est cité pour son travail éditorial (fiches produit,
 * descriptions) — PAS pour la photographie, qui n'a pas été réalisée
 * par Devzair sur ce projet.
 */

interface Props {
  page: ServicePageDefinition
}

defineProps<Props>()

const nidemiel = caseStudies.find((cs) => cs.id === "nidemiel")

const faqItems = [
  {
    question: "Les textes et les photos sont-ils inclus dans la création de site ?",
    answer:
      "Non, par défaut. La rédaction et la photographie sont des postes budgétaires distincts de la création de site ou de la boutique. Ce point est abordé au cadrage pour définir ce qui est inclus, ce qui est produit séparément et ce qui reste à votre charge.",
  },
  {
    question: "Peut-on utiliser nos propres photos ?",
    answer:
      "Oui. Si vous avez des photos récentes, en bonne résolution et représentatives de votre activité, nous les intégrons. Si certains manquent ou si la qualité n'est pas adaptée à une utilisation web, nous discutons des alternatives — nouvelle prise de vue, banque d'images adaptée ou reformulation de la mise en page.",
  },
  {
    question: "Quel format de photos faut-il prévoir pour le web ?",
    answer:
      "Les photos destinées au web sont livrées en formats optimisés (WebP, AVIF avec fallback JPEG), recadrées selon l'usage — format paysage pour les héros, carré pour les galeries, portrait pour les fiches produit. L'optimisation du poids est systématique pour éviter de pénaliser les temps de chargement.",
  },
  {
    question: "Rédigez-vous les textes dans tous les secteurs d'activité ?",
    answer:
      "Nous travaillons à partir des informations que vous nous transmettez — ce que vous faites, pour qui, comment. Ce sont vos connaissances métier qui donnent la substance ; ce que nous faisons, c'est les structurer pour un lecteur web et les adapter à chaque page. Nous ne produisons pas de contenu sans brief de votre part.",
  },
  {
    question: "Combien de temps faut-il pour produire les contenus ?",
    answer:
      "Le délai dépend du volume — nombre de pages, de fiches produit, de photos à réaliser — et de la disponibilité des informations de votre côté. Un site vitrine de cinq pages avec rédaction et photos peut prendre une à deux semaines. Un catalogue e-commerce de cent références est un chantier différent.",
  },
  {
    question: "Est-ce que les contenus sont optimisés pour le référencement ?",
    answer:
      "Les textes que nous rédigeons sont structurés pour être lisibles par les moteurs : hiérarchie de titres cohérente, mots-clés présents naturellement, balises alt sur les images. Un travail SEO de fond — étude de mots-clés, audit de l'existant, netlinking — dépasse ce périmètre et relève du service SEO distinct.",
  },
]
</script>

<template>
  <div class="pcc-page">
    <!-- A. Hero -->
    <EditorialHero
      eyebrow="Photographie et contenu web"
      title="Des photos et des textes qui décrivent ce que vous faites réellement."
      lead="Le contenu d'un site, c'est ce que vos visiteurs lisent et voient. Des textes vagues et des photos génériques ne décrivent pas votre activité — ils ne la distinguent pas non plus. Ce que nous produisons doit être exact, utile et représentatif de ce que vous faites."
    >
      <template #actions>
        <NuxtLink
          to="/contact"
          class="pcc-page__hero-cta pcc-page__hero-cta--primary"
        >
          Parler de votre projet
        </NuxtLink>
        <NuxtLink
          to="/estimer-mon-projet"
          class="pcc-page__hero-cta pcc-page__hero-cta--secondary"
        >
          Estimer mon projet
        </NuxtLink>
      </template>
    </EditorialHero>

    <!-- B. Avant de produire -->
    <EditorialSection
      tone="subtle"
      eyebrow="Point de départ"
      title="Avant de produire, on regarde ce qui existe."
      section-id="avant-de-produire"
    >
      <div class="pcc-page__prose">
        <p class="pcc-page__paragraph pcc-page__paragraph--lead">
          Beaucoup de sites ont déjà du contenu — des textes, des photos, des
          descriptions — mais il est dispersé, vieilli ou inadapté au format
          web. Avant de produire quoi que ce soit, nous faisons le point sur
          ce que vous avez.
        </p>
        <p class="pcc-page__paragraph">
          Certains contenus peuvent être réutilisés avec ajustements. D'autres
          doivent être repris de zéro. Et parfois, un format de page différent
          rend la question du contenu plus simple qu'elle n'y paraissait.
          Ce diagnostic préalable évite de produire du contenu pour le jeter
          ensuite.
        </p>
        <p class="pcc-page__paragraph">
          C'est aussi à cette étape que nous définissons le bon niveau de
          contenu pour chaque page — certaines pages méritent un texte long
          et documenté, d'autres sont plus efficaces avec une phrase et un
          visuel.
        </p>
      </div>
    </EditorialSection>

    <!-- C. Montrer -->
    <EditorialSection
      eyebrow="Photographie"
      title="Montrer votre activité, vos produits, votre lieu."
      section-id="photographie"
    >
      <div class="pcc-page__photo-grid">
        <div class="pcc-page__photo-block">
          <h3 class="pcc-page__block-title">Ce que la photo fait que le texte ne fait pas</h3>
          <p class="pcc-page__paragraph">
            Une photo de votre atelier, de vos produits ou de votre équipe
            dit quelque chose qu'un paragraphe de description ne peut pas
            remplacer. Elle donne une échelle, une lumière, une texture.
          </p>
          <p class="pcc-page__paragraph">
            Ce qui est utile, c'est une photo qui correspond à la réalité de
            ce que vous faites — pas une image de stock qui pourrait décrire
            n'importe quelle activité du même secteur.
          </p>
        </div>
        <div class="pcc-page__photo-block">
          <h3 class="pcc-page__block-title">Formats adaptés à l'usage</h3>
          <p class="pcc-page__paragraph">
            Hero pleine largeur, vignettes de galerie, photos de fiche produit,
            portraits d'équipe — chaque usage a des contraintes de format et
            de cadrage différentes. Nous cadrons la prise de vue en fonction
            de l'intégration prévue, pas après coup.
          </p>
          <p class="pcc-page__paragraph">
            Les fichiers sont livrés dans des formats optimisés pour le web,
            avec les variantes de taille nécessaires pour le rendu responsive.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- D. Expliquer -->
    <EditorialSection
      tone="subtle"
      eyebrow="Rédaction"
      title="Expliquer ce que vous faites, pour quelqu'un qui ne le sait pas encore."
      section-id="redaction"
    >
      <div class="pcc-page__explain">
        <div class="pcc-page__explain-prose">
          <p class="pcc-page__paragraph pcc-page__paragraph--lead">
            Un texte de page web doit être compris du premier coup par quelqu'un
            qui ne connaît pas votre secteur. Ce n'est pas simplifier — c'est
            choisir les bons mots pour le bon lecteur.
          </p>
          <p class="pcc-page__paragraph">
            Ce que nous faisons : partir de ce que vous nous expliquez —
            votre activité, vos clients, ce qui vous distingue — et le
            traduire en texte structuré, lisible en diagonale, avec une
            hiérarchie claire entre ce qui est important et ce qui est
            secondaire.
          </p>
          <p class="pcc-page__paragraph">
            Ce que nous ne faisons pas : rédiger sans brief, inventer des
            caractéristiques ou reprendre des formulations génériques qui
            pourraient décrire n'importe qui dans votre secteur.
          </p>
        </div>
        <div class="pcc-page__explain-types">
          <div class="pcc-page__type-item">
            <h3 class="pcc-page__type-title">Pages de présentation</h3>
            <p class="pcc-page__type-text">Accueil, page service, page à propos — textes qui expliquent ce que vous faites et pour qui.</p>
          </div>
          <div class="pcc-page__type-item">
            <h3 class="pcc-page__type-title">Fiches produit</h3>
            <p class="pcc-page__type-text">Descriptions précises, caractéristiques techniques, informations utiles à la décision d'achat.</p>
          </div>
          <div class="pcc-page__type-item">
            <h3 class="pcc-page__type-title">Contenus d'accompagnement</h3>
            <p class="pcc-page__type-text">FAQ, conditions, textes de confirmation, messages d'erreur — les petits textes qui font la différence sur l'expérience.</p>
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- E. Écrire pour une personne -->
    <EditorialSection
      eyebrow="Ton et voix"
      title="Écrire pour une personne, pas pour un moteur."
      section-id="ton"
    >
      <div class="pcc-page__tone">
        <div class="pcc-page__tone-prose">
          <p class="pcc-page__paragraph">
            Le ton d'un texte — formel ou direct, technique ou accessible —
            dépend de qui vous lisez et de ce qu'ils cherchent à comprendre.
            Un artisan qui s'adresse à des particuliers n'écrit pas comme un
            cabinet qui s'adresse à des directions générales.
          </p>
          <p class="pcc-page__paragraph">
            Nous ajustons le registre à votre activité et à vos clients.
            Ce qui ne change pas : la clarté, la précision, l'absence de
            formules vides.
          </p>
        </div>
        <div class="pcc-page__tone-marks" aria-hidden="true">
          <div class="pcc-page__tone-mark pcc-page__tone-mark--a" />
          <div class="pcc-page__tone-mark pcc-page__tone-mark--b" />
        </div>
      </div>
    </EditorialSection>

    <!-- F. Photo et texte ensemble -->
    <EditorialSection
      tone="subtle"
      eyebrow="Cohérence"
      title="Photo et texte : deux éléments qui travaillent ensemble."
      section-id="coherence"
    >
      <div class="pcc-page__combo">
        <div class="pcc-page__combo-item">
          <span class="pcc-page__combo-num" aria-hidden="true">01</span>
          <div>
            <h3 class="pcc-page__combo-title">Cadrage conjoint</h3>
            <p class="pcc-page__combo-text">
              Quand photo et texte sont produits dans la même mission, nous
              définissons en amont ce que chaque image doit montrer en relation
              avec ce que le texte explique. Pas de redondance, pas de
              contradiction entre les deux.
            </p>
          </div>
        </div>
        <div class="pcc-page__combo-item">
          <span class="pcc-page__combo-num" aria-hidden="true">02</span>
          <div>
            <h3 class="pcc-page__combo-title">Intégration pensée</h3>
            <p class="pcc-page__combo-text">
              Les photos sont choisies ou réalisées en fonction de leur
              position dans la page : un visuel hero a des besoins différents
              d'une photo de section ou d'une vignette de carte produit.
            </p>
          </div>
        </div>
        <div class="pcc-page__combo-item">
          <span class="pcc-page__combo-num" aria-hidden="true">03</span>
          <div>
            <h3 class="pcc-page__combo-title">Textes alt descriptifs</h3>
            <p class="pcc-page__combo-text">
              Chaque image reçoit un texte alternatif précis — utile pour
              l'accessibilité, mais aussi pour les moteurs qui ne peuvent pas
              interpréter le contenu visuel.
            </p>
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- G. Intégration au projet -->
    <EditorialSection
      eyebrow="Organisation"
      title="Le contenu, intégré au calendrier du projet."
      section-id="integration"
    >
      <div class="pcc-page__integration">
        <div class="pcc-page__integration-prose">
          <p class="pcc-page__paragraph">
            Les contenus arrivent souvent trop tard dans un projet de site.
            Textes à finaliser, photos manquantes, descriptions incomplètes —
            c'est fréquemment ce qui rallonge les délais de mise en ligne.
          </p>
          <p class="pcc-page__paragraph">
            Quand la production de contenu est intégrée à la mission, nous
            planifions les échéances de livraison des textes et des photos
            en amont du développement — pas en parallèle ou après. Le
            développeur peut alors intégrer des contenus réels dès le départ,
            ce qui évite les mises à jour tardives et les ajustements de
            mise en page de dernière minute.
          </p>
        </div>
        <div class="pcc-page__integration-ctas">
          <NuxtLink to="/estimer-mon-projet" class="pcc-page__cta pcc-page__cta--primary">
            Estimer mon projet
          </NuxtLink>
          <NuxtLink to="/contact" class="pcc-page__cta pcc-page__cta--secondary">
            Nous contacter
          </NuxtLink>
        </div>
      </div>
    </EditorialSection>

    <!-- H. Budget -->
    <EditorialSection
      tone="subtle"
      eyebrow="Budget"
      title="Ce qui fait varier le périmètre d'une mission contenu."
      section-id="budget"
    >
      <div class="pcc-page__budget">
        <div class="pcc-page__budget-item">
          <h3 class="pcc-page__budget-title">Rédaction seule</h3>
          <p class="pcc-page__budget-text">
            Volume de textes à produire, nombre de pages, niveau de recherche
            nécessaire selon le secteur, nombre de cycles de relecture.
          </p>
        </div>
        <div class="pcc-page__budget-item">
          <h3 class="pcc-page__budget-title">Photographie seule</h3>
          <p class="pcc-page__budget-text">
            Nombre de produits ou de sujets, lieu de prise de vue, variantes
            de cadrage demandées, post-traitement et volume de fichiers livrés.
          </p>
        </div>
        <div class="pcc-page__budget-item">
          <h3 class="pcc-page__budget-title">Mission complète</h3>
          <p class="pcc-page__budget-text">
            Rédaction et photographie dans la même mission — planifiées
            ensemble, livrées en cohérence. Cadrage conjoint inclus.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- I. Réalisation Nidemiel — éditorial -->
    <EditorialSection
      v-if="nidemiel"
      eyebrow="Réalisation — contenu éditorial"
      :title="`${nidemiel.name} — ${nidemiel.description}`"
      section-id="realisation"
    >
      <div class="pcc-page__case">
        <div class="pcc-page__case-content">
          <p class="pcc-page__paragraph">
            Pour Nidemiel, une boutique en ligne de cosmétiques, nous avons
            travaillé la structure éditoriale des fiches produit : hiérarchie
            des informations, descriptions précises des ingrédients et de
            leurs bénéfices, organisation des variantes. L'objectif était
            de rendre les fiches lisibles en quelques secondes tout en
            donnant les informations nécessaires à la décision d'achat.
          </p>
          <p class="pcc-page__paragraph">
            Ce travail éditorial a été mené en parallèle du développement de
            la boutique, ce qui a permis d'intégrer des contenus réels dès
            le début — sans placeholder à remplacer en fin de projet.
          </p>
          <ul class="pcc-page__case-tags" aria-label="Interventions">
            <li
              v-for="tag in nidemiel.tags"
              :key="tag"
              class="pcc-page__case-tag"
            >
              {{ tag }}
            </li>
          </ul>
          <NuxtLink
            :to="nidemiel.route"
            class="pcc-page__case-link"
          >
            Voir la réalisation →
          </NuxtLink>
        </div>
        <div class="pcc-page__case-visual" aria-hidden="true">
          <img
            :src="nidemiel.imageSrc"
            :alt="nidemiel.imageAlt"
            class="pcc-page__case-img"
            width="560"
            height="350"
            loading="lazy"
            decoding="async"
          >
        </div>
      </div>
    </EditorialSection>

    <!-- J. FAQ -->
    <EditorialSection
      tone="subtle"
      eyebrow="Questions fréquentes"
      title="Ce que nos clients demandent le plus souvent."
      section-id="faq"
    >
      <dl class="pcc-page__faq">
        <div
          v-for="item in faqItems"
          :key="item.question"
          class="pcc-page__faq-item"
        >
          <dt class="pcc-page__faq-question">{{ item.question }}</dt>
          <dd class="pcc-page__faq-answer">{{ item.answer }}</dd>
        </div>
      </dl>
    </EditorialSection>

    <!-- K. CTA final -->
    <EditorialCallout
      eyebrow="Votre projet"
      title="Un projet avec une production de contenu ?"
      description="Racontez-nous ce que vous faites et ce dont vous avez besoin. Textes, photos, ou les deux — nous cadrons ensemble ce qui est utile et ce qui ne l'est pas."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />
  </div>
</template>

<style scoped>
.pcc-page {
  display: flex;
  flex-direction: column;
}

/* ─── Hero CTA ─── */
.pcc-page__hero-cta {
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

.pcc-page__hero-cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.pcc-page__hero-cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.pcc-page__hero-cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.pcc-page__hero-cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .pcc-page__hero-cta { transition: none; }
}

/* ─── Prose ─── */
.pcc-page__prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 72ch;
}

.pcc-page__paragraph {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.75;
  color: var(--text-secondary);
  margin: 0;
}

.pcc-page__paragraph--lead {
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  color: var(--text-primary);
}

/* ─── Photo grid ─── */
.pcc-page__photo-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.pcc-page__photo-block {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.pcc-page__block-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

@media (min-width: 768px) {
  .pcc-page__photo-grid {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-12);
  }
}

/* ─── Explain ─── */
.pcc-page__explain {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: start;
}

.pcc-page__explain-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.pcc-page__explain-types {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.pcc-page__type-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-5);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--surface-primary);
}

.pcc-page__type-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1rem;
  color: var(--text-primary);
  margin: 0;
}

.pcc-page__type-text {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 900px) {
  .pcc-page__explain {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
  }
}

/* ─── Ton ─── */
.pcc-page__tone {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: start;
}

.pcc-page__tone-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 72ch;
}

.pcc-page__tone-marks {
  display: flex;
  gap: var(--space-4);
  justify-content: center;
  padding: var(--space-8);
}

.pcc-page__tone-mark {
  border-radius: var(--radius-md);
  flex-shrink: 0;
}

.pcc-page__tone-mark--a {
  width: 80px;
  height: 80px;
  background-color: var(--action-primary);
  opacity: 0.35;
  border-radius: 50%;
}

.pcc-page__tone-mark--b {
  width: 56px;
  height: 80px;
  background-color: var(--action-primary);
  opacity: 0.7;
}

@media (min-width: 900px) {
  .pcc-page__tone {
    grid-template-columns: 1fr auto;
    gap: var(--space-16);
  }
}

/* ─── Combo ─── */
.pcc-page__combo {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.pcc-page__combo-item {
  display: flex;
  gap: var(--space-5);
  align-items: flex-start;
}

.pcc-page__combo-num {
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

.pcc-page__combo-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0 0 var(--space-2);
}

.pcc-page__combo-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

/* ─── Integration ─── */
.pcc-page__integration {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.pcc-page__integration-prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 72ch;
}

.pcc-page__integration-ctas {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4);
}

.pcc-page__cta {
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

.pcc-page__cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.pcc-page__cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.pcc-page__cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.pcc-page__cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .pcc-page__cta { transition: none; }
}

/* ─── Budget ─── */
.pcc-page__budget {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
}

.pcc-page__budget-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--surface-primary);
}

.pcc-page__budget-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

.pcc-page__budget-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .pcc-page__budget {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* ─── Réalisation ─── */
.pcc-page__case {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
  align-items: start;
}

.pcc-page__case-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.pcc-page__case-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.pcc-page__case-tag {
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

.pcc-page__case-link {
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--text-accent);
  text-decoration: none;
  align-self: flex-start;
}

.pcc-page__case-link:hover {
  text-decoration: underline;
}

.pcc-page__case-visual {
  border-radius: var(--radius-lg);
  overflow: hidden;
  line-height: 0;
}

.pcc-page__case-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

@media (min-width: 900px) {
  .pcc-page__case {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-12);
  }
}

/* ─── FAQ ─── */
.pcc-page__faq {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin: 0;
}

.pcc-page__faq-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.pcc-page__faq-item:last-child {
  border-bottom: 1px solid var(--border-default);
}

.pcc-page__faq-question {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.3vw, 1.125rem);
  color: var(--text-primary);
  margin: 0;
}

.pcc-page__faq-answer {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.75;
  color: var(--text-secondary);
  margin: 0;
  max-width: 72ch;
}
</style>
