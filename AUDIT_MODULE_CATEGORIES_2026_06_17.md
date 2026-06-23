# Audit du module Gestion des Categories

Date : 2026-06-17  
Projet : ISPITSRK / ISTAHT  
Application : `C:\ISTHT2026\ispitsrk.ma`  
Module audite : Categories d'approvisionnement

## 1. Resume executif

Le module Categories a deja ete fortement simplifie par rapport a l'ancienne architecture.

Avant, la classification passait par plusieurs couches :

```text
Categorie principale
  -> Nature de prestation
    -> Categorie
      -> Article
        -> Categorie marche
```

Aujourd'hui, le coeur actif est beaucoup plus simple :

```text
Categorie
  -> Articles
  -> Marches
  -> Bons de commande internes
  -> Stock via mouvements des articles
```

Conclusion audit :

- La direction actuelle est bonne.
- La table `categories` est devenue le referentiel central.
- Les anciennes tables `categorie_principales`, `nature_prestations` et `marche_categories` ne sont plus actives.
- Il reste quelques traces techniques a nettoyer : colonne `icone`, doublon `couleur` / `couleur_affichage`, duplication des champs de lot dans `articles`, dependance au scope global `Article::withNonExists()`.
- L'UX actuelle est fonctionnelle mais encore orientee tableau. Pour les utilisateurs finaux, une page en cartes avec actions directes serait plus simple.

Note d'audit du module : **8 / 10**

## 2. Structure actuelle du module

### Fichiers principaux

| Type | Fichier |
|---|---|
| Modele | `app/Models/Categorie.php` |
| Controleur | `app/Http/Controllers/CategorieController.php` |
| Liste UI | `resources/js/Pages/Categories/Index.vue` |
| Detail UI | `resources/js/Pages/Categories/Show.vue` |
| Modal creation | `resources/js/Pages/Categories/CreateCategorieModal.vue` |
| Modal modification | `resources/js/Pages/Categories/EditCategorieModal.vue` |
| Import bordereau | `app/Services/BordereauArticleImporter.php` |
| Routes | `routes/web.php` |

### Routes actives

| Methode | URL | Route | Role |
|---|---|---|---|
| GET | `/categories` | `categories.index` | Liste et filtres. |
| POST | `/categories/import` | `categories.import` | Import bordereau articles. |
| POST | `/categories/store` | `categories.store` | Creation categorie. |
| GET | `/categories/{categorie}` | `categories.show` | Detail categorie. |
| GET | `/categories/{categorie}/edit` | `categories.edit` | Modal modification. |
| PUT | `/categories/{categorie}` | `categories.update` | Enregistrement modification. |
| DELETE | `/categories/{categorie}` | `categories.destroy` | Suppression ou desactivation. |

### Permissions Spatie

| Permission | Usage |
|---|---|
| `list_categories` | Voir liste et detail. |
| `create_categories` | Creer et importer. |
| `edit_categories` | Modifier. |
| `delete_categories` | Supprimer/desactiver. |

## 3. Structure de donnees

### Table principale : `categories`

Etat reel observe dans la base `optizaworks_gs`.

| Colonne | Type | Role |
|---|---|---|
| `id` | bigint unsigned | Identifiant technique. |
| `nom` | varchar(100) | Nom affiche. |
| `code` | varchar(20), unique | Code interne. |
| `code_lot` | varchar(20), nullable | Code du lot marche, ex. `LOT-1`. |
| `numero_lot` | tinyint unsigned, nullable | Numero de lot pour tri et presets. |
| `couleur_affichage` | varchar(20), nullable | Couleur visible dans l'interface. |
| `icone` | varchar(80), nullable | Ancienne colonne icone, plus utile pour l'UX cible. |
| `description` | text, nullable | Description metier. |
| `couleur` | varchar(20), nullable | Doublon technique de compatibilite. |
| `est_actif` | tinyint(1) | Active ou masque la categorie. |
| `created_at` | timestamp | Creation. |
| `updated_at` | timestamp | Modification. |

Index :

- `PRIMARY KEY (id)`
- index unique sur `code`

### Tables directement liees

| Table | Relation | Role |
|---|---|---|
| `articles` | `articles.categorie_id -> categories.id` | Articles de la categorie. |
| `bon_commandes` | `bon_commandes.categorie_id -> categories.id` | Marches par categorie. |
| `chef_commandes` | `chef_commandes.categorie_id -> categories.id` | Bons de commande internes par categorie. |

### Tables liees indirectement

| Table | Chemin | Role |
|---|---|---|
| `mouvement_stocks` | `categories -> articles -> mouvement_stocks` | Entrees/sorties de stock. |
| `bon_livraisons` | `categories -> chef_commandes -> bon_livraisons` et articles BL | Livraisons associees. |
| `receptions` | `categories -> bon_livraisons -> receptions` | Reception physique. |
| `decomptes` | `categories -> marches -> livraisons/receptions -> decomptes` | Suivi financier. |

### Volumes actuels observes

| Donnee | Nombre |
|---|---:|
| Categories | 12 |
| Articles | 365 |
| Marches / `bon_commandes` | 6 |
| Bons internes / `chef_commandes` | 8 |
| Mouvements stock | 96 |

Repartition des articles par categorie :

| Categorie | Code | Lot | Articles |
|---|---|---:|---:|
| Lot N1 - Viandes et Abats | `VIA` | 1 | 9 |
| Lot N2 - Volailles, Lapin et Oeufs | `VOL` | 2 | 6 |
| Lot N3 - Poissons et Fruits de Mer | `POI` | 3 | 13 |
| Lot N4 - Epicerie | `EP` | 4 | 244 |
| Lot N5 - Fruits et Legumes | `LEG` | 5 | 89 |
| Lot N6 - Pain et Viennoiserie | `PAI` | 6 | 4 |
| Autres categories achats/fournitures | divers | - | 0 |

Observation : certaines donnees affichent des caracteres mal encodes (`Nø`, `‚`, `?ufs`). Cela n'empeche pas le fonctionnement, mais doit etre corrige pour une UX propre.

## 4. Relations Eloquent actuelles

Modele `Categorie` :

```php
articles()       -> hasMany Article
marches()        -> hasMany BonCommande via categorie_id
chefCommandes()  -> hasMany ChefCommande via categorie_id
scopeActives()   -> filtre est_actif = true
```

Modele `Article` :

```php
categorie()              -> belongsTo Categorie
bonCommandeArticles()    -> hasMany BonCommandeArticle
mouvementsStock()        -> hasMany MouvementStock
mouvementsStockEntree()  -> hasMany MouvementStock type entree
mouvementsStockSortie()  -> hasMany MouvementStock type sortie
images()                 -> hasMany ArticleImage
```

Important : `Article` applique un global scope `IsExistsInMarcheScope`. Beaucoup de requetes du module doivent donc appeler `Article::withNonExists()` pour inclure tous les articles. Ce point ajoute de la complexite technique.

## 5. Comment une categorie est creee

### Creation manuelle

Formulaire actuel :

- Nom
- Code interne
- Lot marche
- Code lot
- Couleur
- Description
- Categorie active

Traitement backend :

1. Validation du nom, code, lot, couleur, description, actif.
2. Creation dans `categories`.
3. `couleur` est remplie depuis `couleur_affichage`.
4. `icone` est forcee a `null`.

### Creation par import bordereau

Service : `BordereauArticleImporter`.

Flux :

1. L'utilisateur charge un fichier Excel/CSV/ODS.
2. Le systeme detecte le lot depuis le nom du fichier ou le contenu.
3. Le systeme cree ou met a jour la categorie.
4. Le systeme cree ou met a jour les articles de ce lot.

Lots geres :

```text
LOT-1 -> Viandes et Abats
LOT-2 -> Volailles, Lapin et Oeufs
LOT-3 -> Poissons et Fruits de Mer
LOT-4 -> Epicerie
LOT-5 -> Fruits et Legumes
LOT-6 -> Pain et Viennoiserie
```

## 6. Comment une categorie est utilisee

### Avec les articles

```text
categories.id
  -> articles.categorie_id
```

Une categorie contient plusieurs articles. Les articles recopient aussi certains champs de la categorie :

- `code_lot`
- `numero_lot`
- `couleur_affichage`
- `icone`

Utilite : affichage et filtres plus rapides.  
Risque : duplication de donnees et besoin de synchronisation quand une categorie change.

### Avec les marches

```text
categories.id
  -> bon_commandes.categorie_id
```

Un marche appartient a une categorie. Lors de creation/modification d'un marche :

- l'utilisateur choisit une categorie,
- les articles du marche doivent appartenir a cette categorie,
- le controleur refuse les articles hors categorie.

### Avec les bons de commande internes

```text
categories.id
  -> chef_commandes.categorie_id
```

Un bon interne est cree pour une categorie. Le systeme cherche ensuite le marche actif attribue pour cette categorie :

```text
Categorie
  -> Marche actif avec fournisseur
    -> Articles autorises
      -> Bon interne
```

### Avec le stock

La categorie n'a pas de stock direct. Le stock est calcule depuis les articles :

```text
Categorie
  -> Articles
    -> MouvementStock entree/sortie
```

La page detail categorie affiche :

- articles,
- stock total,
- valeur stock,
- sous seuil,
- rupture,
- mouvements recents,
- marches recents,
- livraisons recentes,
- receptions recentes.

## 7. Schema simplifie actuel

```text
Categorie
  |
  +-- Articles
  |     |
  |     +-- Mouvement stock
  |
  +-- Marches / BonCommande
  |     |
  |     +-- Lignes de marche / BonCommandeArticle
  |
  +-- Bons internes / ChefCommande
        |
        +-- Bons de livraison
              |
              +-- Receptions
```

## 8. Cartographie des relations historiques

Ancienne logique :

```text
Categorie Principale
  -> Nature de prestation
    -> Categorie
      -> Article
        -> MarcheCategory
          -> Marche
```

### Evaluation des anciennes relations

| Relation | Utilite metier | Utilite technique | Impact utilisateur | Complexite |
|---|---|---|---|---|
| `categorie_principales -> categories` | Faible dans le contexte alimentaire actuel. | Ancienne classification. | Ajoutait un choix supplementaire. | Forte. |
| `nature_prestations -> categories` | Faible, souvent toujours meme nature. | Filtrage historique. | Peu visible, mais complexifie formulaires. | Forte. |
| `marche_categories -> articles/marches` | Redondante avec `categories`. | Ancien referentiel marche. | Confusion entre categorie article et categorie marche. | Tres forte. |

Reponse metier : ces relations n'apportent plus de valeur suffisante pour la gestion des marches alimentaires. Leur suppression est justifiee.

### Etat actuel des suppressions

Tables actives supprimees :

- `categorie_principales`
- `nature_prestations`
- `marche_categories`

Tables archivees conservees :

- `archive_categorie_principales` : 1 ligne
- `archive_nature_prestations` : 1 ligne
- `archive_marche_categories` : 11 lignes

## 9. Analyse de la complexite restante

### Points forts

- Une categorie est maintenant le niveau principal.
- Les articles, marches et bons internes pointent directement vers `categories.id`.
- La suppression est securisee : si une categorie contient des articles ou documents, elle est desactivee au lieu d'etre supprimee.
- Les formulaires creation/modification sont alignes.
- Les permissions sont claires.
- L'import bordereau automatise la creation d'articles.

### Points faibles

| Probleme | Impact | Niveau |
|---|---|---|
| Colonne `icone` encore presente | Donnee inutile pour l'UX cible couleur seulement. | Moyen |
| Doublon `couleur` / `couleur_affichage` | Deux sources possibles pour une meme information. | Moyen |
| Copie des champs lot dans `articles` | Risque de divergence si synchronisation incomplete. | Moyen |
| Scope global `Article::withNonExists()` | Complexifie toutes les requetes articles. | Eleve |
| Page categories en tableau | Efficace mais moins intuitive pour utilisateurs non techniques. | Moyen |
| Import bordereau dans un bloc `details` | Fonctionnel mais peu guide pour utilisateur final. | Faible |
| Categories sans articles | Bruit dans l'ecran principal si elles ne sont pas utilisees. | Moyen |
| Encodage de certaines valeurs | Mauvaise lisibilite metier. | Moyen |

## 10. Elements a conserver

| Element | Pourquoi conserver |
|---|---|
| Table `categories` | Referentiel metier central. |
| `nom`, `code`, `description`, `est_actif` | Informations minimales indispensables. |
| `code_lot`, `numero_lot` | Tres utile pour marches alimentaires par lot. |
| `couleur_affichage` | Suffisant pour identifier visuellement la categorie. |
| Relation `Categorie -> Articles` | Relation centrale. |
| Relation `Categorie -> Marches` | Indispensable au flux achat. |
| Relation `Categorie -> ChefCommandes` | Indispensable au bon interne. |
| Desactivation au lieu de suppression si donnees | Evite la perte de donnees. |
| Import bordereau | Gain de temps important. |

## 11. Elements a supprimer ou reduire

| Element | Action proposee | Justification | Risque |
|---|---|---|---|
| `categories.icone` | Supprimer apres verification UI/import. | La demande actuelle veut couleur sans icone. | Faible si plus aucune page ne l'utilise. |
| `articles.icone` | Supprimer apres suppression categorie. | Herite d'une logique visuelle abandonnee. | Faible. |
| `categories.couleur` | Fusionner vers `couleur_affichage`. | Evite doublon. | Moyen si certains exports lisent `couleur`. |
| `articles.code_lot`, `articles.numero_lot`, `articles.couleur_affichage` | Garder temporairement ou remplacer par jointure. | Donnees derivees de categorie. | Moyen pour performance/filtres. |
| Categories actives sans articles ni marches | Archiver/desactiver si non utiles. | Reduit bruit utilisateur. | Faible. |
| Scope global article `IsExistsInMarcheScope` | Revoir et remplacer par scopes explicites. | `withNonExists()` est source d'erreurs. | Eleve, a faire avec tests. |

## 12. Structure metier cible proposee

Structure recommandee :

```text
Categorie
  -> Articles
    -> Marche
      -> Fournisseur attributaire
        -> Bon de commande interne
          -> Bon de livraison
            -> Reception
              -> Stock
                -> Decompte
```

Version simplifiee pour les utilisateurs :

```text
Categorie
  -> Articles
  -> Marches
  -> Stock
```

Table `categories` cible :

| Colonne | Role |
|---|---|
| `id` | Identifiant. |
| `nom` | Nom de la categorie. |
| `code` | Code unique. |
| `code_lot` | Code lot marche. |
| `numero_lot` | Ordre/numero lot. |
| `couleur_affichage` | Couleur UI. |
| `description` | Description. |
| `est_actif` | Statut. |
| `created_at`, `updated_at` | Audit technique. |

## 13. UX actuelle

### Parcours creation categorie actuel

```text
Categories
  -> Nouvelle categorie
    -> saisir nom/code/lot/couleur/description/statut
      -> Enregistrer
```

Nombre de clics minimum : 3 a 4.

Points positifs :

- Modal rapide.
- Champs creation et modification identiques.
- Lot pre-remplit code/couleur.

Points faibles :

- Page principale en tableau, moins visuelle.
- Actions "Ajouter article" et "Creer marche" ne sont pas disponibles directement depuis une categorie.
- Import bordereau visible mais peu accompagne.
- Les categories vides melangees aux categories alimentaires principales.

## 14. UX cible proposee

### Parcours utilisateur simplifie

```text
Page Categories
  -> Voir les cartes categories
  -> Cliquer sur une categorie
  -> Voir articles, marches, stock, alertes
  -> Action directe : Ajouter article ou Creer marche
```

Objectif : un utilisateur non technique doit comprendre immediatement :

- combien d'articles sont dans la categorie,
- s'il y a des ruptures,
- s'il existe des marches actifs,
- quelles actions sont possibles.

### Actions directes recommandees

Sur chaque categorie :

- Voir
- Modifier
- Supprimer/desactiver
- Ajouter article
- Creer marche

### Reduction des etapes

| Action | Avant | Cible |
|---|---:|---:|
| Creer categorie | 3-4 clics | 2-3 clics |
| Ajouter article dans categorie | Aller Articles + choisir categorie | 1 clic depuis carte categorie |
| Creer marche pour categorie | Aller Marches + choisir categorie | 1 clic depuis carte categorie |
| Voir alertes stock categorie | Lire tableau/detail | Badge direct sur carte |

## 15. Proposition d'interface

### Page Categories cible

Disposition :

```text
+----------------------------------------------------------+
| Categories d'approvisionnement       [Nouvelle categorie] |
| Recherche...  Lot...  Statut...                          |
+----------------------------------------------------------+
| KPI: 12 categories | 365 articles | 297 ruptures | ...   |
+----------------------------------------------------------+
| [Carte Viandes] [Carte Volailles] [Carte Poissons]       |
| [Carte Epicerie] [Carte Fruits/Legumes] [Carte Pain]     |
+----------------------------------------------------------+
```

### Carte categorie

```text
+--------------------------------------+
| [couleur] Viandes et Abats           |
| LOT-1 | Active                       |
|                                      |
| Articles : 9                         |
| Marches  : 1                         |
| Rupture  : 6                         |
| Sous seuil : 0                       |
|                                      |
| [Voir] [Modifier] [Ajouter article]  |
| [Creer marche] [Supprimer]           |
+--------------------------------------+
```

### Detail categorie cible

```text
+----------------------------------------------------------+
| Viandes et Abats                         [Modifier]       |
| LOT-1 | Active | Couleur                              |
+----------------------------------------------------------+
| KPI articles | KPI stock | KPI marches | KPI alertes      |
+----------------------------------------------------------+
| Onglets : Articles | Marches | Stock | Livraisons         |
+----------------------------------------------------------+
| Tableau simple selon onglet choisi                        |
+----------------------------------------------------------+
```

## 16. Style graphique recommande

Style : ERP moderne, sobre et operationnel.

Inspirations :

- Odoo pour la simplicite des cartes.
- Zoho Inventory pour les listes et badges.
- SAP Fiori pour la lisibilite metier.

Couleurs :

| Usage | Couleur |
|---|---|
| Actions principales | Bleu fonce ISTAHT |
| Navigation et filtres | Bleu clair ISTAHT |
| Validation / actif | Vert |
| Alertes / sous seuil | Orange |
| Suppression / rupture | Rouge |
| Fonds | Blanc, gris tres clair |

Composants :

- Cartes categories.
- Badges statut et alertes.
- Tableaux simplifiés.
- Boutons coherents.
- Modales courtes.
- Filtres persistants.
- Confirmation suppression explicite.

## 17. Animations recommandees

Animations legeres uniquement :

- Apparition progressive des cartes.
- Hover discret sur carte.
- Transition douce des modales.
- Skeleton loader au chargement.
- Compteurs animes sur KPI.

A eviter :

- Animations longues.
- Effets 3D.
- Transitions qui retardent la saisie.
- Decorations inutiles.

## 18. Plan de migration vers la structure cible

### Phase 1 - Stabilisation

1. Corriger l'encodage des noms de categories.
2. Identifier les categories actives sans articles ni marches.
3. Ajouter tests pour :
   - creation categorie,
   - modification categorie,
   - suppression/desactivation,
   - import bordereau,
   - creation marche par categorie.

### Phase 2 - Nettoyage donnees

1. Verifier que toutes les relations pointent vers `categories.id`.
2. Confirmer que les tables archivees ne sont plus lues.
3. Exporter une sauvegarde des tables :
   - `categories`
   - `articles`
   - `bon_commandes`
   - `chef_commandes`
   - archives legacy
4. Desactiver ou renommer les categories inutilisees.

### Phase 3 - Nettoyage schema

1. Supprimer `icone` de `categories` apres verification.
2. Supprimer `icone` de `articles`.
3. Fusionner `couleur` et `couleur_affichage`.
4. Revoir la duplication `code_lot` / `numero_lot` dans `articles`.
5. Revoir le global scope `IsExistsInMarcheScope`.

### Phase 4 - Refonte UX

1. Remplacer la table principale par une grille de cartes.
2. Ajouter actions directes :
   - Ajouter article.
   - Creer marche.
3. Ajouter onglets dans le detail categorie :
   - Articles.
   - Marches.
   - Stock.
   - Livraisons/Receptions.
4. Ajouter skeleton loaders et transitions legeres.

### Phase 5 - Verification finale

Commandes recommandees :

```bash
php artisan test
npm.cmd run build
php artisan route:list --name=categories
```

Tests metier a executer manuellement :

```text
Creer categorie
Modifier categorie
Supprimer categorie vide
Desactiver categorie avec articles
Importer bordereau
Creer article depuis categorie
Creer marche depuis categorie
Verifier stock et alertes
```

## 19. Decision recommandee

Decision proposee :

Conserver l'architecture actuelle simplifiee, mais finaliser le nettoyage et refondre l'UX en cartes.

Priorites :

1. Garder `Categorie -> Articles -> Marches` comme base metier.
2. Supprimer les dernieres traces inutiles (`icone`, doublons couleur).
3. Remplacer la liste tableau par cartes categorie.
4. Ajouter actions directes "Ajouter article" et "Creer marche".
5. Nettoyer les categories inutilisees ou non alimentaires si elles ne servent pas aux marches.

Cette trajectoire reduit la complexite technique et rend le module comprehensible pour un utilisateur administratif non technique.

