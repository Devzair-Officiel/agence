# Checklist GO-LIVE — Estimateur V1

Référence : EST-9 hardening pass (2026-09-02). À exécuter **dans l'ordre** par le responsable de déploiement.

---

## A. Pré-déploiement — Variables d'environnement

| # | Vérification | Commande / Action |
|---|---|---|
| A-1 | `APP_ENV=prod` dans `.env.prod` | `grep APP_ENV .env.prod` |
| A-2 | `APP_ADMIN_BASE_URL` = HTTPS, domaine public (non localhost, non 127.0.0.1, non ::1) | Inspecter `.env.prod` |
| A-3 | `CONTACT_RECIPIENT` non vide (adresse email Devzair opérationnelle) | Inspecter `.env.prod` |
| A-4 | `MAILER_DSN` ≠ `null://` (transport SMTP ou SES configuré) | Inspecter `.env.prod` |
| A-5 | `CONTACT_ORIGIN_ALLOWLIST` contient `https://devzair.fr` (et aucun `localhost`) | Inspecter `.env.prod` |
| A-6 | `NUXT_PUBLIC_SITE_INDEXABLE=true` dans `.env.prod` | `grep SITE_INDEXABLE .env.prod` |
| A-7 | `DATABASE_URL` pointe sur PostgreSQL prod | Inspecter `.env.prod` |

---

## B. Pré-déploiement — Commande preflight

```sh
docker compose --env-file .env.prod -f compose.prod.yaml \
  exec api php bin/console app:estimator:preflight --env=prod
```

**Résultat attendu : `Preflight OK`** (code de sortie 0).

Tout `[FAIL:…]` bloque le déploiement. Corriger la variable d'environnement concernée avant de continuer.

---

## C. Déploiement

Suivre `docs/DEPLOIEMENT-PROD.md §3` (frontend + API simultanément, car la migration est requise).

```sh
# 1. Build
docker compose --env-file .env.prod -f compose.prod.yaml build web api

# 2. Migration Doctrine (estimator_lead + estimator_partnership_proposal)
docker compose --env-file .env.prod -f compose.prod.yaml \
  exec api php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# 3. Validation schéma
docker compose --env-file .env.prod -f compose.prod.yaml \
  exec api php bin/console doctrine:schema:validate --env=prod

# 4. Recréer les conteneurs
docker compose --env-file .env.prod -f compose.prod.yaml \
  up -d --force-recreate api web
```

---

## D. Post-déploiement — Santé de base

| # | Vérification | Commande / Attendu |
|---|---|---|
| D-1 | API health | `curl -sS https://devzair.fr/api/health` → `{"status":"ok"}` |
| D-2 | Page estimateur accessible | `curl -sS https://devzair.fr/estimer-mon-projet` → HTTP 200 |
| D-3 | SSR contient le H1 | `curl -sS https://devzair.fr/estimer-mon-projet \| grep "Estimez votre projet"` |
| D-4 | SSR non indexable avant bascule | `curl -sI https://devzair.fr/estimer-mon-projet \| grep -i x-robots` si `SITE_INDEXABLE=false` |
| D-5 | Endpoint estimate répond | `curl -sS -X POST https://devzair.fr/api/estimate -H "Content-Type: application/json" -d '{"project_type":"vitrinesite","scale":"small","features":[],"content_needs":[],"visibility":[]}' -H "Origin: https://devzair.fr"` → JSON avec `outcome` |

---

## E. Post-déploiement — Vérifications fonctionnelles

| # | Vérification | Acteur |
|---|---|---|
| E-1 | Parcours complet vitrinesite → résultat affiché | Humain (navigateur) |
| E-2 | Formulaire lead soumis → email reçu sur `CONTACT_RECIPIENT` | Humain |
| E-3 | Formulaire partenariat soumis → email reçu | Humain |
| E-4 | Bouton honeypot rempli → réponse 202 silencieuse (pas d'email) | Humain |
| E-5 | Rate limiting lead : >10 soumissions/min → 429 | Test optionnel |
| E-6 | Admin `/admin/estimator/leads` liste les leads | Humain (ROLE_ADMIN) |
| E-7 | Admin `/admin/estimator/partnerships` liste les propositions | Humain (ROLE_ADMIN) |

---

## F. Post-déploiement — Indexabilité (si GO public immédiat)

```sh
# Mettre NUXT_PUBLIC_SITE_INDEXABLE=true dans .env.prod, puis rebuild web
docker compose --env-file .env.prod -f compose.prod.yaml build web
docker compose --env-file .env.prod -f compose.prod.yaml up -d --force-recreate web
```

Vérifier : `curl -sI https://devzair.fr/estimer-mon-projet | grep -i x-robots` → absent (pas de noindex).

---

## G. Cron — Purge quotidienne RGPD

Ajouter au planificateur d'infrastructure (cron, systemd-timer ou équivalent) :

```sh
# Exécuter en heures creuses (ex. 03h00 UTC)
docker compose --env-file .env.prod -f compose.prod.yaml \
  exec api php bin/console app:estimator:purge-expired --env=prod
```

- Fréquence : **quotidienne** (une fois par jour, heures creuses).
- Aucun PII produit en sortie.
- Politique : 24 mois depuis `createdAt` (`EstimatorRetentionPolicy::MONTHS = 24`).
- Les propositions de partenariat sont purgées **avant** les leads.

Tester avec dry-run d'abord :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml \
  exec api php bin/console app:estimator:purge-expired --dry-run --env=prod
```

---

## H. Doctrine schema drift (pré-existant)

`doctrine:schema:validate` peut signaler un drift si des tables non gérées par Doctrine sont présentes. Ce drift **pré-date** le module Estimateur et n'est pas causé par lui. Ne pas bloquer le GO-LIVE sur ce point si les migrations Estimateur passent sans erreur.

---

## Résultat attendu final

- Preflight : `Preflight OK` ✔
- Migration : `[OK] DoctrineMigrations\Version2026…` ×2 ✔
- Schema validate : `[OK] The mapping files are correct.` ✔ (mapping seul ; drift de schéma toléré si pré-existant)
- `/api/health` : `{"status":"ok"}` ✔
- Emails lead + partenariat reçus ✔
- Cron quotidien planifié ✔
