# Checklist — Mise en production du formulaire de contact

Cette checklist couvre l'activation réelle du formulaire de contact (envoi
via SMTP OVHcloud). Elle est **complémentaire** de la checklist SEO et de
la checklist infra (Caddy / Docker).

Convention : chaque case doit être cochée par la personne qui a effectué
l'action, avec date et commentaire si nécessaire. Ne jamais cocher pour
quelqu'un d'autre. En cas de doute sur un item, ouvrir la question dans
`docs/10-TRACKING.md` plutôt que de forcer le déploiement.

---

## 0. Prérequis

- [ ] Une boîte email OVHcloud dédiée (`contact@…` ou équivalent) est
      créée et accessible.
- [ ] Un compte SMTP OVHcloud (utilisateur + mot de passe) est provisionné
      pour l'application, distinct des identifiants utilisateurs finaux.
- [ ] Le mot de passe SMTP est stocké dans le **secret manager**
      (`.env.local` local dev ou coffre en prod) — **jamais** en Git,
      jamais dans un dashboard partagé.
- [ ] Le domaine d'envoi (`devzair.fr` ou autre) est vérifié SPF/DKIM
      côté OVHcloud (`v=spf1 include:mx.ovh.com …`, DKIM signé par OVH).
- [ ] Une politique DMARC (au minimum `p=none` pour observer, idéalement
      `p=quarantine`) est publiée sur le domaine.

## 1. Variables d'environnement — API Symfony

Toutes ces variables doivent être présentes dans le secret manager (ou
`.env.local` selon la cible). Exécuter la vérification avec la commande
`bin/console app:contact:check` en environnement cible.

- [ ] `APP_ENV=prod`
- [ ] `APP_DEBUG=0`
- [ ] `APP_SECRET` — chaîne aléatoire ≥ 32 caractères (générée par
      `openssl rand -hex 32`).
- [ ] `MAILER_DSN` — forme `smtps://USER:PASSWORD@HOST:465` (TLS
      implicite recommandé). `smtp://` sur port 587 accepté avec
      STARTTLS. **Pas** de `null://null` en prod.
- [ ] `CONTACT_FROM_EMAIL` — adresse app-controlled, appartenant au
      domaine du site (ex. `no-reply@devzair.fr`). Pas d'email visiteur.
- [ ] `CONTACT_FROM_NAME` — libellé lisible (ex. `Devzair — Site`).
- [ ] `CONTACT_RECIPIENT` — adresse email réelle qui reçoit les demandes
      (ex. `contact@devzair.fr`). Vide = 503 systématique.
- [ ] `CONTACT_RATE_LIMIT` — défaut `5` acceptable, revoir si trafic
      réel supérieur.
- [ ] `CONTACT_RATE_INTERVAL` — défaut `"10 minutes"`.
- [ ] `CONTACT_ORIGIN_ALLOWLIST` — l'URL prod exacte, séparée par
      virgules si plusieurs domaines (ex. `https://devzair.fr`).
- [ ] `TRUSTED_PROXIES` — inclut Caddy / le reverse proxy (souvent
      `REMOTE_ADDR` suffit en Docker Compose).

## 2. Turnstile (facultatif — deux flags à aligner)

Le formulaire fonctionne sans Turnstile ; il est **facultatif** par
choix documenté (ADR-008). Si vous l'activez, cocher les deux flags.

- [ ] `TURNSTILE_ENABLED=true` côté API.
- [ ] `TURNSTILE_SECRET` — secret Cloudflare (dashboard Turnstile), stocké
      dans le secret manager.
- [ ] `NUXT_PUBLIC_TURNSTILE_ENABLED=true` côté Nuxt.
- [ ] `NUXT_PUBLIC_TURNSTILE_SITE_KEY` — site-key publique (non-secret),
      copiée depuis le même widget Cloudflare que le secret ci-dessus.

**Divergence interdite :** un seul des deux flags à `true` produit un
rejet systématique. Vérifier explicitement l'alignement.

## 3. Vérification pré-déploiement

- [ ] Exécuter `bin/console app:contact:check --env=prod` (dans l'image
      ou l'environnement cible). Sortie attendue : `Configuration OK`
      **ou** uniquement des avertissements assumés.
- [ ] Aucun code d'erreur listé dans la sortie
      (`mailer_dsn_null_in_prod`, `mailer_dsn_plaintext_in_prod`,
      `recipient_missing`, `recipient_invalid`, `from_email_*`,
      `turnstile_secret_missing`, `origin_allowlist_empty`).
- [ ] `docker compose config` (ou équivalent) — pas de warning sur les
      variables non résolues.
- [ ] Les logs Symfony (`var/log/prod.log` ou stream Docker) n'affichent
      pas d'erreur au démarrage.

## 4. Test réel maîtrisé (1 seul envoi)

Objectif : valider le chemin réseau OVHcloud + réception, **une seule
fois**, en marquant clairement le message comme test.

- [ ] Depuis un navigateur (pas via curl), soumettre le formulaire avec
      une adresse `email` que l'équipe contrôle. Message : préfixé par
      `[TEST-PROD]`.
- [ ] Vérifier réception dans la boîte `CONTACT_RECIPIENT` dans les
      2 minutes.
- [ ] Vérifier que l'en-tête `Reply-To` est bien l'adresse de test.
- [ ] Vérifier que le sujet est `[Devzair] Nouvelle demande de contact — <8 caractères>`,
      **sans** nom ni email visiteur.
- [ ] Vérifier que le corps est en texte brut (pas d'HTML).
- [ ] Le message reçu contient bien le `Request-Id` complet, corrélable
      avec les logs (`X-Request-Id` en header).

## 5. Vérification côté observabilité

- [ ] Dans les logs applicatifs, l'événement `contact.accepted` apparaît
      pour l'envoi de test (canal `contact`).
- [ ] Aucun log ne contient le corps du message, l'email ou le téléphone
      du visiteur (double check sur ce test).
- [ ] Le `Request-Id` visible en front (sous le bandeau de succès) est
      exactement le même que dans `X-Request-Id` (header réponse) et dans
      les logs.

## 6. Test de dégradation contrôlée

Objectif : valider que le formulaire échoue proprement quand le SMTP
n'est pas joignable.

- [ ] Simuler l'indisponibilité en mettant temporairement
      `MAILER_DSN=null://null` **ou** un mot de passe SMTP volontairement
      erroné.
- [ ] Soumettre une demande de test.
- [ ] Vérifier une réponse HTTP `503` avec `code: "temporary_error"`.
- [ ] Vérifier le bandeau d'erreur côté navigateur (texte verbatim
      documenté dans ADR-008).
- [ ] Vérifier que les valeurs saisies sont **conservées** (l'utilisateur
      peut retenter directement).
- [ ] Vérifier l'événement de log `contact.mailer_unavailable` (niveau
      warning, canal `contact`), sans PII.
- [ ] **Restaurer** la configuration correcte.

## 7. Robustesse

- [ ] Rate limit atteint (6 requêtes en < 10 min depuis la même IP)
      → réponse `429` + header `Retry-After`. Bandeau front affiche le
      délai.
- [ ] Origin absente ou étrangère → `403 origin_not_allowed`. Vérifier
      qu'un `curl -X POST` sans header `Origin` renvoie bien 403.
- [ ] Payload > 10 KB → `413 payload_too_large`.
- [ ] Message trop court (< 20 caractères) → `400 validation_failed`
      avec erreurs par champ, formulaire non renvoyé au SMTP.

## 8. Revue de sécurité (rapide)

- [ ] Aucun endpoint autre que `POST /api/contact` n'accepte de
      soumission utilisateur non authentifiée.
- [ ] Le mot de passe SMTP n'apparaît **pas** dans `git log --all -p`
      pour la période récente (`git log --all -S "<mot de passe>"`).
- [ ] Le secret Turnstile (si utilisé) n'apparaît pas non plus.
- [ ] Le header `Cache-Control: no-store` est bien présent sur toutes
      les réponses `/api/contact` (vérifier via `curl -I`).
- [ ] Aucune réponse ne divulgue de stack trace applicative en prod
      (`APP_DEBUG=0` vérifié).

## 9. Documentation

- [ ] `docs/10-TRACKING.md` est mis à jour avec la date d'activation
      réelle et le nom du responsable.
- [ ] Le lien vers ce document est mentionné dans le canal ops
      (Slack, wiki interne, etc.).

## 10. Rollback

En cas de régression détectée après mise en prod :

- [ ] Repasser `MAILER_DSN=null://null` **et** afficher un message
      d'indisponibilité programmée dans le composant `HomeCallToAction`,
      OU
- [ ] Revert du déploiement applicatif via l'orchestrateur ou le déploiement
      Compose précédent.
- [ ] Post-mortem écrit dans `docs/10-TRACKING.md` (cause, impact,
      correctif prévu). Ne pas cocher cette case sans l'écrit.
