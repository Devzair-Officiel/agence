# ADR-006 — Runtime backend : Symfony 7.4 LTS + Caddy en frontal

- Statut : accepté
- Date : 2026-08-02
- Décideurs : équipe Devzair
- Portée : Phase 6A (endpoint `/api/contact` seul). Les décisions liées à la
  persistance PostgreSQL et à Doctrine sont **différées** aux phases où un
  domaine métier persistant sera introduit (Phase 8 minimum).

## Contexte

La Phase 6A introduit un premier endpoint HTTP côté backend
(`POST /api/contact`). L'AGENTS.md fige déjà Symfony 7.4 LTS et Caddy comme
reverse-proxy. Il reste à choisir :

- le **runtime PHP** (FPM, FrankenPHP, `php -S`) ;
- la topologie **Docker Compose** compatible avec le web Nuxt existant ;
- comment router `/api/**` vers Symfony et le reste vers Nuxt sans mélanger
  les deux serveurs applicatifs.

Contraintes explicites de la Phase 6A :

- pas de base de données ;
- pas de worker asynchrone ;
- un seul endpoint POST + un health check ;
- aucun secret introduit en dev ;
- CI reproductible.

## Options étudiées

### Runtime PHP

1. **PHP-FPM + nginx** — standard, robuste, mais introduit deux processus,
   un pool à dimensionner, et un serveur HTTP en plus de Caddy.
2. **FrankenPHP** — serveur unique moderne, très performant, mais ajoute
   une dépendance runtime relativement jeune que rien ne justifie tant
   qu'on n'a pas de charge à absorber.
3. **PHP built-in server (`php -S`)** — single-thread, non recommandé en
   prod, mais parfait pour un endpoint unique sans DB derrière un frontal
   qui absorbe déjà la concurrence.

### Reverse-proxy

1. **Caddy** — déjà acté dans l'AGENTS.md et le dossier `infra/caddy/`.
2. **Traefik** — équivalent fonctionnel, mais aucune règle métier ne
   justifie de changer d'outil.

### Topologie Docker Compose

1. **Un service Docker par app + Caddy public** — chaque service isolé,
   port hôte unique (3001). Aligné §16.1 du 06-ARCHITECTURE-CODE.md.
2. **Un seul conteneur monolithique** — refusé, casse la séparation web/api
   et complique l'itération sur l'un sans l'autre.

## Décision

### Runtime

- **PHP 8.4 CLI + serveur intégré** (`php -S 0.0.0.0:8000 -t public/`)
  pour la Phase 6A. Justifications :
  - aucune concurrence PHP à gérer : Caddy sérialise déjà les requêtes
    lentes ; un endpoint POST + un GET santé ne requièrent pas un pool ;
  - zéro dépendance supplémentaire dans l'image (pas d'FPM, pas de socket,
    pas de nginx) ;
  - retour du runtime immédiat au premier `docker compose up`.
- **Migration prévue** vers FrankenPHP (préféré) ou PHP-FPM + Caddy à
  partir du premier de ces déclencheurs :
  - introduction d'une base de données (Phase 8) ;
  - besoin de traitements longs (Doctrine, jobs) ;
  - montée en charge mesurée (>1 req/s soutenues sur `/api/**`).

### Reverse-proxy

- **Caddy 2 Alpine** en frontal, seul port hôte exposé (`3001:80`).
- Routing Caddyfile :
  - `path /api/*` → `reverse_proxy api:8000` avec `uri strip_prefix /api` ;
  - fallback → `reverse_proxy web:3000` (Nuxt).
- `X-Forwarded-*` propagés à Symfony ; les trusted proxies sont configurés
  côté Symfony (`TRUSTED_PROXIES=127.0.0.1,REMOTE_ADDR`).

### Topologie

- `web`  : Nuxt en dev, `expose: 3000` (réseau interne seulement).
- `api`  : Symfony CLI server, `expose: 8000` (réseau interne seulement).
- `caddy` : seul service avec un port hôte (`3001:80`), `depends_on: [web, api]`.
- L'URL publique reste `http://localhost:3001` — donc
  `NUXT_PUBLIC_SITE_URL` et `CONTACT_ORIGIN_ALLOWLIST` n'ont pas à changer
  entre l'ancienne et la nouvelle topologie.

### PostgreSQL / Doctrine

**Différés.** L'endpoint /api/contact déclenche un envoi email et ne
persiste rien. Introduire PostgreSQL maintenant serait de la
sur-ingénierie (règle AGENTS.md #8). Le sujet sera ré-ouvert dans un
ADR-008 dédié quand le premier domaine persistant sera introduit
(Phase 8+ ou blog).

## Raisons

- **KISS** : le stack minimal (Caddy + php-cli + Nuxt) démarre en une
  commande et n'a rien de spécifique à la Phase 6A que la CI ne puisse
  répliquer.
- **Sécurité** : un seul point d'entrée public simplifie l'analyse de
  surface d'attaque ; les deux app-servers ne sont pas accessibles depuis
  l'hôte.
- **Portabilité** : ne rien introduire qu'on ne saura pas reproduire en
  prod. Le passage à FrankenPHP ou FPM sera un simple swap de `command:`
  + Dockerfile, sans changement d'architecture.
- **DRY** : `NUXT_PUBLIC_SITE_URL`, `NUXT_PUBLIC_API_BASE_URL` et
  `CONTACT_ORIGIN_ALLOWLIST` restent alignés sur la même URL publique.

## Conséquences

Positives :

- démarrage `docker compose up --build` fonctionnel de bout en bout ;
- un endpoint `/api/health` accessible via Caddy pour la CI ;
- migration future vers un runtime de prod isolée à `Dockerfile` et
  `command:`, sans toucher au routing.

Négatives / à surveiller :

- `php -S` est **single-thread** : toute requête lente bloque les
  suivantes. Acceptable tant qu'on n'a qu'un endpoint POST sans DB. À
  revoir dès qu'une opération dépasse 200 ms de p95 ou qu'on atteint
  1 req/s soutenu.
- Le serveur intégré PHP ne doit **jamais** être utilisé en production
  publique : le passage en FrankenPHP/FPM est un pré-requis Phase 7 ou 8.

## Références

- `docs/06-ARCHITECTURE-CODE.md` §16.1, §16.2, §16.9, §16.10.
- `docs/08-ROADMAP.md` Phase 6A.
- `AGENTS.md` — stack confirmée.
- ADR-004 (modules SEO) — même format et cadre décisionnel.
