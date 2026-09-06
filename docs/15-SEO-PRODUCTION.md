# SEO Production — Plan et recette SEO-COM-10

> Plan opérationnel de mise en production SEO. Audit live du 2026-09-06, décisions et actions humaines.

---

## 1. État production au 2026-09-06

| Contrôle | État | Détail |
|---|---|---|
| Domaine live | ✓ | `https://devzair.fr` — accessible |
| HTTPS | ✓ | HTTP/2, certificat valide |
| `siteIndexable` runtime | ✓ | `true` — site ouvert aux crawlers |
| Robots.txt | ✓ | Mode indexable, politique IA conforme DEC-096+101 |
| Canonical homepage | ✓ | `https://devzair.fr/` |
| SSR contenu homepage | ✓ | Title, OG, Schema.org Organization+WebSite présents |
| Schema.org | ✓ | Organization + WebSite, aucune donnée fictive |
| **Image production** | ⛔ **OBSOLÈTE** | Build antérieur à SEO-COM-4/5/6/8 |
| Services (hub + 8 pages) | ⛔ 404 | Non présents dans l'image déployée |
| Réalisations (hub + 4 pages) | ⛔ 404 | Non présents dans l'image déployée |
| `/ressources/site-internet-pas-cher` | ⛔ 404 | Non présent dans l'image déployée |
| `/agence` (contenu) | ⛔ Ancien | « Globale » au lieu d'« Intégrée », lead pré-SEO-COM-8 |
| Navigation | ⛔ Ancienne | Services et Réalisations absents du menu |
| Sitemap | ⛔ 11 URLs | Services, réalisations et article manquants |
| Haramain-prestige | ✓ 404 | Correctement absent |
| E-shop-admin | ✓ 404 | Correctement absent |

### Redirections (live)

| Source | Code | Destination |
|---|---|---|
| `http://devzair.fr/` | 308 | `https://devzair.fr/` ✓ |
| `http://www.devzair.fr/` | 308 | `https://www.devzair.fr/` → 301 → `https://devzair.fr/` |
| `https://www.devzair.fr/` | 301 | `https://devzair.fr/` ✓ |

La chaîne www HTTP → www HTTPS → non-www est une double redirection. Acceptable mais à surveiller. Caddy gère le tout.

---

## 2. Checklist GO/NO-GO (état au 2026-09-06)

| Item | État |
|---|---|
| Domaine final `https://devzair.fr` | ✓ GO |
| HTTPS valide, pas de mixed content | ✓ GO |
| Canonical HTTPS sans localhost | ✓ GO (pages disponibles) |
| Aucune canonical localhost dans pages live | ✓ GO |
| Aucune page placeholder | ✓ GO |
| 8 services publiés | ⛔ NO-GO — image obsolète |
| 5 expertises publiées | ✓ GO |
| 4 réalisations publiques | ⛔ NO-GO — image obsolète |
| Haramain absent | ✓ GO |
| E-Shop Admin absent | ✓ GO |
| Article prix publié | ⛔ NO-GO — image obsolète |
| Sitemap cohérent (toutes pages) | ⛔ NO-GO — 11 URLs seulement |
| Robots production cohérent | ✓ GO |
| Pages 404 correctes | ✓ GO (haramain, e-shop-admin) |
| Admin exclu du crawl | ✓ GO (`/admin` bloqué dans robots.txt) |
| Aucun secret exposé | ✓ GO |
| Build production vert | ✓ GO (image actuelle fonctionne) |
| SSR production vert | Partiel — pages disponibles OK |

**VERDICT : NO-GO — rebuild production obligatoire avant ouverture à Search Console.**

---

## 3. Action humaine critique — Rebuild production

### Pourquoi le rebuild est nécessaire

L'image Docker actuelle a été buildée avec un commit antérieur à SEO-COM-4 (réalisations), SEO-COM-5 (services), SEO-COM-6 (article prix) et SEO-COM-8 (agence, home). Elle manque :
- 8 pages services + hub services
- 4 pages réalisations + hub réalisations
- Article `/ressources/site-internet-pas-cher`
- Les corrections éditoriales de `/agence` et de la homepage

### Commandes de rebuild (à exécuter sur le VPS)

```bash
# 1. Aller à la racine du projet
cd /opt/devzair  # ou le chemin réel du projet sur le VPS

# 2. S'assurer que .env.prod est à jour avec :
#    NUXT_PUBLIC_SITE_URL=https://devzair.fr
#    NUXT_PUBLIC_SITE_INDEXABLE=true
#    NUXT_PUBLIC_TURNSTILE_SITE_KEY=<clé réelle>
#    NUXT_PUBLIC_TURNSTILE_ENABLED=true
#    (et toutes les autres variables requises)

# 3. Récupérer le dernier code
git pull origin main

# 4. Rebuild + redéploiement
docker compose -f compose.prod.yaml --env-file .env.prod build --no-cache web
docker compose -f compose.prod.yaml --env-file .env.prod up -d --no-deps web

# 5. Vérification immédiate
curl -s -o /dev/null -w "%{http_code}\n" https://devzair.fr/services
curl -s -o /dev/null -w "%{http_code}\n" https://devzair.fr/realisations
curl -s -o /dev/null -w "%{http_code}\n" https://devzair.fr/ressources/site-internet-pas-cher
```

**Note importante** : depuis SEO-COM-10, `NUXT_PUBLIC_SITE_INDEXABLE` est maintenant **obligatoire** (`:?` dans compose.prod.yaml) — le build échoue si la variable est absente ou vide. C'est intentionnel.

### Rollback si nécessaire

```bash
# Remettre l'ancienne image (Docker garde les layers précédents)
docker compose -f compose.prod.yaml up -d --no-deps web
# Ou : pointer sur un tag d'image précis si la politique de tag est en place
```

---

## 4. Politique d'indexation — Séparation des environnements

| Environnement | `NUXT_PUBLIC_SITE_INDEXABLE` | Résultat |
|---|---|---|
| Dev local | `false` (`.env.example` default) | noindex + robots bloquant |
| CI/Test | `false` (pas de surcharge) | noindex + robots bloquant |
| Production | **`true` (obligatoire dans `.env.prod`)** | indexable |

Depuis SEO-COM-10 : `compose.prod.yaml` utilise `:?` — toute tentative de déploiement production sans variable explicite échoue avec un message d'erreur clair. Cela empêche l'ouverture accidentelle à l'indexation ET la fermeture accidentelle.

---

## 5. Recette post-rebuild (à faire immédiatement après déploiement)

### HTTP et contenu

```bash
for path in "/" "/services" "/services/creation-site-internet" "/services/seo-referencement-naturel" "/realisations" "/realisations/kitchen-meat" "/realisations/nidemiel" "/ressources" "/ressources/site-internet-pas-cher" "/agence" "/expertises" "/contact" "/estimer-mon-projet"; do
  echo -n "$path → "
  curl -s -o /dev/null -w "%{http_code}\n" "https://devzair.fr$path"
done
```

**Attendu** : 200 sur toutes les routes ci-dessus.

```bash
# 404 obligatoires
for path in "/realisations/haramain-prestige" "/realisations/e-shop-admin" "/services/inexistant"; do
  echo -n "$path → "
  curl -s -o /dev/null -w "%{http_code}\n" "https://devzair.fr$path"
done
```

**Attendu** : 404 sur toutes.

### Canonical et indexation

```bash
curl -s https://devzair.fr/services/creation-site-internet | grep -oE 'canonical.*devzair[^"]*|noindex'
```

**Attendu** : canonical `https://devzair.fr/services/creation-site-internet`, aucun `noindex`.

### Sitemap

```bash
curl -s https://devzair.fr/sitemap.xml | grep -c '<loc>'
```

**Attendu** : ≥ 25 URLs (6 principales + 5 expertises + 9 services + 5 réalisations + 1 estimateur + articles publiés).

Décompte attendu :
- Pages principales : `/`, `/agence`, `/contact`, `/expertises`, `/ressources`, `/estimer-mon-projet` → 6
- Expertises détail : 5
- Services hub + 8 pages : 9
- Réalisations hub + 4 pages : 5
- Article : `/ressources/site-internet-pas-cher` → 1 (+ lastmod via API Symfony)
- **Total minimum** : 26

### Contenu /agence (SEO-COM-8)

```bash
curl -s https://devzair.fr/agence | grep -E 'Intégrée|interlocuteur direct|Globale|équipe réduite'
```

**Attendu** : "Intégrée" et "interlocuteur direct" présents, "Globale" et "équipe réduite" absents.

---

## 6. Search Console

**État au 2026-09-06** : EN ATTENTE ACTION HUMAINE.

### Étape 1 — Créer la propriété Domain

1. Ouvrir [Google Search Console](https://search.google.com/search-console/)
2. Ajouter une propriété → **Domaine** (pas "Préfixe URL")
3. Saisir : `devzair.fr`
4. Google fournit un enregistrement TXT de type : `google-site-verification=XXXX`

### Étape 2 — Ajouter le TXT DNS

Chez le fournisseur DNS (OVH, Cloudflare, ou autre) :
- Type : `TXT`
- Nom/Hôte : `@` (ou `devzair.fr`)
- Valeur : la valeur exacte fournie par Google
- **Ne jamais inventer cette valeur**
- **Ne jamais supprimer les TXT existants** (SPF, DKIM, DMARC)
- Ajouter en complément

### Étape 3 — Valider dans Search Console

Après propagation DNS (quelques minutes à quelques heures) :
- Cliquer "Vérifier" dans Search Console
- Conserver l'enregistrement DNS indéfiniment (Google re-vérifie périodiquement)

### Étape 4 — Soumettre le sitemap

1. Indexation → Sitemaps
2. Saisir l'URL : `sitemap.xml`
3. Cliquer "Envoyer"
4. Documenter la date de soumission ici

### Étape 5 — Inspecter les URLs prioritaires

Après validation :

| URL | Action | Attendu |
|---|---|---|
| `https://devzair.fr/` | Inspecter | Accessible, indexable |
| `https://devzair.fr/services` | Inspecter | Accessible, indexable |
| `https://devzair.fr/services/creation-site-internet` | Inspecter | Accessible, indexable |
| `https://devzair.fr/realisations` | Inspecter | Accessible, indexable |
| `https://devzair.fr/ressources/site-internet-pas-cher` | Inspecter | Accessible, indexable |

---

## 7. Domaine canonique

- **Canonique final** : `https://devzair.fr` (non-www, HTTPS)
- `NUXT_PUBLIC_SITE_URL=https://devzair.fr` dans `.env.prod`
- Toutes les variantes redirigent vers cette URL
- Aucune version www parallèle indexable

---

## 8. Sitemap — État cible post-rebuild

Le sitemap doit contenir après rebuild :

| Catégorie | URLs attendues |
|---|---|
| Pages principales | `/`, `/agence`, `/contact`, `/expertises`, `/ressources`, `/estimer-mon-projet` |
| Expertises détail | 5 × `/expertises/{slug}` |
| Services hub | `/services` |
| Services détail | 8 × `/services/{slug}` |
| Réalisations hub | `/realisations` |
| Réalisations détail | 4 × `/realisations/{slug}` |
| Ressources | `/ressources/site-internet-pas-cher` + lastmod |

**Exclusions vérifiées** :
- `/realisations/haramain-prestige` → absent ✓
- `/realisations/e-shop-admin` → absent ✓
- `/admin/**` → absent ✓
- Articles draft/archived → absents ✓

---

## 9. Baseline Search Console

À remplir quand les premières données sont disponibles (J+14 minimum).

| Métrique | Valeur | Date |
|---|---|---|
| Propriété validée | — | À compléter |
| Sitemap soumis | — | À compléter |
| URLs découvertes | — | À compléter |
| Premières impressions | PAS ENCORE DE DONNÉES | — |
| Premières requêtes de marque | PAS ENCORE DE DONNÉES | — |
| Position moyenne initiale | PAS ENCORE DE DONNÉES | — |

---

## 10. Cadence de suivi post-activation

| Jalon | Action |
|---|---|
| J0 | Rebuild + redéploiement. Recette HTTP/sitemap/canonical. |
| J+2/J+3 | Vérifier les premières découvertes dans Search Console (Indexation / Pages). |
| J+7 | Premier contrôle indexation. Identifier exclusions anormales. |
| J+14 | Premières tendances si données Search Console disponibles. Ajuster `docs/15-SEO-PRODUCTION.md`. |
| J+28 | Premier bilan SEO exploitable. Décider si publication des 3 articles prix restants. |

---

## 11. Analytics

**État** : NON CONFIGURÉ.

Google Analytics 4 ou équivalent n'est pas déployé à ce stade. La décision implique consentement, cookies, et conformité RGPD — à traiter dans un chantier distinct.

Search Console suffit pour les données d'impressions et de requêtes Google.

---

## 12. Core Web Vitals

Données terrain (CrUX / Search Console) : **PAS ENCORE DE DONNÉES** — trafic insuffisant au démarrage.

Lighthouse lab (mobile, une fois le rebuild fait) :
- URL : `https://devzair.fr/`
- URL : `https://devzair.fr/services/creation-site-internet`
- URL : `https://devzair.fr/ressources/site-internet-pas-cher`
- À documenter ici après exécution

---

## 13. Rollback SEO

En cas d'incident après ouverture à l'indexation :

```bash
# Remettre siteIndexable=false
# Modifier .env.prod : NUXT_PUBLIC_SITE_INDEXABLE=false
# Rebuild + redéploiement
docker compose -f compose.prod.yaml --env-file .env.prod build web
docker compose -f compose.prod.yaml --env-file .env.prod up -d --no-deps web
```

**Attention** : après indexation Google, repasser à `noindex` ne désindexe pas immédiatement. C'est un signal fort à réserver aux incidents réels (fuite de contenu, erreur critique, environnement non prêt).

---

## 14. Prévention de désindexation accidentelle

Depuis SEO-COM-10 :
- `compose.prod.yaml` : `NUXT_PUBLIC_SITE_INDEXABLE` est obligatoire (`:?`)
- Tout déploiement production sans cette variable **échoue explicitement**
- Dev/staging restent sur `false` sans exception
- `.env.example` documente l'obligation

Pour les futurs déploiements automatisés (CI/CD) : inclure `NUXT_PUBLIC_SITE_INDEXABLE=true` dans les secrets de l'environnement de production.

---

## 15. Actions restantes — Priorisées

### P0 — Bloquant (avant Search Console)

- [ ] **Rebuild + redéploiement production** avec le code `main` actuel (commit `b4470cd`)
  - Commandes : voir §3
  - Vérifier HTTP 200 sur services, réalisations, article
  - Vérifier sitemap ≥ 26 URLs
  - Vérifier `/agence` avec contenu SEO-COM-8

### P1 — Search Console (après rebuild)

- [ ] **Créer propriété Domain** `devzair.fr` dans Google Search Console
- [ ] **Ajouter TXT DNS** de validation (valeur fournie par Google)
- [ ] **Soumettre** `sitemap.xml`
- [ ] **Inspecter** les 5 URLs prioritaires (§6 étape 5)
- [ ] **Documenter** date de soumission + état initial dans §9

### P2 — Suivi

- [ ] J+7 : contrôle indexation
- [ ] J+14 : premières tendances
- [ ] J+28 : bilan et décision articles prix

---

## Règle de maintenance

Ce document est mis à jour à chaque événement majeur : rebuild, validation Search Console, incident, bilan mensuel. Ne pas dupliquer les données de `docs/13-SEO-COMMERCIAL.md` ni `docs/14-BRAND-AUTHORITY.md`.
