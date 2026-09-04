<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import { caseStudies } from "~/config/case-studies"
import type { ServicePageDefinition } from "~/config/service-pages"

/**
 * Page dédiée `/services/creation-site-internet`.
 *
 * Rôle : présenter le service de création de site internet de façon
 * éditoriale, honnête et orientée besoin client — sans superlatif,
 * sans chiffre inventé, sans promesse SEO non vérifiable.
 *
 * Ce composant NE pose AUCUN SEO ni JSON-LD : ces appels sont faits
 * dans `pages/services/[slug].vue` avant le rendu.
 *
 * Distinction avec `/expertises/construire` :
 *   - Construire répond à « comment Devzair fabrique-t-il ? » (méthode,
 *     stack, process de fabrication) ;
 *   - cette page répond à « de quoi mon projet a-t-il besoin ? » (contexte
 *     client, budget, périmètre adapté, réalisations vitrines).
 */

interface Props {
  page: ServicePageDefinition
}

defineProps<Props>()

const vitrineCaseStudies = caseStudies.filter((cs) =>
  ["kitchen-meat", "haramain-prestige", "al-mumayiz"].includes(cs.id),
)

const faqItems = [
  {
    question: "Combien coûte la création d'un site internet professionnel ?",
    answer:
      "Le prix varie selon le nombre de pages, la complexité du design, l'intégration d'un CMS et la production de contenu. Un site vitrine de cinq à dix pages se situe généralement entre 1 500 et 5 000 € selon ces paramètres. L'estimateur de projet vous donne une fourchette adaptée à votre contexte en quelques minutes, sans engagement.",
  },
  {
    question: "Combien de temps faut-il pour créer un site ?",
    answer:
      "Entre quatre et dix semaines selon le périmètre et la disponibilité des contenus. Le délai se rallonge souvent côté textes et photos — pas côté développement. Un brief clair et des contenus prêts font gagner deux à trois semaines sur la plupart des projets.",
  },
  {
    question: "Est-ce que je pourrai modifier le contenu du site moi-même ?",
    answer:
      "Si un système de gestion de contenu (CMS) est retenu lors du cadrage, oui. Nous formons les clients à son utilisation à la livraison. Pour les sites légers ou très stables dans le temps, un CMS n'est pas toujours justifié — nous explicitons les avantages et contraintes de chaque option avant de trancher.",
  },
  {
    question: "Dois-je fournir les textes et les photos moi-même ?",
    answer:
      "Les textes que vous rédigez décrivent ce que vous faites mieux que n'importe qui. Ce que nous faisons, c'est les adapter à une structure web lisible. Si vous ne pouvez pas les produire, nous proposons un service de rédaction et de photographie à part — ce n'est pas inclus par défaut dans la création de site.",
  },
  {
    question: "Est-ce que vous vous occupez de l'hébergement et du nom de domaine ?",
    answer:
      "Oui, si vous souhaitez déléguer ces aspects. Nous pouvons prendre en charge le nom de domaine, la configuration DNS et l'hébergement, ou intervenir sur ceux que vous avez déjà. Les coûts d'hébergement annuels sont séparés du devis de création.",
  },
  {
    question: "Est-ce qu'un site vitrine suffit ou ai-je besoin d'un e-commerce ?",
    answer:
      "Un site vitrine présente votre activité et génère des contacts — il ne vend pas directement en ligne. Si vous avez des produits à commander et à payer en ligne, un e-commerce est plus adapté. Si vous ne savez pas encore, l'estimateur de projet peut vous aider à clarifier le bon type de site pour votre situation.",
  },
]
</script>

<template>
  <div class="csi-page">
    <!-- A. Hero -->
    <EditorialHero
      eyebrow="Création de site internet"
      title="Un site internet professionnel, pensé pour votre activité et votre image."
      lead="Avant la maquette et le développement, il y a une question simple : à quoi sert ce site ? Pour un artisan, c'est souvent convaincre et décrocher un contact. Pour un cabinet, c'est asseoir une crédibilité. La réponse détermine la structure, les pages, les contenus — et le budget."
    >
      <template #actions>
        <NuxtLink
          to="/estimer-mon-projet"
          class="csi-page__hero-cta csi-page__hero-cta--primary"
        >
          Estimer mon projet
        </NuxtLink>
        <NuxtLink
          to="/contact"
          class="csi-page__hero-cta csi-page__hero-cta--secondary"
        >
          Parler de votre projet
        </NuxtLink>
      </template>
    </EditorialHero>

    <!-- B. Situations concrètes -->
    <EditorialSection
      tone="subtle"
      eyebrow="Quelques situations concrètes"
      title="Un site fait la différence dans ces contextes."
      section-id="situations"
    >
      <div class="csi-page__situations">
        <div class="csi-page__situation-block">
          <h3 class="csi-page__situation-title">
            Vos clients potentiels vous cherchent en ligne — et ne vous trouvent pas.
          </h3>
          <p class="csi-page__situation-text">
            Les annuaires et les réseaux sociaux ne remplacent pas un site. Un client qui tape le nom de votre activité sur Google et ne trouve rien d'officiel vous compare immédiatement à un concurrent mieux référencé. L'absence en ligne n'est pas neutre.
          </p>
        </div>

        <div class="csi-page__situation-block">
          <h3 class="csi-page__situation-title">
            Votre site actuel ne reflète plus ce que vous faites réellement.
          </h3>
          <p class="csi-page__situation-text">
            Un site vieilli, confus ou mal adapté au mobile nuit à la première impression. C'est souvent pire qu'une absence, car il communique un message que vous n'avez pas validé. La refonte est alors moins un luxe qu'un besoin commercial.
          </p>
        </div>

        <div class="csi-page__situation-block">
          <h3 class="csi-page__situation-title">
            Vous préparez le lancement d'une activité ou d'une nouvelle offre.
          </h3>
          <p class="csi-page__situation-text">
            Poser une base solide dès le départ — structure claire, SEO de base intégré, contenus pensés pour votre cible — coûte moins cher que refaire dans six mois ce qui a été fait en urgence. La précipitation au lancement se paie presque toujours plus tard.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- C. Périmètre adapté -->
    <EditorialSection
      tone="default"
      eyebrow="Périmètre adapté"
      title="Un artisan et une PME n'ont pas nécessairement le même site."
      intro="La question n'est pas la taille de l'entreprise, mais ce que le site doit accomplir. Un site de cinq pages peut parfaitement servir une activité bien définie. Un site de quarante pages est parfois insuffisant quand la structure est mal pensée."
      section-id="perimetre"
    >
      <div class="csi-page__scope-grid">
        <div class="csi-page__scope-card">
          <h3 class="csi-page__scope-title">Site vitrine simple</h3>
          <p class="csi-page__scope-description">
            Accueil, présentation de l'activité, page de contact — parfois une page de portfolio ou de références. Adapté aux indépendants, artisans et petites structures dont l'offre est lisible et stable.
          </p>
          <ul class="csi-page__scope-list" role="list">
            <li>5 à 8 pages</li>
            <li>Formulaire de contact</li>
            <li>SEO de base intégré</li>
            <li>CMS optionnel</li>
          </ul>
        </div>

        <div class="csi-page__scope-card">
          <h3 class="csi-page__scope-title">Site institutionnel</h3>
          <p class="csi-page__scope-description">
            Plusieurs services ou pôles d'activité, page équipe, témoignages structurés, espace ressources ou blog. Adapté aux PME, cabinets et structures qui ont plusieurs cibles ou plusieurs niveaux d'information à servir.
          </p>
          <ul class="csi-page__scope-list" role="list">
            <li>10 à 40 pages</li>
            <li>CMS de contenu</li>
            <li>Architecture de navigation</li>
            <li>SEO sémantique travaillé</li>
          </ul>
        </div>
      </div>
    </EditorialSection>

    <!-- D. Design sur mesure (AJOUT IMPORTANT) -->
    <EditorialSection
      tone="subtle"
      eyebrow="Design sur mesure"
      title="Votre site ne devrait pas ressembler à celui de vos concurrents."
      section-id="design"
    >
      <div class="csi-page__design-content">
        <div class="csi-page__design-text">
          <p class="csi-page__paragraph">
            Les templates génériques sont reconnaissables. En quelques secondes, un visiteur identifie inconsciemment le thème WordPress ou Webflow utilisé, et l'image de marque devient indifférenciable de celle du voisin de secteur. Ce n'est pas une question esthétique : c'est une question de crédibilité perçue.
          </p>
          <p class="csi-page__paragraph">
            Nous ne produisons pas de sites basés sur des thèmes génériques. Chaque projet part de votre activité, de votre cible et — quand elle existe — de votre identité visuelle. La typographie, la palette, le ton éditorial et la façon dont les sections s'enchaînent sont pensés pour vous, pas pour une catégorie abstraite de clients.
          </p>
          <p class="csi-page__paragraph">
            Si vous n'avez pas encore d'identité visuelle établie, nous pouvons intégrer ce travail en amont du développement dans le cadre de notre service de <NuxtLink to="/services/design-ui-ux-identite-visuelle" class="csi-page__inline-link">design et identité visuelle</NuxtLink>. Les deux chantiers — design et développement — sont naturellement liés : un site réussi est une interface cohérente, pas une pile de composants assemblés.
          </p>
        </div>
        <div class="csi-page__design-accent" aria-hidden="true">
          <div class="csi-page__design-mark csi-page__design-mark--a" />
          <div class="csi-page__design-mark csi-page__design-mark--b" />
          <div class="csi-page__design-mark csi-page__design-mark--c" />
        </div>
      </div>
    </EditorialSection>

    <!-- E. Ce que comprend le projet -->
    <EditorialSection
      tone="default"
      eyebrow="Périmètre de la mission"
      title="Ce que comprend le projet."
      intro="Un projet de site internet passe par plusieurs phases successives. Chaque étape est explicitement cadrée au démarrage — pas de surprise en cours de route."
      section-id="perimetre-mission"
    >
      <ol class="csi-page__steps" role="list">
        <li class="csi-page__step">
          <span class="csi-page__step-number" aria-hidden="true">01</span>
          <div class="csi-page__step-content">
            <h3 class="csi-page__step-title">Cadrage et architecture</h3>
            <p class="csi-page__step-description">
              Un échange structuré pour comprendre votre activité, vos cibles et vos objectifs. On en ressort avec un plan de site, une liste de pages et un périmètre validé — avant de dessiner quoi que ce soit.
            </p>
          </div>
        </li>

        <li class="csi-page__step">
          <span class="csi-page__step-number" aria-hidden="true">02</span>
          <div class="csi-page__step-content">
            <h3 class="csi-page__step-title">Design et maquettes</h3>
            <p class="csi-page__step-description">
              Conception des maquettes de chaque gabarit de page (desktop et mobile). Deux allers-retours de révision sont inclus avant validation finale. Le design est livré en lien partagé pour annotation collaborative.
            </p>
          </div>
        </li>

        <li class="csi-page__step">
          <span class="csi-page__step-number" aria-hidden="true">03</span>
          <div class="csi-page__step-content">
            <h3 class="csi-page__step-title">Développement</h3>
            <p class="csi-page__step-description">
              Intégration des maquettes en HTML/CSS, avec ou sans CMS selon les besoins définis au cadrage. Performances, accessibilité (WCAG AA) et compatibilité multi-navigateur sont inclus dans la livraison — pas des options.
            </p>
          </div>
        </li>

        <li class="csi-page__step">
          <span class="csi-page__step-number" aria-hidden="true">04</span>
          <div class="csi-page__step-content">
            <h3 class="csi-page__step-title">Intégration du contenu</h3>
            <p class="csi-page__step-description">
              Mise en place des textes et des photos dans le site. Si vous avez fourni des contenus validés, nous les intégrons. Si leur production est prévue dans le projet (rédaction, photographie), elle est réalisée en parallèle du développement.
            </p>
          </div>
        </li>

        <li class="csi-page__step">
          <span class="csi-page__step-number" aria-hidden="true">05</span>
          <div class="csi-page__step-content">
            <h3 class="csi-page__step-title">Mise en ligne et formation</h3>
            <p class="csi-page__step-description">
              Déploiement sur l'hébergement définitif, configuration du nom de domaine et des DNS, vérification finale en conditions réelles. Si le site est doté d'un CMS, une session de formation à son usage est incluse.
            </p>
          </div>
        </li>
      </ol>
    </EditorialSection>

    <!-- F. Contenu -->
    <EditorialSection
      tone="subtle"
      eyebrow="Contenu"
      title="Un site techniquement réussi reste muet sans contenus adaptés."
      section-id="contenu"
    >
      <div class="csi-page__content-grid">
        <div class="csi-page__content-block">
          <p class="csi-page__paragraph">
            Un site visuellement soigné qui dit peu ou mal ne convainc personne. Les textes ont besoin de décrire précisément ce que vous faites, pour qui et pourquoi. Les photos ont besoin de montrer un vrai travail, une vraie équipe, un vrai lieu — pas des images de stock génériques.
          </p>
          <p class="csi-page__paragraph">
            Dans la pratique, les textes que nos clients rédigent eux-mêmes — même bruts — sont souvent meilleurs que ceux produits sans les connaître. Ce que nous apportons, c'est la structure : hiérarchie éditoriale, longueur par section, formulation orientée vers le lecteur.
          </p>
        </div>
        <div class="csi-page__content-block">
          <p class="csi-page__paragraph">
            Si vous ne pouvez pas produire textes et photos en interne, notre service de <NuxtLink to="/services/photographie-creation-contenu" class="csi-page__inline-link">photographie et contenu web</NuxtLink> peut être ajouté au projet. Cette étape est optionnelle et cadrée séparément — ce n'est jamais une obligation, c'est un choix éclairé.
          </p>
          <p class="csi-page__paragraph">
            Un site livré sans contenu finalisé n'est pas un site terminé. Prévoir le délai de production éditoriale dans le planning du projet, c'est la principale façon d'éviter un retard à la mise en ligne.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- G. SEO de base -->
    <EditorialSection
      tone="default"
      eyebrow="Référencement"
      title="Les bases du référencement travaillées dès le départ."
      section-id="seo"
    >
      <div class="csi-page__seo-content">
        <div class="csi-page__seo-included">
          <h3 class="csi-page__seo-subtitle">Ce qui est inclus dans tout projet de site</h3>
          <ul class="csi-page__seo-list" role="list">
            <li class="csi-page__seo-item">Structure de pages avec balises title et meta description adaptées</li>
            <li class="csi-page__seo-item">Hiérarchie de titres cohérente (H1 unique, H2 pour les sections principales)</li>
            <li class="csi-page__seo-item">Sitemap XML et fichier robots.txt configurés</li>
            <li class="csi-page__seo-item">URLs propres, canoniques et sans duplication</li>
            <li class="csi-page__seo-item">Performances de chargement optimisées (images, polices, code)</li>
            <li class="csi-page__seo-item">Données structurées Schema.org basiques (organisation, fil d'Ariane)</li>
          </ul>
        </div>
        <div class="csi-page__seo-honest">
          <h3 class="csi-page__seo-subtitle">Ce que le SEO de base ne fait pas</h3>
          <p class="csi-page__paragraph">
            La création d'un site ne déclenche pas automatiquement une montée dans les résultats de recherche. Le référencement naturel est un travail de fond qui prend plusieurs mois et qui s'appuie sur des contenus régulièrement enrichis, des liens entrants et une autorité sectorielle construite dans le temps.
          </p>
          <p class="csi-page__paragraph">
            Si votre objectif est de générer du trafic organique qualifié à moyen terme, notre service de <NuxtLink to="/services/seo-referencement-naturel" class="csi-page__inline-link">SEO et référencement naturel</NuxtLink> peut être envisagé en complément, une fois le site en ligne. Nous n'incluons pas ce chantier par défaut dans une création de site — il répond à un autre besoin, avec un autre budget et un autre calendrier.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- H. Déroulement du projet -->
    <EditorialSection
      tone="subtle"
      eyebrow="Déroulement"
      title="De l'échange initial à la mise en ligne."
      intro="Un projet de création de site dure typiquement entre quatre et dix semaines, selon le périmètre défini et la disponibilité des contenus. Le calendrier est fixé au démarrage."
      section-id="deroulement"
    >
      <div class="csi-page__timeline">
        <div class="csi-page__timeline-item">
          <div class="csi-page__timeline-marker" aria-hidden="true" />
          <div class="csi-page__timeline-content">
            <h3 class="csi-page__timeline-title">Semaine 1 — Découverte et cadrage</h3>
            <p class="csi-page__timeline-text">
              Un ou deux échanges pour comprendre votre contexte : activité, cibles, concurrents de référence, contraintes techniques éventuelles. En sortie : plan de site et périmètre validé.
            </p>
          </div>
        </div>

        <div class="csi-page__timeline-item">
          <div class="csi-page__timeline-marker" aria-hidden="true" />
          <div class="csi-page__timeline-content">
            <h3 class="csi-page__timeline-title">Semaines 2-3 — Conception et design</h3>
            <p class="csi-page__timeline-text">
              Wireframes des gabarits principaux, puis maquettes desktop et mobile. Deux cycles de révision sont inclus. La validation du design marque le point de départ du développement.
            </p>
          </div>
        </div>

        <div class="csi-page__timeline-item">
          <div class="csi-page__timeline-marker" aria-hidden="true" />
          <div class="csi-page__timeline-content">
            <h3 class="csi-page__timeline-title">Semaines 3-7 — Développement et contenu</h3>
            <p class="csi-page__timeline-text">
              Intégration en parallèle : développement du site côté technique, production ou finalisation des contenus côté éditorial. Les deux branches convergent avant la mise en ligne.
            </p>
          </div>
        </div>

        <div class="csi-page__timeline-item">
          <div class="csi-page__timeline-marker" aria-hidden="true" />
          <div class="csi-page__timeline-content">
            <h3 class="csi-page__timeline-title">Semaine 8+ — Recette et mise en ligne</h3>
            <p class="csi-page__timeline-text">
              Tests multi-navigateur, vérifications d'accessibilité et de performance, contrôle SEO de base. Déploiement sur l'hébergement définitif, configuration des DNS, formation au CMS si applicable.
            </p>
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- I. Ce qui fait varier le prix -->
    <EditorialSection
      tone="default"
      eyebrow="Budget"
      title="Ce qui fait réellement varier le prix."
      section-id="budget"
    >
      <div class="csi-page__budget-content">
        <p class="csi-page__paragraph csi-page__paragraph--lead">
          Le coût d'un site dépend de quelques variables concrètes, pas d'un tarif catalogue. Les voici dans l'ordre d'impact le plus fréquent.
        </p>
        <ul class="csi-page__budget-list" role="list">
          <li class="csi-page__budget-item">
            <span class="csi-page__budget-label">Nombre de pages et volume de contenu</span>
            <span class="csi-page__budget-detail">Un site de 5 pages nécessite moins de temps qu'un site de 30, même si la maquette de chaque gabarit est similaire.</span>
          </li>
          <li class="csi-page__budget-item">
            <span class="csi-page__budget-label">Design entièrement sur mesure ou adapté</span>
            <span class="csi-page__budget-detail">Un design original créé de zéro demande plus de temps qu'un design qui s'appuie sur une identité existante et un système de composants établi.</span>
          </li>
          <li class="csi-page__budget-item">
            <span class="csi-page__budget-label">Présence ou non d'un CMS</span>
            <span class="csi-page__budget-detail">Intégrer un système de gestion de contenu ajoute un périmètre de développement et une session de formation, mais donne une autonomie réelle sur la mise à jour du site.</span>
          </li>
          <li class="csi-page__budget-item">
            <span class="csi-page__budget-label">Production de contenu incluse ou non</span>
            <span class="csi-page__budget-detail">Rédaction et photographie déléguées à Devzair représentent un poste budgétaire séparé, mais permettent de livrer un site prêt à l'usage sans dépendre de délais externes.</span>
          </li>
          <li class="csi-page__budget-item">
            <span class="csi-page__budget-label">Intégrations externes</span>
            <span class="csi-page__budget-detail">Système de réservation, paiement en ligne partiel, connexion à un CRM ou à un outil tiers — chaque intégration ajoute un périmètre technique et des tests associés.</span>
          </li>
        </ul>
        <div class="csi-page__budget-cta">
          <p class="csi-page__budget-cta-text">
            L'estimateur de projet vous aide à identifier les variables pertinentes pour votre situation et vous donne une fourchette budgétaire indicative en quelques minutes.
          </p>
          <NuxtLink to="/estimer-mon-projet" class="csi-page__estimator-link">
            Estimer mon projet →
          </NuxtLink>
        </div>
      </div>
    </EditorialSection>

    <!-- J. Réalisations vitrines -->
    <EditorialSection
      tone="subtle"
      eyebrow="Réalisations"
      title="Sites vitrines que nous avons conçus."
      intro="Trois projets représentatifs du périmètre vitrine — secteurs différents, contraintes différentes, même exigence de lisibilité."
      section-id="realisations"
    >
      <ul class="csi-page__cases" role="list">
        <li
          v-for="cs in vitrineCaseStudies"
          :key="cs.id"
          class="csi-page__case-card"
        >
          <div class="csi-page__case-header">
            <h3 class="csi-page__case-name">{{ cs.name }}</h3>
            <span class="csi-page__case-category">{{ cs.category }}</span>
          </div>
          <p class="csi-page__case-description">{{ cs.longDescription }}</p>
          <div class="csi-page__case-tags">
            <span
              v-for="tag in cs.tags"
              :key="tag"
              class="csi-page__case-tag"
            >
              {{ tag }}
            </span>
          </div>
          <a
            v-if="cs.href"
            :href="cs.href"
            target="_blank"
            rel="noopener noreferrer"
            class="csi-page__case-link"
          >
            Voir le site →
          </a>
        </li>
      </ul>
    </EditorialSection>

    <!-- K. FAQ -->
    <EditorialSection
      tone="default"
      eyebrow="Questions fréquentes"
      title="Ce que nos clients demandent avant de démarrer."
      section-id="faq"
    >
      <dl class="csi-page__faq">
        <div
          v-for="item in faqItems"
          :key="item.question"
          class="csi-page__faq-item"
        >
          <dt class="csi-page__faq-question">{{ item.question }}</dt>
          <dd class="csi-page__faq-answer">{{ item.answer }}</dd>
        </div>
      </dl>
    </EditorialSection>

    <!-- L. CTA final -->
    <EditorialCallout
      eyebrow="Démarrer votre projet"
      title="Pas encore sûr du périmètre exact ?"
      description="L'estimateur de projet vous aide à identifier ce dont vous avez réellement besoin — type de site, pages clés, contenu, CMS — en quelques minutes, sans engagement."
      :primary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
      :secondary="{ label: 'Parler de votre projet', to: '/contact' }"
    />
  </div>
</template>

<style scoped>
.csi-page {
  display: flex;
  flex-direction: column;
}

/* ── Hero CTAs ────────────────────────────────────────────────── */

.csi-page__hero-cta {
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

.csi-page__hero-cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.csi-page__hero-cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.csi-page__hero-cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.csi-page__hero-cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

/* ── Texte courant ────────────────────────────────────────────── */

.csi-page__paragraph {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

.csi-page__paragraph--lead {
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  color: var(--text-primary);
}

/* ── Lien inline ──────────────────────────────────────────────── */

.csi-page__inline-link {
  color: var(--color-petrol);
  text-decoration: underline;
  text-underline-offset: 2px;
  transition: color 0.12s ease;
}

.csi-page__inline-link:hover {
  color: var(--color-navy);
}

/* ── B. Situations ────────────────────────────────────────────── */

.csi-page__situations {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.csi-page__situation-block {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-left: var(--space-4);
  border-left: 2px solid var(--color-petrol);
}

.csi-page__situation-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.6vw, 1.125rem);
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
}

.csi-page__situation-text {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

/* ── C. Périmètre adapté ──────────────────────────────────────── */

.csi-page__scope-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.csi-page__scope-card {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  background-color: var(--background-secondary);
  border: 1px solid rgba(12, 91, 87, 0.12);
  border-radius: var(--radius-md);
}

.csi-page__scope-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.25;
  color: var(--text-primary);
  margin: 0;
}

.csi-page__scope-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
}

.csi-page__scope-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.csi-page__scope-list li::before {
  content: "—";
  margin-right: var(--space-2);
  color: var(--color-petrol);
  font-weight: 600;
}

/* ── D. Design sur mesure ─────────────────────────────────────── */

.csi-page__design-content {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.csi-page__design-text {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.csi-page__design-accent {
  display: none;
}

/* ── E. Étapes du projet ──────────────────────────────────────── */

.csi-page__steps {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.csi-page__step {
  display: flex;
  gap: var(--space-5);
  padding: var(--space-5) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.csi-page__step:last-child {
  border-bottom: none;
}

.csi-page__step-number {
  flex-shrink: 0;
  font-family: var(--font-family-mono, "Space Mono", monospace);
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-petrol);
  padding-top: 0.2em;
  min-width: 2rem;
}

.csi-page__step-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.csi-page__step-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(0.9375rem, 1.4vw, 1.0625rem);
  line-height: 1.25;
  color: var(--text-primary);
  margin: 0;
}

.csi-page__step-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

/* ── F. Contenu ───────────────────────────────────────────────── */

.csi-page__content-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
}

.csi-page__content-block {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

/* ── G. SEO ───────────────────────────────────────────────────── */

.csi-page__seo-content {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.csi-page__seo-subtitle {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(0.9375rem, 1.3vw, 1rem);
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0 0 var(--space-4);
}

.csi-page__seo-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.csi-page__seo-item {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.5;
  color: var(--text-secondary);
  padding-left: var(--space-5);
  position: relative;
}

.csi-page__seo-item::before {
  content: "✓";
  position: absolute;
  left: 0;
  color: var(--color-petrol);
  font-weight: 700;
}

.csi-page__seo-honest {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

/* ── H. Timeline ──────────────────────────────────────────────── */

.csi-page__timeline {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  position: relative;
}

.csi-page__timeline-item {
  display: flex;
  gap: var(--space-5);
  padding: var(--space-4) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.csi-page__timeline-item:last-child {
  border-bottom: none;
}

.csi-page__timeline-marker {
  flex-shrink: 0;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: var(--color-petrol);
  margin-top: 0.45em;
}

.csi-page__timeline-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.csi-page__timeline-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(0.9375rem, 1.3vw, 1rem);
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
}

.csi-page__timeline-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

/* ── I. Budget ────────────────────────────────────────────────── */

.csi-page__budget-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.csi-page__budget-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0;
}

.csi-page__budget-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  padding: var(--space-4) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.csi-page__budget-item:last-child {
  border-bottom: none;
}

.csi-page__budget-label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 0.9375rem;
  line-height: 1.25;
  color: var(--text-primary);
}

.csi-page__budget-detail {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.csi-page__budget-cta {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-5);
  background-color: var(--background-secondary);
  border-radius: var(--radius-md);
  border-left: 2px solid var(--color-petrol);
}

.csi-page__budget-cta-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.csi-page__estimator-link {
  display: inline-flex;
  align-self: flex-start;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-petrol);
  text-decoration: none;
  transition: color 0.12s ease;
}

.csi-page__estimator-link:hover {
  color: var(--color-navy);
}

/* ── J. Réalisations ──────────────────────────────────────────── */

.csi-page__cases {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.csi-page__case-card {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-5) var(--space-6);
  background-color: var(--background-primary);
  border: 1px solid rgba(12, 91, 87, 0.12);
  border-radius: var(--radius-md);
}

.csi-page__case-header {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.csi-page__case-name {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.25;
  color: var(--text-primary);
  margin: 0;
}

.csi-page__case-category {
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  color: var(--color-petrol);
  font-weight: 600;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.csi-page__case-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  flex: 1;
}

.csi-page__case-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
}

.csi-page__case-tag {
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  color: var(--text-secondary);
  background-color: var(--background-secondary);
  border: 1px solid rgba(12, 91, 87, 0.12);
  border-radius: var(--radius-sm);
  padding: 2px var(--space-2);
}

.csi-page__case-link {
  display: inline-flex;
  align-self: flex-start;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-petrol);
  text-decoration: none;
  transition: color 0.12s ease;
}

.csi-page__case-link:hover {
  color: var(--color-navy);
}

/* ── K. FAQ ───────────────────────────────────────────────────── */

.csi-page__faq {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin: 0;
}

.csi-page__faq-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-5) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.csi-page__faq-item:last-child {
  border-bottom: none;
}

.csi-page__faq-question {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(0.9375rem, 1.3vw, 1.0625rem);
  line-height: 1.3;
  color: var(--text-primary);
}

.csi-page__faq-answer {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 66ch;
}

/* ── Responsive ───────────────────────────────────────────────── */

@media (min-width: 640px) {
  .csi-page__scope-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .csi-page__cases {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 768px) {
  .csi-page__situations {
    gap: var(--space-10);
  }

  .csi-page__design-accent {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    justify-content: flex-start;
    padding-top: var(--space-4);
  }

  .csi-page__design-mark {
    border-radius: var(--radius-sm);
    height: 4px;
  }

  .csi-page__design-mark--a {
    width: 100%;
    background-color: var(--color-petrol);
    opacity: 0.8;
  }

  .csi-page__design-mark--b {
    width: 70%;
    background-color: var(--color-petrol);
    opacity: 0.4;
  }

  .csi-page__design-mark--c {
    width: 45%;
    background-color: var(--color-petrol);
    opacity: 0.2;
  }
}

@media (min-width: 900px) {
  .csi-page__design-content {
    grid-template-columns: minmax(0, 3fr) minmax(0, 1fr);
    gap: var(--space-12);
    align-items: start;
  }

  .csi-page__content-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .csi-page__seo-content {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .csi-page__cases {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
