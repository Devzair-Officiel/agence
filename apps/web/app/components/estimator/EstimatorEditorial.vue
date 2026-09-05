<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import BaseLink from "~/components/base/BaseLink.vue"

// SSR editorial — rendu après EstimatorShell, jamais avant.
// Aucun JS client. Toutes les variables CSS viennent de tokens.css.
</script>

<template>
  <section class="ee" aria-label="Comprendre le budget d'un projet web">

    <!-- A — Pourquoi une fourchette -->
    <div class="ee__band ee__band--fourchette">
      <BaseContainer width="wide">
        <div class="ee__fourchette">
          <div class="ee__fourchette-main">
            <h2 class="ee__h2">
              Pourquoi une fourchette plutôt qu'un tarif fixe ?
            </h2>
            <p class="ee__para">
              Un site vitrine simple et une application métier sur mesure ne
              demandent pas le même travail, ni le même temps. Annoncer un
              prix unique serait trompeur. Une fourchette est plus honnête :
              elle reflète ce qui varie réellement selon votre besoin —
              périmètre, contenus, fonctionnalités, niveau de finition.
            </p>
            <p class="ee__para">
              L'estimation que vous obtenez ici est indicative. Elle vous
              donne un ordre de grandeur avant toute discussion, sans vous
              engager à quoi que ce soit. Le devis définitif arrive après un
              cadrage réel, une fois le projet bien compris.
            </p>
          </div>
          <aside class="ee__retenir" aria-label="À retenir">
            <BaseEyebrow class="ee__retenir-label">À retenir</BaseEyebrow>
            <p class="ee__retenir-text">
              Une estimation donne un ordre de grandeur honnête. Elle ne
              remplace pas le cadrage — elle le précède.
            </p>
          </aside>
        </div>
      </BaseContainer>
    </div>

    <!-- B — Ce qui fait varier le budget -->
    <div class="ee__band ee__band--facteurs">
      <BaseContainer width="wide">
        <header class="ee__facteurs-header">
          <h2 class="ee__h2">
            Ce qui fait réellement varier le budget d'un projet web
          </h2>
        </header>
        <div class="ee__facteurs-grid">
          <div class="ee__facteur">
            <span class="ee__facteur-num" aria-hidden="true">01</span>
            <h3 class="ee__facteur-title">Le périmètre fonctionnel</h3>
            <p class="ee__facteur-text">
              Un formulaire de contact ne représente pas le même effort qu'un
              espace client avec gestion de commandes. Chaque fonctionnalité
              supplémentaire a un coût en temps de développement et en tests.
            </p>
          </div>
          <div class="ee__facteur">
            <span class="ee__facteur-num" aria-hidden="true">02</span>
            <h3 class="ee__facteur-title">Les contenus</h3>
            <p class="ee__facteur-text">
              Qui les produit, dans quel format, en combien de pages. Un
              projet avec textes et photos prêts est moins long à livrer qu'un
              projet où tout est à créer.
            </p>
          </div>
          <div class="ee__facteur">
            <span class="ee__facteur-num" aria-hidden="true">03</span>
            <h3 class="ee__facteur-title">Le design</h3>
            <p class="ee__facteur-text">
              Partir d'une identité visuelle existante ou en créer une n'implique
              pas le même investissement. La complexité des maquettes et le
              niveau de personnalisation influencent le temps de travail.
            </p>
          </div>
          <div class="ee__facteur">
            <span class="ee__facteur-num" aria-hidden="true">04</span>
            <h3 class="ee__facteur-title">Les intégrations techniques</h3>
            <p class="ee__facteur-text">
              Connexion à un CRM, à un système de réservation, à un paiement en
              ligne — chaque intégration externe demande du travail d'adaptation
              et de test.
            </p>
          </div>
          <div class="ee__facteur">
            <span class="ee__facteur-num" aria-hidden="true">05</span>
            <h3 class="ee__facteur-title">Les besoins dans le temps</h3>
            <p class="ee__facteur-text">
              Un projet maintenu et fait évoluer régulièrement n'a pas le même
              coût total qu'un site livré une fois et laissé sans suivi.
            </p>
          </div>
        </div>
      </BaseContainer>
    </div>

    <!-- C — Principe Devzair -->
    <div class="ee__band ee__band--principe">
      <BaseContainer width="wide">
        <div class="ee__principe">
          <div class="ee__principe-text">
            <h2 class="ee__h2 ee__h2--inverse">
              Investir là où ça compte, simplifier ce qui n'est pas essentiel
            </h2>
            <p class="ee__para ee__para--inverse">
              Un site vitrine simple n'a pas besoin de quinze fonctionnalités.
              Avant de définir le périmètre d'un projet, nous identifions ce
              qui est réellement utile à votre activité — et ce qui peut
              attendre ou être simplifié sans affecter le résultat.
            </p>
            <p class="ee__para ee__para--inverse">
              L'objectif n'est pas de proposer le moins cher possible, mais de
              concentrer l'effort là où il produit une différence concrète : un
              site qui permet à un visiteur de comprendre ce que vous faites,
              de vous faire confiance et de vous contacter.
            </p>
          </div>
          <aside class="ee__principe-aside">
            <p class="ee__principe-aside-label">
              Vous avez un besoin de site vitrine professionnel ?
            </p>
            <BaseLink
              to="/services/creation-site-internet"
              tone="inverse"
            >
              Voir le service création de site
            </BaseLink>
          </aside>
        </div>
      </BaseContainer>
    </div>

    <!-- D — FAQ -->
    <div class="ee__band ee__band--faq">
      <BaseContainer width="wide">
        <h2 class="ee__h2 ee__h2--faq">
          Questions fréquentes sur le budget d'un projet web
        </h2>
        <dl class="ee__faq">
          <div class="ee__faq-item">
            <dt class="ee__faq-q">
              Le devis sera-t-il identique à l'estimation obtenue ici ?
            </dt>
            <dd class="ee__faq-a">
              Non. L'estimation est indicative. Un devis est établi après un
              cadrage réel du projet — une fois que les besoins, les contraintes
              et le périmètre ont été clarifiés ensemble. L'estimation vous donne
              un ordre de grandeur avant cette étape.
            </dd>
          </div>
          <div class="ee__faq-item">
            <dt class="ee__faq-q">
              Peut-on réduire le budget sans sacrifier la qualité ?
            </dt>
            <dd class="ee__faq-a">
              Oui, en simplifiant le périmètre. Moins de pages, contenus fournis
              par le client, design basé sur une identité existante — ces choix
              réduisent le temps de travail sans affecter la qualité technique ni
              le soin apporté au projet.
              <BaseLink
                to="/ressources/site-internet-pas-cher"
                class="ee__faq-link"
              >
                Voir ce qu'il est possible de simplifier sans sacrifier l'essentiel
              </BaseLink>
            </dd>
          </div>
          <div class="ee__faq-item">
            <dt class="ee__faq-q">
              Je ne sais pas encore quel type de projet j'ai besoin — est-ce
              que ça bloque l'estimation ?
            </dt>
            <dd class="ee__faq-a">
              Non. Le configurateur propose l'option « Je ne sais pas encore »,
              qui adapte le questionnaire pour vous aider à mieux cerner votre
              besoin et vous donner une fourchette de référence.
            </dd>
          </div>
          <div class="ee__faq-item">
            <dt class="ee__faq-q">
              Cette estimation m'engage-t-elle à quelque chose ?
            </dt>
            <dd class="ee__faq-a">
              Non. L'estimation est gratuite et sans engagement. Elle est un
              point de départ pour une conversation, pas un contrat.
            </dd>
          </div>
        </dl>
      </BaseContainer>
    </div>

  </section>
</template>

<style scoped>
/* ─────────────────────────────────────────────
   Structure racine
───────────────────────────────────────────── */
.ee {
  border-top: 1px solid var(--border-default);
}

/* ─────────────────────────────────────────────
   Bandes visuelles — chacune a son fond
───────────────────────────────────────────── */
.ee__band {
  padding-block: var(--space-16);
}

.ee__band--fourchette {
  background: var(--background-secondary);
}

.ee__band--facteurs {
  background: var(--surface-primary);
}

.ee__band--principe {
  background: var(--background-inverse);
}

.ee__band--faq {
  background: var(--background-primary);
}

@media (min-width: 768px) {
  .ee__band {
    padding-block: var(--space-20);
  }
}

/* ─────────────────────────────────────────────
   Typographie partagée
───────────────────────────────────────────── */
.ee__h2 {
  font-family: var(--font-family-heading);
  font-size: clamp(1.375rem, 3vw, 2rem);
  font-weight: var(--font-weight-heading);
  line-height: 1.15;
  letter-spacing: -0.02em;
  color: var(--text-primary);
  margin: 0 0 var(--space-6);
}

.ee__h2--inverse {
  color: var(--text-inverse);
}

.ee__h2--faq {
  margin-bottom: var(--space-8);
}

.ee__para {
  font-size: var(--font-size-base, 1rem);
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0 0 var(--space-4);
  max-width: 62ch;
}

.ee__para--inverse {
  color: var(--text-inverse-muted);
}

/* ─────────────────────────────────────────────
   A — Pourquoi une fourchette
───────────────────────────────────────────── */
.ee__fourchette {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
  align-items: start;
}

.ee__fourchette-main {
  min-width: 0;
}

.ee__retenir {
  background: var(--background-primary);
  border-left: 3px solid var(--color-petrol);
  border-radius: 0 var(--radius-md) var(--radius-md) 0;
  padding: var(--space-6) var(--space-6) var(--space-6) var(--space-5);
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.ee__retenir-label {
  margin: 0;
}

.ee__retenir-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-primary);
  margin: 0;
  font-weight: var(--font-weight-body-strong);
}

@media (min-width: 900px) {
  .ee__fourchette {
    grid-template-columns: 1fr 18rem;
    gap: var(--space-12);
  }

  .ee__retenir {
    align-self: start;
    position: sticky;
    top: calc(var(--site-header-height) + var(--space-6));
  }
}

/* ─────────────────────────────────────────────
   B — Ce qui fait varier
───────────────────────────────────────────── */
.ee__facteurs-header {
  max-width: 48ch;
  margin-bottom: var(--space-10);
}

.ee__facteurs-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
}

.ee__facteur {
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
  display: grid;
  grid-template-columns: 3rem 1fr;
  grid-template-rows: auto auto;
  column-gap: var(--space-5);
  row-gap: var(--space-1);
}

.ee__facteur-num {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  line-height: 1.8;
  grid-column: 1;
  grid-row: 1;
}

.ee__facteur-title {
  font-family: var(--font-family-heading);
  font-size: clamp(0.9375rem, 1.4vw, 1.0625rem);
  font-weight: var(--font-weight-heading-medium);
  color: var(--text-primary);
  margin: 0;
  line-height: 1.3;
  letter-spacing: -0.01em;
  grid-column: 2;
  grid-row: 1;
}

.ee__facteur-text {
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  grid-column: 2;
  grid-row: 2;
}

@media (min-width: 768px) {
  .ee__facteurs-grid {
    grid-template-columns: 1fr 1fr;
    column-gap: var(--space-10);
  }

  /* Dernier item (5e) en pleine largeur si impair */
  .ee__facteur:last-child:nth-child(odd) {
    grid-column: 1 / -1;
  }
}

/* ─────────────────────────────────────────────
   C — Principe Devzair (navy)
───────────────────────────────────────────── */
.ee__principe {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: start;
}

.ee__principe-text {
  min-width: 0;
}

.ee__principe-aside {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  padding: var(--space-6);
  border: 1px solid var(--border-inverse);
  border-radius: var(--radius-md);
}

.ee__principe-aside-label {
  font-size: 0.875rem;
  line-height: 1.5;
  color: var(--text-inverse-muted);
  margin: 0;
}

@media (min-width: 900px) {
  .ee__principe {
    grid-template-columns: 1fr 22rem;
    gap: var(--space-16);
  }

  .ee__principe-aside {
    align-self: center;
  }
}

/* ─────────────────────────────────────────────
   D — FAQ
───────────────────────────────────────────── */
.ee__faq {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
  margin: 0;
}

.ee__faq-item {
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.ee__faq-q {
  font-family: var(--font-family-heading);
  font-size: clamp(0.9375rem, 1.4vw, 1.0625rem);
  font-weight: var(--font-weight-heading-medium);
  color: var(--text-primary);
  line-height: 1.4;
  letter-spacing: -0.01em;
  margin: 0 0 var(--space-3);
}

.ee__faq-a {
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

.ee__faq-link {
  display: block;
  margin-top: var(--space-3);
  font-size: 0.875rem;
}

@media (min-width: 768px) {
  .ee__faq {
    grid-template-columns: 1fr 1fr;
    column-gap: var(--space-12);
  }
}

/* ─────────────────────────────────────────────
   Reduced motion
───────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
  .ee__retenir {
    transition: none;
  }
}
</style>
