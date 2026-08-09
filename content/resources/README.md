# Contenus éditoriaux — sources versionnées

Ce répertoire contient les fichiers source des articles publiés sous
`/ressources` sur le site public. Chaque fichier `.md` est la source unique
du contenu ; la base éditoriale PostgreSQL est reconstruite depuis ces
fichiers via le pipeline `app:editorial:import` + `app:editorial:publish`.

## Format

Chaque fichier respecte le contrat décrit par `MarkdownArticleFileParser` :

```
---
slug: mon-article
title: "…"
excerpt: "…"
seo:
  title: "…"
  description: "…"
author:
  name: "Devzair"
  type: organization
expertises:
  - concevoir
  - construire
---
Contenu Markdown…
```

- Slug : `[a-z0-9]` séparés par tirets, 3 à 120 caractères, immutable.
- Title : 5 à 200 caractères.
- Excerpt : 40 à 320 caractères (visible dans les cartes `/ressources`).
- SEO title : 30 à 70 caractères.
- SEO description : 70 à 160 caractères.
- Expertises : liste d'identifiants réels (`concevoir`, `construire`,
  `valoriser`, `visibilite`, `faire-evoluer`).
- `publishedAt` : jamais dans le front matter — fourni exclusivement par
  la CLI `app:editorial:publish --published-at=…`.

## Politique de contenu

- Aucune promesse chiffrée sans preuve versionnée.
- Aucun témoignage, aucun client, aucune récompense inventée.
- Employer « nous », présenter Devzair comme une agence à taille humaine.
- Pas de HTML brut (refusé par `MarkdownContentValidator`).
- Liens uniquement en schéma `http`, `https`, `mailto`, `tel` ou relatif.

## Amorçage local

Le script `scripts/editorial-content-bootstrap.sh` importe puis publie
les fichiers présents ici, en sautant les slugs déjà en base
(create-only). Il ne remplace jamais un article existant.
