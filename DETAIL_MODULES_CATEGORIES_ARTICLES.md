# Detail des modules Categories et Articles

Date de reference : 2026-06-07

Ce document decrit les tables, colonnes, relations, routes et champs de formulaires utilises par les modules `Categories` et `Articles` apres la simplification de l'architecture metier.

Architecture metier actuelle :

```text
Categorie
  -> Articles
  -> Marches
  -> Fournisseur
  -> Bons de commande internes
  -> Bons de livraison
  -> Receptions
  -> Stock
  -> Decomptes
```

Les anciennes couches `categorie_principales`, `nature_prestations` et `marche_categories` ne sont plus utilisees par ces modules. Elles ont ete archivees puis supprimees de la base active.

## Module Categories

### Role metier

Une categorie est le niveau principal de classement des articles et des marches alimentaires.

Exemples :

- Viandes et Abats
- Volailles, Lapin et Oeufs
- Poissons et Fruits de Mer
- Fruits et Legumes
- Epicerie
- Pain et Viennoiserie
- Produits d'entretien
- Fournitures diverses

### Table principale : `categories`

| Colonne | Type base | Obligatoire | Role |
|---|---:|---:|---|
| `id` | bigint unsigned | Oui | Identifiant technique. |
| `nom` | varchar(100) | Oui | Nom affiche de la categorie. |
| `code` | varchar(20) | Oui | Code interne unique. |
| `code_lot` | varchar(20) | Non | Code du lot marche, exemple `LOT-1`. |
| `numero_lot` | tinyint unsigned | Non | Numero de lot pour le tri et le rattachement marche. |
| `couleur_affichage` | varchar(20) | Non | Couleur UI de la categorie. |
| `icone` | varchar(80) | Non | Nom de l'icone Heroicons utilisee dans l'interface. |
| `description` | text | Non | Description fonctionnelle. |
| `couleur` | varchar(20) | Non | Couleur technique de compatibilite, remplie depuis `couleur_affichage`. |
| `est_actif` | tinyint(1) | Oui | Active ou masque la categorie. Defaut : `1`. |
| `created_at` | timestamp | Non | Date de creation. |
| `updated_at` | timestamp | Non | Date de modification. |

### Relations Eloquent

Depuis `App\Models\Categorie` :

| Relation | Type | Table cible | Cle |
|---|---|---|---|
| `articles()` | hasMany | `articles` | `articles.categorie_id` |
| `marches()` | hasMany | `bon_commandes` | `bon_commandes.categorie_id` |
| `chefCommandes()` | hasMany | `chef_commandes` | `chef_commandes.categorie_id` |

### Tables consultees dans les ecrans Categories

| Table | Utilisation |
|---|---|
| `categories` | Liste, creation, modification et fiche categorie. |
| `articles` | Compteurs articles, articles actifs, rupture, sous seuil, stock total. |
| `bon_commandes` | Nombre de marches et marches recents par categorie. |
| `mouvement_stocks` | Entrees, sorties et mouvements recents par categorie. |
| `bon_livraisons` | Livraisons recentes liees aux articles de la categorie. |
| `receptions` | Receptions recentes liees aux livraisons/articles de la categorie. |

### Routes du module Categories

| Methode | URL | Nom route | Action |
|---|---|---|---|
| GET | `/categories` | `categories.index` | Liste des categories. |
| POST | `/categories/store` | `categories.store` | Creation. |
| GET | `/categories/{categorie}/edit` | `categories.edit` | Modal de modification. |
| GET | `/categories/{categorie}` | `categories.show` | Detail d'une categorie. |
| PUT | `/categories/{categorie}` | `categories.update` | Modification. |
| POST | `/categories/import` | `categories.import` | Import bordereau articles. |

### Permissions Spatie

| Permission | Utilisation |
|---|---|
| `list_categories` | Acces liste et detail. |
| `create_categories` | Creation et import. |
| `edit_categories` | Modification. |

### Formulaire creation categorie

Fichier : `resources/js/Pages/Categories/CreateCategorieModal.vue`

Endpoint : `POST categories.store`

| Champ formulaire | Colonne sauvegardee | Type UI | Regle backend |
|---|---|---|---|
| `nom` | `categories.nom` | Texte | Obligatoire, string, max 255. |
| `code` | `categories.code` | Texte | Obligatoire, string, max 20, unique. |
| `numero_lot` | `categories.numero_lot` | Select | Optionnel, entier entre 1 et 99. |
| `code_lot` | `categories.code_lot` | Texte | Optionnel, string, max 20. |
| `couleur_affichage` | `categories.couleur_affichage` et `categories.couleur` | Color picker | Optionnel, string, max 20. |
| `icone` | `categories.icone` | Texte | Optionnel, string, max 80. |
| `description` | `categories.description` | Textarea | Optionnel, string, max 500. |
| `est_actif` | `categories.est_actif` | Checkbox | Boolean. |

Comportement automatique :

- Le choix du `numero_lot` applique un preset.
- Le preset remplit automatiquement `code_lot`, `couleur_affichage` et `icone`.
- A la creation, `couleur` est aussi rempli depuis `couleur_affichage`.
- La categorie est active si `est_actif` vaut `true`.

Presets lot utilises dans le formulaire :

| Lot | Code | Nom | Couleur | Icone |
|---:|---|---|---|---|
| 1 | `LOT-1` | Viandes et Abats | `#0b2f5f` | `ScaleIcon` |
| 2 | `LOT-2` | Volailles, Lapin et Oeufs | `#155e9f` | `Squares2X2Icon` |
| 3 | `LOT-3` | Poissons et Fruits de Mer | `#0ea5b7` | `GlobeAltIcon` |
| 4 | `LOT-4` | Epicerie | `#15803d` | `ShoppingBagIcon` |
| 5 | `LOT-5` | Fruits et Legumes | `#f59e0b` | `SunIcon` |
| 6 | `LOT-6` | Pain et Viennoiserie | `#ea580c` | `CakeIcon` |

### Formulaire modification categorie

Fichier : `resources/js/Pages/Categories/EditCategorieModal.vue`

Endpoint : `PUT categories.update`

Champs identiques au formulaire de creation.

Comportement supplementaire :

- Quand une categorie est modifiee, les articles rattaches sont synchronises.
- Les colonnes suivantes sont propagees vers tous les articles de la categorie :
  - `code_lot`
  - `numero_lot`
  - `couleur_affichage`
  - `icone`

### Formulaire import bordereau

Fichier : `resources/js/Pages/Categories/Index.vue`

Endpoint : `POST categories.import`

| Champ formulaire | Type UI | Regle backend | Role |
|---|---|---|---|
| `fichier` | File input | Obligatoire, fichier `xlsx`, `xls`, `csv` ou `ods`, max 20 Mo. | Importe les articles depuis un bordereau. |
| `numero_lot` | Select | Optionnel, entier dans `[1,2,3,4,5,6]`. | Force le lot ou laisse la detection automatique. |

Service utilise : `App\Services\BordereauArticleImporter`.

Effet :

- Cree ou met a jour les categories.
- Cree ou met a jour les articles.
- Alimente les donnees lot et affichage depuis la categorie.

### Informations affichees dans la liste Categories

La page `Categories/Index.vue` affiche :

- KPI : nombre de categories, articles, articles actifs, sous seuil, rupture.
- Filtres : recherche, lot, marche, statut, tri.
- Cartes categorie avec :
  - nom
  - code lot ou code interne
  - description
  - couleur et icone
  - nombre d'articles
  - nombre de marches
  - entrees stock
  - sorties stock
  - articles en rupture
  - articles sous seuil
  - articles actifs

## Module Articles

### Role metier

Un article appartient a une seule categorie metier.

Exemple :

```text
Categorie : Poissons et Fruits de Mer
Articles  : Sardine, Dorade, Merlan, Calamar, Crevette
```

### Table principale : `articles`

| Colonne | Type base | Obligatoire | Role |
|---|---:|---:|---|
| `id` | bigint unsigned | Oui | Identifiant technique. |
| `reference` | varchar(50) | Oui | Reference unique de l'article. |
| `designation` | varchar(191) | Oui | Nom de l'article. |
| `description` | text | Non | Description de l'article. |
| `quantite_stock` | decimal(8,2) | Oui | Stock courant. Defaut : `0.00`. |
| `categorie_id` | bigint unsigned | Oui | Cle etrangere vers `categories.id`. |
| `unite_mesure` | varchar(20) | Oui | Unite de gestion : kg, L, piece, sac, etc. |
| `taux_tva` | decimal(5,2) | Oui | TVA technique sur l'article. Defaut : `0.00`. |
| `seuil_minimal` | int | Oui | Seuil d'alerte bas. Defaut : `0`. |
| `seuil_maximal` | int | Oui | Seuil haut. Defaut : `0`. |
| `in_marche` | tinyint(1) | Oui | Indique si l'article est deja utilise dans un marche. Defaut : `0`. |
| `est_actif` | tinyint(1) | Oui | Article actif ou inactif. Defaut : `1`. |
| `created_at` | timestamp | Non | Date de creation. |
| `updated_at` | timestamp | Non | Date de modification. |
| `code_lot` | varchar(20) | Non | Copie du `code_lot` de la categorie. |
| `numero_lot` | tinyint unsigned | Non | Copie du `numero_lot` de la categorie. |
| `couleur_affichage` | varchar(20) | Non | Copie de la couleur de la categorie. |
| `icone` | varchar(80) | Non | Copie de l'icone de la categorie. |

### Table secondaire : `article_images`

Cette table est utilisee pour afficher l'image principale d'un article dans la liste et le detail.

| Colonne | Type base | Obligatoire | Role |
|---|---:|---:|---|
| `id` | bigint unsigned | Oui | Identifiant technique. |
| `article_id` | bigint unsigned | Oui | Cle etrangere vers `articles.id`. |
| `image_path` | varchar(191) | Oui | Chemin relatif dans `storage`. |
| `nom_fichier` | varchar(191) | Oui | Nom original ou nom technique du fichier. |
| `est_principale` | tinyint(1) | Oui | Image principale de l'article. Defaut : `0`. |
| `created_at` | timestamp | Non | Date de creation. |
| `updated_at` | timestamp | Non | Date de modification. |

Important : le formulaire principal Article actuel ne contient pas d'upload image. Les images sont chargees pour affichage via la relation `images`.

### Relations Eloquent

Depuis `App\Models\Article` :

| Relation | Type | Table cible | Cle |
|---|---|---|---|
| `categorie()` | belongsTo | `categories` | `articles.categorie_id` |
| `images()` | hasMany | `article_images` | `article_images.article_id` |
| `bonCommandeArticles()` | hasMany | `bon_commande_articles` | `bon_commande_articles.article_id` |
| `lignesReception()` | hasMany | `ligne_receptions` | `ligne_receptions.article_id` |
| `mouvementsStock()` | hasMany | `mouvement_stocks` | `mouvement_stocks.article_id` |
| `mouvementsStockEntree()` | hasMany filtre | `mouvement_stocks` | `type_mouvement = entree` |
| `mouvementsStockSortie()` | hasMany filtre | `mouvement_stocks` | `type_mouvement = sortie` |

### Tables consultees dans les ecrans Articles

| Table | Utilisation |
|---|---|
| `articles` | Liste, creation, modification, detail. |
| `categories` | Select categorie, filtre categorie, badge categorie. |
| `article_images` | Image principale affichee dans la liste et le detail. |
| `bon_commande_articles` | Prix courant via le marche actif. |
| `bon_commandes` | Verification du marche actif pour le prix courant. |
| `mouvement_stocks` | Stock, entrees, sorties et valorisation indirecte. |

### Routes du module Articles

Routes declarees par `Route::resource('articles', ArticleController::class)`.

Actions actives dans le controleur :

| Methode | URL | Nom route | Action |
|---|---|---|---|
| GET | `/articles` | `articles.index` | Liste des articles. |
| GET | `/articles/create` | `articles.create` | Modal de creation. |
| POST | `/articles` | `articles.store` | Creation. |
| GET | `/articles/{article}` | `articles.show` | Detail. |
| GET | `/articles/{article}/edit` | `articles.edit` | Modal de modification. |
| PUT/PATCH | `/articles/{article}` | `articles.update` | Modification. |

### Permissions Spatie

| Permission | Utilisation |
|---|---|
| `list_articles` | Acces liste. |
| `show_articles` | Acces detail. |
| `create_articles` | Creation. |
| `edit_articles` | Modification. |

### Formulaire creation article

Fichier : `resources/js/Pages/Articles/CreateArticleModal.vue`

Endpoint : `POST articles.store`

| Champ formulaire | Colonne sauvegardee | Type UI | Regle backend |
|---|---|---|---|
| `reference` | `articles.reference` | Texte | Obligatoire, string, max 50, unique. |
| `designation` | `articles.designation` | Texte | Obligatoire, string, max 255. |
| `description` | `articles.description` | Textarea | Optionnel, string, max 255. |
| `categorie_id` | `articles.categorie_id` | Select | Obligatoire, doit exister dans `categories.id`. |
| `unite_mesure` | `articles.unite_mesure` | Select | Obligatoire, valeur autorisee. |
| `est_actif` | `articles.est_actif` | Checkbox | Boolean. |

Valeurs autorisees pour `unite_mesure` :

- `kg`
- `L`
- `piece`
- `sachet`
- `sac`
- `boite`
- `bidon`
- `paquet`
- `flacon`
- `pot`
- `bouteille`

Comportement automatique a la creation :

- Le controleur recupere la categorie choisie.
- Les champs suivants sont copies depuis la categorie vers l'article :
  - `code_lot`
  - `numero_lot`
  - `couleur_affichage`
  - `icone`
- `seuil_minimal` est initialise a `0`.
- `seuil_maximal` est initialise a `0`.
- `taux_tva` existe dans l'objet formulaire Vue mais n'est pas persiste par `ArticleController::store`.
- `quantite_stock` reste a la valeur par defaut base : `0.00`.

### Formulaire modification article

Fichier : `resources/js/Pages/Articles/EditArticleModal.vue`

Endpoint : `PUT articles.update`

Champs visibles identiques au formulaire de creation :

- `reference`
- `designation`
- `description`
- `categorie_id`
- `unite_mesure`
- `est_actif`

Champs presents dans l'objet formulaire mais non visibles dans le template actuel :

- `taux_tva`
- `seuil_minimal`
- `seuil_maximal`

Comportement automatique a la modification :

- Si la categorie change, les donnees de lot et d'affichage sont recopiees depuis la nouvelle categorie.
- Le controleur met a jour :
  - `reference`
  - `designation`
  - `description`
  - `categorie_id`
  - `code_lot`
  - `numero_lot`
  - `couleur_affichage`
  - `icone`
  - `unite_mesure`
  - `seuil_minimal`
  - `seuil_maximal`
  - `est_actif`
- `taux_tva` est envoye par le formulaire mais n'est pas sauvegarde par `ArticleController::update`.

### Informations affichees dans la liste Articles

La page `Articles/Index.vue` affiche :

- KPI : total articles, articles actifs, sous seuil, rupture.
- Filtres instantanes :
  - recherche par reference, designation ou categorie
  - categorie
  - statut actif/inactif
  - stock normal, sous seuil, rupture
- Tableau articles :
  - photo principale ou initiale
  - designation
  - reference
  - categorie
  - unite
  - quantite stock
  - seuil minimal
  - statut stock
  - statut actif/inactif
  - actions voir / modifier selon permissions

## Regles metier importantes

### Categorie vers Article

Un article appartient uniquement a une categorie :

```text
categories.id -> articles.categorie_id
```

La categorie porte les informations de lot et d'affichage.

L'article recopie ces informations pour faciliter l'affichage et les filtres :

```text
categories.code_lot            -> articles.code_lot
categories.numero_lot          -> articles.numero_lot
categories.couleur_affichage   -> articles.couleur_affichage
categories.icone               -> articles.icone
```

### Stock article

Le stock est lu depuis `articles.quantite_stock`.

Les statuts affiches sont calcules ainsi :

| Statut | Condition |
|---|---|
| Rupture | `quantite_stock <= 0` |
| Sous seuil | `quantite_stock > 0` et `quantite_stock <= seuil_minimal` |
| Stock normal | `quantite_stock > seuil_minimal` |

### Prix courant article

Le prix courant est calcule depuis le marche actif :

```text
articles
  -> bon_commande_articles
  -> bon_commandes actifs entre date_debut et date_fin
```

Champ utilise :

- `bon_commande_articles.prix_unitaire_ht`

### Article dans marche

`articles.in_marche` indique si l'article est marque comme deja utilise dans un marche.

Le modele `Article` ajoute aussi un global scope `IsExistsInMarcheScope`, et les controleurs utilisent `withNonExists()` quand ils doivent inclure les articles normalement masques par ce scope.

## Colonnes legacy supprimees

Ces colonnes ne doivent plus etre utilisees dans les formulaires, les modeles ou les requetes actives :

- `categorie_principale_id`
- `nature_prestation_id`
- `marche_category_id`
- `legacy_marche_category_id`

Tables legacy supprimees de la base active :

- `categorie_principales`
- `nature_prestations`
- `marche_categories`

Tables archive conservees :

- `archive_categorie_principales`
- `archive_nature_prestations`
- `archive_marche_categories`

## Resume rapide des formulaires

### Creation / modification categorie

Champs visibles :

- Nom
- Code interne
- Lot marche
- Code lot
- Couleur
- Icone Heroicons
- Description
- Categorie active

### Import categorie/articles

Champs visibles :

- Fichier bordereau
- Numero de lot optionnel

### Creation / modification article

Champs visibles :

- Reference
- Designation
- Description
- Categorie
- Unite
- Article actif

Champs techniques geres automatiquement :

- Stock courant
- Seuil minimal
- Seuil maximal
- Donnees de lot copiees depuis la categorie
- Couleur et icone copiees depuis la categorie
