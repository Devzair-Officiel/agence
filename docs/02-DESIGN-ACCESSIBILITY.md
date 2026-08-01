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

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
