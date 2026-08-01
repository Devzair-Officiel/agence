# Projet, positionnement et périmètre

> Source de vérité pour la mission de Devzair, les objectifs du site, son périmètre et son arborescence.

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

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
