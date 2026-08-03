# Contenu éditorial et modèles

> Structure attendue des pages, règles de preuve et modèles de contenu.

## 7. Contenu éditorial par page

Les volumes ci-dessous sont des repères de complétude, pas des critères de classement. Le contenu doit répondre à l’intention de l’utilisateur sans remplissage artificiel.

## 7.1 Accueil

### Objectif

Faire comprendre en quelques secondes :

1. ce qu’est Devzair ;
2. pour qui l’agence travaille ;
3. quels problèmes elle résout ;
4. ce qui la différencie ;
5. quelle action effectuer.

### Structure recommandée

1. **Hero**
   - H1 clair ;
   - promesse principale ;
   - phrase de réassurance ;
   - CTA principal ;
   - CTA secondaire vers les réalisations ou services ;
   - visuel professionnel cohérent.

2. **Problèmes clients** — livré par `HomeProblems.vue` (Phase 5B)
   - manque de visibilité sur les recherches qui comptent ;
   - image en ligne qui ne reflète pas le niveau réel ;
   - site lent, peu fluide, peu convaincant ;
   - outils internes désorganisés ou vieillissants ;
   - absence de stratégie durable et de suivi.

   Ton descriptif, jamais anxiogène ; aucun chiffre, aucun témoignage,
   aucun client nommé. La liste est un `<ol>` sémantique (ordre = du plus
   visible au plus structurel). La numérotation Space Mono duplique
   l'ordre logique et reste `aria-hidden`.

3. **Réponse Devzair** — livré par `HomeConnectedApproach.vue` (Phase 5B)
   fusion des points « Solutions » et « Approche globale » du plan éditorial.
   Un parcours en 5 étapes reprend les cinq pôles de
   `app/config/expertise-pillars.ts` (source unique — aucun label dupliqué).
   Rendu sur fond navy, `<ol>` sémantique, flèches ↓ (mobile) / → (≥768px)
   décoratives et `aria-hidden`. La `longDescription` de chaque pôle est
   publiée en `sr-only` pour les lecteurs d'écran, sans surcharger le
   rendu visuel dense.

4. **Cinq pôles détaillés** — livré par `HomeExpertisePillars.vue` (Phase 5B)
   Ancre `#expertises`. Chaque carte publie : label, description courte,
   `longDescription` narrative, 3 `services` (source unique
   `expertise-pillars.ts`). Mobile : carrousel scroll-snap CSS-natif
   (aucun JS, aucun bouton, 5 cartes toujours dans le DOM). Desktop
   ≥1024px : grille asymétrique 3×2 (Concevoir spans 2 rows, Faire
   évoluer reçoit un fond petrol pour marquer la clôture du parcours).

5. **Réalisations ou preuves** — livré par `HomeFeaturedCaseStudy.vue` (Phase 5C)
   - uniquement projets réels ;
   - contexte, intervention, résultat démontrable ;
   - pas de chiffres sans méthode de mesure.

   Tant qu'aucun projet n'est publiable (DEV-003 « À faire »), la section
   publie la variante « état honnête » : eyebrow `Études de cas en
   préparation` et titre `Nos réalisations détaillées seront bientôt
   disponibles.`. Aucun faux client, aucun logo, aucun bouton vers route
   absente. L'ancre `#realisations` reste active pour le CTA hero et la
   nav header (cf. DEC-035).

6. **Méthode** — livré par `HomeProcess.vue` (Phase 5C)
   Six étapes verbatim rendues comme `<ol>` sémantique (l'ordre importe),
   source unique `app/config/project-process.ts` :
   - Découverte ;
   - Cadrage ;
   - Conception ;
   - Développement ;
   - Lancement ;
   - Évolution.

   Mobile : timeline verticale (point + trait via pseudo-éléments décoratifs).
   Tablette ≥640px : grille 2 colonnes. Desktop ≥1024px : grille 3×2 (six
   colonnes écartées pour la lisibilité, cf. DEC-037). Aucune interaction :
   pas d'accordion, pas de tab, pas de bouton.

7. **Pourquoi Devzair** — livré par `HomeTrust.vue` (Phase 5C)
   Cinq promesses verbatim rendues comme `<ul>` (l'ordre n'est pas
   significatif), source unique `app/config/trust-promises.ts` :
   - Approche personnalisée ;
   - Vision globale ;
   - Qualité technique ;
   - Transparence ;
   - Accompagnement durable.

   Aucun superlatif absolu (« meilleur », « unique »). Cartes non
   interactives. Mobile : liste verticale. Tablette ≥640px : 2 colonnes
   avec 5ᵉ carte pleine largeur pour éviter l'orpheline. Desktop ≥1024px :
   grille asymétrique 3+2 centrée.

8. **FAQ éditoriale**
   - questions réellement utiles ;
   - réponses courtes puis approfondissement ;
   - ne pas ajouter de balisage FAQ dans le seul but d’obtenir un résultat enrichi Google.

9. **CTA final** — livré par `HomeCallToAction.vue` (Phase 5D)
   Section ancrée `#contact`, huitième et dernière de l'accueil. Verbatim :
   eyebrow `Parlons de votre projet`, H2 `Construisons une présence
   digitale à la hauteur de votre entreprise.`, paragraphe `Un premier
   échange nous permettra de comprendre votre besoin, de clarifier les
   priorités et de définir une direction adaptée à votre activité.`

   Stratégie de contact conditionnelle :

   - `site.contact.email` défini → un bouton `Nous écrire` (mailto) est
     rendu ;
   - `site.contact.email` null (défaut Phase 5D) → aucun bouton, aucun
     lien mailto, aucun href="#" fictif ;
   - `site.contact.email` null **et** `runtimeConfig.public.siteIndexable`
     à `false` → une notice discrète `Le moyen de contact en ligne sera
     activé avant la mise en production.` s'affiche pour rassurer les
     lecteurs de preprod. Cette notice ne fuit jamais en production.

   Le formulaire de contact complet (validation serveur, anti-spam,
   e-mail transactionnel) est traité en Phase 6.

### H1 de travail

> Des solutions digitales complètes pour rendre votre entreprise visible, crédible et efficace en ligne

À valider lors de la recherche éditoriale et sémantique.

---

## 7.2 Page `/agence`

### État actuel : livrée (Phase 7A)

Route SSR pré-rendue (`nuxt.config.ts::routeRules['/agence']={prerender:true}`),
implémentation `apps/web/app/pages/agence.vue`, suite unitaire
`test/unit/pages/agence.spec.ts` et suite E2E `test/e2e/institutional-pages.spec.ts`.

### Contenu livré

- **H1 verbatim** : « Une agence digitale à taille humaine, pensée pour
  accompagner les entreprises dans leur globalité. »
- **Eyebrow** : `L'agence`.
- **Introduction verbatim** : « Devzair réunit stratégie, design,
  développement, contenus et visibilité afin de construire des solutions
  digitales cohérentes, utiles et évolutives. »
- Trois sections `EditorialSection` : Positionnement, Fonctionnement,
  Valeurs.
- Callout final `EditorialCallout` vers `/expertises` (secondaire vers
  `/#contact`).

### Vigilance appliquée

Aucun organigramme fictif, aucun effectif chiffré, aucune date de
fondation, aucun témoignage anonyme, aucune adresse. Formulation
retenue pour l'équipe : « Nous réunissons les compétences adaptées à
chaque projet ». Test unitaire garde-fou :
`test/unit/pages/agence.spec.ts::"never publishes fictional data"` bloque
`lorem ipsum`, `john doe`, `@example.`, `fondée en \d{4}`,
`plus de \d+ (clients|projets|années)`.

---

## 7.2 bis Page `/expertises` (vue d'ensemble)

### État actuel : livrée (Phase 7A, cartes rendues cliquables en Phase 7B)

Route SSR pré-rendue (`nuxt.config.ts::routeRules['/expertises']={prerender:true}`),
implémentation `apps/web/app/pages/expertises/index.vue`, suite unitaire
`test/unit/pages/expertises.spec.ts` et suite E2E
`test/e2e/institutional-pages.spec.ts`.

### Rôle

Page d'entrée vers les cinq pôles d'expertise Devzair. Depuis la
Phase 7B, les cinq cartes exposent un `<NuxtLink>` vers
`/expertises/{slug}` grâce au patron `v-if="isPublished()"` / `v-else`
d'`ExpertiseOverviewCard` (cf. DEC-062). Aucun lien mort : si la config
d'une entrée bascule un jour à `status !== "published"`, la carte
revient automatiquement en `<article>` non interactif.

### Contenu livré

- **H1 verbatim** : « Cinq pôles complémentaires pour construire une
  présence digitale cohérente. »
- **Eyebrow** : `Nos expertises`.
- **Introduction verbatim** : « Chaque entreprise possède des besoins
  différents. Nous réunissons les expertises adaptées pour concevoir,
  construire, valoriser, rendre visible et faire évoluer votre projet. »
- Section « Notre approche » (`EditorialSection`).
- Grille de cinq `ExpertiseOverviewCard` cliquables (CTA « Découvrir ce
  pôle → » en `--color-petrol`, contraste 6:1 sur crème/sable, cf.
  DEC-063), H3 non lié, description longue verbatim, 3 services par pôle.
- Callout final vers `/agence` (secondaire vers `/#contact`).

### Vigilance appliquée

Aucun chiffre inventé (nombre de projets, nombre de clients, ancienneté),
aucun logo tiers, aucun témoignage. Sources de vérité : la config typée
`app/config/expertise-pages.ts` (5 entrées `status: "published"` depuis
la Phase 7B) et `app/config/expertise-pillars.ts` (contenu narratif
partagé avec l'accueil).

---

## 7.2 ter Pages `/expertises/{slug}` (cinq pages détaillées)

### État actuel : livrées (Phase 7B)

Route dynamique `apps/web/app/pages/expertises/[slug].vue` avec
`createError({ statusCode: 404, fatal: true })` pour tout slug inconnu
(cf. DEC-064 — jamais de fallback silencieux). Cinq entrées
pré-rendues (`nuxt.config.ts::routeRules['/expertises/{slug}']={prerender:true}`
pour chaque slug listé dans `app/config/expertise-pages.ts`) et
`sitemap.ts` alignée. Suites de tests : `test/unit/pages/expertise-slug.spec.ts`,
`test/unit/expertise/*.spec.ts`, `test/unit/config/expertise-pages.spec.ts`,
`test/e2e/expertise-pages.spec.ts`.

### Rôle

Détailler chaque pôle avec un contenu utile à la décision, sans jamais
inventer de client, de chiffre ni de résultat. Les cinq pages partagent
le même patron :

1. `ExpertisePageHero` — eyebrow, H1 verbatim (issu de la config),
   introduction narrative.
2. `EditorialSection` × 2 — approche du pôle, méthodologie.
3. `ExpertiseDeliverables` — liste `<ul>` des livrables concrets.
4. `ExpertiseBenefits` — bénéfices attendus formulés sans superlatif
   (`ExpertiseBenefit[]` typé, `readonly`).
5. `ExpertiseRelatedPillars` — exactement 2 pôles reliés (jamais le pôle
   courant, cf. DEC-066) pour tisser le maillage interne.
6. Fil d'ariane sémantique (`<nav aria-label="Fil d'ariane">`) rendu par
   le composable `useBreadcrumb`.
7. Callout final vers `/#contact`.

### Données structurées

Chaque page émet un JSON-LD `Service` via `useExpertiseServiceSchema`
avec `provider: { "@id": "${origin}/#organization" }` (référence à
l'`Organization` globale posée par `useSiteSchema` au niveau layout,
jamais de duplication — cf. DEC-065).

### Vigilance appliquée

Aucun placeholder, aucun `lorem ipsum`, aucun chiffre inventé, aucun
témoignage anonyme. Le test unitaire
`test/unit/pages/expertise-slug.spec.ts` bloque tout contenu fictif et
toute route inconnue rendue autrement qu'en 404. Le patron
`v-if="isPublished()"` + `<article v-else>` remplace la tentation
d'utiliser `<component :is="...">` pour NuxtLink (impossible : NuxtLink
n'est pas une balise HTML, cf. DEC-062).

---

## 7.3 Page `/methode`

### Étapes

1. échange initial ;
2. analyse du besoin et des utilisateurs ;
3. cadrage du périmètre ;
4. proposition et planification ;
5. architecture et contenus ;
6. maquettes UX/UI ;
7. développement ;
8. contrôles fonctionnels, SEO, sécurité et accessibilité ;
9. mise en ligne ;
10. suivi, maintenance et amélioration.

Pour chaque étape, préciser :

- les objectifs ;
- les livrables ;
- les décisions attendues ;
- les responsabilités ;
- les critères de validation.

---

## 7.4 Page `/services`

### Rôle

Présenter la vision d’ensemble et guider vers la bonne page spécialisée.

### Structure

- introduction centrée sur les besoins ;
- carte de chaque service ;
- liens entre services complémentaires ;
- exemples de livrables ;
- méthode de choix ;
- CTA vers un échange de cadrage.

---

## 7.5 Modèle d’une page service

Chaque page service doit contenir :

1. H1 centré sur le besoin ;
2. introduction claire et spécifique ;
3. problèmes résolus ;
4. profils concernés ;
5. prestations incluses ;
6. déroulement ;
7. livrables ;
8. choix techniques ou méthodologiques expliqués ;
9. preuves ou cas réels ;
10. services complémentaires ;
11. questions fréquentes utiles ;
12. CTA contextualisé.

### Services initiaux

#### Création de site internet

- site vitrine ;
- architecture de contenu ;
- UX/UI ;
- intégration ;
- responsive ;
- CMS si pertinent ;
- performance ;
- SEO technique ;
- autonomie éditoriale ;
- maintenance.

#### Site e-commerce

- catalogue ;
- tunnel de commande ;
- paiement ;
- livraison ;
- gestion des produits ;
- conformité ;
- performance ;
- sécurité ;
- suivi des conversions.

Ne pas publier de promesses de chiffre d’affaires.

#### Application web et métier

- analyse des processus ;
- workflows ;
- rôles et permissions ;
- tableaux de bord ;
- intégrations ;
- API ;
- sécurité ;
- évolutivité ;
- maintenance.

#### Design UI/UX et identité visuelle

- recherche utilisateur ;
- architecture de l’information ;
- wireframes ;
- maquettes ;
- design system ;
- identité ;
- cohérence multicanale ;
- tests d’utilisabilité.

#### Photographie et création de contenu

- photographie professionnelle ;
- portraits, lieux, produits ou réalisations ;
- direction visuelle ;
- contenus web ;
- optimisation des médias ;
- droits d’utilisation et consentements.

#### SEO et référencement naturel

- audit ;
- stratégie sémantique ;
- architecture ;
- optimisation technique ;
- contenu ;
- maillage ;
- données structurées ;
- suivi ;
- amélioration continue.

#### Visibilité locale

- cohérence des informations d’entreprise ;
- fiche d’établissement si l’activité y est éligible ;
- pages locales utiles ;
- avis authentiques ;
- contenus géolocalisés réels ;
- suivi des demandes locales.

#### Maintenance et accompagnement

- mises à jour ;
- sauvegardes ;
- supervision ;
- corrections ;
- sécurité ;
- optimisation ;
- évolutions ;
- rapport périodique selon contrat.

---

## 7.6 Réalisations et études de cas

### Une étude de cas doit préciser

- client ou secteur, selon autorisation ;
- contexte ;
- problème ;
- objectifs ;
- contraintes ;
- méthode ;
- services mobilisés ;
- solution ;
- captures et visuels autorisés ;
- résultats mesurables ;
- période de mesure ;
- outils de mesure ;
- limites ;
- témoignage uniquement avec accord ;
- CTA vers un service pertinent.

### Règles de preuve

- distinguer résultat mesuré, observation et estimation ;
- ne pas attribuer toute évolution au seul travail de Devzair sans preuve ;
- ne pas publier de données confidentielles ;
- anonymiser lorsque nécessaire ;
- indiquer `projet interne` pour les démonstrateurs internes.

---

## 7.7 Ressources et stratégie éditoriale

### Piliers éditoriaux

- création et refonte de site ;
- e-commerce ;
- applications métier ;
- UX/UI et image de marque ;
- SEO technique et éditorial ;
- visibilité locale ;
- contenu et photographie ;
- sécurité, performance et maintenance ;
- choix d’outils et retour d’expérience.

### Types de contenus

- guides pratiques ;
- comparatifs argumentés ;
- checklists ;
- réponses à des questions précises ;
- études de cas ;
- analyses de méthodes ;
- glossaire métier ;
- retours d’expérience vérifiables.

### Exigences

- auteur ou responsable éditorial identifiable ;
- date de publication et de mise à jour ;
- sources primaires pour les affirmations techniques, juridiques ou chiffrées ;
- distinction claire entre fait, conseil et opinion ;
- contenu original et utile ;
- révision périodique ;
- suppression ou redirection des contenus obsolètes.

---

## 7.8 Contact et demande de devis

### Formulaire MVP

Champs minimaux :

- nom ;
- entreprise, facultatif selon cible ;
- adresse e-mail ;
- téléphone facultatif ;
- type de projet ;
- message ;
- budget ou échéance uniquement si utile et expliqué ;
- fichier facultatif uniquement si la sécurité d’upload est correctement mise en place.

### UX

- labels visibles ;
- messages d’erreur précis ;
- conservation des champs valides après erreur ;
- confirmation claire ;
- délai de réponse annoncé seulement s’il peut être tenu ;
- alternative de contact ;
- notice de confidentialité près du formulaire.

### Sécurité

- validation serveur ;
- limitation de débit ;
- protection anti-spam progressive ;
- contrôle des en-têtes et injections e-mail ;
- CSRF lorsque pertinent ;
- aucune donnée sensible demandée ;
- durée de conservation définie ;
- journalisation sans contenu excessif.

---

## 17. Modèle de contenu

Prévoir des types structurés plutôt que des pages entièrement libres.

### Service

- titre ;
- slug ;
- résumé ;
- problème ;
- publics ;
- bénéfices ;
- prestations ;
- livrables ;
- méthode ;
- preuves ;
- FAQ éditoriale ;
- CTA ;
- SEO title ;
- meta description ;
- canonical ;
- image sociale ;
- indexabilité ;
- date de mise à jour.

### Étude de cas

- titre ;
- client ou anonymisation ;
- secteur ;
- contexte ;
- objectifs ;
- contraintes ;
- services ;
- solution ;
- résultats ;
- période et méthode de mesure ;
- visuels ;
- citation autorisée ;
- liens ;
- date ;
- SEO.

### Article

- titre ;
- slug ;
- résumé ;
- auteur réel ;
- relecteur éventuel ;
- date de publication ;
- date de mise à jour ;
- corps structuré ;
- sources ;
- sujets ;
- service lié ;
- image ;
- SEO ;
- statut éditorial.

### Membre ou contributeur

Seulement pour des personnes réelles ayant autorisé la publication :

- nom ;
- rôle exact ;
- biographie ;
- expertise ;
- photo ;
- profils ;
- contributions.

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
