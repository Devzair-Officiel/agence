# Architecture Decision Records

Créer un ADR pour toute décision technique durable qui influence plusieurs fichiers ou phases.

## Convention de nommage

```text
ADR-001-nuxt-ssr.md
ADR-002-docker-compose.md
ADR-003-strategie-seo.md
```

## Modèle

```md
# ADR-XXX — Titre

- Statut : proposé | accepté | remplacé | abandonné
- Date : AAAA-MM-JJ
- Décideurs : noms ou rôles

## Contexte

Pourquoi une décision est nécessaire.

## Options étudiées

1. Option A
2. Option B

## Décision

Choix retenu.

## Raisons

Critères factuels et compromis.

## Conséquences

Effets positifs, limites, risques et travaux associés.

## Références

Documentation officielle et liens vers les tâches ou PR.
```

Un ADR ne doit pas servir de journal de tâche. Les décisions réversibles et locales restent dans la PR ou le commit.
