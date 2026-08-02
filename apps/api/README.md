# apps/api — Backend Symfony 7.4 LTS (Phase 6A)

API HTTP de l'agence Devzair. La Phase 6A ne comporte **qu'un seul
endpoint métier** : `POST /api/contact` (soumission du formulaire de
contact). Un endpoint de santé `GET /api/health` est également exposé.

Voir les décisions consignées :

- `docs/adr/ADR-006-runtime-symfony-caddy.md` — runtime PHP + reverse proxy.
- `docs/adr/ADR-007-endpoint-contact-securite.md` — sécurité endpoint.

## Stack

- PHP 8.4 CLI (serveur intégré `php -S` en dev — voir ADR-006 pour la
  migration prévue vers FrankenPHP ou PHP-FPM en production).
- Symfony 7.4 LTS avec les composants suivants uniquement :
  - `symfony/framework-bundle` — MicroKernel ;
  - `symfony/serializer` + `symfony/validator` — DTO avec contraintes ;
  - `symfony/http-client` — vérification Cloudflare Turnstile ;
  - `symfony/mailer` + `symfony/mime` — envoi email (défaut null://null) ;
  - `symfony/rate-limiter` — token bucket par IP ;
  - `symfony/monolog-bundle` — logs structurés canal `contact` ;
  - `symfony/uid` — `Request-Id` UUID v7.
- Pas de Doctrine, pas de Twig, pas de base de données en Phase 6A.

## Architecture

Convention AGENTS.md §6 (contrôleur → service → interface remplaçable) :

```
src/
  Contact/
    Controller/
      ContactSubmissionController.php    ← POST /api/contact
      HealthController.php               ← GET /api/health
    Dto/
      ContactRequest.php                 ← contraintes de validation
    Service/
      SubmitContactMessage.php           ← cas d'usage
      ContactMessageSenderInterface.php  ← contrat stable
      SymfonyContactMessageSender.php    ← implémentation Mailer
      InMemoryContactMessageSender.php   ← fake pour tests
      ContactSubmissionResult.php
    Security/
      OriginAllowlist.php                ← CSRF Option B (Origin allowlist)
      ContactRateLimiter.php             ← RateLimiter token bucket
      TurnstileVerifierInterface.php     ← contrat CAPTCHA
      TurnstileVerifierFactory.php       ← sélection selon env
      AlwaysAllowTurnstileVerifier.php   ← dev/test
      CloudflareTurnstileVerifier.php    ← prod (Siteverify)
      TurnstileVerdict.php
  EventListener/
    ApiExceptionListener.php             ← toute exception → JSON
```

## Pipeline `POST /api/contact`

1. Génère un `Request-Id` (UUID v7) porté par la réponse et tous les logs.
2. Rejette si `Origin` absent ou non listé → 403 `origin_not_allowed`.
3. Rejette si payload > 10 KB → 413 `payload_too_large`.
4. Consomme un jeton du rate limiter (par IP) → 429 + `Retry-After` sinon.
5. Deserialize JSON. Erreur → 400 `invalid_json`.
6. Honeypot : `website` non vide → 202 générique, **aucun email envoyé**.
7. Validation DTO. Erreur → 400 `validation_failed` + `errors`.
8. Vérifie Turnstile côté serveur. Refus → 403 `turnstile_rejected`.
9. Compose l'email (`From` app, `Reply-To` visiteur, texte brut) et envoie.
10. Log `contact.submitted` + `request_id` + `project_type` uniquement.
    **Aucun PII loggué** : ni message, ni email visiteur, ni téléphone,
    ni token, ni secret.

## Développement local

```bash
# Depuis la racine du monorepo :
docker compose up -d --build       # démarre caddy + web + api
curl -s http://localhost:3001/api/health
# → {"status":"ok"}

# Test rapide de contact (Origin obligatoire) :
curl -sX POST http://localhost:3001/api/contact \
     -H 'Content-Type: application/json' \
     -H 'Origin: http://localhost:3001' \
     -d '{
       "name":"Alice",
       "email":"alice@example.com",
       "projectType":"refonte",
       "message":"Un message de test suffisamment long pour passer.",
       "consent":true,
       "website":"",
       "turnstileToken":"dev-noop"
     }'
# → {"status":"accepted","request_id":"..."}
```

En dev, `TURNSTILE_ENABLED=false` (implémentation `AlwaysAllow`) et
`MAILER_DSN=null://null` (aucun envoi effectif). Ces défauts sont surs
tant que le formulaire côté navigateur n'existe pas.

## Contrôles qualité locaux

Composer et PHPUnit sont préinstallés dans l'image `api`. Pour lancer :

```bash
docker compose exec api composer validate --strict
docker compose exec api php bin/console lint:yaml config --parse-tags
docker compose exec api php bin/console lint:container --env=test
docker compose exec api vendor/bin/phpunit
```

En CI, le workflow `.github/workflows/api-quality.yml` exécute la même
séquence sur chaque push et pull request touchant `apps/api/`.

## Environnement

Toutes les variables sont documentées dans `.env.example` à la racine du
monorepo. Défauts sûrs :

| Variable                    | Défaut               | Utilité                                    |
|-----------------------------|----------------------|--------------------------------------------|
| `APP_ENV`                   | `dev`                | Environnement Symfony                      |
| `APP_SECRET`                | valeur factice       | Signature cookies internes (non utilisé)   |
| `MAILER_DSN`                | `null://null`        | Aucun envoi email par défaut               |
| `CONTACT_RECIPIENT`         | *vide*               | Destinataire (obligatoire en prod)         |
| `TURNSTILE_ENABLED`         | `false`              | Turnstile désactivé en dev/test            |
| `TURNSTILE_SECRET`          | *vide*               | Requis si `TURNSTILE_ENABLED=true`         |
| `CONTACT_RATE_LIMIT`        | `5`                  | Jetons/fenêtre                             |
| `CONTACT_RATE_INTERVAL`     | `10 minutes`         | Fenêtre du bucket                          |
| `CONTACT_ORIGIN_ALLOWLIST`  | `http://localhost:3001` | Origins autorisés                       |
| `TRUSTED_PROXIES`           | `127.0.0.1,REMOTE_ADDR` | Caddy en frontal                       |

**Fail-safe** : `TURNSTILE_ENABLED=true` sans `TURNSTILE_SECRET` → boot
en exception. Documenté dans ADR-007.

## Ce qui n'est pas dans la Phase 6A

- Formulaire côté navigateur (Nuxt) et widget Turnstile.
- Page publique `/contact`.
- Persistance PostgreSQL et Doctrine.
- Back-office ou CRM.
- Queue asynchrone / worker.
- Image Open Graph dynamique.

Ces sujets sont différés aux phases 6B et suivantes.
