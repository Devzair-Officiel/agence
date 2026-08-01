# DEVZAIR — Référentiel de conception, développement et suivi

**Version :** 2.0  
**Date :** 1 août 2026  
**Statut :** document directeur du projet — architecture Nuxt confirmée  
**Fichier à lire avant toute intervention de Codex**

---

## 1. Rôle de ce document

Ce fichier constitue la source de vérité du projet Devzair. Il doit servir à :

- cadrer le positionnement, le périmètre et les priorités ;
- organiser le développement étape par étape ;
- définir les pages, leur rôle et leur contenu éditorial ;
- encadrer le SEO, le référencement local et le GEO ;
- imposer les exigences de sécurité, de confidentialité, de performance et d’accessibilité ;
- fixer les critères d’acceptation avant validation de chaque phase ;
- conserver les décisions, écarts, tâches et changements du projet.

### Règles obligatoires

1. Codex doit lire ce fichier avant chaque nouvelle tâche.
2. Une seule phase fonctionnelle doit être traitée à la fois.
3. Aucun contenu factuel ne doit être inventé.
4. Aucun client, résultat, chiffre, membre d’équipe, adresse, certification, avis ou témoignage fictif ne doit être publié.
5. Toute information manquante doit être marquée `À VALIDER` ou utilisée comme donnée de démonstration clairement identifiable en environnement local.
6. Toute nouvelle fonctionnalité doit inclure les tests, la sécurité, l’accessibilité, le SEO et la documentation correspondants.
7. Une tâche n’est terminée que lorsque ses critères d’acceptation sont vérifiés.
8. Ce fichier doit être mis à jour à la fin de chaque étape : cases cochées, décisions, limites et prochaine action.
9. Aucun déploiement en production ne doit être réalisé si un contrôle bloquant échoue.
10. Le site doit utiliser **« nous »** et présenter Devzair comme une agence digitale à taille humaine, jamais comme un développeur freelance isolé.
11. Le frontend est construit avec **Nuxt 4, Vue et TypeScript strict**, et non comme une SPA Vue classique.
12. Le rendu serveur ou le pré-rendu doit rester actif pour toutes les pages publiques stratégiques.
13. Le backend futur sera une **API Symfony 7.4 LTS** avec PostgreSQL, intégrée sans remettre en cause les URL publiques.
14. Les principes SOLID, la séparation des responsabilités, DRY et KISS s’appliquent sans créer d’abstractions prématurées.
15. Les pages orchestrent ; les composants présentent ; les composables adaptent Vue/Nuxt ; les services exécutent les cas d’usage ; les dépôts accèdent aux données.
16. Aucun composant de présentation ne doit appeler directement une API distante.
17. Toute métadonnée SEO doit être générée à partir d’une source structurée et réutilisable, jamais copiée dans plusieurs composants.
18. Une abstraction commune ne doit être créée qu’après l’apparition d’un besoin réel et stable, généralement après deux ou trois usages similaires.

---

## 1.1 Décisions techniques confirmées

| Sujet | Décision |
|---|---|
| Dépôt | Monorepo Git unique à la racine `devzair/` |
| Frontend | Nuxt 4, Vue 3, TypeScript strict |
| Template initial | `minimal` |
| Gestionnaire | npm avec `package-lock.json` versionné |
| Environnement | Docker Compose |
| Rendu | SSR par défaut, pré-rendu sélectif des pages marketing |
| Backend futur | Symfony 7.4 LTS |
| Base de données future | PostgreSQL |
| Blog | Contenu géré par Symfony, rendu par Nuxt sous `/ressources/**` |
| Domaine | Un seul domaine public ; l’API est exposée sous `/api/**` |
| SEO | Métadonnées typées, canonical, sitemap, robots, Schema.org et contrôle d’indexation |
| GEO | HTML serveur, contenus vérifiables, entités structurées et politique explicite des robots d’IA |
| Qualité | ESLint flat config, typecheck, tests unitaires, intégration et E2E |
| Architecture | SOLID pragmatique, DRY, KISS, responsabilités séparées |

### État confirmé au 1 août 2026

- [x] Projet Nuxt créé dans `apps/web`.
- [x] Template `minimal` sélectionné.
- [x] npm sélectionné.
- [x] Aucun dépôt Git imbriqué créé dans `apps/web`.
- [x] Aucun module Nuxt ajouté pendant l’assistant d’installation.
- [ ] Dépôt Git racine initialisé et premier commit réalisé.
- [ ] Docker Compose permanent créé.
- [ ] Outils de qualité installés.
- [ ] Architecture des répertoires créée.
- [ ] Socle SEO global créé.

---

## 2. Positionnement de Devzair

### Définition

Devzair est une agence digitale spécialisée dans la création de solutions web, la visibilité en ligne et l’accompagnement numérique des entreprises.

### Publics prioritaires

- TPE et PME ;
- commerces et entreprises locales ;
- indépendants et professions de service ;
- porteurs de projets structurés ;
- organisations ayant besoin d’un site, d’un outil métier ou d’une meilleure visibilité.

### Promesse

> Nous aidons les entreprises à construire une présence digitale claire, professionnelle et efficace grâce à des sites web, applications, contenus visuels et stratégies SEO conçus pour attirer, rassurer et convertir leurs clients.

### Message central

> Devzair ne crée pas seulement des sites internet. Nous concevons des solutions digitales complètes pour aider les entreprises à être visibles, crédibles et mieux organisées.

### Différenciation

Devzair réunit dans une même démarche :

- stratégie et cadrage ;
- UX/UI et identité visuelle ;
- développement web et applicatif ;
- photographie et création de contenu ;
- SEO, visibilité locale et GEO ;
- mise en ligne, maintenance et évolution.

### Ton éditorial

Le ton doit être :

- professionnel, clair et rassurant ;
- concret, sans jargon inutile ;
- sérieux sans donner l’image d’une grande agence impersonnelle ;
- orienté besoins, résultats et accompagnement ;
- honnête sur les capacités, limites, délais et preuves disponibles.

### Termes à privilégier

`agence digitale`, `équipe projet`, `expertise web`, `accompagnement global`, `solutions digitales`, `présence en ligne`, `visibilité`, `conversion`, `image professionnelle`, `solution évolutive`.

### Termes à éviter

`je suis développeur`, `mon profil`, `freelance développeur web`, `je crée des sites`, ainsi que toute formulation laissant croire à une équipe, un local ou une expérience qui ne seraient pas démontrables.

---

## 3. Objectifs du site

### Objectifs principaux

1. Présenter clairement l’offre et le positionnement.
2. Inspirer confiance sans exagération commerciale.
3. Générer des demandes qualifiées.
4. Démontrer la méthode et la qualité technique.
5. Développer la visibilité organique classique et locale.
6. Rendre les contenus compréhensibles par les moteurs de recherche et les systèmes d’IA.
7. Permettre l’évolution future vers un espace client, des outils métier ou des offres récurrentes.

### Conversions prioritaires

- demande de devis ;
- prise de contact ;
- demande d’audit ou d’échange ;
- appel téléphonique, uniquement si un numéro professionnel est confirmé ;
- consultation d’une étude de cas ;
- consultation d’une page service avant contact.

### Indicateurs

- demandes qualifiées reçues ;
- taux de conversion des pages services ;
- sources des demandes ;
- trafic organique de marque et hors marque ;
- pages indexées sans erreur ;
- clics et impressions issus des moteurs ;
- conformité Core Web Vitals ;
- erreurs techniques, sécurité et disponibilité ;
- trafic identifiable provenant d’outils ou moteurs d’IA ;
- fréquence de crawl des robots autorisés.

Aucun objectif de première position, volume de trafic ou délai de référencement ne doit être promis.

---

## 4. Périmètre du projet

### MVP — lancement initial

Le MVP doit comprendre :

- page d’accueil ;
- page agence ;
- page méthode ;
- page générale des services ;
- pages individuelles des services prioritaires ;
- portfolio ou études de cas, même limité au lancement ;
- page contact / demande de devis ;
- blog ou ressources avec architecture prête ;
- mentions légales ;
- politique de confidentialité ;
- gestion des cookies et préférences, si nécessaire ;
- plan du site HTML facultatif mais recommandé ;
- robots.txt ;
- sitemap XML ;
- données structurées pertinentes ;
- suivi d’audience conforme ;
- Search Console et Bing Webmaster Tools ;
- socle de sécurité, tests et supervision.

### Phase ultérieure possible

- espace client ;
- suivi de projet ;
- paiement ou abonnement ;
- outils de devis avancés ;
- prise de rendez-vous ;
- portail de maintenance ;
- centre de ressources ;
- site multilingue ;
- simulateurs ou diagnostics interactifs.

Ces fonctionnalités ne doivent pas être anticipées dans le code au prix d’une complexité inutile. Prévoir des points d’extension propres, sans surarchitecture.

---

## 5. Arborescence et structure d’URL

Les URL doivent être courtes, descriptives, stables, en minuscules et séparées par des tirets.

```text
/
├── /agence
├── /methode
├── /services
│   ├── /creation-site-internet
│   ├── /site-e-commerce
│   ├── /application-web-metier
│   ├── /design-ui-ux-identite-visuelle
│   ├── /photographie-creation-contenu
│   ├── /seo-referencement-naturel
│   ├── /visibilite-locale
│   └── /maintenance-accompagnement
├── /realisations
│   └── /[slug-etude-de-cas]
├── /secteurs
│   └── /[secteur-reellement-cible]
├── /ressources
│   └── /[slug-article]
├── /contact
├── /demande-de-devis              # seulement si distincte de /contact
├── /mentions-legales
├── /politique-confidentialite
├── /gestion-des-cookies
└── /accessibilite                 # déclaration ou engagement selon situation
```

### Règles d’arborescence

- Ne pas multiplier les pages faibles ou redondantes.
- Ne créer une page secteur que si elle répond à un besoin réel avec un contenu spécifique.
- Ne créer une page locale que pour une zone réellement desservie et avec des informations utiles propres à cette zone.
- Ne pas produire de pages locales clonées avec uniquement le nom de la ville modifié.
- Une même intention de recherche doit avoir une page principale clairement identifiée.
- Toute suppression ou modification d’URL publiée doit prévoir une redirection adaptée.
- Ne jamais faire apparaître dans l’index les pages d’administration, de prévisualisation, de test ou de recherche interne.

---

## 6. Navigation et maillage interne

### Navigation principale recommandée

- Services
- Réalisations
- Méthode
- Agence
- Ressources
- Contact

### En-tête

- logo cliquable vers l’accueil ;
- navigation accessible au clavier ;
- CTA principal visible : `Parler de votre projet` ou `Demander un devis` ;
- menu mobile utilisable sans JavaScript bloquant ;
- état de focus clairement visible.

### Pied de page

- résumé du positionnement ;
- liens vers les services principaux ;
- liens de confiance et pages légales ;
- coordonnées professionnelles confirmées ;
- zones réellement desservies ;
- réseaux sociaux officiels ;
- lien de gestion des préférences cookies ;
- copyright dynamique.

### Maillage interne

- chaque service doit être lié depuis `/services`, l’accueil et les études de cas pertinentes ;
- chaque article doit renvoyer vers un service ou une ressource utile, sans forcer la conversion ;
- chaque étude de cas doit renvoyer vers les services réellement mobilisés ;
- utiliser des ancres descriptives, pas seulement « cliquez ici » ;
- ajouter un fil d’Ariane sur les pages profondes ;
- aucune page stratégique ne doit être orpheline.

---

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

2. **Problèmes clients**
   - manque de visibilité ;
   - image peu professionnelle ;
   - site lent ou peu convaincant ;
   - outils internes désorganisés ;
   - absence de stratégie SEO ou de suivi.

3. **Solutions**
   - aperçu des grandes familles de services ;
   - lien vers chaque page dédiée.

4. **Approche globale**
   - stratégie ;
   - design ;
   - développement ;
   - contenu ;
   - visibilité ;
   - suivi.

5. **Réalisations ou preuves**
   - uniquement projets réels ;
   - contexte, intervention, résultat démontrable ;
   - pas de chiffres sans méthode de mesure.

6. **Méthode**
   - découverte ;
   - cadrage ;
   - conception ;
   - développement ;
   - validation ;
   - lancement ;
   - amélioration.

7. **Pourquoi Devzair**
   - agence à taille humaine ;
   - interlocution claire ;
   - approche sur mesure ;
   - qualité technique ;
   - continuité après la mise en ligne.

8. **FAQ éditoriale**
   - questions réellement utiles ;
   - réponses courtes puis approfondissement ;
   - ne pas ajouter de balisage FAQ dans le seul but d’obtenir un résultat enrichi Google.

9. **CTA final**
   - formulaire court ou lien vers contact ;
   - explication de la prochaine étape.

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

## 8. Identité visuelle et expérience utilisateur

## 8.1 Principes

- image premium mais accessible ;
- design humain, sobre et distinctif ;
- hiérarchie visuelle forte ;
- espaces respirants ;
- cohérence entre stratégie, développement, photographie et SEO ;
- éviter l’apparence de thème générique ou d’agence surdimensionnée.

## 8.2 Design system minimal

Définir avant intégration :

- palette ;
- typographies et solutions de repli ;
- échelle d’espacement ;
- grille ;
- largeurs de contenu ;
- boutons ;
- liens ;
- formulaires ;
- cartes ;
- messages d’état ;
- tableaux ;
- modales ;
- navigation ;
- icônes ;
- styles de focus ;
- règles d’animation ;
- traitement des images.

Chaque composant doit documenter ses variantes, états, comportement responsive et règles d’accessibilité.

## 8.3 Responsive

Tester au minimum :

- petit mobile ;
- mobile standard ;
- tablette ;
- ordinateur portable ;
- grand écran ;
- zoom navigateur à 200 % ;
- orientation portrait et paysage lorsque pertinente.

Ne pas concevoir uniquement à partir de largeurs fixes.

---

## 9. Accessibilité

### Cible

Viser WCAG 2.2 niveau AA pour le parcours principal.

### Contrôles

- HTML sémantique ;
- ordre logique des titres ;
- navigation clavier complète ;
- focus visible et non masqué ;
- lien d’évitement ;
- contrastes suffisants ;
- texte redimensionnable ;
- formulaires correctement étiquetés ;
- erreurs annoncées et compréhensibles ;
- alternatives textuelles adaptées ;
- sous-titres et transcriptions pour les médias concernés ;
- animations réduites si `prefers-reduced-motion` ;
- zones cliquables suffisantes ;
- pas d’information transmise uniquement par la couleur ;
- compatibilité avec lecteurs d’écran sur les parcours principaux.

### Tests

- analyse automatisée ;
- navigation clavier manuelle ;
- lecteur d’écran sur les parcours critiques ;
- zoom et reflow ;
- contrôle des contrastes ;
- audit après changement majeur de composants.

L’automatisation ne remplace pas les vérifications manuelles.

---

## 10. SEO technique

## 10.1 Rendu et accessibilité aux robots

- le contenu stratégique doit être présent dans le HTML rendu ;
- privilégier SSR, SSG ou rendu serveur équivalent pour les pages publiques ;
- ne pas dépendre d’une interaction utilisateur pour charger le contenu principal ;
- utiliser des liens HTML explorables ;
- vérifier le rendu avec les outils d’inspection des moteurs ;
- ne pas masquer du contenu important dans des images ou vidéos sans équivalent textuel.

## 10.2 Métadonnées

Chaque page indexable doit avoir :

- un `<title>` unique, descriptif et naturel ;
- une meta description spécifique ;
- une URL canonique absolue ;
- un H1 principal cohérent ;
- une hiérarchie H2/H3 logique ;
- les balises Open Graph nécessaires ;
- les métadonnées sociales pertinentes ;
- une langue de document correcte ;
- un `noindex` explicite pour les pages non destinées aux résultats.

Les longueurs ne doivent pas être forcées mécaniquement : l’objectif est la clarté et l’absence de troncature inutile.

## 10.3 Indexation

- un sitemap XML contenant uniquement les URL canoniques, publiques et indexables ;
- `lastmod` uniquement s’il reflète une modification réelle ;
- robots.txt à la racine ;
- ne pas utiliser robots.txt pour protéger une information privée ;
- utiliser authentification, suppression, `noindex` ou en-tête X-Robots-Tag selon le cas ;
- exclure préproduction, administration, résultats internes et paramètres inutiles ;
- vérifier les codes HTTP ;
- page supprimée sans équivalent : 404 ou 410 ;
- page déplacée : 301 vers l’équivalent le plus proche ;
- éviter les chaînes et boucles de redirection ;
- corriger les soft 404.

## 10.4 Canonicalisation et variantes

- une seule version de domaine : HTTPS et choix clair entre domaine racine et `www` ;
- redirection permanente des variantes ;
- pas de canonical vers une page non équivalente ;
- paramètres de tracking sans duplication indexable ;
- cohérence entre canonical, sitemap, liens internes et redirections ;
- si multilingue : URL distincte, contenu réellement traduit, `hreflang` réciproque et canonique dans la même langue.

## 10.5 Données structurées

Implémenter uniquement les données exactes, visibles et pertinentes :

- `Organization` sur l’accueil ou la page agence ;
- sous-type approprié si la situation réelle le permet ;
- `WebSite` ;
- `BreadcrumbList` ;
- `Article` ou `BlogPosting` pour les ressources ;
- données de profil d’auteur si l’auteur est réel ;
- types complémentaires de schema.org lorsque cohérents, sans promettre de résultat enrichi.

### Règles

- JSON-LD recommandé ;
- identifiants `@id` stables ;
- logo, nom, URL, coordonnées et profils cohérents ;
- ne pas créer de notes ou avis auto-attribués ;
- ne pas baliser un contenu absent de la page ;
- valider la syntaxe et les règles Google applicables ;
- surveiller les avertissements et actions manuelles.

**Note 2026 :** les résultats enrichis FAQ ont été retirés de Google. Les sections de questions-réponses restent utiles aux visiteurs et à la compréhension du contenu, mais ne doivent pas être créées pour obtenir cet ancien affichage.

## 10.6 Images et médias

- formats modernes lorsque compatibles ;
- compression adaptée ;
- dimensions explicites ;
- `srcset` et tailles responsives ;
- chargement différé hors écran ;
- ne pas différer l’image LCP principale ;
- textes alternatifs contextuels ;
- noms de fichiers descriptifs sans sur-optimisation ;
- légendes lorsque utiles ;
- droits, crédits et autorisations documentés ;
- sitemap image seulement si nécessaire.

## 10.7 Performance

Cibles terrain au 75e centile, mobile et ordinateur :

- LCP ≤ 2,5 s ;
- INP ≤ 200 ms ;
- CLS ≤ 0,1.

### Budget initial à définir après choix technique

- poids JavaScript ;
- poids CSS ;
- poids total initial ;
- nombre de polices ;
- taille maximale des images ;
- nombre de scripts tiers ;
- temps serveur ;
- seuil Lighthouse de contrôle en CI, sans le confondre avec les données réelles utilisateurs.

### Actions

- réduire le JavaScript client ;
- supprimer le code inutilisé ;
- optimiser les polices ;
- précharger uniquement les ressources critiques ;
- cache HTTP correct ;
- CDN si pertinent ;
- compression Brotli ou gzip ;
- images responsives ;
- éviter les scripts tiers non essentiels ;
- mesurer en laboratoire et sur le terrain.

---


## 10.8 Implémentation SEO obligatoire avec Nuxt

### Principe général

Nuxt doit conserver son rendu universel. Une page publique importante ne doit pas utiliser `ssr: false`. Le HTML initial doit déjà contenir :

- le titre principal ;
- le contenu éditorial essentiel ;
- les liens internes ;
- le `<title>` ;
- la meta description ;
- la canonical ;
- les balises Open Graph ;
- les données structurées utiles.

Le chargement client ne doit servir qu’à enrichir l’expérience, pas à rendre le contenu indexable.

### Répartition des responsabilités SEO

| Élément | Emplacement recommandé |
|---|---|
| Nom, URL et identité globale | configuration centrale du site |
| Langue, favicon, title template | `app/app.vue` ou configuration globale |
| Métadonnées propres à une page | page concernée via `usePageSeo()` |
| Canonical | composable SEO central |
| Robots propres à une page | composable SEO ou règles de route |
| Schema.org global | configuration SEO ou composable global |
| Schema.org d’un article | page dynamique de l’article |
| Sitemap et robots.txt | module SEO ou route serveur dédiée |
| Redirections | `routeRules` ou reverse proxy selon le cas |
| Pages non indexables | meta robots et protection réelle si privées |

### Composable central `usePageSeo`

Créer un seul composable pour les métadonnées communes afin d’éviter la répétition :

```ts
// apps/web/app/composables/usePageSeo.ts
export interface PageSeoInput {
  title: string
  description: string
  image?: string
  canonicalPath?: string
  robots?: 'index, follow' | 'noindex, follow' | 'noindex, nofollow'
  type?: 'website' | 'article'
}

export function usePageSeo(input: PageSeoInput) {
  const route = useRoute()
  const config = useRuntimeConfig()

  const canonicalUrl = computed(() => {
    const path = input.canonicalPath ?? route.path
    return new URL(path, config.public.siteUrl).toString()
  })

  const imageUrl = computed(() => new URL(
    input.image ?? '/images/og/default.jpg',
    config.public.siteUrl,
  ).toString())

  useSeoMeta({
    title: input.title,
    description: input.description,
    robots: input.robots ?? 'index, follow',
    ogTitle: input.title,
    ogDescription: input.description,
    ogType: input.type ?? 'website',
    ogUrl: () => canonicalUrl.value,
    ogImage: () => imageUrl.value,
    twitterCard: 'summary_large_image',
    twitterTitle: input.title,
    twitterDescription: input.description,
    twitterImage: () => imageUrl.value,
  })

  useHead({
    link: [
      {
        rel: 'canonical',
        href: () => canonicalUrl.value,
      },
    ],
  })
}
```

### Règles du composable

- `siteUrl` doit être une URL absolue définie dans `runtimeConfig.public`.
- `route.path` est préféré à `route.fullPath` afin d’exclure les paramètres de tracking.
- Les images sociales doivent être absolues.
- Les titres, descriptions et images restent propres à chaque page.
- Ne pas utiliser `useServerSeoMeta` dans le nouveau code : il est déprécié ; utiliser `useSeoMeta`, éventuellement dans `if (import.meta.server)` pour des métadonnées statiques.
- Ne pas appeler `usePageSeo` depuis plusieurs composants d’une même page.
- Une page dynamique doit attendre ses données avec `useFetch` ou `useAsyncData` avant de définir ses métadonnées.
- Une ressource absente doit générer une vraie erreur 404 avec `createError`.

### Exemple d’une page statique

```vue
<script setup lang="ts">
usePageSeo({
  title: 'Agence digitale pour les entreprises',
  description: 'Devzair conçoit des sites, applications et stratégies de visibilité adaptés aux entreprises.',
  canonicalPath: '/',
})
</script>

<template>
  <main>
    <h1>Des solutions digitales complètes pour votre entreprise</h1>
  </main>
</template>
```

### Exemple futur d’un article Symfony

```vue
<script setup lang="ts">
const route = useRoute()
const slug = computed(() => String(route.params.slug))

const { data: article, error } = await useFetch(
  () => `/api/articles/${encodeURIComponent(slug.value)}`,
  {
    key: () => `article:${slug.value}`,
  },
)

if (error.value || !article.value) {
  throw createError({
    statusCode: 404,
    statusMessage: 'Article introuvable',
  })
}

usePageSeo({
  title: article.value.seoTitle || article.value.title,
  description: article.value.metaDescription,
  image: article.value.ogImage,
  canonicalPath: `/ressources/${article.value.slug}`,
  type: 'article',
})
</script>
```

### Rendu hybride

La stratégie initiale doit être simple :

```ts
// apps/web/nuxt.config.ts
export default defineNuxtConfig({
  routeRules: {
    '/': { prerender: true },
    '/agence': { prerender: true },
    '/methode': { prerender: true },
    '/services': { prerender: true },
    '/services/**': { prerender: true },

    // À conserver en SSR tant que la stratégie de cache du blog
    // et son invalidation ne sont pas définies.
    '/ressources/**': { ssr: true },
  },
})
```

Règles :

- pré-rendre les pages marketing stables ;
- conserver le SSR pour les pages dynamiques au début ;
- n’activer SWR ou ISR qu’avec une durée, une invalidation et une stratégie de fraîcheur documentées ;
- ne jamais mettre en cache une réponse personnalisée ou privée ;
- toutes les pages pré-rendues doivent être accessibles par de vrais liens HTML ou déclarées explicitement.

### Modules SEO

Ne pas ajouter de module avant que le socle Nuxt passe `build`, `typecheck` et `lint`.

Après ce point de contrôle, la solution recommandée est d’évaluer puis d’installer le module consolidé **Nuxt SEO** :

```bash
docker compose exec web npx nuxt module add seo
```

Il peut centraliser notamment :

- la configuration du site ;
- le sitemap XML ;
- robots.txt ;
- Schema.org ;
- les images Open Graph ;
- les utilitaires SEO.

Avant validation :

- contrôler la compatibilité avec la version verrouillée de Nuxt ;
- consulter les changements de version ;
- vérifier le HTML réellement généré ;
- ne pas accepter une configuration automatique sans test ;
- ne pas ajouter d’autres modules couvrant le même besoin.

### Checklist SEO pour chaque page

- [ ] Une intention principale claire.
- [ ] Un `<title>` unique.
- [ ] Une meta description unique.
- [ ] Une canonical absolue et correcte.
- [ ] Un seul H1 visible.
- [ ] Une structure H2/H3 logique.
- [ ] Le contenu essentiel est rendu côté serveur.
- [ ] Les liens sont de vrais `<a href>`.
- [ ] L’image sociale existe et est accessible.
- [ ] Le statut d’indexation est volontaire.
- [ ] Les données structurées correspondent au contenu visible.
- [ ] La page retourne le bon code HTTP.
- [ ] Aucun paramètre de tracking dans la canonical.
- [ ] Aucun contenu important uniquement après `onMounted`.
- [ ] Le HTML est contrôlé avec `curl` ou une inspection équivalente.

---

## 11. SEO éditorial et autorité

### Recherche sémantique

Pour chaque page :

1. identifier le public ;
2. définir le problème et l’intention ;
3. analyser les formulations réelles des prospects ;
4. choisir une intention principale ;
5. relever les sous-questions ;
6. analyser les pages concurrentes sans les copier ;
7. définir l’angle propre à Devzair ;
8. rédiger pour l’utilisateur ;
9. relier la page à l’offre et aux preuves ;
10. mesurer puis améliorer.

### Qualité

- contenu utile, original, à jour et vérifiable ;
- vocabulaire métier naturel ;
- réponses précises avant les développements détaillés ;
- exemples réels ;
- sources pour les sujets sensibles ou techniques ;
- pas de bourrage de mots-clés ;
- pas de texte généré en masse sans relecture et expertise ;
- pas de pages conçues uniquement pour capter une variante de requête.

### Autorité

- études de cas solides ;
- pages auteurs ou contributeurs réels ;
- biographies professionnelles exactes ;
- mentions et liens depuis des partenaires légitimes ;
- profils d’entreprise cohérents ;
- contenu cité et digne d’être référencé ;
- avis clients authentiques, datés et recueillis loyalement.

---

## 12. SEO local

À appliquer uniquement si Devzair remplit réellement les conditions de présence ou de zone de service.

### Socle

- nom, adresse éventuelle et téléphone cohérents ;
- informations de contact identiques sur les supports ;
- fiche d’établissement correctement configurée si éligible ;
- catégories exactes ;
- horaires réels ;
- zone desservie réelle ;
- photos originales ;
- description sans sur-optimisation ;
- avis authentiques ;
- réponses professionnelles aux avis ;
- liens vers les pages adaptées ;
- suivi des appels et demandes sans fausser les coordonnées publiques.

### Pages locales

Une page locale doit apporter :

- besoins spécifiques de la zone ;
- modalités réelles d’intervention ;
- réalisations locales autorisées ;
- témoignages locaux réels ;
- informations pratiques ;
- contenu unique.

Aucune page locale ne doit être publiée avant confirmation de la zone desservie.

---

## 13. GEO — visibilité dans les moteurs génératifs

Le GEO ne repose pas sur une norme universelle garantissant une citation. Il complète le SEO, la qualité éditoriale, l’autorité et la disponibilité technique.

## 13.1 Objectifs

- rendre l’identité de Devzair non ambiguë ;
- fournir des réponses claires et extractibles ;
- renforcer la vérifiabilité ;
- faciliter l’exploration par les robots autorisés ;
- développer des contenus susceptibles d’être cités ;
- mesurer les visites et mentions lorsque cela est techniquement possible.

## 13.2 Principes éditoriaux

- commencer les sections par une réponse directe ;
- utiliser des titres explicites ;
- définir les termes ;
- présenter les étapes, critères, limites et exemples ;
- dater les contenus susceptibles d’évoluer ;
- citer les sources primaires ;
- indiquer l’auteur ou le responsable éditorial ;
- documenter la méthode derrière les chiffres ;
- distinguer faits, interprétations et recommandations ;
- conserver une identité de marque cohérente sur le site et les profils externes ;
- produire des études originales et cas concrets.

## 13.3 Accessibilité technique aux systèmes d’IA

- contenu public accessible sans connexion ;
- contenu important présent dans le HTML ;
- robots.txt volontairement configuré ;
- ne pas bloquer involontairement les robots légitimes dans le CDN ou WAF ;
- surveiller les codes 403, 429 et erreurs de crawl ;
- autoriser ou refuser séparément les robots selon la politique de Devzair.

### Exemple de politique à décider

```txt
User-agent: OAI-SearchBot
Allow: /

User-agent: GPTBot
# Décision distincte concernant l’utilisation potentielle pour l’entraînement
Allow: /
```

- `OAI-SearchBot` concerne l’apparition dans la recherche ChatGPT.
- `GPTBot` concerne une politique distincte liée à l’entraînement.
- Toute décision doit être consignée dans le journal des décisions.
- Les plages IP et noms d’agents doivent être vérifiés dans la documentation officielle avant configuration ou allowlist.

### `llms.txt`

Peut être étudié comme fichier expérimental d’orientation, mais ne doit pas être considéré comme un standard officiel, une garantie de citation ou un remplacement de robots.txt, du sitemap, du HTML et des données structurées.

## 13.4 Contenus favorables à la citation

- définitions expertes ;
- méthodes détaillées ;
- checklists originales ;
- comparatifs transparents ;
- données propriétaires documentées ;
- études de cas ;
- réponses aux questions précises ;
- glossaires ;
- contenus de référence régulièrement maintenus.

## 13.5 Mesure GEO

- trafic référent identifiable depuis les outils d’IA ;
- pages d’entrée et conversions associées ;
- logs de crawl des agents autorisés ;
- tests périodiques sur une liste stable de questions ;
- présence, exactitude et sources des réponses observées ;
- évolution des recherches de marque ;
- liens et mentions obtenus.

Ne pas déclarer une amélioration GEO sur la base de quelques tests manuels isolés.

---


## 13.6 Mise en œuvre GEO dans Nuxt

Le GEO ne doit pas créer une seconde version du contenu réservée aux robots. Le même contenu fiable doit être accessible aux visiteurs, moteurs et systèmes d’IA autorisés.

### Exigences Nuxt

- conserver le SSR pour les contenus éditoriaux ;
- exposer les informations importantes dans le HTML initial ;
- utiliser des URL stables ;
- relier les pages par des liens HTML ;
- produire un graphe Schema.org cohérent entre `Organization`, `WebSite`, `WebPage`, `Service`, `Article` et `Person` lorsque ces entités sont réelles ;
- inclure auteurs, dates de publication, dates de modification et sources ;
- ajouter une réponse directe au début des sections importantes ;
- éviter les slogans vagues dépourvus de faits ;
- ne jamais masquer ou injecter un contenu différent selon le user-agent ;
- documenter la politique de chaque robot d’IA.

### Politique initiale recommandée

La décision définitive appartient au responsable du projet. Pour favoriser la présence dans ChatGPT Search tout en gardant un choix distinct pour l’entraînement :

```txt
User-agent: OAI-SearchBot
Allow: /

User-agent: GPTBot
Disallow: /
```

Cette politique peut être modifiée après décision explicite. `OAI-SearchBot` et `GPTBot` ont des fonctions distinctes. La configuration doit être testée sur l’URL publique de `robots.txt`.

### Éléments qui renforcent la citabilité

- une page agence précise et vérifiable ;
- des pages services détaillant méthode, livrables et limites ;
- des études de cas avec méthode de mesure ;
- des articles avec auteur et sources ;
- des définitions concises ;
- des tableaux comparatifs transparents ;
- des dates de mise à jour ;
- des données structurées sans informations inventées ;
- une identité cohérente sur les profils externes.

### Éléments non prioritaires

- `llms.txt` reste expérimental ;
- les versions Markdown automatiques ne remplacent pas le HTML ;
- un « score GEO » propriétaire ne constitue pas une preuve ;
- une citation ponctuelle par une IA ne démontre pas une progression durable.

---

## 14. Sécurité

### Référentiel

- OWASP Top 10:2025 comme socle de sensibilisation ;
- OWASP ASVS 5.0 :
  - niveau 1 minimum pour le site public ;
  - niveau 2 pour toute administration, authentification, API métier, espace client ou traitement sensible.

## 14.1 Modèle de menace initial

Identifier :

- données collectées ;
- rôles ;
- surfaces publiques ;
- formulaires ;
- administration ;
- stockage de fichiers ;
- services externes ;
- clés API ;
- pipeline de déploiement ;
- sauvegardes ;
- accès hébergeur ;
- scénarios d’abus ;
- impact d’une indisponibilité ou fuite.

Mettre à jour le modèle à chaque fonctionnalité importante.

## 14.2 Secrets et configuration

- aucun secret dans Git ;
- `.env.example` sans valeur sensible ;
- secrets distincts par environnement ;
- rotation possible ;
- droits minimaux ;
- suppression des comptes et clés inutilisés ;
- variables de production injectées par le gestionnaire de secrets ou l’hébergeur ;
- préproduction séparée ;
- désactivation du mode debug en production ;
- messages d’erreur publics non détaillés.

## 14.3 Dépendances et chaîne logicielle

- fichiers de verrouillage versionnés ;
- mises à jour contrôlées ;
- analyse automatique des dépendances ;
- revue des alertes ;
- provenance des paquets ;
- nombre de dépendances réduit ;
- interdiction des paquets abandonnés sans justification ;
- inventaire logiciel ou SBOM si l’architecture le justifie ;
- intégrité des artefacts de build ;
- branche principale protégée.

## 14.4 Entrées et sorties

- validation serveur par liste d’autorisation ;
- limites de taille et format ;
- requêtes paramétrées ;
- encodage selon le contexte ;
- échappement automatique conservé ;
- HTML utilisateur interdit sauf besoin démontré et sanitisation robuste ;
- protection contre injections SQL, commandes, modèles et en-têtes ;
- validation des URL externes et prévention SSRF ;
- aucune confiance accordée aux données client.

## 14.5 Authentification et administration

Si une administration existe :

- URL non considérée comme protection ;
- MFA pour les comptes privilégiés ;
- mots de passe stockés avec algorithme moderne fourni par le framework ;
- limitation des tentatives ;
- réinitialisation sécurisée ;
- vérification de l’adresse e-mail si nécessaire ;
- sessions rotatives ;
- cookies `Secure`, `HttpOnly`, `SameSite` adapté ;
- expiration et révocation ;
- réauthentification pour actions sensibles ;
- journalisation des connexions et actions administratives ;
- rôles et permissions explicites ;
- contrôle d’accès côté serveur sur chaque action.

## 14.6 CSRF, XSS et politiques navigateur

- protection CSRF pour les actions basées sur session ;
- aucune action d’écriture via GET ;
- CSP déployée progressivement, d’abord en report-only si nécessaire ;
- éviter `unsafe-inline` et `unsafe-eval` ;
- nonces ou hashes pour les scripts nécessaires ;
- politique de framing via `frame-ancestors` ;
- contrôle strict des sources de scripts, images, connexions et formulaires.

## 14.7 En-têtes de sécurité

Configurer et tester selon l’architecture :

- `Content-Security-Policy` ;
- `Strict-Transport-Security` après validation HTTPS complète ;
- `X-Content-Type-Options: nosniff` ;
- `Referrer-Policy` ;
- `Permissions-Policy` ;
- `frame-ancestors` dans CSP ;
- `X-Frame-Options` comme compatibilité si nécessaire ;
- type de contenu correct et UTF-8 ;
- suppression des en-têtes révélant inutilement la technologie.

La politique exacte doit être générée à partir des ressources réellement utilisées.

## 14.8 Téléversement de fichiers

À éviter dans le MVP si non indispensable. Si présent :

- extensions autorisées ;
- vérification de signature/type réel ;
- taille limitée ;
- nom généré ;
- stockage hors racine publique ou service dédié ;
- téléchargement via contrôleur ;
- contrôle d’accès ;
- analyse antivirus ou sandbox lorsque disponible ;
- suppression automatique selon rétention ;
- protection CSRF ;
- blocage des fichiers actifs ;
- aucune exécution dans le répertoire de stockage.

## 14.9 Formulaires et anti-abus

- limitation par IP et autres signaux avec prudence ;
- honeypot accessible ;
- temporisation ;
- validation de cohérence ;
- blocage des volumes anormaux ;
- CAPTCHA uniquement en dernier recours, avec solution accessible ;
- protection contre l’envoi massif d’e-mails ;
- destinataires imposés côté serveur ;
- aucun en-tête e-mail construit directement depuis une entrée utilisateur.

## 14.10 Transport, hébergement et réseau

- HTTPS obligatoire ;
- protocoles et suites modernes gérés par l’hébergeur ;
- redirection HTTP vers HTTPS ;
- accès d’administration restreints ;
- base de données non exposée publiquement ;
- pare-feu et règles minimales ;
- CDN/WAF configuré sans bloquer les robots choisis ;
- protection DDoS adaptée au risque ;
- environnements isolés.

## 14.11 Journaux, alertes et incidents

Journaliser sans enregistrer inutilement les données personnelles :

- échecs d’authentification ;
- changements de privilèges ;
- actions administratives ;
- erreurs serveur ;
- anomalies de formulaire ;
- alertes de sécurité ;
- déploiements ;
- sauvegardes.

Prévoir :

- horodatage cohérent ;
- accès restreint ;
- durée de conservation ;
- alertes exploitables ;
- procédure d’incident ;
- contact responsable ;
- rotation des secrets ;
- restauration ;
- analyse post-incident.

## 14.12 Sauvegardes

- sauvegardes automatiques ;
- chiffrement ;
- emplacement séparé ;
- rétention définie ;
- contrôle des succès ;
- test réel de restauration ;
- documentation du RPO/RTO lorsque le service devient critique.

---

## 15. Protection des données et conformité

Ce référentiel n’est pas un avis juridique. Les textes finaux doivent être validés selon la structure juridique, l’hébergement, les prestataires et les traitements réels.

### Inventaire des traitements

Pour chaque collecte :

- finalité ;
- données ;
- base légale ;
- destinataires ;
- sous-traitants ;
- transfert hors EEE ;
- durée de conservation ;
- mesures de sécurité ;
- droits des personnes ;
- preuve de consentement si requise.

### Minimisation

- ne collecter que le nécessaire ;
- rendre facultatifs les champs non indispensables ;
- ne pas demander de données sensibles par formulaire ;
- supprimer ou anonymiser à échéance ;
- séparer prospection et réponse à une demande.

### Cookies et traceurs

- aucun traceur non essentiel avant consentement lorsqu’il est requis ;
- bouton accepter et refuser au même niveau ;
- choix par finalité ;
- preuve du choix ;
- retrait aussi simple que l’acceptation ;
- lien permanent de gestion des préférences ;
- politique mise à jour ;
- vérification des scripts tiers après chaque changement.

Pour une mesure d’audience présentée comme exemptée de consentement, vérifier que la solution et sa configuration satisfont réellement toutes les conditions de la CNIL.

### Formulaires

- information courte à proximité ;
- lien vers la politique complète ;
- pas de case obligatoire de consentement si la base légale n’est pas le consentement ;
- opt-in séparé, facultatif et non précoché pour la prospection ;
- mécanisme de traitement des demandes d’accès, rectification, opposition et suppression.

---

## 16. Architecture technique confirmée

## 16.1 Architecture cible

```text
Internet
   |
   v
Caddy / reverse proxy
   |
   +--> /, /services/**, /ressources/**  --> Nuxt 4 / Nitro
   |
   +--> /api/**                         --> Symfony 7.4 LTS
                                                |
                                                v
                                           PostgreSQL
```

### Principes

- un seul domaine public ;
- Nuxt reste responsable du rendu des pages, de l’UX et des métadonnées ;
- Symfony devient responsable des données du blog, de la validation métier, de l’administration et des autorisations ;
- PostgreSQL stocke les articles, catégories, auteurs et données métier ;
- Caddy gère TLS, redirections, compression et routage ;
- le navigateur ne reçoit jamais de secret Symfony ;
- Nuxt peut utiliser son serveur Nitro comme BFF lorsque des secrets ou cookies doivent être protégés.

## 16.2 Structure du monorepo

```text
devzair/
├── apps/
│   ├── web/
│   │   ├── app/
│   │   │   ├── assets/
│   │   │   │   └── css/
│   │   │   ├── components/
│   │   │   │   ├── base/
│   │   │   │   ├── layout/
│   │   │   │   ├── sections/
│   │   │   │   └── features/
│   │   │   ├── composables/
│   │   │   ├── config/
│   │   │   ├── layouts/
│   │   │   ├── pages/
│   │   │   ├── repositories/
│   │   │   ├── services/
│   │   │   ├── types/
│   │   │   ├── utils/
│   │   │   ├── app.vue
│   │   │   └── error.vue
│   │   ├── server/
│   │   │   ├── api/
│   │   │   ├── middleware/
│   │   │   └── utils/
│   │   ├── shared/
│   │   │   ├── types/
│   │   │   └── utils/
│   │   ├── public/
│   │   ├── test/
│   │   ├── Dockerfile
│   │   ├── Dockerfile.dev
│   │   ├── nuxt.config.ts
│   │   └── package.json
│   └── api/                         # ajouté à la phase Symfony
├── infra/
│   └── caddy/
├── docs/
│   └── adr/
├── compose.yaml
├── compose.prod.yaml
├── .env.example
├── .gitignore
├── Makefile
├── README.md
└── DEVZAIR_REFERENTIEL_PROJET.md
```

### Usage des répertoires Nuxt

| Répertoire | Responsabilité |
|---|---|
| `app/pages` | Orchestration des pages, récupération initiale et métadonnées |
| `app/components/base` | Composants élémentaires du design system |
| `app/components/layout` | Header, footer, navigation et structures globales |
| `app/components/sections` | Sections éditoriales réutilisables |
| `app/components/features` | Composants liés à une fonctionnalité |
| `app/composables` | Adaptation réactive Vue/Nuxt de cas d’usage |
| `app/services` | Logique applicative et orchestration indépendante de l’affichage |
| `app/repositories` | Contrats et adaptateurs d’accès aux données |
| `app/config` | Navigation, identité du site et configuration statique |
| `app/types` | Types propres à l’application frontend |
| `app/utils` | Fonctions pures sans état |
| `server/api` | Endpoints Nitro publics ou BFF |
| `server/utils` | Logique exclusivement serveur |
| `shared` | Types et fonctions réellement utilisables côté application et serveur |
| `public` | Fichiers servis tels quels |
| `test` | Tests unitaires, intégration et E2E |

Ne pas créer `layers/` pendant le MVP. Les layers deviennent pertinents seulement lorsqu’un domaine ou un design system doit être isolé ou partagé.

## 16.3 Application pragmatique de SOLID

### S — Responsabilité unique

- une page assemble les sections et définit son SEO ;
- un composant de présentation affiche des données reçues ;
- un composable gère un état ou un cas d’usage lié à Vue ;
- un service orchestre une action métier ;
- un repository accède à une source de données ;
- un utilitaire réalise une transformation pure.

Signaux d’alerte :

- composant dépassant plusieurs responsabilités ;
- appel API, validation, analytics et rendu dans le même fichier ;
- fichier générique nommé `helpers.ts` contenant des fonctions sans rapport ;
- page contenant tout le contenu, la logique et le style du projet.

### O — Ouvert à l’extension, fermé aux modifications inutiles

- utiliser des props typées et des slots pour les variantes réelles ;
- préférer la composition aux grands blocs conditionnels ;
- ajouter une variante sans casser les usages existants ;
- ne pas créer un composant universel configurable par des dizaines de props.

### L — Substitution

- un adaptateur de données doit respecter le contrat qu’il implémente ;
- une version mock, Symfony ou Nitro doit retourner les mêmes structures attendues ;
- les composants ne doivent pas dépendre des détails de la source.

### I — Ségrégation des interfaces

- créer de petits types ciblés ;
- ne pas transmettre un objet `Article` complet lorsqu’un composant n’utilise que `title`, `slug` et `excerpt` ;
- séparer les modèles de liste, détail et administration ;
- ne pas exposer les champs internes de Symfony au frontend.

### D — Inversion des dépendances

Le code métier dépend de contrats et non d’un appel `$fetch` dispersé.

```ts
// shared/types/article.ts
export interface ArticleSummary {
  slug: string
  title: string
  excerpt: string
  publishedAt: string
}

// app/repositories/ArticleRepository.ts
export interface ArticleRepository {
  findAll(): Promise<ArticleSummary[]>
  findBySlug(slug: string): Promise<ArticleDetail | null>
}
```

Un adaptateur Symfony implémentera ce contrat. Pour les premières pages statiques, ne pas créer de repository vide : ajouter cette abstraction au moment de l’intégration réelle du blog.

## 16.4 DRY, KISS et règle de trois

### À centraliser

- identité du site ;
- navigation ;
- coordonnées validées ;
- textes légaux récurrents ;
- génération des canonicals ;
- métadonnées communes ;
- boutons et champs de formulaire ;
- appels à l’API ;
- types d’articles ;
- formats de date ;
- gestion des erreurs ;
- événements analytics.

### À ne pas centraliser prématurément

Deux sections visuellement proches ne doivent pas forcément devenir un seul composant générique. Appliquer la règle suivante :

1. premier usage : implémentation claire ;
2. deuxième usage : observer les ressemblances et différences ;
3. troisième usage stable : extraire l’abstraction si elle réduit réellement le code et la complexité.

### Interdictions

- copier-coller un composant puis le modifier légèrement ;
- dupliquer nom, URL ou coordonnées de Devzair dans plusieurs pages ;
- dupliquer la logique de canonical ;
- appeler l’API Symfony depuis plusieurs composants avec des formats différents ;
- créer des composants `BaseSection` ou `UniversalCard` capables de tout faire ;
- stocker une logique métier dans le template Vue ;
- utiliser `any` pour contourner le typage.

## 16.5 Configuration Nuxt initiale

```ts
// apps/web/nuxt.config.ts
export default defineNuxtConfig({
  compatibilityDate: '2026-08-01',

  devtools: {
    enabled: true,
  },

  typescript: {
    strict: true,
    typeCheck: true,
  },

  runtimeConfig: {
    // Secrets exclusivement serveur à ajouter ici plus tard.
    public: {
      siteUrl: '',
      apiBaseUrl: '/api',
    },
  },

  app: {
    head: {
      htmlAttrs: {
        lang: 'fr',
      },
      meta: [
        {
          name: 'viewport',
          content: 'width=device-width, initial-scale=1',
        },
      ],
    },
  },

  routeRules: {
    '/': { prerender: true },
    '/agence': { prerender: true },
    '/methode': { prerender: true },
    '/services': { prerender: true },
    '/services/**': { prerender: true },
  },
})
```

Règles :

- ne jamais mettre un secret dans `runtimeConfig.public` ;
- déclarer les clés dans `runtimeConfig` avant de les surcharger par variables `NUXT_*` ;
- ne pas lire directement `process.env` dans les composants ;
- ne pas modifier les fichiers générés dans `.nuxt` ;
- conserver TypeScript strict ;
- ne pas désactiver SSR globalement ;
- ne pas activer une option expérimentale sans ADR.

## 16.6 Environnement Docker

### Objectif

Le conteneur de développement doit démarrer de façon reproductible sans lancer `npm install` à chaque démarrage.

### `apps/web/Dockerfile.dev`

```dockerfile
FROM node:24-alpine

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
```

### `compose.yaml`

```yaml
services:
  web:
    build:
      context: ./apps/web
      dockerfile: Dockerfile.dev
    working_dir: /app
    ports:
      - "3000:3000"
    volumes:
      - ./apps/web:/app
      - web_node_modules:/app/node_modules
    environment:
      NUXT_PUBLIC_SITE_URL: http://localhost:3000
      NUXT_PUBLIC_API_BASE_URL: /api
    restart: unless-stopped

volumes:
  web_node_modules:
```

### Commandes

```bash
docker compose build web
docker compose up -d
docker compose logs -f web
docker compose exec web npm run typecheck
docker compose exec web npm run lint
docker compose exec web npm run test
docker compose exec web npm run build
docker compose down
```

Après modification de `package.json` ou `package-lock.json` :

```bash
docker compose build --no-cache web
docker compose up -d
```

## 16.7 Outils de qualité à installer avant les pages

```bash
docker compose exec web npx nuxt module add eslint
docker compose exec web npm install --save-dev typescript vue-tsc
docker compose exec web npm install --save-dev \
  @nuxt/test-utils vitest @vue/test-utils happy-dom playwright
```

Scripts recommandés :

```json
{
  "scripts": {
    "dev": "nuxt dev",
    "build": "nuxt build",
    "preview": "nuxt preview",
    "postinstall": "nuxt prepare",
    "typecheck": "nuxt typecheck",
    "lint": "eslint .",
    "lint:fix": "eslint . --fix",
    "test": "vitest run",
    "test:watch": "vitest",
    "test:e2e": "playwright test"
  }
}
```

Chaque installation doit être suivie de :

```bash
docker compose exec web npm run typecheck
docker compose exec web npm run lint
docker compose exec web npm run build
```

## 16.8 Flux futur entre Nuxt et Symfony

### Lecture publique

```text
Page Nuxt
  -> composable
      -> service
          -> ArticleRepository
              -> adaptateur Symfony
                  -> /api/articles
```

### Écriture et administration

- les opérations d’administration sont réalisées dans une interface protégée ;
- Symfony valide toutes les données ;
- les autorisations sont contrôlées côté Symfony ;
- un middleware de route Nuxt ne constitue jamais une sécurité suffisante ;
- les tokens sensibles ne sont pas stockés dans `localStorage` ;
- les secrets et appels privilégiés passent côté serveur.

### Contenu HTML du blog

Éviter `v-html`. Préférer :

1. un contenu structuré en blocs rendus par composants contrôlés ;
2. ou un HTML assaini côté Symfony avec une politique stricte ;
3. puis une défense complémentaire côté frontend si nécessaire.

Aucun HTML fourni par un utilisateur ne doit être rendu sans assainissement.

## 16.9 ADR obligatoires

- `ADR-001` — Nuxt 4, Vue et TypeScript strict ;
- `ADR-002` — Docker Compose et monorepo ;
- `ADR-003` — stratégie SSR/pré-rendu ;
- `ADR-004` — module SEO retenu ;
- `ADR-005` — design system ;
- `ADR-006` — Symfony 7.4 LTS et PostgreSQL ;
- `ADR-007` — contrat API du blog ;
- `ADR-008` — authentification de l’administration ;
- `ADR-009` — stratégie de cache et invalidation ;
- `ADR-010` — analytics et consentement.

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

## 18. Analytics et mesure

### Principe

Mesurer uniquement ce qui sert une décision.

### Événements

- envoi réussi du formulaire ;
- erreur de formulaire ;
- clic e-mail ;
- clic téléphone ;
- CTA principal ;
- consultation d’étude de cas ;
- passage d’une page service au contact ;
- téléchargement réel ;
- prise de rendez-vous si ajoutée.

### Qualité des données

- plan de marquage versionné ;
- environnement de test exclu ;
- trafic interne filtré selon méthode licite ;
- aucune donnée personnelle dans les URL ou événements ;
- noms d’événements stables ;
- consentement respecté ;
- tests avant mise en production ;
- documentation des changements.

### Tableaux de bord

- acquisition ;
- comportement ;
- conversion ;
- SEO ;
- performance ;
- disponibilité ;
- erreurs ;
- GEO identifiable.

---

## 19. Tests et assurance qualité

## 19.1 Tests automatisés

Selon la pile :

- tests unitaires ;
- intégration ;
- composants ;
- API ;
- formulaires ;
- permissions ;
- génération des métadonnées ;
- sitemap ;
- robots.txt ;
- données structurées ;
- redirections ;
- tests end-to-end des parcours critiques ;
- tests d’accessibilité automatisés ;
- analyse statique ;
- dépendances ;
- recherche de secrets.

## 19.2 Tests manuels

- contenus et orthographe ;
- navigation ;
- mobile ;
- clavier ;
- lecteur d’écran sur parcours critiques ;
- formulaires ;
- e-mails ;
- erreurs ;
- liens ;
- partage social ;
- impression si utile ;
- consentement ;
- indexabilité ;
- données structurées ;
- performance ;
- sécurité de configuration ;
- restauration de sauvegarde.

## 19.3 Navigateurs

Définir une matrice basée sur l’audience. Par défaut :

- versions récentes de Chrome, Edge, Firefox et Safari ;
- Safari iOS ;
- Chrome Android.

Documenter toute incompatibilité acceptée.

---

## 20. CI/CD et environnements

### Environnements

- local ;
- test automatisé ;
- préproduction protégée ;
- production.

### Préproduction

- accès protégé ;
- `noindex` en en-tête ou authentification ;
- absence du sitemap public de production ;
- données de test non sensibles ;
- e-mails redirigés ou désactivés ;
- clés distinctes.

### Pipeline minimal

1. installation reproductible ;
2. lint ;
3. analyse de types ;
4. tests ;
5. build ;
6. analyse de dépendances ;
7. recherche de secrets ;
8. contrôle accessibilité/SEO de base ;
9. déploiement préproduction ;
10. smoke tests ;
11. approbation ;
12. déploiement production ;
13. smoke tests production ;
14. possibilité de rollback.

### Déploiement

- migrations réversibles ou procédure de retour ;
- sauvegarde avant changement risqué ;
- journal de version ;
- contrôle des variables ;
- purge cache maîtrisée ;
- surveillance renforcée après livraison.

---

## 21. Phases de réalisation — ordre impératif

Une phase ne doit pas être sautée. Une page ou un module ne commence que si le critère de sortie de la phase précédente est rempli.

## Phase 0 — Cadrage fonctionnel

- [ ] Confirmer les services du MVP.
- [ ] Confirmer les cibles prioritaires.
- [ ] Confirmer les zones réellement desservies.
- [ ] Inventorier les réalisations et preuves publiables.
- [ ] Confirmer les coordonnées professionnelles.
- [ ] Confirmer le domaine canonique.
- [ ] Définir les conversions attendues.
- [ ] Recenser les données personnelles collectées.
- [ ] Valider l’arborescence éditoriale.
- [ ] Marquer chaque information inconnue `À VALIDER`.

### Critère de sortie

Les pages initiales, leurs objectifs et leurs contenus nécessaires sont connus.

---

## Phase 1 — Dépôt et Docker Nuxt

### État actuel : EN COURS

- [x] Créer `apps/web` avec le template Nuxt `minimal`.
- [x] Utiliser npm.
- [x] Refuser le dépôt Git imbriqué.
- [x] Refuser l’installation automatique de modules.
- [ ] Initialiser Git à la racine du monorepo.
- [ ] Ajouter `.gitignore` racine.
- [ ] Ajouter `Dockerfile.dev`.
- [ ] Ajouter `compose.yaml`.
- [ ] Ajouter `.env.example`.
- [ ] Ajouter un `README.md`.
- [ ] Ajouter le présent référentiel.
- [ ] Vérifier l’accès à `http://localhost:3000`.
- [ ] Exécuter un premier `npm run build`.
- [ ] Créer le premier commit propre.

### Critère de sortie

Le projet démarre uniquement avec Docker, le build passe et la base est versionnée.

---

## Phase 2 — Qualité et architecture Nuxt

- [ ] Installer `@nuxt/eslint`.
- [ ] Installer TypeScript et `vue-tsc`.
- [ ] Activer le typecheck.
- [ ] Installer Vitest et Nuxt Test Utils.
- [ ] Préparer Playwright.
- [ ] Ajouter les scripts npm.
- [ ] Créer l’arborescence `app`, `server` et `shared`.
- [ ] Créer `app.vue`.
- [ ] Créer le layout par défaut.
- [ ] Créer `error.vue`.
- [ ] Créer la première page.
- [ ] Ajouter la configuration centralisée du site.
- [ ] Documenter les conventions de nommage.
- [ ] Ajouter une CI minimale.
- [ ] Exécuter lint, typecheck, tests et build.

### Critère de sortie

Les outils empêchent l’introduction de code non typé, mal structuré ou non testable.

---

## Phase 3 — Design system et accessibilité de base

- [ ] Définir les tokens de couleur.
- [ ] Définir typographies et espacements.
- [ ] Définir conteneurs et grille.
- [ ] Créer les composants de base nécessaires seulement.
- [ ] Créer header, footer et navigation.
- [ ] Tester clavier et focus.
- [ ] Tester les contrastes.
- [ ] Tester mobile et zoom.
- [ ] Documenter les variantes.
- [ ] Éviter les composants universels surconfigurés.

### Critère de sortie

Le layout et les composants indispensables sont accessibles, typés et réutilisables.

---

## Phase 4 — Socle SEO Nuxt

- [ ] Définir `siteUrl`.
- [ ] Configurer la langue.
- [ ] Configurer le title template.
- [ ] Créer `usePageSeo`.
- [ ] Créer l’image Open Graph par défaut.
- [ ] Ajouter les canonicals.
- [ ] Définir la politique d’indexation par environnement.
- [ ] Configurer le pré-rendu des pages marketing.
- [ ] Évaluer puis installer Nuxt SEO.
- [ ] Générer robots.txt.
- [ ] Générer sitemap.xml.
- [ ] Ajouter `Organization` et `WebSite`.
- [ ] Tester les métadonnées dans le HTML serveur.
- [ ] Tester les codes HTTP et redirections.
- [ ] Ajouter des tests SEO automatisés ciblés.

### Critère de sortie

Une page de démonstration contient toutes ses métadonnées dans le HTML initial et l’environnement de préproduction ne peut pas être indexé.

---

## Phase 5 — Contenus et pages principales

Ordre recommandé :

1. accueil ;
2. services ;
3. première page service complète ;
4. agence ;
5. méthode ;
6. contact ;
7. autres services ;
8. réalisations.

Pour chaque page :

- [ ] brief éditorial ;
- [ ] intention principale ;
- [ ] contenu validé ;
- [ ] composants nécessaires ;
- [ ] responsive ;
- [ ] accessibilité ;
- [ ] métadonnées ;
- [ ] canonical ;
- [ ] maillage ;
- [ ] données structurées si pertinentes ;
- [ ] test HTML serveur ;
- [ ] test fonctionnel.

### Critère de sortie

Les pages ne contiennent ni faux contenu, ni placeholder, ni duplication structurelle inutile.

---

## Phase 6 — Formulaire de contact

- [ ] Définir les champs minimaux.
- [ ] Créer un schéma de validation.
- [ ] Valider côté serveur.
- [ ] Ajouter CSRF si le mécanisme choisi l’exige.
- [ ] Ajouter limitation de débit.
- [ ] Ajouter protection anti-spam progressive.
- [ ] Ajouter e-mail transactionnel.
- [ ] Ajouter messages accessibles.
- [ ] Ajouter notice de confidentialité.
- [ ] Tester erreurs et succès.
- [ ] Ne pas exposer de clé d’e-mail au navigateur.
- [ ] Ajouter les événements analytics après décision de consentement.

### Critère de sortie

Une demande réelle est transmise de façon sûre, traçable et conforme.

---

## Phase 7 — Réalisations et autorité éditoriale

- [ ] Modèle d’étude de cas.
- [ ] Preuves et autorisations.
- [ ] Périodes et méthodes de mesure.
- [ ] Composants de liste et détail.
- [ ] Schema.org adapté.
- [ ] Maillage vers les services.
- [ ] Pages auteurs réels si utiles.
- [ ] Politique de mise à jour.

### Critère de sortie

Chaque preuve publiée est vérifiable et contextualisée.

---

## Phase 8 — Création de l’API Symfony

- [ ] Créer `apps/api` avec Symfony 7.4 LTS.
- [ ] Ajouter Docker PHP.
- [ ] Ajouter PostgreSQL.
- [ ] Configurer migrations.
- [ ] Définir Article, Catégorie et Auteur.
- [ ] Définir DTO publics.
- [ ] Définir validation.
- [ ] Définir statuts éditoriaux.
- [ ] Définir permissions.
- [ ] Créer endpoints publics.
- [ ] Créer administration protégée.
- [ ] Ajouter tests Symfony.
- [ ] Documenter OpenAPI si retenu.
- [ ] Définir la sanitisation du contenu.

### Critère de sortie

L’API retourne des contrats stables, validés, documentés et testés.

---

## Phase 9 — Intégration du blog dans Nuxt

- [ ] Créer les types partagés frontend.
- [ ] Créer `ArticleRepository`.
- [ ] Créer l’adaptateur Symfony.
- [ ] Créer les composables de liste et détail.
- [ ] Créer `/ressources`.
- [ ] Créer `/ressources/[slug]`.
- [ ] Récupérer les données pendant le SSR.
- [ ] Gérer 404 et erreurs.
- [ ] Ajouter métadonnées dynamiques.
- [ ] Ajouter Schema.org `Article`.
- [ ] Ajouter auteur et dates.
- [ ] Ajouter sitemap dynamique.
- [ ] Ajouter flux RSS si pertinent.
- [ ] Définir cache et invalidation.
- [ ] Tester la publication et la mise à jour.

### Critère de sortie

Un article publié dans Symfony est visible sous une URL Nuxt canonique avec HTML, métadonnées et données structurées corrects.

---

## Phase 10 — SEO local et GEO avancé

- [ ] Valider l’éligibilité locale.
- [ ] Harmoniser les informations d’entreprise.
- [ ] Configurer les profils officiels.
- [ ] Définir la politique `OAI-SearchBot`.
- [ ] Définir la politique `GPTBot`.
- [ ] Vérifier WAF et CDN.
- [ ] Ajouter sources et dates aux contenus.
- [ ] Vérifier le graphe des entités.
- [ ] Tester une liste stable de requêtes.
- [ ] Mesurer les référents IA identifiables.
- [ ] Étudier `llms.txt` sans le considérer obligatoire.

### Critère de sortie

L’identité, les contenus et la politique des robots sont cohérents et vérifiables.

---

## Phase 11 — Sécurité, performance et accessibilité

- [ ] Audit OWASP.
- [ ] Revue des secrets.
- [ ] Revue des dépendances.
- [ ] CSP.
- [ ] En-têtes.
- [ ] Permissions.
- [ ] Analyse des formulaires.
- [ ] Contrôle des contenus HTML.
- [ ] Audit WCAG 2.2 AA.
- [ ] Navigation clavier.
- [ ] Lecteur d’écran.
- [ ] Mesures LCP, INP et CLS.
- [ ] Budget JavaScript.
- [ ] Optimisation des images.
- [ ] Test de charge raisonnable.
- [ ] Corrections.

### Critère de sortie

Aucun défaut critique connu et aucune barrière majeure sur les parcours principaux.

---

## Phase 12 — Préproduction et production

- [ ] Préproduction protégée.
- [ ] Recette fonctionnelle.
- [ ] Recette éditoriale.
- [ ] Recette SEO.
- [ ] Recette sécurité.
- [ ] Recette confidentialité.
- [ ] Recette analytics.
- [ ] Sauvegarde et restauration.
- [ ] Plan de rollback.
- [ ] TLS.
- [ ] Redirections de domaine.
- [ ] Smoke tests.
- [ ] Soumission Search Console.
- [ ] Soumission Bing Webmaster Tools.
- [ ] Monitoring et alertes.
- [ ] Journal de version.

### Critère de sortie

Verdict explicite `GO`, `GO CONDITIONNEL` ou `NO-GO`.

---

## Phase 13 — Suivi continu

### Après lancement

- [ ] surveiller disponibilité et erreurs ;
- [ ] surveiller les formulaires ;
- [ ] contrôler indexation et crawl ;
- [ ] contrôler les données structurées ;
- [ ] analyser les conversions ;
- [ ] analyser les Core Web Vitals terrain ;
- [ ] mettre à jour dépendances et contenus ;
- [ ] tester les restaurations ;
- [ ] revoir les règles de robots ;
- [ ] améliorer les pages à partir de données réelles.

---

## 22. Définition de terminé

Une tâche est `TERMINÉE` uniquement si :

- le besoin est satisfait ;
- le code est relu ;
- les tests passent ;
- les erreurs sont gérées ;
- la sécurité est vérifiée ;
- le responsive est vérifié ;
- l’accessibilité concernée est vérifiée ;
- le SEO concerné est vérifié ;
- les données personnelles sont traitées conformément au plan ;
- la documentation est à jour ;
- aucun placeholder ou contenu fictif n’est exposé ;
- le changement est déployable et réversible ;
- ce fichier est mis à jour.

---

## 23. Protocole d’utilisation avec Codex

### Prompt de début de tâche

```text
Lis intégralement DEVZAIR_REFERENTIEL_PROJET.md.

Travaille uniquement sur la tâche suivante :
[DESCRIPTION PRÉCISE]

Avant de modifier le code :
1. inspecte l’existant ;
2. indique la phase concernée ;
3. liste les fichiers susceptibles d’être modifiés ;
4. identifie les risques SEO, sécurité, données personnelles, accessibilité et régression ;
5. propose un plan court.

Pendant l’implémentation :
- respecte la pile et les conventions existantes ;
- n’invente aucun contenu factuel ;
- ajoute ou adapte les tests ;
- ne modifie pas un périmètre sans rapport ;
- conserve la compatibilité avec les décisions ADR.

À la fin :
- exécute les contrôles disponibles ;
- résume les modifications ;
- indique les tests exécutés et leurs résultats ;
- mentionne les limites ou contrôles manuels restants ;
- mets à jour les cases et le journal du référentiel.
```

### Prompt de revue

```text
Analyse les changements de la branche par rapport au référentiel Devzair.

Recherche en priorité :
- régressions fonctionnelles ;
- failles de sécurité ;
- problèmes d’autorisation ;
- fuite de secrets ou données personnelles ;
- défauts SEO/indexation ;
- données structurées incorrectes ;
- accessibilité ;
- performance ;
- contenu inventé ou non prouvé ;
- tests manquants.

Classe les constats par criticité et cite les fichiers et lignes.
Ne propose pas de changement hors périmètre sans justification.
```

### Prompt de recette avant production

```text
Effectue une revue de préparation à la production à partir de
DEVZAIR_REFERENTIEL_PROJET.md.

Vérifie au minimum :
- build et tests ;
- variables et secrets ;
- HTTPS et redirections ;
- indexabilité ;
- robots.txt et sitemap ;
- canonicals ;
- pages légales et consentement ;
- formulaires et e-mails ;
- sécurité des en-têtes ;
- erreurs et logs ;
- accessibilité des parcours critiques ;
- performance ;
- sauvegarde et rollback.

Retourne :
1. GO ;
2. GO CONDITIONNEL avec conditions bloquantes ;
3. NO-GO avec causes précises.
```

---

## 24. Registre de suivi

| ID | Phase | Tâche | Priorité | Statut | Preuve attendue | Prochaine action |
|---|---|---|---|---|---|---|
| DEV-001 | 0 | Confirmer les services du MVP | Haute | À faire | Liste validée | Cadrage éditorial |
| DEV-002 | 0 | Confirmer la zone desservie | Haute | À faire | Information écrite | Valider les pages locales |
| DEV-003 | 0 | Inventorier les réalisations publiables | Haute | À faire | Dossiers et autorisations | Préparer les études de cas |
| DEV-004 | 1 | Créer Dockerfile.dev | Haute | À faire | Build Docker réussi | Écrire le fichier |
| DEV-005 | 1 | Créer compose.yaml | Haute | À faire | Site accessible sur le port 3000 | Configurer le service web |
| DEV-006 | 1 | Initialiser Git à la racine | Haute | À faire | Premier commit | Vérifier l’absence de dépôt imbriqué |
| DEV-007 | 2 | Installer ESLint et le typecheck | Haute | À faire | Commandes vertes | Ajouter scripts npm |
| DEV-008 | 2 | Créer l’architecture Nuxt | Haute | À faire | Arborescence validée | Créer layout et première page |
| DEV-009 | 4 | Créer `usePageSeo` | Haute | À faire | Test du HTML serveur | Centraliser les métadonnées |
| DEV-010 | 4 | Configurer robots, sitemap et Schema.org | Haute | À faire | URLs publiques valides | Évaluer Nuxt SEO |

### Statuts autorisés

- `À faire`
- `En cours`
- `Bloqué`
- `En revue`
- `Terminé`
- `Abandonné`

---

## 25. Journal des décisions

| Date | ID | Décision | Raisons | Conséquences |
|---|---|---|---|---|
| 2026-08-01 | DEC-001 | Présenter Devzair comme une agence digitale à taille humaine | Refléter l’offre globale et le fonctionnement projet | Employer « nous », ne pas utiliser le positionnement freelance |
| 2026-08-01 | DEC-002 | Utiliser Nuxt 4 avec Vue et TypeScript strict | SSR, routage, métadonnées et maintenabilité | Ne pas construire une SPA Vue pure |
| 2026-08-01 | DEC-003 | Utiliser un monorepo Docker Compose | Préparer l’ajout de Symfony sans restructuration | Git unique à la racine |
| 2026-08-01 | DEC-004 | Utiliser Symfony 7.4 LTS pour l’API future | Stabilité et maintenance longue | API ajoutée dans `apps/api` |
| 2026-08-01 | DEC-005 | Appliquer SOLID de façon pragmatique | Séparer les responsabilités sans surarchitecture | Pages, composants, composables, services et repositories distincts |
| 2026-08-01 | DEC-006 | Centraliser les métadonnées communes | Éviter les incohérences et répétitions | Création de `usePageSeo` |
| 2026-08-01 | DEC-007 | Pré-rendre les pages marketing et garder le SSR pour le blog initial | Performance et fraîcheur | Cache dynamique décidé plus tard |
| 2026-08-01 | DEC-008 | Ne pas installer de modules pendant le scaffolding | Base maîtrisée | Modules ajoutés avec contrôle et tests |

---

## 26. Journal des changements

| Date | Version | Changement |
|---|---|---|
| 2026-08-01 | 1.0 | Création du référentiel initial |
| 2026-08-01 | 2.0 | Architecture Nuxt/Docker/Symfony confirmée, phases réorganisées, SOLID/DRY et implémentation SEO/GEO Nuxt ajoutés |

---

## 27. Sources officielles de référence

Les règles externes doivent être revérifiées avant toute décision importante, car elles évoluent.

### Nuxt et qualité du code

- Nuxt 4 — Structure des répertoires :  
  https://nuxt.com/docs/4.x/directory-structure
- Nuxt 4 — SEO et métadonnées :  
  https://nuxt.com/docs/4.x/getting-started/seo-meta
- Nuxt 4 — `useSeoMeta` :  
  https://nuxt.com/docs/4.x/api/composables/use-seo-meta
- Nuxt 4 — Récupération des données :  
  https://nuxt.com/docs/4.x/getting-started/data-fetching
- Nuxt 4 — `useAsyncData` :  
  https://nuxt.com/docs/4.x/api/composables/use-async-data
- Nuxt 4 — Pré-rendu :  
  https://nuxt.com/docs/4.x/getting-started/prerendering
- Nuxt 4 — Runtime Config :  
  https://nuxt.com/docs/4.x/guide/going-further/runtime-config
- Nuxt 4 — TypeScript :  
  https://nuxt.com/docs/4.x/guide/concepts/typescript
- Nuxt 4 — Tests :  
  https://nuxt.com/docs/4.x/getting-started/testing
- Nuxt ESLint — module et flat config :  
  https://eslint.nuxt.com/packages/module
- Nuxt SEO — guide et modules :  
  https://nuxtseo.com/learn-seo/nuxt

### SEO et performance

- Google Search Central — Guide SEO :  
  https://developers.google.com/search/docs/fundamentals/seo-starter-guide?hl=fr
- Google Search Central — Guide pour développeurs :  
  https://developers.google.com/search/docs/fundamentals/get-started-developers?hl=fr
- Google Search Central — Exploration et indexation :  
  https://developers.google.com/search/docs/crawling-indexing?hl=fr
- Google Search Central — robots.txt :  
  https://developers.google.com/search/docs/crawling-indexing/robots/intro
- Google Search Central — Organization :  
  https://developers.google.com/search/docs/appearance/structured-data/organization?hl=fr
- Google Search Central — LocalBusiness :  
  https://developers.google.com/search/docs/appearance/structured-data/local-business?hl=fr
- Google Search Central — FAQPage et retrait des résultats enrichis FAQ en 2026 :  
  https://developers.google.com/search/docs/appearance/structured-data/faqpage
- web.dev — Core Web Vitals :  
  https://web.dev/articles/vitals
- IndexNow — Documentation :  
  https://www.indexnow.org/documentation

### GEO et robots OpenAI

- OpenAI — Overview of OpenAI Crawlers :  
  https://developers.openai.com/api/docs/bots
- Answer.AI — proposition communautaire `llms.txt` :  
  https://llmstxt.org/

### Sécurité

- OWASP Top 10:2025 :  
  https://owasp.org/Top10/2025/
- OWASP ASVS 5.0 :  
  https://owasp.org/www-project-application-security-verification-standard/
- OWASP Cheat Sheet Series :  
  https://cheatsheetseries.owasp.org/
- OWASP CSP Cheat Sheet :  
  https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html
- OWASP Authentication Cheat Sheet :  
  https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html
- OWASP File Upload Cheat Sheet :  
  https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html

### Accessibilité

- W3C WAI — WCAG 2.2 Quick Reference :  
  https://www.w3.org/WAI/WCAG22/quickref/

### Données personnelles et cookies

- CNIL — Règles relatives aux cookies et traceurs :  
  https://www.cnil.fr/fr/cookies-et-autres-traceurs/regles
- CNIL — Outils de mesure d’audience :  
  https://www.cnil.fr/fr/cookies-solutions-pour-les-outils-de-mesure-daudience

---

## 28. Prochaine étape immédiate

La prochaine intervention doit rester limitée à la **Phase 1 — Dépôt et Docker Nuxt**.

### Ordre exact

1. vérifier que la commande de création Nuxt est terminée ;
2. revenir à la racine `devzair/` ;
3. initialiser Git à la racine si ce n’est pas déjà fait ;
4. créer `.gitignore` ;
5. créer `apps/web/Dockerfile.dev` ;
6. créer `compose.yaml` ;
7. créer `.env.example` ;
8. lancer `docker compose build web` ;
9. lancer `docker compose up -d` ;
10. vérifier `http://localhost:3000` ;
11. lancer un build de production ;
12. créer le premier commit ;
13. mettre à jour le registre de suivi.

### Prompt Codex correspondant

```text
Lis intégralement DEVZAIR_REFERENTIEL_PROJET.md.

Nous sommes uniquement dans la Phase 1 — Dépôt et Docker Nuxt.
Le projet Nuxt minimal existe déjà dans apps/web avec npm.

Inspecte d’abord l’arborescence existante, puis :
1. crée un Dockerfile.dev reproductible utilisant npm ci ;
2. crée compose.yaml avec un service web et un volume node_modules ;
3. crée .env.example sans secret ;
4. complète le .gitignore racine ;
5. ajoute des commandes simples dans le README ;
6. ne crée aucune page métier et n’installe aucun module SEO ;
7. exécute le démarrage Docker et le build Nuxt ;
8. indique précisément les fichiers modifiés et les résultats ;
9. mets à jour les cases de la Phase 1 et le registre de suivi.

Respecte SOLID, KISS et DRY, mais ne crée aucune abstraction inutile
à cette étape.
```

La phase suivante sera **Phase 2 — Qualité et architecture Nuxt**, uniquement après validation du build Docker.
