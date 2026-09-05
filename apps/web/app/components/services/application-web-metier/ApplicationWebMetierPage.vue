<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import { caseStudies } from "~/config/case-studies"
import type { ServicePageDefinition } from "~/config/service-pages"

/**
 * Page dédiée `/services/application-web-metier`.
 *
 * Rôle : expliquer dans quels cas un outil métier sur mesure devient pertinent
 * — en partant des situations et des processus réels, pas des technologies.
 *
 * Ce composant NE pose AUCUN SEO ni JSON-LD : ces appels sont faits
 * dans `pages/services/[slug].vue` avant le rendu.
 *
 * Distinction avec les deux autres services construction :
 *   - Création de site répond à « présenter mon activité » ;
 *   - E-commerce répond à « vendre mes produits en ligne » ;
 *   - cette page répond à « outiller un processus interne ou opérationnel ».
 */

interface Props {
  page: ServicePageDefinition
}

defineProps<Props>()

const mizan = caseStudies.find((cs) => cs.id === "mizan")

const useCases = [
  {
    key: "disperse",
    title: "Des informations dispersées dans plusieurs outils.",
    text: "Commandes dans les e-mails, stock dans un tableur, clients dans un autre fichier — à un certain volume, la friction devient coûteuse. Centraliser dans un outil adapté réduit les erreurs et libère du temps sur les tâches à faible valeur ajoutée.",
  },
  {
    key: "repetitif",
    title: "Des tâches répétitives qui consomment du temps humain.",
    text: "Certains workflows peuvent être en partie automatisés sans perdre le contrôle sur les cas exceptionnels. Avant de coder quoi que ce soit, nous regardons ce qui est vraiment automatisable et ce qui nécessite un jugement humain.",
  },
  {
    key: "multiutilisateur",
    title: "Plusieurs personnes accèdent aux mêmes données.",
    text: "Dès qu'une équipe utilise un outil partagé, les questions de rôles, de droits et de cohérence des données se posent. Une application bien conçue gère ces questions en amont — pas à coups de colonnes supplémentaires dans un tableur partagé.",
  },
]

const faqItems = [
  {
    question: "Quelle est la différence entre une application web et un site internet ?",
    answer:
      "Un site internet s'adresse principalement à des visiteurs extérieurs : il présente, informe, convainc. Une application web est un outil qui sert un processus — elle manipule des données, gère des états, répond à des actions. La frontière n'est pas toujours nette, mais la question utile est : est-ce qu'on cherche à communiquer, ou à accomplir une tâche ?",
  },
  {
    question: "Peut-on remplacer plusieurs fichiers Excel par une application ?",
    answer:
      "Souvent, oui — mais la transition doit être préparée. Avant de migrer des données, nous regardons comment les tableurs sont utilisés concrètement : quels champs, quelles formules, quels cas limites. Un outil qui reproduit exactement ce que fait Excel n'est pas toujours plus utile. L'objectif est d'identifier ce qui bloque et de concevoir autour de ça.",
  },
  {
    question: "L'application peut-elle fonctionner sur mobile ?",
    answer:
      "Oui, si le contexte d'usage le justifie. Certaines applications sont conçues mobile-first — notamment quand les utilisateurs interviennent sur le terrain. D'autres sont plus adaptées à un usage bureau. Nous concevons en fonction du contexte réel, pas d'un principe général.",
  },
  {
    question: "Peut-on commencer avec seulement quelques fonctionnalités ?",
    answer:
      "C'est souvent la meilleure approche. Un outil avec un périmètre prioritaire bien pensé est livré plus vite, adopté plus facilement et peut évoluer sur la base de ce qui est vraiment utilisé — plutôt que sur ce qui semblait utile au moment du cadrage.",
  },
  {
    question: "Comment est déterminé le budget ?",
    answer:
      "Le périmètre d'une application dépend du nombre de parcours, des règles métier, des données manipulées, des intégrations, des rôles utilisateurs et du niveau de polish attendu. C'est pourquoi une application métier n'a pas de prix standard. Un échange court suffit à identifier les grandes variables et à évaluer l'ordre de grandeur.",
  },
  {
    question: "Peut-on connecter l'application à d'autres outils existants ?",
    answer:
      "Oui, lorsque ces outils exposent une API ou un format d'échange. Cela peut couvrir des logiciels de comptabilité, des outils de messagerie, des plateformes e-commerce ou des services métier spécifiques. L'intégration est cadrée selon ce qui est techniquement faisable et ce qui apporte réellement de la valeur.",
  },
]
</script>

<template>
  <div class="awm-page">
    <!-- A. Hero -->
    <EditorialHero
      eyebrow="Application web métier"
      title="Une application web conçue autour de votre façon de travailler."
      lead="Avant d'ouvrir un éditeur de code, nous cherchons à comprendre les tâches réelles, les données manipulées, les personnes qui utilisent l'outil et les règles qui gouvernent votre activité. Un outil conçu à partir des processus existants est adopté plus facilement qu'un outil qui force à s'adapter."
    >
      <template #actions>
        <NuxtLink
          to="/contact"
          class="awm-page__hero-cta awm-page__hero-cta--primary"
        >
          Parler de votre projet
        </NuxtLink>
        <NuxtLink
          to="/estimer-mon-projet"
          class="awm-page__hero-cta awm-page__hero-cta--secondary"
        >
          Estimer mon projet
        </NuxtLink>
      </template>
    </EditorialHero>

    <!-- B. Quand un site ne suffit plus -->
    <EditorialSection
      tone="subtle"
      eyebrow="Point de départ"
      title="La différence entre un site et un outil."
      section-id="point-de-depart"
    >
      <div class="awm-page__distinction">
        <div class="awm-page__distinction-row">
          <div class="awm-page__distinction-col awm-page__distinction-col--site">
            <p class="awm-page__distinction-type">Site internet</p>
            <p class="awm-page__distinction-text">
              S'adresse à des visiteurs extérieurs. Présente une activité,
              informe, convainc, génère un contact ou une vente. Le contenu
              est stable ; l'interaction reste limitée.
            </p>
          </div>
          <div class="awm-page__distinction-col awm-page__distinction-col--app">
            <p class="awm-page__distinction-type">Application métier</p>
            <p class="awm-page__distinction-text">
              Sert un processus. Manipule des données, gère des états, répond
              à des actions. Les utilisateurs ne sont pas des visiteurs anonymes :
              ils ont des rôles, des droits et des tâches à accomplir.
            </p>
          </div>
        </div>
        <p class="awm-page__paragraph">
          La question qui oriente le choix n'est pas technique : est-ce qu'on
          cherche à communiquer, ou à accomplir une tâche ? Les deux peuvent
          coexister dans un même projet, mais répondent à des logiques différentes.
        </p>
      </div>
    </EditorialSection>

    <!-- C. Situations concrètes -->
    <EditorialSection
      tone="default"
      eyebrow="Cas d'usage"
      title="Dans quelles situations un outil métier devient pertinent."
      section-id="cas-usage"
    >
      <ul class="awm-page__use-cases" role="list">
        <li
          v-for="(item, index) in useCases"
          :key="item.key"
          class="awm-page__use-case"
        >
          <span class="awm-page__use-case-num" aria-hidden="true">
            {{ String(index + 1).padStart(2, "0") }}
          </span>
          <div class="awm-page__use-case-body">
            <h3 class="awm-page__use-case-title">{{ item.title }}</h3>
            <p class="awm-page__use-case-text">{{ item.text }}</p>
          </div>
        </li>
      </ul>
    </EditorialSection>

    <!-- D. Partir du processus réel -->
    <EditorialSection
      tone="subtle"
      eyebrow="Méthode"
      title="Comprendre avant de concevoir."
      intro="Un outil métier qui ne correspond pas aux habitudes de travail réelles ne sera pas utilisé — quelle que soit la qualité du développement."
      section-id="methode"
    >
      <div class="awm-page__process">
        <div class="awm-page__process-block">
          <h3 class="awm-page__process-title">Qui fait quoi ?</h3>
          <p class="awm-page__process-text">
            Identifier les utilisateurs, leurs rôles et leurs niveaux de
            droits avant de dessiner une seule interface. Un commercial, un
            gestionnaire de stock et un dirigeant n'ont pas besoin de voir
            les mêmes données.
          </p>
        </div>
        <div class="awm-page__process-block">
          <h3 class="awm-page__process-title">Quelles données, dans quel ordre ?</h3>
          <p class="awm-page__process-text">
            Cartographier les données qui circulent, leur source, leurs
            transformations et leurs destinations. C'est ce travail préalable
            qui permet d'éviter de devoir refondre la base six mois après
            le lancement.
          </p>
        </div>
        <div class="awm-page__process-block">
          <h3 class="awm-page__process-title">Ce qui peut être automatisé — et ce qui ne doit pas.</h3>
          <p class="awm-page__process-text">
            Tout ce qui est répétitif n'est pas automatisable. Les cas
            exceptionnels, les jugements métier et les arbitrages humains
            doivent rester accessibles. Nous distinguons ce qui gagne à
            être automatisé de ce qui doit rester dans la main de l'utilisateur.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- E. Interface et UX -->
    <EditorialSection
      tone="default"
      eyebrow="Interface"
      title="Le design d'une application, c'est d'abord l'efficacité."
      section-id="interface"
    >
      <div class="awm-page__ux-split">
        <div class="awm-page__ux-text">
          <p class="awm-page__paragraph">
            Dans une application métier, le design ne sert pas à impressionner
            un visiteur en trois secondes. Il sert à rendre une tâche rapide,
            une donnée lisible, une erreur compréhensible. Un utilisateur qui
            travaille avec l'outil plusieurs heures par jour ne pardonne pas
            une interface lente, confuse ou bruyante.
          </p>
          <p class="awm-page__paragraph">
            La hiérarchie de l'information, la densité des écrans, la gestion
            des états d'erreur et les actions destructrices irréversibles sont
            des sujets de conception à part entière — pas des détails réglés
            en fin de projet.
          </p>
          <p class="awm-page__paragraph">
            Le design peut aussi servir l'image de l'entreprise lorsque
            l'application est utilisée en face du client. Dans ce cas,
            l'identité visuelle entre en jeu — au même titre que pour un site.
            Les deux objectifs ne sont pas incompatibles.
          </p>
        </div>
        <div class="awm-page__ux-modules">
          <div class="awm-page__ux-module">
            <span class="awm-page__ux-module-label">Vitesse</span>
            <span class="awm-page__ux-module-text">Actions fréquentes accessibles en un minimum de clics</span>
          </div>
          <div class="awm-page__ux-module">
            <span class="awm-page__ux-module-label">Lisibilité</span>
            <span class="awm-page__ux-module-text">Données présentées dans le contexte où elles servent</span>
          </div>
          <div class="awm-page__ux-module">
            <span class="awm-page__ux-module-label">Erreurs</span>
            <span class="awm-page__ux-module-text">Messages explicites, pas de perte silencieuse de données</span>
          </div>
          <div class="awm-page__ux-module">
            <span class="awm-page__ux-module-label">Terrain</span>
            <span class="awm-page__ux-module-text">Interface adaptée aux conditions d'usage réelles</span>
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- F. Mobile-first -->
    <EditorialSection
      tone="subtle"
      eyebrow="Accessibilité terrain"
      title="Mobile-first quand le métier le demande."
      section-id="mobile"
    >
      <div class="awm-page__mobile-content">
        <p class="awm-page__paragraph">
          Certains métiers se pratiquent loin d'un bureau : gestion de stock
          en entrepôt, suivi de commandes en déplacement, consultation rapide
          d'informations client en rendez-vous. Dans ces contextes, concevoir
          pour mobile en premier n'est pas une mode — c'est simplement aligner
          le produit sur la façon dont il sera utilisé.
        </p>
        <p class="awm-page__paragraph">
          D'autres applications sont plutôt pensées pour une utilisation bureau,
          avec des tableaux de données complexes, plusieurs colonnes et une
          navigation riche. La décision se prend au cadrage, selon les contextes
          d'usage réels décrits par les futurs utilisateurs — pas par défaut.
        </p>
      </div>
    </EditorialSection>

    <!-- G. Périmètre progressif -->
    <EditorialSection
      tone="default"
      eyebrow="Évolution"
      title="Un outil peut démarrer avec les fonctionnalités prioritaires."
      section-id="evolution"
    >
      <div class="awm-page__evolution">
        <p class="awm-page__paragraph">
          Vouloir tout livrer en une fois est souvent contre-productif. Un
          périmètre initial concentré sur les besoins les plus critiques permet
          de livrer plus vite, de tester en conditions réelles et d'ajuster
          avant d'investir dans les fonctionnalités secondaires.
        </p>
        <p class="awm-page__paragraph">
          Une architecture bien pensée dès le départ permet d'ajouter des
          modules — nouvelles sections, nouveaux rôles, nouvelles intégrations
          — sans remettre en cause les fondations. Ce n'est pas de la scalabilité
          abstraite : c'est de la construction propre dès la première version.
        </p>
        <div class="awm-page__evolution-note">
          <p class="awm-page__evolution-note-text">
            La maintenance et les évolutions futures font partie des sujets
            évoqués au cadrage. Nous sommes transparents sur ce qui peut être
            repris par une autre équipe et ce qui nécessite une continuité de
            connaissance du projet.
          </p>
        </div>
      </div>
    </EditorialSection>

    <!-- H. Accès et sécurité -->
    <EditorialSection
      tone="subtle"
      eyebrow="Accès et données"
      title="Qui peut faire quoi dans votre outil."
      section-id="securite"
    >
      <div class="awm-page__security">
        <p class="awm-page__paragraph">
          Une application multi-utilisateurs soulève des questions d'accès :
          qui peut consulter quelles données, qui peut les modifier, qui peut
          les supprimer. Ces règles ne sont pas des détails techniques — elles
          reflètent des décisions métier qui doivent être arrêtées avant le
          développement.
        </p>
        <p class="awm-page__paragraph">
          Selon le contexte du projet, nous abordons : l'authentification, les
          niveaux de permission, la gestion des données personnelles conformément
          au RGPD, les sauvegardes et la traçabilité des actions sensibles.
          Nous ne promettons pas une sécurité absolue — nous définissons un
          niveau de protection adapté à ce que l'application gère réellement.
        </p>
      </div>
    </EditorialSection>

    <!-- I. Budget -->
    <EditorialSection
      tone="default"
      eyebrow="Budget"
      title="Pourquoi une application métier n'a pas de prix standard."
      section-id="budget"
    >
      <div class="awm-page__budget">
        <p class="awm-page__paragraph sec-page__paragraph--lead">
          Le périmètre d'une application dépend de variables que seul un
          échange permet d'identifier précisément. Voici les plus déterminantes.
        </p>
        <div class="awm-page__budget-items">
          <div class="awm-page__budget-item">
            <span class="awm-page__budget-item-label">Nombre de parcours et d'états</span>
            <p class="awm-page__budget-item-text">
              Une commande peut passer par plusieurs statuts, chacun
              déclenchant des actions différentes. Plus les états sont
              nombreux et les règles complexes, plus le développement
              et les tests prennent de temps.
            </p>
          </div>
          <div class="awm-page__budget-item">
            <span class="awm-page__budget-item-label">Rôles utilisateurs</span>
            <p class="awm-page__budget-item-text">
              Chaque rôle distinct nécessite ses propres vues, ses propres
              droits et ses propres scénarios de test. Deux rôles = deux
              applications à concevoir dans le même outil.
            </p>
          </div>
          <div class="awm-page__budget-item">
            <span class="awm-page__budget-item-label">Intégrations externes</span>
            <p class="awm-page__budget-item-text">
              Connexion à un logiciel de comptabilité, à un outil de
              messagerie, à une plateforme de paiement — chaque intégration
              ajoute un périmètre de développement et de tests.
            </p>
          </div>
          <div class="awm-page__budget-item">
            <span class="awm-page__budget-item-label">Volume de données et performance</span>
            <p class="awm-page__budget-item-text">
              Un outil qui gère quelques dizaines d'enregistrements n'a pas
              les mêmes contraintes qu'un outil qui en gère des milliers avec
              des recherches et des filtres en temps réel.
            </p>
          </div>
        </div>
        <div class="awm-page__budget-cta">
          <p class="awm-page__budget-cta-text">
            Un échange de vingt minutes suffit généralement à identifier les
            grandes variables et à évaluer un ordre de grandeur. L'estimateur
            de projet peut aussi vous donner un point de départ.
          </p>
          <div class="awm-page__budget-cta-actions">
            <NuxtLink to="/contact" class="awm-page__cta-link awm-page__cta-link--primary">
              Parler de votre projet →
            </NuxtLink>
            <NuxtLink to="/estimer-mon-projet" class="awm-page__cta-link awm-page__cta-link--secondary">
              Estimer mon projet →
            </NuxtLink>
          </div>
        </div>
      </div>
    </EditorialSection>

    <!-- J. Réalisation Mizan -->
    <EditorialSection
      v-if="mizan && mizan.status === 'published'"
      tone="subtle"
      eyebrow="Réalisation"
      title="Mizan — application SaaS de gestion pour commerçants."
      section-id="realisation"
    >
      <div class="awm-page__case">
        <div class="awm-page__case-content">
          <p class="awm-page__case-category">{{ mizan.category }}</p>
          <p class="awm-page__paragraph">
            {{ mizan.longDescription }}
          </p>
          <ul class="awm-page__case-tags" aria-label="Interventions">
            <li
              v-for="tag in mizan.tags"
              :key="tag"
              class="awm-page__case-tag"
            >
              {{ tag }}
            </li>
          </ul>
          <NuxtLink
            :to="mizan.route"
            class="awm-page__case-link"
          >
            Voir l'étude de cas complète →
          </NuxtLink>
        </div>
        <div class="awm-page__case-visual">
          <img
            :src="mizan.imageSrc"
            :alt="mizan.imageAlt"
            class="awm-page__case-img"
            width="960"
            height="600"
            loading="lazy"
            decoding="async"
          >
        </div>
      </div>
    </EditorialSection>

    <!-- K. FAQ -->
    <EditorialSection
      tone="default"
      eyebrow="Questions fréquentes"
      title="Ce que nos clients demandent avant de démarrer un projet applicatif."
      section-id="faq"
    >
      <dl class="awm-page__faq">
        <div
          v-for="item in faqItems"
          :key="item.question"
          class="awm-page__faq-item"
        >
          <dt class="awm-page__faq-question">{{ item.question }}</dt>
          <dd class="awm-page__faq-answer">{{ item.answer }}</dd>
        </div>
      </dl>
    </EditorialSection>

    <!-- L. CTA final -->
    <EditorialCallout
      eyebrow="Votre projet applicatif"
      title="Un outil à concevoir ou à restructurer ?"
      description="Décrivez-nous le contexte d'usage, les personnes concernées et les tâches prioritaires. Nous identifions ensemble ce qui mérite d'être conçu en premier."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />
  </div>
</template>

<style scoped>
.awm-page {
  display: flex;
  flex-direction: column;
}

/* ── Hero CTAs ────────────────────────────────────────────────── */

.awm-page__hero-cta {
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

.awm-page__hero-cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.awm-page__hero-cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.awm-page__hero-cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.awm-page__hero-cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

/* ── Texte courant ────────────────────────────────────────────── */

.awm-page__paragraph {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

/* ── B. Distinction ───────────────────────────────────────────── */

.awm-page__distinction {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.awm-page__distinction-row {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.awm-page__distinction-col {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-5);
  border-radius: var(--radius-md);
  background-color: var(--background-primary);
  border: 1px solid rgba(12, 91, 87, 0.12);
}

.awm-page__distinction-col--app {
  border-color: var(--color-petrol);
  background-color: rgba(12, 91, 87, 0.04);
}

.awm-page__distinction-type {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.awm-page__distinction-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
}

/* ── C. Cas d'usage ───────────────────────────────────────────── */

.awm-page__use-cases {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  border-top: 1px solid var(--border-default);
}

.awm-page__use-case {
  display: grid;
  grid-template-columns: 3rem 1fr;
  gap: var(--space-5);
  padding: var(--space-7) 0;
  border-bottom: 1px solid var(--border-default);
}

.awm-page__use-case-num {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.75rem;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
  align-self: start;
  padding-top: 0.15rem;
}

.awm-page__use-case-body {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.awm-page__use-case-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.6vw, 1.125rem);
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
}

.awm-page__use-case-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

/* ── D. Processus ─────────────────────────────────────────────── */

.awm-page__process {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.awm-page__process-block {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-left: var(--space-4);
  border-left: 2px solid var(--color-petrol);
}

.awm-page__process-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.5vw, 1.0625rem);
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
}

.awm-page__process-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

/* ── E. Interface / UX ────────────────────────────────────────── */

.awm-page__ux-split {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.awm-page__ux-text {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.awm-page__ux-modules {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--space-3);
}

.awm-page__ux-module {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  padding: var(--space-4);
  background-color: var(--background-secondary);
  border-radius: var(--radius-md);
  border: 1px solid rgba(12, 91, 87, 0.1);
}

.awm-page__ux-module-label {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.awm-page__ux-module-text {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

/* ── F. Mobile ────────────────────────────────────────────────── */

.awm-page__mobile-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 68ch;
}

/* ── G. Évolution ─────────────────────────────────────────────── */

.awm-page__evolution {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.awm-page__evolution-note {
  padding: var(--space-4) var(--space-5);
  background-color: var(--background-secondary);
  border-radius: var(--radius-md);
  border-left: 2px solid rgba(12, 91, 87, 0.3);
}

.awm-page__evolution-note-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
}

/* ── H. Sécurité ──────────────────────────────────────────────── */

.awm-page__security {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 68ch;
}

/* ── I. Budget ────────────────────────────────────────────────── */

.awm-page__budget {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.awm-page__budget-items {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
  border-top: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.awm-page__budget-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-5) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.awm-page__budget-item-label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 0.9375rem;
  line-height: 1.25;
  color: var(--text-primary);
}

.awm-page__budget-item-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.awm-page__budget-cta {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  padding: var(--space-5);
  background-color: var(--background-secondary);
  border-radius: var(--radius-md);
  border-left: 2px solid var(--color-petrol);
}

.awm-page__budget-cta-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.awm-page__budget-cta-actions {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  flex-wrap: wrap;
}

.awm-page__cta-link {
  display: inline-flex;
  align-self: flex-start;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.12s ease;
}

.awm-page__cta-link--primary {
  color: var(--color-petrol);
}

.awm-page__cta-link--primary:hover {
  color: var(--color-navy);
}

.awm-page__cta-link--secondary {
  color: var(--text-secondary);
}

.awm-page__cta-link--secondary:hover {
  color: var(--text-primary);
}

/* ── J. Réalisation Mizan ─────────────────────────────────────── */

.awm-page__case {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
  align-items: start;
}

.awm-page__case-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.awm-page__case-category {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.6875rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.awm-page__case-visual {
  border-radius: var(--radius-lg);
  overflow: hidden;
  line-height: 0;
}

.awm-page__case-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

.awm-page__case-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.awm-page__case-tag {
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

.awm-page__case-link {
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

.awm-page__case-link:hover {
  color: var(--color-navy);
}

/* ── K. FAQ ───────────────────────────────────────────────────── */

.awm-page__faq {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin: 0;
}

.awm-page__faq-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-5) 0;
  border-bottom: 1px solid var(--border-subtle, rgba(12, 91, 87, 0.1));
}

.awm-page__faq-item:last-child {
  border-bottom: none;
}

.awm-page__faq-question {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(0.9375rem, 1.3vw, 1.0625rem);
  line-height: 1.3;
  color: var(--text-primary);
}

.awm-page__faq-answer {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 66ch;
}

/* ── Responsive ───────────────────────────────────────────────── */

@media (min-width: 640px) {
  .awm-page__distinction-row {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .awm-page__budget-cta-actions {
    flex-direction: row;
    align-items: center;
    gap: var(--space-5);
  }
}

@media (min-width: 768px) {
  .awm-page__process {
    gap: var(--space-10);
  }

  .awm-page__budget-items {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: var(--space-8);
  }
}

@media (min-width: 900px) {
  .awm-page__ux-split {
    grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
    gap: var(--space-12);
    align-items: start;
  }

  .awm-page__case {
    grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
    gap: var(--space-12);
  }
}

@media (prefers-reduced-motion: reduce) {
  .awm-page__hero-cta,
  .awm-page__cta-link,
  .awm-page__case-link {
    transition: none;
  }
}
</style>
