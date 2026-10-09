# SEO Production — Plan et recette SEO-COM-10

> Plan opérationnel de mise en production SEO. Audit live du 2026-09-06, réconcilié par l'audit du 2026-10-08 (§0), décisions et actions humaines.

---

## 0. État production vérifié au 2026-10-08

Vérifications HTTP en lecture seule sur `https://devzair.fr` (aucune action sur le serveur ni sur la base).

| Contrôle | État | Détail |
|---|---|---|
| Rebuild post SEO-COM-4/5/6/8 | ✓ Déployé | Services, réalisations et `/ressources/site-internet-pas-cher` répondent 200 |
| Sitemap | ✓ 37 URLs | 8 pages principales, 5 expertises, 8 services, 4 réalisations, 10 articles, accueil ; seuls des articles publiés y figurent |
| Robots | ✓ | Mode indexable, `/admin` exclu, politique IA DEC-096 + DEC-101 |
| Accueil SSR | ✓ | Title, description, canonical `https://devzair.fr/`, `og:site_name=Devzair`, `og:image` absolue, un seul H1 |
| Schema.org | ✓ | `Organization` (name, url, description, logo) + `WebSite` (name, url, publisher → `@id`) ; aucun `sameAs`, `address` ni `contactPoint` |
| Assets | ✓ | `/og-image.png` et `/brand/logo_devzaire_agency.png` répondent 200 (`image/png`) |
| Search Console | Active | Données disponibles (visibilité encore limitée) ; date de validation et de soumission du sitemap non documentées ici |
| **Liens morts** | ⛔ 4 | 4 articles publiés pointent vers `/ressources/seo-creation-site-internet` et `/ressources/site-vitrine-ou-sur-mesure` (404) |
| Marque dans `<main>` | ⚠ Faible | 29 pages sur 37 sans « Devzair » dans `<main>` ; hero de l'accueil sans mention de la marque |

### Correctifs présents dans Git, NON déployés (2026-10-08)

- Accueil : titre absolu `Devzair — Agence digitale : sites web, applications et SEO` (option `absoluteTitle` de `usePageSeo`, les autres pages gardent « %s | Devzair ») ; introduction du hero commençant par « Devzair est une agence digitale… ».
- Footer (toutes pages) : description nommant Devzair et le rôle d'agence digitale.
- `/agence` : introduction « Devzair, c'est un interlocuteur direct… ».
- Sources Markdown des 4 articles corrigées ; deux sources retirées archivées dans `content/archive/resources/` ; slugs retirés du bootstrap.

Les 4 articles publiés ne seront **pas** corrigés par le déploiement : l'import est create-only. Voir §16.

---

## 1. État production au 2026-09-06 (historique)

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

> Mise à jour 2026-10-08 : le rebuild P0 est constaté en production (§0). Nouvelles actions humaines : déployer le lot « marque et liens morts » (§0), republier les 4 articles (§16), dérouler la checklist (§17).

### P0 — Bloquant (avant Search Console) — FAIT (constaté le 2026-10-08)

- [x] **Rebuild + redéploiement production** avec le code `main` actuel (commit `b4470cd`)
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

## 16. Republication d'un article déjà publié (correction de contenu)

**Contexte** : `app:editorial:import` est create-only et l'agrégat `Article` n'accepte une modification qu'en `Draft`. Corriger un fichier `content/resources/*.md` ne modifie pas l'article publié. Le seul chemin existant est l'admin HTTP : `Published → Archived → Draft → (édition) → Published`.

**Garanties vérifiées dans le code** :
- le slug est immuable (`UpdateDraftArticle` ne le modifie jamais) ;
- `publishedAt` (date de première publication, `BlogPosting.datePublished`) est conservé à la restauration et à la republication — la date saisie au moment de republier est ignorée ;
- `updatedAt` est mis à jour (→ `dateModified`, `lastmod` du sitemap) ;
- côté Nuxt, le cache éditorial revalide chaque détail par ETag et purge l'entrée sur 404 : aucune invalidation manuelle n'est nécessaire ; le listing `/ressources` est en SWR 60 s.

**Indisponibilité** : entre « Archiver » et « Publier », l'article répond **404** et disparaît du listing et du sitemap. Préparer le texte avant d'archiver pour limiter la fenêtre à 1–2 minutes par article ; traiter les articles un par un, hors heure de forte audience.

### Procédure manuelle (validation humaine obligatoire, en production)

Pré-requis : sauvegarde PostgreSQL récente (`pg_dump`), accès admin, texte corrigé copié depuis le fichier Git correspondant.

Pour chaque article (`ameliorer-visibilite-locale-entreprise`, `creer-site-internet-professionnel`, `maintenance-site-internet`, `application-metier-remplacer-excel`) :

1. `/admin/articles` → ouvrir l'article ; noter le statut, la date de publication et l'image principale.
2. Ouvrir l'édition et copier le corps Markdown actuel dans un fichier local (sauvegarde de retour arrière).
3. **Archiver** l'article (l'URL publique passe en 404).
4. **Restaurer** l'article (statut `Draft`).
5. **Éditer** : remplacer uniquement le paragraphe concerné par la version de `content/resources/<slug>.md` ; enregistrer. Ne pas toucher au titre, à l'extrait ni au SEO.
6. **Prévisualiser** et vérifier le lien remplacé.
7. **Publier**. Vérifier que la date de publication affichée est l'ancienne.
8. Contrôler immédiatement :
   ```bash
   curl -s -o /dev/null -w "%{http_code}\n" https://devzair.fr/ressources/<slug>       # 200
   curl -s https://devzair.fr/ressources/<slug> | grep -c 'seo-creation-site-internet\|site-vitrine-ou-sur-mesure'  # 0
   curl -s https://devzair.fr/ressources/<slug> | grep -o '"datePublished":"[^"]*"'   # date inchangée
   ```

**Ne pas** : relancer `editorial-content-bootstrap.sh` en production pour corriger un article, supprimer un article, rediriger les URL retirées vers l'accueil.

**Retour arrière** : si l'édition pose problème, repasser par Archiver → Restaurer → coller le corps sauvegardé (étape 2) → Publier. Les URL retirées restent en 404 dans tous les cas.

---

## 17. Checklist après déploiement du lot « marque et liens morts » (2026-10-08)

```bash
# Accueil : titre unique de marque, introduction, H1 unique
curl -s https://devzair.fr/ | grep -o '<title>[^<]*</title>'
#   attendu : <title>Devzair — Agence digitale : sites web, applications et SEO</title>
curl -s https://devzair.fr/ | grep -o 'og:title" content="[^"]*"'
curl -s https://devzair.fr/ | grep -c '<h1'                                 # 1
curl -s https://devzair.fr/ | grep -c 'Devzair est une agence digitale'     # ≥ 2 (hero + footer)
curl -s https://devzair.fr/ | grep -o '<link rel="canonical"[^>]*>'          # https://devzair.fr/

# Template inchangé ailleurs
curl -s https://devzair.fr/agence | grep -o '<title>[^<]*</title>'          # … | Devzair

# JSON-LD
curl -s https://devzair.fr/ | grep -o '"@type":"\(Organization\|WebSite\)","@id":"[^"]*","[a-z]*":"[^"]*"'

# URL retirées : 404, absentes du sitemap
for s in seo-creation-site-internet site-vitrine-ou-sur-mesure; do
  curl -s -o /dev/null -w "$s %{http_code}\n" https://devzair.fr/ressources/$s   # 404
done
curl -s https://devzair.fr/sitemap.xml | grep -c 'seo-creation-site-internet\|site-vitrine-ou-sur-mesure'  # 0
curl -s https://devzair.fr/sitemap.xml | grep -c '<loc>'                         # 37 (inchangé)
```

Après republication des 4 articles (§16) : aucune page du sitemap ne doit contenir `href="/ressources/seo-creation-site-internet"` ni `href="/ressources/site-vitrine-ou-sur-mesure"`. Dans Search Console : inspecter `https://devzair.fr/` et demander une indexation ; suivre les impressions de la requête « devzair » sans attendre de résultat garanti.

---

## Règle de maintenance

Ce document est mis à jour à chaque événement majeur : rebuild, validation Search Console, incident, bilan mensuel. Ne pas dupliquer les données de `docs/13-SEO-COMMERCIAL.md` ni `docs/14-BRAND-AUTHORITY.md`.
