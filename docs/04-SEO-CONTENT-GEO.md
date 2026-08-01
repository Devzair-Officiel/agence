# SEO éditorial, local et GEO

> Qualité éditoriale, autorité, visibilité locale et présence dans les moteurs génératifs.

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

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
