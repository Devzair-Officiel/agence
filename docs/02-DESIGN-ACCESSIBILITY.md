# Design, responsive et accessibilité

> Règles du design system, de l’expérience utilisateur et de l’accessibilité.

## 8. Identité visuelle et expérience utilisateur

## 8.1 Principes

- image premium mais accessible ;
- design humain, sobre et distinctif ;
- hiérarchie visuelle forte ;
- espaces respirants ;
- cohérence entre stratégie, développement, photographie et SEO ;
- éviter l’apparence de thème générique ou d’agence surdimensionnée.

## 8.2 Design system minimal

Définir avant intégration :

- palette ;
- typographies et solutions de repli ;
- échelle d’espacement ;
- grille ;
- largeurs de contenu ;
- boutons ;
- liens ;
- formulaires ;
- cartes ;
- messages d’état ;
- tableaux ;
- modales ;
- navigation ;
- icônes ;
- styles de focus ;
- règles d’animation ;
- traitement des images.

Chaque composant doit documenter ses variantes, états, comportement responsive et règles d’accessibilité.

## 8.3 Responsive

Tester au minimum :

- petit mobile ;
- mobile standard ;
- tablette ;
- ordinateur portable ;
- grand écran ;
- zoom navigateur à 200 % ;
- orientation portrait et paysage lorsque pertinente.

Ne pas concevoir uniquement à partir de largeurs fixes.

---

## 9. Accessibilité

### Cible

Viser WCAG 2.2 niveau AA pour le parcours principal.

### Contrôles

- HTML sémantique ;
- ordre logique des titres ;
- navigation clavier complète ;
- focus visible et non masqué ;
- lien d’évitement ;
- contrastes suffisants ;
- texte redimensionnable ;
- formulaires correctement étiquetés ;
- erreurs annoncées et compréhensibles ;
- alternatives textuelles adaptées ;
- sous-titres et transcriptions pour les médias concernés ;
- animations réduites si `prefers-reduced-motion` ;
- zones cliquables suffisantes ;
- pas d’information transmise uniquement par la couleur ;
- compatibilité avec lecteurs d’écran sur les parcours principaux.

### Tests

- analyse automatisée ;
- navigation clavier manuelle ;
- lecteur d’écran sur les parcours critiques ;
- zoom et reflow ;
- contrôle des contrastes ;
- audit après changement majeur de composants.

L’automatisation ne remplace pas les vérifications manuelles.

### Illustrations informatives (SVG)

Un schéma qui porte du sens (ex. le graphe des cinq pôles sur `/`) doit :

- déclarer `role="img"` sur le `<svg>` racine ;
- exposer un `<title>` court et un `<desc>` détaillé, tous deux référencés
  via `aria-labelledby` (une seule annonce, pas une lecture confuse des
  fragments internes) ;
- générer les ids `<title>`/`<desc>` avec `useId()` de Vue plutôt que des
  chaînes littérales, pour rester SSR-safe si le composant est rendu
  plusieurs fois dans une même page ;
- marquer tous les sous-groupes purement graphiques `aria-hidden="true"` ;
- ne poser aucun `tabindex` : une illustration n’a pas à entrer dans le
  parcours clavier tant qu’elle n’est pas interactive.

### Contraste Devzair-blue sur surfaces sombres

Le token `--color-devzair-blue` (`#2e86d9`) ne satisfait pas WCAG 2.2 AA
pour du **petit texte gras** (Space Mono à 12 px / 700, cas des index et
tags de section) sur les surfaces sombres suivantes :

- sur `--color-petrol` (`#0c5b57`) → 2,08:1 (seuil 4,5:1) — échec ;
- sur `--surface-inverse` (`#141e2c`) → 4,41:1 (seuil 4,5:1) — échec.

Règle :

- petit texte gras (mono 11–12 px, bold) sur ces surfaces → utiliser
  `--color-cream` (contraste ≥ 8,8:1) ou `--color-cream-muted` pour un
  ton plus discret ;
- puce décorative sur fond petrol → `--color-cream-muted` plutôt que
  `--color-devzair-blue`.

Devzair-blue reste utilisable comme accent sur `--color-navy` (base
sombre non élevée) et sur toutes les surfaces claires : Axe le valide à
5:1+ dans ces contextes.

### Carrousels et scroll-snap

Un carrousel mobile doit rester CSS-natif tant qu'aucun besoin métier
ne justifie une bibliothèque JS :

- `display: flex; overflow-x: auto; scroll-snap-type: x mandatory;` ;
- chaque item `scroll-snap-align: start` ;
- l'ensemble des items reste dans le DOM SSR — aucune carte cachée par
  JS, aucune duplication de contenu, indexabilité et lecture linéaire
  préservées ;
- pas de boutons prev/next fantômes ;
- hint visuel `aria-hidden` + doublure `sr-only` pour indiquer le geste
  aux lecteurs d'écran ;
- `scroll-behavior: smooth` explicitement remis à `auto` en
  `prefers-reduced-motion` — défense en profondeur.

### Timeline verticale via pseudo-éléments

Une timeline de type méthode (ex. `HomeProcess` mobile) doit rester
purement décorative et n'ajouter aucun élément au DOM :

- point + trait vertical dessinés via `::before` (trait) et `::after`
  (point) sur chaque `<li>` ;
- traits ajustés au premier et au dernier item pour ne pas dépasser
  visuellement le premier point ni le dernier ;
- décor totalement escamoté dès le premier breakpoint où la grille prend
  le relais (`content: none;` sur les pseudo-éléments) — pas de
  superposition ambiguë en 2 colonnes ;
- aucune couleur porteuse d'information (le texte reste explicite sans
  le point).

### Grille desktop asymétrique 3+2 centrée

Pour une liste de 5 items sur desktop qui ne veut ni 5 colonnes trop
serrées ni 4+1 orphelines, préférer une grille `repeat(6, 1fr)` avec
`grid-column: span 2` par défaut, puis centrer les items 4 et 5 :

- item 4 : `grid-column: 2 / span 2` ;
- item 5 : `grid-column: 4 / span 2`.

Pattern utilisé par `HomeTrust`. Il évite d'introduire une colonne fantôme
ou d'aligner à gauche une rangée incomplète, et reste 100 % CSS.

### Animations et `prefers-reduced-motion`

Les animations doivent :

- rester en CSS pure (`animation` ou `transition`), sans JavaScript ;
- être finies dans le temps (pas de `infinite`) ;
- utiliser `animation-fill-mode: forwards` pour figer l’état final ;
- **et** être neutralisées explicitement au niveau du composant en
  `prefers-reduced-motion: reduce`.

La règle globale de `assets/css/animations.css` force
`animation-duration` et `transition-duration` à `0.01ms !important`, mais
elle ne restaure pas les états initiaux d’un composant (par exemple
`stroke-dashoffset: 200` ou `opacity: 0`). Le composant doit donc
publier sa propre `@media (prefers-reduced-motion: reduce)` qui remet
explicitement les valeurs d’arrivée (`stroke-dashoffset: 0`, `opacity:
1`, `transform: none`) — défense en profondeur au-dessus de la règle
globale.

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
