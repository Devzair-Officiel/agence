<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import ContactForm from "~/components/contact/ContactForm.vue"

/**
 * Page dédiée `/contact` — accueille le formulaire complet extrait de la
 * section CTA de la page d'accueil (auparavant `#contact` sur `/`).
 *
 * Composition — colonne unique centrée, ambiance chaude (fond sable +
 * carte cream élevée). L'ancien split navy/cream écrasait l'intro et
 * cassait la lecture du titre ; on préfère un rythme vertical simple :
 *
 *   1. hero centré : eyebrow, H1 (accent métallique sur « présence
 *      digitale »), lead ;
 *   2. frise « 1 / 2 / 3 » sur trois cartes horizontales sur desktop
 *      (empilées sur mobile) — même pastille numérotée que le mock, mais
 *      posée sur des cartes cream pour rester dans la palette chaude ;
 *   3. carte formulaire cream centrée (≤ 780 px) — le champ d'attention
 *      principal est le form, tout le reste sert de mise en confiance.
 *
 * Contraintes éditoriales (AGENTS.md §1) : pas d'engagement chiffré non
 * validé (« 24 h ouvrées », « proposition chiffrée »). Pas de bloc
 * « coordonnées » (site.contact null → règle §11 interdit tout
 * placeholder public).
 *
 * Accessibilité :
 *   - un unique H1 par page (celui-ci) ;
 *   - la frise est un `<ol>` sémantique ;
 *   - les pastilles chiffrées sont `aria-hidden` : le contenu textuel
 *     du `<li>` porte déjà l'information pour AT.
 *
 * SEO :
 *   - `usePageSeo` pour title/description/canonical/OG, `type: website` ;
 *   - `noindex` non appliqué : « devzair contact » est une requête
 *     légitime.
 */

const runtime = useRuntimeConfig()

const apiBaseUrl =
  typeof runtime.public.apiBaseUrl === "string" ? runtime.public.apiBaseUrl : "/api"
const turnstileSiteKey =
  typeof runtime.public.turnstileSiteKey === "string"
    ? runtime.public.turnstileSiteKey
    : ""
const turnstileEnabled = runtime.public.turnstileEnabled === true
const contactEndpoint = `${apiBaseUrl.replace(/\/$/, "")}/contact`

const timeline: readonly { title: string; detail: string }[] = [
  {
    title: "Une réponse humaine",
    detail: "Nous lisons chaque message. Pas d'accusé automatique.",
  },
  {
    title: "Un échange pour cadrer votre besoin",
    detail: "Appel ou visio, sans engagement.",
  },
  {
    title: "Une direction claire",
    detail: "Priorités, étapes et suite définies ensemble.",
  },
]

/**
 * Panneau éditorial à gauche du formulaire — aide le visiteur à préparer
 * un message utile. Chaque bloc décrit une information à partager, jamais
 * un engagement chiffré non validé (règle AGENTS.md §1). Le libellé
 * « Budget indicatif » reste large : on ne promet ni fourchette ni offre.
 */
const prompts: readonly { title: string; detail: string }[] = [
  {
    title: "Votre contexte",
    detail:
      "Activité, marché, taille de l'équipe — ce qui nous aidera à comprendre où vous en êtes.",
  },
  {
    title: "Votre objectif",
    detail:
      "Ce que vous cherchez à obtenir concrètement : refonte, visibilité, nouvelle plateforme, cadrage.",
  },
  {
    title: "Vos contraintes",
    detail:
      "Échéance visée, existant technique, équipe interne — tout ce qui borne la mission.",
  },
  {
    title: "Un ordre de grandeur",
    detail:
      "Budget indicatif si vous en avez un, pour évaluer ensemble ce qui est réaliste.",
  },
]

usePageSeo({
  title: "Contact — parlons de votre projet",
  description:
    "Contactez Devzair pour discuter de votre projet digital : site, application, identité, contenu ou visibilité. Un premier échange pour comprendre votre besoin et clarifier les priorités.",
  path: "/contact",
  type: "website",
})
</script>

<template>
  <main class="contact-page">
    <!--
      Hero sombre — bandeau navy avec un faisceau lumineux conique
      descendant depuis le haut-centre. Grille de points subtile en fond
      pour texturer l'obscurité. Le titre et le lead se lisent au centre
      du faisceau, comme sous un projecteur.
    -->
    <section
      class="contact-page__hero-section"
      aria-labelledby="contact-page-title"
    >
      <BaseContainer width="wide" class="contact-page__hero-container">
        <header class="contact-page__hero">
          <BaseEyebrow tone="inverse" class="contact-page__eyebrow">
            Parlons de votre projet
          </BaseEyebrow>
          <h1 id="contact-page-title" class="contact-page__title">
            Construisons une
            <span class="contact-page__title-emphasis">présence digitale</span>
            à la hauteur de votre entreprise.
          </h1>
          <p class="contact-page__lead">
            Un premier échange nous permettra de comprendre votre besoin, de
            clarifier les priorités et de définir une direction adaptée à
            votre activité.
          </p>
        </header>

        <!--
          Frise « ce qui se passe ensuite » — désormais dans le hero
          sombre, elle prolonge le faisceau lumineux vers le bas et fait
          la charnière visuelle avant le formulaire. Les cartes sont
          traitées en verre translucide sur fond sombre.
        -->
        <ol class="contact-page__timeline" aria-label="Ce qui se passe ensuite">
          <li
            v-for="(step, index) in timeline"
            :key="step.title"
            class="contact-page__step"
            :style="{ '--step-delay': `${index * 120}ms` }"
          >
            <span class="contact-page__step-number" aria-hidden="true">
              {{ String(index + 1).padStart(2, "0") }}
            </span>
            <p class="contact-page__step-title">{{ step.title }}</p>
            <p class="contact-page__step-detail">{{ step.detail }}</p>
          </li>
        </ol>
      </BaseContainer>
    </section>

    <section class="contact-page__body-section">
      <BaseContainer width="wide" class="contact-page__container">
        <div class="contact-page__split">
          <aside class="contact-page__aside" aria-labelledby="contact-page-prompts-title">
            <p class="contact-page__aside-eyebrow">Préparer votre message</p>
            <h2 id="contact-page-prompts-title" class="contact-page__aside-title">
              Ce qu'il est utile de nous partager
            </h2>
            <p class="contact-page__aside-lead">
              Un message précis permet un premier retour plus concret. Voici les
              éléments que nous relisons en priorité.
            </p>
            <ul class="contact-page__prompts">
              <li
                v-for="prompt in prompts"
                :key="prompt.title"
                class="contact-page__prompt"
              >
                <p class="contact-page__prompt-title">{{ prompt.title }}</p>
                <p class="contact-page__prompt-detail">{{ prompt.detail }}</p>
              </li>
            </ul>
            <p class="contact-page__aside-note">
              Vos informations restent chez nous. Aucune donnée transmise à un
              tiers, aucun démarchage.
            </p>
          </aside>

          <div class="contact-page__form-card">
            <ContactForm
              :endpoint="contactEndpoint"
              :turnstile-site-key="turnstileSiteKey"
              :turnstile-enabled="turnstileEnabled"
            />
          </div>
        </div>
      </BaseContainer>
    </section>
  </main>
</template>

<style scoped>
.contact-page {
  color: var(--text-primary);
}

/*
 * Hero sombre — fond navy stratifié :
 *   1. base linéaire (haut plus foncé → bas légèrement plus clair) ;
 *   2. grille de points 32×32 très ténue, texture sans distraire ;
 *   3. faisceau conique doux (halo large) émanant depuis le haut-centre ;
 *   4. cœur du faisceau plus concentré, effet projecteur.
 * Les 4 couches sont empilées dans un même background pour éviter tout
 * pseudo-élément supplémentaire et garder le stacking simple.
 */
.contact-page__hero-section {
  position: relative;
  padding-block: var(--space-10) var(--space-12);
  min-height: calc(85svh - var(--site-header-height));
  display: flex;
  background:
    radial-gradient(
      ellipse 45% 95% at 50% 0%,
      rgba(220, 235, 255, 0.34) 0%,
      rgba(220, 235, 255, 0.16) 30%,
      rgba(220, 235, 255, 0.06) 55%,
      transparent 80%
    ),
    radial-gradient(
      ellipse 95% 120% at 50% -8%,
      rgba(160, 195, 235, 0.18) 0%,
      rgba(160, 195, 235, 0.06) 45%,
      transparent 75%
    ),
    radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px) 0 0 / 34px 34px,
    linear-gradient(180deg, #07101c 0%, #0d1c2d 100%);
  color: #f4f1ea;
  overflow: hidden;
}

.contact-page__hero-container {
  position: relative;
  flex: 1;
  display: grid;
  grid-template-rows: 1fr auto;
  row-gap: var(--space-12);
}

.contact-page__hero-container > .contact-page__hero {
  align-self: center;
}

/*
 * Hero — texte centré, respirations généreuses. Le H1 utilise le même
 * dégradé métallique argent → bleu que le hero d'accueil, en fallback
 * couleur solide sur les navigateurs sans background-clip: text.
 */
.contact-page__hero {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: var(--space-4);
  max-width: 60rem;
  margin-inline: auto;
}

.contact-page__eyebrow {
  margin-bottom: var(--space-1);
}

.contact-page__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2.25rem, 5vw, 3.25rem);
  line-height: 1.08;
  letter-spacing: -0.02em;
  color: #f4f1ea;
  margin: 0;
  max-width: 22ch;
  text-wrap: balance;
}

.contact-page__title-emphasis {
  color: var(--color-devzair-blue);
}

@supports ((-webkit-background-clip: text) or (background-clip: text)) {
  .contact-page__title-emphasis {
    background: linear-gradient(
      120deg,
      #b8d4f0 0%,
      var(--color-devzair-blue) 100%
    );
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }
}

.contact-page__lead {
  margin: 0;
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: rgba(244, 241, 234, 0.72);
  max-width: 58ch;
  text-wrap: pretty;
}

.contact-page__body-section {
  background-color: #ded9ce;
  padding-block: var(--space-12) var(--space-16);
}

.contact-page__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

/*
 * Split desktop 2 colonnes : aside éditorial à gauche (contenu qui aide
 * le visiteur à préparer un message utile), form à droite. Sur mobile,
 * empilé — l'aside passe au-dessus pour ancrer l'intention avant l'action.
 */
.contact-page__split {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
  align-items: start;
}

@media (min-width: 960px) {
  .contact-page__split {
    grid-template-columns: minmax(0, 4fr) minmax(0, 7fr);
    gap: var(--space-10);
  }
}

@media (min-width: 1200px) {
  .contact-page__split {
    grid-template-columns: minmax(0, 4fr) minmax(0, 8fr);
    gap: var(--space-16);
  }
}

.contact-page__aside {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

@media (min-width: 960px) {
  .contact-page__aside {
    position: sticky;
    top: calc(var(--site-header-height) + var(--space-8));
    padding-right: var(--space-4);
  }
}

.contact-page__aside-eyebrow {
  margin: 0;
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.75rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.contact-page__aside-title {
  margin: 0;
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 2.6vw, 1.875rem);
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  text-wrap: balance;
}

.contact-page__aside-lead {
  margin: 0;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  max-width: 40ch;
}

.contact-page__prompts {
  list-style: none;
  margin: var(--space-2) 0 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.contact-page__prompt {
  position: relative;
  padding-left: var(--space-5);
  border-left: 2px solid rgba(12, 91, 87, 0.28);
  padding-block: 0.125rem;
}

.contact-page__prompt-title {
  margin: 0 0 0.25rem;
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1rem;
  line-height: 1.3;
  color: var(--text-primary);
}

.contact-page__prompt-detail {
  margin: 0;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.contact-page__aside-note {
  margin: var(--space-4) 0 0;
  padding-top: var(--space-4);
  border-top: 1px solid rgba(22, 25, 28, 0.12);
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  line-height: 1.55;
  color: var(--text-muted);
  max-width: 42ch;
}

/*
 * Frise « ce qui se passe ensuite » — trois cartes horizontales,
 * chacune avec sa pastille numérotée petrol et son mini-titre. Sur
 * mobile, empilée. Aucune surcharge visuelle : c'est un rappel de
 * confiance qui doit rester secondaire au form.
 */
.contact-page__timeline {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

@media (min-width: 720px) {
  .contact-page__timeline {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-5);
  }
}

/*
 * Cartes « étapes » — surface verre foncé (rgba blanc 3% + bordure fine
 * ton froid). Un halo radial doux se dévoile au hover pour renforcer
 * l'effet projecteur du hero. Le grand numéro mono en accent devzair-blue
 * remplace la pastille carrée pour un rendu plus éditorial.
 *
 * Animation d'entrée : fade + slide-up séquencé via `--step-delay` (injecté
 * par v-for). Respecte prefers-reduced-motion.
 */
.contact-page__step {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-6);
  background:
    radial-gradient(
      circle at 50% 0%,
      rgba(184, 212, 240, 0) 0%,
      rgba(184, 212, 240, 0) 100%
    ),
    rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  overflow: hidden;
  transition:
    transform 320ms cubic-bezier(0.22, 0.61, 0.36, 1),
    border-color 320ms ease,
    background 320ms ease,
    box-shadow 320ms ease;
  opacity: 0;
  transform: translateY(14px);
  animation: contact-step-enter 640ms cubic-bezier(0.22, 0.61, 0.36, 1) forwards;
  animation-delay: var(--step-delay, 0ms);
  will-change: transform;
}

.contact-page__step:hover {
  transform: translateY(-4px);
  border-color: rgba(184, 212, 240, 0.28);
  background:
    radial-gradient(
      circle at 50% 0%,
      rgba(184, 212, 240, 0.12) 0%,
      rgba(184, 212, 240, 0) 70%
    ),
    rgba(255, 255, 255, 0.05);
  box-shadow: 0 18px 40px -24px rgba(120, 170, 220, 0.4);
}

@keyframes contact-step-enter {
  from {
    opacity: 0;
    transform: translateY(14px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.contact-page__step-number {
  font-family: var(--font-family-mono, ui-monospace, "Space Mono", monospace);
  font-size: 0.875rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: var(--color-devzair-blue);
  margin-bottom: var(--space-2);
}

.contact-page__step-title {
  margin: 0;
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.0625rem;
  line-height: 1.3;
  color: #f4f1ea;
}

.contact-page__step-detail {
  margin: 0;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.55;
  color: rgba(244, 241, 234, 0.65);
}

@media (prefers-reduced-motion: reduce) {
  .contact-page__step {
    opacity: 1;
    transform: none;
    animation: none;
    transition: none;
  }
  .contact-page__step:hover {
    transform: none;
  }
}

/*
 * Form card — surface cream élevée. En split desktop, elle occupe la
 * colonne droite du grid ; sur mobile, elle prend toute la largeur
 * disponible sous l'aside empilé au-dessus.
 */
.contact-page__form-card {
  width: 100%;
  background: #f8f5ef;
  border: 1px solid rgba(22, 25, 28, 0.14);
  border-radius: 20px;
  padding: var(--space-6) var(--space-5);
  box-shadow: 0 24px 60px -34px rgba(22, 25, 28, 0.4);
}

@media (min-width: 640px) {
  .contact-page__form-card {
    padding: var(--space-8) var(--space-8);
  }
}

@media (min-width: 960px) {
  .contact-page__hero-section {
    padding-block: var(--space-12) var(--space-16);
  }
  .contact-page__body-section {
    padding-block: var(--space-16) var(--space-20);
  }
  .contact-page__container {
    gap: var(--space-12);
  }
  .contact-page__form-card {
    padding: var(--space-10) var(--space-10);
  }
}
</style>
