# ADR-009 — Persistance PostgreSQL du domaine éditorial

- Statut : accepté
- Date : 2026-08-04
- Décideurs : équipe Devzair

## Contexte

La Phase 8A ouvre la partie éditoriale du site (ressources / articles).
Jusqu'ici, l'API n'avait pas de persistance : le seul endpoint (`POST /contact`)
délègue immédiatement à un service Mailer. Il faut désormais :

- stocker durablement les articles (titre, corps markdown, SEO, statut) ;
- exposer une lecture publique paginée et une lecture par slug ;
- garder une architecture pure isolant le domaine (Domain / Application /
  Infrastructure / Presentation) telle que définie dans `docs/06-ARCHITECTURE-CODE.md`.

Le domaine restera **écrit** hors du site public en Phase 8A (aucune UI admin,
aucun endpoint d'écriture). L'écriture arrivera en Phase 8B via commandes CLI
puis endpoint authentifié.

## Options étudiées

1. **PostgreSQL 17-alpine + Doctrine ORM 3** (retenu).
2. SQLite fichier : simple, mais pas de type `jsonb`, pas de contraintes
   Postgres-natifs, et incohérent avec la production visée (OVH Cloud managé).
3. Fichiers markdown + repository de lecture de fichiers : évite une base
   mais ne survivrait pas à la Phase 8B (recherche, filtres, écriture concurrente).
4. MySQL 8 : équivalent fonctionnel mais l'écosystème PostgreSQL (JSONB, EXPLAIN
   verbeux, `pg_stat_statements`) est nettement plus aligné avec le tri éditorial
   filtré par tags que l'on prépare pour la Phase 9.

## Décision

- **Postgres 17-alpine**, version verrouillée dans `compose.yaml` et dans le
  workflow CI. Toute évolution majeure (18+) passe par une nouvelle ADR.
- **Doctrine ORM 3** (`^3.3`) + **DoctrineBundle** (`^2.13`) + **Migrations Bundle**
  (`^3.4`). Doctrine ORM 3 est requis pour PHP 8.4 strict et pour les *lazy
  ghost objects* natifs.
- **Attributs PHP directement sur l'entité** `Article` : pragmatique et lisible.
  Les attributs restent purement techniques ; toute règle métier vit dans les
  factories/méthodes de l'agrégat.
- **UUID v7** via `Symfony\Component\Uid\Uuid`, utilisé tel quel dans l'entité
  (pas de VO `ArticleId` — pas de comportement métier justifiant l'encapsulation).
- **`Types::JSON` mappé en `jsonb`** pour la colonne `expertise_ids`. Ouvre la
  porte à un index GIN futur sans migration de type.
- **Contenu markdown stocké brut**. Le sanitizer/renderer (probablement
  `league/commonmark` + `HTMLPurifier`) est repoussé en Phase 8B côté Nuxt.
- Pas de service publié : Postgres n'est jamais exposé sur l'hôte, seulement
  sur le réseau interne Compose. En production, la base est managée (OVH).

## Raisons

- **Cohérence dev ↔ prod** : la même version majeure Postgres vit du poste
  développeur jusqu'au CI et jusqu'à la prod managée.
- **Type jsonb natif** : premier des cas d'usage éditoriaux (liste de piliers)
  est déjà multi-valué ; JSONB évite une table de jointure prématurée.
- **Doctrine ORM 3** : générateur d'index composite via attributs, support natif
  UUID, mode `report_fields_where_declared` qui verrouille les erreurs
  d'héritage — meilleur alignement avec le code strict PHP 8.4.
- **UUID v7** : monotone, permet un tri secondaire déterministe par id à
  égalité de `publishedAt`, et donne un id triable en base sans colonne
  `created_at` supplémentaire pour l'ordre.

## Conséquences

**Positives**

- Prêt à héberger d'autres agrégats (Phase 9, 10…) sans réviser la stack.
- Cache d'ORM configurable en prod (`when@prod` dans `doctrine.yaml`).
- Requêtes SQL directes possibles (dashboards, jobs de maintenance).

**Limites / risques**

- Un service supplémentaire à opérer (backups, montée de version). Sujet
  transféré en Phase 10 (opérabilité).
- L'entité `Article` est aussi une entité Doctrine : le domaine dépend de
  Doctrine au sens du package. Ce compromis est explicite (attributs
  ORM) et compensé par le fait qu'aucune règle métier n'utilise ces attributs.
- Le passage à un modèle rich CQRS/Event Sourcing (si un jour justifié)
  demanderait de scinder entité domaine et entité Doctrine.

## Références

- `apps/api/config/packages/doctrine.yaml` — mapping `Editorial`.
- `apps/api/config/packages/doctrine_migrations.yaml` — racine `App\Migrations`.
- `apps/api/migrations/Version20260803120000.php` — création table `editorial_article`.
- `apps/api/src/Editorial/` — code source Phase 8A.
- `compose.yaml` — service `postgres` verrouillé sur 17-alpine.
- `.github/workflows/api-quality.yml` — service Postgres du CI + migrations.
