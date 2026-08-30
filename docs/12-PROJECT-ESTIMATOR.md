# Estimateur / Configurateur de Projet Devzair

> Source de vérité fonctionnelle du sous-projet « Estimer votre projet ».
> EST-0 — Cadrage et documentation : **TERMINÉ** (2026-08-29).
> EST-1A — Contrat métier : **TERMINÉ** (2026-08-29).
> EST-1B — Calibration + moteur : **TERMINÉ** (2026-08-30). Q-01 validée. Grille `2026-v1` active.
> EST-1C — API HTTP : **TERMINÉ** (2026-08-30). `POST /api/estimate` opérationnel, recette 16 critères validée.
> EST-2 — Shell UX du configurateur : **Implémentation technique livrée — validation visuelle requise** (2026-08-30).
> Prochaine phase : **EST-3 — Questionnaires conditionnels**.

---

## 1. Vision

L'estimateur permet à un prospect de qualifier son projet, de comprendre les contours d'un accompagnement Devzair et d'obtenir une estimation budgétaire indicative — le tout avant tout échange commercial.

L'outil prépare la conversation.  
Il ne remplace pas le cadrage humain.  
Il ne réduit pas Devzair à un générateur automatique de sites ni à un comparateur de prix.

Devzair reste une **agence digitale à taille humaine** qui livre des solutions sur mesure avec accompagnement personnalisé.

---

## 2. Objectifs

- Permettre à un prospect non technique de formaliser son besoin.
- Qualifier le type de projet et le niveau de complexité avant tout rendez-vous.
- Fournir une fourchette budgétaire indicative (MIN / MAX) honnête et non contractuelle.
- Distinguer investissement initial et accompagnement récurrent.
- Proposer des modalités de règlement adaptées.
- Ouvrir une voie vers la transmission du brief ou la prise de contact.
- Ouvrir une voie distincte vers une proposition de partenariat (sous réserve de validation humaine).

---

## 3. Non-objectifs

- Ne pas remplacer le cadrage commercial ou technique.
- Ne pas afficher un prix ferme ou contractuel.
- Ne pas traiter un paiement réel, un prélèvement ou un crédit.
- Ne pas calculer automatiquement une équité, un revenue share ou une valorisation.
- Ne pas constituer une offre commerciale.
- Ne pas présenter Devzair comme un service low-cost ou un freelance.
- Ne pas stocker de données personnelles identifiantes avant que le prospect ait choisi de les fournir.

---

## 4. Principes validés

### A — Résultat en fourchette

L'estimation est présentée sous la forme **MIN — MAX**.  
Aucun montant artificiellement précis n'est affiché.

### B — Estimation visible avant collecte d'email

Le résultat (fourchette, résumé, modalités) est visible immédiatement.  
L'email n'est demandé que si le prospect souhaite recevoir ou transmettre son estimation.

### C — Coordonnées après résultat uniquement

Les données personnelles identifiantes (PII) ne sont collectées qu'après affichage du résultat, et uniquement si l'utilisateur choisit explicitement de les fournir.

### D — Séparation investissement initial / récurrent

L'écran de résultat distingue strictement :

- **Investissement initial** : conception, développement, intégration, mise en ligne.
- **Accompagnement récurrent** : maintenance, hébergement, SEO continu, mises à jour, support.

### E — Paiement échelonné = répartition du montant

Le paiement en plusieurs fois est présenté comme une **simulation de répartition du montant** sur une durée.

Ne pas concevoir :
- crédit ou intérêts ;
- financement bancaire ;
- prélèvement automatique ;
- paiement réel en ligne.

Ces modalités sont exclusivement présentées comme des pistes à discuter lors du cadrage.

### F — Partenariat = candidature soumise à validation humaine

Le partenariat n'est pas un mode de paiement automatique.  
Il constitue une **proposition soumise à validation humaine** dans un parcours dédié (voir §13).

### G — Aucun calcul automatique de partenariat

Aucun pourcentage d'équité, de revenue share, de valorisation ou de conditions financières n'est calculé automatiquement.

### H — Aucun tarif réel inventé

**Critique.** Aucun montant Devzair de production ne doit être inventé.  
Toute valeur tarifaire absente est marquée **À VALIDER**.

### I — Fixtures de test séparées des tarifs réels

Les futurs tests du moteur utilisent des fixtures tarifaires explicitement marquées **TEST**.  
Ces fixtures ne sont jamais confondues avec les tarifs Devzair réels.

### J — Moteur tarifaire côté serveur

Le moteur d'estimation final est **autoritaire côté serveur** (Symfony).  
Le bundle Vue ne contient pas la source de vérité du prix final.

---

## 5. Utilisateurs cibles

| Profil | Besoin principal |
|---|---|
| TPE / PME | Comprendre le budget d'un premier site ou d'une refonte |
| Commerçant | Évaluer un e-commerce ou une présence locale |
| Indépendant / professionnel | Obtenir une fourchette avant de décider |
| Porteur de projet | Qualifier son besoin avant un premier rendez-vous |
| Prospect non technique | Formaliser un objectif sans connaître la solution |
| Porteur de projet avec partenariat | Proposer une collaboration sans pouvoir investir à ce stade |

---

## 6. Parcours général (flux principal)

Le parcours est linéaire, avec des branches conditionnelles selon le type de projet.  
La durée estimée est de **5 à 10 minutes** pour un parcours complet.

```
01 → Projet / objectif
02 → Situation actuelle
03 → Périmètre
04 → Fonctionnalités
05 → Identité / contenus
06 → Visibilité
07 → Suivi après lancement
     ↓
     RÉSULTAT
     ↓
     [Optionnel] Transmettre le brief / Être recontacté / Partenariat
```

### Étape 01 — Projet / objectif

**Questions :**
- Quel type de projet envisagez-vous ?
  - Site vitrine
  - E-commerce
  - Application métier
  - Refonte / amélioration d'un existant
  - Autre
  - Je ne sais pas encore → branche objectif-first (§7)

**Note éditoriale :** La branche « Je ne sais pas encore » part de l'objectif (ce que le prospect veut accomplir) et recommande un type de projet à la fin. Elle ne bloque pas le parcours.

---

### Étape 02 — Situation actuelle

**Questions :**
- Avez-vous déjà un site ou une solution en ligne ?
  - Oui, je veux l'améliorer ou le remplacer
  - Non, c'est un projet de création
  - Je ne sais pas encore
- Quel est l'horizon de votre projet ?
  - Dans les 3 mois
  - Dans 3 à 6 mois
  - Dans 6 à 12 mois
  - Je ne sais pas encore

**Pertinence :** L'horizon influe sur la priorisation mais pas nécessairement sur la fourchette budgétaire. Si l'horizon ne change pas l'estimation, ne pas le conserver (principe KISS).

---

### Étape 03 — Périmètre

Questions adaptées au type de projet sélectionné (voir §8).

---

### Étape 04 — Fonctionnalités

Questions adaptées au type de projet sélectionné (voir §8).

---

### Étape 05 — Identité / contenus

**Questions communes :**
- Avez-vous une charte graphique ou une identité visuelle existante ?
  - Oui, complète (logo, couleurs, typographies)
  - Partielle (logo seulement ou éléments épars)
  - Non, à créer de zéro
  - Je ne sais pas encore
- Les contenus textes (pages, descriptions) sont-ils :
  - Prêts à livrer
  - À rédiger par Devzair
  - À rédiger avec notre aide
  - Je ne sais pas encore
- Des photographies professionnelles sont-elles nécessaires ?
  - Non
  - Oui, pour présenter l'activité / l'équipe / les produits
  - Je ne sais pas encore

---

### Étape 06 — Visibilité

**Questions communes :**
- Souhaitez-vous une optimisation pour les moteurs de recherche (SEO) ?
  - Oui, c'est une priorité
  - Oui, basique
  - Non / plus tard
  - Je ne sais pas encore
- Avez-vous des besoins de visibilité locale (Google Business, zone géographique) ?
  - Oui
  - Non
  - Je ne sais pas encore

---

### Étape 07 — Suivi après lancement

**Questions communes :**
- Souhaitez-vous un accompagnement après la mise en ligne ?
  - Oui, maintenance et mises à jour régulières
  - Oui, hébergement uniquement
  - Oui, SEO continu
  - Non, je gère en autonomie
  - Je ne sais pas encore

---

### Résultat

Affichage immédiat de l'écran de résultat (voir §9).  
Aucun email requis à ce stade.

---

## 7. Arbre de décision — branche « Je ne sais pas encore »

Lorsque le prospect ne sait pas quel type de projet il envisage, le parcours part de son **objectif**.

### Questions objectif-first

1. Qu'est-ce que vous souhaitez accomplir ?
   - Présenter mon activité / mon entreprise en ligne
   - Vendre des produits ou des services en ligne
   - Obtenir davantage de demandes de contact ou de rendez-vous
   - Digitaliser ou automatiser un processus interne
   - Améliorer un site ou une solution existante
   - Améliorer ma visibilité sur les moteurs de recherche
   - Plusieurs de ces objectifs
   - Autre (champ libre optionnel)

2. Qui sont vos utilisateurs principaux ?
   - Des clients particuliers (B2C)
   - Des clients professionnels (B2B)
   - Des collaborateurs internes
   - Un mélange
   - Je ne sais pas encore

3. Avez-vous déjà une solution en ligne ?
   - Oui, je veux l'améliorer
   - Non, c'est une création
   - Je ne sais pas encore

### Recommandation finale (branche objectif-first)

En fonction des réponses :

| Objectif dominant | Recommandation |
|---|---|
| Présenter l'activité | Site vitrine |
| Vendre en ligne | E-commerce |
| Davantage de demandes | Site vitrine avec formulaires |
| Digitaliser un processus | Application métier |
| Améliorer l'existant | Refonte |
| Améliorer la visibilité | SEO / site vitrine selon l'existant |
| Plusieurs objectifs | Cadrage humain recommandé |

Le prospect est informé de la recommandation avant de poursuivre le questionnaire.  
Il peut l'accepter ou choisir un autre type manuellement.

---

## 8. Questions par type de projet

### 8.1 Site vitrine

| Dimension | Questions |
|---|---|
| Ampleur | Nombre de pages estimé (1–5, 6–15, +15, je ne sais pas) |
| Formulaires | Contact simple, multi-étapes, prise de rendez-vous |
| Prise de RDV | Intégration d'un outil de réservation (Calendly ou équivalent) |
| Multilingue | Oui / Non / Plus tard |
| Administration | Besoin d'un back-office pour modifier le contenu soi-même |
| Intégrations | CRM, emailing, outils tiers (À préciser) |
| Contenus | Prêts / À rédiger / Avec aide |
| Photographie | Non nécessaire / Reportage professionnel |
| SEO | Basique / Avancé / Local |
| Maintenance | Oui / Non / Je ne sais pas |

Questions à éliminer si elles ne changent pas l'estimation : À VALIDER lors de la calibration EST-1.

### 8.2 E-commerce

| Dimension | Questions |
|---|---|
| Taille du catalogue | < 50 produits / 50–500 / 500+ / Je ne sais pas |
| Variantes | Taille, couleur, option (Oui / Non / Je ne sais pas) |
| Paiement | CB en ligne, virement, autre (passerelle : À VALIDER) |
| Livraison | Livraison physique / Numérique / Les deux / Je ne sais pas |
| Comptes clients | Oui (espace client) / Non |
| Suivi des commandes | Oui / Non / Je ne sais pas |
| Gestion des stocks | Oui / Non / Intégration ERP (À VALIDER) |
| Promotions | Codes promo, soldes (Oui / Non / Plus tard) |
| Intégrations / API | Marketplace, ERP, comptabilité (À préciser) |
| Multilingue | Oui / Non / Plus tard |
| Contenus | Prêts / À rédiger / Avec aide |
| SEO | Basique / Avancé / Local |
| Maintenance | Oui / Non / Je ne sais pas |

### 8.3 Application métier

| Dimension | Questions |
|---|---|
| Utilisateurs | Combien d'utilisateurs simultanés estimés |
| Rôles | Plusieurs niveaux d'accès (Oui / Non / Je ne sais pas) |
| Authentification | Simple / SSO / LDAP / Je ne sais pas |
| Workflows | Validation, approbation, tâches séquentielles |
| Données | Volume et nature des données manipulées |
| Documents | Génération / signature / archivage (Oui / Non) |
| Notifications | Email, in-app, SMS (préciser) |
| Imports / exports | Excel, CSV, API (Oui / Non / À préciser) |
| API / intégrations | Systèmes existants à connecter |
| Administration | Back-office admin pour gérer les utilisateurs / données |
| Maintenance | Oui / Non / Je ne sais pas |

### 8.4 Refonte / amélioration d'un existant

| Dimension | Questions |
|---|---|
| Existant | Technologie actuelle (WordPress, autre, inconnu) |
| Problème actuel | Performance / Accessibilité / Design vieilli / Fonctionnalités manquantes / SEO / Autre |
| Conservation | Conserver le contenu existant (Oui / Partiellement / Non) |
| Migration | Migration de données (Oui / Non / Je ne sais pas) |
| Contenu | Reprendre / Réécrire / Compléter |
| Fonctionnalités | Ajouter / Supprimer / Conserver |
| SEO | Préserver le référencement actuel (Oui, prioritaire / Non / Je ne sais pas) |
| Intégrations | Connecteurs existants à conserver ou remplacer |

### 8.5 Branche « Je ne sais pas encore »

Voir §7. Le questionnaire objectif-first remplace les étapes 03 et 04.

---

## 9. Écran de résultat

L'écran est affiché immédiatement après la dernière étape, sans collecte d'email préalable.

### Structure de l'écran résultat

---

#### TYPE / RECOMMANDATION

Exemple : **Site vitrine avec prise de rendez-vous**  
*(Le libellé exact dépend des réponses — non inventé ici.)*

---

#### RÉSUMÉ DE VOTRE PROJET

- **Objectif :** [synthèse de l'étape 01 / objectif-first]
- **Périmètre :** [synthèse de l'étape 03]
- **Fonctionnalités :** [synthèse de l'étape 04]
- **Contenus :** [synthèse de l'étape 05]
- **Visibilité :** [synthèse de l'étape 06]
- **Suivi :** [synthèse de l'étape 07]

---

#### ESTIMATION INDICATIVE

**[À VALIDER] €** — **[À VALIDER] €**

> Estimation indicative, non contractuelle.  
> Elle sera confirmée et ajustée après un échange et un cadrage avec notre équipe.  
> *(Texte juridique final : À VALIDER)*

---

#### INVESTISSEMENT INITIAL

Conception, développement, intégration, contenus, mise en ligne.

*Détail ligne à ligne (exemples de structure, montants À VALIDER) :*

| Prestation | Fourchette |
|---|---|
| Conception et design | À VALIDER |
| Développement | À VALIDER |
| Intégration des contenus | À VALIDER |
| SEO initial | À VALIDER |
| Mise en ligne | À VALIDER |
| ... | ... |

---

#### ACCOMPAGNEMENT RÉCURRENT

Maintenance, hébergement, SEO continu, mises à jour.

*Détail ligne à ligne (exemples de structure, montants À VALIDER) :*

| Prestation | Fréquence | Fourchette |
|---|---|---|
| Hébergement | Mensuel / Annuel | À VALIDER |
| Maintenance et mises à jour | Mensuel | À VALIDER |
| SEO continu | Mensuel | À VALIDER |
| ... | ... | ... |

---

#### MODALITÉS ENVISAGEABLES

- **Règlement standard** : acompte à la commande, solde à la livraison (conditions À VALIDER).
- **Paiement échelonné** : répartition du montant sur [N] mois (simulation — aucun crédit ni intérêt, conditions À VALIDER).
- **Solution adaptée** : à discuter lors du cadrage selon la nature du projet.

---

#### ACTIONS

- **Parler de votre projet** → formulaire de contact ou prise de rendez-vous.
- **Recevoir / transmettre cette estimation** → saisie de l'email (optionnelle, après affichage).

*(Libellés CTA exacts : À VALIDER)*

---

## 10. Modèle d'estimation

### 10.1 Principe

Le moteur d'estimation prend en entrée un objet `ProjectEstimateInput` (ou équivalent) et retourne un objet `EstimateResult` (ou équivalent).

**Entrée indicative :**

```
ProjectEstimateInput {
  projectType          // site-vitrine | ecommerce | application | refonte | unknown
  objectives[]         // liste des objectifs cochés
  currentSituation     // creation | refonte | unknown
  scope {}             // réponses étape périmètre (selon projectType)
  features {}          // réponses étape fonctionnalités (selon projectType)
  identity {}          // charte, contenus, photos
  visibility {}        // SEO, local
  afterLaunch {}       // maintenance, récurrent
  horizon             // 0-3m | 3-6m | 6-12m | unknown
}
```

**Sortie indicative :**

```
EstimateResult {
  recommendedProject    // type recommandé après scoring
  minAmount             // montant plancher (À VALIDER — aucune valeur inventée ici)
  maxAmount             // montant plafond (À VALIDER — aucune valeur inventée ici)
  oneOffItems[]         // lignes de l'investissement initial
  recurringItems[]      // lignes de l'accompagnement récurrent
  assumptions[]         // hypothèses du calcul (ex. "Contenus fournis par le client")
  pricingVersion        // identifiant de la version tarifaire utilisée
}
```

*Les noms exacts des objets sont indicatifs et seront confirmés lors de EST-1.*

### 10.2 Règles du moteur

- Le moteur est **autoritaire côté serveur** (Symfony).
- Le bundle Vue **ne contient pas** les grilles tarifaires.
- Les montants calculés côté serveur sont transmis à Nuxt après validation.
- Le navigateur reçoit uniquement le résultat formaté, jamais la grille brute.
- Un `pricingVersion` est systématiquement attaché au résultat (voir §15).

### 10.3 Calibration

**Aucun tarif réel n'est défini dans ce document.**  
La calibration des montants MIN / MAX est réalisée lors de **EST-1** par l'équipe Devzair.  
Tous les montants sont marqués **À VALIDER** jusqu'à cette étape.

### 10.4 Contrat sémantique Unknown / Other — signal EST-4

Lorsque le moteur ne peut pas inférer un type de projet fiable (`ProjectType::Unknown` ou `ProjectType::Other` sans objectifs déterminants, ou avec objectifs conflictuels), il retourne obligatoirement l'assumption :

```
unknown_project_requires_human_scoping
```

**Fourchette fallback (valeur technique) :**

La fourchette retournée dans ce cas — correspondant à l'étendue complète des types connus [900 € — 4 000 €] — est une **valeur technique** permettant de satisfaire le contrat `EstimateResult`, qui exige un `EstimateRange` non nul. Elle ne constitue **pas** une estimation commerciale fiable et ne doit jamais être présentée comme telle.

**Inférence déterministe et conflits :**

Le moteur utilise deux niveaux de signal pour tenter l'inférence :

- **Fort** : `SellOnline` → Ecommerce · `DigitalizeProcess` → BusinessApp
- **Faible** : `PresentBusiness | GenerateLeads | ImproveVisibility` → VitrineSite (cède aux signaux forts)

Si plusieurs familles fortes distinctes sont présentes (ex. `SellOnline + DigitalizeProcess`), le moteur détecte un conflit et retourne `unknown_project_requires_human_scoping` sans inférer de type.

**Contrat pour EST-4 (affichage résultat) :**

Lorsque l'assumption `unknown_project_requires_human_scoping` est présente dans l'`EstimateResult`, l'écran de résultat **ne doit pas** afficher la fourchette comme une estimation normale. Il doit afficher à la place un message du type :

> Votre projet nécessite un premier cadrage.

La fourchette fallback peut rester dans le JSON de réponse pour la cohérence du contrat, mais ne doit pas être rendue telle quelle à l'utilisateur final.

**Ce comportement d'affichage ne doit pas être implémenté avant EST-4.**

---

## 11. Paiement échelonné

### Définition

Le paiement échelonné est une **répartition du montant** de l'investissement initial sur un nombre de mensualités convenues entre Devzair et le client lors du cadrage.

Il ne constitue pas :
- un crédit à la consommation ;
- un financement avec intérêts ;
- un prélèvement automatique contractualisé.

### Présentation dans l'outil

L'outil peut simuler une répartition indicative :

> Exemple : Un investissement de X € réparti sur 6 mois représente environ X/6 € par mois.

*(Exemple uniquement — aucun montant réel défini.)*

L'avertissement est explicite : **simulation indicative, conditions finales à définir lors du cadrage**.

### Conditions

- Durée : À VALIDER (conditions commerciales Devzair).
- Acompte minimal : À VALIDER.
- Conditions contractuelles : À VALIDER (mentions légales, CGV, devis).

---

## 12. Prestations récurrentes

### Catégories envisagées

| Catégorie | Description |
|---|---|
| Hébergement | Serveur, domaine, certificat TLS |
| Maintenance technique | Mises à jour CMS / framework, corrections bugs |
| Mises à jour de contenu | Ajout/modification de pages, actualités |
| SEO continu | Audit régulier, optimisation, création de contenus |
| Support | Réponse aux demandes, assistance |
| Autres | À VALIDER |

### Fréquence et montants

**Tous les montants sont À VALIDER.**  
La structure (mensuel / annuel / trimestriel) est également À VALIDER selon les offres Devzair.

### Séparation dans l'affichage

L'investissement initial et l'accompagnement récurrent sont présentés dans deux blocs distincts.  
L'utilisateur ne peut pas confondre un coût ponctuel et une prestation mensuelle.

---

## 13. Partenariat

### Positionnement

Le partenariat n'est **pas** une modalité de paiement.  
Il n'est **pas** placé à côté d'une option de paiement standard.

Il constitue une **voie distincte**, accessible depuis une section dédiée ou un CTA spécifique dans l'écran de résultat.

### Parcours partenariat

Le prospect remplit un formulaire complémentaire couvrant :

1. **État du projet**
   - Idée / Maquette / MVP / Produit en production

2. **Problème résolu**
   - Description du problème adressé et de la solution envisagée

3. **Modèle économique**
   - Comment le projet génère (ou prévoit de générer) des revenus

4. **Traction éventuelle**
   - Utilisateurs, clients, chiffre d'affaires, preuves d'intérêt (si existants)

5. **Clients / utilisateurs existants**
   - Nombre, profil, retours (si applicable)

6. **Rôle du porteur**
   - Responsabilités, compétences, temps disponible

7. **Rôle attendu de Devzair**
   - Conception / Développement / Design / SEO / Autre (préciser)

8. **Proposition du porteur**
   - Ce que le porteur propose à Devzair en échange de son implication (À VALIDER — aucune condition inventée ici)

9. **Contexte complémentaire**
   - Contraintes, délais, partenaires existants, contexte légal (champ libre)

### Règles

- **Aucune condition de partenariat n'est définie automatiquement** (ni pourcentage, ni equity, ni revenue share).
- La décision est **obligatoirement humaine**.
- Le formulaire est soumis à l'équipe Devzair pour examen.
- Le prospect reçoit un accusé de réception mais **aucune réponse automatique favorable**.
- Les critères d'éligibilité sont À VALIDER par l'équipe Devzair avant l'ouverture de EST-7.

### Données collectées dans ce parcours

Voir §16 (données et vie privée).

---

## 14. Architecture technique cible

### 14.1 Principes directeurs

- Le **navigateur** n'est pas la source de vérité du prix final.
- **Nuxt** porte le questionnaire, l'état de navigation et l'affichage.
- **Symfony** porte le moteur d'estimation (calcul, validation, versioning tarifaire).
- **PostgreSQL** intervient uniquement quand la conservation d'une demande ou d'un lead devient nécessaire (EST-6+).

### 14.2 Flux de données (schéma conceptuel)

```
Navigateur (Vue / Nuxt)
  → [questionnaire] état local (mémoire ou sessionStorage)
  → [soumission] POST /api/estimate
     ↓
  Symfony (API)
  → validation de l'input
  → chargement de la grille tarifaire versionnée
  → calcul MIN / MAX + lignes détaillées
  → retour EstimateResult (JSON signé côté serveur)
     ↓
  Nuxt (server route ou API layer)
  → transmission au composant résultat
     ↓
  Vue (composant résultat)
  → affichage de la fourchette, du résumé, des modalités
```

### 14.3 Composants identifiés (noms indicatifs — à confirmer en EST-1 et EST-2)

**Nuxt / Vue :**
- Page `/estimer-mon-projet` (SSR, indexable selon décision À VALIDER)
- Composant `ProjectEstimator` (orchestrateur du questionnaire)
- Composants de step (un par étape, rendu conditionnel)
- Composant `EstimateResult` (affichage du résultat)
- Composable `useEstimator` (état du questionnaire, navigation entre étapes, appel API)
- Types partagés `EstimateInput`, `EstimateResult`

**Symfony :**
- DTO `ProjectEstimateInput` (validation stricte)
- Service `ProjectEstimationEngine` (calcul autoritaire)
- Interface `PricingRepositoryInterface` (chargement de la grille versionnée)
- DTO `EstimateResult` (sortie typée)
- Contrôleur `POST /api/estimate`

**PostgreSQL (EST-6+) :**
- Table `project_estimate_lead` (uniquement si conservation d'une demande qualifiée)
- Aucune table créée en EST-0 à EST-5

### 14.4 Contraintes architecturales

- Cohérence avec les conventions existantes : SOLID, DRY, KISS, architecture hexagonale (Domaine / Application / Infrastructure / Présentation).
- Un composant visuel ne doit pas appeler directement l'API distante (règle AGENTS.md §6).
- Les grilles tarifaires ne doivent jamais apparaître dans le bundle JavaScript public.
- L'endpoint `/api/estimate` est soumis aux mêmes règles de sécurité que `/api/contact` (Origin allowlist, rate limiting, payload maximal, Request-Id).

---

## 15. Version tarifaire

### Problème

Si la grille de prix évolue, les demandes antérieures doivent rester rattachées aux règles utilisées pour leur calcul.  
Un prospect ayant reçu une estimation en juillet ne doit pas obtenir une relecture différente en novembre avec la même référence.

### Principe

- Chaque estimation est calculée avec une **version tarifaire identifiée**.
- La version est attachée à l'objet `EstimateResult` (`pricingVersion`).
- Si une demande est persistée (EST-6+), la version tarifaire est stockée avec la demande.
- Le moteur peut charger une version historique pour relire une demande ancienne.

### Numérotation

Aucun numéro de version réel n'est défini dans cette phase.  
Le principe est documenté. La première version sera nommée lors de la calibration en EST-1.

### Administration

Les versions tarifaires sont gérées côté serveur (Symfony).  
L'administration des grilles est prévue en EST-8.

---

## 16. Données et vie privée

### 16.1 Séparation calcul / collecte

Le calcul initial fonctionne **sans aucune donnée personnelle identifiante (PII)**.  
L'utilisateur peut obtenir une estimation complète sans fournir son nom ni son email.

### 16.2 Données collectées par étape

| Données | Étape | PII ? | Nécessaire ? |
|---|---|---|---|
| Réponses questionnaire (type projet, périmètre, fonctionnalités…) | Étapes 01–07 | Non | Oui (calcul) |
| Email | Optionnel après résultat | Oui | Uniquement si l'utilisateur choisit de transmettre |
| Nom | Optionnel après résultat | Oui | Uniquement si l'utilisateur choisit de transmettre |
| Téléphone | Optionnel après résultat | Oui | Non — à arbitrer (À VALIDER) |
| Contenu du parcours partenariat | Parcours dédié | Partiellement | Oui (qualification) |

### 16.3 Base légale

À VALIDER par l'équipe Devzair et un conseiller juridique si nécessaire, selon :
- Exécution d'un contrat ou mesures précontractuelles (article 6.1.b RGPD) pour la transmission de la demande.
- Intérêt légitime ou consentement pour le suivi commercial.

### 16.4 Finalités

- Calcul de l'estimation : traitement local ou côté serveur, sans conservation si l'utilisateur ne choisit pas de transmettre.
- Qualification et contact : transmission du brief et prise de rendez-vous.
- Partenariat : examen de la proposition par l'équipe Devzair.

### 16.5 Durée de conservation

**À VALIDER** avant l'ouverture de EST-6.  
Ne pas inventer de durée dans cette phase.

### 16.6 Suppression

**À VALIDER** (procédure de suppression sur demande, droit à l'effacement RGPD).

### 16.7 Journalisation

- Les logs ne doivent pas contenir de PII (cohérent avec le pattern `canal contact` existant).
- Le `X-Request-Id` est systématiquement présent pour la corrélation.

### 16.8 Anti-spam et rate limiting

- L'endpoint `/api/estimate` est soumis au rate limiting (token bucket par IP — conditions À VALIDER).
- Un honeypot silencieux peut être envisagé sur le formulaire de contact final.
- Cloudflare Turnstile peut être activé sur le formulaire final (cohérent avec l'architecture existante).

### 16.9 Mise à jour de docs/05-SECURITY-PRIVACY.md

Le document `docs/05-SECURITY-PRIVACY.md` sera mis à jour lors de **EST-6**, quand la collecte de PII sera réellement ouverte.  
Ne pas modifier ce document dans la phase EST-0.

---

## 17. Accessibilité

L'outil respecte **WCAG 2.2 niveau AA** a minima.

### Exigences par composant

| Exigence | Détail |
|---|---|
| Navigation clavier | Toutes les étapes et tous les choix sont utilisables au clavier sans souris |
| Gestion du focus | Lors d'un changement d'étape, le focus est déplacé vers le titre de la nouvelle étape ou le premier élément pertinent |
| Progression accessible | L'état de progression est communiqué aux lecteurs d'écran (ex. `aria-label="Étape 3 sur 7"`) |
| Groupes de choix | `<fieldset>` + `<legend>` pour chaque groupe de boutons radio ou cases à cocher |
| Erreurs reliées | Les messages d'erreur sont liés aux champs via `aria-describedby` |
| Pas d'info par couleur seule | Chaque information visuelle a un équivalent textuel ou structurel |
| Taille des cibles | 44 × 44 px minimum pour les éléments interactifs (WCAG 2.2 §2.5.8) |
| Reduced motion | Toute animation est désactivée ou réduite si `prefers-reduced-motion: reduce` |
| Responsive | Fonctionnel à partir de 320 px de largeur |
| Zoom 200 % | Aucune information perdue à 200 % de zoom |
| Résultat sans animation | L'écran de résultat est compréhensible sans animation (rendu SSR suffisant) |

### Tests attendus

- Axe WCAG 2.2 AA (serious + critical bloquants) sur chaque étape et sur l'écran résultat.
- Navigation clavier complète du parcours (E2E Playwright).
- Responsive 320 / 390 / 768 / 1024 / 1440 px.
- Vérification `prefers-reduced-motion`.
- Audit lecteur d'écran : À VALIDER (audit dynamique Phase 11C).

---

## 18. Sécurité

### Règles applicables à l'endpoint `/api/estimate`

- **Origin allowlist** stricte (cohérent avec l'architecture contact existante).
- **Rate limiting** par IP (token bucket — conditions À VALIDER).
- **Payload maximal** : limite en octets à définir (À VALIDER).
- **Validation stricte** de l'input côté serveur (DTO Symfony Validator).
- **Aucune donnée sensible** dans les logs (canal dédié `estimator`, Request-Id).
- **Pas de PII** dans la première phase (calcul sans email).
- **Pas de secret dans le bundle Vue** (grilles tarifaires côté serveur uniquement).
- Honeypot silencieux si un formulaire final collecte des données (cohérent avec `ContactRequest`).
- Cloudflare Turnstile optionnel sur le formulaire final (cohérent avec l'architecture existante).

### Revue de sécurité

Une revue de sécurité ciblée sera menée en **EST-9** (intégration site + QA + lancement), cohérente avec la checklist `docs/checklists/ADMIN-SECURITY-REVIEW.md`.

---

## 19. Analytics

### Événements futurs envisagés

| Événement | Déclencheur |
|---|---|
| `estimator_started` | Ouverture de la page `/estimer-mon-projet` ou de l'outil |
| `estimator_step_completed` | Validation d'une étape (inclure numéro d'étape, pas le contenu détaillé) |
| `estimator_result_viewed` | Affichage de l'écran de résultat |
| `estimator_contact_started` | Clic sur le CTA de contact ou d'envoi de l'estimation |
| `estimator_contact_submitted` | Soumission du formulaire de contact final |
| `estimator_partnership_started` | Accès au parcours partenariat |

### Règles

- **Ne jamais envoyer le contenu détaillé des réponses** aux analytics (une réponse peut contenir des informations confidentielles sur le projet d'un prospect).
- **Respecter la politique de consentement** existante et future du site.
- Les événements ne sont envoyés que si le prospect a consenti au tracking.
- Ne pas implémenter dans cette phase.

---

## 20. Administration future (EST-8)

L'administration des bases tarifaires est **prévue mais non implémentée** avant EST-8.

### Fonctionnalités prévues

- Saisie et modification des lignes tarifaires par type de projet.
- Définition des fourchettes MIN / MAX par option.
- Gestion des règles de composition (combinaisons d'options).
- Création de nouvelles versions tarifaires.
- Activation / désactivation des modalités de règlement autorisées.
- Gestion des prestations récurrentes.

### Interface

- Back-office Symfony / Twig SSR (cohérent avec l'administration éditoriale existante).
- Accès réservé aux administrateurs Devzair (`ROLE_ADMIN`).
- Aucun endpoint HTTP public pour modifier les grilles.

### Premier moteur

Le premier moteur (EST-1) peut être configuré côté serveur de manière versionnée, sans interface d'administration.  
L'interface d'administration (EST-8) est une phase distincte et non bloquante pour les phases EST-1 à EST-7.

---

## 21. Roadmap EST-0 → EST-9

### EST-0 — Cadrage et documentation

**Objectif :** Créer la source de vérité fonctionnelle du sous-projet, aligner l'équipe, et définir le plan d'exécution.

**Périmètre :**
- Création de `docs/12-PROJECT-ESTIMATOR.md` (le présent document).
- Mise à jour de `docs/08-ROADMAP.md`.
- Mise à jour de `docs/10-TRACKING.md`.

**Hors périmètre :** Tout code applicatif (page, composant, route, entité, migration, CSS, test). Résolution des questions ouvertes (identifiées et classifiées, non résolues).

**Fichiers/domaines concernés :** `docs/` uniquement.

**Tests attendus :** Aucun test applicatif. Revue humaine du document.

**Critères de sortie :**
- [x] `docs/12-PROJECT-ESTIMATOR.md` créé et couvrant les 24 sections.
- [x] `docs/08-ROADMAP.md` mis à jour avec le programme Estimateur et les sous-jalons EST-1A/B/C.
- [x] `docs/10-TRACKING.md` mis à jour avec les tâches DEV-064 à DEV-073.
- [x] Aucun tarif réel inventé. Tous les montants inconnus marqués À VALIDER.
- [x] Questions ouvertes Q-01 à Q-14 identifiées et classées par phase bloquante (voir §23).
- [x] Aucun code applicatif écrit.
- [x] `git diff --check` exit 0.

**Dépendances :** Aucune phase précédente.

**Risques :** Dérive du périmètre (écriture de code anticipée). Invention de tarifs.

**Statut : TERMINÉE (2026-08-29).**

---

### EST-1 — Domaine + moteur d'estimation (Symfony) — **TERMINÉE** (2026-08-30)

**Objectif :** Implémenter le domaine Estimator côté Symfony, le moteur de calcul autoritaire et l'endpoint HTTP sécurisé.

EST-1 est décomposé en trois sous-jalons. Tous terminés.

**Résultat :** 254 tests Estimator, 495 assertions. Suite complète 867/867.

**Hors périmètre (global EST-1) :** Interface Nuxt, persistence PostgreSQL, administration.

**Fichiers/domaines probables :** `apps/api/src/Estimator/`, `apps/api/config/routes/`, `apps/api/tests/Estimator/`.

---

#### EST-1A — Contrat métier

**Périmètre :**
- DTO `ProjectEstimateInput` (types, enum `ProjectType`, value objects, validation métier).
- DTO `EstimateResult` (fourchette MIN/MAX, `oneOffItems[]`, `recurringItems[]`, `pricingVersion`, `assumptions[]`).
- Invariants métier (cohérence input/output, règles de composition sans montant réel).
- Fixtures tarifaires explicitement marquées **TEST** — aucune valeur Devzair réelle.
- PHPUnit domaine.

**Dépendances :** EST-0 terminé.

**Critères de sortie :** Le contrat métier est défini, typé et testé avec fixtures TEST. Aucune grille Devzair réelle. EST-1A peut démarrer sans Q-01.

---

#### EST-1B — Calibration + moteur

**Périmètre :**
- Service `ProjectEstimationEngine` (calcul autoritaire, entrée → sortie).
- Interface `PricingRepositoryInterface` + implémentation statique versionnée.
- Première grille Devzair réelle (Q-01 — **BLOQUANT**).
- Règles MIN/MAX par type de projet et option.
- PHPUnit moteur.

**Dépendances :** EST-1A. Q-01 validé par l'équipe Devzair (**bloquant**).

**Critères de sortie :** Le moteur calcule des fourchettes MIN/MAX réelles pour chaque type de projet, avec version tarifaire attachée.

---

#### EST-1C — API HTTP — **TERMINÉE** (2026-08-30)

**Périmètre livré :**
- `EstimateController` (`POST /estimate` — Caddy strips `/api` prefix) avec pipeline en 9 étapes : requestId UUID v7, Origin allowlist, taille payload (10 KB), rate limit, désérialisation JSON, validation Symfony Validator, mapping Presentation→Domaine, moteur, réponse.
- `EstimateRequest` DTO : champs `project_type`, `current_situation`, `scale` (requis, `Choice`), tableaux optionnels `objectives`, `features`, `content_needs`, `visibility_needs`, `care_needs` (valeurs validées, sans PII).
- `EstimateRequestMapper` : conversion DTO → `ProjectEstimateInput` via `fromString()` des enums.
- `EstimateResponseFactory` : champ `outcome` (`estimated` / `human_scoping_required`), montants en minor units entiers (jamais float), `recurring_items` avec `period: "month"`, `assumptions` en codes snake_case, `pricing_version: "2026-v1"`.
- `EstimateRateLimiter` (bucket `estimate_ip` dédié, indépendant de `contact_ip`) : 30 req/min/IP par défaut (`ESTIMATE_RATE_LIMIT` / `ESTIMATE_RATE_INTERVAL`). `InMemoryStorage` en test (reset à chaque kernel boot).
- Canal Monolog `estimator` dédié. Aucun PII ni payload loggué. Événements : `origin_rejected`, `payload_too_large`, `rate_limited`, `invalid_json`, `validation_failed`, `estimate_generated`.
- `Cache-Control: no-store` sur toutes les réponses.
- `ESTIMATE_RATE_LIMIT=30` / `ESTIMATE_RATE_INTERVAL="1 minute"` dans `.env.example`. `ESTIMATE_RATE_LIMIT=5` dans `.env.test`.
- 254 tests Estimator (unitaires + WebTestCase), 495 assertions. Suite complète 867/867.

**Dépendances :** EST-1B.

**Critères de sortie :** `POST /api/estimate` retourne un `EstimateResult` valide et versionné pour chaque type de projet, avec les garanties de sécurité HTTP.

---

**Risques (EST-1 global) :** Calibration commerciale des fourchettes (dépendance humaine — Q-01). Complexité des règles de composition. EST-1A est découplé pour ne pas attendre Q-01.

---

### EST-2 — Shell UX du configurateur (Nuxt)

**Objectif :** Créer la page `/estimer-mon-projet` et l'architecture UX du configurateur : navigation entre étapes, indicateur de progression, squelette accessible.

**Périmètre :**
- Page `apps/web/app/pages/estimer-mon-projet.vue`.
- Composant orchestrateur `ProjectEstimator.vue`.
- Navigation entre étapes (précédent / suivant).
- Indicateur de progression accessible.
- Gestion du focus entre étapes.
- Layouts et styles de base (tokens existants).
- Vitest et Playwright (structure, navigation, accessibilité).

**Hors périmètre :** Questionnaires conditionnels (EST-3), calcul (EST-4).

**Fichiers/domaines probables :** `apps/web/app/pages/`, `apps/web/app/components/estimator/`, `apps/web/app/composables/useEstimator.ts`.

**Tests attendus :** Vitest (orchestrateur, navigation, focus). Playwright (structure SSR, navigation clavier, accessibilité Axe, responsive).

**Critères de sortie :** Le shell du configurateur est navigable, accessible et responsive. Aucun questionnaire ni calcul fonctionnel requis.

**Dépendances :** EST-1 (contrat de l'API).

**Risques :** Gestion de focus complexe lors des transitions d'étapes.

---

### EST-3 — Questionnaires conditionnels

**Objectif :** Implémenter les questions par type de projet (§8) avec leur logique conditionnelle.

**Périmètre :**
- Composants de step pour chaque type de projet (site vitrine, e-commerce, application, refonte, objectif-first).
- Logique conditionnelle dans `useEstimator`.
- Arbre de décision pour la branche « Je ne sais pas encore ».
- Validation de chaque étape avant passage à la suivante.
- Vitest et Playwright.

**Hors périmètre :** Calcul et écran résultat (EST-4).

**Fichiers/domaines probables :** `apps/web/app/components/estimator/steps/`.

**Tests attendus :** Vitest (logique conditionnelle, validation). Playwright (parcours complets par type, branche objectif-first, « Je ne sais pas »).

**Critères de sortie :** Les cinq branches de questionnaire sont complètes, accessibles et testées.

**Dépendances :** EST-2.

**Risques :** Explosion combinatoire des cas de test. Formulations inadaptées aux prospects non techniques.

---

### EST-4 — Calcul + écran résultat

**Objectif :** Connecter le questionnaire au moteur Symfony et afficher l'écran résultat.

**Périmètre :**
- Composable `useEstimator` : appel `POST /api/estimate`, gestion des états (chargement, erreur, succès).
- Composant `EstimateResult.vue`.
- Affichage fourchette MIN / MAX.
- Résumé du projet.
- Séparation investissement initial / récurrent.
- Vitest et Playwright.

**Hors périmètre :** Modalités de paiement (EST-5), collecte de PII (EST-6).

**Fichiers/domaines probables :** `apps/web/app/components/estimator/EstimateResult.vue`, `apps/web/app/composables/useEstimator.ts`.

**Tests attendus :** Vitest (états, mapping API). Playwright (affichage résultat, séparation initial/récurrent, responsive, Axe).

**Critères de sortie :** Un parcours complet aboutit à un résultat affiché, cohérent, accessible et non contractuel.

**Dépendances :** EST-3, EST-1.

**Risques :** Incohérence entre les hypothèses affichées et le résultat.

---

### EST-5 — Modalités de paiement / récurrent

**Objectif :** Implémenter l'affichage des modalités envisageables et la simulation de paiement échelonné.

**Périmètre :**
- Section « Modalités envisageables » dans `EstimateResult.vue`.
- Simulation de répartition du montant (sans paiement réel).
- Affichage de l'accompagnement récurrent avec les conditions À VALIDER.
- Mentions légales de non-engagement.

**Hors périmètre :** Paiement réel, crédit, prélèvement automatique.

**Fichiers/domaines probables :** Extension de `EstimateResult.vue`.

**Tests attendus :** Vitest (calcul de répartition). Playwright (affichage, mentions de non-engagement).

**Critères de sortie :** Les modalités sont affichées avec avertissements. La simulation ne peut pas être confondue avec un paiement réel.

**Dépendances :** EST-4.

**Risques :** Ambiguïté entre simulation et engagement commercial.

---

### EST-6 — Lead qualifié + persistence / API

**Objectif :** Permettre au prospect de transmettre son estimation (email + brief structuré) et implémenter la persistence de la demande.

**Périmètre :**
- Formulaire de contact final (email, nom, message optionnel) accessible après l'écran résultat.
- Endpoint Symfony de réception du lead (POST /api/estimate/submit ou similaire).
- Persistence PostgreSQL (table `project_estimate_lead` ou similaire).
- Notification email à Devzair.
- Honeypot, rate limiting, Turnstile optionnel.
- Mise à jour de `docs/05-SECURITY-PRIVACY.md`.

**Hors périmètre :** Parcours partenariat (EST-7).

**Fichiers/domaines probables :** `apps/api/src/Estimator/`, table en PostgreSQL, `apps/web/app/components/estimator/LeadForm.vue`.

**Tests attendus :** PHPUnit (endpoint, validation, email, PII logs). Vitest (formulaire, états). Playwright (soumission, confirmation, erreurs).

**Critères de sortie :** Un lead est transmis, reçu par email, persisté, et aucun PII ne fuite dans les logs.

**Dépendances :** EST-5. Validation juridique des durées de conservation (À VALIDER).

**Risques :** Non-conformité RGPD si la durée de conservation n'est pas validée.

---

### EST-7 — Parcours partenariat

**Objectif :** Implémenter le parcours de candidature partenariat (formulaire dédié, transmission, accusé de réception).

**Périmètre :**
- Formulaire partenariat (9 sections, voir §13).
- Endpoint Symfony de réception.
- Notification email à Devzair.
- Pas de réponse automatique favorable.
- Critères d'éligibilité À VALIDER avant l'ouverture de cette phase.

**Hors périmètre :** Traitement automatique, calcul de conditions, intégration CRM.

**Fichiers/domaines probables :** Extension du domaine `Estimator`, `apps/web/app/components/estimator/PartnershipForm.vue`.

**Tests attendus :** PHPUnit, Vitest, Playwright.

**Critères de sortie :** Le parcours partenariat est distinct, accessible, et aucune condition n'est calculée automatiquement.

**Dépendances :** EST-6. Critères d'éligibilité validés par l'équipe Devzair.

**Risques :** Confusion entre partenariat et paiement. Attentes mal calibrées.

---

### EST-8 — Administration tarifaire

**Objectif :** Implémenter l'interface d'administration des grilles tarifaires dans le back-office Symfony.

**Périmètre :**
- CRUD sur les lignes tarifaires par type de projet.
- Gestion des versions tarifaires.
- Activation / désactivation des modalités de règlement.
- Gestion des prestations récurrentes.
- Interface Twig SSR (cohérent avec l'administration éditoriale).

**Hors périmètre :** Exposition publique des grilles.

**Fichiers/domaines probables :** `apps/api/src/Estimator/Administration/`, `apps/api/templates/admin/estimator/`.

**Tests attendus :** PHPUnit, Playwright (recette admin).

**Critères de sortie :** L'administrateur peut modifier une grille, créer une nouvelle version, et l'activer sans intervention technique.

**Dépendances :** EST-1, Phase 8C (administration authentifiée existante).

**Risques :** Complexité de la modélisation des règles tarifaires.

---

### EST-9 — Intégration site + QA + lancement

**Objectif :** Intégrer l'outil dans la navigation publique du site, réaliser la recette QA complète, et ouvrir la route publique `/estimer-mon-projet`.

**Périmètre :**
- Ajout du lien dans la navigation principale.
- Ajout dans le sitemap.
- Métadonnées SEO de la page.
- Recette fonctionnelle complète (tous les parcours).
- Recette accessibilité (audit Axe + lecteur d'écran).
- Recette sécurité (OWASP, secrets, rate limiting, PII).
- Recette RGPD (collecte, conservation, mentions).
- Tests de charge raisonnables.

**Hors périmètre :** CRM, intégrations tiers non planifiées.

**Fichiers/domaines probables :** `apps/web/app/config/navigation.ts`, `nuxt.config.ts` (sitemap, routeRules), `apps/web/test/e2e/estimator-*.spec.ts`.

**Tests attendus :** Suite E2E complète. Audit Axe. Vérification SSR et SEO.

**Critères de sortie :** L'outil est publié, indexable, accessible, sécurisé, conforme RGPD, et lié depuis la navigation principale.

**Dépendances :** EST-8 ou accord de déploiement sans interface d'administration (À arbitrer).

**Risques :** Dérive du positionnement (veiller à ne pas présenter Devzair comme un service low-cost).

---

## 22. Critères d'acceptation (MVP)

Le MVP de l'estimateur est considéré fonctionnel lorsque :

1. Un prospect peut compléter un parcours complet (étapes 01–07) pour chaque type de projet.
2. L'écran de résultat affiche une fourchette MIN / MAX non contractuelle.
3. La distinction investissement initial / récurrent est clairement présentée.
4. Le prospect peut transmettre son brief sans avoir fourni son email pendant le calcul.
5. Aucun tarif réel n'est exposé dans le bundle JavaScript côté client.
6. L'outil est entièrement utilisable au clavier, sans souris.
7. Axe WCAG 2.2 AA ne signale aucune violation serious ou critical.
8. Le formulaire de contact final respecte les principes RGPD validés.
9. Le positionnement Devzair (agence à taille humaine, accompagnement personnalisé) est préservé à chaque étape.
10. Aucun montant artificiel n'a été inventé dans le moteur.

---

## 23. Questions ouvertes / À VALIDER

Les questions sont classées par phase bloquante. Aucune ne bloque EST-0 (terminée) ni EST-1A.

### ~~BLOQUANT EST-1B~~ — Calibration commerciale — **RÉSOLUE**

| # | Question | Responsable | Note |
|---|---|---|---|
| Q-01 | Grille tarifaire réelle (montants MIN / MAX par type et option) | Devzair | **VALIDÉE (2026-08-30)** — Grille `2026-v1` implémentée dans `DevzairPricingCatalogV1`. |

### BLOQUANT EST-5 — Modalités de paiement

| # | Question | Responsable | Note |
|---|---|---|---|
| Q-03 | Conditions du paiement échelonné (durée, acompte) | Devzair | Nécessaire pour présenter des modalités honnêtes. |
| Q-11 | Nombre de mensualités pour la simulation d'échelonnement | Devzair | Couplé à Q-03. |

### BLOQUANT EST-6 — Persistence et conformité RGPD

| # | Question | Responsable | Note |
|---|---|---|---|
| Q-02 | Durée de conservation des leads (RGPD) | Devzair + conseil juridique | Collecte de PII impossible sans durée validée. **Critique RGPD.** |
| Q-06 | Politique de suppression des données (droit à l'effacement) | Devzair | Droit à l'effacement RGPD — À VALIDER avant ouverture EST-6. |
| Q-10 | Le téléphone est-il collecté dans le formulaire final ? | Devzair | Impacte le design et les mentions du formulaire de lead. |
| Q-12 | Modalité de réception interne du lead (email seul, CRM, autre) | Devzair | Impacte l'architecture Symfony de réception et de persistence. |

### BLOQUANT EST-7 — Partenariat

| # | Question | Responsable | Note |
|---|---|---|---|
| Q-04 | Critères d'éligibilité au partenariat | Devzair | Ouvrir EST-7 sans critères crée des attentes non gérables. |

### BLOQUANT EST-9 / PRODUCTION

| # | Question | Responsable | Note |
|---|---|---|---|
| Q-05 | Texte juridique de non-engagement (avertissement résultat) | Devzair + conseil juridique | Placeholder autorisé pendant le développement — obligatoire avant mise en production. |
| Q-07 | Conditions CGV / CGU liées à l'estimation | Devzair | À VALIDER avant lancement public. |
| Q-08 | La page `/estimer-mon-projet` est-elle indexable ou noindex ? | Devzair (SEO) | Décision SEO à prendre avant EST-9. |
| Q-13 | La page `/estimer-mon-projet` est-elle pré-rendue ou SSR dynamique ? | Devzair (SEO / performance) | Le shell UX peut être développé sans cette décision. À trancher avant EST-9 / mise en production. |
| Q-14 | Faut-il un flux RSS ou sitemap spécifique à l'estimateur ? | Devzair | À VALIDER lors de la recette EST-9. |

### NON BLOQUANT À CE STADE

| # | Question | Responsable | Note |
|---|---|---|---|
| Q-09 | Turnstile activé ou non sur le formulaire final ? | Devzair | Optionnel — cohérent avec l'architecture contact existante. À décider avant mise en production mais non bloquant pour démarrer EST-6. |

---

## 24. Journal de décisions du sous-projet

| Date | ID | Décision | Raisons | Conséquences |
|---|---|---|---|---|
| 2026-08-29 | DEST-001 | Ouvrir le sous-projet Estimateur en phase EST-0 (cadrage uniquement) | Aligner l'équipe avant tout développement, éviter les refontes coûteuses | Aucun code écrit en EST-0 |
| 2026-08-29 | DEST-002 | Le résultat est affiché sans collecte d'email | Respect de la vie privée, confiance du prospect, principe B validé | La collecte de PII est différée après affichage |
| 2026-08-29 | DEST-003 | Le moteur tarifaire est autoritaire côté Symfony | Le navigateur ne doit pas être la source de vérité du prix | Grilles tarifaires absentes du bundle Vue |
| 2026-08-29 | DEST-004 | Aucun montant réel n'est défini en EST-0 | Règle absolue : ne pas inventer de tarif Devzair | Calibration réalisée en EST-1 par l'équipe |
| 2026-08-29 | DEST-005 | Le partenariat est un parcours distinct, pas une option de paiement | Éviter la confusion entre modalités commerciales et proposition de collaboration | Section dédiée, formulaire spécifique, décision humaine obligatoire |
| 2026-08-29 | DEST-006 | Versioning tarifaire documenté comme principe | Les demandes anciennes doivent rester rattachées à leurs règles de calcul | `pricingVersion` présent dans `EstimateResult` dès EST-1 |
| 2026-08-29 | DEST-007 | Paiement échelonné = simulation de répartition uniquement | Éviter toute confusion avec un crédit ou un prélèvement automatique | Mention explicite de non-engagement sur l'écran résultat |
| 2026-08-29 | DEST-008 | La branche « Je ne sais pas encore » part de l'objectif | Permettre à un prospect non technique de qualifier son besoin | Questionnaire objectif-first avant recommandation de type |
| 2026-08-29 | DEST-009 | PostgreSQL n'est impliqué qu'à partir de EST-6 (persistence lead) | Éviter une dépendance prématurée à la base de données pour le calcul pur | Les phases EST-1 à EST-5 n'ajoutent aucune table |
| 2026-08-29 | DEST-010 | L'état du questionnaire est géré en mémoire (recommandation MVP) | Solution la plus simple, aucun stockage de données côté client sans consentement | SessionStorage ou URL token envisagés si un besoin de reprise émerge |
| 2026-08-29 | DEST-011 | Le développement du sous-projet peut être conduit en parallèle des phases globales restantes | Le sous-projet est indépendant et n'interfère pas avec les phases 1–13 ; bloquer sur Phase 12 retarderait inutilement EST-1A et EST-2 | La mise en production reste soumise aux gates habituelles (sécurité, privacy, QA, recette, GO explicite) coordonnée lors de EST-9 |
| 2026-08-29 | DEST-012 | EST-1 est découpé en trois sous-jalons EST-1A / EST-1B / EST-1C | EST-1A (contrat métier) ne nécessite aucun tarif réel, ce qui évite de bloquer le développement en attente de Q-01 | EST-1A peut démarrer immédiatement après EST-0 ; EST-1B est conditionné à Q-01 ; EST-1C suit EST-1B |
| 2026-08-29 | DEST-013 | EST-0 est terminé dès que les questions ouvertes sont identifiées et classées, sans exiger leur résolution | La résolution des questions commerciales (Q-01, Q-03, Q-04…) appartient à Devzair et n'est pas un prérequis pour clôturer le cadrage documentaire | Q-01 reste obligatoire avant EST-1B ; Q-02 avant EST-6 ; Q-04 avant EST-7 |
| 2026-08-30 | DEST-014 | Q-01 validée — grille tarifaire `2026-v1` retenue comme première politique commerciale Devzair | Stratégie : prix d'entrée accessibles, pas de positionnement low-cost, valeur du travail préservée, orientation forte vers le récurrent | Implémentée dans `DevzairPricingCatalogV1` ; séparation investissement initial / récurrent strictement respectée ; le moteur est pur (sans I/O, déterministe, versionné) |
| 2026-08-30 | DEST-015 | Le scale n'affecte que le socle, pas les features/contenus/visibilité | Éviter une dérive multiplicative globale qui rend les fourchettes incompréhensibles | Les features, contenus et visibilité sont des suppléments fixes, indépendants de la taille déclarée |
| 2026-08-30 | DEST-016 | Arrondi conservateur à 50 € — MIN vers le bas, MAX vers le haut | Éviter les montants artificiellement précis (1 437,50 €) qui suggèrent une fausse exactitude | Les line items conservent leurs valeurs exactes ; l'arrondi s'applique uniquement à `estimateRange` (somme agrégée) |
| 2026-08-30 | DEST-017 | Anti-doublon récurrent : un seul tier de maintenance (le plus haut applicable), ContinuousSeo orthogonal | Éviter de facturer ESSENTIAL_MAINTENANCE + MAINTENANCE_FOLLOWUP quand le second couvre déjà le premier | Logique de tier : FunctionalEvolution > ContentUpdates > Support > Maintenance ; SEO continu ajouté séparément |
| 2026-08-30 | DEST-018 | For Unknown/Other sans inférence : fourchette large [vitrine_min, businessapp_max] + assumption humaine | Ne pas inventer une estimation 0 € ni ignorer le besoin ; être honnête sur l'incertitude | `unknown_project_requires_human_scoping` ajouté ; la fourchette est réelle et annotée, non commerciale |
| 2026-08-30 | DEST-019 | Inférence déterministe : deux familles fortes (SellOnline + DigitalizeProcess) → conflit → human scoping | Éviter qu'un ordre aléatoire de sélection des objectifs produise des résultats différents pour le même ensemble | Classification en deux niveaux : objectifs forts (Ecommerce / BusinessApp) et faibles (VitrineSite) ; 2+ forts distincts → null → fallback |
| 2026-08-30 | DEST-020 | L'endpoint `POST /api/estimate` réutilise `OriginAllowlist` du module Contact (même env `CONTACT_ORIGIN_ALLOWLIST`) | Un seul point de configuration pour la whitelist d'origines autorisées ; cohérence des règles CSRF stateless | `EstimateRateLimiter` utilise un bucket `estimate_ip` dédié pour éviter toute interférence avec le budget de tokens Contact |
| 2026-08-30 | DEST-021 | Tous les montants de la réponse HTTP sont des entiers en unités mineures (centimes EUR) | Éliminer tout risque d'imprécision flottante dans la sérialisation JSON | `amountMinor: int` est l'unité de transport ; le frontend convertit (÷ 100) pour l'affichage |
