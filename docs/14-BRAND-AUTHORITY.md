# Autorité de marque Devzair — Plan opérationnel SEO-COM-9

> Source de vérité pour l'identité externe, les profils officiels, la présence locale et la stratégie d'autorité. Ne recopie pas les règles générales des autres référentiels.

---

## 1. Identité publique validée

| Attribut | Valeur | Statut |
|---|---|---|
| Nom de marque | Devzair | VALIDÉ (source : `site.ts`, `docs/00-PROJECT.md`) |
| Tagline | Agence digitale à taille humaine | VALIDÉ (source : `site.ts`) |
| Domaine | devzair.fr | VALIDÉ (source : `runtimeConfig.public.siteUrl`) |
| Langue | Français (`fr_FR`) | VALIDÉ |
| Positionnement | Agence digitale — sites web, applications, design, contenus, SEO | VALIDÉ |
| Voix éditoriale | Première personne du pluriel : « nous » | VALIDÉ (règle AGENTS.md §2) |

### Cohérence orthographique

La marque s'écrit **Devzair** (D majuscule, z minuscule). Les variantes suivantes sont **interdites dans les surfaces publiques** :

- DevZair — non
- Dev Zair — non
- DEVZAIR — non (sauf nécessité graphique explicite)
- Devzair Agency — non (sauf usage ponctuel en profil externe anglophone)
- Devzair Web — non

Note technique : le fichier asset `/brand/logo_devzaire_agency.png` porte « devzaire » dans son nom de fichier interne. Cette inconsistance n'est pas publique — elle n'affecte pas le SEO. Aucune action urgente.

---

## 2. Données à valider par l'humain

Ces informations sont indispensables avant toute modification de `site.ts`, du Schema.org ou de tout profil externe.

| Information | Statut |
|---|---|
| Raison sociale (legalName) | NON VALIDÉ |
| E-mail professionnel public | NON VALIDÉ |
| Téléphone professionnel public | NON VALIDÉ |
| Ville d'établissement | NON VALIDÉ |
| Adresse postale professionnelle | NON VALIDÉ |
| Zone desservie réelle | NON VALIDÉ |
| Profils sociaux officiels | NON VALIDÉ |
| Logo dédié pour Schema.org / OG | NON VALIDÉ (asset existant : `/brand/logo_devzaire_agency.png` — à confirmer comme logo officiel) |
| Image Open Graph officielle | NON VALIDÉ (`/public/og/` vide — seul `.gitkeep` présent) |

**Règle absolue** : aucune de ces données ne doit être déduite d'un commit Git, d'un domaine WHOIS, d'une adresse personnelle ou d'une hypothèse.

---

## 3. Présence externe observée (audit 2026-09-06)

### Méthode

Quatre requêtes Google effectuées le 2026-09-06 via WebSearch.

| Requête | Résultat |
|---|---|
| `Devzair agence digitale` | Aucun résultat direct pour Devzair |
| `devzair.fr site web agence` | Aucun résultat direct pour devzair.fr |
| `"Devzair" site:linkedin.com OR site:github.com OR site:instagram.com` | Aucun profil exact — résultats similaires non pertinents (DevZaid, Devzur, Devzery) |
| `Devzair digital agency France` | Aucun résultat direct |

### Interprétation

L'absence de résultats est **cohérente** avec l'état actuel du projet :
- `NUXT_PUBLIC_SITE_INDEXABLE` est volontairement `false` (politique SEO-COM-10)
- La marque est récente et n'a pas encore de profils externes officiels créés
- Aucune pénalité Google identifiée — l'indexation n'a pas encore été demandée

**Ne pas interpréter cette absence comme un problème SEO.** SEO-COM-10 traitera l'ouverture à l'indexation.

### Collisions de marque observées

| Nom | Plateforme | Risque |
|---|---|---|
| Devzur (Devzur) | GitHub | Similaire mais distinct — faible risque de confusion |
| DevZaid | GitHub | Différent — pas de risque |
| Devzery | LinkedIn | Différent — pas de risque |

Aucune violation de marque identifiée. Aucune analyse juridique — observation uniquement.

---

## 4. Google Business Profile

**Décision : D — INFORMATIONS INSUFFISANTES**

### Justification

Les règles officielles Google exigent, pour une fiche d'établissement ou de zone de service :
- Un contact direct ou physique avec les clients (réception dans un établissement ou déplacement réel sur place)
- Des horaires d'ouverture réels
- Une adresse réelle pour validation (une adresse résidentielle est acceptée si l'entreprise se déplace chez ses clients — elle peut être masquée au public, seule la zone desservie étant affichée)

Ce qui reste interdit quelle que soit la situation :
- Adresse fictive, bureau virtuel non occupé, boîte postale, adresse choisie uniquement pour le SEO

Les questions suivantes **n'ont pas de réponse documentée** :

1. Devzair reçoit-il physiquement des clients à une adresse ?
2. Devzair se déplace-t-il réellement chez des clients ?
3. Si oui, dans quelles zones réelles ?
4. Quelle adresse réelle pourrait servir à la validation Google ?
5. Quels horaires correspondent à une présence/contact réel ?

**Décision opérationnelle : ne pas créer de fiche Google Business Profile à ce stade.**

Cette décision ne signifie pas que Devzair n'est pas éligible. Elle signifie que les données nécessaires ne sont pas disponibles pour créer une fiche conforme et exacte.

Réévaluation possible dès que les questions ci-dessus ont une réponse documentée et honnête.

---

## 5. Visibilité locale

### Ce qui peut être fait sans données locales validées

- Maintenir le SEO général (contenu de qualité, maillage, Schema.org `Organization`)
- Publier les études de cas réelles
- Partager du contenu utile sur les profils externes (quand créés)
- Maintenir une cohérence de marque sur tous les supports

### Ce qui est interdit sans données validées

- Page `/agence-web-paris` ou similaire — **interdit**
- Liste de villes dans le footer — **interdit**
- `LocalBusiness` ou sous-type dans le Schema.org — **interdit**
- `areaServed`, `PostalAddress`, `telephone` dans le Schema — **interdit**
- `site.contact.city` rempli avec une ville choisie pour le SEO — **interdit**

---

## 6. Profils externes — Matrice

| Canal | Existe | Vérifié | Priorité | Action | Données nécessaires |
|---|---|---|---|---|---|
| LinkedIn (page entreprise) | INCONNU | NON | **P1 haute** | Créer si inexistant — action humaine | Raison sociale, email admin, description, logo |
| GitHub (`Devzair-Officiel`) | EXISTE — compte User public | NON (profil officiel non validé) | **P1 haute** | Confirmer que ce compte est le profil officiel Devzair, puis compléter : nom, avatar, bio, site devzair.fr | Validation humaine — ne pas renseigner `sameAs` avant confirmation |
| Instagram | INCONNU | NON | P2 moyenne | Évaluer selon activité visuelle | Logo, bio, contenu |
| Facebook (page) | INCONNU | NON | P3 faible | Peu pertinent pour B2B agence digitale | — |
| Google Business Profile | INCONNU | NON | BLOQUÉ — ÉLIGIBILITÉ | Voir §4 — ne pas créer maintenant | Adresse, zone, horaires validés |
| Malt / Codeur / Sortlist | NON | — | P3 faible | Évaluer — risque de positionnement freelance | À éviter si présentation comme individu |
| Clutch / DesignRush | NON | — | P2 moyenne | Pertinent si profil agence réel possible | Minimum 3-5 projets documentés |
| Trustpilot | NON | — | P3 faible | Seulement si avis clients réels disponibles | — |

### Critères de priorisation

Un profil externe est prioritaire si :
1. Il est indexé et crédible (LinkedIn, GitHub)
2. Il permet un backlink vers devzair.fr
3. Il renforce le `sameAs` Organization Schema
4. Il atteint réellement les clients cibles de Devzair (TPE/PME)

---

## 7. GitHub

### État observable (2026-09-06)

Un compte GitHub public `Devzair-Officiel` existe et possède au moins le dépôt public `agence` (ce monorepo). Ce compte est de type **User**, pas Organization.

Son statut comme profil public officiel de marque Devzair **n'est pas encore validé humainement** — il ne peut pas être ajouté à `site.socialProfiles` sans confirmation explicite.

### Actions humaines recommandées (P1)

- [ ] Confirmer que `github.com/Devzair-Officiel` est bien le profil GitHub officiel public de Devzair
- [ ] Confirmer que la visibilité publique du dépôt `agence` est volontaire
- [ ] Si profil confirmé : compléter le profil — nom affiché, avatar/logo, bio courte, site `https://devzair.fr`
- [ ] Après confirmation : renseigner `site.socialProfiles` avec l'URL exacte vérifiée

Ne pas rendre le dépôt privé sans décision explicite — vérifier simplement que cette visibilité est intentionnelle.

---

## 8. Schema Organization — État et règles

### État actuel (2026-09-06)

`useSiteSchema.ts` émet uniquement :

```json
{
  "@type": "Organization",
  "@id": "https://devzair.fr/#organization",
  "name": "Devzair",
  "url": "https://devzair.fr",
  "description": "Sites internet, applications métier..."
}
```

Les champs conditionnels ne sont **pas émis** car leurs valeurs sont `null` :
- `legalName` — absent ✓
- `logo` — absent ✓ (aucune image OG configurée)
- `sameAs` — absent ✓ (socialProfiles=[])
- `contactPoint` — absent ✓ (email/phone null)

**Aucun LocalBusiness, aucune adresse fictive, aucun sameAs inventé.** ✓

### Note technique : logo vs OG image

Le code actuel dans `useSiteSchema.ts` :
```typescript
if (site.defaultOgImage) organization.logo = buildAbsoluteAssetUrl(origin, site.defaultOgImage)
```

L'image OG et le logo Schema.org sont des entités distinctes. Quand un logo officiel est validé :
1. Ajouter un champ `logo: string | null` dans l'interface `SiteConfig`
2. Alimenter `organization.logo` depuis `site.logo`, pas depuis `site.defaultOgImage`
3. Conserver `site.defaultOgImage` pour le partage social uniquement

**Aucun changement aujourd'hui** — les deux champs sont null.

---

## 9. Logo, favicon et Open Graph

### Favicon — État ✓

| Asset | Format | Dimensions | Déclaration nuxt.config | Statut |
|---|---|---|---|---|
| `/favicon.ico` | ICO (PNG interne) | 16×16 | `rel="icon"` | ✓ Présent |
| `/favicon-32x32.png` | PNG | 32×32 | `rel="icon" sizes="32x32"` | ✓ Présent |
| `/apple-touch-icon.png` | PNG | 180×180 | `rel="apple-touch-icon"` | ✓ Présent |

Pas de `site.webmanifest` — non bloquant au stade actuel.

### Logo utilisé (header et footer)

`/brand/logo_devzaire_agency.png` — déclaré dans `SiteHeader.vue` et `SiteFooter.vue`.

Autres assets présents mais non utilisés dans le code : `logo-hd.png`, `logo.png`, `agence-devzair.png`.

**Action humaine recommandée** : confirmer quel asset est le logo officiel à utiliser pour Schema.org et les profils externes.

### Open Graph

`/public/og/` ne contient qu'un `.gitkeep`. `site.defaultOgImage` est `null`.

**Conséquence** : les pages partagées sur les réseaux sociaux n'affichent pas d'image prévisuelle.

**Action humaine recommandée (P1)** : fournir un visuel OG officiel (format recommandé : 1200×630 px, WebP ou JPEG, sans texte trop petit). L'implémenter dans `/public/og/og-default.webp` puis renseigner `site.defaultOgImage = '/og/og-default.webp'`.

---

## 10. Bios de référence

À utiliser pour les profils externes, les annuaires et toute présentation publique. Ne pas bourrer de mots-clés.

### Bio très courte (~100 caractères)

> Devzair — agence digitale qui aide les entreprises à développer leur présence en ligne.

### Bio courte (~200 caractères)

> Devzair est une agence digitale à taille humaine. Nous concevons des sites web, applications et stratégies SEO sur mesure pour aider les entreprises à être visibles et efficaces en ligne.

### Présentation complète (~550 caractères)

> Devzair crée des présences digitales complètes pour les entreprises qui veulent être visibles, crédibles et efficaces en ligne. Nous réunissons dans une même démarche la stratégie, le design, le développement web, la création de contenus et le référencement naturel. Chaque projet part d'un besoin réel et débouche sur une solution cohérente, évolutive et mesurable. Nous travaillons avec les TPE, PME, commerces et porteurs de projets qui recherchent un interlocuteur direct et un engagement dans la durée.

**Règles éditoriales pour toutes les bios** :
- Voix « nous » obligatoire
- Aucune promesse de résultat chiffré
- Pas de « experts passionnés », « solutions innovantes », « leader »
- Pas de liste exhaustive des 8 services

---

## 11. Stratégie avis clients

**Aucun faux avis. Aucun achat. Aucune demande filtrée.**

Quand des avis deviennent pertinents (GBP ou plateforme confirmée) :
- Demander librement aux clients réels de partager leur expérience
- Ne pas conditionner la demande à une satisfaction supposée
- Ne pas rédiger de témoignage au nom d'un client

**Statut actuel** : aucune plateforme d'avis active — aucune action possible.

---

## 12. Stratégie citations et backlinks

### Actions légitimes retenues

| Action | Priorité | Condition |
|---|---|---|
| Compléter le profil `Devzair-Officiel` sur GitHub | P1 | Si compte confirmé comme profil officiel |
| Créer la page LinkedIn entreprise | P1 | Dès validation raison sociale + email |
| Partager `/ressources/site-internet-pas-cher` sur LinkedIn | P2 | Après création page LinkedIn |
| Demander un lien « Réalisé par Devzair » aux clients des 4 études de cas | P2 | Accord client explicite requis |
| Soumettre à 1-2 annuaires crédibles (ex. Clutch si profil agence réel) | P3 | Minimum 3-5 projets documentés |

### Actions rejetées

- Fermes de liens — **interdit**
- Inscriptions automatiques dans des annuaires SEO — **interdit**
- Échange de liens artificiels — **interdit**
- Commentaires de blogs pour obtenir un lien — **interdit**
- Achat de backlinks — **interdit**
- Faux profils — **interdit**

---

## 13. Réalisations et contenus comme actifs d'autorité

### Études de cas publiées (P2)

| Étude de cas | URL | Usage autorité |
|---|---|---|
| Kitchen Meat | `/realisations/kitchen-meat` | Partage social, référence design |
| Nidemiel | `/realisations/nidemiel` | Partage social, référence e-commerce |
| Mizan | `/realisations/mizan` | Partage social, référence application |
| Al Mumayiz | `/realisations/al-mumayiz` | Partage social, référence identité |

Stratégie : présenter un projet réel → partager l'étude de cas → permettre au client de relayer volontairement. Ne pas demander de backlink automatiquement.

### Ressource publiée (P3)

`/ressources/site-internet-pas-cher` — contenu utile à partager comme expertise. Les 3 autres articles prix du backlog ne sont pas publiés — ne pas les activer sans mesure préalable.

---

## 14. Actions humaines — Liste priorisée

### P0 — Identité critique (requis avant toute mise à jour de site.ts)

- [ ] **Confirmer le logo officiel** à utiliser pour le Schema.org et les profils externes (parmi : `logo.png`, `logo-hd.png`, `logo_devzaire_agency.png`, `agence-devzair.png`)
- [ ] **Fournir l'image Open Graph officielle** — 1200×630 px — à placer dans `/public/og/og-default.webp`

### P1 — Profils officiels importants

- [ ] **Raison sociale (legalName)** — confirmer le nom légal exact pour `site.ts` et les profils externes
- [ ] **E-mail professionnel public** — valider et confirmer (ex. contact@devzair.fr)
- [ ] **Créer la page LinkedIn entreprise Devzair** — utiliser la bio courte, le logo officiel, le lien devzair.fr
- [ ] **Confirmer que `github.com/Devzair-Officiel` est le profil public officiel Devzair** — puis compléter avatar, bio, site devzair.fr
- [ ] **Confirmer que la visibilité publique du dépôt `agence` est volontaire**

### P2 — Signaux complémentaires

- [ ] **Téléphone professionnel** — valider si un numéro dédié existe pour le renseigner dans `site.ts`
- [ ] **Confirmer l'éligibilité GBP** — répondre aux 5 questions de la section §4
- [ ] **Lien « Réalisé par Devzair »** — approcher les 4 clients des études de cas publiées

### P3 — Opportunités d'autorité éditoriale

- [ ] Évaluer Clutch après 5+ projets documentés
- [ ] Publier les 3 articles prix restants quand la mesure le justifie
- [ ] Créer un compte Instagram si contenu visuel régulier prévu

---

## 15. Interdits permanents

- Bureau virtuel, coworking fictif, adresse louée pour le SEO
- Pages `/agence-web-[ville]` sans implantation réelle validée
- Footer géographique inventé (« Devzair intervient à Paris, Lyon… »)
- `LocalBusiness`, `PostalAddress`, `areaServed` sans données validées
- `socialProfiles` avec des URLs non vérifiées
- Faux avis, faux témoignages, fausses certifications
- Contenu personnel (adresse, SIREN) non destiné à être public dans le dépôt Git

---

## 16. Critères de mesure futurs (SEO-COM-10)

- Recherches de marque « Devzair » dans Google Search Console (impressions, clics)
- Pages indexées sans erreur
- Trafic organique de marque vs hors marque
- Taux de clics sur les études de cas
- Demandes de contact attribuées à un canal
- Liens entrants identifiés (Search Console, Ahrefs si disponible)
- Mentions non liées (Google Alerts sur « Devzair »)

---

## Règle de maintenance

Ce document est mis à jour à chaque changement de statut d'une donnée (validation, création d'un profil, décision locale). Il ne recopie pas les règles générales de `docs/04-SEO-CONTENT-GEO.md`. Les tâches réalisées sont consignées dans `docs/10-TRACKING.md`.
