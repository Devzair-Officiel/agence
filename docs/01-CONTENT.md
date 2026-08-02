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

### Contenu

- mission ;
- positionnement ;
- type d’entreprises accompagnées ;
- manière de constituer l’équipe projet ;
- expertises disponibles ;
- valeurs de travail ;
- transparence sur le fonctionnement ;
- zone d’intervention réelle ;
- éléments de preuve vérifiables ;
- CTA.

### Vigilance

Ne pas présenter de faux organigramme. Si Devzair s’appuie sur un réseau de spécialistes, employer une formulation exacte et vérifiable, par exemple : `Nous réunissons les compétences adaptées à chaque projet`.

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
