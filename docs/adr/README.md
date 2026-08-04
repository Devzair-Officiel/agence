# Architecture Decision Records

Créer un ADR pour toute décision technique durable qui influence plusieurs fichiers ou phases.

## Convention de nommage

```text
ADR-001-nuxt-ssr.md
ADR-002-docker-compose.md
ADR-003-strategie-seo.md
```

## ADR acceptés

- `ADR-004` — Modules SEO retenus (Option 2 « à la carte »).
- `ADR-006` — Runtime backend Symfony 7.4 LTS + reverse proxy Caddy.
- `ADR-007` — Sécurité de l'endpoint `POST /api/contact` (CSRF stateless,
  Turnstile, rate limit, logging sans PII).
- `ADR-008` — Transport mail OVHcloud via `MAILER_DSN`, Turnstile facultatif
  (deux flags alignés), réponse HTTP 503 `temporary_error` sur échec SMTP.
- `ADR-009` — Persistance PostgreSQL 17-alpine du domaine éditorial (Doctrine
  ORM 3, UUID v7, `expertise_ids` en `jsonb`).

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
