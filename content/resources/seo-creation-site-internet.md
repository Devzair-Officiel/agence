---
slug: seo-creation-site-internet
title: "Pourquoi le SEO doit être pensé dès la création d'un site"
excerpt: "Attendre la mise en ligne pour parler référencement, c'est prendre le risque de refaire une partie du travail. Nous montrons ce qui doit être décidé dès la conception du site."
seo:
  title: "SEO et création de site : penser le référencement dès le départ"
  description: "Les décisions SEO qui se prennent avant la mise en ligne : architecture, contenus, performance, balisage, maillage — pour éviter de refaire le travail."
author:
  name: "Devzair"
  type: organization
expertises:
  - visibilite
  - concevoir
---
« On fera le SEO à la fin. » Cette phrase, nous l'avons entendue sur presque tous les projets où nous avons été appelés en rattrapage. Le résultat est le même : à la fin, il n'est plus possible de « faire du SEO » sans reprendre une part importante du site. Certaines décisions structurantes se prennent au tout début du projet — et deviennent difficiles ou coûteuses à modifier après la mise en ligne.

Cet article détaille ce qui se joue avant qu'une seule ligne de code ne soit livrée, et pourquoi séparer conception et référencement produit presque toujours des sites plus faibles que ce qu'ils auraient pu être.

## Le SEO ne se rajoute pas à la fin

Le référencement naturel n'est pas une couche que l'on applique après coup, comme on ajoute une peinture. C'est un ensemble de décisions techniques, éditoriales et structurelles qui influent sur la façon dont les moteurs comprennent votre site. Certaines de ces décisions se prennent au moment de la conception ; d'autres peuvent être ajustées plus tard ; quelques-unes sont pratiquement irréversibles une fois le site en production.

Ce qui est réellement rattrapable en fin de projet : les balises `title` et `meta description`, les images sans attribut `alt`, quelques problèmes de rapidité, l'ajout d'un plan de site. Ce qui l'est nettement moins : l'arborescence, la structure des URLs, le choix technologique, le modèle de contenu, la stratégie de maillage. Le vrai coût du SEO tardif est là — dans les décisions qui auraient dû être discutées dès le cadrage.

## L'architecture d'information, première décision SEO

Un moteur de recherche voit un site comme un ensemble de pages reliées entre elles. La manière dont ces pages sont regroupées, hiérarchisées et nommées lui indique — implicitement — de quoi le site parle et comment ses sujets sont organisés.

Concrètement, cela signifie que la décision de créer des rubriques distinctes, de regrouper certains sujets sous une même page ou au contraire de les séparer, a un impact direct sur la manière dont le site sera perçu. Une arborescence pensée pour vos publics est aussi une arborescence lisible pour un moteur. Ce travail relève de ce que nous appelons [concevoir un site professionnel](/expertises/concevoir), et il conditionne le reste. Il est traité plus en détail dans notre article sur la [création de site internet professionnel](/ressources/creer-site-internet-professionnel).

## Contenu et intentions de recherche

Un contenu ne se référence pas seul. Il répond — ou non — à une intention de recherche. La bonne question à se poser en rédigeant une page n'est pas « comment placer le mot-clé » mais « à quelle question cette page répond-elle mieux que les autres du web ? ».

Cette différence est structurante. Un site conçu autour des intentions de ses publics produit naturellement un contenu utile, lisible et durable. Un site conçu autour d'une liste de mots-clés produit du contenu artificiel qui vieillit mal — et qui aujourd'hui n'a plus vraiment de valeur, tant les moteurs ont progressé dans la détection du contenu de qualité. Cette réalité vaut aussi pour l'IA générative : les modèles synthétisent mieux le contenu qui répond clairement à une intention.

Nous préférons partir des questions réelles que se posent vos publics, y répondre de façon précise, et laisser le référencement se construire à partir de là. C'est plus lent, mais c'est aussi ce qui rend un site utile trois ans après la mise en ligne.

## Les fondations techniques

Certains éléments techniques sont difficilement rattrapables en fin de projet. Nous en citons quelques-uns parmi les plus fréquents :

- Le rendu côté serveur (SSR) ou son absence : Google documente que ses robots exécutent JavaScript dans un second temps du crawl, avec des délais et des limites propres. Sans rendu côté serveur, le contenu clé peut être indexé plus tardivement ou incomplètement, et corriger cela après coup demande souvent un changement d'architecture.
- La structure des URLs : les URLs choisies dès le départ deviennent des adresses stables. Google publie des recommandations explicites sur les migrations d'URLs (redirections 301, période de conservation, mise à jour des liens et du sitemap) pour limiter les pertes de signaux, mais ces migrations restent coûteuses et rarement sans impact.
- Le balisage sémantique : un site où les titres `<h1>` sont désorganisés, où les listes sont des paragraphes et où les tableaux sont des divs coûte cher à rectifier une fois la production entamée.

Ces choix techniques relèvent du [travail de construction](/expertises/construire) — et ils se décident au moment où l'on choisit le socle, pas au moment où l'on écrit la balise `title`.

## Les Core Web Vitals sans effet d'annonce

Google publie trois indicateurs Core Web Vitals stables — Largest Contentful Paint, Cumulative Layout Shift et Interaction to Next Paint (ce dernier ayant remplacé First Input Delay en mars 2024) — utilisés dans l'évaluation de l'expérience de page, aussi bien sur mobile que sur ordinateur. Ces indicateurs approchent l'expérience réelle des visiteurs : temps d'attente avant que la page soit utilisable, stabilité visuelle du contenu, réactivité des interactions.

Les valeurs prises en compte proviennent du Chrome User Experience Report, agrégation sur 28 jours glissants des mesures collectées auprès des vrais visiteurs Chrome. Cela veut dire qu'on ne peut pas « les préparer à la fin » : ils dépendent de choix techniques faits au départ (poids des ressources, priorisation du contenu principal, dimensionnement des images, stratégie de rendu). Un site conçu sans y penser peut mettre plusieurs semaines à voir ses métriques de terrain se stabiliser après optimisation, puisque la fenêtre CrUX reste glissante.

## Le maillage interne, souvent négligé

Le maillage — c'est-à-dire les liens entre les pages du site — est un des leviers SEO les moins spectaculaires et les plus efficaces. Il sert deux objectifs simultanément : aider vos visiteurs à trouver ce dont ils ont besoin, et indiquer aux moteurs les relations entre vos contenus.

Un site bien maillé n'a pas dix menus imbriqués. Il a des liens contextuels, insérés dans le texte, qui pointent vers les pages pertinentes au moment où la question du visiteur se pose. Cet équilibre se travaille dès l'écriture des contenus — pas dans un chantier de « maillage » ajouté après coup, qui produit souvent des blocs artificiels de liens en fin d'article.

## Notre manière d'intégrer le SEO au projet

Nous ne séparons pas le SEO du projet. Il est présent dans la conception (arborescence, modèle de contenu), dans la rédaction (intentions, structure des titres, maillage), dans la construction technique (SSR, performance, balisage), et bien sûr dans le suivi. Cette intégration est ce qui nous permet, six mois après la mise en ligne, de discuter avec vous d'indicateurs qui bougent réellement — et pas simplement d'avoir un site « prêt pour Google ».

Notre travail sur la [visibilité](/expertises/visibilite) est étroitement lié à ce qui se joue dans la conception. Il se prolonge naturellement dans une [stratégie de visibilité locale](/ressources/ameliorer-visibilite-locale-entreprise) lorsque le rayonnement de l'entreprise est géographique.

## En résumé

Le SEO n'est pas une couche que l'on applique à la fin. C'est un ensemble de décisions qui traversent tout le projet, et dont les plus structurantes se prennent au tout début. Un site conçu sans y penser peut être rattrapé en partie ; il n'atteindra que rarement le niveau qu'il aurait pu avoir avec un cadrage initial correct.

Si vous préparez un projet de site et que le référencement fait partie de vos objectifs, il est utile d'en parler avant de fixer un cahier des charges. [Contactez-nous](/contact) pour échanger sur votre contexte : le bon moment pour en discuter, c'est maintenant, pas après la mise en ligne.

## Sources et références

- Google Search Central — [Bases du SEO pour JavaScript](https://developers.google.com/search/docs/crawling-indexing/javascript/javascript-seo-basics) : comportement documenté du crawl et du rendu JavaScript par Googlebot.
- Google Search Central — [Migrations de site avec changement d'URL](https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes) : recommandations officielles sur les redirections 301, la durée de conservation et la préservation des signaux.
- Google Search Central — [Comprendre l'expérience de page dans les résultats Google](https://developers.google.com/search/docs/appearance/page-experience) : place des Core Web Vitals dans l'évaluation d'expérience, sur mobile comme sur ordinateur.
- web.dev — [Core Web Vitals](https://web.dev/articles/vitals) : définitions LCP, CLS, INP et seuils « good » / « needs improvement » / « poor ».
