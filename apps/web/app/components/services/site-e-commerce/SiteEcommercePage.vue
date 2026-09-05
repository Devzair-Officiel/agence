<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import { caseStudies } from "~/config/case-studies"
import type { ServicePageDefinition } from "~/config/service-pages"

/**
 * Page dédiée `/services/site-e-commerce`.
 *
 * Rôle : expliquer ce que représente un projet e-commerce réel — du catalogue
 * au tunnel d'achat — sans promettre de résultats commerciaux ni donner de prix.
 *
 * Ce composant NE pose AUCUN SEO ni JSON-LD : ces appels sont faits
 * dans `pages/services/[slug].vue` avant le rendu.
 *
 * Distinction avec les deux autres services construction :
 *   - Création de site internet répond à « présenter mon activité » ;
 *   - cette page répond à « vendre mes produits en ligne » ;
 *   - Application web métier répond à « outiller mes processus internes ».
 */

interface Props {
  page: ServicePageDefinition
}

defineProps<Props>()

const nidemiel = caseStudies.find((cs) => cs.id === "nidemiel")

const faqItems = [
  {
    question: "Quel budget prévoir pour une boutique en ligne ?",
    answer:
      "Le périmètre d'une boutique varie considérablement selon le nombre de produits, les variantes, les règles de livraison et les intégrations nécessaires. Une boutique simple de vingt produits sans variantes ni gestion complexe n'a pas le même coût qu'un catalogue de trois cents références avec plusieurs modes de livraison et un espace client. L'estimateur de projet vous aide à identifier les variables pertinentes à votre situation.",
  },
  {
    question: "Comment gérer mes produits et mes commandes après la mise en ligne ?",
    answer:
      "Une prise en main de l'outil peut être prévue au périmètre pour vous rendre autonome sur les opérations courantes. Selon le projet, cela peut couvrir l'ajout de produits, la mise à jour des stocks, le traitement des commandes et l'édition des contenus. Ce point est discuté et cadré au démarrage.",
  },
  {
    question: "Puis-je utiliser mon identité visuelle actuelle ?",
    answer:
      "Oui, si vous avez déjà une identité établie — logo, palette, typographie — nous l'intégrons dans le design de la boutique. Si votre identité visuelle est en cours de définition ou doit évoluer, nous proposons un accompagnement design en amont, cadrée séparément. Les deux chantiers peuvent aussi se dérouler en parallèle.",
  },
  {
    question: "Devzair peut-il créer les photos produits et les textes ?",
    answer:
      "La photographie et la rédaction peuvent être intégrées au projet si elles sont prévues dès le cadrage. Ce n'est pas inclus par défaut dans une boutique — c'est un poste budgétaire à part, discuté au démarrage. Des contenus produits bien construits ont un impact direct sur la lisibilité de la fiche et sur le référencement.",
  },
  {
    question: "Le référencement est-il prévu dès la construction ?",
    answer:
      "Les bases SEO techniques sont intégrées dans tout projet : URLs propres, balises title et meta adaptées, données structurées produit lorsque pertinentes, architecture de catégories lisible par les moteurs. En revanche, un travail SEO de fond sur les contenus et la popularité dépasse le cadre d'une création et fait l'objet d'un service distinct.",
  },
  {
    question: "Peut-on faire évoluer la boutique après la mise en ligne ?",
    answer:
      "Oui. Une boutique bien construite techniquement peut accueillir de nouveaux produits, de nouvelles catégories, des promotions ou des modes de livraison supplémentaires sans refonte complète. Nous concevons avec cette évolutivité en tête, pas uniquement pour le périmètre initial.",
  },
]
</script>

<template>
  <div class="sec-page">
    <!-- A. Hero -->
    <EditorialHero
      eyebrow="Site e-commerce"
      title="Votre boutique en ligne, construite autour de vos produits et de vos clients."
      lead="Vendre en ligne ne se résume pas à installer un bouton « Ajouter au panier ». Avant la boutique, il y a le catalogue, la logique de présentation, le parcours d'achat et — surtout — ce que vos clients ont besoin de comprendre avant de commander."
    >
      <template #actions>
        <NuxtLink
          to="/contact"
          class="sec-page__hero-cta sec-page__hero-cta--primary"
        >
          Parler de votre projet
        </NuxtLink>
        <NuxtLink
          to="/estimer-mon-projet"
          class="sec-page__hero-cta sec-page__hero-cta--secondary"
        >
          Estimer mon projet
        </NuxtLink>
      </template>
    </EditorialHero>

    <!-- B. Commencer par le commerce -->
    <EditorialSection
      tone="subtle"
      eyebrow="Point de départ"
      title="Vendre en ligne commence par comprendre ce que vous vendez."
      section-id="point-de-depart"
    >
      <div class="sec-page__commerce">
        <div class="sec-page__commerce-block">
          <h3 class="sec-page__commerce-title">Qu'est-ce qui est vendu, et comment ?</h3>
          <p class="sec-page__commerce-text">
            Un catalogue de miels artisanaux, un service de photographie, des
            abonnements mensuels ou des pièces techniques de rechange n'ont pas
            le même parcours d'achat. Avant d'ouvrir un éditeur de code, nous
            posons les questions simples : quels produits, quelles variantes,
            quels prix, quelles contraintes de stock, quelle logique de livraison ?
          </p>
        </div>

        <div class="sec-page__commerce-block">
          <h3 class="sec-page__commerce-title">Ce que le client doit savoir avant d'acheter.</h3>
          <p class="sec-page__commerce-text">
            Selon ce que vous vendez, l'information produit n'a pas la même structure.
            Un vêtement demande taille et couleur. Un aliment appelle la composition
            et l'origine. Une prestation de service demande une description du
            périmètre. La fiche produit n'est pas un gabarit générique — elle se
            construit autour de ce que votre client doit comprendre pour décider.
          </p>
        </div>

        <div class="sec-page__commerce-block">
          <h3 class="sec-page__commerce-title">Un catalogue qui peut évoluer.</h3>
          <p class="sec-page__commerce-text">
            Vous n'avez peut-être pas tous vos produits finalisés au moment du
            lancement. C'est normal. Ce qui compte, c'est que la structure du
            catalogue soit pensée pour accueillir de nouvelles références sans
            reprendre l'architecture à zéro six mois plus tard.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- C. Catalogue -->
    <EditorialSection
      tone="default"
      eyebrow="Catalogue"
      title="Des produits organisés pour que vos clients s'y retrouvent."
      intro="Un catalogue bien structuré aide le visiteur à trouver ce qu'il cherche et à comprendre ce qui distingue un produit d'un autre."
      section-id="catalogue"
    >
      <div class="sec-page__catalogue-grid">
        <div class="sec-page__catalogue-item">
          <span class="sec-page__catalogue-label">Catégories et navigation</span>
          <p class="sec-page__catalogue-text">
            L'architecture des catégories conditionne la clarté du catalogue et
            son référencement. Une hiérarchie trop plate noie les produits ;
            une hiérarchie trop profonde décourage la navigation. Nous la
            construisons selon la logique de votre activité.
          </p>
        </div>
        <div class="sec-page__catalogue-item">
          <span class="sec-page__catalogue-label">Fiches produit</span>
          <p class="sec-page__catalogue-text">
            Titre, description, photographie, caractéristiques, variantes, prix,
            disponibilité — chaque élément de la fiche a une fonction. La
            présentation est pensée pour répondre aux questions avant qu'elles
            soient posées.
          </p>
        </div>
        <div class="sec-page__catalogue-item">
          <span class="sec-page__catalogue-label">Variantes et options</span>
          <p class="sec-page__catalogue-text">
            Tailles, couleurs, formats, configurations — lorsque vos produits
            existent en plusieurs versions, l'interface doit présenter les
            options clairement sans surcharger la page d'informations techniques.
          </p>
        </div>
        <div class="sec-page__catalogue-item">
          <span class="sec-page__catalogue-label">Photographie produit</span>
          <p class="sec-page__catalogue-text">
            Une boutique dont les images sont de qualité inégale ou mal cadrées
            nuit à la crédibilité du produit. La production photo peut être
            intégrée au projet — ou nous travaillons avec les visuels que vous
            fournissez.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- D. Parcours d'achat -->
    <EditorialSection
      tone="subtle"
      eyebrow="Parcours d'achat"
      title="De la fiche produit à la confirmation de commande."
      intro="Un tunnel d'achat simplifié ne garantit pas un taux de conversion élevé — mais un tunnel inutilement long ou confus peut contribuer à des abandons évitables."
      section-id="parcours"
    >
      <ol class="sec-page__tunnel" role="list">
        <li class="sec-page__tunnel-step">
          <span class="sec-page__tunnel-num" aria-hidden="true">01</span>
          <div class="sec-page__tunnel-body">
            <h3 class="sec-page__tunnel-title">Découverte et sélection</h3>
            <p class="sec-page__tunnel-text">
              Moteur de recherche interne, filtres, recommandations ou navigation
              par catégorie — selon ce qui correspond le mieux à votre catalogue
              et à la façon dont vos clients cherchent.
            </p>
          </div>
        </li>
        <li class="sec-page__tunnel-step">
          <span class="sec-page__tunnel-num" aria-hidden="true">02</span>
          <div class="sec-page__tunnel-body">
            <h3 class="sec-page__tunnel-title">Fiche produit et ajout au panier</h3>
            <p class="sec-page__tunnel-text">
              L'étape où le visiteur décide d'acheter ou non. La lisibilité
              des informations produit, la qualité des visuels et la clarté
              des options disponibles jouent ici directement.
            </p>
          </div>
        </li>
        <li class="sec-page__tunnel-step">
          <span class="sec-page__tunnel-num" aria-hidden="true">03</span>
          <div class="sec-page__tunnel-body">
            <h3 class="sec-page__tunnel-title">Panier et options de livraison</h3>
            <p class="sec-page__tunnel-text">
              Récapitulatif clair, modes de livraison affichés avec leurs délais
              et leurs coûts, possibilité de modifier la commande sans perdre
              ce qui a déjà été saisi.
            </p>
          </div>
        </li>
        <li class="sec-page__tunnel-step">
          <span class="sec-page__tunnel-num" aria-hidden="true">04</span>
          <div class="sec-page__tunnel-body">
            <h3 class="sec-page__tunnel-title">Paiement sécurisé</h3>
            <p class="sec-page__tunnel-text">
              Intégration d'une solution de paiement adaptée au volume et au
              contexte du projet. Le choix du prestataire de paiement dépend
              des frais de transaction, des moyens de paiement attendus et
              des contraintes juridiques éventuelles.
            </p>
          </div>
        </li>
        <li class="sec-page__tunnel-step">
          <span class="sec-page__tunnel-num" aria-hidden="true">05</span>
          <div class="sec-page__tunnel-body">
            <h3 class="sec-page__tunnel-title">Confirmation et suivi</h3>
            <p class="sec-page__tunnel-text">
              Page de confirmation, e-mail récapitulatif, accès au suivi de
              commande — selon le périmètre retenu. Un client informé de
              l'état de sa commande a moins de raisons de contacter le support.
            </p>
          </div>
        </li>
      </ol>
    </EditorialSection>

    <!-- E. Design -->
    <EditorialSection
      tone="default"
      eyebrow="Design"
      title="Une boutique de miel n'a pas l'aspect d'un distributeur industriel."
      section-id="design"
    >
      <div class="sec-page__design-split">
        <div class="sec-page__design-text">
          <p class="sec-page__paragraph">
            Le design d'une boutique en ligne n'est pas un habillage qu'on pose
            sur une structure générique. Il conditionne la façon dont vos produits
            sont perçus — leur qualité perçue, leur prix justifié, leur désirabilité.
            Une boutique d'artisan alimentaire et un distributeur de pièces techniques
            n'ont aucune raison de se ressembler.
          </p>
          <p class="sec-page__paragraph">
            Nous concevons à partir de ce que vous vendez, de qui vous l'achetez
            et du positionnement que vous souhaitez occuper. La typographie, la
            densité des pages, la façon dont les photos respirent, la hiérarchie
            des informations sur la fiche — tout participe à l'impression finale.
          </p>
          <p class="sec-page__paragraph">
            Si vous n'avez pas encore d'identité visuelle établie,
            <NuxtLink to="/services/design-ui-ux-identite-visuelle" class="sec-page__inline-link">
              un accompagnement design et identité visuelle
            </NuxtLink>
            peut être prévu en amont du développement de la boutique.
          </p>
        </div>
        <div class="sec-page__design-marks" aria-hidden="true">
          <div class="sec-page__design-mark sec-page__design-mark--a" />
          <div class="sec-page__design-mark sec-page__design-mark--b" />
          <div class="sec-page__design-mark sec-page__design-mark--c" />
          <div class="sec-page__design-mark sec-page__design-mark--d" />
        </div>
      </div>
    </EditorialSection>

    <!-- F. Périmètre -->
    <EditorialSection
      tone="subtle"
      eyebrow="Périmètre de la mission"
      title="Ce que peut comprendre le projet."
      intro="Selon le besoin et le contexte, un projet e-commerce peut couvrir tout ou partie des éléments suivants. Ce qui est inclus est toujours défini au cadrage — pas en cours de développement."
      section-id="perimetre"
    >
      <div class="sec-page__scope">
        <ul class="sec-page__scope-list" role="list">
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Architecture du catalogue et structure de navigation</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Design UI/UX personnalisé (pages catalogue, fiche produit, panier, tunnel)</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Développement et intégration des gabarits</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Intégration du catalogue initial (selon fourniture client)</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Intégration d'une solution de paiement</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Gestion des modes de livraison (selon projet)</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Espace client et historique des commandes (selon projet)</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Interface d'administration : produits, commandes, stock</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Photographie produit (si intégré au projet)</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Rédaction des fiches produit (si intégré au projet)</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>SEO technique de base et données structurées produit</span>
          </li>
          <li class="sec-page__scope-item">
            <span class="sec-page__scope-check" aria-hidden="true">—</span>
            <span>Mise en ligne, configuration du domaine et formation</span>
          </li>
        </ul>
        <p class="sec-page__scope-note">
          Cette liste n'est pas un engagement sur chaque élément. Ce qui est
          retenu est explicitement cadrée avant le démarrage.
        </p>
      </div>
    </EditorialSection>

    <!-- G. Administration -->
    <EditorialSection
      tone="default"
      eyebrow="Administration"
      title="Une boutique doit être praticable côté entreprise aussi."
      section-id="administration"
    >
      <div class="sec-page__admin-content">
        <p class="sec-page__paragraph">
          Le soin apporté à l'expérience d'achat doit se retrouver côté
          administration. Ajouter un produit, modifier un prix, suivre une
          commande, ajuster un stock — ces tâches doivent rester simples,
          même pour quelqu'un qui n'est pas développeur.
        </p>
        <p class="sec-page__paragraph">
          Selon le périmètre retenu, l'interface de gestion peut couvrir :
          l'édition du catalogue, le traitement des commandes par statut,
          les alertes de stock bas, la gestion des codes promotionnels et
          l'accès aux données clients conformément au RGPD. Nous préférons
          un outil d'administration bien calibré à une surcharge de fonctionnalités
          que personne n'utilise.
        </p>
        <p class="sec-page__paragraph">
          Une prise en main de l'outil peut être prévue au périmètre. L'objectif
          est que vous puissiez gérer les opérations courantes sans dépendre de
          l'agence pour chaque modification.
        </p>
      </div>
    </EditorialSection>

    <!-- H. SEO e-commerce -->
    <EditorialSection
      tone="subtle"
      eyebrow="Référencement"
      title="Les bases SEO d'une boutique en ligne."
      section-id="seo"
    >
      <div class="sec-page__seo-split">
        <div>
          <h3 class="sec-page__seo-subtitle">Ce que nous visons à intégrer par défaut</h3>
          <ul class="sec-page__seo-list" role="list">
            <li>URLs propres pour chaque catégorie et chaque produit</li>
            <li>Balises title et meta description adaptées au produit</li>
            <li>Données structurées Schema.org pour les produits (lorsque pertinentes)</li>
            <li>Architecture de catégories lisible par les moteurs</li>
            <li>Performances de chargement et images optimisées</li>
            <li>Sitemap XML incluant les produits et catégories</li>
          </ul>
        </div>
        <div>
          <h3 class="sec-page__seo-subtitle">Ce que le SEO technique ne remplace pas</h3>
          <p class="sec-page__paragraph">
            Un catalogue bien structuré et des fiches produit détaillées
            contribuent naturellement au référencement. En revanche, une
            stratégie de contenu longue durée — guides d'achat, articles
            de blog, contenus saisonniers — relève d'un chantier distinct.
            Nous l'évoquons au cadrage si cela correspond à vos objectifs.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- I. Budget -->
    <EditorialSection
      tone="default"
      eyebrow="Budget"
      title="Pourquoi deux boutiques ne coûtent pas la même chose."
      section-id="budget"
    >
      <div class="sec-page__budget">
        <p class="sec-page__paragraph sec-page__paragraph--lead">
          Le périmètre d'une boutique est déterminé par des variables concrètes,
          pas par un tarif catalogue. Les plus structurantes sont les suivantes.
        </p>
        <div class="sec-page__budget-factors">
          <div class="sec-page__budget-factor">
            <h3 class="sec-page__budget-factor-title">Volume et complexité du catalogue</h3>
            <p class="sec-page__budget-factor-text">
              Vingt produits simples à un prix fixe ne nécessitent pas les
              mêmes outils qu'un catalogue de cinq cents références avec
              variantes, règles de prix et gestion de stock en temps réel.
            </p>
          </div>
          <div class="sec-page__budget-factor">
            <h3 class="sec-page__budget-factor-title">Modes de livraison</h3>
            <p class="sec-page__budget-factor-text">
              Livraison à tarif fixe, règles par zone, transporteurs multiples,
              retrait en boutique — chaque configuration ajoute un périmètre
              de développement et de tests.
            </p>
          </div>
          <div class="sec-page__budget-factor">
            <h3 class="sec-page__budget-factor-title">Compte client et historique</h3>
            <p class="sec-page__budget-factor-text">
              Une boutique sans espace client est plus simple à développer.
              L'ajout d'un espace client, d'un historique des commandes et
              d'une gestion des retours représente un périmètre supplémentaire.
            </p>
          </div>
          <div class="sec-page__budget-factor">
            <h3 class="sec-page__budget-factor-title">Contenu et photographie</h3>
            <p class="sec-page__budget-factor-text">
              Si la production de photos produits et la rédaction des fiches
              sont déléguées à Devzair, cela s'ajoute au périmètre technique.
              Des contenus soignés contribuent à la lisibilité des fiches et
              à leur référencement.
            </p>
          </div>
        </div>
        <div class="sec-page__budget-cta">
          <p class="sec-page__budget-cta-text">
            L'estimateur de projet vous donne une fourchette budgétaire
            adaptée à votre contexte en quelques minutes, sans engagement.
          </p>
          <NuxtLink to="/estimer-mon-projet" class="sec-page__estimator-link">
            Estimer mon projet →
          </NuxtLink>
        </div>
      </div>
    </EditorialSection>

    <!-- J. Réalisation Nidemiel -->
    <EditorialSection
      v-if="nidemiel && nidemiel.status === 'published'"
      tone="subtle"
      eyebrow="Réalisation"
      title="Nidemiel — boutique en ligne pour un artisan miellier."
      section-id="realisation"
    >
      <div class="sec-page__case">
        <div class="sec-page__case-visual">
          <img
            :src="nidemiel.imageSrc"
            :alt="nidemiel.imageAlt"
            class="sec-page__case-img"
            width="960"
            height="600"
            loading="lazy"
            decoding="async"
          >
        </div>
        <div class="sec-page__case-content">
          <p class="sec-page__case-category">{{ nidemiel.category }}</p>
          <p class="sec-page__paragraph">
            {{ nidemiel.longDescription }}
          </p>
          <ul class="sec-page__case-tags" aria-label="Interventions">
            <li
              v-for="tag in nidemiel.tags"
              :key="tag"
              class="sec-page__case-tag"
            >
              {{ tag }}
            </li>
          </ul>
          <NuxtLink
            :to="nidemiel.route"
            class="sec-page__case-link"
          >
            Voir l'étude de cas complète →
          </NuxtLink>
        </div>
      </div>
    </EditorialSection>

    <!-- K. FAQ -->
    <EditorialSection
      tone="default"
      eyebrow="Questions fréquentes"
      title="Ce que nos clients demandent avant de démarrer une boutique."
      section-id="faq"
    >
      <dl class="sec-page__faq">
        <div
          v-for="item in faqItems"
          :key="item.question"
          class="sec-page__faq-item"
        >
          <dt class="sec-page__faq-question">{{ item.question }}</dt>
          <dd class="sec-page__faq-answer">{{ item.answer }}</dd>
        </div>
      </dl>
    </EditorialSection>

    <!-- L. CTA final -->
    <EditorialCallout
      eyebrow="Votre projet e-commerce"
      title="Un projet de boutique en ligne à concevoir ?"
      description="Racontez-nous ce que vous vendez, à qui et dans quel contexte. Un échange court suffit pour évaluer le périmètre utile et ce qui peut attendre."
      :primary="{ label: 'Parler de votre projet', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />
  </div>
</template>

<style scoped>
.sec-page {
  display: flex;
  flex-direction: column;
}

/* ── Hero CTAs ────────────────────────────────────────────────── */

.sec-page__hero-cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-3) var(--space-5);
  border-radius: var(--radius-md);
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 600;
  line-height: 1.2;
  text-decoration: none;
  transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}

.sec-page__hero-cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.sec-page__hero-cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.sec-page__hero-cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.sec-page__hero-cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

/* ── Texte courant ────────────────────────────────────────────── */

.sec-page__paragraph {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

.sec-page__paragraph--lead {
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  color: var(--text-primary);
}

/* ── Lien inline ──────────────────────────────────────────────── */

.sec-page__inline-link {
  color: var(--color-petrol);
  text-decoration: underline;
  text-underline-offset: 2px;
  transition: color 0.12s ease;
}

.sec-page__inline-link:hover {
  color: var(--color-navy);
}

/* ── B. Commerce ──────────────────────────────────────────────── */

.sec-page__commerce {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.sec-page__commerce-block {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-left: var(--space-4);
  border-left: 2px solid var(--color-petrol);
}

.sec-page__commerce-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.6vw, 1.125rem);
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
}

.sec-page__commerce-text {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

/* ── C. Catalogue ─────────────────────────────────────────────── */

.sec-page__catalogue-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
  border-top: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.sec-page__catalogue-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.sec-page__catalogue-label {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.sec-page__catalogue-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 58ch;
}

/* ── D. Tunnel ────────────────────────────────────────────────── */

.sec-page__tunnel {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0;
}

.sec-page__tunnel-step {
  display: grid;
  grid-template-columns: 3rem 1fr;
  gap: var(--space-5);
  padding: var(--space-6) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.sec-page__tunnel-step:last-child {
  border-bottom: none;
}

.sec-page__tunnel-num {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  color: var(--color-petrol);
  padding-top: 0.15em;
}

.sec-page__tunnel-body {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.sec-page__tunnel-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(0.9375rem, 1.4vw, 1.0625rem);
  line-height: 1.25;
  color: var(--text-primary);
  margin: 0;
}

.sec-page__tunnel-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

/* ── E. Design ────────────────────────────────────────────────── */

.sec-page__design-split {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.sec-page__design-text {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.sec-page__design-marks {
  display: none;
}

/* ── F. Périmètre ─────────────────────────────────────────────── */

.sec-page__scope {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.sec-page__scope-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
}

.sec-page__scope-item {
  display: flex;
  gap: var(--space-3);
  align-items: baseline;
  padding: var(--space-3) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.5;
  color: var(--text-secondary);
}

.sec-page__scope-check {
  flex-shrink: 0;
  color: var(--color-petrol);
  font-weight: 700;
  font-family: var(--font-family-mono);
}

.sec-page__scope-note {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.6;
  color: var(--text-muted);
  margin: 0;
  padding: var(--space-4);
  background-color: var(--background-secondary);
  border-radius: var(--radius-md);
  border-left: 2px solid rgba(12, 91, 87, 0.2);
}

/* ── G. Administration ────────────────────────────────────────── */

.sec-page__admin-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 68ch;
}

/* ── H. SEO ───────────────────────────────────────────────────── */

.sec-page__seo-split {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.sec-page__seo-subtitle {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(0.9375rem, 1.3vw, 1rem);
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0 0 var(--space-4);
}

.sec-page__seo-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.sec-page__seo-list li {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.5;
  color: var(--text-secondary);
  padding-left: var(--space-5);
  position: relative;
}

.sec-page__seo-list li::before {
  content: "✓";
  position: absolute;
  left: 0;
  color: var(--color-petrol);
  font-weight: 700;
}

/* ── I. Budget ────────────────────────────────────────────────── */

.sec-page__budget {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.sec-page__budget-factors {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
  border-top: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.sec-page__budget-factor {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-5) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.sec-page__budget-factor-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 0.9375rem;
  color: var(--text-primary);
  margin: 0;
}

.sec-page__budget-factor-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.sec-page__budget-cta {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-5);
  background-color: var(--background-secondary);
  border-radius: var(--radius-md);
  border-left: 2px solid var(--color-petrol);
}

.sec-page__budget-cta-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.sec-page__estimator-link {
  display: inline-flex;
  align-self: flex-start;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-petrol);
  text-decoration: none;
  transition: color 0.12s ease;
}

.sec-page__estimator-link:hover {
  color: var(--color-navy);
}

/* ── J. Réalisation Nidemiel ──────────────────────────────────── */

.sec-page__case {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
  align-items: start;
}

.sec-page__case-visual {
  border-radius: var(--radius-lg);
  overflow: hidden;
  line-height: 0;
}

.sec-page__case-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

.sec-page__case-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.sec-page__case-category {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.6875rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.sec-page__case-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.sec-page__case-tag {
  font-family: var(--font-family-mono);
  font-size: 0.625rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text-accent);
  padding: 3px 8px;
  border: 1px solid rgba(12, 91, 87, 0.3);
  border-radius: var(--radius-pill);
  background-color: rgba(12, 91, 87, 0.05);
}

.sec-page__case-link {
  display: inline-flex;
  align-self: flex-start;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-petrol);
  text-decoration: none;
  border-bottom: 1px solid currentColor;
  padding-bottom: 2px;
  transition: color 0.12s ease;
}

.sec-page__case-link:hover {
  color: var(--color-navy);
}

/* ── K. FAQ ───────────────────────────────────────────────────── */

.sec-page__faq {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin: 0;
}

.sec-page__faq-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-5) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.sec-page__faq-item:last-child {
  border-bottom: none;
}

.sec-page__faq-question {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(0.9375rem, 1.3vw, 1.0625rem);
  line-height: 1.3;
  color: var(--text-primary);
}

.sec-page__faq-answer {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 66ch;
}

/* ── Responsive ───────────────────────────────────────────────── */

@media (min-width: 640px) {
  .sec-page__scope-list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: var(--space-8);
  }
}

@media (min-width: 768px) {
  .sec-page__commerce {
    gap: var(--space-10);
  }

  .sec-page__catalogue-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: var(--space-10);
  }

  .sec-page__budget-factors {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: var(--space-8);
  }
}

@media (min-width: 900px) {
  .sec-page__design-split {
    grid-template-columns: minmax(0, 3fr) minmax(0, 1fr);
    gap: var(--space-12);
    align-items: start;
  }

  .sec-page__design-marks {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    padding-top: var(--space-2);
  }

  .sec-page__design-mark {
    border-radius: var(--radius-sm);
    height: 3px;
  }

  .sec-page__design-mark--a {
    width: 100%;
    background-color: var(--color-petrol);
    opacity: 0.7;
  }

  .sec-page__design-mark--b {
    width: 65%;
    background-color: var(--color-navy);
    opacity: 0.4;
  }

  .sec-page__design-mark--c {
    width: 40%;
    background-color: var(--color-petrol);
    opacity: 0.25;
  }

  .sec-page__design-mark--d {
    width: 20%;
    background-color: var(--color-navy);
    opacity: 0.15;
  }

  .sec-page__case {
    grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
    gap: var(--space-12);
  }

  .sec-page__seo-split {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (prefers-reduced-motion: reduce) {
  .sec-page__hero-cta,
  .sec-page__inline-link,
  .sec-page__estimator-link,
  .sec-page__case-link {
    transition: none;
  }
}
</style>
