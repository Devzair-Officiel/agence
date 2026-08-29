# Administration Devzair — Refonte UI/UX progressive

> Spécification de conception — Phase 0 : audit et plan d'implémentation.
>
> Cette refonte modernise l'interface d'administration Symfony/Twig existante.
> Elle ne réécrit pas l'application et n'introduit ni framework d'admin,
> ni Vue/React, ni Tailwind, ni Bootstrap.
>
> **Ne pas coder avant validation de ce document.**

---

## Sommaire

1. [État réel de l'admin aujourd'hui](#1-état-réel-de-ladmin-aujourdhui)
2. [Architecture actuelle](#2-architecture-actuelle)
3. [Contraintes de sécurité à préserver](#3-contraintes-de-sécurité-à-préserver)
4. [Problèmes UI/UX constatés](#4-problèmes-uiux-constatés)
5. [Décisions UI/UX validées](#5-décisions-uiux-validées)
6. [Design tokens à utiliser](#6-design-tokens-à-utiliser)
7. [Comportement responsive](#7-comportement-responsive)
8. [Écrans concernés](#8-écrans-concernés)
9. [Fonctionnalités explicitement différées](#9-fonctionnalités-explicitement-différées)
10. [Contrainte future n8n](#10-contrainte-future-n8n)
11. [Stratégie de tests](#11-stratégie-de-tests)
12. [Plan d'implémentation par phases](#12-plan-dimplément-par-phases)
13. [Critères d'acceptation par phase](#13-critères-dacceptation-par-phase)
14. [Checklist d'avancement](#14-checklist-davancement)

---

## 1. État réel de l'admin aujourd'hui

### 1.1 Périmètre fonctionnel réel

L'administration est **entièrement fonctionnelle et en production** depuis les Phases 8C1–8C4 et 9A (terminées). Elle couvre :

| Module | Fonctionnalités présentes |
|---|---|
| Authentification | Login form login (email/password), logout POST, throttling 5/15 min, session sécurisée `DZ_ADMIN_SESSID` |
| Dashboard | Affichage profil admin (displayName, email, lastLoginAt) + bouton Logout |
| Articles | Liste paginée 20 items, filtres par statut, création brouillon, édition, publication, archivage, restauration, prévisualisation |
| Médias | Liste paginée 20 items, upload (JPEG/PNG/WebP ≤ 8 MiB, ≤ 8000×8000 px), prévisualisation authentifiée |
| Image principale | Chooser paginé pour associer un media à un article (avec alt-text) |

### 1.2 Templates Twig (14 fichiers)

```
apps/api/templates/admin/
├── _layout.html.twig           # Layout de base (topbar, flashs, main container)
├── login.html.twig             # Formulaire login
├── dashboard.html.twig         # Tableau de bord actuel (profil + logout)
├── articles/
│   ├── list.html.twig          # Liste paginée + filtre statut
│   ├── new.html.twig           # Création brouillon
│   ├── edit.html.twig          # Édition article
│   ├── _form.html.twig         # Formulaire mutualisé create/edit
│   ├── _row_actions.html.twig  # Actions inline par article
│   ├── preview.html.twig       # Prévisualisation authentifiée
│   ├── hero_image.html.twig    # Chooser image principale
│   └── rate_limited.html.twig  # Page d'attente throttle
└── media/
    ├── list.html.twig          # Liste paginée médias
    ├── new.html.twig           # Formulaire upload
    └── rate_limited.html.twig  # Page d'attente throttle
```

### 1.3 CSS actuel

**Fichier :** `apps/api/public/admin/assets/admin.css` (909 lignes)

**État actuel :**
- Palette générique (bleus système, gris système, fond blanc) sans rapport avec l'identité Devzair
- Typographie `system-ui, -apple-system, "Segoe UI", Roboto` — aucune police Devzair
- Navigation uniquement via `.admin-topbar` horizontal (fond noir `#111`)
- Pas de sidebar
- Layout en colonne unique partout
- Composants fonctionnels mais sans identité visuelle (`.card`, `.button-primary`, `.status-badge`, `.admin-table`)

### 1.4 Routes admin complètes

| Route | Chemin | Méthodes |
|---|---|---|
| `admin_login` | `/admin/login` | GET, POST |
| `admin_dashboard` | `/admin` | GET |
| `admin_logout` | `/admin/logout` | POST |
| `admin_articles_list` | `/admin/articles` | GET |
| `admin_article_create` | `/admin/articles/new` | GET, POST |
| `admin_article_edit` | `/admin/articles/{id}/edit` | GET, POST |
| `admin_article_preview` | `/admin/articles/{id}/preview` | GET |
| `admin_article_publish` | `/admin/articles/{id}/publish` | POST |
| `admin_article_archive` | `/admin/articles/{id}/archive` | POST |
| `admin_article_restore` | `/admin/articles/{id}/restore` | POST |
| `admin_article_hero_image` | `/admin/articles/{id}/image` | GET, POST |
| `admin_article_hero_image_remove` | `/admin/articles/{id}/image/remove` | POST |
| `admin_media_list` | `/admin/media` | GET |
| `admin_media_upload` | `/admin/media/new` | GET, POST |
| `admin_media_preview` | `/admin/media/{id}/preview` | GET |

---

## 2. Architecture actuelle

### 2.1 Stack technique

```
Caddy (reverse proxy, port hôte 3001)
  └── /admin/** → Symfony 7.4 / PHP 8.4 (SSR Twig, pas de JS)
      ├── PostgreSQL 17 (editorial_article, admin_user, editorial_media_asset)
      └── Stockage fichiers privé hors webroot
```

### 2.2 Couches applicatives

| Couche | Emplacement | Responsabilité |
|---|---|---|
| Présentation HTTP | `src/Admin/Presentation/Http/` | Contrôleurs (CSRF, rate limit, redirection PRG) |
| Application | `src/Editorial/Application/Command/` | Handlers (validation atomique, mutations domaine) |
| Domaine | `src/Editorial/Domain/` `src/Admin/Domain/` `src/EditorialMedia/Domain/` | Agrégats, VOs, invariants, transitions |
| Infrastructure | `src/*/Infrastructure/` | Doctrine, sessions, audit logger, rate limiter |
| Templates | `templates/admin/` | Twig SSR, aucun JS |

### 2.3 Entités

**`Article`** (table `editorial_article`) :
- Champs éditoriaux : `slug` (immuable), `title`, `excerpt`, `bodyMarkdown`, `seoTitle`, `seoDescription`, `authorName`, `authorType`, `expertiseIds`
- Cycle de vie : `status` (Draft → Published → Archived ↔ Draft via restore)
- Image : `heroMediaId`, `heroImageAlt`
- Timestamps : `createdAt`, `updatedAt`, `publishedAt` (conservé après archivage)

**`AdminUser`** (table `admin_user`) :
- Champs : `id`, `email`, `normalizedEmail` (UNIQUE), `passwordHash`, `displayName`, `isActive`, `lastLoginAt`, `createdAt`, `updatedAt`
- Rôle implicite unique : `ROLE_ADMIN` (pas de colonne `roles`)

**`MediaAsset`** (table `editorial_media_asset`) :
- Champs : `id`, `storageKey` (UNIQUE, `{uuid}/original.{ext}`), `originalFilename`, `mimeType`, `sizeBytes`, `width`, `height`, `sha256`, `createdAt`
- Aucune FK vers `editorial_article` en Phase 9A (rattachement prévu en 9B)

### 2.4 Formulaires

Aucun Form Type Symfony — hydratation manuelle via DTOs (`ArticleCreateData`, `ArticleEditData`). Validation entièrement dans les handlers et les VOs du domaine.

---

## 3. Contraintes de sécurité à préserver

### 3.1 CSRF

Tokens contextuels par action et par article (jamais un token global) :

| Action | Token ID |
|---|---|
| Login | `authenticate` |
| Logout | `logout` |
| Création article | `article_create` |
| Édition article | `article_edit_{id}` |
| Publication | `article_publish_{id}` |
| Archivage | `article_archive_{id}` |
| Restauration | `article_restore_{id}` |
| Définir image principale | `article_hero_image_set_{id}` |
| Retirer image principale | `article_hero_image_remove_{id}` |
| Upload média | `media_upload` |

**Invariant :** Un token exfiltré par XSS ne peut affecter qu'une seule action sur un seul article.

### 3.2 CSP admin (stricte, ne pas affaiblir)

```
default-src 'none'
script-src 'none'
style-src 'self'
img-src 'self' data:
font-src 'self'
form-action 'self'
base-uri 'none'
frame-ancestors 'none'
```

**Conséquences pour la refonte :**
- Aucun script inline ni externe possible
- CSS externalisé uniquement (`admin.css` statique servi par Symfony)
- Polices auto-hébergées uniquement (voir §6.4)
- Images servies localement ou en `data:` URI
- Toute future introduction de JavaScript doit être justifiée fonctionnellement **et** nécessitera une revision de la CSP (`nonce` ou hash — pas `unsafe-inline`)

### 3.3 Rate limiting (à préserver)

| Limiteur | Politique | Seuil actuel |
|---|---|---|
| `admin_write` | Token bucket | 60 req / 5 min par admin |
| `admin_publish` | Token bucket | 20 req / 5 min par admin |
| `admin_media_upload` | Token bucket | Env `ADMIN_MEDIA_UPLOAD_LIMIT` / `ADMIN_MEDIA_UPLOAD_INTERVAL` |
| Login throttling | Symfony native | 5 tentatives / 15 min |

### 3.4 Autres contraintes

- **Session :** `DZ_ADMIN_SESSID` HttpOnly, SameSite=Lax, Secure=auto, `session_fixation_strategy: migrate`
- **Audit logging :** Canal Monolog `admin`, jamais de PII (email, displayName, IP), UUID admin uniquement
- **Password hashing :** Argon2id (`auto`) en prod, Bcrypt cost 4 en test
- **Headers défensifs :** `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: no-referrer`, `Cache-Control: private, no-store`, `X-Robots-Tag: noindex, nofollow`, COOP/CORP `same-origin`
- **Validation d'UUID :** Regex `[0-9a-fA-F-]{36}` sur chaque route avec paramètre UUID
- **AdminUserChecker :** Refuse l'authentification si `is_active = false`

---

## 4. Problèmes UI/UX constatés

### 4.1 Navigation — Problèmes critiques

| Problème | Impact |
|---|---|
| Topbar horizontal uniquement (fond `#111`, max-width 64rem) | Espace de navigation limité ; pas de place pour afficher le contexte de la page en cours ; structure non évolutive |
| Aucune indication de page active dans la navigation | L'utilisateur ne sait pas où il se trouve |
| Aucun accès aux informations admin dans la nav | Profil utilisateur caché dans le dashboard seulement |
| Navigation étroite sur mobile | Liens topbar compressés sans solution responsive dédiée |

### 4.2 Dashboard — Problèmes majeurs

| Problème | Impact |
|---|---|
| Le contenu principal est la fiche admin (displayName, email, lastLoginAt) | Le dashboard n'apporte aucune information éditoriale utile |
| Aucun compteur d'articles ni de médias | Impossible de connaître l'état du contenu d'un coup d'œil |
| Aucune activité récente | Pas de visibilité sur les dernières modifications |
| Aucune action rapide | L'admin doit naviguer pour atteindre son action principale |

### 4.3 Liste des articles — Problèmes

| Problème | Impact |
|---|---|
| Tableau HTML générique | Le titre n'est pas dominant (même taille que le slug) |
| Slug affiché comme colonne dédiée (très large) | Occupe trop d'espace ; information secondaire traitée comme primaire |
| Rangée d'actions inline sans groupement | Plusieurs boutons côte à côte (Éditer, Publier, Archiver) encombrent la ligne |
| Aucune hiérarchie visuelle entre titre et métadonnées | Scannabilité faible |

### 4.4 Éditeur d'article — Problème structurel majeur

| Problème | Impact |
|---|---|
| Colonne unique | Le bloc Publication (cycle de vie) est enterré sous le contenu ; l'admin doit défiler pour publier |
| Champs éditoriaux et champs de publication mélangés | Pas de séparation entre le travail éditorial et les actions de cycle de vie |
| Image principale sur une page séparée (`/admin/articles/{id}/image`) | Friction importante ; nécessite deux navigations pour configurer un article |

### 4.5 Médiathèque — Problèmes

| Problème | Impact |
|---|---|
| Tableau liste uniquement (nom, type, dimensions, poids, date) | Aucun aperçu visuel ; impossible de reconnaître une image rapidement |
| Thumb 80×80 dans un tableau dense | Trop petit pour distinguer les images |
| Fiche détail inexistante | Pas d'accès aux métadonnées complètes ni à un aperçu plein format |

### 4.6 Identité visuelle — Problème global

| Problème | Impact |
|---|---|
| Palette générique (bleu `#2563eb`, fond blanc, gris système) | Aucune cohérence avec le site public Devzair |
| Typographie system-ui uniquement | Rupture totale avec Schibsted Grotesk / Hanken Grotesk |
| Topbar fond `#111` (noir générique) | Non aligné avec `--color-navy` de Devzair |
| Pas d'espace respirant — main max-width 64rem avec padding 0 1.5rem | Densité qui manque d'organisation visuelle |

---

## 5. Décisions UI/UX validées

### 5.1 Shell admin

**Desktop (≥ 1024 px) :**
- Sidebar persistante **240–264 px** de largeur, fond `--color-navy` / `--color-navy-deep`
- Zone de travail principale prend le reste (`calc(100vw - 256px)` environ)
- Sidebar ne défile pas avec le contenu (position fixed ou sticky)

**Structure de la sidebar :**

```
┌─────────────────────────────────────────────────────────────────────┐
│ SIDEBAR (240–264 px)      │ ZONE DE TRAVAIL                         │
│ fond navy                 │                                          │
│                           │                                          │
│  [Logo/Marque Devzair]    │                                          │
│                           │                                          │
│  Navigation principale :  │                                          │
│  • Tableau de bord        │                                          │
│  • Articles               │                                          │
│  • Médias                 │                                          │
│                           │                                          │
│  ──────────────────────   │                                          │
│                           │                                          │
│  ══════════════════════   │                                          │
│                           │                                          │
│  [Admin: displayName]     │                                          │
│  Voir le site ↗           │                                          │
│  [Déconnexion]            │                                          │
└─────────────────────────────────────────────────────────────────────┘
```

**Navigation active :** item courant doit être clairement identifiable (fond légèrement plus clair + accent coloré ou bordure gauche en `--color-petrol` ou `--color-devzair-blue`).

**À ne pas afficher** tant qu'inexistant : "Utilisateurs" (gestion multi-utilisateurs non implémentée), "Statistiques" (aucune métrique réelle), "Paramètres" (aucune fonctionnalité correspondante), "Compte" (aucune route dédiée existante — le nom de l'admin connecté s'affiche dans le footer de sidebar sans lien).

### 5.2 Dashboard

Objectif : vue de synthèse éditoriale (jamais la fiche profil comme contenu principal).

**Données affichables depuis le backend actuel :**
- Nombre d'articles publiés
- Nombre de brouillons
- Nombre d'articles archivés
- Nombre de médias
- Articles récemment modifiés (liste des N derniers par `updated_at DESC`)
- Actions rapides : "Nouveau brouillon", "Voir les articles publiés"

**Contrainte :** Ne jamais afficher de compteur ou métrique sans données réelles. Si un compteur n'est pas encore exposé par le backend, ne pas le créer.

### 5.3 Liste des articles

- Titre de page + CTA "Nouveau brouillon"
- Filtres visuels par statut (Draft / Publié / Archivé), accessible sans JS via query param `?status=`
- Titre d'article **dominant** (plus grand, gras)
- Slug affiché **sous le titre** comme information secondaire (style monospace discret), pas comme colonne dédiée
- Badge de statut coloré
- Date de dernière modification
- **Une** action principale évidente (Éditer)
- Actions secondaires regroupées : si une solution HTML-native sans JS est propre et accessible, privilégier `<details>/<summary>` ou un formulaire de sélection ; sinon, grouper en boutons petits bien espacés

### 5.4 Éditeur d'article — Layout deux colonnes

**Structure CSS de base :**

```css
.article-editor-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 340px;
  gap: 24px; /* à ajuster entre 24 et 32px */
  align-items: start;
}
```

**Colonne principale (~2/3) :**
- Titre
- Slug (affiché en lecture seule à l'édition, avec une note sur son immuabilité)
- Chapô (`excerpt`)
- Contenu Markdown (`bodyMarkdown`)
- Éléments éditoriaux nécessitant une grande largeur

**Colonne latérale (~340 px, sticky sur desktop) :**
```css
.article-editor-sidebar {
  position: sticky;
  top: 24px;
  max-height: calc(100vh - 48px);
  overflow-y: auto;
}
```

Contenu de la sidebar, dans l'ordre recommandé :
1. **Bloc Publication** (le plus visible) : statut actuel, bouton publier/archiver/restaurer selon les règles actuelles, dates utiles
2. **Actions** : sauvegarder, prévisualiser
3. **Image principale** : aperçu de l'image actuelle + lien vers le chooser (ou chooser inline si réalisable sans page dédiée dans une phase future)
4. **SEO** : seoTitle, seoDescription
5. **Piliers d'expertise** : checkboxes
6. **Auteur** : authorName, authorType

**Sur tablette/mobile :** repasser en colonne unique. Bloc Publication placé EN PREMIER (avant le formulaire de contenu) pour rester accessible sans défilement.

Tous les champs, validations serveur, messages d'erreur, CSRF et règles de cycle de vie existants sont strictement préservés.

### 5.5 Médiathèque

**Direction future (refonte visuelle de Phase 1) :**
- Grille d'aperçus (images affichées à taille lisible)
- Informations fichier secondaires (nom, dimensions, poids) en dessous ou au survol
- Alternance grille / liste envisageable dans une phase ultérieure

**Conserver avec le backend actuel :**
- Upload via formulaire existant
- Pagination
- Prévisualisation via `admin_media_preview`

Voir §9 pour les fonctionnalités différées.

---

## 6. Design tokens à utiliser

### 6.1 Source des tokens

Les tokens sont définis dans `apps/web/app/assets/css/tokens.css`. Pour l'administration, les valeurs doivent être **copiées ou redéclarées** dans `admin.css` sous les mêmes noms. Ne pas référencer le fichier Nuxt directement (origines différentes, pipelines différents).

### 6.2 Palette

| Token | Valeur | Usage admin |
|---|---|---|
| `--color-sand` | `#efebe2` | Background du workspace (zone de travail principale) |
| `--color-cream` | `#f4f1ea` | Background secondaire, surfaces neutres |
| `--color-cream-elevated` | `#f8f5ef` | Cards, surfaces élevées |
| `--color-ink` | `#16191c` | Texte principal |
| `--color-navy` | `#0e1622` | Sidebar background |
| `--color-navy-deep` | `#0a121c` | Sidebar header, zones très sombres |
| `--color-navy-elevated` | `#141e2c` | Item actif sidebar, hover sidebar |
| `--color-petrol` | `#0c5b57` | Actions principales, accent de navigation active |
| `--color-petrol-hover` | `#0a4a47` | Hover bouton principal |
| `--color-petrol-active` | `#083f3c` | Active bouton principal |
| `--color-devzair-blue` | `#2e86d9` | Focus ring |
| `--color-status-error` | `#b23a2e` | Erreurs, actions dangereuses |
| `--color-status-success` | `#2a6e4a` | Succès, publication |

**Tokens sémantiques à déclarer dans `admin.css` :**

```css
:root {
  /* Workspace */
  --admin-bg-workspace: var(--color-sand);
  --admin-bg-surface: var(--color-cream-elevated);
  --admin-bg-surface-raised: #ffffff;

  /* Sidebar */
  --admin-sidebar-bg: var(--color-navy);
  --admin-sidebar-bg-item-hover: var(--color-navy-elevated);
  --admin-sidebar-bg-item-active: var(--color-navy-elevated);
  --admin-sidebar-accent: var(--color-petrol);
  --admin-sidebar-text: rgba(248, 245, 239, 0.75);    /* cream à 75% */
  --admin-sidebar-text-active: var(--color-cream-elevated);

  /* Texte */
  --admin-text-primary: var(--color-ink);
  --admin-text-secondary: rgba(22, 25, 28, 0.6);
  --admin-text-muted: rgba(22, 25, 28, 0.4);

  /* Actions */
  --admin-action-primary: var(--color-petrol);
  --admin-action-primary-hover: var(--color-petrol-hover);
  --admin-action-primary-active: var(--color-petrol-active);
  --admin-action-danger: var(--color-status-error);
  --admin-action-success: var(--color-status-success);

  /* Focus */
  --admin-focus-ring: var(--color-devzair-blue);

  /* Bordures */
  --admin-border: rgba(22, 25, 28, 0.12);
  --admin-border-strong: rgba(22, 25, 28, 0.22);
}
```

### 6.3 Espacement

Échelle 4 pt héritée des tokens Devzair :

```css
--space-1: 0.25rem;   /* 4px */
--space-2: 0.5rem;    /* 8px */
--space-3: 0.75rem;   /* 12px */
--space-4: 1rem;      /* 16px */
--space-5: 1.25rem;   /* 20px */
--space-6: 1.5rem;    /* 24px */
--space-8: 2rem;      /* 32px */
--space-10: 2.5rem;   /* 40px */
--space-12: 3rem;     /* 48px */
```

### 6.4 Typographie et polices

**Situation actuelle :** Les polices Devzair (Schibsted Grotesk, Hanken Grotesk, Space Mono) sont gérées par `@nuxt/fonts` dans `apps/web`. Les fichiers sont téléchargés au build Nuxt et servis depuis l'origine Nuxt. Aucun fichier de police n'est présent dans `apps/api/public/`.

**Stratégie pour la refonte (KISS) :**

| Phase | Typographie | Justification |
|---|---|---|
| Phase 1 | Fallbacks système enrichis | Aucune dépendance, CSP `font-src 'self'` immédiatement compatible |
| Phase future | Polices Devzair copiées dans `apps/api/public/admin/fonts/` + `@font-face` dans `admin.css` | Requiert une tâche dédiée d'extraction des fichiers woff2 |

**Fallbacks système recommandés pour Phase 1 :**

```css
--admin-font-heading: "Schibsted Grotesk", "Inter", ui-sans-serif, system-ui,
  -apple-system, "Segoe UI", sans-serif;
--admin-font-body: "Hanken Grotesk", "Inter", ui-sans-serif, system-ui,
  -apple-system, "Segoe UI", sans-serif;
--admin-font-mono: "Space Mono", ui-monospace, "SFMono-Regular", Menlo,
  Consolas, monospace;
```

Les noms de famille sont déclarés même si les fichiers ne sont pas encore présents — cela permettra un upgrade transparent une fois les fichiers copiés, sans modifier les déclarations CSS.

### 6.5 Radius et ombres

```css
--admin-radius-sm: 6px;
--admin-radius-md: 10px;
--admin-radius-lg: 14px;

/* Ombres légères — sans excès */
--admin-shadow-sm: 0 1px 3px rgba(14, 22, 34, 0.10);
--admin-shadow-md: 0 2px 8px rgba(14, 22, 34, 0.12);
```

**À éviter :** grands arrondis (`border-radius > 16px`), ombres portées excessives (`box-shadow` multicouches), dégradés décoratifs complexes.

### 6.6 Statuts des articles (à aligner avec les tokens)

```css
/* Draft */
--admin-status-draft-bg: rgba(14, 22, 34, 0.06);
--admin-status-draft-border: rgba(14, 22, 34, 0.18);
--admin-status-draft-text: var(--color-ink);

/* Published */
--admin-status-published-bg: rgba(42, 110, 74, 0.10);
--admin-status-published-border: var(--color-status-success);
--admin-status-published-text: var(--color-status-success);

/* Archived */
--admin-status-archived-bg: rgba(14, 22, 34, 0.04);
--admin-status-archived-border: rgba(14, 22, 34, 0.14);
--admin-status-archived-text: var(--admin-text-muted);
```

---

## 7. Comportement responsive

### 7.1 Breakpoints à tester

| Largeur | Cible | Comportement sidebar |
|---|---|---|
| 320 px | Petit mobile | Sidebar masquée, topbar minimal avec menu burger |
| 390 px | Mobile standard | Identique |
| 768 px | Tablette | Sidebar masquée par défaut, déclenchable (overlay ou panneau coulissant sans JS) |
| 1024 px | Ordinateur portable | Sidebar persistante (240 px) |
| 1440 px | Desktop large | Sidebar persistante (256–264 px) |

### 7.2 Navigation mobile/tablette

**Approche R1 — `<details>/<summary>` natif :**

```html
<details class="admin-mobile-menu">
  <summary class="admin-mobile-menu__toggle">Menu</summary>
  <div class="admin-mobile-menu__panel">
    <ul>…liens de navigation…</ul>
    <a href="/">Voir le site ↗</a>
    <form method="post" action="/admin/logout">…CSRF…<button>Déconnexion</button></form>
  </div>
</details>
```

Avantages :
- Aucun JavaScript, compatible `script-src 'none'`
- Clavier natif (Espace/Entrée sur `<summary>` bascule l'état ouvert/fermé)
- État open/closed exposé nativement au navigateur et aux AT
- Aucun piège de focus simulé (pas de drawer/modal)
- Contient les mêmes destinations que la sidebar desktop

Limites documentées : l'élément `<details>` n'est pas un drawer modal ; il ne piège pas le focus. C'est intentionnel pour R1. Une amélioration JS (drawer avec `aria-expanded`, `aria-controls`, piège de focus réel) pourra être étudiée si un besoin fonctionnel la justifie.

**À ne pas utiliser :** checkbox hack, `:target`, faux drawer nécessitant un focus trap.

**Sur ≥ 768 px** : la sidebar persistante s'affiche à partir de 1024 px. Entre 768 px et 1023 px, la topbar avec `<details>` reste active.

### 7.3 Éditeur d'article responsive

| Largeur | Layout |
|---|---|
| < 768 px | Colonne unique. Bloc Publication **en premier** (avant `textarea` Markdown) |
| 768–1023 px | Colonne unique. Même ordre que mobile |
| ≥ 1024 px | Deux colonnes (`minmax(0, 1fr) 340px`), gap 24–32 px |

Aucun champ ne doit disparaître ou devenir inaccessible en mobile.

### 7.4 Liste des articles responsive

| Largeur | Comportement |
|---|---|
| < 640 px | Cartes empilées (chaque article = une carte) plutôt qu'un tableau |
| ≥ 640 px | Tableau avec colonnes adaptées |

### 7.5 Médiathèque responsive

| Largeur | Comportement |
|---|---|
| < 480 px | Grille 2 colonnes |
| 480–767 px | Grille 3 colonnes |
| 768–1023 px | Grille 4 colonnes |
| ≥ 1024 px | Grille 5–6 colonnes |

### 7.6 Accessibilité — règles applicables à la refonte

- Navigation clavier complète (Tab, Shift+Tab, Entrée, Espace)
- Focus visible non masqué : `outline: 2px solid var(--admin-focus-ring); outline-offset: 2px`
- Labels de formulaires réels (`<label for>` associé à chaque `<input>/<select>/<textarea>`)
- Messages d'erreur reliés via `aria-describedby`
- États hover/focus/disabled/active distincts sur tous les éléments interactifs
- Contraste AA minimum : texte principal sur fond workspace, texte sidebar sur fond navy
- Cibles interactives principales : Devzair vise **~40–44 px** de hauteur comme standard UX interne (confort d'utilisation) — au-delà du minimum réglementaire WCAG 2.2 AA §2.5.5 (Enhanced, niveau AAA)
- WCAG 2.2 AA §2.5.8 (Target Size Minimum) fixe le seuil réglementaire à **24 × 24 CSS px** sous certaines conditions d'espacement ; les boutons secondaires doivent respecter ce minimum et conserver `gap ≥ 8px` entre cibles adjacentes
- Tableaux accessibles sur petits écrans (éviter `overflow-x: auto` sans `role="region"` + `aria-label`)
- Zoom navigateur 200 % : vérifier que le texte et les formulaires restent utilisables

---

## 8. Écrans concernés

### 8.1 Login (`/admin/login`)

**Changements prévus :** Centrage vertical du formulaire, application des tokens Devzair (fond cream-elevated, typographie, bouton petrol), logo ou marque Devzair.  
**À préserver :** Formulaire, CSRF `authenticate`, message d'erreur générique.

### 8.2 Dashboard (`/admin`)

**R1 — enveloppe uniquement :** Intégrer le dashboard actuel dans le nouveau shell. Le contenu fonctionnel actuel (profil, dernière connexion, logout) est conservé. Aucun compteur, aucune requête Doctrine supplémentaire.

**R2 — contenu éditorial :** Remplacer le contenu par les données réelles (compteurs d'articles par statut, compteur médias, activité récente, actions rapides). Nécessite un nouveau handler `GetAdminDashboardSummaryHandler`.

### 8.3 Liste des articles (`/admin/articles`)

**Changements prévus :** Voir §5.3.  
**À préserver :** Pagination, filtres par statut via query param, toutes les actions POST avec CSRF.

### 8.4 Création d'article (`/admin/articles/new`)

**Changements prévus :** Application des tokens Devzair.  
**À préserver :** Tous les champs, CSRF `article_create`, validations serveur.

### 8.5 Édition d'article (`/admin/articles/{id}/edit`)

**Changements prévus :** Layout deux colonnes (voir §5.4).  
**À préserver :** Tous les champs, CSRF `article_edit_{id}`, validations, messages d'erreur, section cycle de vie, toutes les actions POST.

### 8.6 Prévisualisation (`/admin/articles/{id}/preview`)

**Changements prévus :** Intégration dans le shell avec sidebar, application des tokens.  
**À préserver :** Rendu Markdown via `CommonMarkArticleRenderer`, bandeau "Prévisualisation privée", actions cycle de vie.

### 8.7 Médiathèque (`/admin/media`)

**Changements prévus :** Grille d'aperçus visuels (voir §5.5).  
**À préserver :** Pagination, upload, toutes les routes existantes.

### 8.8 Upload média (`/admin/media/new`)

**Changements prévus :** Application des tokens Devzair.  
**À préserver :** Constraints fichier (8 MiB, JPEG/PNG/WebP, dimensions), CSRF `media_upload`.

### 8.9 Chooser image principale (`/admin/articles/{id}/image`)

**Changements prévus :** Grille visuelle (même approche que la médiathèque), meilleure affordance pour la sélection.  
**À préserver :** CSRF `article_hero_image_set_{id}`, field alt-text, pagination.

### 8.10 Pages rate-limited

**Changements prévus :** Application des tokens Devzair, intégration dans le shell.  
**À préserver :** Message d'attente, lien retour.

---

## 9. Fonctionnalités explicitement différées

### 9.1 Avec le backend actuel (refonte visuelle uniquement)

Ces éléments ne nécessitent pas de nouveau backend mais ne font pas partie de la Phase 1 de refonte :

- Bascule grille/liste dans la médiathèque (fonctionnel mais à prioriser)
- Meilleure fiche détail d'un media (nécessite un nouvel écran ou une modale)
- Prévisualisation publique d'article dans un onglet séparé (amélioration UX mineure)

### 9.2 Nécessite une nouvelle fonctionnalité backend

Ces éléments doivent être explicitement planifiés et ne seront pas inclus dans la refonte purement visuelle :

| Fonctionnalité | Raison du report |
|---|---|
| Drag & drop upload de médias | Require JS + éventuellement un endpoint XHR |
| Recherche dynamique articles/médias | Nécessite un endpoint de recherche côté Symfony |
| Filtres avancés (par auteur, par expertise, par date) | Extension du handler de liste + UI |
| Sélection et suppression multiple | Nécessite de nouvelles routes POST groupées |
| Copie d'URL de média | Nécessite JS (clipboard API) |
| Chooser image inline dans l'éditeur (sans page dédiée) | Requiert soit une intégration JS/AJAX, soit une refonte du flux |
| Gestion multi-utilisateurs (section "Utilisateurs" dans la nav) | Nécessite migration colonne `roles`, routes CRUD admin, refonte `access_control` |
| Statistiques de contenu (vues, clics) | Aucun tracking analytics côté admin à ce stade |
| Paramètres système | Aucune fonctionnalité correspondante existante |
| MFA (TOTP) | Différé au-delà de Phase 8C4 (cf. ADR-012) |
| Reset de mot de passe via UI | Reste CLI-only tant que l'admin n'est pas multi-utilisateurs |

---

## 10. Contrainte future n8n

### 10.1 Contexte

Une automatisation de création d'articles via n8n sera ajoutée dans une phase future. Cette automatisation **ne doit pas** utiliser le compte administrateur humain ni simuler une connexion au formulaire `/admin`.

### 10.2 Principes architecturaux à prévoir dès maintenant

| Principe | Détail |
|---|---|
| Identité distincte | Compte de service dédié (ex. `n8n-content`) distinct des comptes humains |
| Credentials révocables | API key ou JWT signé, pas de session cookie, rotation sans affecter les comptes humains |
| Permissions minimales | Uniquement créer/mettre à jour des brouillons, uploader des médias — pas publier, pas gérer les utilisateurs |
| Audit de l'acteur technique | Les logs doivent distinguer les actions n8n des actions humaines (champ `actor_type: service` vs `human`) |
| Rate limiting dédié | Limiteur indépendant du limiteur admin humain |
| Aucun secret en Git | Clés API en variables d'environnement ou secret manager |
| Distinction auteur éditorial / acteur technique | L'auteur public affiché dans l'article (`authorName`) est l'auteur éditorial humain, pas le compte n8n |
| Brouillons par défaut | L'automatisation crée des brouillons validés par un humain avant publication |
| Gestion des retries/idempotence | Un identifiant de corrélation n8n doit éviter les doublons sur retry |

### 10.3 Options architecturales identifiées (non codées)

**Option A — API Token Symfony (recommandée pour la phase proche)**
- Créer un système d'authentification par token `Bearer` (`Authorization: Bearer <api-key>`)
- Nouveau firewall Symfony `api_internal` sur `/api/editorial/**` distinct de `/admin`
- `AdminUser` étendu avec champ `is_service_account: bool` ou nouvelle entité `ServiceAccount` dédiée
- Token haché en base (jamais en clair)
- Avantage : intégration native Symfony, révocable en DB, auditable
- Inconvénient : nécessite une migration et un nouveau firewall

**Option B — JWT signé (préférable si scaling futur)**
- Token JWT signé avec clé privée (Lexik JWT Bundle ou implémentation maison)
- Claims : `sub` (service account ID), `scope: editorial_write`, `exp`
- Stateless — pas de session
- Avantage : idéal pour horizontal scaling futur
- Inconvénient : révocation plus complexe (liste de révocation ou TTL court)

**Option C — Compte admin de service avec rôle restreint (solution minimale)**
- Entité `AdminUser` existante avec `is_active: bool`, protégé par `ADMIN_SERVICE_ACCOUNT_ID` env
- Rôle `ROLE_EDITORIAL_SERVICE` distinct de `ROLE_ADMIN`
- Avantage : zéro migration (si rôles multiples étaient déjà en place)
- Inconvénient : `ROLE_ADMIN` donne trop de droits ; nécessite refonte du contrôle d'accès

**Recommandation :** Option A pour la phase proche (implémentation minimale, cohérente avec Symfony), Option B à considérer si l'API est exposée à d'autres clients.

---

## 11. Stratégie de tests

### 11.1 Tests existants à préserver

| Suite | Localisation | Comportement attendu |
|---|---|---|
| PHPUnit admin | `apps/api/tests/Admin/` | Vert avant et après chaque phase de refonte |
| PHPUnit éditorial | `apps/api/tests/Editorial/` | Vert (non touché par la refonte CSS/Twig) |
| PHPUnit sécurité | `AdminSecurityHeadersTest` | Vérifie CSP, headers — DOIT rester vert |
| Playwright `admin.spec.ts` | `apps/web/test/e2e/` | Auth, accessibilité Axe WCAG 2.2 AA login + dashboard |
| Playwright `admin-editorial.spec.ts` | `apps/web/test/e2e/` | Création, édition, publication, archivage, restauration |
| Playwright `admin-preview.spec.ts` | `apps/web/test/e2e/` | Prévisualisation authentifiée |

### 11.2 Stratégie pour la refonte

**Avant chaque phase :**
- Lancer `php bin/phpunit --testsuite admin` → vert
- Lancer `npx playwright test admin` → vert
- Documenter le résultat de référence

**Après chaque phase :**
- Toutes les suites existantes doivent rester vertes
- Axe WCAG 2.2 AA ne doit pas régresser
- Les sélecteurs Playwright qui cassent à cause des changements HTML doivent être mis à jour **avant** de valider la phase

**Pour la sidebar et le nouveau shell :**
- Ajouter des tests Playwright pour : navigation clavier entre items, item actif correctement identifié, sidebar visible/masquée selon le breakpoint

**Pour le dashboard refonte :**
- Ajouter des assertions SSR sur la présence des compteurs
- Vérifier que les compteurs affichent des valeurs cohérentes avec la DB de test

**Pour l'éditeur deux colonnes :**
- Ajouter des assertions sur la présence du bloc Publication en premier sur mobile
- Vérifier que tous les champs sont toujours présents et soumettables

### 11.3 Recette manuelle obligatoire

Par écran et par phase :
- Navigation clavier complète (Tab, Shift+Tab, Entrée)
- Zoom 200 % (aucun contenu tronqué, aucun bouton inaccessible)
- Breakpoints 320 / 390 / 768 / 1024 / 1440 px
- Formulaires : soumettre avec données invalides (messages d'erreur visibles et corrects)
- Cycle de vie article complet : créer brouillon → publier → archiver → restaurer
- Upload média → associer à un article → vérifier l'aperçu

---

## 12. Plan d'implémentation par phases

> Chaque phase doit laisser l'administration **entièrement fonctionnelle** et les tests verts à la fin.

### Phase R0 — Préparation (cette phase, audit + spécification)

- [x] Audit de l'existant
- [x] Rédaction du document de conception
- [ ] Validation du document par l'équipe

### Phase R1 — Shell, tokens et identité visuelle de base

**Périmètre :**
- Nouveau `_layout.html.twig` avec sidebar persistante (desktop) et topbar mobile
- Extraction des tokens Devzair dans `admin.css` (`:root {}` avec toutes les variables)
- Remplacement de la palette générique par les tokens Devzair
- Fallbacks typographiques enrichis (noms de famille Devzair, fichiers non encore copiés)
- Login redesigné (centré, card, fond workspace)
- Dashboard existant intégré dans le nouveau shell (profil simplifié en bas de sidebar)
- Navigation active fonctionnelle

**Fichiers Twig modifiés :**
- `templates/admin/_layout.html.twig` (structure complète)
- `templates/admin/login.html.twig`
- `templates/admin/dashboard.html.twig` (structure uniquement, contenu §8.2 en Phase R2)

**Fichiers CSS :**
- `apps/api/public/admin/assets/admin.css` (ajout tokens, nouveaux composants shell, mise à jour des primitives partagées ; styles des écrans R3–R6 conservés en section « legacy »)

**Fichiers non modifiés :** Contrôleurs, handlers, entités, routes, sécurité, tests PHPUnit.

**Tests à maintenir verts :** Tous.  
**Tests à ajouter/adapter :** Assertions Playwright sur la présence de la sidebar, item actif, navigation.

### Phase R2 — Dashboard éditorial

**Périmètre :**
- Nouveau handler de lecture `GetAdminDashboardSummaryHandler` (compteurs articles par statut, compteur médias, liste des 5 derniers articles modifiés)
- Nouveau template de dashboard avec les données réelles
- Actions rapides

**Fichiers Symfony modifiés/créés :**
- `src/Admin/Application/Query/AdminDashboardSummary.php` (DTO résultat)
- `src/Admin/Application/Query/GetAdminDashboardSummaryHandler.php` (agrège les ports existants)
- `templates/admin/dashboard.html.twig`
- `src/Admin/Presentation/Http/AdminDashboardController.php` (appel du nouveau handler)

**Décision d'implémentation :** Le handler réutilise les repositories de lecture existants (`AdminArticleReadRepositoryInterface` et `MediaAssetRepositoryInterface`) — aucune requête Doctrine dédiée n'a été ajoutée.

**Tests ajoutés :** Handler test PHPUnit (7 cas), describe Playwright R2 (4 tests), sélecteurs CSRF scopés corrigés dans 3 fichiers de test.

### Phase R3 — Liste des articles

**Périmètre :**
- Refonte du template `articles/list.html.twig`
- Titre dominant, slug secondaire sous le titre, badges statuts Devzair
- Regroupement des actions secondaires

**Fichiers Twig modifiés :**
- `templates/admin/articles/list.html.twig`
- `templates/admin/articles/_row_actions.html.twig`

**Fichiers non modifiés :** Contrôleurs, handlers, routes.

**Tests à adapter :** Sélecteurs Playwright si cassés.

### Phase R4 — Éditeur d'article deux colonnes

**Périmètre :**
- Refonte du template `articles/edit.html.twig` en deux colonnes
- Bloc Publication prominent dans la sidebar
- Réorganisation de `_form.html.twig`
- Responsive : colonne unique mobile avec Publication en premier

**Fichiers Twig modifiés :**
- `templates/admin/articles/edit.html.twig`
- `templates/admin/articles/_form.html.twig`

**Attention :** CSRF tokens, validations serveur, messages d'erreur, et toutes les actions POST doivent être strictement préservés.

**Tests :** Vérifier que tous les scénarios de `admin-editorial.spec.ts` restent verts. Ajouter test responsive (Publication visible sans défilement sur 390 px).

### Phase R5 — Médiathèque visuelle

**Périmètre :**
- Grille d'aperçus dans `media/list.html.twig`
- Taille de thumb significative (minimum 160×160 px)
- Informations fichier sous chaque aperçu
- Responsive (§7.5)

**Fichiers Twig modifiés :**
- `templates/admin/media/list.html.twig`

**Fichiers non modifiés :** Contrôleurs, routes, upload.

**Tests :** Adapter sélecteurs Playwright si cassés.

### Phase R6 — Création et prévisualisation

**Périmètre :**
- Refonte `articles/new.html.twig` (formulaire de création dans le nouveau shell)
- Refonte `articles/preview.html.twig`
- Refonte `articles/hero_image.html.twig` (grille visuelle comme la médiathèque)
- Refonte des pages `rate_limited.html.twig`

**Tests :** Maintenir `admin-preview.spec.ts` vert.

### Phase R7 — Accessibilité, audit final et polish

**Périmètre :**
- Audit Axe complet sur tous les écrans
- Recette manuelle (keyboard, zoom 200 %, breakpoints)
- Corrections WCAG restantes
- Vérification contraste sur tous les éléments nouveaux
- Documentation des décisions d'accessibilité dans `docs/02-DESIGN-ACCESSIBILITY.md`

---

## 13. Critères d'acceptation par phase

### Phase R1 — Shell + tokens

- [x] La sidebar est visible et persistante ≥ 1024 px
- [x] La sidebar utilise `--color-navy` / `--color-navy-deep`
- [x] L'item de navigation actif est clairement identifié
- [x] Le login est redesigné avec les tokens Devzair
- [x] Le fond de la zone de travail est `--color-sand`
- [x] Les boutons principaux sont en `--color-petrol`
- [x] Le focus ring est `--color-devzair-blue`
- [x] Aucun test PHPUnit ne régresse (569/569)
- [x] `admin.spec.ts`, `admin-editorial.spec.ts` et `admin-preview.spec.ts` sont verts (16/16)
- [x] Axe WCAG 2.2 AA : aucune violation `serious`/`critical` sur login et dashboard

### Phase R2 — Dashboard

- [x] Le dashboard affiche le nombre d'articles publiés, de brouillons et d'archives
- [x] Le dashboard affiche le nombre de médias
- [x] La liste d'activité récente est présente (5 articles, `updatedAt DESC`)
- [x] Les compteurs proviennent de données réelles (pas hardcodés)
- [x] La fiche profil admin n'est plus le contenu principal
- [x] Les tests handler dashboard sont verts (PHPUnit 7/7)
- [ ] Les compteurs sont présents dans le HTML SSR (Playwright — tests écrits, non exécutés sur serveur live)

### Phase R3 — Liste articles

- [x] Le titre est l'élément dominant de chaque ligne
- [x] Le slug est affiché sous le titre (petit, monospace, `<code>`)
- [x] Les badges de statut utilisent les tokens Devzair
- [x] Les actions secondaires regroupées dans `<details>/<summary>` (aucun JS)
- [x] Archiver : danger subtil (bordure rouge, fond transparent)
- [x] Le CTA "+ Nouveau brouillon" visible dans l'en-tête de page
- [x] Filtres par statut : `<nav aria-label>` + liens `aria-current="page"` (no tablist)
- [x] Format date `d/m/Y · H:i`
- [x] Colonne Slug supprimée — fusionnée dans la colonne Article
- [x] Responsive mobile : `tr → blocks` via CSS + `data-label` (Option A)
- [x] État vide avec CTA "+ Nouveau brouillon"
- [x] `admin-editorial.spec.ts` vert (5/5) — correctif scoping `aside` sidebar
- [x] `admin.spec.ts` vert (8/8) — correctifs `exact: true` KPI + scoping KPI section
- [x] PHPUnit suite complète verte (576/576)

### Phase R4 — Éditeur deux colonnes

- [x] Sur ≥ 1024 px : layout deux colonnes (`grid-template-columns: minmax(0, 1fr) 340px`)
- [x] La colonne latérale est sticky sur desktop
- [x] Le bloc Publication est le premier élément de la sidebar
- [x] Sur < 768 px : layout colonne unique avec Publication EN PREMIER
- [x] Tous les champs existent et sont soumettables
- [x] Les validations serveur fonctionnent (messages d'erreur visibles)
- [x] Les CSRF tokens sont présents dans tous les formulaires
- [x] Aucun formulaire imbriqué (`//form//form` count = 0) — PHPUnit `testNoNestedFormsOnDraftEditPage`
- [x] `admin-editorial.spec.ts` vert (9/9) — correctifs sélecteurs `code.admin-editor-slug` / `code.admin-article-slug` / `p.admin-article-title`
- [x] `admin.spec.ts` vert (8/8)
- [x] PHPUnit suite complète verte (577/577)

### Phase R5 — Médiathèque

- [x] Les médias sont affichés en grille avec aperçu (aspect-ratio 4/3, `object-fit: cover`)
- [x] Les informations fichier sont lisibles (nom, dimensions, format humain, poids Kio/Mio, date)
- [x] La grille est responsive : 2 cols (320–479 px), 3 cols (480–767 px), 4 cols (768–1279 px), 5 cols (1280–1439 px), 6 cols (≥ 1440 px)
- [x] L'upload fonctionne toujours (CTA + route `admin_media_upload` inchangés)
- [x] MIME affiché en format humain (JPEG, PNG, WebP) via map Twig locale
- [x] Poids : Kio sous 1 Mio, Mio au-delà
- [x] `loading="lazy"` + `width`/`height` natifs préservés
- [x] Lien preview aria-label `Prévisualiser « filename »`
- [x] État vide modernisé avec CTA
- [x] Pagination conservée
- [x] Tri backend `createdAt DESC` conservé
- [x] `admin-media.spec.ts` vert (9/9) — fix sélecteur Médias + grille + breakpoints + Axe
- [x] PHPUnit suite complète verte (577/577)
- [x] Note R4 : duplication `edit.html.twig` / `_form.html.twig` à résoudre en R6

### Phase R6 — Création et prévisualisation

- [ ] Tous les écrans sont dans le nouveau shell
- [ ] `admin-preview.spec.ts` reste vert
- [ ] Les pages rate-limited sont dans le nouveau shell

### Phase R7 — Accessibilité finale

- [ ] Axe WCAG 2.2 AA : 0 violation `serious`/`critical` sur tous les écrans
- [ ] Navigation clavier complète vérifiée manuellement
- [ ] Zoom 200 % fonctionnel sur tous les écrans
- [ ] Breakpoints 320/390/768/1024/1440 vérifiés
- [ ] Contrastes vérifiés (sidebar navy + texte cream, workspace sand + texte ink)
- [ ] Cibles interactives principales ≥ 44 × 44 px
- [ ] Boutons secondaires ≥ 24 × 24 px avec gap ≥ 8 px

---

## 14. Checklist d'avancement

### Phase R0 — Audit et spécification

- [x] Audit templates Twig admin
- [x] Audit CSS admin actuel
- [x] Audit contrôleurs et routes
- [x] Audit entités et domaine
- [x] Audit sécurité (CSRF, CSP, rate limiting, audit logging)
- [x] Audit tests existants
- [x] Audit design tokens frontend public
- [x] Identification problèmes UI/UX
- [x] Identification divergences documentation/code
- [x] Rédaction spécification complète
- [x] 5 corrections intégrées avant implémentation R1
- [x] Validation du document par l'équipe

### Phase R1 — Shell + tokens
- [x] Nouveau `_layout.html.twig` avec sidebar
- [x] `admin.css` refonte complète avec tokens Devzair
- [x] `login.html.twig` redesigné
- [x] `dashboard.html.twig` dans le nouveau shell
- [x] Navigation active fonctionnelle
- [x] Responsive sidebar mobile (< 1024 px via topbar `<details>`)
- [x] Tests PHPUnit verts (569/569)
- [x] Tests Playwright verts (16/16)
- [x] Axe WCAG 2.2 AA OK (login + dashboard + preview + editorial)

### Phase R2 — Dashboard éditorial
- [x] Handler `GetAdminDashboardSummaryHandler` + DTO `AdminDashboardSummary`
- [x] Réutilisation des repositories de lecture existants (pas de requête Doctrine dédiée)
- [x] `dashboard.html.twig` avec données réelles (KPI × 4, contenus récents × 5, empty state)
- [x] `AdminDashboardController` mis à jour
- [x] Tests PHPUnit handler (7/7 verts)
- [x] Tests Playwright dashboard (4 tests — `admin.spec.ts`)
- [x] PHPUnit suite complète verte (576/576) — baseline R2.1
- [x] CTA "Ajouter un média" pointe vers `admin_media_upload`
- [x] Statuts affichés en français (Brouillon / Publié / Archivé)
- [x] Format date neutre français (`d/m/Y · H:i`)
- [x] Correctif sélecteurs CSRF dans `AdminArticleCreateControllerTest`, `AdminArticleAuditLoggingTest`, `AdminMediaUploadControllerTest`

### Phase R3 — Liste articles
- [x] `articles/list.html.twig` refonte (header, nav filtres, table 4 col, empty state)
- [x] `articles/_row_actions.html.twig` refonte (`<details>/<summary>`, danger-subtle)
- [x] CSS R3 ajouté (`admin-article-filters`, `admin-actions-*`, `button-danger-subtle`, badges tokens, responsive mobile)
- [x] PHPUnit suite verte (576/576)
- [x] Playwright verts — `admin-editorial.spec.ts` (5/5), `admin.spec.ts` (8/8)

### Phase R4 — Éditeur deux colonnes
- [x] `articles/edit.html.twig` layout deux colonnes (sidebar DOM-first, `form=` attribute, locked state)
- [x] CSS R4 ajouté (`.admin-editor-grid`, `.admin-editor-sidebar`, `.admin-editor-main`, `.admin-publication-card`, `.admin-editor-markdown`, `.admin-editor-locked`, etc.)
- [x] Aucun formulaire imbriqué (`testNoNestedFormsOnDraftEditPage` PHPUnit)
- [x] Responsive mobile : Publication avant Markdown (ordre DOM + CSS `order`)
- [x] PHPUnit suite verte (577/577)
- [x] Playwright verts — `admin-editorial.spec.ts` (9/9), `admin.spec.ts` (8/8)
- [x] Correctif R3 thead : `display: none` → visually-hidden clip pattern

### Phase R5 — Médiathèque
- [x] `media/list.html.twig` refonte complète (grille `ul[role="list"] > li`, cards, metadata)
- [x] CSS R5 ajouté (`.admin-media-page`, `.admin-media-grid`, `.admin-media-card`, état vide)
- [x] `AdminMediaListControllerTest` adapté (XPath table → XPath word-boundary card)
- [x] `admin-media.spec.ts` refondu (9/9 tests : grille, metadata, breakpoints, focus, Axe)
- [x] Défaut pré-existant corrigé (Médias link strict mode → scope `aside`)

### Phase R6 — Création et prévisualisation (commit `0117641`)
- [x] `articles/_fields_main.html.twig` + `_fields_metadata.html.twig` — fragments mutualisés (suppression duplication `_form.html.twig`)
- [x] `articles/new.html.twig` reécrit en grille deux-colonnes (`admin-editor-grid`, main premier dans le DOM)
- [x] `articles/preview.html.twig` redesigné — layout lecture deux-colonnes, dates `d/m/Y · H:i`, labels expertises humanisés
- [x] `articles/hero_image.html.twig` — en-tête `admin-page-header` harmonisé
- [x] `articles/rate_limited.html.twig` + `media/rate_limited.html.twig` — `admin-page-header` harmonisé
- [x] `media/new.html.twig` — en-tête `admin-page-header` harmonisé
- [x] CSS R6 : `.admin-preview-grid`, `:checked` hero chooser, pages utilitaires
- [x] Fix dette R4 : `admin-preview.spec.ts` sélecteur `'Prévisualiser'` `exact:true`
- [x] PHPUnit : +1 `testNoNestedFormsOnCreatePage` (596/596)
- [x] Playwright 33/33

### Phase R7 — Audit final et polish (2026-08-29)
- [x] PASS A — Audit CSS : 4 variables `--admin-*` non définies identifiées (R5 dette)
- [x] PASS B — Variables `--admin-border → --border-default`, `--admin-radius-md → --radius-md`, `--admin-focus-ring → --color-devzair-blue`
- [x] Suppression hover `box-shadow: var(--admin-shadow-sm)` → `border-color: var(--color-petrol)` sobre
- [x] Transition `prefers-reduced-motion` ajoutée sur `.admin-media-card`
- [x] CSS legacy supprimé : `.page-header`, `.page-meta`, `.page-eyebrow`, `.preview-title`, `.preview-actions`, `.preview-content__intro`, `.admin-media-thumb`
- [x] Wording "uploads" → "téléversements" dans `media/rate_limited.html.twig`
- [x] PHPUnit final : 596/596
- [x] Playwright final : 33/33 (admin.spec.ts 8/8, admin-editorial.spec.ts 9/9, admin-media.spec.ts 9/9, admin-preview.spec.ts 7/7)
- [x] `|raw` unique dans le codebase admin confirmé : `preview.html.twig` sur `contentHtml`
- [x] Aucune variable CSS non définie résiduelle

---

## Annexe — Divergences documentation / code identifiées

| Document | Déclaration obsolète | Réalité du code |
|---|---|---|
| `AGENTS.md` | "La phase active est **Phase 1 — Dépôt et Docker Nuxt**" | Le projet est à la **Phase 9** (9A terminée, 9B à venir). Les phases 1–8C4 sont toutes terminées. |
| `AGENTS.md` | "Backend futur : Symfony 7.4 LTS" | Symfony 7.4 est **entièrement implémenté** depuis la Phase 6A. L'administration est opérationnelle depuis la Phase 8C. |
| `AGENTS.md` | "Base future : PostgreSQL" | PostgreSQL 17 est **en production** depuis la Phase 8A. |
| `AGENTS.md` | "Blog : données Symfony rendues par Nuxt sous `/ressources/**`" | Cette architecture est **déjà implémentée** (Phase 8B2). |

**Corrections minimales recommandées :**

Dans `AGENTS.md`, section "État actuel" :

```
La phase active est **Phase 9 — Média éditorial et intégration du blog dans Nuxt**.

Phase 9A (fondation média éditoriale) est terminée.
La prochaine tâche est la Phase 9B (rattachement médias à l'article + exposition publique).

L'administration Symfony (Phase 8C) est entièrement fonctionnelle.
Symfony 7.4, PostgreSQL 17 et la stack Docker complète sont opérationnels.
```

**Note :** La refonte UI/UX de l'administration (Phases R0–R7) est une nouvelle initiative en parallèle du reste de la roadmap.

---

## Rapport d'audit final R7 — 2026-08-29

### Git
- **SHA baseline R6 :** `0117641`
- **Branche :** `main`
- **Statut :** 1 commit R7 (`fix(admin): audit CSS, polish R7`) en avance sur `origin/main`

### Baselines
| Suite | Entrée R7 | Sortie R7 |
|---|---|---|
| PHPUnit | 596/596 | **596/596** |
| admin.spec.ts | 8/8 | **8/8** |
| admin-editorial.spec.ts | 9/9 | **9/9** |
| admin-media.spec.ts | 9/9 | **9/9** |
| admin-preview.spec.ts | 7/7 | **7/7** |
| **Total Playwright** | **33/33** | **33/33** |

### Problèmes identifiés (PASS A)

| Sévérité | Problème | Fichier | Correction |
|---|---|---|---|
| CRITIQUE | `--admin-border` non définie | admin.css l.1989, 2055 | → `--border-default` |
| CRITIQUE | `--admin-radius-md` non définie | admin.css l.1990, 2056 | → `--radius-md` |
| CRITIQUE | `--admin-shadow-sm` non définie | admin.css l.2063 | → supprimée (hover border-color) |
| CRITIQUE | `--admin-focus-ring` non définie | admin.css l.2075 | → `--color-devzair-blue` |
| MINEUR | Classes CSS mortes (7 classes) | admin.css | Supprimées |
| MINEUR | Transition sans `prefers-reduced-motion` | admin.css | Encapsulée |
| MINEUR | "uploads" dans UI (mot anglais) | media/rate_limited.html.twig | → "téléversements" |

### Variables CSS non définies (R5 dette) — corrigées
Toutes les 4 variables `--admin-*` introduites en R5 sans définition ont été remplacées par les tokens source officiels.

### CSS legacy supprimé
| Classe | Raison |
|---|---|
| `.page-header` | Remplacé par `.admin-page-header` (R3) |
| `.page-meta` | Orpheline depuis R3 |
| `.page-eyebrow` | Orpheline depuis R6 (preview redesigné) |
| `.preview-title` | Orpheline depuis R6 (→ `.admin-preview-title`) |
| `.preview-actions` | Orpheline depuis R6 |
| `.preview-content__intro` | Orpheline depuis R6 |
| `.admin-media-thumb` | Legacy pré-R5, jamais utilisée |

### Audit `|raw`
- **Unique occurrence :** `preview.html.twig` → `preview.contentHtml|raw`
- **Justification :** `CommonMarkArticleRenderer` + `MarkdownSecurityPolicy` (ADR-010/011)
- **Résultat :** CONFORME

### Audit CSRF
Tous les identifiants CSRF inchangés et conformes :
`article_create`, `article_edit_{uuid}`, `article_publish_{uuid}`, `article_archive_{uuid}`, `article_restore_{uuid}`, `article_hero_image_set_{uuid}`, `article_hero_image_remove_{uuid}`, `media_upload`

### Audit formulaires imbriqués
- `edit` : 0 form imbriqué (PHPUnit `testNoNestedFormsOnDraftEditPage`)
- `new` : 0 form imbriqué (PHPUnit `testNoNestedFormsOnCreatePage`)
- Pattern `form=` HTML5 pour associer les champs sidebar sans imbrication

### Audit dates
Tous les templates admin utilisent `d/m/Y · H:i` avec `<time datetime="...c">`. Aucun UTC visible.

### Audit accessibilité automatisé (Playwright Axe WCAG 2.2 AA)
Les suites admin-editorial.spec.ts, admin-preview.spec.ts et admin-media.spec.ts incluent des scans Axe sur les écrans principaux. 0 violation serious/critical à la clôture de R7.

### Dettes résiduelles documentées
| Dette | Impact | Recommandation |
|---|---|---|
| Ordre clavier Edit R4 (sidebar DOM-first) | Sur desktop, focus passe Publication → Titre → Corps → SEO. Cohérent visuellement mais tab-order DOM-first. | Acceptable pour admin interne. Surveiller les retours utilisateur. |
| `_form.html.twig` encore présent | Fragment hérité, plus utilisé par new/edit (R6). | Peut être supprimé lors d'une future session. |
| 320 px médiathèque 2 colonnes | ~130 px par carte. Lisible mais dense. | Si remontée UX négative, passer à 1 colonne sous 30rem. |

### Fichiers modifiés (R7)
```
apps/api/public/admin/assets/admin.css
apps/api/templates/admin/media/rate_limited.html.twig
docs/12-ADMIN-REFONTE.md
```
