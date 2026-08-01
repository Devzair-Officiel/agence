# Analytics, tests et livraison

> Plan de mesure, assurance qualité, navigateurs, environnements et CI/CD.

## 18. Analytics et mesure

### Principe

Mesurer uniquement ce qui sert une décision.

### Événements

- envoi réussi du formulaire ;
- erreur de formulaire ;
- clic e-mail ;
- clic téléphone ;
- CTA principal ;
- consultation d’étude de cas ;
- passage d’une page service au contact ;
- téléchargement réel ;
- prise de rendez-vous si ajoutée.

### Qualité des données

- plan de marquage versionné ;
- environnement de test exclu ;
- trafic interne filtré selon méthode licite ;
- aucune donnée personnelle dans les URL ou événements ;
- noms d’événements stables ;
- consentement respecté ;
- tests avant mise en production ;
- documentation des changements.

### Tableaux de bord

- acquisition ;
- comportement ;
- conversion ;
- SEO ;
- performance ;
- disponibilité ;
- erreurs ;
- GEO identifiable.

---

## 19. Tests et assurance qualité

## 19.1 Tests automatisés

Selon la pile :

- tests unitaires ;
- intégration ;
- composants ;
- API ;
- formulaires ;
- permissions ;
- génération des métadonnées ;
- sitemap ;
- robots.txt ;
- données structurées ;
- redirections ;
- tests end-to-end des parcours critiques ;
- tests d’accessibilité automatisés ;
- analyse statique ;
- dépendances ;
- recherche de secrets.

## 19.2 Tests manuels

- contenus et orthographe ;
- navigation ;
- mobile ;
- clavier ;
- lecteur d’écran sur parcours critiques ;
- formulaires ;
- e-mails ;
- erreurs ;
- liens ;
- partage social ;
- impression si utile ;
- consentement ;
- indexabilité ;
- données structurées ;
- performance ;
- sécurité de configuration ;
- restauration de sauvegarde.

## 19.3 Navigateurs

Définir une matrice basée sur l’audience. Par défaut :

- versions récentes de Chrome, Edge, Firefox et Safari ;
- Safari iOS ;
- Chrome Android.

Documenter toute incompatibilité acceptée.

---

## 20. CI/CD et environnements

### Environnements

- local ;
- test automatisé ;
- préproduction protégée ;
- production.

### Préproduction

- accès protégé ;
- `noindex` en en-tête ou authentification ;
- absence du sitemap public de production ;
- données de test non sensibles ;
- e-mails redirigés ou désactivés ;
- clés distinctes.

### Pipeline minimal

1. installation reproductible ;
2. lint ;
3. analyse de types ;
4. tests ;
5. build ;
6. analyse de dépendances ;
7. recherche de secrets ;
8. contrôle accessibilité/SEO de base ;
9. déploiement préproduction ;
10. smoke tests ;
11. approbation ;
12. déploiement production ;
13. smoke tests production ;
14. possibilité de rollback.

### Déploiement

- migrations réversibles ou procédure de retour ;
- sauvegarde avant changement risqué ;
- journal de version ;
- contrôle des variables ;
- purge cache maîtrisée ;
- surveillance renforcée après livraison.

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
